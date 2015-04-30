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
        $session = $this->getSession();
        if ( !$session ) {
            die('error!');
        }

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

        //$objectiveItems  = $this->getObjective();
        return $costItems;
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
        FacebookSession::setDefaultApplication( $this->id, $this->secret );
        $session = FacebookSession::newAppSession();

        // To validate the session:
        try {
            $session->validate();
        } catch (FacebookRequestException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
            Log::record($ex->getMessage());
            return null;
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
            Log::record($ex->getMessage());
            return null;
        }

        return $session;
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
