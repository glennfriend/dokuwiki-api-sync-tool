<?php
namespace App\Utility\Url;

/**
 *  路徑管理
 *      - Url Generator
 *
 *  @see https://en.wikipedia.org/wiki/Uniform_Resource_Identifier
 */
class HomeUrlManager
{

    /**
     *  儲存基本路徑資訊
     */
    protected static $data = [];

    /**
     *
     */
    public static function init($option)
    {
        self::$data = [
            'basePath'  => $option['basePath'],
            'baseUrl'   => $option['baseUrl'],
            'host'      => $option['host'],
        ];
    }

    /**
     *  傳回網站入口基本路徑
     */
    public static function getUrl($pathFile='')
    {
        return self::$data['baseUrl'] . $pathFile;
    }

    public static function getHost()
    {
        return self::$data['host'];
    }

    /* ================================================================================
        extends
    ================================================================================ */

    /**
     *
     */
    public static function createUrl($segment, $args=[])
    {
        $url = self::$data['baseUrl'] . $segment;
        if (!$args) {
            return $url;
        }
        $query = [];
        foreach ($args as $key => $value) {
            $query[] = $key .'='. $value;
        }
        return $url . '?' . join('&', $query);
    }

    /* ================================================================================
        產生專案以外的網址
    ================================================================================ */

    // public function getxxxxxx()




}
