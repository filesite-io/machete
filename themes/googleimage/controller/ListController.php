<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';

Class ListController extends Controller {

    public function actionIndex() {
        //获取数据
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $scanResults = $scanner->getScanResults();

        //获取目录
        $menus = $scanner->getMenus();
        $cateId = $this->get('id', $menus[0]['id']);

        $titles = [];
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
        $subcate = $scanResults[$cateId];
        $breadcrumbs = $this->getBreadcrumbs($menus, $subcate);

        //获取当前目录下的readme
        $htmlCateReadme = '';
        $cateReadmeFile = $scanner->getDefaultReadme($cateId);
        if (!empty($cateReadmeFile)) {
            $Parsedown = new Parsedown();
            $content = file_get_contents($cateReadmeFile['realpath']);
            $htmlCateReadme = $Parsedown->text($content);
            $htmlCateReadme = $scanner->fixMDUrls($cateReadmeFile['realpath'], $htmlCateReadme);
        }

        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统";
        if (!empty($subcate)) {
            $pageTitle = "{$subcate['directory']}的照片，来自{$pageTitle}";
            if (!empty($subcate['title'])) {
                $pageTitle = $subcate['title'];
            }
        }
        $viewName = '//site/index';     //共享视图
        $params = compact('cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme');
        return $this->render($viewName, $params, $pageTitle);
    }

    //根据目录结构以及当前目录获取面包屑
    protected function getBreadcrumbs($menus, $subcate) {
        $breads = [];

        array_push($breads, [
            'id' => $subcate['id'],
            'name' => $subcate['directory'],
            'url' => $subcate['path'],
        ]);

        $foundKey = array_search($subcate['pid'], array_column($menus, 'id'));
        if ($foundKey !== false) {
            array_unshift($breads, [
                'id' => $menus[$foundKey]['id'],
                'name' => $menus[$foundKey]['directory'],
                'url' => $menus[$foundKey]['path'],
            ]);
        }

        return $breads;
    }

}
