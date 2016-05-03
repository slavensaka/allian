<?php
require 'vendor/autoload.php';
use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

$classes = array(
    'Allian\Http\Controllers\CustLoginController',
    // 'Some\Namespace\OtherClass',
);

$output_dir  = __DIR__ . '/apidocs';
$output_file = 'index.html'; // defaults to index.html

try {
    $builder = new Builder($classes, $output_dir, 'Api Title', $output_file);
    $builder->generate();
} catch (Exception $e) {
    echo 'There was an error generating the documentation: ', $e->getMessage();
}