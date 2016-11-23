<?php
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
    echo "<pre>\n";
}

//echo ini_get("memory_limit"); exit;
//echo ini_set("memory_limit","2048M");

$basePath = dirname(__DIR__);
require_once $basePath . '/app/bootstrap.php';
initialize($basePath);

perform();
exit;

/**
 * 
 */
function perform()
{
    if ( phpversion() < '5.5' ) {
        /**
         *  @see array_column(), PHP 5.5
         */
        show("PHP Version need >= 5.5", true);
        exit;
    }

    if (!getParam('exec')) {
        show('---- debug mode ---- (參數 "exec" 執行寫入指令)');
        exit;
    }

    Log::record('start PHP '. phpversion() );

    // create CSV file
    $manager = createGoogleSheet();
    $tmpCsvFileName = getTmpCsvFileName();
    // makeCsvFile($tmpCsvFileName, $manager->getGid());
    writeCsvFile($tmpCsvFileName, $manager->worksheet->getCsv());

    // create campaign CSV file
    $campaigncsvFileName = getCampaignCsvFileName();
    // makeCsvFile($campaigncsvFileName, '976337387');
    $campaignWorksheet = getCampaignGoogleSheet();
    writeCsvFile($campaigncsvFileName, $campaignWorksheet->getCsv());

    // merge
    $originCsv = file_get_contents($campaigncsvFileName);
    $newCsv    = file_get_contents($tmpCsvFileName);

    $items = explode("\n", $newCsv);
    unset($items[0]);
    $newCsv = join("\n", $items);


    $mergeCsvContent = $originCsv . $newCsv;
    $csvFileName = getCsvFileName();
    file_put_contents($csvFileName, $mergeCsvContent);

    // upload CSV file
    uploadCsvFile($csvFileName);

    show("done", true);
}

/**
 *
 */
function getCampaignGoogleSheet()
{
    $token = GoogleApiHelper::getToken();
    if (!$token) {
        show('token error!', true);
        exit;
    }

    $worksheet = GoogleApiHelper::getWorksheet(
        APPLICATION_GOOGLE_SPREADSHEETS_BOOK,
        'campaign',
        $token
    );
    if (!$worksheet) {
        // 問題可能會出在 "無法刪除" 或 "無法建立"
        show('Error: "campaign" sheet not found!', true);
        exit;
    }

    return $worksheet;
}

/**
 *
 */
function createGoogleSheet()
{
    $token = GoogleApiHelper::getToken();
    if (!$token) {
        show('token error!', true);
        exit;
    }

    // delete and create sheet
    $sheetName = 'sheet_' . date('w');
    GoogleApiHelper::deleteWorksheet(
        APPLICATION_GOOGLE_SPREADSHEETS_BOOK,
        $sheetName,
        $token
    );
    $worksheet = GoogleApiHelper::createWorksheet(
        APPLICATION_GOOGLE_SPREADSHEETS_BOOK,
        $sheetName,
        $token
    );
    if (!$worksheet) {
        // 問題可能會出在 "無法刪除" 或 "無法建立"
        show('Error: sheet not found!', true);
        exit;
    }


    // create fields title
    $headers = [
        'date', 'channel', 'campaign', 'adgroup', 'keyword',
        'match_type', 'impressions', 'clicks', 'cost'
    ];
    $manager = new GoogleWorksheetManager($worksheet);
    $manager->createHeaders($headers);

    // add data
    $items = getFacebookAdGroupsItems();
    $index = 0;
    foreach ($items as $item) {

        $row = $manager->buildRow();
        $row = updateDate($row);
        // 注意, row 的名稱, 請自行去除 "_" 底線符號, google api 會過濾該符號
        $row['channel']     = '3090';
        $row['campaign']    = $item['campaign_name'];
        $row['adgroup']     = "'". $item['adset_name'];
        $row['keyword']     = 'Null';
        $row['matchtype']   = 'Broad';
        $row['impressions'] = $item['impressions'];
        $row['cost']        = $item['spend'];
        $row['clicks']      = $item['action_comment'];

        $index++;
        echo "{$index}";

        // add sheet row
        if (getParam('exec')) {
            appendRow($row, $manager);
        }
        echo ' ';

        // show message
        if (!isCli()) {
            ob_flush(); flush();
        }

    }

    show('');
    return $manager;
}

/**
 *  資料寫入 google sheet
 *  @return true=有寫入, false=無寫入
 */
function appendRow($row, $sheet)
{
    try {
        $sheet->addRow($row);
    }
    catch ( Exception $e) {
        show($e->getMessage(), true);
        exit;
    }
    return true;
}

/**
 *  NOTE: 請正確設定 google sheet 的時區
 */
function updateDate( $row )
{
  //$row['date'] = date("n/j/Y", time());
  //$row['date'] = date('n/j/Y', strtotime($row['date'] . ' - 1 day'));
    $row['date'] = date("n/j/Y", strtotime(date("n/j/Y") . ' - 1 day'));
    return $row;
}

/**
 *  cache facebook data
 */
function getFacebookAdGroupsItems()
{
    static $result;
    if ($result) {
        return $result;
    }

    $result = FacebookHelper::getWrapAdsetLevel();
    return $result;
}

/**
 *  NOTE: Pinterest 目前已停用
 */
function updateByPinterest( $row )
{
    return $row;
    exit;

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

    $row['conv']    = (string) $row['conv']   ;
    $row['revenue'] = (string) $row['revenue'];
    return $row;
}

/**
 *
 */
function getCsvFileName()
{
    $dateFormat = date('Y-m-d', time());
    $dateFormat = date('Y-m-d', strtotime($dateFormat . ' - 1 day'));
    $path = APPLICATION_DIR . '/tmp/csv_upload';
    $file = "SimplyBridal-UC_File-{$dateFormat}.csv";
    return $path . '/' . $file;
}

/**
 *
 */
function getCampaignCsvFileName()
{
    $path = APPLICATION_DIR . '/tmp/csv_upload';
    $file = "origin.csv";
    return $path . '/' . $file;
}

/**
 *
 */
function getTmpCsvFileName()
{
    $path = APPLICATION_DIR . '/tmp/csv_upload';
    $file = "tmp.csv";
    return $path . '/' . $file;
}

