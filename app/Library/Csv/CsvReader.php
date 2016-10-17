<?php
namespace App\Library\Csv;

// 下面的程式碼跟 example 不符
// 該程式由舊的寫法，要改成新的寫法

// 未開始撰寫
exit;


/*
example:

    //
    $csvFile = 'my.csv';
    $csv = new CsvReader($csvFile);
    echo "Totle Count: " . $csv->getTotalCount();

    //
    $filterHook = function($name, $value) {
        if ('money' === $name) {
            $value = sprintf("%01.2f", $value);
        }
        elseif ('age' === $name) {
            $value = (int) $value;
        }
        return $value;
    };
    $csv->hook($filterHook);

    // get by yield
    foreach ($csv->get() as $item) {
        print_r($item);
    }


$csv = 'your_file.csv';
if (($handle = fopen($csvFile, 'r')) !== false) {

    CsvManager::setHeader(fgetcsv($handle));
    CsvManager::setFilter(array(
        'money' => 'float',
        'age'   => 'int'
    ));

    while (($line = fgetcsv($handle)) !== false) {
        $item = CsvManager::map($line);
        print_r($item);
    }

    fclose($handle);
}
*/


/**
 * parse csv file
 */
class CsvReader
{
    /**
     *
     */
    private static $header = array();

    /**
     *
     */
    private static $filterSetting = array();

    /**
     *  init
     */
    public static function init()
    {
        self::$header        = array();
        self::$filterSetting = array();
    }

    /**
     *  set csv header
     */
    public static function setHeader(Array $row)
    {
        self::$header = $row;
    }

    /**
     *  save to file
     */
    public static function save($filename, Array $contents, $isWriteHeader=false )
    {
        $fp = fopen($filename, 'w');
        if ( $isWriteHeader ) {
            fputcsv($fp, self::$header);
        }
        foreach ($contents as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }

    /**
     *
     */
    public static function map($row)
    {
        $item = array_combine(self::$header, $row);

        foreach ( $item as $key => $value ) {
            foreach ( self::$filterSetting as $filterKey => $filterType ) {
                if ( $key===$filterKey ) {
                    $item[$key] = self::$filterType($value);
                }
            }
        }
        return $item;
    }

    /**
     *
     */
    public static function setFilter( Array $filterSetting )
    {
        self::$filterSetting = $filterSetting;
    }

    /**
     *  magic call getting and setting
     */
    static function __callStatic($name, $args)
    {
        $function = '_filter_'.$name;
        return self::$function($args[0]);
    }

    /* --------------------------------------------------------------------------------
        custom filter
    -------------------------------------------------------------------------------- */

    private static function _filter_float( $value )
    {
        return (float) $value;
    }

    private static function _filter_int( $value )
    {
        return (int) $value;
    }

}

/*
example:

$csv = 'your_file.csv';
if (($handle = fopen($csvFile, 'r')) !== false) {

    CsvManager::setHeader(fgetcsv($handle));
    CsvManager::setFilter(array(
        'money' => 'float',
        'age'   => 'int'
    ));

    while (($line = fgetcsv($handle)) !== false) {
        $item = CsvManager::map($line);
        print_r($item);
    }

    fclose($handle);
}
*/
