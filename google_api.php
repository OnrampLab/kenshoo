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
    echo '<pre>';

    $token = GoogleApiHelper::getToken();
    if ( !$token ) {
        die('token error!');
    }

    $worksheet = GoogleApiHelper::getWorksheet( 'test001', 'sheet1', $token );
    if ( !$worksheet ) {
        die('worksheet not found!');
    }

    $sheet = new GoogleWorksheetManager($worksheet);
    $count = $sheet->getCount();
    for ( $i=0; $i<$count; $i++ ) {
        $row = $sheet->getRow($i);

        // Override = 1 or 0
        if ( !$row['override'] ) {
            // 為 0 時不覆蓋任何值
            continue;
        }
        
        print_r($row);
        $row = updateDate($row);
        $row = updateByFacebook($row);
        print_r($row);
        exit;
        //$sheet->setRow($i, $row);
    }

}

function updateDate( $row )
{
    $row['date'] = date("n/j/Y", time());
    $row['date'] = date('n/j/Y', strtotime($row['date'] . ' - 1 day'));
    return $row;
}

function updateByFacebook( $row )
{

    $fb = new Fb(APPLICATION_FACEBOOK_ID, APPLICATION_FACEBOOK_SECRET);
    $result = $fb->get();

    // create new cvs contents
    ArrayIndex::set($result['cost']);
    $contents = array();

    CsvManager::setHeader(fgetcsv($handle));
    CsvManager::setFilter(array(
        'cost' => 'float',
        'fb-objective' => 'int'
    ));
print_r($row);
    $index = ArrayIndex::getIndex('group_name', $row['campaign']);
echo 'index='.$index;
    if ( null !== $index ) {
        $row['cost']        = ArrayIndex::get($index, 'spend');
        $row['impressions'] = ArrayIndex::get($index, 'reach');
        $row['clicks']      = ArrayIndex::get($index, 'clicks');
    }
print_r($row);
exit;
    return $row;

}



