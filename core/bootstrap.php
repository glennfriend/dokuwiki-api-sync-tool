<?php
use App\Utility\Config\Config;
use App\Utility\Console\CliManager;

$initialize = function()
{
    global $argv;

    // --------------------------------------------------------------------------------
    //  start
    // --------------------------------------------------------------------------------

    error_reporting(-1);
    ini_set('html_errors','Off');
    ini_set('display_errors', 'Off');


    /**
     *  load global helper function
     */
    include ('global-helper.php');
    $basePath = getProjectPath();


    /**
     *  load composer
     */
    $loadComposer = function($basePath)
    {
        $autoload = $basePath . '/composer/vendor/autoload.php';
        if (!file_exists($autoload)) {
            die('Lose your composer!');
        }
        require_once ($autoload);

        // custom extend load
        $loader = new Composer\Autoload\ClassLoader();
        $loader->addPsr4('Bridge\\',    "{$basePath}/core/package/Bridge/");
        $loader->addPsr4('Ydin\\',      "{$basePath}/core/package/Ydin/");
        $loader->addPsr4('App\\',       "{$basePath}/app/");
        $loader->register();
    };
    $loadComposer($basePath);

    /**
     *  load other helper function
     */
    if (isCli()) {
        include ('cli-helper.php');
        CliManager::init($argv);
    }
    else {
        include ('web-helper.php');
    }


    // init config
    $errorMessage = Config::init(
        $basePath . '/core/config',
        $basePath . '/config.php'
    );
    if ($errorMessage) {
       show('Config Eerror: '. $errorMessage);
       exit;
    }

    if (getProjectPath() !== $basePath) {
       show('base path setting error!');
       exit;
    }

    if (isTraining()) {
        error_reporting(E_ALL);
        ini_set('html_errors', 'On');
        ini_set('display_errors', 'On');
    }

    if (isCli()) {
        ini_set('html_errors', 'Off');
        ini_set('display_errors', 'Off');
    }

    date_default_timezone_set(conf('app.timezone'));

    // --------------------------------------------------------------------------------
    //  load base DI infromation
    // --------------------------------------------------------------------------------
    /**
     *  load resorce
     */
    $loadResource = function($basePath)
    {
        $di = di();
        $di->setParameter('app.path', $basePath);

        /*
            Example:
                $di
                    ->register('example', 'Lib\Abc')
                    ->addArgument('%app.path%');                    // __construct
                    ->setProperty('setDb', [new Reference('db')]);  // ??
        */

        // log & log folder
        $di->register('log', 'Bridge\Log')
           ->addMethodCall('init', ['%app.path%/var']);

        // cache
        $di->register('cache', 'Bridge\Cache')
            ->addMethodCall('init', ['%app.path%/var/cache']);

    };
    $loadResource($basePath);

    // --------------------------------------------------------------------------------
    //  vlidate
    // --------------------------------------------------------------------------------

    if (phpversion() < '5.5') {
        show("PHP Version need >= 5.5");
        exit;
    }

};
$initialize();
unset($initialize);
