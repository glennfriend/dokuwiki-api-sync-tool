<?php
namespace App\Business\Dokuwiki;
use App\Utility\ThirdPartyService\Dokuwiki;
/**
 *  Information
 */
class Info
{
    /**
     *  取得基本資訊
     */
    static public function getBasicInfos()
    {
        $client = Dokuwiki::getClient();

        try {

            $time = $client->call('dokuwiki.getTime');

        } catch (Zend\XmlRpc\Client\Exception\HttpException $e) {

            echo "wiki API error:";
            echo $e->getCode();     // returns 404
            echo "\n";
            echo $e->getMessage();  // returns "Not Found"
            echo "\n";

        }

        return [
            'title'         => $client->call('dokuwiki.getTitle'),
            'version'       => $client->call('dokuwiki.getVersion'),
            'api_version'   => $client->call('dokuwiki.getXMLRPCAPIVersion'),
            'time'          => $time,
            'time_format'   => date("Y-m-d H:i:s", $time),
        ];
    }

}
