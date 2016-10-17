<?php
namespace App\Utility\Project;

class HeaderFactory
{

    /**
     *
     */
    public static function pipe(\Slim\Http\Response $response)
    {
        //
        // $response = $response->withAddedHeader("Aaa", "bbb");

        // TODO: CSP 請找時間轉到 config 之中
        // Content Security Policy
        // http://www.ruanyifeng.com/blog/2016/09/csp.html
        $csp = [
            "script-src 'self' 'unsafe-inline' ajax.googleapis.com ",
            "object-src 'none' ",
            "style-src  'self' 'unsafe-inline' ",
            "child-src  https: ",
        ];
        $response = $response->withAddedHeader("Content-Security-Policy", join('; ', $csp) );

        //
        return $response;
    }

}
