<?php

namespace Allian\Helpers;

use Database\Connect;

class ArrayValues {

	public static function timezonesTop(){
		$timezones_array_top = array(
			'US/Pacific' => "Pacific Time (UTC -8:00)",
			'US/Mountain' => "Mountain Time (UTC -7:00)",
			'US/Central' => "Central Time (UTC -6:00)",
			'US/Eastern' => "Eastern Time (UTC -5:00)",
			'US/Hawaii' => "Hawaii­Aleutian Time (UTC -10:00)",
			'US/Alaska' => "Alaska Time (UTC -9:00)",
			'Canada/Atlantic' => "Atlantic Time (Canada)  (UTC -4:00)",
			'America/Godthab' => "West Greenland Time (UTC -3:00)",
			'Atlantic/Stanley' => "Falkland Islands (UTC -2:00) ",
			'Atlantic/Cape_Verde' => "East Greenland Time (UTC -1:00)",
			'Europe/London' => "Western European Time (UTC)",
			'Europe/Berlin' => "Central European Time (UTC +1:00)",
			'Europe/Athens' => "Eastern European Time (UTC +2:00)",
			'Europe/Moscow' => "Moscow Time (UTC +3:00)",
			'Asia/Tehran' => "Iran Time (UTC +3:30)",
			'Asia/Yerevan' => "Russian Samara time (UTC +4:00)",
			'Asia/Tashkent' => "Russia Yekaterinburg Time (UTC +5:00)",
			'Asia/Dhaka' => "Bangladesh (UTC +6:00)",
			'Asia/Bangkok' => "Thailand Time (UTC +7:00)",
			'Asia/Ulaanbaatar' => "Mongolia Time (UTC +8:00)",
			'Asia/Seoul' => "South Korea Time (UTC +9:00)",
			'Australia/Sydney' => "Australia (UTC +10:00)",
			'Pacific/Auckland' => "New Zeland (UTC +12:00)",
		);

			return $timezones_array_top;
		// return array('timezonesTop' => $timezones_array_top);
	}

