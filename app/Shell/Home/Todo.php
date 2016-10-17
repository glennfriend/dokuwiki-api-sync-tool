<?php
namespace App\Shell\Home;
use App\Shell\MainController;
use App\Model\Access;
use App\Business\Backup\SystemInfo;
use App\Business\Backup\Manager;


/**
 *
 */
class Todo extends MainController
{

    /**
     *
     */
    public function perform()
    {
        $this->toBackups();
    }

    // --------------------------------------------------------------------------------
    //  private
    // --------------------------------------------------------------------------------

    private function toBackups()
    {
        if ('yes' === getParam(0)) {
            $isExec  = true;
            $isDebug = false;
        }
        else {
            $isExec  = false;
            $isDebug = true;
            show('<< Preview Mode >>');
            show();
        }

        //
        $tablesInfos = SystemInfo::getBackupTablesInfos();
        if (!$tablesInfos) {
            echo '    ';
            echo '沒有設定 config 內容';
            echo "\n\n";
            return;
        }

        $dbUser = conf('db.mysql.user');
        $dbPwd  = conf('db.mysql.pass');
        $dbName = conf('db.mysql.db');
        $backupPath = SystemInfo::getBackupPath();
        $prefix = 'mysqldump -u '. $dbUser .' --password="'. $dbPwd .'" --databases '. $dbName . ' ';


        $lastTipSqlCommand = [];
        foreach ($tablesInfos as $tableInfo) {

            $table      = $tableInfo['table'];
            $dateField  = $tableInfo['date_field_name'];

            $manager = $this->factoryBackupManager($table, $dateField);
            $map = $manager->getBackupMap();
            $info = $manager->getTableInfoFromAccess();

            show("[{$table}]");
            if ($info['error']) {
                show('    Error: ' . $info['error']);
                show();
                continue;
            }

            $execCommandCount = 0;
            foreach ($map as $dateString => $value) {

                if ('focus' !== $value) {
                    continue;
                }

                list($dateStart, $dateEnd) = $this->getTwoDateTimebyDateString($dateString);

                $saveFile           = "{$table}.{$dateString}.running";
                $successFile        = "{$table}.{$dateString}.sql";
                $saveTo             = $backupPath . '/' . $saveFile;
                $successTo          = $backupPath . '/' . $successFile;
                $where              = "{$dateField} > '{$dateStart}' AND {$dateField} < '{$dateEnd}'";
                $condition1Command  = '--tables '. $table .' --where="'. $where .'"';
                $condition2Command  = $condition1Command . ' > "'. $saveTo .'"';
                $execCommand        = $prefix .' '. $condition2Command;


                $execCommandCount++;
                if ($isDebug) {
                    // 假裝 執行
                    show('   ' . $condition1Command);
                }
                elseif ($isExec) {
                    // 真的執行
                    show("    save -> {$successFile}");

                    shell_exec($execCommand);
                    if (!file_exists($saveTo)) {
                        show('    Fail: 原因可能是 路徑問題? 權限問題?');
                        exit;
                    }
                    rename($saveTo, $successTo);
                }

            }

            if ($execCommandCount > 0) {
                $lastTipSqlCommand[] = "DELETE FROM `{$dbName}`.`{$table}` where `{$dateField}` < '{$dateEnd}'";
            }

            if (0 === $execCommandCount) {
                show('    沒有發現資料需要備份');
            }

            show();
        }

        if ($isExec && $lastTipSqlCommand) {
            show('Tip:');
            show('    如果備份已完成, 以下做為參考的 SQL 指令, 請看清楚, 修改成適當的日期, 之後再服用');
            echo "    ";
            show(join("\n    ", $lastTipSqlCommand));
            show();
        }


    }

    /**
     *  取得所有 "需要" 備份的 tables
     */
    private function getBackupTables()
    {
        return array_column( SystemInfo::getBackupTablesInfos(), 'table');
    }

    /**
     *
     */
    private function factoryBackupManager($table, $dateField)
    {
        return new Manager(
            $table,
            $dateField,
            [
                'Access' => new Access($table, $dateField),
            ]
        );
    }

    /**
     *  yyyymm convert to [開始時間, 結束時間]
     *
     *  example
     *      200108
     *          => ['2001-08-01', '2001-08-31']
     *
     *  @param string yyyymm
     *  @return array[yyyy-mm-dd, yyyy-mm-dd]
     */
    private function getTwoDateTimebyDateString($dateString)
    {
        $year   = substr($dateString, 0, 4);
        $month  = substr($dateString, 4, 2);
        $start  = "{$year}-{$month}-01";
        $end    = date("Y-m-01", strtotime("{$start} +1 month"));
        return [$start, $end];
    }

}
