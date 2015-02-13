<?php
#!/usr/bin/php -q
/*
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 1200);
*/
require_once 'vendor/autoload.php';
require_once 'library/Facebook.php';

require_once 'uploadHelper.php';
require_once 'downloadHelper.php';
require_once 'config.php';
cron();


/*
* This is a daily report function
*/
function cron()
{
    echo date("Y-m-d H:i:s", time()) . '  ';
    $uploadDir = APPLICATION_UPLOAD_DIR;
    $backupDir = APPLICATION_BACKUP_DIR;
    
    $downloader = new googleDocDownloader();
    $downloader->dowmloadKenshooCsv();
    
    $upload = new Upload();
    $fileName = $upload->getFile($uploadDir);
    if($fileName){
        $uploadFile = $upload->renameFile($fileName, $uploadDir);
        //$result = $upload->ftpUpload($uploadFile);
        $result = false;
        if($result){
            $upload->backupFile($uploadFile, $backupDir);
            echo 'done.';
        }
        else
            echo 'FTP error.';
    } else {
        echo 'Get file error.';
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