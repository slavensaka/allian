<?php
// echo $customer->getValueEncoded( "Street" );

//Dealing with unix timestamps
// $timestamp=1381920863;
// echo gmdate("Y-m-d\TH:i:s\Z", $timestamp);
// echo date('d M Y H:i:s Z',$timestamp);
// echo date('c',$timestamp);
// echo strtotime('2013-10-16T10:54:23Z');

// echo __DIR__; C:\xampp\htdocs\testgauss
// echo dirname(__FILE__);   C:\xampp\htdocs\testgauss
// define('ROOT_DIR', __DIR__);
// define('ROOT_PATH', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
// echo substr($_SERVER['REQUEST_URI']);
// echo $_SERVER['DOCUMENT_ROOT'];

// Connection failed: SQLSTATE[HY000] [1130] Host '212.92.200.253' is not allowed to connect to this MySQL server
// $dsn = "mysql:host=localhost;dbname=allian10_abs_linguist_portal";
// $username = "allian10_alenb";
// $password = "allian2016@";
// $username = "root";
// $password = "";
// try {
// 	$conn = new PDO( $dsn, $username, $password );
// 	$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
// 	// $conn = null; //Always close, Do not forget
// 	// var_dump( $conn);
// 	// $sql = "SELECT * FROM callidentify";
// 	$sql = "SHOW COLUMNS FROM CustLogin";
// 	 $rows = $conn->query( $sql );
// 	foreach ( $rows as $row ) {
// 		echo $row;
// 		// echo "starttime = " . $row["starttime"] . "<br/>";
// 	}
// } catch ( PDOException $e ) {
// echo "Connection failed: " . $e->getMessage();
// }

# Fill our vars and run on cli
# $ php -f db-connect-test.php


	// $dbname = 'allian10_abs_linguist_portal';
	// $dbuser = 'allian10_alenb';
	// $dbpass = 'allian2016@';
	// $dbhost = 'vps9239.inmotionhosting.com';

// $dbname = 'alliantr_testgauss';
// $dbuser = 'alliantr_gauss';
// $dbpass = '124L3lSFlM5Ngyk9';
// $dbhost = 'vps9239.inmotionhosting.com';

$dbname="allian10_abs_linguist_portal";
$dbuser="root";
$dbpass="";
$dbhost="localhost";

// $dsn = 'mysql:host=vps9239.inmotionhosting.com;dbname=alliantr_testgauss';
// $username = 'alliantr_gauss';
// $password = '124L3lSFlM5Ngyk9';
// $options = array(
//     PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
// );

// $dbh = new PDO($dsn, $username, $password, $options);
$name = 'Spanish';
$con = mysqli_connect("localhost", "root", "", "allian10_abs_linguist_portal");
// $query = "SELECT LangId FROM langlist WHERE LangName='$name'";


$langID = 1;
$query ="SELECT DISTINCT langpair_trans.Lang2 AS lang2, langlist.LangName AS langName FROM langpair_trans LEFT JOIN langlist ON langpair_trans.Lang2 = langlist.LangId WHERE langpair_trans.Lang1 = '$langID' AND Approved = 1 AND Lang2 IS NOT NULL AND Lang2 <> 'N/A' ORDER BY langlist.LangName";




	while($row = mysqli_fetch_array($con,$query))

			{

				var_dump($result["langName"]);

			}



	// $connect = mysql_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");
	// mysql_select_db($dbname) or die("Could not open the db '$dbname'");
	// $test_query = "SHOW TABLES FROM $dbname";
	// $test_query = "SELECT card_number FROM payment_details";
	// $test_query = "SELECT LoginPassword FROM CustLogin";


// 	$card_detail_query = "SELECT * from CustLogin WHERE CustomerID= '$CustomerID' and LoginPassword= '$Password' ";

// $card_detail=mysql_query($card_detail_query);
// 	$card=mysqli_fetch_assoc($card_detail);
// print_r($card);


	// $result = mysql_query($test_query);
// while($table = mysql_fetch_array($result)) { // go through each row that was returned in $result
//     echo($table[0] . "<BR>");    // print the table that was returned on that row.
// }
	// $tblCnt = 0;
	// while($tbl = mysql_fetch_array($result)) {
	//   	$tblCnt++;
 //  		echo $tbl[0]."<br />\n";
	// }
	// if (!$tblCnt) {
	//   echo "There are no tables<br />\n";
	// } else {
	//   echo "There are $tblCnt tables<br />\n";
	//   echo "Success";
	// }

//Works
// \Stripe\Stripe::setApiKey('sk_test_AM1QoITYp8ZBFgSkRY4Fruby');
// $myCard = array('number' => '4242424242424242', 'exp_month' => 8, 'exp_year' => 2018);
// $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => 2000, 'currency' => 'usd'));
// echo $charge;