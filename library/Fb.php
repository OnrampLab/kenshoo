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

        $result = array();
        $result['cost'] = $this->getCost();
        //$result['objective'] = $this->getObjective();
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
            'date_preset'=>'yesterday',
            'data_columns'=>array('spend','campaign_group_name','campaign_group_id'),
        );
        
        $stats = $account->getReportsStats($fields, $params);
        $result = array();
        foreach($stats as $stat) {
            $result[] = array(
                'group_name' => $stat->campaign_group_name,
                'account_id' => $stat->campaign_group_id,
                'spend'      => $stat->spend,
            );
        }

        return $result;
    }

    /**
     *
     */
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
