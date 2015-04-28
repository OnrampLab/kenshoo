<?php

class TollfreeforwardingHelper
{

    public static function getStat()
    {
        static $stat;
        if ( $stat ) {
            return $stat;
        }

        $items = self::getAll();
        foreach ( $items as $index => $item ) {
            if ( !$item ) {
                continue;
            }
            $items[$index]['phone'] = substr($item['phone'],-10);
        }

        // 如果沒有今天的 phone call, 則去除該筆資料
        $hasTodayPhoneItems = array();
        $todayPhones = TollfreeforwardingHelper::getUniqueTodayPhones();
        foreach ( $items as $item ) {
            if (in_array($item['phone'], $todayPhones)) {
                $hasTodayPhoneItems[] = $item;
            }
        }

        $hasTodayPhoneItems = self::baseFilter( $hasTodayPhoneItems );
        $hasTodayPhoneItems = self::uniquePhone($hasTodayPhoneItems);
        $stat = self::statisticById($hasTodayPhoneItems);
        return $stat;
    }

    /* --------------------------------------------------------------------------------
        
    -------------------------------------------------------------------------------- */

    /**
     *  刪除來自客服的 id (電話) -> 1-800-701-4026
     */
    private static function baseFilter( $items )
    {
        $result = array();
        foreach ( $items as $item ) {
        
            if ( $item['id']=='1-800-701-4026' ) {
                continue;
            }
        
            $result[] = $item;
        }
        return $result;
    }

    /**
     *  phone to unique
     *  在多筆 phone unique 的情況, 要讓 second 取得為最大值
     */
    private static function uniquePhone( $items )
    {
        $result = array();
        foreach ( $items as $item ) {
            $key = $item['phone'];
            if ( !isset($result[$key]) ) {
                $result[$key] = $item;
            }
            else {
                if ( $result[$key]['second'] < $item['second'] ) {
                    $result[$key]['second'] = $item['second'];
                }
            }
        }
        return array_values($result);
    }

    /**
     *  統計資料
     *      - conv    -> 每通電話  61秒 以上 @*1
     *      - revenue -> 每通電話 181秒 以上 @*50
     */
    private static function statisticById( $items )
    {
        $stat = array();
        foreach ( $items as $item ) {
            $key = $item['id'];

            if ( !isset($stat[$key]) ) {
                $stat[$key] = array(
                    'id'      => $item['id'],
                    'conv'    => 0,
                    'revenue' => 0,
                );
            }

            if ( $item['second'] >=181 ) {
                // 單位為 50
                $stat[$key]['revenue'] += 50;
            }
            if ( $item['second'] >=61 ) {
                $stat[$key]['conv']++;
            }

        }

        return array_values($stat);
    }

    /**
     *  
     */
    private static function getAll()
    {
        $csvContent = self::getContentByApi();
        $csvItems = explode("\n",$csvContent);

        $result = array();
        CsvManager::init();
        CsvManager::setHeader(array('id','phone','second'));
        foreach ( $csvItems as $csvItem ) {
             $data = CsvManager::map(explode(",",$csvItem));
             if ( $data ) {
                 $result[] = $data;
             }
        }

        return $result;
    }

    /**
     *
     */
    private static function getContentByApi()
    {
        $user = APPLICATION_TOLLFREEFORWARDING_USER;
        $pwd  = APPLICATION_TOLLFREEFORWARDING_PWD;

        // 原本日期區設定為 "只取 前60天 的記錄"
        // 後來改為從 2014-10-1 到今天
        // 後來因為該 API 不能取超過 10000 筆, 所以分成取每個月, 最後再併一起 by 2015-04-15

        $dateList = self::getBetweenMonthFiratDay();

        $total = count($dateList)-1;
        $content = '';
        for ( $i=0; $i<$total; $i++ ) {
            $startDate = $dateList[$i];
            $endDate   = $dateList[$i+1];

            $url = "https://tollfreeforwarding.com/api/?"
                 . "u={$user}"
                 . "&p={$pwd}"
                 . "&rangeStart={$startDate}"
                 . "&rangeEnd={$endDate}"
                 . "&timezone=-8"
                 . "&fields=callerId,appear,durationSeconds"
                 . "&format=comma"
            ;
            // debug
            // echo $url."\n";

            $result = trim(file_get_contents($url));
            if ( "<"==substr($result,0,1) ) {
                $log  = date("Y-m-d H:i:s", time()) . " - tollfreeforwarding.com API error: \n";
                $log .= $url . "\n";
                $log .= $result;
                Log::record( $log );
                exit;
            }

            $content .= $result . "\n";

        }
        // debug
        // echo $content; exit;

        return $content;
    }

