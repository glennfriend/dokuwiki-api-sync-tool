<?php
namespace App\Utility\Identity;

use Bridge\Session as Session;
use App\Utility\Identity\UserIdentity as UserIdentity;
use App\Model\User as User;
use App\Model\Users as Users;

/**
 *  管理登入之後的 User 自己本身
 *  由於資訊存於 session
 *  所以是等同管理 session 中的資訊
 */
class UserManager
{

    /**
     *  get User if authenticate
     *  @return Users model
     */
    public static function getUser()
    {
        $accountId = Session::get('account_id');
        if (!$accountId) {
            return false;
        }

        $users = new Users();
        return $users->getUser($accountId);
    }

    /**
     *  公開顯示 user 名稱
     *  @return string
     */
    public static function getDisplayUser()
    {
        if (UserIdentity::isLogin()) {
            $user = self::getUser();
            return $user->getUsername();
        } else {
            return '[未知的使用者]';
        }
    }

    /* ================================================================================
        dev environment or not
    ================================================================================ */

    /**
     *  是否為 開發者 權限
     *  @return boolean
     */
    public static function isDeveloper()
    {
        $myself = UserManager::getUser();
        if (!$myself) {
            return false;
        }
        return $myself->hasPermission('developer');
    }

    /**
     *  是否為 網站管理者 權限
     *  @return boolean
     */
    public static function isAdmin()
    {
        $myself = UserManager::getUser();
        if (!$myself) {
            return false;
        }
        return $myself->hasPermission(['manager', 'developer']);
    }

    /**
     *  是否擁有該權限
     *  @param array, $askPermissions
     *  @return boolean
     */
    public static function hasPermission($askPermissions)
    {
        $myself = UserManager::getUser();
        if (!$myself) {
            return false;
        }
        return $myself->hasPermission($askPermissions);
    }

    /**
     *  設定開發環境模式
     *  如果 role name 權限非 developer , 將無法設定成功
     *
     *  @param boolean mode
     *  @return boolean
     */
    public static function setDebugMode($mode)
    {
        if (!UserManager::isAdmin()) {
            return false;
        }

        if (true===$mode) {
            Session::set('is_debug', true);
        } else {
            Session::set('is_debug', false);
        }
        return true;
    }

    /**
     *  是否為除錯模式
     *  @return boolean
     */
    public static function isDebugMode()
    {
        if (true === Session::get('is_debug')) {
            return true;
        }
        return false;
    }

    /* ================================================================================

    ================================================================================ */
}
