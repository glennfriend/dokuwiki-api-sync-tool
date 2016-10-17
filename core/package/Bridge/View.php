<?php
namespace Bridge;

/**
 *  View 的部份, 必須針對每一種 Engine 做不同的處理
 *  雖然沒辦法做完全的無縫抽換, 但是可以盡量做到大部份的封裝
 */
class View
{
    /**
     *
     */
    protected static $_engine;

    /**
     *  init
     */
    public static function init($config=[])
    {
        self::$_engine = new Options\ViewEngine($config);
    }

    /**
     *  取得該頁面使用的 layout, 必須先設定, 沒有設定就沒有 layout
     */
    public static function getLayout()
    {
        return self::$_engine->get('layout');
    }

    /**
     *  供給 controller 來設定 layout 使用
     */
    public static function setLayout($layout)
    {
        self::$_engine->set('layout', $layout);
    }

    /**
     *  將 key, value 設定到 view 裡面
     *  提供 template 可以使用例如 $this->hello 的參數
     */
    public static function assingViewParam($key, $value)
    {
        if ('_' == substr($key,0,1)) {
            throw new \Exception('View Engine assingViewParam key error: ' . $key);
        }
        self::$_engine->$key = $value;
    }

    /**
     *  僅供給 controller 使用的 render()
     *  用來產生最後有帶 layout 的 view
     */
    public static function render($templateName, $params)
    {
        return self::$_engine->render($templateName, $params);
    }

    /**
     *  代入樣版名稱, 來取得完整的檔案路徑
     */
    public static function getPathFile($templateName)
    {
        return self::$_engine->getPathFile($templateName);
    }

}
