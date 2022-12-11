<?php
/**
 * View Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/ListController.php';

Class ViewController extends ListController {

    public function actionIndex() {
        $fileId = $this->get('id', '');
        if (!empty($fileId)) {
            $fileId = preg_replace('/\W/', '', $fileId);
        }

        //获取数据
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $scanResults = $scanner->getScanResults();
        if (empty($scanResults[$fileId])) {
            throw new Exception("404 - 文件编号 {$fileId} 找不到", 404);
        }

        //获取目录
        $menus = $scanner->getMenus();

        $titles = array();
        $htmlReadme = '';
        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            $titles = $scanner->getMDTitles($readmeFile['id']);

            $Parsedown = new Parsedown();
            $content = file_get_contents($readmeFile['realpath']);
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);
        }

        //获取目录面包屑
        $video = $scanResults[$fileId];
        $breadcrumbs = $this->getBreadcrumbs($scanResults, $video);

        //获取当前目录下的readme
        $htmlCateReadme = '';
        $cateReadmeFile = $scanner->getDefaultReadme($fileId);
        if (!empty($cateReadmeFile)) {
            $Parsedown = new Parsedown();
            $content = file_get_contents($cateReadmeFile['realpath']);
            $htmlCateReadme = $Parsedown->text($content);
            $htmlCateReadme = $scanner->fixMDUrls($cateReadmeFile['realpath'], $htmlCateReadme);
        }

        $pageTitle = $defaultTitle = !empty($titles) ? $titles[0]['name'] : FSC::$app['config']['site_name'];
        if (!empty($video)) {
            $pageTitle = "{$video['filename']}，来自{$defaultTitle}";
            if (!empty($video['title'])) {
                $pageTitle = "{$video['title']}，来自{$defaultTitle}";
            }
        }
        $viewName = 'index';
        $params = compact('fileId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme', 'video');
        return $this->render($viewName, $params, $pageTitle);
    }

}