	public static function timezones(){
		$timezones_array = array(
		    'Pacific/Midway' => "Midway Island (UTC -11:00) ",
		    'US/Samoa' => "Samoa (UTC -11:00)",
		    'US/Hawaii' => "Hawaii (UTC -10:00)",
		    'US/Alaska' => "Alaska (UTC -09:00) ",
		    'US/Pacific' => "Pacific Time (US &amp; Canada) (UTC -08:00)",
		    'America/Tijuana' => "Tijuana (UTC -08:00)",
		    'US/Arizona' => "Arizona (UTC -07:00)",
		    'US/Mountain' => "Mountain Time (US &amp; Canada) (UTC -07:00)",
		    'America/Chihuahua' => "Chihuahua (UTC -07:00)",
		    'America/Mazatlan' => "Mazatlan (UTC -07:00)",
		    'America/Mexico_City' => "Mexico City (UTC -06:00)",
		    'America/Monterrey' => "Monterrey (UTC -06:00)",
		    'Canada/Saskatchewan' => "Saskatchewan (UTC -06:00)",
		    'US/Central' => "Central Time (US &amp; Canada) (UTC -06:00)",
		    'US/Eastern' => "Eastern Time (US &amp; Canada) (UTC -05:00)",
		    'US/East-Indiana' => "Indiana (East) (UTC -05:00)",
		    'America/Bogota' => "Bogota (UTC -05:00)",
		    'America/Lima' => "Lima (UTC -05:00)",
		    'America/Caracas' => "Caracas (UTC -04:30)",
		    'Canada/Atlantic' => "Atlantic Time (Canada) (UTC -04:00)",
		    'America/La_Paz' => "La Paz (UTC -04:00)",
		    'America/Santiago' => "Santiago (UTC -04:00)",
		    'Canada/Newfoundland' => "Newfoundland (UTC -03:30)",
		    'America/Buenos_Aires' => "Buenos Aires (UTC -03:00)",
		    'America/Godthab' => "Greenland (UTC -03:00)",
		    'Atlantic/Stanley' => "Stanley (UTC -02:00)",
		    'Atlantic/Azores' => "Azores (UTC -01:00)",
		    'Atlantic/Cape_Verde' => "Cape Verde Is. (UTC -01:00)",
		    'Africa/Casablanca' => "Casablanca (UTC )",
		    'Europe/Dublin' => "Dublin (UTC )",
		    'Europe/Lisbon' => "Lisbon (UTC )",
		    'Europe/London' => "London (UTC )",
		    'Africa/Monrovia' => "Monrovia (UTC )",
		    'Europe/Amsterdam' => "Amsterdam (UTC +01:00)",
		    'Europe/Belgrade' => "Belgrade (UTC +01:00)",
		    'Europe/Berlin' => "Berlin (UTC +01:00)",
		    'Europe/Bratislava' => "Bratislava (UTC +01:00)",
		    'Europe/Brussels' => "Brussels (UTC +01:00)",
		    'Europe/Budapest' => "Budapest (UTC +01:00)",
		    'Europe/Copenhagen' => "Copenhagen (UTC +01:00)",
		    'Europe/Ljubljana' => "Ljubljana (UTC +01:00)",
		    'Europe/Madrid' => "Madrid (UTC +01:00)",
		    'Europe/Paris' => "Paris (UTC +01:00)",
		    'Europe/Prague' => "Prague (UTC +01:00)",
		    'Europe/Rome' => "Rome (UTC +01:00)",
		    'Europe/Sarajevo' => "Sarajevo (UTC +01:00)",
		    'Europe/Skopje' => "Skopje (UTC +01:00)",
		    'Europe/Stockholm' => "Stockholm (UTC +01:00)",
		    'Europe/Vienna' => "Vienna (UTC +01:00)",
		    'Europe/Warsaw' => "Warsaw (UTC +01:00)",
		    'Europe/Zagreb' => "Zagreb (UTC +01:00)",
		    'Europe/Athens' => "Athens (UTC +02:00)",
		    'Europe/Bucharest' => "Bucharest (UTC +02:00)",
		    'Africa/Cairo' => "Cairo (UTC +02:00)",
		    'Africa/Harare' => "Harare (UTC +02:00)",
		    'Europe/Helsinki' => "Helsinki (UTC +02:00)",
		    'Europe/Istanbul' => "Istanbul (UTC +02:00)",
		    'Asia/Jerusalem' => "Jerusalem (UTC +02:00)",
		    'Europe/Kiev' => "Kyiv (UTC +02:00)",
		    'Europe/Minsk' => "Minsk (UTC +02:00)",
		    'Europe/Riga' => "Riga (UTC +02:00)",
		    'Europe/Sofia' => "Sofia (UTC +02:00)",
		    'Europe/Tallinn' => "Tallinn (UTC +02:00)",
		    'Europe/Vilnius' => "Vilnius (UTC +02:00)",
		    'Asia/Baghdad' => "Baghdad (UTC +03:00)",
		    'Asia/Kuwait' => "Kuwait (UTC +03:00)",
		    'Africa/Nairobi' => "Nairobi (UTC +03:00)",
		    'Asia/Riyadh' => "Riyadh (UTC +03:00)",
		    'Asia/Tehran' => "Tehran (UTC +03:30)",
		    'Europe/Moscow' => "Moscow (UTC +04:00)",
		    'Asia/Baku' => "Baku (UTC +04:00)",
		    'Europe/Volgograd' => "Volgograd (UTC +04:00)",
		    'Asia/Muscat' => "Muscat (UTC +04:00)",
		    'Asia/Tbilisi' => "Tbilisi (UTC +04:00)",
		    'Asia/Yerevan' => "Yerevan (UTC +04:00)",
		    'Asia/Kabul' => "Kabul (UTC +04:30)",
		    'Asia/Karachi' => "Karachi (UTC +05:00)",
		    'Asia/Tashkent' => "Tashkent (UTC +05:00)",
		    'Asia/Kolkata' => "Kolkata (UTC +05:30)",
		    'Asia/Kathmandu' => "Kathmandu (UTC +05:45)",
		    'Asia/Yekaterinburg' => "Ekaterinburg (UTC +06:00)",
		    'Asia/Almaty' => "Almaty (UTC +06:00)",
		    'Asia/Dhaka' => "Dhaka (UTC +06:00)",
		    'Asia/Novosibirsk' => "Novosibirsk (UTC +07:00)",
		    'Asia/Bangkok' => "Bangkok (UTC +07:00)",
		    'Asia/Jakarta' => "Jakarta (UTC +07:00)",
		    'Asia/Krasnoyarsk' => "Krasnoyarsk (UTC +08:00)",
		    'Asia/Chongqing' => "Chongqing (UTC +08:00)",
		    'Asia/Hong_Kong' => "Hong Kong (UTC +08:00)",
		    'Asia/Kuala_Lumpur' => "Kuala Lumpur (UTC +08:00)",
		    'Australia/Perth' => "Perth (UTC +08:00)",
		    'Asia/Singapore' => "Singapore (UTC +08:00)",
		    'Asia/Taipei' => "Taipei (UTC +08:00)",
		    'Asia/Ulaanbaatar' => "Ulaan Bataar (UTC +08:00)",
		    'Asia/Urumqi' => "Urumqi (UTC +08:00)",
		    'Asia/Irkutsk' => "Irkutsk (UTC +09:00)",
		    'Asia/Seoul' => "Seoul (UTC +09:00)",
		    'Asia/Tokyo' => "Tokyo (UTC +09:00)",
		    'Australia/Adelaide' => "Adelaide (UTC +09:30)",
		    'Australia/Darwin' => "Darwin (UTC +09:30)",
		    'Asia/Yakutsk' => "Yakutsk (UTC +10:00)",
		    'Australia/Brisbane' => "Brisbane (UTC +10:00)",
		    'Australia/Canberra' => "Canberra (UTC +10:00)",
		    'Pacific/Guam' => "Guam (UTC +10:00)",
		    'Australia/Hobart' => "Hobart (UTC +10:00)",
		    'Australia/Melbourne' => "Melbourne (UTC +10:00)",
		    'Pacific/Port_Moresby' => "Port Moresby (UTC +10:00)",
		    'Australia/Sydney' => "Sydney (UTC +10:00)",
		    'Asia/Vladivostok' => "Vladivostok (UTC +11:00)",
		    'Asia/Magadan' => "Magadan (UTC +12:00) ",
		    'Pacific/Auckland' => "Auckland (UTC +12:00) ",
		    'Pacific/Fiji' => "Fiji (UTC +12:00)",);
		return $timezones_array;
		return array('timezones' => $timezones_array);
	}

