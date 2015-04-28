<?php
#!/usr/bin/php -q

/**
 *  利用 google API 直接修改 google spreadsheet
 */
if (PHP_SAPI !== 'cli') {
    if ( '192.168.'       !== substr($_SERVER['REMOTE_ADDR'],0,8) &&
         '203.75.167.229' !== $_SERVER['REMOTE_ADDR'] )
    {
        echo "Deny";
        exit;
    }
    echo '<pre>';
}

error_reporting(E_ALL);
ini_set('html_errors','On');
ini_set('display_errors','On');

//echo ini_get("memory_limit"); exit;
//echo ini_set("memory_limit","2048M");

require_once 'config.php';
date_default_timezone_set(APPLICATION_TIMEZONE);

require_once 'vendor/autoload.php';
require_once 'library/Log.php';
require_once 'library/CsvManager.php';
require_once 'library/CsvReadManager.php';
require_once 'library/ArrayIndex.php';
require_once 'library/Fb.php';
require_once 'library/GoogleApiHelper.php';
require_once 'library/GoogleWorksheetManager.php';
require_once 'helper/GoogleSheetdownloadHelper.php';
require_once 'helper/TollfreeforwardingHelper.php';
require_once 'helper/PinterestHelper.php';
require_once 'helper/MailHelper.php';
require_once 'uploadHelper.php';
require_once 'downloadHelper.php';

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
        echo "get file error\n";
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

        // 無論如何都必須修改的值
        $row = updateDate($row);

        // 在 Override = 1 的情況要下修改的值
        if ( $row['override'] ) {
            $row['impressions'] = 0;
            $row['clicks'] = 0;
            $row['cost'] = 0;
            $row = updateByFacebook($row, $header);
        }
        $row = updateByPinterest( $row );
        $row = updateByTollfreeforwarding( $row );
        $sheet->setRow($i, $row);

        // debug
        echo $i. ' ';
        if (PHP_SAPI !== 'cli') {
            ob_flush(); flush();
        }
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

    CsvManager::init();
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
 *
 */
function updateByPinterest( $row )
{
    static $pinterestRows;
    if ( !$pinterestRows ) {
        $pinterestRows = PinterestHelper::getAllRows();
        // print_r($pinterestRows); exit;
    }

    /*
     *  取 64 byte 是因為 pinterest 的欄位最大只能存 64 byte
     */
    foreach ( $pinterestRows as $pinterestRow ) {
        if ( substr($row['campaign'],0,64) != substr($pinterestRow['name'],0,64) ) {
            // name 核對不同
            continue;
        }
        if ( $row['date'] != date('n/j/Y',$pinterestRow['date']) ) {
            // 日期 核對不同
            continue;
        }
        $row['cost']        = (float) substr($pinterestRow['spend'],1);
        $row['impressions'] = $pinterestRow['impressions'];
        $row['clicks']      = $pinterestRow['repins'];
        break;
    }

    return $row;
}

/**
 *  tollfreeforwarding API
 *  使用時請注意時區!
 */
function updateByTollfreeforwarding( $row )
{
    static $stat;
    if ( !$stat ) {
        $stat = TollfreeforwardingHelper::getStat();
        // print_r($stat); exit;
    }

    $row['conv']    = 0;
    $row['revenue'] = 0;
    ArrayIndex::set($stat);

    $phoneNumbers = explode("||", $row['phonenum'] );
    foreach ( $phoneNumbers as $number ) {
    $index = ArrayIndex::getIndexByHasString('id', $number);
        if ( null !== $index ) {
            $row['conv']    += ArrayIndex::get($index, 'conv');
            $row['revenue'] += ArrayIndex::get($index, 'revenue');
        }
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
