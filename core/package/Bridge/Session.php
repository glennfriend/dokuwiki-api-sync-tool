<?php
namespace Bridge;

/**
 *  Session
 *      - 在未過期的情況下進入網頁, session expire 會自動延長時間
 */
class Session
{

    /**
     *  init
     *  @return boolean true=已經過期, false=未過期
     */
    public static function init($opt=[])
    {
        /**
         *  2h = 2 * 60 * 60 =  7200
         *  3h = 3 * 60 * 60 = 10800
         */
        $opt += [
            'sessionPath'   => '',
            'expire'        => 10800,
        ];

        if(!$opt['sessionPath']) {
            throw new \Exception('Error: Session path not setting!');
        }
        ini_set('session.save_path', $opt['sessionPath']);

        /*
            ini_set('session.gc_maxlifetime',  $expire );
            ini_set('session.cookie_lifetime', $expire );
            session_set_cookie_params($expire);

            if (isset($_COOKIE['PHPSESSID']) && ''!==$_COOKIE['PHPSESSID']) {
                setcookie('PHPSESSID', session_id(), time() + $expire, '/');
            }

            print_r(session_get_cookie_params());
        */


        session_start();

        $sessionExpire = self::get('session_expire');
        $isExpire = false;
        if ($sessionExpire) {
            if (time() >= $sessionExpire) {
                self::destroy();
                $isExpire = true;
            }
        }
        else {
            // first time
        }

        self::set('session_expire', time() + $opt['expire']);
        return $isExpire;
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  get session by key
     */
    public static function get($key, $defaultValue=null)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return $defaultValue;
    }

    public static function getAll()
    {
        return $_SESSION;
    }

    /*
        // 支援使用 "." 的方式取得多維陣列下的值 => get('user.name')
        getDot()
    */

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  set
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     *  remove
     */
    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     *  destroy all
     */
    public static function destroy()
    {
        session_destroy();
    }

}
