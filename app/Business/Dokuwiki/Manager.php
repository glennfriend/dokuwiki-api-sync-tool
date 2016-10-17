<?php
namespace App\Business\Backup;
use App\Business\Backup\Calendar;

/**
 *  管理 Backup 機制
 */
class Manager
{

    const YEAR_MIN_BETWEEN = 5;

    /**
     *  @param string $table     table name
     *  @param string $dateField create timestamp
     *  @param array  $apis      many api class
     */
    public function __construct($table, $dateField, Array $apis)
    {
        $this->table     = $table;
        $this->dateField = $dateField;
        $this->apis      = [
            'Access' => $apis['Access']
        ];
    }

    /**
     *  將整個 備份表 在 視覺上 結構化
     *
     *  備份的開始
     *      - 取決於最後一次備份的檔案 (檔案名稱中有標示 檔案日期)
     *
     *  如果沒有任何備份
     *      - 以資料表中最新的那一個日期 做為開始
     *
     */
    public function getBackupMap()
    {
        $map = [];
        $currentDate = date('Ym');
        $tableName = $this->table;

        $myDates = SystemInfo::getBackupDatesByTableName($tableName);
        if ($myDates) {
            // 曾經備份過
            $firstYear   = (int) SystemInfo::getFirstYearByTableName($tableName);
            $currentYear = (int) date('Y');

            // 最後一次備份的日期 yyyy-mm
            $lastBackupDate = SystemInfo::getLastBackupByTableName($tableName);
        }
        else {
            // 從來沒有備份過
            // 就要以資料庫最早的時間, 做為備份的 開始 時間
            $tableInfo = $this->getTableInfoFromAccess();
            $firstDate = $tableInfo['results']['first'];
            if (!$firstDate) {
                // database error
                return [];
            }

            $firstYear   = (int) substr($firstDate, 0, 4);
            $currentYear = (int) date('Y');

            // 最後一次備份的日期 yyyy-mm
            // 必須使用資料表最早的那個日期
            $lastBackupDate = substr($firstDate, 0, 4) . substr($firstDate, 5, 2);
        }
        // dd_dump($myDates);
        // dd_dump($lastBackupDate);

        $map = Calendar::buildBetweenArray($firstYear, $currentYear);
        foreach ($map as $key => $value) {
            if ($key >= $currentDate) {
                $map[$key] = 'future';
            }
            elseif (in_array($key, $myDates)) {
                $map[$key] = 'yes';  // backuped
            }
            else {
                // 這裡的情況有兩種
                // 1. 以前未備份
                // 2. 目標備份
                if ($key >= $lastBackupDate) {
                    $map[$key] = 'focus';
                }
                else {
                    $map[$key] = 'no';
                }
            }
        }

        return $map;
    }

    /**
     *  get 資料表 現況
     */
    public function getTableInfoFromAccess()
    {
        $access = $this->apis['Access'];
        $result = [
            'table'             => $this->table,
            'date_field_name'   => $this->dateField,
            'error'             => '',
            'results' => [
                'first' => '',
                'last'  => '',
            ]
        ];

        $row = $access->getFristDate();
        $errorMessage = $access->getModelError();
        if ($errorMessage) {
            $result['error'] = 'Database Query Error: ' . $errorMessage;
            return $result;
        }

        $currentYear = date('Y');
        $yearMin = $currentYear - self::YEAR_MIN_BETWEEN;

        $result['results']['first'] = $row[$this->dateField];
        if ($result['results']['first'] <= $yearMin) {
            $result['results']['first'] = $yearMin . '-01-01';
        }

        $row = $access->getLastDate();
        $result['results']['last'] = $row[$this->dateField];

        return $result;
    }

    // --------------------------------------------------------------------------------
    //  private
    // --------------------------------------------------------------------------------



}