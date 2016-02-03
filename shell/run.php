<?php

    include_once dirname(__DIR__) . '/config.php';
    date_default_timezone_set(APPLICATION_TIMEZONE);

    $now = date("Y-m-d H:i:s");
    // $show = $now . "\n";
    system("echo \"{$now}\" >> /var/www/kenshoo/tmp/log.log");
    system("/root/.phpbrew/php/php-5.6.6/bin/php -q /var/www/kenshoo/shell/ad-groups.php exec >> /var/www/kenshoo/tmp/log.log");



