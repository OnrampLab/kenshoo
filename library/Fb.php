<?php

use Facebook\FacebookSession;
use FacebookAds\Api;
use FacebookAds\Object\AdAccount;

class Fb
{
    protected $id;
    protected $secret;
    
    public function __construct( $id, $secret )
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    public function get()
    {
        /*
        $session = $this->getSession();
        if ( !$session ) {
            Log::record(date("Y-m-d H:i:s", time()) . ' - Facebook session error');
            die('error!');
        }
        */

        // TODO: 目前沒辦法取得永久的 token 值, 暫時要使用人工設定的方式來處理, 請統一一個時間來調整時間, 例如每個月的第一個工作天
        $token = APPLICATION_FACEBOOK_LONG_TOKEN;
        Api::init( $this->id, $this->secret, $token );
        $api = Api::instance();

        $costItems = $this->getCost();

        // match and get
        $campaignIdItems = $this->getCampaignIds();
        foreach ( $costItems as $index => $item ) {
            foreach ( $campaignIdItems as $campaignIdItem ) {
                if ( $item['group_name'] == $campaignIdItem['group_name'] ) {
                    $costItems[$index]['campaign_id'] = $campaignIdItem['campaign_id'];
                    break;
                }
            }
        }

        // match and get
        $adGroupStats = $this->getAdGroupStats();
        foreach ( $costItems as $index => $item ) {
            foreach ( $adGroupStats as $adGroupStatItem ) {
                if ( $item['campaign_id'] == $adGroupStatItem['campaign_id'] ) {
                    $costItems[$index]['inline_actions_comment'] = $adGroupStatItem['inline_actions']['comment'];
                    break;
                }
            }
        }


/*
        $fb = new Facebook\Facebook([
            'app_id'                => $this->id,
            'app_secret'            => $this->secret,
            'default_graph_version' => 'v2.4',
        ]);
        $fb->setDefaultAccessToken(APPLICATION_FACEBOOK_LONG_TOKEN);
        $actId = 'act_' . APPLICATION_FACEBOOK_ACT_ID;

        //$objectiveItems  = $this->getObjective();
        print_r($this->getActiveItems($fb, $actId));
        exit;
*/

        return $costItems;
    }

    /**
     *
     */
    public function getActiveItems($fb, $actId)
    {

        try {
            //echo date("Y-m-d", time()-);
            $fields = "name,campaign_group_status,insights{campaign_group_name,spend,reach,actions}";
            $date = date("Y-m-d", strtotime("-1 day"));
            $url = "/{$actId}/adcampaign_groups?fields={$fields}&campaign_group_status=['ACTIVE']&time_range={'since':'{$date}','until':'{$date}'}&limit=5";
            
            // $url = "act_112950872167640/adcampaign_groups?fields=name,campaign_group_status,insights{actions}";
            $url = $actId . '/insights?level=adgroup&fields=campaign_group_name,spend,reach,actions&time_range={"since":"2015-08-02","until":"2015-08-02"}';

            // try it about ACTIVE ( 五百多筆)
            $url = '/act_112950872167640/insights?level=adgroup&fields=campaign_group_name,spend,reach,actions&campaign_status=["ACTIVE"]&time_range={"since":"2015-08-02","until":"2015-08-02"}';


            $url = '/act_112950872167640/insights?level=adgroup&fields=campaign_group_name' . '&time_range={"since":"2015-08-02","until":"2015-08-02"}';

            echo $url;
            echo "\n";

          //$response = $fb->get('/'. $actId .'/adcampaign_groups?fields=name,campaign_group_status,insights{campaign_group_name,spend,reach,actions}&limit=5&campaign_group_status=["ACTIVE"]&date_preset=yesterday&limit=5');
          //$response = $fb->get('/'. $actId .'/adcampaign_groups?fields=name,campaign_group_status=ACTIVE,insights{campaign_group_name,spend,reach,actions}&limit=5&date_preset=yesterday&limit=5');
            $response = $fb->get($url);
            
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $message = 'Graph returned an error: ' . $e->getMessage();
            echo $message;
            Log::record($message);
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $message =  'Facebook SDK returned an error: ' . $e->getMessage();
            echo $message;
            Log::record($message);
            exit;
        }

        $total = 0;
        $feed = $response->getGraphEdge();
        foreach ($feed as $status) {
            $total++;
            print_r($status->asArray());
        }


        while( $nextFeed = $fb->next($feed) ) {
            echo "---------------------------------( {$total} )--";
            foreach ($nextFeed as $status) {
                $total++;
                print_r($status->asArray());
            }
        }

        echo 'total = '.$total;

    }

