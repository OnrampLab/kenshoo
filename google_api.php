<?php
#!/usr/bin/php -q

if (PHP_SAPI !== 'cli') {
    if ( '192.168.'       !== substr($_SERVER['REMOTE_ADDR'],0,8) &&
         '203.75.167.229' !== $_SERVER['REMOTE_ADDR'] )
    {
        exit;
    }
}

error_reporting(E_ALL);
ini_set('html_errors','On');
ini_set('display_errors','On');

require_once 'config.php';
date_default_timezone_set(APPLICATION_TIMEZONE);

require_once 'vendor/autoload.php';
require_once 'library/Log.php';
require_once 'library/CsvManager.php';
require_once 'library/ArrayIndex.php';
require_once 'library/Fb.php';
require_once 'library/GoogleApiHelper.php';
require_once 'library/GoogleWorksheetManager.php';
require_once 'helper/GoogleSheetdownloadHelper.php';
require_once 'helper/MailHelper.php';
require_once 'uploadHelper.php';
require_once 'downloadHelper.php';

echo '<pre>';
perform();
exit;

/**
 * 
 */
function perform()
{
    Log::record(date("Y-m-d H:i:s", time()) . ' - start PHP '. phpversion() );

    //
    upgradeGoogleSheet();

    //    
    $dateFormat = date('Y-m-d',time());
    $dateFormat = date('Y-m-d', strtotime($dateFormat . ' - 1 day'));
    $uploadPath = APPLICATION_DIR . '/tmp/csv_upload';
    $uploadFile = "SimplyBridal-UC_File-{$dateFormat}.csv";

    // create
    makeCsvFile( $uploadPath .'/'. $uploadFile );

    // upload
    uploadCsvFile( $uploadPath .'/'. $uploadFile );

    Log::record(date("Y-m-d H:i:s", time()) . ' - Done');
    echo "done\n";
}

/**
 *  create csv file
 */
function makeCsvFile( $pathFile )
{
    GoogleSheetdownloadHelper::download( $pathFile, APPLICATION_GOOGLE_KENSHOO_KEY, APPLICATION_GOOGLE_KENSHOO_GID );
}

/**
 *  upload csv file
 */
function uploadCsvFile( $pathFile )
{
    $upload = new Upload();
    if ( !file_exists($pathFile) ) {
        Log::record(date("Y-m-d H:i:s", time()) . ' - Get file error');
        MailHelper::sendFail();
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

/**
 *
 */
function upgradeGoogleSheet()
{
    $token = GoogleApiHelper::getToken();
    if ( !$token ) {
        die('token error!');
    }

    $worksheet = GoogleApiHelper::getWorksheet( APPLICATION_GOOGLE_SPREADSHEETS_BOOK, APPLICATION_GOOGLE_SPREADSHEETS_SHEET, $token );
    if ( !$worksheet ) {
        die('worksheet not found!');
    }

    $sheet = new GoogleWorksheetManager($worksheet);
    $header = $sheet->getHeader();
    $count = $sheet->getCount();
    for ( $i=0; $i<$count; $i++ ) {
        $row = $sheet->getRow($i);

        // Override = 1 or 0
        if ( !$row['override'] ) {
            // 為 0 時不覆蓋任何值
            continue;
        }
    
        $row = updateDate($row);
        $row = updateByFacebook($row, $header);
        $sheet->setRow($i, $row);
    }
}

/**
 *
 */
function updateDate( $row )
{
    $row['date'] = date("n/j/Y", time());
    $row['date'] = date('n/j/Y', strtotime($row['date'] . ' - 1 day'));
    return $row;
}

/**
 *
 */
function updateByFacebook( $row, $header )
{
    $facebookData = getFacebookData();

    // create new cvs contents
    ArrayIndex::set($facebookData['cost']);
    $contents = array();

    CsvManager::setHeader($header);
    CsvManager::setFilter(array(
        'cost' => 'float',
        'fb-objective' => 'int'
    ));

    $index = ArrayIndex::getIndex('group_name', $row['campaign']);
    if ( null !== $index ) {
        $row['cost']        = ArrayIndex::get($index, 'spend');
        $row['impressions'] = ArrayIndex::get($index, 'reach');
        $row['clicks']      = ArrayIndex::get($index, 'clicks');
    }

    return $row;

}

/**
 *  cache facebook data
 */
function getFacebookData()
{
    static $result;
    if ( $result ) {
        return $result;
    }

    $fb = new Fb(APPLICATION_FACEBOOK_ID, APPLICATION_FACEBOOK_SECRET);
    $result = $fb->get();
    return $result;
}
