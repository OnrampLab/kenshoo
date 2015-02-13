<?php

//use Facebook\FacebookSession;
//use Facebook\FacebookRedirectLoginHelper;
// use FacebookAds\Object\AdAccount;

class Facebook
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
        Facebook\FacebookSession::setDefaultApplication( $this->id, $this->secret );


        //$account = new AdAccount('act_112950872167640');            
        //print_r($account);
    }



}