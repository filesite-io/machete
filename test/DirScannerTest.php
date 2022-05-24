<?php
/**
 * 类 DirScanner 测试
 */
require_once __DIR__ . '/../lib/DirScanner.php';
require_once __DIR__ . '/../plugins/Parsedown.php';


class DirScannerTest extends DirScanner {

    public function secureLinkTest($path, $secret = '', $userIp = '', $pattern = '') {
        if (empty($secret)) {
            $secret = 'Tester';
        }
        if (empty($userIp)) {
            $userIp = '127.0.0.1';
        }
        $this->setNginxSecret($secret);
        $this->setUserIp($userIp);

        if (!empty($pattern)) {
            $this->setNginxSecureLinkMd5Pattern($pattern);
        }

        echo "\n";
        echo "==function secureLinkTest==\n";
        echo "secret: " . $this->getNginxSecret() . "\n";
        echo "user ip: " . $this->getUserIp() . "\n";
        echo "timeout: " . $this->getNginxSecureTimeout() . " seconds\n";
        echo "secure link md5: " . $this->getNginxSecureLinkMd5Pattern() . "\n";
        echo "path: {$path}\n";
        $url = $this->getSecureLink($path);
        echo "secure link: {$url}\n";
        echo "\n";
    }

    public function getFilePathTest($directory, $filename, $extension) {
        echo "\n";
        echo "==function getFilePathTest==\n";
        echo "directory: {$directory}\n";
        echo "filename: {$filename}\n";
        echo "extension: {$extension}\n";
        $url = $this->getFilePath($directory, $filename, $extension);
        echo "path: {$url}\n";
        $this->setNginxSecure('on', 'Tester');
        $secure_url = $this->getFilePath($directory, $filename, $extension);
        echo "secure path: {$secure_url}\n";
        $this->setNginxSecure('off');
        echo "\n";
    }


}


//--调用测试方法--
$scanner = new DirScannerTest();
//$scanner->secureLinkTest('/default/', 'foo=bar', '127.0.0.1', '{test}hello');
//$scanner->secureLinkTest('/default/', 'foo=bar', '127.0.0.1', '');
//$scanner->secureLinkTest('/default/', 'foo=bar', '127.0.0.1', '{secret} {secure_link_expires}{uri}{remote_addr}');

//$scanner->getFilePathTest('/content/小说/金庸/', '书剑恩仇录', 'md');
//$scanner->getFilePathTest('/content/小说/金庸/', '封面图', 'jpg');
//$scanner->getFilePathTest('/content/视频/游戏/', 'demo', 'm3u8');
//$scanner->getFilePathTest('/content/视频/游戏/', '推荐', 'url');
//$scanner->getFilePathTest('/content/视频/游戏/', '测试', 'mp4');

//$dirTree = $scanner->scan('./');
//$dirTree = $scanner->scan(__DIR__);

//$scanner->setWebRoot('/dogs/');
//$dirTree = $scanner->scan(__DIR__ . '/../www/dogs/', 4);

$scanner->setWebRoot('/dogs/');
$dirTree = $scanner->scan(__DIR__ . '/../www/dogs/', 5);
echo "Time cost: {$scanner->scanTimeCost} ms\n";
echo "\n";

print_r($dirTree);
echo "\n";
echo "\n";
exit;


//$readmeFile = $scanner->getDefaultReadme('目录id');
$readmeFile = $scanner->getDefaultReadme();
if (!empty($readmeFile)) {
    $readme_id = $readmeFile['id'];
    $readme_titles = $scanner->getMDTitles($readme_id);
    echo "Titles of MD file {$readme_id}:\n";
    print_r($readme_titles);
    echo "\n";
    echo "\n";

    $content = file_get_contents($readmeFile['realpath']);
    $Parsedown = new Parsedown();
    $html = $Parsedown->text($content);
    $html = $scanner->fixMDUrls($readmeFile['realpath'], $html);
    echo "{$html}\n";
    echo "\n";
    echo "\n";
}else {
    echo "No readme file.\n\n";
}


//$menus = $scanner->getMenus();
//echo "Directories:\n";
//print_r($menus);
//echo "\n";
//echo "\n";


//echo "Directories and files' tree:\n";
//print_r($dirTree);
//echo "\n";
//echo "\n";


//$scanResults = $scanner->getScanResults();
//print_r($scanResults);
//echo "\n";
//echo "\n";



