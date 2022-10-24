<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';

Class SiteController extends Controller {

    public function actionIndex() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlContact = '';  //Readme_contact.txt 说明文件内容，右侧悬浮菜单里的“联系我”
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $titles = array();
        $content = '';

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $readmeFile = $scanner->getDefaultReadme();
        
        $menus = $scanner->getMenus();

        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $titles = $scanner->getMDTitles($readmeFile['id']);
            $content = file_get_contents($readmeFile['realpath']);

            $Parsedown = new Parsedown();
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);

            if (!empty($readmeFile['contact'])) {
                $htmlContact = $Parsedown->text($readmeFile['contact']);
                $htmlContact = $scanner->fixMDUrls($readmeFile['realpath'], $htmlContact);
            }
        }

        //排序
        $sortedTree = $this->sortMenusAndDirTree($menus_sorted, $menus, $dirTree);
        if (!empty($sortedTree)) {
            $menus = $sortedTree['menus'];
            $dirTree = $sortedTree['dirTree'];
        }


        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统";
        $viewName = 'index';
        $params = compact('dirTree', 'menus', 'htmlReadme', 'htmlContact');
        return $this->render($viewName, $params, $pageTitle);
    }

}