	public static function langFrom(){
		$con = Connect::con();
		$q = "select * from LangList ORDER BY LangName='English' desc, LangName asc";
        $res = mysqli_query($con, $q) or die("An Error Occurred");
        $langsFrom = array();
	    if ($res and mysqli_num_rows($res) > 0) {
	        for ($k = 0; $k < mysqli_num_rows($res); $k++) {
	            $row = mysqli_fetch_assoc($res);
	            $langsFrom[trim($row['LangName'])] = trim($row['LangName']);
            }
        }
        return $langsFrom;
        return array('langFrom' => $langsFrom);
	}

	public static function langTo(){
		$con = Connect::con();
		$q = "select * from LangList ORDER BY LangName='English' desc, LangName asc";
        $res = mysqli_query($con, $q) or die("An Error Occurred");
        $langsTo = array();
	    if ($res and mysqli_num_rows($res) > 0) {
	        for ($k = 0; $k < mysqli_num_rows($res); $k++) {
	            $row = mysqli_fetch_assoc($res);
	            $langsTo[trim($row['LangName'])] = trim($row['LangName']);
            }
        }
        return $langsTo;
        return array('langTo' => $langsTo);
	}

	public static function countries(){
		$countries = array(
				"United States"  => "United States",
				"United Kingdom"  => "United Kingdom",
				"Australia"  => "Australia",
				"Canada"  => "Canada",
				"France"  => "France",
				"New Zealand"  => "New Zealand",
				"India"  => "India",
				"Brazil"  => "Brazil",
				"Afghanistan"  => "Afghanistan",
				"Åland Islands"  => "Åland Islands",
				"Albania"  => "Albania",
				"Algeria"  => "Algeria",
				"American Samoa"  => "American Samoa",
				"Andorra"  => "Andorra",
				"Angola"  => "Angola",
				"Anguilla"  => "Anguilla",
				"Antarctica"  => "Antarctica",
				"Antigua and Barbuda"  => "Antigua and Barbuda",
				"Argentina"  => "Argentina",
				"Armenia"  => "Armenia",
				"Aruba"  => "Aruba",
				"Austria"  => "Austria",
				"Azerbaijan"  => "Azerbaijan",
				"Bahamas"  => "Bahamas",
				"Bahrain"  => "Bahrain",
				"Bangladesh"  => "Bangladesh",
				"Barbados"  => "Barbados",
				"Belarus"  => "Belarus",
				"Belgium"  => "Belgium",
				"Belize"  => "Belize",
				"Benin"  => "Benin",
				"Bermuda"  => "Bermuda",
				"Bhutan"  => "Bhutan",
				"Bolivia"  => "Bolivia",
				"Bosnia and Herzegovina"  => "Bosnia and Herzegovina",
				"Botswana"  => "Botswana",
				"British Indian Ocean Territory"  => "British Indian Ocean Territory",
				"Brunei Darussalam"  => "Brunei Darussalam",
				"Bulgaria"  => "Bulgaria",
				"Burkina Faso"  => "Burkina Faso",
				"Burundi"  => "Burundi",
				"Cambodia"  => "Cambodia",
				"Cameroon"  => "Cameroon",
				"Cape Verde"  => "Cape Verde",
				"Cayman Islands"  => "Cayman Islands",
				"Central African Republic"  => "Central African Republic",
				"Chad"  => "Chad",
				"Chile"  => "Chile",
				"China"  => "China",
				"Colombia"  => "Colombia",
				"Comoros"  => "Comoros",
				"Democratic Republic of the Congo"  => "Democratic Republic of the Congo",
				"Republic of the Congo"  => "Republic of the Congo",
				"Cook Islands"  => "Cook Islands",
				"Costa Rica"  => "Costa Rica",
				"Côte d'Ivoire"  => "Côte d'Ivoire",
				"Croatia"  => "Croatia",
				"Cuba"  => "Cuba",
				"Cyprus"  => "Cyprus",
				"Czech Republic"  => "Czech Republic",
				"Denmark"  => "Denmark",
				"Djibouti"  => "Djibouti",
				"Dominica"  => "Dominica",
				"Dominican Republic"  => "Dominican Republic",
				"East Timor"  => "East Timor",
				"Ecuador"  => "Ecuador",
				"Egypt"  => "Egypt",
				"El Salvador"  => "El Salvador",
				"Equatorial Guinea"  => "Equatorial Guinea",
				"Eritrea"  => "Eritrea",
				"Estonia"  => "Estonia",
				"Ethiopia"  => "Ethiopia",
				"Faroe Islands"  => "Faroe Islands",
				"Fiji"  => "Fiji",
				"Finland"  => "Finland",
				"Gabon"  => "Gabon",
				"Gambia"  => "Gambia",
				"Georgia"  => "Georgia",
				"Germany"  => "Germany",
				"Ghana"  => "Ghana",
				"Gibraltar"  => "Gibraltar",
				"Greece"  => "Greece",
				"Grenada"  => "Grenada",
				"Guatemala"  => "Guatemala",
				"Guinea"  => "Guinea",
				"Guinea-Bissau"  => "Guinea-Bissau",
				"Guyana"  => "Guyana",
				"Haiti"  => "Haiti",
				"Honduras"  => "Honduras",
				"Hong Kong"  => "Hong Kong",
				"Hungary"  => "Hungary",
				"Iceland"  => "Iceland",
				"Indonesia"  => "Indonesia",
				"Iran"  => "Iran",
				"Iraq"  => "Iraq",
				"Ireland"  => "Ireland",
				"Israel"  => "Israel",
				"Italy"  => "Italy",
				"Jamaica"  => "Jamaica",
				"Japan"  => "Japan",
				"Jordan"  => "Jordan",
				"Kazakhstan"  => "Kazakhstan",
				"Kenya"  => "Kenya",
				"Kiribati"  => "Kiribati",
				"North Korea"  => "North Korea",
				"South Korea"  => "South Korea",
				"Kuwait"  => "Kuwait",
				"Kyrgyzstan"  => "Kyrgyzstan",
				"Laos"  => "Laos",
				"Latvia"  => "Latvia",
				"Lebanon"  => "Lebanon",
				"Lesotho"  => "Lesotho",
				"Liberia"  => "Liberia",
				"Libya"  => "Libya",
				"Liechtenstein"  => "Liechtenstein",
				"Lithuania"  => "Lithuania",
				"Luxembourg"  => "Luxembourg",
				"Macedonia"  => "Macedonia",
				"Madagascar"  => "Madagascar",
				"Malawi"  => "Malawi",
				"Malaysia"  => "Malaysia",
				"Maldives"  => "Maldives",
				"Mali"  => "Mali",
				"Malta"  => "Malta",
				"Marshall Islands"  => "Marshall Islands",
				"Mauritania"  => "Mauritania",
				"Mauritius"  => "Mauritius",
				"Mexico"  => "Mexico",
				"Micronesia"  => "Micronesia",
				"Moldova"  => "Moldova",
				"Monaco"  => "Monaco",
				"Mongolia"  => "Mongolia",
				"Montenegro"  => "Montenegro",
				"Morocco"  => "Morocco",
				"Mozambique"  => "Mozambique",
				"Myanmar"  => "Myanmar",
				"Namibia"  => "Namibia",
				"Nauru"  => "Nauru",
				"Nepal"  => "Nepal",
				"Netherlands"  => "Netherlands",
				"Netherlands Antilles"  => "Netherlands Antilles",
				"Nicaragua"  => "Nicaragua",
				"Niger"  => "Niger",
				"Nigeria"  => "Nigeria",
				"Norway"  => "Norway",
				"Oman"  => "Oman",
				"Pakistan"  => "Pakistan",
				"Palau"  => "Palau",
				"Palestine"  => "Palestine",
				"Panama"  => "Panama",
				"Papua New Guinea"  => "Papua New Guinea",
				"Paraguay"  => "Paraguay",
				"Peru"  => "Peru",
				"Philippines"  => "Philippines",
				"Poland"  => "Poland",
				"Portugal"  => "Portugal",
				"Puerto Rico"  => "Puerto Rico",
				"Qatar"  => "Qatar",
				"Romania"  => "Romania",
				"Russia"  => "Russia",
				"Rwanda"  => "Rwanda",
				"Saint Kitts and Nevis"  => "Saint Kitts and Nevis",
				"Saint Lucia"  => "Saint Lucia",
				"Saint Vincent and the Grenadines"  => "Saint Vincent and the Grenadines",
				"Samoa"  => "Samoa",
				"San Marino"  => "San Marino",
				"Sao Tome and Principe"  => "Sao Tome and Principe",
				"Saudi Arabia"  => "Saudi Arabia",
				"Senegal"  => "Senegal",
				"Serbia"  => "Serbia",
				"Seychelles"  => "Seychelles",
				"Sierra Leone"  => "Sierra Leone",
				"Singapore"  => "Singapore",
				"Slovakia"  => "Slovakia",
				"Slovenia"  => "Slovenia",
				"Solomon Islands"  => "Solomon Islands",
				"Somalia"  => "Somalia",
				"South Africa"  => "South Africa",
				"Spain"  => "Spain",
				"Sri Lanka"  => "Sri Lanka",
				"Sudan"  => "Sudan",
				"Suriname"  => "Suriname",
				"Swaziland"  => "Swaziland",
				"Sweden"  => "Sweden",
				"Switzerland"  => "Switzerland",
				"Syria"  => "Syria",
				"Taiwan"  => "Taiwan",
				"Tajikistan"  => "Tajikistan",
				"Tanzania"  => "Tanzania",
				"Thailand"  => "Thailand",
				"Togo"  => "Togo",
				"Tonga"  => "Tonga",
				"Trinidad and Tobago"  => "Trinidad and Tobago",
				"Tunisia"  => "Tunisia",
				"Turkey"  => "Turkey",
				"Turkmenistan"  => "Turkmenistan",
				"Tuvalu"  => "Tuvalu",
				"Uganda"  => "Uganda",
				"Ukraine"  => "Ukraine",
				"United Arab Emirates"  => "United Arab Emirates",
				"United States Minor Outlying Islands"  => "United States Minor Outlying Islands",
				"Uruguay"  => "Uruguay",
				"Uzbekistan"  => "Uzbekistan",
				"Vanuatu"  => "Vanuatu",
				"Vatican City"  => "Vatican City",
				"Venezuela"  => "Venezuela",
				"Vietnam"  => "Vietnam",
				"Virgin Islands, British"  => "Virgin Islands, British",
				"Virgin Islands, U.S."  => "Virgin Islands, U.S.",
				"Yemen"  => "Yemen",
				"Zambia"  => "Zambia",
				"Zimbabwe"  => "Zimbabwe");
		return $countries;
		return array('countries' => $countries);
	}

