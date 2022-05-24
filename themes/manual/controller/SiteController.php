<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';

Class SiteController extends Controller {

    public function actionIndex() {
        //获取数据
        $titles = [];
        $content = '';
        $html = '';

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $readmeFile = $scanner->getDefaultReadme();
        
        if (!empty($readmeFile)) {
            $titles = $scanner->getMDTitles($readmeFile['id']);
            $content = file_get_contents($readmeFile['realpath']);

            $Parsedown = new Parsedown();
            $html = $Parsedown->text($content);
            $html = $scanner->fixMDUrls($readmeFile['realpath'], $html);
        }

        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统";
        $viewName = 'index';
        $params = compact('titles', 'content', 'html');
        return $this->render($viewName, $params, $pageTitle);
    }

}
