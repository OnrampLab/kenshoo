<?php

function initialize($basePath)
{
    error_reporting(E_ALL);
    ini_set('html_errors','Off');
    ini_set('display_errors','On');

    require_once  $basePath . '/config.php';
    date_default_timezone_set(APPLICATION_TIMEZONE);

    require_once $basePath . '/vendor/autoload.php';

    require_once $basePath . '/app/library/Log.php';
    Log::init(   $basePath . '/tmp');

    require_once $basePath . '/app/library/GoogleWorksheetManager.php';
    require_once $basePath . '/app/library/CsvManager.php';
    require_once $basePath . '/app/library/CsvReadManager.php';
    require_once $basePath . '/app/library/ArrayIndex.php';
    require_once $basePath . '/app/library/GoogleApiHelper.php';

    require_once $basePath . '/app/helper/DownloadHelper.php';
    require_once $basePath . '/app/helper/FacebookHelper.php';
    require_once $basePath . '/app/helper/TollfreeforwardingHelper.php';
    require_once $basePath . '/app/helper/PinterestHelper.php';
    require_once $basePath . '/app/helper/MailHelper.php';
    require_once $basePath . '/app/helper/uploadHelper.php';

    require_once $basePath . '/queue/QueueBrg.php';
    require_once $basePath . '/queue/QueueBrgGearmanClient.php';
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

    // write to log
    if (!$writeLog) {
        return;
    }

    if (is_object($data) || is_array($data)) {
        Log::record(
            print_r($data, true)
        );
    }
    else {
        Log::record($data);
    }
}

/**
 *  get command line value
 *
 *  @return string|int or null
 */
function getParam($key)
{
    global $argv;
    $allParams = $argv;
    array_shift($allParams);

    if (in_array($key, $allParams)) {
        return true;
    }

    foreach ($allParams as $param) {

        $tmp = explode('=', $param);
        $name = $tmp[0];
        array_shift($tmp);
        $value = join('=', $tmp);

        if ($name===$key) {
            return $value;
        }
    }

    return null;
}

function isCli()
{
    return PHP_SAPI === 'cli';
}

/**
 *  Clean invisible control characters and unused code points
 *
 *  \p{C} or \p{Other}: invisible control characters and unused code points.
 *      \p{Cc} or \p{Control}: an ASCII 0x00–0x1F or Latin-1 0x80–0x9F control character.
 *      \p{Cf} or \p{Format}: invisible formatting indicator.
 *      \p{Co} or \p{Private_Use}: any code point reserved for private use.
 *      \p{Cs} or \p{Surrogate}: one half of a surrogate pair in UTF-16 encoding.
 *      \p{Cn} or \p{Unassigned}: any code point to which no character has been assigned.
 *
 *  該程式可以清除 RIGHT-TO-LEFT MARK (200F)
 *
 *  @see http://www.regular-expressions.info/unicode.html
 *
 */
function filterUnusedCode( $row )
{
    foreach ( $row as $index => $value ) {
        $row[$index] = preg_replace('/\p{C}+/u', "", $value );
    }
    return $row;
}

/**
 *  create csv content to file
 */
function writeCsvFile($pathFile, $content)
{
    DownloadHelper::contentToCsv($content, $pathFile);
}

/**
 *  create csv file
 *
 *      在 2016-05-15 當天發現, 如果在沒有登入 google account 的情況下
 *      即使是公開的 spreadsheets, 也無法檢視
 *      所以目前已停止該功能
 */
/*
function makeCsvFile($pathFile, $gid=0)
{
    $key = APPLICATION_GOOGLE_KENSHOO_KEY;
    $url = 'docs.google.com/feeds/download/spreadsheets/Export?key=' . $key . '&exportFormat=csv';
    if ($gid) {
        $url .= "&gid=" . $gid;
    }
    $content = DownloadHelper::getByUrl($url);
    DownloadHelper::contentToCsv($content, $pathFile);
}
*/

/**
 *  upload csv file
 */
function uploadCsvFile( $pathFile )
{
    $upload = new Upload();
    if ( !file_exists($pathFile) ) {
        Log::record(date("Y-m-d H:i:s", time()) . ' - Get file error');
        MailHelper::sendFail();
        show("get file error");
        exit;
    }

    $result = $upload->ftpUpload($pathFile);
    if($result){
        Log::record(date("Y-m-d H:i:s", time()) . ' - FTP success');
        MailHelper::sendSuccess();
    }
    else {
        Log::record(date("Y-m-d H:i:s", time()) . ' - FTP error');
        MailHelper::sendFail();
    }
}

