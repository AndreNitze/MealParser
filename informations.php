<?php
	date_default_timezone_set("Europe/Berlin");
	setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
	header('Content-Type: application/json;charset=utf-8');
	
	// Array for additives
	$additives = [
		[
			"id" => "(1)",
			"definition" => "mit Farbstoff",
			"meaning" => "Optische Aufwertung der wertbestimmenden Zutaten (z.B. höherer Fruchtanteil in der Kaltschale)."
		],[
			"id" => "(2)",
			"definition" => "mit Konservierungsstoff",
			"meaning" => "Erhaltung bzw. Verlängerung der Genusstauglichkeit des Lebensmittels."
		],[
			"id" => "(3)",
			"definition" => "mit Antioxidationsmittel",
			"meaning" => "wie (1) und (2)"
		],[
			"id" => "(4)",
			"definition" => "mit Geschmacksverstärker",
			"meaning" => "zur Verstärkung des Geschmacks der wertbestimmenden Zutaten"
		],[
			"id" => "(5)",
			"definition" => "geschwefelt",
			"meaning" => "Schwefel dient der Abtötung von unerwünschten Mikroorganismen"
		],[
			"id" => "(6)",
			"definition" => "geschwärzt",
			"meaning" => "Schwärzung erfolgt durch Eisenoxide. Zur Färbung grüner Oliven."
		],[
			"id" => "(7)",
			"definition" => "gewachst",
			"meaning" => "Überzugsmittel der Fruchtschale von Zitrusfrüchten zur Beeinflussung der Haltbarkeit."
		],[
			"id" => "(8)",
			"definition" => "mit Phosphat",
			"meaning" => "Bestandteil des Erbgutes aller Lebewesen und ist in Lebensmitteln tierischen Ursprungs enthalten. Phosphatverbindungen werden u.a. als Säuerungsmittel in Cola, Wurstwaren eingesetzt"
		],[
			"id" => "(9)",
			"definition" => "mit Süßungsmittel",
			"meaning" => "Süßstoffe, liefern kaum Nahrungsenergie und werden deshalb u.a. in energiereduzierten Lebensmitteln eingesetzt"
		],[
			"id" => "(11)",
			"definition" => "mit Aspartam-Acesulfamsalz (eingesetzt enthält eine Phenylalaninquelle)",
			"meaning" => "Wird als Süßungsmittel oder Geschmacksverstärker eingesetzt. Es geht im Stoffwechsel des Körpers ein. Der Eiweißbaustein Phenylalanin führt bei Personen, die an Phenylketourie leiden zu schweren Gesundheitsschäden."
		],[
			"id" => "(13)",
			"definition" => "mit Milcheiweiß",
			"meaning" => ""
		],[
			"id" => "(14)",
			"definition" => "mit Eiklar",
			"meaning" => "Einsatz von Fremdeiweiß, wird als Bindemittel verwendet"
		],[
			"id" => "(20)",
			"definition" => "chininhaltig",
			"meaning" => "Bitteraroma in Erfrischungsgetränken wie Tonic-Wasser."
		],[
			"id" => "(21)",
			"definition" => "mit Koffein",
			"meaning" => "Aroma-gebende Komponente"
		],[
			"id" => "(22)",
			"definition" => "mit Milchpulver",
			"meaning" => ""
		],[
			"id" => "(23)",
			"definition" => "mit Molkenpulver",
			"meaning" => ""
		],[
			"id" => "(KF)",
			"definition" => "mit kakaohaltiger Fettglasur",
			"meaning" => ""
		],[
			"id" => "(TL)",
			"definition" => "enthält tierisches Lab",
			"meaning" => ""
		],[
			"id" => "(AL)",
			"definition" => "mit Alkohol",
			"meaning" => "Aroma-gebende Komponente"
		],[
			"id" => "(GE)",
			"definition" => "mit Gelatine",
			"meaning" => ""
		]
	];
	
	// Array for allergens
	$allergens = [
		[
			"id" => "(A)",
			"definition" => "Gluten ist das Klebereiweiß in den Getreidesorten Weizen, Dinkel, Roggen, Gerste Hafer und Kamut",
			"containedIn" => "Saucen, panierte Speisen, Puddings, Bulgur, Couscous, Grießspeisen, Backwaren, Saitan, verzehrfertige Joghurt-und Quarkspeisen, Feinkostsalate, Wurstwaren, Schimmel- und Schmelzkäse"
		],[
			"id" => "(B)",
			"definition" => "Krebstiere sind Garnelen, Hummer, Fluss-und Taschenkrebse, Krabben",
			"containedIn" => "Feinkostsalate, Paella, Bouillabaise, asiatische Suppen, Saucen und Würzmischungen"
		],[
			"id" => "(C)",
			"definition" => "Eier",
			"containedIn" => "Mayonnaisen, Remouladen, Teigwaren (Tortellini, Spätzle, Schupfnudeln), Gnocchi, Backwaren, Panaden, geklärte und gebundene Suppen"
		],[
			"id" => "(D)",
			"definition" => "Fisch",
			"containedIn" => "Paella, Bouillabaise, Worchester Sauce, asiatische Würzpasten"
		],[
			"id" => "(E)",
			"definition" => "Erdnüsse",
			"containedIn" => "Frühstücksflocken, Backwaren, Süßspeisen- und Aufstriche, Würzsaucen, Gemüsebratlinge"
		],[
			"id" => "(F)",
			"definition" => "Soja",
			"containedIn" => "Milch- und Sahneersatz auf Sojabasis, Tofu, Sojasauce, Zusatzstoff in Süsswaren v.a. in Schokolade, Wurst- und Fleischwaren"
		],[
			"id" => "(G)",
			"definition" => "Milch",
			"containedIn" => "Backwaren, vegetarische Bratlinge, Wurstwaren, Dressings und Würzsaucen"
		],[
			"id" => "(H)",
			"definition" => "Schalenfrüchte sind Mandeln, Hasel-, Wal-, Cashew-, Pecan-, Para- und Macadamianüsse, Pistazien",
			"containedIn" => "Marzipan, Nougat, Aufstriche, Back-, Wurstwaren, Pesto, Feinkostsalate, vegetarische Bratlinge"
		],[
			"id" => "(I)",
			"definition" => "Sellerie",
			"containedIn" => "Gewürzmischungen, Salatsaucenbasis, Instant-Brühen, Fleischwaren, Ketchup, Bratlinge"
		],[
			"id" => "(J)",
			"definition" => "Senf",
			"containedIn" => "Gesäuerte Gemüse, Chutneys, Dressings, Wurstwaren, Bratlinge"
		],[
			"id" => "(K)",
			"definition" => "Sesam",
			"containedIn" => "Backwaren, Frühstückscerealien, Brotaufstriche"
		],[
			"id" => "(L)",
			"definition" => "Schwefeldioxid, Sulfite",
			"containedIn" => "Wein, weinhaltige Getränke, getrocknete Früchte Convenience-Produkte (z.B. Bratkartoffel, Instant-Kartoffelpüree), Konserven"
		],[
			"id" => "(M)",
			"definition" => "Lupine",
			"containedIn" => "Vegetarische Convenience-Produkte, regenerierfertige Backwaren"
		],[
			"id" => "(N)",
			"definition" => "Weichtiere sind Schnecken, Muscheln, Austern und Tintenfische",
			"containedIn" => "Fisch- und Feinkostsalate, Paella und Bouillabaise, asiatische Suppen, Saucen und Würzmischungen"
		]
	];
	
	// Array for nutritions
	$nutritions = [
		[
			"id" => "(NOR)",
			"name" => "Normal",
			"definition" => "Keine Einschränkungen.",
			"excludedSymbols" => array(),
			"excludedAdditives" => array(),
			"excludedAllergens" => array()
		],[
			"id" => "(OVO)",
			"name" => "Ovo-Lacto-Vegetarisch",
			"definition" => "Ovo-Lacto-Vegetarier verzehren, neben pflanzlichen Nahrungsmitteln, nur Produkte, die von lebenden Tieren stammen.",
			"excludedSymbols" => [ "mit Schweinefleisch", "mit Rindfleisch", "mit Lamm", "mit Fisch", "mit Geflügelfleisch"],
			"excludedAdditives" => [ "(GE)" ],
			"excludedAllergens" => [ "(B)", "(D)", "(N)" ]
		],[
			"id" => "(VEG)",
			"name" => "Vegan",
			"definition" => "Veganismus ist eine besondere Form des Vegetarismus, bei der keinerlei tierische Produkte konsumiert werden.",
			"excludedSymbols" => [ "mit Schweinefleisch", "mit Rindfleisch", "mit Lamm", "mit Fisch", "mit Geflügelfleisch"],
			"excludedAdditives" => [ "(13)", "(14)", "(22)", "(23)", "(TL)", "(GE)" ],
			"excludedAllergens" => [ "(B)", "(C)", "(D)", "(G)", "(N)" ]
		]
	];
	
	// Array for symbols
	$infoSymbols = [
		[
	        "id" => "mit Schweinefleisch",
	        "definition" => "Schweinefleisch"
	    ],[
	        "id" => "mit Geflügelfleisch",
	        "definition" => "Geflügelfleisch"
	    ],[
	        "id" => "mit Fisch",
	        "definition" => "Fisch"
	    ],[
	        "id" => "mit Rindfleisch",
	        "definition" => "Rindfleisch"
	    ],[
	        "id" => "mit Lamm",
	        "definition" => "Lammfleisch"
	    ],[
	        "id" => "ovo-lacto-vegetabil",
	        "definition" => "ovo-lacto-vegetabil"
	    ],[
	        "id" => "vegan",
	        "definition" => "vegan"
	    ],[
	        "id" => "mensaVital",
	        "definition" => "Mensa Vital"
	    ]
	];
		
	$result = ['additives' => $additives, 'allergens' => $allergens, 'nutritions' => $nutritions, 'infoSymbols' => $infoSymbols];

	// Convert Array to JSON String and echo
	echo json_encode($result);
?>
