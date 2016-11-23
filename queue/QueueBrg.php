<?php

/**
 *  bridge Queue
 *
 *  目前提供的方式
 *      v - Gearman
 *      x - PHP-Resque
 *      x - Pheanstalk
 *
 */
class QueueBrg
{

    /**
     *  factory client
     */
    public static function factoryClient()
    {
        return new QueueBrgGearmanClient( self::getOptions() );
    }

    /**
     *  factory worker
     */
    public static function factoryWorker()
    {
        return new QueueBrgGearmanWorker( self::getOptions() );
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    /**
     *  get options
     */
    private static function getOptions()
    {
        // default
        $options = array(
            'server'  => APPLICATION_QUEUE_GEARMAN_SERVER,
            'service' => APPLICATION_QUEUE_GEARMAN_SERVICE,
            'isDebug' => false,
        );

        // 處理 server 參數
        $multipleServer = explode(',', $options['server'] );
        $options['server'] = array();
        foreach( $multipleServer as $server ) {
            $options['server'][] = trim($server);
        }

        // 處理 service 參數
        if ( $options['service'] ) {
            $service = explode(',', $options['service'] );
            $options['service'] = array();
            foreach ( $service as $name ) {
                $options['service'][] = trim($name);
            }
        }

        return $options;
    }

}

