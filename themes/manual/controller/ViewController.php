<?php
/**
 * View Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class ViewController extends Controller {

    public function actionIndex() {
        $fileId = $this->get('id', '');
        if (!empty($fileId)) {
            $fileId = preg_replace('/\W/', '', $fileId);
        }

        //获取数据
        $titles = array();
        $content = '';
        $html = '';
        $author = '';
        $publishTime = $updateTime = 0;

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);

        $scanResults = $scanner->getScanResults();
        if (!empty($scanResults[$fileId])) {
            $readmeFile = $scanResults[$fileId];
            $titles = $scanner->getMDTitles($readmeFile['id']);
            $content = file_get_contents($readmeFile['realpath']);

            $Parsedown = new Parsedown();
            $html = $Parsedown->text($content);
            $html = $scanner->fixMDUrls($readmeFile['realpath'], $html);

            $publishTime = Common::getFileCreateTime($readmeFile);
            $updateTime = $readmeFile['fstat']['atime'];
            if (!empty($readmeFile['author'])) {
                $author = $readmeFile['author'];
            }
        }else {
            throw new Exception("404 - 文件编号 {$fileId} 找不到", 404);
        }

        $pageTitle = !empty($titles) ? $titles[0]['name'] : "No title, 无标题";
        $viewName = 'index';
        $params = compact('titles', 'content', 'html', 'author', 'publishTime', 'updateTime');
        return $this->render($viewName, $params, $pageTitle);
    }

}
