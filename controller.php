<?php
#!/usr/bin/php -q

if (PHP_SAPI !== 'cli') {
    if ( '192.168.'       !== substr($_SERVER['REMOTE_ADDR'],0,8) &&
         '203.75.167.229' !== $_SERVER['REMOTE_ADDR'] )
    {
        exit;
    }
}

// date_default_timezone_set('America/Los_Angeles');
date_default_timezone_set('Europe/London');

error_reporting(E_ALL);
ini_set('html_errors','On');
ini_set('display_errors','On');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 1200);

require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'library/Log.php';
require_once 'library/CsvManager.php';
require_once 'library/ArrayIndex.php';
require_once 'library/Fb.php';
require_once 'helper/MailHelper.php';
require_once 'uploadHelper.php';
require_once 'downloadHelper.php';
cron();

/*
* This is a daily report function
*/
function cron()
{
    MailHelper::sendStart();

    echo date("Y-m-d H:i:s", time()) . '  ';
    Log::record(date("Y-m-d H:i:s", time()) . ' - start PHP '. phpversion() );

    $uploadDir = APPLICATION_UPLOAD_DIR;
    $backupDir = APPLICATION_BACKUP_DIR;
    
    $downloader = new googleDocDownloader();
    $downloader->dowmloadKenshooCsv();
    
    $upload = new Upload();
    $fileName = $upload->getFile($uploadDir);
    if($fileName){
        $uploadFile = $upload->renameFile($fileName, $uploadDir);
        $result = $upload->ftpUpload($uploadFile);
        if($result){
            $upload->backupFile($uploadFile, $backupDir);
            echo 'done.';
            Log::record(date("Y-m-d H:i:s", time()) . ' - don');
            MailHelper::sendSuccess();
        }
        else {
            echo 'FTP error.';
            Log::record(date("Y-m-d H:i:s", time()) . ' - FTP error');
            MailHelper::sendFail();
        }
    } else {
        echo 'Get file error.';
        Log::record(date("Y-m-d H:i:s", time()) . ' - Get file error');
        MailHelper::sendFail();
    }
    echo "\n";
}

function downloadGoogleDoc()
{
    $driveFile = new DriveFile('760681116383-vhn7t6v7vb4463bm02u2vl4r781o4jhs@developer.gserviceaccount.com',
    __DIR__ . '/key/Google Document-b231761a6c36.p12',
    'turtlemt@gmail.com');

    $files = $driveFile->searchFile('kenshoo');
    
    if (isset($files[0])) {
        $file = $files[0];
        echo "file id: " . $file->id . "\n";
        $meta = $driveFile->getMetadata( $file);
        echo "meta data: \n";
        print_r($meta);
    
        if ($content = $driveFile->getSpreadsheetFile($file)) {
            file_put_contents('spreadsheet.csv',$content);
        }
        else {
            echo "An error occurred.";
        }
    
    }
    
    echo "\n";
}
