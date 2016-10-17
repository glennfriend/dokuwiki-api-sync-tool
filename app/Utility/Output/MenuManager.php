<?php
namespace App\Utility\Output;

use App\Model\User as User;

/**
 *  管理 menu 在後台的顯示
 */
class MenuManager
{
    /**
     *  儲存所有的 menu info
     */
    private static $_meunInfos = array();

    /**
     *  要 focus 在那個 main-menu
     */
    private static $_mainIndex = null;

    /**
     *  要 focus 在那個 sub-menu
     */
    private static $_subIndex = null;


    /* --------------------------------------------------------------------------------
        public
    -------------------------------------------------------------------------------- */

    /**
     *  init
     *  load all plugin setting
     *
     *  @param $user, user model, 因為權限關系, 能取得到的 menu 訊息會不同
     */
    public static function init(User $user)
    {
        if (self::$_meunInfos) {
            return;
        }

        // plugins 以執行先後順序排序
        $infos = array();
        foreach (self::_getMenuInfos() as $info) {
            $key        = $info['main']['key'];
            $order      = sprintf("%05d", $info['main_order']);
            $orderKey   = "s{$order}{$key}";
            $infos[$orderKey] = $info;
        }
        ksort($infos);
        $infos = array_values($infos);

        // 濾除不符合 user 的 main menu 權限
        foreach ($infos as $mainIndex => $items) {
            $role = $items['main']['role'];
            $result = $user->hasPermission($role);
            if (!$result) {
                unset($infos[$mainIndex]);
            }
        }
        $infos = array_values($infos);

        // 濾除不符合 user 的 sub menu 權限
        foreach ($infos as $mainIndex => $items) {
            foreach ($items['sub'] as $subIndex => $sub) {
                $role = $sub['role'];
                $result = $user->hasPermission($role);
                if (!$result) {
                    unset($infos[$mainIndex]['sub'][$subIndex]);
                }
            }
            array_values($infos[$mainIndex]['sub']);
        }

        self::$_meunInfos = $infos;
    }

    /**
     *  取得所有的 menu infos
     */
    public static function getMenuInfos()
    {
        return self::$_meunInfos;
    }

    /**
     *  設定要 focus 在那個 main-menu
     *
     *  @return boolean, true = 設定成功, false = 找不到這個 key 對應的 main menu
     */
    public static function setMain($key)
    {
        foreach (self::getMenuInfos() as $index => $info) {
            if ($info['main']['key']===$key) {
                self::$_mainIndex = $index;
                return true;
            }
        }
        return false;
    }

    /**
     *  取得現在 focus 在那一個 main menu
     *
     *  return array|null
     */
    public static function getFocusMain()
    {
        if (null === self::$_mainIndex) {
            return null;
        }

        $mainIndex = self::$_mainIndex;
        $menuInfos = self::getMenuInfos();
        return $menuInfos[$mainIndex]['main'];
    }

    /**
     *  取得現在 focus 在那一個 main menu subs
     *
     *  return array|null
     */
    public static function getFocusMainSubs()
    {
        if (null === self::$_mainIndex) {
            return null;
        }

        $mainIndex = self::$_mainIndex;
        $menuInfos = self::getMenuInfos();
        return $menuInfos[$mainIndex]['sub'];
    }


    /**
     *  設定要 focus 在那個 sub-menu
     *  如果之前沒有設定 focus main-menu, 則無法設定
     *
     *  @return boolean, true = 設定成功, false = 找不到這個 key 對應的 sub menu
     */
    public static function setSub($subKey)
    {
        if (null === self::$_mainIndex) {
            return false;
        }
        $mainIndex = self::$_mainIndex;

        $menuInfos = self::getMenuInfos();
        $subMenus = $menuInfos[$mainIndex]['sub'];
        foreach ($subMenus as $index => $info) {
            if ($info['key']===$subKey) {
                self::$_subIndex = $index;
                return true;
            }
        }
        return false;
    }

    /**
     *  取得現在 focus 在那一個 sub menu
     *
     *  return array|null
     */
    public static function getFocusSub()
    {
        if (null === self::$_mainIndex) {
            return false;
        }
        if (null === self::$_subIndex) {
            return false;
        }

        $mainIndex = self::$_mainIndex;
        $subIndex  = self::$_subIndex;
        $menuInfos = self::getMenuInfos();
        return $menuInfos[$mainIndex]['sub'][$subIndex];
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    /**
     *  自動掃描 menu config 目錄, 取得詳細資訊
     *  會驗證 plugin 設定檔案是否存在
     *  只會傳存在的 plugins
     *
     *  NOTE: 可以 cache 這段程式, 但必須要考慮清除機制
     *
     *  @return menu info array
     */
    private static function _getMenuInfos()
    {
        $menuFolder =  getProjectPath('/resource/menus');
        $menus = glob($menuFolder . "/*.php");

        $infos = [];
        foreach ($menus as $menuFile) {
            $info = include $menuFile;
            $errorMessage = self::validteMenuInfo($info);
            if ($errorMessage) {
                $menuFileName = basename($menuFile);
                throw new \Exception('Menu config ' . $menuFileName . ' error: ' . $errorMessage);
                exit;
            }
            $infos[] = $info;
        }
        return $infos;
    }

    /**
     *  vlidate menu info data
     *
     *  @return return null is success, return error-string is error
     */
    private static function validteMenuInfo($info)
    {

        if (!isset($info['main_order'])) {
            return 'order';
        }
        if (!is_numeric($info['main_order'])) {
            return 'order need is number';
        }

        if (!is_array($info['main'])) {
            return 'main';
        }
        if (!isset($info['main']['key'])) {
            return 'main key';
        }
        if (!isset($info['main']['label'])) {
            return 'main label';
        }
        if (!isset($info['main']['link'])) {
            return 'main link';
        }
        if (!isset($info['main']['role'])) {
            return 'main role';
        }

        if (!is_array($info['sub'])) {
            return 'sub';
        }

        // 予許空的 sub
        if (!$info['sub']) {
            return null;
        }

        foreach ($info['sub'] as $subMenu) {
            if (!isset($subMenu['key'])) {
                return 'sub key';
            }
            if (!isset($subMenu['label'])) {
                return 'sub label';
            }
            if (!isset($subMenu['link'])) {
                return 'sub link';
            }
            if (!isset($subMenu['role'])) {
                return 'sub role';
            }
        }

        return null;
    }

}
