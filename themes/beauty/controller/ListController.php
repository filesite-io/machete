<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class ListController extends Controller {

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

        //获取目录面包屑
        $cateId = $this->get('id', $menus[0]['id']);
        $subcate = $scanResults[$cateId];
        $breadcrumbs = $this->getBreadcrumbs($menus, $subcate);

/*
header('Content-type: text/html; charset=utf-8');
print_r($subcate);
print_r($breadcrumbs);
print_r($menus);exit;
*/

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
        if (!empty($subcate)) {
            $pageTitle = "{$subcate['directory']}的照片，来自{$pageTitle}";
            if (!empty($subcate['title'])) {
                $pageTitle = $subcate['title'];
            }
        }
        $viewName = '//site/index';     //共享视图
        $params = compact(
            'cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme',
            'mp3File', 'page', 'pageSize'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //实现php 5.5开始支持的array_column方法
    protected function array_column($arr, $col) {
        $out = array();

        if (!empty($arr) && is_array($arr) && !empty($col)) {
            foreach ($arr as $index => $item) {
                if (!empty($item[$col])) {
                    array_push($out, $item[$col]);
                }
            }
        }

        return $out;
    }

    //根据pid在目录数组里找出对应的父目录数据
    protected function getParentCateByPid($pid, $cates) {
        $parent = array();

        foreach ($cates as $index => $item) {
            if ($item['id'] == $pid) {
                $parent = $item;
                break;
            }else if (!empty($item['directories'])) {
                $parent = $this->getParentCateByPid($pid, $item['directories']);
                if (!empty($parent)) {break;}
            }
        }


        return $parent;
    }

    //根据目录结构以及当前目录获取面包屑
    protected function getBreadcrumbs($menus, $subcate) {
        $breads = array();

        array_push($breads, [
            'id' => $subcate['id'],
            'name' => $subcate['directory'],
            'url' => $subcate['path'],
        ]);

        $pid = $subcate['pid'];
        $parentCate = $this->getParentCateByPid($pid, $menus);
        while (!empty($parentCate)) {
            array_unshift($breads, [
                'id' => $parentCate['id'],
                'name' => $parentCate['directory'],
                'url' => $parentCate['path'],
            ]);

            $pid = $parentCate['pid'];
            $parentCate = $this->getParentCateByPid($pid, $menus);
        }

        return $breads;
    }

}
