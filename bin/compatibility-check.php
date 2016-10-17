<?php

extension_check(array(
    'phalcon',
    'curl',
    'gd',
    'imagick',
));

dir_check(array(
    '../media/weddings/thumb',
    '../tmp/cache',
    '../tmp/log',
));

system_information();


/**
 *
 */
function extension_check($extensions)
{

    $fail = '';
    $pass = '';

    // php version
    if (version_compare(phpversion(), '5.3.11', '<')) {
        $fail .= fail('You need [PHP 5.3.11] (or greater)');
    }
    else {
        $pass .= success('You have [PHP 5.3.11] (or greater)');
    }

    // php safe mode
    if ( ini_get('safe_mode') ) {
        $fail .= fail('Safe Mode is [on]');
    }
    else {
        $pass .= success('Safe Mode is [off]');
    }

    // mysql
    preg_match('/[0-9]\.[0-9]+\.[0-9]+/', shell_exec('mysql -V'), $tmp);
    $version = $tmp[0];
    if (version_compare($version, '5.0.0', '<')) {
        $fail .= fail('You need [MySQL 5.0.0] (or greater)');
    }
    else {
        $pass .= success('You have [MySQL 5.0.0] (or greater)');
    }

    // mysql innodb
    // preg_match('/YES/', shell_exec('mysqladmin variables | grep have_innodb '), $tmp );

    // Apache mode rewrite
    if (!function_exists('apache_get_modules')) {
        $mod_rewrite =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
    }
    else {
        $modules = apache_get_modules();
        $mod_rewrite = in_array('mod_rewrite', $modules);
    }
    if ( !$mod_rewrite ) {
        $fail .= fail('You need [Apache mod_rewrite]');
    }
    else {
        $pass .= success('You have [Apache mod_rewrite]');
    }

    // php extensions
    foreach ($extensions as $extension) {
        if (!extension_loaded($extension)) {
            $fail .= fail('You are missing the ['. $extension .'] extension');
        }
        else {
            $pass .= success('You have the ['. $extension .'] extension');
        }
    }


    // display
    echo "\n";
    if ($fail) {
        echo title('Extension');
        echo $pass;
        echo $fail;
    }
    else {
        echo title('Extension Pass');
        echo $pass;
    }

}


/**
 *
 */
function dir_check($dirs)
{
    $fail = '';
    $pass = '';

    foreach ( $dirs as $dir ) {
        if ( !is_dir($dir) ) {
            $fail .= fail("{$dir} not found");
        }
        else {
            $pass .= success("{$dir} is find");
        }
    }


    // display
    echo "\n";
    if ($fail) {
        echo title('Directory');
        echo $pass;
        echo $fail;
    }
    else {
        echo title('Directory Pass');
        echo $pass;
    }

}

/**
 *  
 */
function system_information()
{
    echo "\n";
    echo title('Information');
    echo '    display_errors    = '. ini_get('display_errors')                  ."\n";
    echo '    register_globals  = '. ini_get('register_globals')                ."\n";
    echo '    post_max_size     = '. ini_get('post_max_size')                   ."\n";

    $inipath = php_ini_loaded_file();
    if ($inipath) {
        echo '    Loaded php.ini    = ' . $inipath ."\n";
    } else {
        echo '    Loaded php.ini    = A php.ini file is not loaded' ."\n";
    }

}

/**
 *  
 */
function title( $str )
{
    return "\033[1;4;33m" . $str . "\033[0m" . "\n";
}

/**
 *  
 */
function success( $str )
{
    return "    " . "\033[42m" . $str . "\033[0m" . "\n";
}

/**
 *  
 */
function fail( $str )
{
    return "    " . "\033[41m" . $str . "\033[0m" . "\n";
}
