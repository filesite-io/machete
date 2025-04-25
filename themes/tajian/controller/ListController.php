<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';
require_once __DIR__ . '/SiteController.php';

Class ListController extends SiteController {

    public function actionIndex() {
        //获取数据
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $scanner->setRootDir(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory']);

        //优先从缓存读取数据
        $prefix = FSC::$app['user_id'];
        $cacheKey = "{$prefix}_allFilesTree";
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (!empty($cachedData)) {
            $dirTree = $cachedData;
            $scanner->setTreeData($cachedData);
        }else {
            $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);
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

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //获取目录面包屑
        $breadcrumbs = $tagItem = array();
        $tagId = $cateId = $this->get('id', '');

        //根据tag获取相关数据，并传给视图；调整视图兼容tag的数据结构
        if (!empty($tags)) {
            if (!empty($tagId) && !empty($tags[$tagId])) {
                $tagItem = $tags[$tagId];
                $breadcrumbs = $this->getBreadcrumbs($tagItem);
            }

            foreach($tags as $id => $tag) {
                $scanResults[$id]['files'] = $this->getTagFiles($tag, $scanResults);
            }
        }

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = $defaultTitle = !empty($titles) ? $titles[0]['name'] : FSC::$app['config']['site_name'];
        if (!empty($tagItem)) {
            $pageTitle = "{$nickname}收藏的{$tagItem['name']}精选视频，来自{$defaultTitle}";
            if (!empty($tagItem['title'])) {
                $pageTitle = "{$tagItem['title']}，来自{$defaultTitle}";
            }
        }
        $viewName = '//site/index';     //共享视图
        $params = compact(
                'cateId', 'dirTree', 'scanResults', 'htmlReadme',
                'breadcrumbs', 'htmlCateReadme', 'tags', 'nickname'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //根据目录结构以及当前目录获取面包屑
    protected function getBreadcrumbs($tag) {
        $breads = array();

        array_push($breads, [
            'id' => $tag['id'],
            'name' => $tag['name'],
            'url' => "/list/?id={$tag['id']}",
        ]);

        return $breads;
    }

}
