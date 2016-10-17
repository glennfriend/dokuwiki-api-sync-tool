<?php
namespace App\Model;
use \BaseModel;

/**
 *  該程式的功能
 *  不需要 value object
 *  也不需要 dao
 *  所以這裡的寫法
 *  就直接取得資料即可
 *
 */
class Access extends \ZendModel3
{

    /**
     *
     */
    public function __construct($tableName, $fieldName)
    {
        $this->tableName    = $tableName;
        $this->myFieldName  = $fieldName;
    }

    /**
     *  取得最早日期的一筆資料
     */
    public function getFristDate()
    {
        $this->error = null;

        $sql =<<<EOD
            SELECT   *
            FROM     `{$this->tableName}`
            ORDER BY `{$this->myFieldName}` ASC
            LIMIT    1
EOD;
        $values = [];

        try {
            $results = BaseModel::getAdapter()->query($sql, $values);
        }
        catch (\Exception $e) {
            $this->setModelErrorMessage($e->getMessage());
            return [];
        }

        if (!$results) {
            return [];
        }

        $row = [];

        // 只會有一筆資料
        foreach ($results as $obj) {
            $row = $obj->getArrayCopy();
            break;
        }

        return $row;
    }

    /**
     *  取得最近日期的一筆資料
     */
    public function getLastDate()
    {
        $this->error = null;

        $sql =<<<EOD
            SELECT   *
            FROM     `{$this->tableName}`
            ORDER BY `{$this->myFieldName}` DESC
            LIMIT    1
EOD;
        $values = [];

        try {
            $results = BaseModel::getAdapter()->query($sql, $values);
        }
        catch (\Exception $e) {
            $this->setModelErrorMessage($e->getMessage());
            return [];
        }

        if (!$results) {
            return [];
        }

        $row = [];

        // 只會有一筆資料
        foreach ($results as $obj) {
            $row = $obj->getArrayCopy();
            break;
        }

        return $row;
    }

}
