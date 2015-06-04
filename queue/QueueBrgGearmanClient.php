<?php


class QueueBrgGearmanClient
{

    /**
     *  store client
     */
    private $client;

    /**
     *  options
     */
    private $options;

    /**
     *  client init
     */
    public function __construct( array $options=array() )
    {
        $this->client = new GearmanClient();
        $this->options = $options;

        $multipleServer = $this->options['server'];
        foreach( $multipleServer as $server ) {

            $mac = preg_split("/:/",$server);
            if ( !is_array($mac) ) {
                continue;
            }

            // $mac[0] is host or ip
            // $mac[1] is port
            if ( 1===count($mac) ) {
                $this->client->addServer( $mac[0] );
            }
            elseif ( 2===count($mac) ) {
                $this->client->addServer( $mac[0], $mac[1] );
            }
            else {
                $this->client->addServer();
            }
        }
    }

    /* --------------------------------------------------------------------------------
        do job
    -------------------------------------------------------------------------------- */

    /**
     *  直接執行, 等待執行結果
     */
    public function push( $job, $data=array() )
    {
        $service = $this->options['service'];
        if( !in_array( $job, $service ) ) {
            return false;
        }
        return $this->client->doNormal( $job, serialize($data) );
    }

    /**
     *  背景執行, 不會等待執行結果
     */
    public function pushBackground( $job, $data=Array() )
    {
        $service = $this->options['service'];
        if( !in_array( $job, $service ) ) {
            return false;
        }
        return $this->client->doBackground( $job, serialize($data) );
    }

}