    /**
     *  從 2014-10-01 開始, 並取得每個月 1號 的日期, 一直到今天
     *
     *  輸入格式 (日期格式)
     *      "2014-10-01"
     *
     *  輸出格式 (array)
     *      Array
     *      (
     *          [0] => 20141101
     *          [1] => 20141201
     *          [2] => 20150101
     *          [3] => 20150201
     *          [4] => 20150206
     *      )
     *
     */
    private static function getBetweenMonthFiratDay( $firstDate="2014-10-01" )
    {
        /*
            // test data
            return array(
                '20141001',
                '20141101',
                '20141201',
            );
        */

        $startYear  = substr($firstDate,0,4);
        $startMonth = substr($firstDate,5,2);
        $startDay   = substr($firstDate,8,2);
        $endYear    = date("Y");
        $today      = date("Ymd");

        // 先取出區間所有的日期
        $allBetween = [];
        $year  = $startYear;
        $month = $startMonth + 1;
        for ( true; $year<=$endYear; $year++ ) {
            for ( true; $month<=12; $month++ ) {
                $allBetween[] = $year . sprintf("%02d", $month) . '01';
            }
            $month = 1;
        }

        // 重新取得自己要的時間
        $list = [];
        $list[] = $startYear . $startMonth . $startDay;
        foreach ( $allBetween as $date ) {
            if ( $date < $today ) {
                $list[] = $date;
            }
        }
        $list[] = $today;

        // debug
        // print_r( $allBetween );
        // print_r( $list );
        // exit;

        return $list;
    }


    /**
     *  取得 "今天" 有 phone call 的 phones array
     *  請注意主程式的時區設定
     *
     *  @return array
     */
    private static function getUniqueTodayPhones()
    {
        $result = array();
        $items = self::getTodayAll();
        foreach ( $items as $item ) {
            $result[] = substr($item['phone'],-10);
        }
        return array_values(array_unique($result));
    }

    /**
     *  
     */
    private static function getTodayAll()
    {
        $csvContent = self::getTodayByApi();
        $csvItems = explode("\n",$csvContent);

        $result = array();
        CsvManager::init();
        CsvManager::setHeader(array('id','phone','second'));
        foreach ( $csvItems as $csvItem ) {
            $data = CsvManager::map(explode(",",$csvItem));
            if ( $data ) {
                $result[] = $data;
            }
        }
        return $result;
    }

    /**
     *
     */
    private static function getTodayByApi()
    {
        $user = APPLICATION_TOLLFREEFORWARDING_USER;
        $pwd = APPLICATION_TOLLFREEFORWARDING_PWD;

        // 原本日期區設定為 "只取 前60天 的記錄"
        $today = date("Y-m-d");
        $startDate = date('Ymd', strtotime($today . ' - 1 day'));
        $endDate   = date("Ymd", strtotime($today . ' + 1 day'));

        $url = "https://tollfreeforwarding.com/api/?"
             . "u={$user}"
             . "&p={$pwd}"
             . "&rangeStart={$startDate}"
             . "&rangeEnd={$endDate}"
             . "&timezone=-8"
             . "&fields=callerId,appear,durationSeconds"
             . "&format=comma"
        ;
        // debug
        // echo $url; exit;

        return trim(file_get_contents($url));
    }

}