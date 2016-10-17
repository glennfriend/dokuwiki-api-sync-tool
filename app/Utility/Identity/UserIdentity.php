<?php
namespace App\Utility\Identity;

use Bridge\Session;
use App\Utility\Identity\UserManager;
use App\Model\Users;

/**
 * Admin UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity
{
    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate( $account, $password )
    {
        if( !isset($account) ) {
            return false;
        }

        if( !isset($password) ) {
            return false;
        }

        $users = new Users();
        $user = $users->getUserByAuthenticate( $account, $password );
        if( !$user ) {
            return false;
        }

        // setting basic config
        $userIp     = trim(strip_tags($_SERVER['REMOTE_ADDR']));
        $userAgent  = trim(strip_tags($_SERVER['HTTP_USER_AGENT']));

        Session::set('account_id', $user->getId() );
        Session::set(
            'login_user_info_string',
            $user->getId() .'_'. $userIp .'_'. $userAgent
        );

        // custom setting
        // 注意, 以下的程式要放在此 method 最後面
        // 依照情況設定
        if( UserManager::isDeveloper() ) {
            UserManager::setDebugMode(true);
        }
        return true;
    }

    /**
     * check is login
     * @return boolean
     */
    public static function isLogin()
    {
        $accountId = Session::get('account_id');
        if( !$accountId ) {
            return false;
        }
        return true;
    }

    /**
     * destory session
     */
    static public function logout()
    {
        Session::destroy();
    }

}