    /**
     *
     */
    public function getFacebookData()
    {

        try {
            // Requires the "read_stream" permission
            $act = 'act_' . APPLICATION_FACEBOOK_ACT_ID;
          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=spend,campaign_group_name,reach,actions&campaign_group_status=ACTIVE&date_preset=yesterday');
          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=spend,campaign_group_name,reach,actions&campaign_status=ACTIVE&date_preset=yesterday');
          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=spend,campaign_group_name,reach,actions&adgroup_status=[\'ACTIVE\']&date_preset=yesterday');

          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=spend,campaign_group_name,reach,actions&campaign_group_status{ACTIVE}&date_preset=yesterday');

          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=campaign_group_name,spend,reach,actions&campaign_group_status=["ACTIVE"]&date_preset=yesterday&limit=5');
          
          //$response = $fb->get('/'. $act .'/adcampaign_groups?fields=name,campaign_group_status,insights{campaign_group_name,spend,reach,actions}&limit=5&campaign_group_status=["ACTIVE"]&date_preset=yesterday&limit=5');
          
          //$response = $fb->get('/'. $act .'/adcampaign_groups?fields=name,campaign_group_status=ACTIVE,insights{campaign_group_name,spend,reach,actions}&limit=5&date_preset=yesterday&limit=5');

          //$response = $fb->get('/'. $act .'/insights?level=adgroup&fields=campaign_group_name,spend,reach,actions&date_preset=yesterday&limit=5');
            $response = $fb->get('/'. $act .'/insights?level=adgroup&fields=campaign_group_name,spend,reach,actions&limit=5&time_range={"since":"2015-08-02","until":"2015-08-02"}');

          
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $message = 'Graph returned an error: ' . $e->getMessage();
            echo $message;
            Log::record($message);
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $message =  'Facebook SDK returned an error: ' . $e->getMessage();
            echo $message;
            Log::record($message);
            exit;
        }

        $total = 0;
        $feed = $response->getGraphEdge();
        foreach ($feed as $status) {
            $total++;
            print_r($status->asArray());
        }

        /*
                // Page 2 (next 5 results)
                while( $nextFeed = $fb->next($feed) ) {
                    echo "---------------------------------( {$total} )--";
                    foreach ($nextFeed as $status) {
                        $total++;
                        print_r($status->asArray());
                    }
                }
        */
        exit;




        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array();
        $params = array(
            /*
            // 未經測試，很有可能無法執行
            'day_start'    => array('year'=>'2015','month'=>'4','day'=>'1'),
            'day_end'      => array('year'=>'2015','month'=>'4','day'=>'1'),
            */
            'date_preset'  => 'yesterday',
          //'data_columns' => array('spend','campaign_group_name','reach','clicks'),
            'data_columns' => array('name','campaign_group_status','insights','spend','campaign_group_name','reach','clicks'),
        );

        $adsData = $account->getAdCampaigns($fields, $params);
        $adsData = $account->getAdGroups($fields, $params);
        //print_r($adsData);
        //exit;


        $result = array();
        foreach($adsData as $data) {
            print_r($data);
            echo '-----------------------------------------------------------';
            continue;          
            exit;
            $result[] = array(
                'group_name' => $stat->campaign_group_name,
                'spend'      => $stat->spend,
                'reach'      => $stat->reach,
                'clicks'     => $stat->clicks,
            );
        }
        return $result;
    }

