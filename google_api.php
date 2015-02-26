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
require_once 'helper/MailHelper.php';
require_once 'uploadHelper.php';
require_once 'downloadHelper.php';

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

perform();
exit;

/**
 * 
 */
function perform()
{
    echo '<pre>';

    $accessToken = getToken();
    $serviceRequest = new DefaultServiceRequest($accessToken);
    ServiceRequestFactory::setInstance($serviceRequest);

    $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
    $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
    //print_R($spreadsheetFeed);

    $spreadsheet = $spreadsheetFeed->getByTitle('test002');
    //print_r($spreadsheet);

    $worksheets = $spreadsheet->getWorksheets();
    $worksheet = $worksheets->getByTitle('sheet1');
    //print_r($worksheets);
    //print_r(get_class_methods($worksheets));

    $listFeed = $worksheet->getListFeed();
    $entries = $listFeed->getEntries();
    $listEntry = $entries[0];
    $values = $listEntry->getValues();
    print_r($values);


/*
    $feed = new Google\Spreadsheet\SpreadsheetFeed(
        ServiceRequestFactory::getInstance()->get('feeds/spreadsheets/private/full')
    );
    print_r($feed);
*/

/*
    舊版的寫法

    $spreadsheet = $spreadsheetFeed->getByTitle('title_of_the_spreadsheet_doc');
    $worksheetFeed = $spreadsheet->getWorksheets();
    $worksheet = $worksheetFeed->getByTitle('title_of_the_tab');
    $listFeed = $worksheet->getListFeed();

    // this bit below will create a new row, only if you have a frozen first row adequatly labelled
    $row = array('name'=>'John', 'age'=>25);
    $listFeed->insert($row);
*/

/*
    print_r(count($spreadsheetFeed));
    foreach($spreadsheetFeed as $spreadsheet){
        print_r($spreadsheet);
    }
*/

/*
    echo '----';
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_GOOGLE_CLIENT_APP_NAME);
    $client->setDeveloperKey(APPLICATION_GOOGLE_CLIENT_API_KEY);
    
    $service = new Google_Service_Books($client);
    echo '<pre>';
    print_r($service);

    echo "\n";
*/
}


function getToken()
{
    $key = file_get_contents( APPLICATION_DIR.APPLICATION_GOOGLE_CLIENT_KEY_FILE);
    $cred = new Google_Auth_AssertionCredentials(
        APPLICATION_GOOGLE_CLIENT_EMAIL,
        array(
            'https://spreadsheets.google.com/feeds',
            'https://docs.google.com/feeds'
        ),
        $key
        // 'notasecret'  // key password
    );

    $client = new Google_Client();
    //$client->setApplicationName(APPLICATION_GOOGLE_CLIENT_APP_NAME);
    //$client->setClientId(APPLICATION_GOOGLE_CLIENT_ID);
    $client->setAssertionCredentials($cred);

    if (!$client->getAuth()->isAccessTokenExpired()) {
        die('token error!');
    }
    else {
        $client->getAuth()->refreshTokenWithAssertion($cred);
    }

    $service_token = json_decode($client->getAccessToken());
    return $service_token->access_token;
}




/*

class csvBridge

    name,age,like
    kevin,15,eat
    vivian,18,eat/sport
    old man,85,mountain
    Chris,45,game

*/
$csv = new CsvBridgeGoogleSpreadsheet();
$csv->init(array(
    'token'               => '',
    'google_client_email' => APPLICATION_GOOGLE_CLIENT_EMAIL
));

$csv = new CsvBridgeFile();
$csv->init( array(
    'file' => 'tmp/test001.csv'
));

$csv->getHeader()   // name,age,like
$csv->setHeader()   // 格式 -> array
$csv->map($row)     // index array -> hash array

$csv->getCount()    // 4
//$csv->field('a1') // name
//$csv->field('a2') // kevin
$csv->getRow(0)     // kevin,15,eat
$csv->setRow(0, Array())    // 實際上會先比對, 不同才改變, 會比較慢, 但對於 sheet 格式來說較合理

$csv->getMapRow(0)  // name=>kevin , age=>15 , like=>eat
$csv->setMapRow(0, Array()) // 實際上會先比對, 不同才改變, 會比較慢, 但對於 sheet 格式來說較合理

$csv->save()        // 儲存所有內容, 預設值 isWriteHeader=true

$count = $csv->getCount();
for ( $i=1; $i<=$count; $i++ ) {
    $mapRow = $csv->getMapRow($i);
    $csv->setMapRow($i, $mapRow );
}



※新的要加上
impressions -> reach
clicks      -> clicks

