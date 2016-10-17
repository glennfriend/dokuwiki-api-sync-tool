<?php
namespace App\Utility\ThirdPartyService;

/**
 *  dokuwiki api basic manager
 */
class Dokuwiki
{

    /**
     *
     */
    public static function getClient()
    {
        static $client;
        if ($client) {
            return $client;
        }
        $client = self::factoryClient();
        return $client;
    }

    /* ================================================================================
        private
    ================================================================================ */

    /**
     *
     */
    private static function factoryClient()
    {
        $http = new \Zend\Http\Client();
        $http->setAuth(
            conf('dokuwiki.auth.user'),
            conf('dokuwiki.auth.password')
        );

        $client = new \Zend\XmlRpc\Client(conf('dokuwiki.auth.url'));
        $client->setHttpClient($http);
        return $client;
    }



}