	public static function schedulingType(){
		$schedulingType = array(
			'get_call' => 'Get Interpreters Call',
			'conference_call' => 'Conference Call');
		// return $schedulingType;
		return array('schedulingType' => $schedulingType);
	}

	public static function neededFor(){
		$neededFor = array(
			"Business Meeting" => "Business Meeting",
			"Court" => "Court",
			"Conference" => "Conference",
			"Depositions" => "Depositions",
			"Immigration Related" => "Immigration Related(USCIS/U.S. Consulate)",
			"Investigations" => "Investigations",
			"Medical" => "Medical",
			"Other" => "Other",);
		// return $neededFor;
		return array('neededFor' => $neededFor);
	}

	public static function supportPhones(){
		$supportPhones = array(
			"1 (877) 512 1195" => 'http://alliantranscribe.com/img/us.png',
			"1(877) 512 1195"=> 'http://alliantranscribe.com/img/canada.png',
			"+44 800 011 9648" =>'http://alliantranscribe.com/img/uk.png',
			"+33 9 75 18 41 68"=> 'http://alliantranscribe.com/img/france.png',
			"+34 518 88 82 27" =>'http://alliantranscribe.com/img/spain.png',
			"+39 06 9480 3714" =>'http://alliantranscribe.com/img/italy.png',
			"+49 157 3598 1132"=> 'http://alliantranscribe.com/img/german.png',
			"+61 8 7100 1671" =>'http://alliantranscribe.com/img/australia.png',
			"+31 85 888 5243"=> 'http://alliantranscribe.com/img/holland.png',
			"+32 2 588 55 16" =>'http://alliantranscribe.com/img/belgium.png',
			"+52 55 4161 3617"=> 'http://alliantranscribe.com/img/mexico.png',
			"+1 615 645 1041" =>'http://alliantranscribe.com/img/intl.png',
		);
		return array('supportPhones' => $supportPhones);
	}

