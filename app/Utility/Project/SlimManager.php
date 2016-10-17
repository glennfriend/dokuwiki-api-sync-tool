<?php
namespace App\Utility\Project;

class SlimManager
{
    protected static $app;
    protected static $request;
    protected static $response;
    protected static $args;

    /**
     *  init
     */
    public static function init($slimApp, $arguments)
    {
        self::$app      = $slimApp;
        self::$request  = $arguments[0];
        self::$response = $arguments[1];
        self::$args     = $arguments[2];
    }

    /* --------------------------------------------------------------------------------

    -------------------------------------------------------------------------------- */

    /**
     *
     */
    public static function getRouter()
    {
        return self::$app->getContainer()->get('router');
    }

    /**
     *
     */
    public static function getRequest()
    {
        return self::$request;
    }

    /**
     *
     */
    public static function getResponse()
    {
        return self::$response;
    }

}
