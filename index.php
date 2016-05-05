<?php
require __DIR__ . '/vendor/autoload.php';

use Allian\Http\Controllers\CustLoginController;
use Allian\Http\Controllers\CallIdentifyController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$klein = new \Klein\Klein();

$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body('Page not Found, ' . $code . '!');
            break;
        case 405:
            $router->response()->body('Method not allowed! ' . $code . '!');
            break;
        default:
            $router->response()->body('General error ' . $code);
    }
});

$callIdentify = new CallIdentifyController();
$klein->respond('GET', '/testgauss/renderdocs', array($callIdentify, 'renderDocs')); // FOR TESTING

$custLogin = new CustLoginController();
// if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
$klein->respond('POST', '/testgauss/login', array($custLogin, 'postLogin'));

$klein->dispatch();

// include('connection.php'); // FOR TESTING