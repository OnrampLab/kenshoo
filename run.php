<?php

    include_once 'config.php';
    date_default_timezone_set(APPLICATION_TIMEZONE);

    $now = date("Y-m-d H:i:s");
    $show = "\n" . $now . "\n";
    system("echo \"{$show}\" >> /var/www/kenshoo/log.log");
    system("/root/.phpbrew/php/php-5.6.6/bin/php -q /var/www/kenshoo/google_api.php >> /var/www/kenshoo/log.log");

?>
