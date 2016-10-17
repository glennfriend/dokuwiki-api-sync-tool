<?php
namespace App\Utility\Config;

/**
 *  Config
 *
 *  一般的設定值放置於特定目錄
 *  以一定的結構來分散、分類、管理
 *
 *  私密性高的設定統一放在一檔案中 (像是 laravel .env 這樣的檔案)
 *  來覆蓋原本 "一般設定值" 的位置
 *
 */
class Config
{

    /**
     *
     */
    protected static $cf = [];

    /**
     *  @param string $configPath -> 一般設定檔的目錄
     *  @param string $configFile -> 私密設定檔案
     *
     *  @return error-message-string|null
     */
    public static function init($folderPath, $configFile)
    {
        // 一般設定值
        if (!file_exists($folderPath)) {
            return 'config folder not found';
        }

        foreach (glob("{$folderPath}/*.php") as $file) {
            $filename = basename($file);
            $key = substr( $filename, 0, strlen($filename)-4 );
            self::$cf[$key] = include($file);
        }

        if (!self::$cf) {
            return 'config folder is empty';
        }

        // 私密設定檔案
        if (!file_exists($configFile)) {
            return 'config file not found';
        }

        // 私密設定值 覆蓋 一般設定值
        $configs = include($configFile);
        if (is_array($configs) && !empty($configs)) {
            self::$cf = self::array_merge_recursive_distinct(self::$cf, $configs);
        }

        return null;
    }

    /**
     *  同 soft()
     *  如果資料不存在 或是值為 null, 直接顯示錯誤訊息
     *
     *  @param int|string - $key
     *  @return any
     */
    public static function get($key)
    {
        $value = self::soft($key);
        if ( null === $value ) {
            throw new \Exception("Error: config [{$key}] not found!");
        }
        return $value;
    }

    /**
     *  使用 '.' 符號的方式取得陣列中的資料
     *
     *  example:
     *
     *      get('vivian')          -> $array['vivian'], 若無值, 則傳回 null
     *      get('vivian', 'guest') -> $array['vivian'], 若無值, 則傳回 'guest' string
     *      get('vivian.age')      -> $array['vivian']['age']
     *      get('vivian.0')        -> $array['vivian'][0]
     *
     *  @see    laravel array_get
     *  @param  int|string - $key
     *  @param  any        - $default
     *  @return any
     */
    public static function soft( $key, $default=null )
    {
        $data = self::$cf;
        if (is_null($key)) {
            return $default;
        }
        if (isset($data[$key])) {
            return $data[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($data) or ! array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }

    // --------------------------------------------------------------------------------
    //  private
    // --------------------------------------------------------------------------------

    /**
     *  合併兩個陣列
     *  @see https://gist.github.com/josephj/5028375
     *
     *  @param array $array1
     *  @param array $array2
     *  @return array
     *  @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     *  @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    private static function array_merge_recursive_distinct(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => $value)
        {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
            {
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            }
            else
            {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

}
