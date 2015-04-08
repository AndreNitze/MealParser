<?php
// Debug Mode
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

// Set local time
date_default_timezone_set("Europe/Berlin");
setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

header('Content-Type: application/json;charset=utf-8');

function formatDate($weirdDate) {
    $date = date_parse_from_format('D, d. F Y', $weirdDate);
    $day = $date["day"];
    $year = $date["year"];

    // Parse localized months manually
    $dateElements = explode(' ', trim(substr($weirdDate, 3)));
    $month = $dateElements[1];

    $localizedMonths = array();
    for ($i = 1; $i <= 12; $i++) {
        // iconv trick for right encoding from http://php.net/manual/de/function.strftime.php
        $localizedMonths[$i] = iconv('ISO-8859-1', 'UTF-8',strftime('%B', mktime(0, 0, 0, $i, 1)));
        if ($month == $localizedMonths[$i]) {
            return strftime("%d.%m.%Y", mktime(0, 0, 0, $i, $day, $year));
        }
    }
}

// Meal API
// Fetches meals from a web page and provides RESTful web service
// @author André Nitze (andre.nitze@fh-brandenburg.de)

// TODO Error handling
// TODO Fix bug of not-up-to-date meal plan

$filename = date('Y-m-d');
$result = array();

if (file_exists($filename) && !isset($_GET['force_update'])) {
    $result = unserialize( file_get_contents($filename) );
} else {
    $dates = array();
    $meals = array();
    $json = "";

    // Fetch HTML with curl extension
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    // Create a DOM parser object
    $dom = new DOMDocument();

    // No meals after 2 pm (closing time)
    if (date('G') < 14) {
        // Get meal from todays' meal plan
        $url = "http://www.studentenwerk-potsdam.de/mensa-brandenburg.html";
        curl_setopt($ch, CURLOPT_URL, $url);
        $html = curl_exec($ch);

        if($html === FALSE) {
            die(curl_error($ch));
        }

        @$dom->loadHTML($html);

        // Create xPath expression and query
        $xpath = new DOMXpath($dom);

        $xpath_query = "//table[@class]/tr[2]/td";

        // Set current date
        $dates[0] = date("d.m.Y");

        // Query the DOM for xPaths where meals are
        $elements = $xpath->query($xpath_query);

        foreach ($elements as $element) {
            $meals[] = preg_replace("/\([^)]+\)/","", htmlentities($element->nodeValue));
        }

        // Add todays' meal to result set, if meals exist
        if (count($meals) > 0) {
            $result[] = ['date' => $dates[0], 'meals' => $meals];
            $UP_TO_DATE = true;
        } else {
            $UP_TO_DATE = false;
        }

        // Reset date array for 'upcoming meals' processing
        unset($dates);
    }

    // Append all upcoming meals to todays meal
    // Get meals from 'upcoming meals' schedule
    $url = "http://www.studentenwerk-potsdam.de/speiseplan.html";
    curl_setopt($ch, CURLOPT_URL, $url);
    $html = curl_exec($ch);

    if($html === FALSE) {
        die(curl_error($ch));
    }

    @$dom->loadHTML($html);
    // Create xPath expression
    $xpath = new DOMXpath($dom);

    // Query the DOM for date nodes
    $xPathQueryDates = "//div[@class='date']";
    $dateNodes = $xpath->query($xPathQueryDates);

    foreach ($dateNodes as $date) {
        $dates[] = formatDate($date->nodeValue);
    }

    // Query the DOM for xPaths where meals are
    $xPathQueryMeals = "//td[@class='text1'] | //td[@class='text2'] | //td[@class='text3'] | //td[@class='text4']";
    $meals = $xpath->query($xPathQueryMeals);

    // Combine dates and corresponding meals
    $mealsPerDay = 4;
    $j = 1;
    $allMealsPerDay = array();
    $length = $meals->length;

    foreach ($dates as $date) {
        for ($i = 1; $i <= $mealsPerDay; $i++) {

            // Sanitize string
            $cleanString = preg_replace("/\([^)]+\)/","", htmlentities($meals->item($j + $i - 2)->nodeValue, ENT_COMPAT));
            if (strlen($cleanString) > 0) {
                $allMealsPerDay[$i - 1] = $cleanString;
            }
        }
        $j += $mealsPerDay;

        // Add this days' meals to result set
        $result[] = ['date' => $date, 'meals' => $allMealsPerDay];
        unset($allMealsPerDay);
    }
    curl_close($ch);
}

// Only persist, if all meals are up-to-date
if ($UP_TO_DATE) {
    file_put_contents($filename, serialize($result));
}

$json = json_encode($result);
echo $json;