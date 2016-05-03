<?php
require 'vendor/autoload.php';

use Allian\Http\Controllers\CustLoginController;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// echo DataObject::lorem();


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
                'Y U so lost?! Page not Found, 404!'
            );
            break;
        case 405:
            $router->response()->body(
                'You can\'t do that! Method not allowed!'
            );
            break;
        default:
            $router->response()->body(
                'Oh no, a bad error happened that caused a '. $code
            );
    }
});
// if(is_callable(array($custLogin, 'testing'))) echo "JE"; else echo "Nije";
$custLogin = new CustLoginController();
$klein->respond('GET', '/login', array($custLogin, 'testing'));
$klein->respond('POST', '/login/[i:id]', array($custLogin, 'postTesting'));

$klein->dispatch();