<?php
namespace Bridge;

class Queue
{

    /**
     *
     */
    public static function factoryWorker()
    {
        // EX. Gearman, PHP-Resque, Pheanstalk
        return new Options\QueueGearmanWorker(self::getOptions());
    }

    /**
     *
     */
    public static function factoryClient()
    {
        return new Options\QueueGearmanClient(self::getOptions());
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
        return [
            'servers'   => conf('queue.gearman.servers'),
            'services'  => conf('queue.gearman.services'),
        ];
    }

}
