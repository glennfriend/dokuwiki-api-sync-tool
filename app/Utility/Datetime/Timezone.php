<?php
namespace App\Utility\Datetime;

/**
 *
 */
class Timezone
{
    /**
     *  取得系統設定的 Time Zone
     *  @return string;
     */
    static public function getSystem()
    {
        $timezone = date_default_timezone_get();
        if ($timezone) {
            return $timezone;
        }

        $timezone = ini_get('date.timezone');
        if ($timezone) {
            return $timezone;
        }

        return '';
    }

    /**
     *  取得所有 PHP 提供的時區資訊
     */
    static public function getAll()
    {
        static $items = null;

        if ($items === null) {
            $items = [];
            $now = new \DateTime();
            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $now->setTimezone(new \DateTimeZone($timezone));
                $offset = $now->getOffset();
                $items[$timezone] = $offset;
            }
        }
        return $items;
    }

    /**
     *  時區轉換
     *
     *  將一個 time value (int) 代入, 並給予該 temestamp 的時區, 與轉換目的之時區
     *  傳回 目的時區 timestamp
     *
     *  一個簡單的例子表示 fromat 相對 timezone 的 timestamp 值
     *      "1970-01-01 00:00:00"
     *          - Asia/Taipei           -28800  嬌正 +8
     *          - UTC                   0       嬌正 +0
     *          - America/Los_Angeles   28800   嬌正 -8
     *
     *  @string $timeString - timestamp int
     *  @string $from       - timezone string
     *  @string $to         - timezone string
     *  @return timestamp|0
     */
    static public function convert($timestamp, $from, $to)
    {
        try {
            $tz1 = new \DateTime(null, new \DateTimeZone($from));
            $tz2 = new \DateTime(null, new \DateTimeZone($to));
            $offset = $tz2->getOffset() - $tz1->getOffset();
            return $timestamp + $offset;
        }
        catch (Exception $e) {
            // error
            return 0;
        }
    }

}
