<?php

    function perform( $data )
    {
        // 5分鐘
        $sleepTime = 5*60;
        echo "sleep {$sleepTime}\n";
        sleep($sleepTime);

        system("/root/.phpbrew/php/php-5.6.5/bin/php -q /var/www/kenshoo/google_api.php >> /var/www/kenshoo/log.log");
    }

