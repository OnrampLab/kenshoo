<?php

function initialize($basePath)
{
    error_reporting(E_ALL);
    ini_set('html_errors','Off');
    ini_set('display_errors','On');

    require_once  $basePath . '/config.php';
    date_default_timezone_set(APPLICATION_TIMEZONE);

    require_once $basePath . '/vendor/autoload.php';

    require_once $basePath . '/library/Log.php';
    Log::init(   $basePath . '/tmp');

}

function show($data, $writeLog=false )
{
    if (is_object($data) || is_array($data)) {
        print_r($data);
    }
    else {
        echo $data;
        echo "\n";
    }

    if ($writeLog) {
        Log::record($data);
    }
}

function isCli()
{
    return PHP_SAPI === 'cli';
}
