<?php
namespace Bridge\Options;

class QueueGearmanClient
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
    public function __construct( $options=array() )
    {
        $this->client = new \GearmanClient();
        $this->options = $options;

        $multipleServer = $this->options['servers'];
        foreach( $multipleServer as $server ) {

            $mac = preg_split("/:/",$server);
            if ( !is_array($mac) ) {
                continue;
            }

            // $mac[0] is host or ip
            // $mac[1] is port
            if (1===count($mac)) {
                $this->client->addServer($mac[0]);
            }
            elseif (2===count($mac)) {
                $this->client->addServer($mac[0], $mac[1]);
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
    public function push($job, $data=array())
    {
        $service = $this->options['services'];
        if (!in_array($job, $service)) {
            return false;
        }
        return $this->client->doNormal($job, serialize($data));
    }

    /**
     *  背景執行, 不會等待執行結果
     */
    public function pushBackground($job, $data=Array())
    {
        $service = $this->options['services'];
        if (!in_array($job, $service)) {
            return false;
        }
        return $this->client->doBackground($job, serialize($data));
    }

}


/*
laravel
    - push
    - fire(刪除已處理的任務)
    - release(重新處理被fire的任務)

*/
