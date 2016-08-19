<?php

class FacebookHelper
{
    /**
     *  Campaigns sheet
     */
    public static function getWrapCampaignLevel()
    {
        $items = [];
        $after = '';

        do {
            $result = self::_getCampaignLevel($after);
            // show($result); exit;

            foreach ($result['data'] as $data) {
                if (!isset($data['campaign_id'])) {
                    continue;
                }
                if (!isset($data['campaign_name'])) {
                    continue;
                }

                $item = [];
                $item['campaign_id']    = $data['campaign_id'];
                $item['campaign_name']  = $data['campaign_name'];
                $item['impressions']    = isset($data['impressions']) ? $data['impressions'] : null;
                $item['spend']          = isset($data['spend'])       ? $data['spend']       : null;
                $item['action_comment'] = (string) (double) null;

                if (isset($data['actions'])) {
                    foreach ($data['actions'] as $actions) {
                        $key = 'action_' . $actions['action_type'];
                        $item[$key] = $actions['value'];
                    }
                }

                $items[] = $item;
            }

            if (!$after) {
                break;
            }
        } while (true);

        // show($items); exit;
        return $items;
    }

    public static function getWrapAdsetLevel()
    {
        $items = [];
        $after = '';

        do {
            $result = self::_getAdsetLevel($after);
            // show($result); exit;

            $after = '';
            if (
                isset($result['paging']) &&
                isset($result['paging']['next']) &&
                isset($result['paging']['cursors']) &&
                isset($result['paging']['cursors']['after'])
            ) {
                $after = $result['paging']['cursors']['after'];
            }

            foreach ($result['data'] as $data) {
                if (!isset($data['campaign_id'])) {
                    continue;
                }
                if (!isset($data['campaign_name'])) {
                    continue;
                }
                if (!isset($data['adset_name'])) {
                    continue;
                }

                $item = [];
                $item['campaign_id']    = $data['campaign_id'];
                $item['campaign_name']  = $data['campaign_name'];
                $item['adset_name']     = $data['adset_name'];
                $item['impressions']    = isset($data['impressions']) ? $data['impressions'] : null;
                $item['spend']          = isset($data['spend'])       ? $data['spend']       : null;
                $item['action_comment'] = (string) (double) null;

                if (isset($data['actions'])) {
                    foreach ($data['actions'] as $actions) {
                        $key = 'action_' . $actions['action_type'];
                        $item[$key] = $actions['value'];
                    }
                }

                $items[] = $item;
            }

            if (!$after) {
                break;
            }
        } while (true);


        // show($items); exit;
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
        $url = "https://graph.facebook.com/v2.6/{$actId}/{$feed}?" . http_build_query($attachment);


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
    }


    /**
     *  campaigns sheet
     */
    private static function _getCampaignLevel($after='')
    {
        // insights?fields=campaign_id,campaign_name,impressions,spend,actions&effective_status[]=ACTIVE&date_preset=yesterday&level=campaign
        $attachment = array(
            'fields'             => 'campaign_id,campaign_name,impressions,spend,actions',
            'effective_status[]' => 'ACTIVE',
            'date_preset'        => 'yesterday',
            'level'              => 'campaign',
            'limit'              => 25,
        );
        if ($after) {
            $attachment['after'] = $after;
        }

        $result = self::facebookCurl('insights', $attachment);
        self::checkFacebookCurlResult($result, 'Facebook get campaigns error');
        return $result;
    }

    /**
     *  adset (AdGroups) sheet
     */
    private static function _getAdsetLevel($after='')
    {
        // insights?fields=campaign_id,campaign_name,adset_name,impressions,spend,actions&effective_status[]=ACTIVE&date_preset=yesterday&level=adset
        $attachment = array(
            'fields'             => 'campaign_id,campaign_name,adset_name,impressions,spend,actions',
            'effective_status[]' => 'ACTIVE',
            'date_preset'        => 'yesterday',
            'level'              => 'adset',
            'limit'              => 25,
        );
        if ($after) {
            $attachment['after'] = $after;
        }

        $result = self::facebookCurl('insights', $attachment);
        self::checkFacebookCurlResult($result, 'Facebook get adsets error');
        return $result;
    }



    /**
     *  AdGroups sheet
     */
    private static function getGroupCampaignsItems()
    {
        $attachment = array(
            'fields'             => 'id,name,adsets',
            'effective_status[]' => 'ACTIVE',
        );
        $result = self::facebookCurl('campaigns', $attachment);

        $items = [];
        if (!is_array($result)) {
            show("Facebook get campaigns ids error: {$result}", true);
            exit;
        }

        if (isset($result['error'])) {
            show("Error:", true);
            show($result, true);
            exit;
        }

        // 整理後輸出
        foreach ($result['data'] as $item) {
            $tmp = $item;
            $tmp['_custom'] = [
                'adsets' => []
            ];

            if (isset($tmp['adsets']) &&
                 isset($tmp['adsets']['data']) &&
                 is_array($tmp['adsets']['data'])) {
                $tmp['_custom']['adsets'] = array_column($tmp['adsets']['data'], 'id');
            }
            unset($tmp['adsets']);
            $items[] = $tmp;
        }
        // show($items); exit;

        return $items;
    }

    /**
     *  檢查 facebookCurl() 傳出來的值, 是否有錯誤
     *  如果有錯誤, 直接輸出錯誤警告, 並停止程式
     */
    private static function checkFacebookCurlResult($result, $errorMessage)
    {
        if (!is_array($result)) {
            show("{$errorMessage}: {$result}", true);
            exit;
        }

        if (isset($result['error'])) {
            show("{$errorMessage} (output content):", true);
            show($result, true);
            exit;
        }
    }
}
