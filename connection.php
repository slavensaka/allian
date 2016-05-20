<?php
//DEALING WITH UNIX TIMESTAMPS
// $timestamp=1381920863;
// echo gmdate("Y-m-d\TH:i:s\Z", $timestamp);
// echo date('d M Y H:i:s Z',$timestamp);
// echo date('c',$timestamp);
// echo strtotime('2013-10-16T10:54:23Z');

// CONSTANTS
// echo __DIR__; C:\xampp\htdocs\testgauss
// echo dirname(__FILE__);   C:\xampp\htdocs\testgauss
// define('ROOT_DIR', __DIR__);
// define('ROOT_PATH', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
// echo substr($_SERVER['REQUEST_URI']);
// echo $_SERVER['DOCUMENT_ROOT'];

# Fill our vars and run on cli
# $ php -f db-connect-test.php

// PRODUCTION SERVER
// $dbname = 'allian10_abs_linguist_portal';
// $dbuser = 'allian10_alenb';
// $dbpass = 'allian2016@';
// $dbhost = 'vps9239.inmotionhosting.com';

// STAGING SERVER
// $dbname = 'alliantr_testgauss';
// $dbuser = 'alliantr_gauss';
// $dbpass = '124L3lSFlM5Ngyk9';
// $dbhost = 'vps9239.inmotionhosting.com';

// DEVELOPMENT SERVER
$dbname="allian10_abs_linguist_portal";
$dbuser="root";
$dbpass="";
$dbhost="localhost";

// CONNECTION
$con = mysqli_connect("localhost", "root", "", "allian10_abs_linguist_portal");
$langID = 1;
$query ="SELECT DISTINCT langpair_trans.Lang2 AS lang2, langlist.LangName AS langName FROM langpair_trans LEFT JOIN langlist ON langpair_trans.Lang2 = langlist.LangId WHERE langpair_trans.Lang1 = '$langID' AND Approved = 1 AND Lang2 IS NOT NULL AND Lang2 <> 'N/A' ORDER BY langlist.LangName";
while($row = mysqli_fetch_array($con,$query)){
	var_dump($result["langName"]);
}