<?php
namespace App\Business\Backup;

/**
 *  array structural tool
 *
 *  一個有 年份 and 月份
 *  的陣列結構
 *
 */
class Calendar
{
    /**
     *  代入類似以下的 年份 資料
     *      參數一 '2001'
     *      參數二 '2002'
     *
     *  產似類似以下的 完整年月份 結果
     *
     *      [
     *          '200101' => null,
     *          '200102' => null,
     *          .... 略過
     *          '200211' => null,
     *          '200212' => null,
     *      ]
     *
     */
    static public function buildBetweenArray($startYear, $endYear)
    {
        $startYear   = (int) $startYear;
        $endYear     = (int) $endYear;
        $currentYear = (int) date('Y');
        $maxYear     = $currentYear + 30;

        if ($startYear < 1900) {
            return [];
        }
        if ($endYear > $maxYear) {
            return [];
        }
        if ($startYear > $endYear) {
            return [];
        }

        $results = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $key = $year . sprintf("%02d", $month);
                $results[$key] = null;
            }
        }

        return $results;
    }
}
