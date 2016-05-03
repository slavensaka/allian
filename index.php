<?php
require 'vendor/autoload.php';
require_once '/app/Models/CustLogin.class.php';
// require_once("database/DataObject.class.php");

use Allian\Http\Controllers\CallIdentifyController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
echo $novi = CallIdentifyController::testing();
// $CustomerID = isset( $_GET["CustomerID"] ) ? (int)$_GET["CustomerID"] : 0;

// $CustomerID = 304;
// if ( !$customer = CustLogin::getCustLogin($CustomerID)) {
//   echo 'Error: Customer not found.';
//   exit;
// }
// header('Content-type: application/json');
// echo json_encode($customer);

// define('ROOT_DIR', __DIR__);
// define('ROOT_PATH', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
// echo substr($_SERVER['REQUEST_URI']);
// echo $_SERVER['DOCUMENT_ROOT'];

$klein = new \Klein\Klein();

$klein->respond('GET', '/hello', function () {
    echo 'Hello World!';
});

$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body(
                'Y U so lost?!'
            );
            break;
        case 405:
            $router->response()->body(
                'You can\'t do that!'
            );
            break;
        default:
            $router->response()->body(
                'Oh no, a bad error happened that caused a '. $code
            );
    }
});

// $klein->respond('GET', '/posts', );
$klein->dispatch();