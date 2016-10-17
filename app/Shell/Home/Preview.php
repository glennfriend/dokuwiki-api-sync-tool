<?php
namespace App\Shell\Home;
use App\Shell\MainController;
use App\Model\Access;
use App\Business\Backup\SystemInfo;
use App\Business\Backup\Manager;

/**
 *
 */
class Preview extends MainController
{

    /**
     *
     */
    public function perform()
    {
        show('<< Display Mode >>');
        show('');

        show('Current');
        show('    ' . date('Y-m-d H:i:s'));
        show('');

        show('Backup Path');
        show('    ' . SystemInfo::getBackupPath());
        show('');

        show('備份現況');
        $this->showBackups();

        show('資料庫現況');
        $this->showDatabasesInfos();

        show('提示');
        show('    如果要 "預覧", 請在 command line 後面加上 `debug` 參數');
        show('    如果要 "執行", 請在後面加上 `yes` 參數');
        show('');
    }

    // --------------------------------------------------------------------------------
    //  private
    // --------------------------------------------------------------------------------

    /**
     *  取得備份目錄的檔案
     *  比對之後顯示相關資訊
     */
    private function showBackups()
    {
        $tablesInfos = SystemInfo::getBackupTablesInfos();
        if (!$tablesInfos) {
            echo '    ';
            echo '沒有設定 config 內容';
            echo "\n\n";
            return;
        }

        foreach ($tablesInfos as $tableInfo) {

            $table      = $tableInfo['table'];
            $dateField  = $tableInfo['date_field_name'];

            echo '    ';
            echo $table;
            echo "\n";

            $manager = $this->factoryBackupManager($table, $dateField);
            $map = $manager->getBackupMap();
            $index = 1;

            foreach ($map as $key => $value) {

                $key = (string) $key;
                $year  = substr($key, 0, 4);
                $month = substr($key, 4, 2);

                if (1===$index) {
                    echo '        ';
                    echo $year. ' :';
                }

                if ('yes' === $value) {
                    echo ' ' . $month;
                }
                elseif ('no' === $value) {
                    echo ' __';
                }
                elseif ('future' === $value) {
                    // 未來的事, 請忽略
                    echo '   ';
                }
                elseif ('focus' === $value) {
                    echo ' **';
                }
                else {
                    echo ' ??';
                }

                $index++;
                if ($index > 12) {
                    $index = 1;
                    echo "\n";
                }
            }

            if (!$map) {
                // 沒有之前的備份
                // 也沒有需要做備份的資料
                // 沒有辦法做備份
                echo "        Error ?";
                echo "\n\n";
                continue;
            }

            echo "\n";
        }

        echo <<<EOD
    [Tip]
        01 ~ 12 - Backuped
        __      - Not backup
        **      - 這次備份的目標, 即使沒有該月份的資料, 一樣會產生備份檔
EOD;
        echo "\n\n";

    }

    /**
     *  資料庫 資料 現況
     */
    private function showDatabasesInfos()
    {
        foreach (SystemInfo::getBackupTablesInfos() as $tableInfo) {

            $table      = $tableInfo['table'];
            $dateField  = $tableInfo['date_field_name'];

            $manager = $this->factoryBackupManager($table, $dateField);
            $infos = $manager->getTableInfoFromAccess();

            $table      = $infos['table'];
            $dateField  = $infos['date_field_name'];
            $error      = $infos['error'];

            echo '    ';
            echo $table;
            echo "\n";

            if ($error) {
                echo '        ';
                echo $error;
                echo "\n\n";
                continue;
            }

            $firstDate  = $infos['results']['first'];
            $lastDate   = $infos['results']['last'];

            echo '        ';
            echo 'first : ';
            echo $firstDate;
            echo "\n";

            echo '        ';
            echo 'last  : ';
            echo $lastDate;
            echo "\n";

            echo "\n";
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

}
