<?php

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookHttpable;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\Entities\AccessToken;
use Facebook\GraphUser;

use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\AbstractCrudObject;
use FacebookAds\Object\Fields\AdAccountFields;

class Fb
{
    protected $id;
    protected $secret;
    
    public function __construct( $id, $secret )
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    public function process()
    {
        $session = $this->getSession();
        if ( !$session ) {
            die('error!');
        }



        /*
        $account = new AdAccount('act_112950872167640');
        //print_r($account);
        */

        /*
        $request = new FacebookRequest( $session, 'GET', '/act_112950872167640');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        */


        // $token = $session->getAccessToken();
        $token = APPLICATION_FACEBOOK_LONG_TOKEN;
        Api::init( $this->id, $this->secret, $token );
        $api = Api::instance();

/*

// 取得 ???? 之後的 code
curl -G \
-d "client_id=369227659923524" \
-d "client_secret=原本的code" \
-d "grant_type=client_credentials" \
"https://graph.facebook.com/oauth/access_token?"

// 查看權限
curl -G \
-d "input_token=369227659923524|要求 grant_type=client_credentials 之後的code" \
-d "access_token=369227659923524|原本的code" \
"https://graph.facebook.com/debug_token?"

curl -G \
-d 'date_preset=yesterday' \
-d 'data_columns=["spend","campaign_group_name","campaign_group_id"]' \
-d 'access_token=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' \
"https://graph.facebook.com/v2.2/act_112950872167640/reportstats"


        // https://graph.facebook.com/v2.2/act_112950872167640/?access_token=
        //                                 act_112950872167640/offsitepixels
        //                                 act_112950872167640/reportstats

*/
        
/*
        $params = array(
            'date_preset'=>'last_28_days',
            'data_columns'=>"['adgroup_id','actions','spend']",
        );
        $params = array(
            'date_preset'=>'offsitepixels',
            'data_columns'=>"['id']",
        );
*/

        $account = new AdAccount('act_' . APPLICATION_FACEBOOK_ACT_ID);
        $fields = array(
            'account_id', 'total_actions', 'spend'
        );
        $params = array(
            'date_preset'=>'yesterday',
            'data_columns'=>array('spend','campaign_group_name','campaign_group_id'),
        );
        
        $account->getReportsStats($fields, $params);
        print_R(
            $account->getData()
        );

exit;
        $stats = $account->getReportsStats(array(), $params);

        foreach($stats as $stat) {
            echo $stat->impressions;
            echo $stat->actions;
        }



        exit;
    }

    private function getSession()
    {
        FacebookSession::setDefaultApplication( $this->id, $this->secret );
        $session = FacebookSession::newAppSession();

        // To validate the session:
        try {
            $session->validate();
        } catch (FacebookRequestException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
            Log::error($ex->getMessage());
            return null;
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
            Log::error($ex->getMessage());
            return null;
        }

        return $session;
    }

}