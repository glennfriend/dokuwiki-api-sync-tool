<?php
use App\Utility\Console\CliManager as CliManager;

/**
 *  取得 command line 處理之後獲得的參數
 */
function getParam($key, $defaultValue=null)
{
    return CliManager::get($key);
}

// --------------------------------------------------------------------------------
//  output
// --------------------------------------------------------------------------------

/**
 * 將 2 維陣列直接輸出為 console table 的格式
 */
function table(Array $rows, $headers=null)
{
    if (null === $headers) {
        $headers = array_keys($rows[0]);
    }
    echo ConsoleHelper::table($headers, $rows);
}