	public static function supportFlags(){
		$supportFlags = array(
			 'http://alliantranscribe.com/img/us.png',
			 'http://alliantranscribe.com/img/canada.png',
			'http://alliantranscribe.com/img/uk.png',
			 'http://alliantranscribe.com/img/france.png',
			'http://alliantranscribe.com/img/spain.png',
			'http://alliantranscribe.com/img/italy.png',
			 'http://alliantranscribe.com/img/german.png',
			'http://alliantranscribe.com/img/australia.png',
			 'http://alliantranscribe.com/img/holland.png',
			'http://alliantranscribe.com/img/belgium.png',
			 'http://alliantranscribe.com/img/mexico.png',
			'http://alliantranscribe.com/img/intl.png',
		);
		return array('supportFlags' => $supportFlags);
	}

	public static function supportTel(){
		$supportTel = array(
			 "1 (877) 512 1195" ,
			"1(877) 512 1195",
			"+44 800 011 9648" ,
			"+33 9 75 18 41 68",
			"+34 518 88 82 27" ,
			"+39 06 9480 3714" ,
			"+49 157 3598 1132",
			"+61 8 7100 1671" ,
			"+31 85 888 5243",
			"+32 2 588 55 16" ,
			"+52 55 4161 3617",
			"+1 615 645 1041" ,
		);
		return array('supportPhones' => $supportTel);
	}

	public static function flags(){
		$flags = array(
			 'http://alliantranscribe.com/img/us.png',
			 'http://alliantranscribe.com/img/canada.png',
			 'http://alliantranscribe.com/img/uk.png',
			 'http://alliantranscribe.com/img/australia.png',
		);
		return array("flags" => $flags);
	}

	public static function telephones(){
		$telephones = array(
			"1 855-733-6655",
			"1 855-733-6655",
			"+44 800 802 1231",
			"+61 3 8609 8382",
		);
		return array("tel" => $telephones);
	}

}