<?php

require __DIR__ . './vendor/autoload.php';

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

$classes = array(
    'Allian\Http\Controllers\CustLoginController',
    'Allian\Http\Controllers\LangPairController',
    'Allian\Http\Controllers\StripeController',
    'Allian\Http\Controllers\ConferenceScheduleController',
    'Allian\Http\Controllers\TwilioController',
    'Allian\Http\Controllers\LangListController',
    'Allian\Http\Controllers\DeveloperController',
    // 'Some\Namespace\OtherClass',
);

$output_dir  = __DIR__ . '/docs';
$output_file = 'index.html';

try {
    $builder = new Builder($classes, $output_dir, 'AllianTelephonic API', $output_file);
    $builder->generate();
} catch (Exception $e) {
    echo 'There was an error generating the documentation: ', $e->getMessage();
}

// php apidoc.php