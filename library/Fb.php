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
//print_r($session);
echo $session->getAccessToken();
//$request = new FacebookRequest($session, 'GET', '/me/accounts?fields=name,access_token,perms');
$request = new FacebookRequest($session, 'GET', '/me');
//print_r($request);
print_r(get_class_methods($request));
$response = $request->execute();
print_r($response);
echo '------------------';
*/

        // graph api request for user data
        /*
        $request = new FacebookRequest( $session, 'GET', '/me' );
        
        */

        /*
        // get response
        $graphObject = $response->getGraphObject();
   
        // print data
        echo  print_r( $graphObject, 1 );
        */


/*
        $helper = new FacebookRedirectLoginHelper('http://www.simplybridal.com/', $this->id, $this->secret );
print_r($helper);
print_r(get_class_methods($helper));
        echo '<a href="' . $helper->getLoginUrl() . '">Login with Facebook</a>';
*/

/*
        try {
            $helper = new FacebookRedirectLoginHelper('/', $this->id, $this->secret );
          //$helper = new FacebookRedirectLoginHelper('/');
        }
        catch( Exception $e) {
            echo '<pre>';
            print_r($e);
        }
*/


        /*
        $account = new AdAccount('act_112950872167640');
        //print_r($account);
        */

        /*
        $request = new FacebookRequest( $session, 'GET', '/act_112950872167640');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        */

        Api::init( $this->id, $this->secret, $session->getAccessToken() );
        $api = Api::instance();

/*
        $account = new AdAccount('act_112950872167640');

        $params = array(
            'date_preset'=>'last_28_days',
            'data_columns'=>"['adgroup_id','actions','spend']",
        );
        $params = array(
            'date_preset'=>'yesterday',
            'data_columns'=>"['spend','campaign_group_name','campaign_group_id']",
        );

        $stats = $account->getReportsStats(array(), $params);

        foreach($stats as $stat) {
            echo $stat->impressions;
            echo $stat->actions;
        }
*/


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