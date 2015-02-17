<?php
#!/usr/bin/php -q

if (PHP_SAPI !== 'cli') {
    if ( '192.168.'       !== substr($_SERVER['REMOTE_ADDR'],0,8) &&
         '203.75.167.229' !== $_SERVER['REMOTE_ADDR'] )
    {
        exit;
    }
}

date_default_timezone_set('Europe/London');

error_reporting(E_ALL);
ini_set('html_errors','On');
ini_set('display_errors','On');

require_once 'config.php';
require_once 'vendor/autoload.php';
run();


/*
* This is a daily report function
*/
function run()
{
    echo get_app_access_token( APPLICATION_FACEBOOK_ID, APPLICATION_FACEBOOK_SECRET, APPLICATION_FACEBOOK_SHORT_TOKEN);
    echo "\n";
}

/*

    GET /oauth/access_token?  
        grant_type=fb_exchange_token&           
        client_id={app-id}&
        client_secret={app-secret}&
        fb_exchange_token={short-lived-token} 

    curl -G \
    -d "grant_type=fb_exchange_token" \
    -d "client_id=999" \
    -d "client_secret=aa9" \
    -d "fb_exchange_token=aA9" \
    "https://graph.facebook.com//oauth/access_token?"

*/
function get_app_access_token( $id, $secret, $token )
{
    $url = 'https://graph.facebook.com/oauth/access_token';
    $params = array(
        "grant_type" => "fb_exchange_token",
        "client_id" => $id,
        "client_secret" => $secret,
        "fb_exchange_token" => $token
    );

    $result = parse_uri(post_url( $url, $params ));
    if ( !isset($result['access_token']) ) {
        exit;
    }
    
    echo $result['access_token'];
}

/**
 *  @return path array or false
 */
function parse_uri( $uri )
{
    $url = parse_url($uri);
    if ( !isset($url) || !isset($url['path']) ) {
        return false;
    }

    $result = array();
    $path = explode("&",$url['path']);
    foreach ( $path as $item ) {
        $tmp = explode("=", $item);
        $result[$tmp[0]] = $tmp[1];
    }
    return $result;
}


/**
 *
 */
function post_url($url, $params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
}

