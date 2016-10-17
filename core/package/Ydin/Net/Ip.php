<?php
/**
 *  Ip
 *
 *  @version    1.0.1
 *  @category   Ydin
 *  @package    Ydin\Net
 *  @uses
 */
namespace Ydin\Net;

class Ip
{

    /**
     *  檢查輸入的 ip 是否為 內部 ip
     *
     *      127.0.0.1
     *      10.0.0.0    - 10.255.255.255  (10/8 prefix)
     *      172.16.0.0  - 172.31.255.255  (172.16/12 prefix)
     *      192.168.0.0 - 192.168.255.255 (192.168/16 prefix)
     *
     *      @see http://www.faqs.org/rfcs/rfc1918.html
     */
    public static function isPrivate( $ip )
    {
        $privateList = array(
            '/^0./',
            '/^127.0.0.1/',
            '/^192.168..*/',
            '/^172.((1[6-9])|(2[0-9])|(3[0-1]))..*/',
            '/^10..*/'
        );

        foreach ( $privateList as $private ) {
            if ( preg_match($private, $ip) ) {
                return true;
            }
        }
        return false;
    }

    /**
     *  IP string to integer
     *  not used ip2long
     *
     *  example:
     *      "192.168.0.1"
     *  
     *  @see http://php.net/manual/en/function.ip2long.php
     *  @return IPv4 address or "0"
     */
    public static function ip2long($ipString)
    {
        return sprintf("%u", ip2long($ipString));
    }

}
