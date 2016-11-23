<?php

class PinterestHelper
{

    public static function getTodayCsvFile()
    {
        $today = date("Y-m-d");
        return 'pinterest-to-['. $today .'].csv';
    }

    public static function setPathFile()
    {
        return APPLICATION_DIR . '/tmp/pinterest_export/' . self::getTodayCsvFile();
    }

    public static function getAllRows()
    {
        $rows = array();
        $csvFile = self::setPathFile();

        if( !file_exists($csvFile)) {
            Log::record(date("Y-m-d H:i:s", time()) . ' - Get pinterest file error');
        }

        $csv = new CsvReadManager($csvFile);
        $csv->setFilter(array(
            'impressions' => 'int'
        ));
        foreach ( $csv->generator() as $item ) {
            if (!$item) {
                continue;
            }
            $dateInt = strtotime($item['date']);
            $rows[] = array(
                'campaign'    => $item['campaign_id'],
                'name'        => $item['name'],
                'date'        => $dateInt,
                'spend'       => $item['spend'],
                'impressions' => $item['impressions'],
                'repins'      => $item['repins'],
            );
        }
        return $rows;
    }

    /* --------------------------------------------------------------------------------
        
    -------------------------------------------------------------------------------- */

}

