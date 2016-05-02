<?php
require 'vendor/autoload.php';
require_once '/app/Models/CustLogin.class.php';
// require_once("database/DataObject.class.php");

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// $CustomerID = isset( $_GET["CustomerID"] ) ? (int)$_GET["CustomerID"] : 0;
$CustomerID = 304;
if ( !$customer = CustLogin::getCustLogin($CustomerID)) {
  echo 'Error: Customer not found.';
  exit;
}
header('Content-type: application/json');
echo json_encode($customer);