    /**
     *
     */
    public function getCost()
    {
        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array();
        $params = array(
            /*
            // 未經測試，很有可能無法執行
            'day_start'    => array('year'=>'2015','month'=>'4','day'=>'1'),
            'day_end'      => array('year'=>'2015','month'=>'4','day'=>'1'),
            */
            'date_preset'  => 'yesterday',
            'data_columns' => array('spend','campaign_group_name','reach','clicks'),
        );

        $stats = $account->getReportsStats($fields, $params);
        $result = array();
        foreach($stats as $stat) {
            $result[] = array(
                'group_name' => $stat->campaign_group_name,
                'spend'      => $stat->spend,
                'reach'      => $stat->reach,
                'clicks'     => $stat->clicks,
            );
        }
        return $result;
    }

    /**
     *
     */
    public function getCampaignIds()
    {
        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array();
        $params = array(
            'date_preset'  => 'yesterday',
            'data_columns' => array('campaign_group_name','campaign_id'),
        );

        $stats = $account->getReportsStats($fields, $params);
        $result = array();
        foreach($stats as $stat) {
            $result[] = array(
                'campaign_id' => $stat->campaign_id,
                'group_name'  => $stat->campaign_group_name,
            );
        }
        return $result;
    }

    /**
     *
     */
    public function getAdGroupStats()
    {
        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array();
        $params = array(
            'start_time'   => date('Y-m-d', $this->getDay(-1)),
            'end_time'     => date('Y-m-d', $this->getDay()),
        );

        $stats = $account->getAdGroupStats($fields, $params);
        $result = array();
        foreach($stats as $stat) {
            $result[] = array(
                'inline_actions' => $stat->inline_actions,
                'campaign_id'    => $stat->campaign_id,
                'start_time'     => $stat->start_time,
                'end_time'       => $stat->end_time,
            );
        }
        return $result;
    }

    /*
    TODO: 找不到相同的 account_id 值, 所以暫時註解起來, 確定無法使用時, 可以刪除此區塊
    public function getObjective()
    {
        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array('objective','account_id');  // ,'name','buying_type','campaign_group_status'
        $params = array(
            'date_preset'=>'yesterday',
            'data_columns'=>array(),
        );
        
        $stats = $account->getAdCampaigns($fields, $params);
        $result = array();
        foreach($stats as $stat) {
            $result[] = array(
                'account_id' => $stat->account_id,
                'objective'  => $stat->objective,
            );
        }

        return $result;
    }
    */

    private function getSession()
    {
        $fb = new Facebook\Facebook([
            'app_id'                => $this->id,
            'app_secret'            => $this->secret,
            'default_graph_version' => 'v2.4',
        ]);

        try {
            $accessToken = $fb->getApp()->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
            exit;
        }

        return $accessToken;

    }

    /**
     *  加減算日期的簡易函式, 以天為單位
     *  
     *  getDay()        // 今天
     *  getDay(1)       // 明天
     *  getDay(-30)     // 30天前
     *  
     *  @param  int $day
     *  @return int
     */
    private function getDay( $day=0 )
    {
        $day = (int) $day;
        return ( time() + ($day*86400) );
    }

}

/*

    參考資訊:

    [取得 ???? 之後的 code]
        curl -G \
        -d "client_id=369227659923524" \
        -d "client_secret=原本的code" \
        -d "grant_type=client_credentials" \
        "https://graph.facebook.com/oauth/access_token?"

    [查看權限]
        curl -G \
        -d "input_token=369227659923524|要求 grant_type=client_credentials 之後的code" \
        -d "access_token=369227659923524|原本的code" \
        "https://graph.facebook.com/debug_token?"

*/
