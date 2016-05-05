<?php
require 'vendor/autoload.php';

use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\CallIdentifyController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$klein = new \Klein\Klein();

$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body('Y U so lost?! Page not Found, 404!');
            break;
        case 405:
            $router->response()->body('You can\'t do that! Method not allowed!');
            break;
        default:
            $router->response()->body('Oh no, a bad error happened that caused a '. $code);
    }
});
$callIdentify = new CallIdentifyController();
$klein->respond('GET', '/allian/renderdocs', array($callIdentify, 'renderDocs')); // FOR TESTING

$custLogin = new CustLoginController();
// if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
$klein->respond('POST', 'allian/login', array($custLogin, 'postLogin'));

$klein->dispatch();

// include('connection.php'); // FOR TESTING