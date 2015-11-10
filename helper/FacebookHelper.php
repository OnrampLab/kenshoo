<?php

class FacebookHelper
{

    public static function getItems()
    {
        $items = self::getCampaignsItems();
        $ids = array_column($items, 'id');  // PHP v5.5 up
        $insights = self::getInsightsByCampaignsIds($ids);

        foreach ($items as $index => $item) {

            // 初始化
            $items[$index]['impressions'] = 0;
            $items[$index]['spend'] = 0;
            $items[$index]['action_comment'] = 0;

            foreach ($insights as $insightId => $insight) {
                if ($item['id']===$insightId) {

                    if (!isset($insight['data'][0])) {
                        show('Facebook 資料比對有誤, 可能是一筆空的資料!! id = '. $insightId, true);
                        break;
                    }

                    $data = $insight['data'][0];
                    if (isset($data['impressions'])) {
                        $items[$index]['impressions'] = $data['impressions'];
                    }
                    if (isset($data['spend'])) {
                        $items[$index]['spend'] = $data['spend'];
                    }
                    if (!isset($data['actions'])) {
                        show('Facebook 資料比對有誤, 可能是一筆空的資料!! (2) id = '. $insightId, true);
                        break;
                    }
                    foreach ($data['actions'] as $actions) {
                        $key = 'action_' . $actions['action_type'];
                        $items[$index][$key] = $actions['value'];
                    }
                    break;
                }

            }
        }

        return $items;
    }




    // --------------------------------------------------------------------------------
    // private
    // --------------------------------------------------------------------------------

    /**
     *  curl facebook API helper
     *  TODO: 目前沒辦法取得 forever token , 暫時使用人工設定的方式來建立 long token, 請統一某個時間來調整時間, 例如每個月的第一個工作天
     *
     *  example
     *      $attachment = [
     *          'fields'             => 'name,adsets{insights{date_start,date_stop}}',
     *          'effective_status[]' => 'ACTIVE',
     *      ];
     *      $result = facebookCurl('campaigns', $attachment);
     *
     *  @return array  - get information
     *          string - error message
     */
    private static function facebookCurl($feed, $attachment)
    {
        $actId = 'act_' . APPLICATION_FACEBOOK_ACT_ID;
        $attachment += array(
            'access_token' => APPLICATION_FACEBOOK_LONG_TOKEN,
        );
        $url = "https://graph.facebook.com/v2.5/{$actId}/{$feed}?" . http_build_query($attachment);


        exec('curl -i -X GET "'. $url .'" 2> /dev/null', $output);


        if (!$output || !is_array($output)) {
            return false;
        }

        $result = $output[ count($output)-1 ];
        $result = json_decode($result, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $result;
            break;

            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        }

        return 'Unknown error';
        exit;

        /*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($attachment) );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type"      => "application/x-www-form-urlencoded; charset=UTF-8",
            "Accept"            => "Application/json",
            "X-Requested-With"  => "XMLHttpRequest",
        ]);
        $result = curl_exec($ch);
        curl_close ($ch);
        return $result;
        */
    }

    /**
     *
     */
    private static function getCampaignsItems()
    {
        // campaigns?fields=id&effective_status[]=ACTIVE
        // campaigns?fields=id,insights{campaign_name,impressions,spend,actions}&effective_status[]=ACTIVE
        $attachment = array(
            'fields'             => 'id,name',
            'effective_status[]' => 'ACTIVE',
        );
        $result = self::facebookCurl('campaigns', $attachment);
        // show($result); exit;

        $items = [];
        if (!is_array($result)) {
            show("Facebook get campaigns ids error: {$result}", true);
            exit;
        }
        foreach ($result['data'] as $item) {
            $items[] = $item;
        }

        return $items;
    }

    /**
     *
     */
    private static function getInsightsByCampaignsIds(Array $ids)
    {
        // insights?ids=6033427270560,6032991364560&fields=campaign_name,impressions,spend,actions&date_preset=yesterday
        $attachment = array(
            'ids'           => join(',', $ids),
            'fields'        => 'campaign_name,impressions,spend,actions',
            'date_preset'   => 'yesterday',
        );
        $result = self::facebookCurl('insights', $attachment);
        // show($result); exit;

        if (!is_array($result)) {
            show("Facebook get insights error: {$result}", true);
            exit;
        }

        return $result;
    }

}
