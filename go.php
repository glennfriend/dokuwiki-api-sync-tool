<?php
/**
 *  整理出所有的目錄
 *  掃描目錄中的檔案, 如果日期較新就重新寫入, 較舊就只提示訊息不寫入
 *
 */
require_once __DIR__ . '/core/bootstrap.php';

use App\Business\Dokuwiki\Info;
use App\Utility\ThirdPartyService\Dokuwiki;

// --------------------------------------------------------------------------------
//  start
// --------------------------------------------------------------------------------

$param      = getParam(0);
$isPriview  = true;
$isDebug    = false;
$isExec     = false;
if ('debug' === $param) {
    echo '<< Debug Mode >>' . "\n";
    echo "\n";
    $isPriview  = false;
    $isDebug    = true;
}
elseif ('exec' === $param) {
    echo '<< Execute Mode >>' . "\n";
    echo "\n";
    $isPriview  = false;
    $isExec     = true;
}
else {
    echo '<< Preview Mode >>' . "\n";
    echo 'arguments is `debug` or `exec`' . "\n";
    echo "\n";
}

// debug
// $infos = Info::getBasicInfos(); dd($infos);
// dd($infos);

//
$globRecursiveFunc = function($pattern, $flags = 0) use (&$globRecursiveFunc)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, $globRecursiveFunc($dir.'/'.basename($pattern), $flags));
    }
    return $files;
};

//
$userNamespace = 'talk:wiki:user:' . conf('dokuwiki.auth.user');
$parsePath = conf('parse_folder_path');
$folderList = $globRecursiveFunc($parsePath . '/*', GLOB_ONLYDIR|GLOB_ERR);
$totalFolders = count($folderList);

$totalFiles = 0;
foreach ($folderList as $folder) {

    $relativelyFolder = substr($folder, strlen($parsePath));
    $wikiFolderTag    = $userNamespace . nameToWikiTag($relativelyFolder);
    // dd_dump($relativelyFolder);
    // dd_dump($wikiFolderTag);

    $files = glob("{$folder}/*.txt", GLOB_ERR);
    foreach ($files as $file) {
        $totalFiles++;
        $relativelyFile = substr($file, strlen($parsePath));
        $forderName     = pathinfo($relativelyFile, PATHINFO_DIRNAME );
        $fileName       = pathinfo($file, PATHINFO_FILENAME);
        $wikiNameTag    = $userNamespace . nameToWikiTag($forderName) .':'. nameToWikiTag($fileName);

        if ($isExec) {

            $result = false;
            $fileMd5Hash = hash_file('md5', $file);
            $pageInfo = getPage($wikiNameTag);

            // 原本的 wiki page 已存在, 而且內容相同
            if ($pageInfo && $pageInfo['md5Hash'] === $fileMd5Hash) {

                $type = 'skip';

            }
            // 新增 or 覆蓋
            else {

                $content = file_get_contents($file);
                $result = addPage($wikiNameTag, $content);
                if ($result) {
                    $type = 'ok';
                }
                else {
                    $type = 'fail';
                }
            }

            printf("[%-4s]", $type);
            echo ' ' . $wikiNameTag . "\n";

        }
        elseif ($isDebug) {

            $fileMd5Hash = hash_file('md5', $file);
            $pageInfo = getPage($wikiNameTag);
            if (!$pageInfo) {
                $type = '[U]';
            }
            elseif ($pageInfo['md5Hash'] === $fileMd5Hash) {
                $type = '[=]';
            }
            else {
                $type = '[!]';
            }

            echo $type . ': ' . $wikiNameTag . "\n";

        }
        elseif ($isPriview) {

            echo 'file : ' . $relativelyFile . "\n";
            echo 'tag  : ' . $wikiNameTag . "\n\n";

            if ($totalFiles>5) {
                echo "因為是 Preview Mode 而中斷\n";
                exit;
            }

        }

    }

}

echo "\n";
echo "Total Folders : " . $totalFolders . "\n";
echo "Total Files   : " . $totalFiles   . "\n";

if ($isExec) {
    echo "Tip: \n";
    echo "    [ok]    寫入,       覆寫\n";
    echo "    [skip]  內容相同,   跳過\n";
    echo "    [fail]  寫入失敗,   略過\n";
}
elseif ($isDebug) {
    echo "Tip: \n";
    echo "    [=] 檔案內容 相同\n";
    echo "    [!] 檔案內容 發生了異動\n";
    echo "    [U] 檔案 不存在\n";
}

exit;




/**
 *  新增一個 wiki page
 */
function addPage($wikiTag, $content)
{
    $client = Dokuwiki::getClient();

    $attribs = [
        'sum'   => null,    // 摘要
        'minor' => true,    // ture = 這是較小的變動
    ];
    return $client->call('wiki.putPage', [$wikiTag, $content, $attribs]);
}

/**
 * 取得 wiki page information
 */
function getPage($wikiTag)
{
    $client = Dokuwiki::getClient();

    try {
        $result = $client->call('wiki.getPageInfo', [$wikiTag]);
    }
    catch (\Exception $e) {
        return [];
    }

    $result['md5Hash'] = md5(
        $client->call('wiki.getPage', [$wikiTag])
    );
    return $result;
}

/**
 *  依照目錄名稱轉為 wiki 所使用的 namespace tag 名稱
 */
function nameToWikiTag($folderName)
{
    $names = explode("/", $folderName);
    foreach ($names as $index => $name) {
        $name = preg_replace('/[%: ]+/', '-', $name);
        $name = preg_replace('/[-]+/', '-', $name);
        $name = trim($name, '/');
        $name = trim($name);
        $names[$index] = $name;
    }
    return join(":", $names);
}

