<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/SiteController.php';

Class ListController extends SiteController {

    public function actionIndex() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
        
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);
        $scanResults = $scanner->getScanResults();

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

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //获取目录面包屑
        $tagId = $cateId = $this->get('id', '');
        $tagItem = $tags[$tagId];
        $breadcrumbs = $this->getBreadcrumbs($scanResults, $tagItem);

        //根据tag获取相关数据，并传给视图；调整视图兼容tag的数据结构
        if (!empty($tags)) {
            foreach($tags as $id => $tag) {
                $scanResults[$id]['files'] = $this->getTagFiles($tag, $scanResults);
            }
        }


        $pageTitle = $defaultTitle = !empty($titles) ? $titles[0]['name'] : FSC::$app['config']['site_name'];
        if (!empty($tagItem)) {
            $pageTitle = "{$tagItem['name']}相关视频，来自{$defaultTitle}";
            if (!empty($tagItem['title'])) {
                $pageTitle = "{$tagItem['title']}，来自{$defaultTitle}";
            }
        }
        $viewName = '//site/index';     //共享视图
        $params = compact('cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme', 'tags');
        return $this->render($viewName, $params, $pageTitle);
    }

    //根据目录结构以及当前目录获取面包屑
    protected function getBreadcrumbs($menus, $tag) {
        $breads = array();

        array_push($breads, [
            'id' => $tag['id'],
            'name' => $tag['name'],
            'url' => "/list/?id={$tag['id']}",
        ]);

        return $breads;
    }

    //根据tag的filenames获取它们的files数据，数据结构保持跟原file一致
    protected function getTagFiles($tag, $scanResults) {
        $files = array();
        if (empty($tag['files'])) {return $files;}

        foreach($tag['files'] as $filename) {
            foreach($scanResults as $id => $item) {
                if (!empty($item['filename']) && $item['filename'] == $filename && $item['extension'] == 'url') {
                    $files[$id] = $item;
                }
            }
        }

        return $files;
    }

}
