<?php
define('USE_AUTHENTICATION', 1);
define('USERNAME', 'wincache');
define('PASSWORD', 'wincache');

if ( USE_AUTHENTICATION == 1 ) {
    if ( !isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW'] ) ||
    $_SERVER['PHP_AUTH_USER'] != USERNAME || $_SERVER['PHP_AUTH_PW'] != PASSWORD ) {
        header( 'WWW-Authenticate: Basic realm="NO auth!"' );
        header( 'HTTP/1.0 401 Unauthorized' );
        exit;
    }
    else
    {
        // Just return welcome
    }
}