<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class SiteController extends Controller {

    public function actionIndex() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
        
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $scanner->setRootDir(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory']);

        //优先从缓存读取数据
        $prefix = FSC::$app['config']['theme'];
        $cacheKey = "{$prefix}_allFilesTree";
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (!empty($cachedData)) {
            $dirTree = $cachedData;
            $scanner->setTreeData($cachedData);
        }else {
            $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
            Common::saveCacheToFile($cacheKey, $dirTree);
        }

        //优先从缓存读取数据
        $cacheKey = "{$prefix}_allFilesData";
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (!empty($cachedData)) {
            $scanResults = $cachedData;
            $scanner->setScanResults($cachedData);
        }else {
            $scanResults = $scanner->getScanResults();
            Common::saveCacheToFile($cacheKey, $scanResults);
        }


        //获取目录
        $menus = $scanner->getMenus();

        $titles = array();
        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $titles = $scanner->getMDTitles($readmeFile['id']);

            $Parsedown = new Parsedown();
            $content = file_get_contents($readmeFile['realpath']);
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);
        }

        //排序
        $sortedTree = $this->sortMenusAndDirTree($menus_sorted, $menus, $dirTree);
        if (!empty($sortedTree)) {
            $menus = $sortedTree['menus'];
            $dirTree = $sortedTree['dirTree'];
        }

        $cateId = $this->get('id', $menus[0]['id']);
        $subcate = $scanResults[$cateId];

        //获取当前目录下的readme
        $cateReadmeFile = $scanner->getDefaultReadme($cateId);
        if (!empty($cateReadmeFile)) {
            $Parsedown = new Parsedown();
            $content = file_get_contents($cateReadmeFile['realpath']);
            $htmlCateReadme = $Parsedown->text($content);
            $htmlCateReadme = $scanner->fixMDUrls($cateReadmeFile['realpath'], $htmlCateReadme);
        }

        //获取默认mp3文件
        $rootCateId = $this->get('id', '');
        $mp3File = $scanner->getDefaultFile('mp3', $rootCateId);
        if (empty($mp3File)) {
            $mp3File = $scanner->getDefaultFile('mp3');
        }


        //翻页支持
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', 24);

        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统";
        if (!empty($readmeFile['title'])) {
            $pageTitle = $readmeFile['title'];
        }
        if (!empty($subcate)) {
            $pageTitle = "{$subcate['directory']}照片，来自{$pageTitle}";
        }
        $viewName = 'index';
        $params = compact(
            'cateId', 'page', 'pageSize',
            'dirTree', 'scanResults', 'menus', 'htmlReadme', 'htmlCateReadme', 'mp3File'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //清空缓存
    public function actionCleancache() {
        $prefix = FSC::$app['config']['theme'];
        $cacheKey = "{$prefix}_allFilesTree";
        Common::cleanFileCache($cacheKey);

        $cacheKey = "{$prefix}_allFilesData";
        Common::cleanFileCache($cacheKey);

        $code = 1;
        $msg = 'OK';
        return $this->renderJson(compact('code', 'msg'));
    }

}
