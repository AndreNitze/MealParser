<?php
	// Debug Mode
	// ini_set('display_errors', 'On');
	// error_reporting(E_ALL | E_STRICT);
	// Set local time
	date_default_timezone_set("Europe/Berlin");
	setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
	header('Content-Type: application/json;charset=utf-8');
	
	// date from 'Do, 18. Juni 2015' to '2015-06-08'
	function formatDate($weirdDate) {
		$date = date_parse_from_format('D, d. M Y', $weirdDate);
		$day = $date["day"];
		$year = $date["year"];
		
		// parse localized months manually
		$aMonths = array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");
		$dateElements = explode(' ', trim(substr($weirdDate, 3)));
		$month = $dateElements[1];
		
		// use the number of the month
		if (in_array($month, $aMonths)) {
			$nMonth = array_search($month, $aMonths);
			// array key +1
			$nMonth = $nMonth+1;
		} else {
			$nMonth = "1";
		}
		
		return strftime("%Y-%m-%d", mktime(0, 0, 0, $nMonth, $day, $year));
	}
	
	// string from 'Gegrillte H&auml;hnchenkeule mit Letscho\r\nund gebackenen Kartoffelecken'
	// to 'Gegrillte Hähnchenkeule mit Letscho und gebackenen Kartoffelecken'
	// TODO or from 'Hähnchengeschnetzeltes ;&quot;Calvados;&quot;'
	// to 'Hähnchengeschnetzeltes 'Calvados'
	function formatString($weirdString) {
		// $weirdString = preg_replace("/\([^)]+\)/", "", $weirdString);
		$weirdString = preg_replace('/\s*,/', ',', $weirdString);
		$weirdString = str_replace(' - ', '-', $weirdString);
		$weirdString = preg_replace('/\s(\r\n)/', ' ', $weirdString);
		$weirdString = preg_replace('/(\r\n)/', ' ', $weirdString);
		$weirdString = str_replace('&quot;',"'",$weirdString);
		$weirdString = html_entity_decode($weirdString, ENT_COMPAT, 'UTF-8');
		return $weirdString;
	}
	
	// Meal API
	// Fetches meals from a web page and provides RESTful web service
	// @author André Nitze (andre.nitze@fh-brandenburg.de) and Jano Espenhahn (espenhah@fh-brandenburg.de)
	// TODO Error handling
	$filename = date('Y-m-d');
	$resultArray = array();
	
	// arrays for all known additives and allergens
	$knownAdditives = array("(1)", "(2)", "(3)", "(4)", "(5)", "(6)", "(7)", "(8)", "(9)", "(11)", "(13)", "(14)", "(20)", "(21)", "(22)", "(23)", "(KF)", "(TL)", "(AL)", "(GE)");
	$knownAllergens = array("(A)", "(B)", "(C)", "(D)", "(E)", "(F)", "(G)", "(H)", "(I)", "(J)", "(K)", "(L)", "(M)", "(N)");
		
	if (file_exists($filename) && !isset($_GET['force_update'])) {
		$resultArray = unserialize( file_get_contents($filename) );
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
			$xPathQueryMeals = "//table[@class]/tr[2]/td";
			
			// making xpath more simple
			$doc = new DOMDocument();
			$doc->loadHTML($html);
			$xml = simplexml_import_dom($doc);
			
			// Set current date
			$dates[0] = date("Y-m-d");
			
			// Query the DOM for xPaths where meals are
			$elements = $xpath->query($xPathQueryMeals);
			
			// fetch meals from today
			$j = 0;
			foreach ($elements as $element) {
				
				// fetch, sanitize meals-string and store in array
				$meals[$j] = formatString(htmlentities($element->nodeValue));
				$j++;
			}
			
			$symbols = array();
			$additives = array();
			$allergens = array();
			
			// combine today meals and other stuff
			for ($k = 1; $k <= count($meals); $k++) {
				
				$cleanString = (string) $meals[$k-1];
				
				// set actual xPathes for symbols, additives and allergens
				$xPathQuerySymbols = "//table/tr/td/div[2]/table/tr[3]/td[$k]/div[2]/a/img";
				$xPathQueryAdditives = "//table/tr/td/div[2]/table/tr[3]/td[$k]/div[1]/a";
				
				// fetch symbols
				foreach ($xml->xpath($xPathQuerySymbols) as $img) {
					if (isset($img["title"])) {
						$symbols[] = (string) $img['title'];
					}	
				}
				
				// fetch additives and allergens
				foreach ($xpath->query($xPathQueryAdditives) as $textNode) {
					$value = $textNode->nodeValue;
					if (in_array($value, $knownAdditives) && !(in_array($value, $additives))) {
						$additives[] = $value;
					} elseif (in_array($value, $knownAllergens)&& !(in_array($value, $allergens))) {
						$allergens[] = $value;
					}
				}
				
				// empty arrays in the right form: value = [ null ] => value = []
				if(!$symbols)
				{
					$symbols = array();
				}
				if(!$additives)
				{
					$additives = array();
				}
				
				if(!$allergens)
				{
					$allergens = array();
				}
				
				// fill the meals with other informations
				if (strlen($cleanString) > 0) {
					$mealsArray[$k - 1] = ['mealNumber' => $k, 'name' => $cleanString, 'symbols' => $symbols, 'additives' => $additives, 'allergens' => $allergens];
				}
				
				// unset for the next run
				unset($symbols);
				unset($additives);
				unset($allergens);
			}
			
			// Add todays' meal to resultArray set, if meals exist
			if (count($mealsArray) > 0 ) {
				$resultArray[] = ['date' => $dates[0], 'meals' => $mealsArray];
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
		
		// Query the DOM for xPaths where meals and additives are
		$xPathQueryMeals = "//td[@class='text1'] | //td[@class='text2'] | //td[@class='text3'] | //td[@class='text4']";
		$meals = $xpath->query($xPathQueryMeals);
		
		// making xpath more simple
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$xml = simplexml_import_dom($doc);
		
		// Combine dates and corresponding meals
		$mealsPerDay = 4;
		$j = 1;
		$actualDay = 1;
		$dayOfWeek = date("N");
		$div = 2;
		$mealsArray = array();
		$symbols = array();
		$additives = array();
		$allergens = array();
		
		// specific for the html on the side
		if ($dayOfWeek >= 5) {
			$div = 3;
		}
		
		foreach ($dates as $date) {
			for ($i = 1; $i <= $mealsPerDay; $i++) {
				
				// fetch and sanitize meals-string
				$cleanString = formatString(htmlentities($meals->item($j + $i - 2)->nodeValue, ENT_COMPAT));
				
				// set actual xPathes
				$xPathQuerySymbols = "//table/tr/td/div[$div]/table[$actualDay]/tr[4]/td[$i]/div[2]/a/img";
				$xPathQueryAdditives = "//table/tr/td/div[$div]/table[$actualDay]/tr[4]/td[$i]/div[1]/a";
				
				// fetch symbols
				foreach ($xml->xpath($xPathQuerySymbols) as $img) {
					if (isset($img["title"])) {
						$symbols[] = (string) $img['title'];
					}	
				}
				
				// fetch additives and allergens
				foreach ($xpath->query($xPathQueryAdditives) as $textNode) {
					$value = $textNode->nodeValue;
					if (in_array($value, $knownAdditives) && !(in_array($value, $additives))) {
						$additives[] = $value;
					} elseif (in_array($value, $knownAllergens)&& !(in_array($value, $allergens))) {
						$allergens[] = $value;
					}
				}
				
				// empty arrays in the right form: value = [ null ] => value = []
				if(!$symbols)
				{
					$symbols = array();
				}
				if(!$additives)
				{
					$additives = array();
				}
				
				if(!$allergens)
				{
					$allergens = array();
				}
				
				// fill the meals with other informations
				if (strlen($cleanString) > 0) {
					$mealsArray[$i - 1] = ['mealNumber' => $i, 'name' => $cleanString, 'symbols' => $symbols, 'additives' => $additives, 'allergens' => $allergens];
				}
				
				// unset for the next run
				unset($symbols);
				unset($additives);
				unset($allergens);
			}
			
			$j += $mealsPerDay;
			
			// Add this days' meals to resultArray set
			$resultArray[] = ['date' => $date, 'meals' => $mealsArray];
			
			// persist for the next time to save traffic
			if ($UP_TO_DATE) {
				file_put_contents($filename, serialize($resultArray));
			}
			
			// it's because of the html on the side
			if (($dayOfWeek >= 5 && $actualDay == 5 && $runs == 0) |
				($dayOfWeek == 4 && $actualDay == 1 && $runs == 0) |
				($dayOfWeek == 3 && $actualDay == 2 && $runs == 0) |
				($dayOfWeek == 2 && $actualDay == 3 && $runs == 0) |
				($dayOfWeek == 1 && $actualDay == 4 && $runs == 0)) {
				$actualDay = 1;
				$div++;
				$runs++;
			} else if ($runs > 0 && $actualDay == 5) {
				$actualDay = 1;
				$div++;
			} else {
				$actualDay++;
			}	
		}
		curl_close($ch);
	}
	
	// put the array in a list
	$result = ['days' => $resultArray];
	
	echo json_encode($result);
?> 
