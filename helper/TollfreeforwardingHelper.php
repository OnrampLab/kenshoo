<?php

class TollfreeforwardingHelper
{

    public static function getStat()
    {
        $result = array();
        $items = self::getAll();
        $items = self::baseFilter( $items );
        $items = self::uniquePhone($items);
        $stat = self::statisticById($items);
        return $stat;
    }

    /* --------------------------------------------------------------------------------
        
    -------------------------------------------------------------------------------- */

    /**
     *  id & phone to unique
     *  在多筆 id & phone unique 的情況, 要讓 second 取得為最大值
     */
    /*
    private static function uniqueIdAndPhone( $items )
    {
        $result = array();
        foreach ( $items as $item ) {
            $id = $item['id'].'_'.$item['phone'];
            if ( !isset($result[$id]) ) {
                $result[$id] = $item;
            }
            else {
                if ( $result[$id]['second'] < $item['second'] ) {
                    $result[$id]['second'] = $item['second'];
                }
            }
        }
        return array_values($result);
    }
    */

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
            $result[] = CsvManager::map(explode(",",$csvItem));
        }
        return $result;
    }

    /**
     *
     */
    private static function getContentByApi()
    {
        $user = APPLICATION_TOLLFREEFORWARDING_USER;
        $pwd = APPLICATION_TOLLFREEFORWARDING_PWD;

        // 原本日期區設定為 "只取 前60天 的記錄"
        // $today = date("Y-m-d");
        // $startDate = date('Ymd', strtotime($today . ' - 60 day'));
        // $endDate   = date("Ymd", strtotime($today . ' + 1 day'));

        // 現在日期區間改為 "2014/10/1 到 今天"
        $today = date("Y-m-d");
        $startDate = date('Ymd', strtotime('2014-10-01'));
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