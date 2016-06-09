<?php

namespace Allian\Http\Controllers;

use Firebase\JWT\JWT;
use \Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\DomainException;
use Firebase\JWT\BeforeValidException;
use RNCryptor\Encryptor;
use RNCryptor\Decryptor;
use Allian\Models\LangList;
use Database\Connect;

class ConferenceScheduleController extends Controller {

	/**
     * @ApiDescription(section="GetTimezones", description="Retrieve json of top timezones and other timezones")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/getTimezones")
     * @ApiBody(sample="{ 'data': 'AwGsq1rYpTw4g6yAX/P7mkrAoKWLlnkxcAQUlNqeV1dyqztE1M4OiLEsM62DaKYeSBCyHilqoynA8MPx2St6jk+fioyzDMm6JZJ9DvECc4MIQpB7NYzK201LUoKl0Rhp7QY=',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjQ1OTMzNjcsImp0aSI6IlJoOGpiMVhUZHFvUDVDVUVSQ29VY3pWR0dnSVFsQWJ1bFwvRFp1U2pcL050OD0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0NjQ1OTMzNjcsImV4cCI6MTQ2NTgwMjk2NywiZGF0YSI6eyJTdWNjZXNzIjoiU3VjY2VzcyJ9fQ.JDwNdycstmqNC0dyrNgNuik_zXCYbx3PwbIkdTX7is3oDrQr6CKQ6mREUt-9tbOys361mcH1kyXaahn9Y2tTRg'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': ''}")
     */
	public function getTimezones($request, $response, $service, $app){
		// TODO       $q = "select * from LangList";
		//        $res = mysqli_query($con, $q) or die("An Error Occurred");
		//        $langs_from = '';
		//        $langs_to = '';
		//        if ($res and mysqli_num_rows($res) > 0) {
		//            for ($k = 0; $k < mysqli_num_rows($res); $k++) {
		//                $row = mysqli_fetch_assoc($res);
		//                $lid = $row['LangId'];
		//                $ln = $row['LangName'];
		//                $checked = (SERVER === "L" && $ln === "English") ? "selected='selected'" : "";
		//                $langs_from .= "<option value='$lid' $checked>$ln</option>
		// ";
		//                $checked = (SERVER === "L" && $ln === "Test") ? "selected='selected'" : "";
		//                $langs_to .= "
		// <option value='$lid' $checked>$ln</option>
		// ";
		//            }
		//        }
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			//Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// Validate token in database for customer stored
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				// $base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $this->errorJson("Authentication problems present")));
			}
			// Retrieve timezones
			// $timezones = include getcwd() . "/app/Http/Controllers/timezones.php";
			$timezones_array_top = array(
				'US/Pacific' => "Pacific Time (UTC -8:00)",
				'US/Mountain' => "Mountain Time (UTC -7:00)",
				'US/Central' => "Central Time (UTC -6:00)",
				'US/Eastern' => "Eastern Time (UTC -5:00)",
				'US/Hawaii' => "Hawaii­Aleutian Time (UTC -10:00)",
				'US/Alaska' => "Alaska Time (UTC -9:00)",
				'Canada/Atlantic' => "Atlantic Time (Canada)  (UTC -4:00)",
				'Greenland' => "West Greenland Time (UTC -3:00)",
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
			    'Greenland' => "Greenland (UTC -03:00)",
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
			    'Pacific/Fiji' => "Fiji (UTC +12:00)",
			);

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
				"C&ocirc;te d'Ivoire"  => "C&ocirc;te d'Ivoire",
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
				"Zimbabwe"  => "Zimbabwe"
			);

	$langFrom = array(
				"Spanish"  => "Spanish",
				"Arabic"  => "Arabic",
				"Chinese"  => "Chinese",
				"French"  => "French");
	$langTo = $langFrom = array(
				"Spanish"  => "Spanish",
				"Arabic"  => "Arabic",
				"Chinese"  => "Chinese",
				"French"  => "French");
	$neededFor = array("Business Meeting" => "Business Meeting",
					"Court" => "Court",
					"Conference" => "Conference",
					"Depositions" => "Depositions",
					"Immigration Related" => "Immigration Related(USCIS/U.S. Consulate)",
					"Investigations" => "Investigations",
					"Medical" => "Medical",
					"Other" => "Other",
					);
		$con = Connect::con();
		$q = "select * from LangList";
        $res = mysqli_query($con, $q) or die("An Error Occurred");
        $langs_from = array();
        $config = array();
        $langs_to = array();
        if ($res and mysqli_num_rows($res) > 0) {
            for ($k = 0; $k < mysqli_num_rows($res); $k++) {
                $row = mysqli_fetch_assoc($res);
                // $langs_from[] = ;

                $langs_from[trim($row['LangName'])] = trim($row['LangName']);


            }
        }

		$schedulingType = array('get_call' => 'Get Interpreters Call',
				'conference_call' => 'Conference Call');


			// Encrypt fomrat json response
			$result = array_merge(array('timezones_top' => $timezones_array_top),
				array('timezones' => $timezones_array),
				array('langTo' => $langs_from),
				array('langFrom' => $langs_from),
				array('countries' => $countries),
				array('schedulingType' => $schedulingType),
				array('neededFor' => $neededFor)
				);
			// Encrypt the data
	     	return $response->json(array('data' => $result));
	    } else {
	    	return $response->json("No token provided. TODO. Encrypt this");
	    }
	}

	/**
     * @ApiDescription(section="SchedulePartOne", description="Retrieve the first part of the payment after user selects end time.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/schedulePartOne")
     * @ApiBody(sample="{ 'data': 'AwE263pKXPZL87/0KgQvzdtyzKhzyS77SKEH8+dD3zmbF7/jYo0rmM31JmqKm1JPhze22UzITS6jScO8CI/oW6f22zK5E75CdXGPIWQ8WifVTuDaww+rpgb9yg4pJ5CaRnZe5w4z9KVsqa+5rpnhcTgo3XRV/vuFWoRZeVwYZM0LamBgyHIm3y1gP6IhMb3t/y4tfydEm/ar9auKlrK+WzPecbS0OHpbCdC2B2Njn5j0iw==',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': 'AwFwlYcP0ZNBJb0tUins+7EoIcFFKEGpYHMnsV95lB84Lj4EAkPQRunwvNvzBrZzSZ+Gi8dSb4ekV/HCpqMevPSyXX1XfzUgo/vB5/luOVfY4DPUJwz83mCusI4uNxP9Y7+zC0JEgmAU3c59AZnXzM+MMLB+5cHgp6ZdTqSPso+dnGRmutrMiBp1UKBiYK0eU0xw5y4euMY19rvZPuEUAozNeshcIJ2TVjImGEHG0DuEP4vlJOQJ9nm+d98glpQDZoE='}")
     */
	public function schedulePartOne($request, $response, $service, $app){
		if($request->token){
			// $type = 'conference_call'; // conference_call or get_call
			// $fromDate = "2016-06-07";
			// $timeStarts = "2016-06-07 12:50:00 AM";
			// $timeEnds = "2016-06-07 12:55:00 PM";
			// $timezone = "Pacific Time (UTC -8:00)";

			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// $service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: timeStarts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			//Validate the jwt token in the database
			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30; // $30
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10; // 10 minutes
			    $rate_per_min=3; // $3
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;  // 30 minutes
			    $rate_per_min=1.75; // $1.75
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			// $details['daily'][] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes$minimum_text::$$amount";

			// if ($type == 'conference_call') {
			//     $amount+= $conference_fee;
			//     $details['daily'][] = "Conference Calling Fee:: $$conference_fee";
			// }
			$rArray = array();
			$rArray['totalPrice'] = $this->amt_format($amount);
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			if($minimum_text){
				$rArray['minimumText'] = $minimum_text ;
			} else {
				$rArray['minimum_text'] = null;
			}
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
			// Return response json
			return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
	    }
	}

	/**
     * @ApiDescription(section="SchedulePartTwo", description="Retrieve the second part of the payment after user selects or diselects scheduling type.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/schedulePartTwo")
     * @ApiBody(sample="{ 'data': 'AwHczgm1k6rprkY4mM5G4FuNE6uiVAXOh3xuljdu1qz5keZV1o/DfT+OaxQ1R6kz5XGpqj9NM4ESDHcMNT1RsWegVpQMwJhELSbTQBjZLPz0ZlF1aghV8Au6uzssiBN/C9fgulUvkvaQoFx83cvn9w74LAz/eaeqajJvosLv7pb6U9fapQ/HyBDchQpbM1V47F25pYQ2llx2qJDL6ES5sPopPxRu5evI8AlPm4NxiwwjWVABsQYZFRN+KG/FkuosxG8eBdyoxMbnnY/XuwbWLvmMW52k7jBMEaGD9wwJLDxbEg==',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': 'AwF47Ao19bvyqfmkLviIOTYKLJsHt2SAj14XEbJ/KmxrNUa0diKjvncePWQ1LrTFvjOHWFNKFW2Rhe1Lm60UaeU5fdZ7rfMEIZ4XAAzaPG593bymYaLEIkWnrscNZ4ExLV/uQkxrZBLCs3+xhFG8m0TrJbBEPVGqlT8nXcbwAWDu0iUfGcZfiqb9Gz4SN3xu3rLK6GJWgQIdOTNI8b23Na0AVoSCTvZTPox+GdWMdHKclZy6IDMkEdwYGgjckBoLtnESW7JXD703/yMyqiQoTdnAnsIXCF7mxtkUAW8a3kfGWlxprFsY5HcvPHgrOZyP4MrcQoguAKUv+rltj6Zt6i6tuz4BYs5s/nSpAByGPhAwVWvvghqt1B/UACZWcK7G3oQ='}")
     */
	public function schedulePartTwo($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			// $service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: timeStarts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: from date not present.')->notNull();

			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}
			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30; // $30
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10; // 10 minutes
			    $rate_per_min=3; // $3
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;  // 30 minutes
			    $rate_per_min=1.75; // $1.75
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			$rArray = array();
			$rArray['daily'] = "ATS - $scheduling_type Telephonic Scheduling ($$rate_per_min/Min) for $actual_minutes minutes";
			if($minimum_text){
				$rArray['minimumText'] = $minimum_text ;
			} else {
				$rArray['minimum_text'] = null;
			}

			if ($data['schedulingType'] == 'conference_call') {
			    $amount+= $conference_fee;
			    $rArray['conferencePresent'] = "Conference Calling Fee:: $$conference_fee";
			} else{
				$rArray['conferencePresent'] = null;
			}
			$rArray['totalPrice'] = $this->amt_format($amount);
			$base64Encrypted = $this->encryptValues(json_encode($rArray));
			// Return response json
			return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
     		return $response->json(array('data' => $base64Encrypted));
	    }
	}

	/**
     * @ApiDescription(section="ScheduleFinal", description="Send everything in form. Store in database, schedule new conference, or call.")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/testgauss/scheduleFinal")
     * @ApiBody(sample="{ 'data': 'AwHwABArb8N5y0oZEHQSqVra9u35WbD3ZiphKB5EkiZpDyUXtFoP/qGZy/qCQ18CSsXawb5Ey7ZaXddxtZExK6dYc+I0FSXysJ16MsGSl0qeRQPFwovstAY0srXddCbKb2IfzfXITcSonEzX/AUiutG5sQlqfPUrnTqpKMqZ5Kv6fu9B3aSQYXj6rpGbueN0vnxG4vk3fegKtQHVjNLy8Y4khPt3inVxdcWCcN5gUdzQqB3N+NuBewzzfG8JBp92jneWjutJyTVSTCcgzImWa2xAYGUGhgyZWwpBezLeRv+Q2/UB891hSnY06kw0Ezq4GUBItqnFmsrubOxVufciaIS1yJQ1GnOrfzM5Ts0xTuKgE0Ax0ayE+QcvRLx6ru6acDCa4YCyDA3YuKEITbzaFnvO0rrTTfc42oI72ERIBfDwmchNJ27JrUK4160IgURimNkycRPFfgDghFJKMadDqkG+FGz/NxrhuXrtUELvbZH9c6yfYnth+XbOCiACSK8wcMwraIWpZdVShpCffPJw6y/7DmvMzia7OdA99fDHZnh90Qks/bU5CCYY1uYUI85IL0eJsbXa0YnE4q3e9zhz5i3t',
     'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjUzODMxNjUsImp0aSI6IlJpRW16NzRHSGhGR043QzEzT1JpQ1FuWXRnOHJ4bk9YVHRRZ002NnBDN1E9IiwiaXNzIjoiYWxsaWFudHJhbnNsYXRlLmNvbSIsIm5iZiI6MTQ2NTM4MzE2NSwiZXhwIjoxNDY2NTkyNzY1LCJkYXRhIjp7IlN1Y2Nlc3MiOiJTdWNjZXNzIn19.DvPdwcIGybU3zs5NH4NRmldNbhrer8AgvSSwi9lBY6SwJ-WKegETMRQmXZvtLu5-qrAx5hwBkEKXqG80zTqByw'}")
     * @ApiParams(name="data", type="object", nullable=false, description="CustomerId.")
     * @ApiParams(name="token", type="object", nullable=false, description="Autentication token for users autentication.")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{'data': 'AwEWTkHxsaqqx0l3xnIpeu+2F4HNrEFBqflCJww0B2cgOr76pcJ69gA3tyF/zrHL+9ApX2uihQ7ZU+icBAX/6IxyuMuhPCVNIAhskrwy8IaGDW0UYvuxbl1dyN0gF6YPRD+KQf+UHdwG9MNjY8oqjqu2+H3Y7Evp4c3KJWXCYKsaidT+2cmW82IssJHzYEyIL6PdqpxEqow4fK6n0Y2yaSsI6AZ5Wicoi1pqG+Fe90LwKdMF4meARSwhJJhfbg7pATK/YbPS2tQPwUYkKR4X23xC5eXAYoaCMp2XV+OgpFQlRA4nF4MMfGFh29sRIK8OnMg='}")
     */
	public function scheduleFinal($request, $response, $service, $app){
		if($request->token){
			// Validate token if not expired, or tampered with
			$this->validateToken($request->token);
			// Decrypt input data
			$data = $this->decryptValues($request->data);
			// Validate input data
			$service->validate($data['CustomerID'], 'Error: No customer id is present.')->notNull()->isInt();
			$service->validate($data['timezone'], 'Error: timezone not present.')->notNull();
			$service->validate($data['fromDate'], 'Error: from date not present.')->notNull();
			$service->validate($data['timeStarts'], 'Error: time starts not present.')->notNull();
			$service->validate($data['timeEnds'], 'Error: timeEnds not present.')->notNull();
			$service->validate($data['langFrom'], 'Error: lang from not present.')->notNull();
			$service->validate($data['langTo'], 'Error: lang to present.')->notNull();
			$service->validate($data['country'], 'Error: country not present.')->notNull();
			$service->validate($data['schedulingType'], 'Error: schedulingType not present.')->notNull();
			$service->validate($data['clients'], 'Error: clients not present.')->notNull();
			$service->validate($data['neededFor'], 'Error: neededFor not present.')->notNull();
			$service->validate($data['description'], 'Error: description not present.')->notNull();

			$validated = $this->validateTokenInDatabase($request->token, $data['CustomerID']);
			// If error validating token in database
			if(!$validated){
				$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("Authentication problems present")));
	     		return $response->json(array('data' => $base64Encrypted));
			}

			$frm_time = $data['fromDate'] . ' ' . $data['timeStarts'];
			$to_time = $data['fromDate'] . ' ' . $data['timeEnds'];
			$assg_frm_st = $data['timeStarts'];
			$assg_frm_en = $data['timeEnds'];

			$frmT = new \DateTime($data['fromDate'].' '.$data['timeStarts'],new \DateTimeZone($data['timezone']));
			$frmT->setTimezone(new \DateTimeZone('GMT'));

		    $toT = new \DateTime($data['fromDate'].' '.$data['timeEnds'],new \DateTimeZone($data['timezone']));
			$toT->setTimezone(new \DateTimeZone('GMT'));

			$frm_lang = $this->get_language_by_name($data['langFrom']);
			$to_lang = $this->get_language_by_name($data['langTo']);

			$details = array();
			$amount = 0;
			$timing = $this->get_assignment_time($frm_time,$to_time);

			$hours_left = $timing["hours_to_start"];
			$minimum_rate = 30; // $30
			$conference_fee = $this->amt_format(5);
			if($hours_left<24) {
			    $minimum_minutes = 10; // 10 minutes
			    $rate_per_min=3; // $3
			    $scheduling_type="Short Notice";
			}else{
			    $minimum_minutes = 15;  // 30 minutes
			    $rate_per_min=1.75; // $1.75
			    $scheduling_type="Regular";
			}
	 		$minutes = $this->telephonic_duration($frm_time, $to_time);
			$actual_minutes = $minutes;
			$minimum_appied = ($minutes <= $minimum_minutes) ? true : false;
			$minutes = ($minimum_appied) ? $minimum_minutes : $minutes;

			$minimum_text = ($minimum_appied) ? "Minimum $scheduling_type telephonic scheduling price is $$minimum_rate" : "";
			$amount += ($minimum_appied) ? $minimum_rate : $rate_per_min * $minutes;
			$amount = $this->amt_format($amount);

			if ($data['schedulingType'] == 'conference_call') {
			    $amount+= $conference_fee;

			} elseif($data['schedulingType'] == 'get_call'){

			}
			$sArray = array();
			$sArray['customer_id'] = $data['CustomerID'];
			$sArray['assg_frm_date'] = $data['fromDate'];
			$sArray['assg_frm_st'] = date("H:i:s",strtotime($assg_frm_st));
			$sArray['assg_frm_en'] = date("H:i:s",strtotime($assg_frm_en));
			$sArray['assg_to_date'] = $data['fromDate'];
			$sArray['timezone'] = $data['timezone'];
			$sArray['assg_frm_timestamp'] =$frmT->format('U');
			$sArray['assg_to_timestamp'] =$toT->format('U');
			// $sArray['interpreting_dur'] =$NESTO['interpreting_duration'];
			$sArray['scheduling_type'] = $data['schedulingType'];
			$sArray['frm_lang'] = $frm_lang; // broj languagea
			$sArray['to_lang'] = $to_lang; // broj Languagea
			$sArray['country'] = $data['country'];
			if ($data['schedulingType'] == 'conference_call') {
				$sArray['onsite_con_phone'] = $data['contacts'];
			} elseif($data['schedulingType'] == 'get_call'){
				$data['onsite_con_email'] = $data['contacts']; //TODO
			}
			// return $data['contacts'];
			$sArray['intr_needed_for'] = $data['neededFor'];
			$sArray['description'] = $data['description'];
			$sArray['orderID'] = md5(time().$data['description']);
			$sArray['currency_code']='usd';
			$sArray['scheduling_type'] = $data['schedulingType'];
			$all_data_valid = true;
			// if(intval($data['amount'])<=29){ // Amount check if less than 29$ error
			// 	$all_data_valid=false;
			// }
			$sArray['amount'] = $amount; // caluculate_price() TODO
			if($all_data_valid){
				if($data['schedulingType'] == 'get_call'){
					$sArray['interpreter_amt'] = (25/100)*$amount;  // caluculate_price() TODO
				} elseif($data['schedulingType'] = 'conference_call'){
					$sArray['interpreter_amt'] = (25/100)*($amount-5); // caluculate_price() TODO
				}
			}

			$sArray['interpreting_dur']= $this->telephonic_duration(
										$data['fromDate'].'T'.$sArray['assg_frm_st'],
										$data['fromDate'].'T'.$sArray['assg_frm_en']);


			//Mysql insert into order_onsite_interpreterž
			// return $response->json($sArray);
			// $con=mysqli_connect("localhost","root","","allian10_abs_linguist_portal"); // TODO for server
			$con = Connect::con(); // TODO PUT INTO model OrderOnsiteInterpreter
			foreach($sArray as $key=>$value){
				$in[$key] = mysqli_real_escape_string($con,$value);
			}
			$fields = implode(',', array_keys($in));
			$values = implode("', '", array_values($in));
			$query = sprintf("insert into order_onsite_interpreter(%s) values('%s')",$fields,$values);
			// return $query;

			$result = mysqli_query($con,$query);
			if(!$result and mysqli_affected_rows($con)>0){
				if(mysqli_errno($con)==1048){
					$feedback=json_encode("Error--Missing Required Values");
				}else {
					$feedback=json_encode("Error--Failed to Save Data");//.mysqli_error($con);
				}

			}

			// TU


			$retArray = array();
			$retArray['timezone'] = $data['timezone'];
			$retArray['status'] = 1;
			$retArray['confStarts'] = $data['fromDate'] . ' ' . $data['timeStarts'];
			$retArray['confEnds'] = $data['fromDate'] . ' ' . $data['timeEnds'];
			$retArray['confCode'] = "12345";
			$retArray['confDialNumber'] = "+18555129043"; //TODO

			$base64Encrypted = $this->encryptValues(json_encode($retArray));
	 		return $response->json(array('data' => $base64Encrypted));
		} else {
	    	$base64Encrypted = $this->encryptValues(json_encode($this->errorJson("No token provided in request")));
	 		return $response->json(array('data' => $base64Encrypted));
    	}

	}

	/**
	 *
	 * Block comment
	 *
	 */
	function get_assignment_time($actual_starting_time, $actual_ending_time = "") {
	    $time_difference = strtotime($actual_starting_time) - time();
	    $duration = strtotime($actual_ending_time) - strtotime($actual_starting_time);
	    $hours = floor($time_difference / 3600);
	    $hours = ($hours < 10) ? "0" . $hours : $hours;
	    $minutes = floor(($time_difference / 60) % 60);
	    $minutes = ($minutes < 10) ? "0" . $minutes : $minutes;
	    $seconds = $time_difference % 60;
	    $seconds = ($seconds < 10) ? "0" . $seconds : $seconds;
	    $time_left = "$hours:$minutes:$seconds";
	    // True if start time is less than 24
	    // False if start time is greater than 24 hours
	    // False if start time is less than an Hour (no need to send 24hr prior notification when only 1 hour is left)
	    $notify = (86400 > $time_difference && $time_difference > 3600); // 24 hours left
	    $notify_checkout = ($actual_ending_time == "") ? false : time() > strtotime($actual_ending_time);
	    return array("notify_24_hours" => $notify, "notify_checkout" => $notify_checkout, "time_to_start" => $time_left, "hours_to_start" => $hours, "minutes_to_start" => $minutes, "time" => $time_difference, "duration" => $duration);
	}

	/**
	 *
	 * Block comment
	 *
	 */
	function amt_format($amt, $decimels = "2", $decimel_point = ".", $thousand_sep = "") {
		return number_format($amt, $decimels, $decimel_point, $thousand_sep);
	}

	function telephonic_duration($frm_time, $to_time) {

	    $start_date = new \DateTime($frm_time);
	    $since_start = $start_date->diff(new \DateTime($to_time));

	    $days = $since_start->d + 1;
	    $minutes += $since_start->h * 60;
	    $minutes += $since_start->i;
	    //echo "<br>".$minutes."<br>";
	    return $minutes * $days;
	}

	function get_language_by_name($langName, $get = 'LangId') {
		// $con=mysqli_connect("localhost","root","","allian10_abs_linguist_portal"); // TODO for server
		$con = Connect::con();
		$query = "SELECT $get FROM `LangList` where LangName LIKE  '%".trim($langName)."%'";
		$get_lang_info = mysqli_query($con, $query);
		$lang = mysqli_fetch_array($get_lang_info);
		$get = $lang[$get];
		return $get;
	}



}