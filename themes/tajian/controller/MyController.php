<?php
/**
 * My Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/SiteController.php';

Class MyController extends SiteController {

    public function actionIndex($viewName = 'index', $defaultTitle = '个人中心') {
        //判断是否已经登录，自动跳转到自己的添加视频网址
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            return $this->redirect('/site/login/');
        }else if (!empty(FSC::$app['config']['multipleUserUriParse']) && FSC::$app['user_id'] != $loginedUser['username']) {
            $shareUrl = "/{$loginedUser['username']}/my/";
            return $this->redirect($shareUrl);
        }


        //获取数据
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);
        $scanResults = $scanner->getScanResults();

        //获取目录
        $menus = $scanner->getMenus();

        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $Parsedown = new Parsedown();
            $content = file_get_contents($readmeFile['realpath']);
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);
        }

        //默认显示的目录
        $cateId = $menus[0]['id'];

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = "{$defaultTitle} | " . FSC::$app['config']['site_name'];
        $params = compact(
                'cateId', 'dirTree', 'scanResults',
                'htmlReadme', 'tags', 'nickname'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //修改昵称
    public function actionSetnickname() {
        $defaultTitle = "修改昵称";
        $viewName = 'setnickname';
        return $this->actionIndex($viewName, $defaultTitle);
    }

    //分类管理
    public function actionTags() {
        $defaultTitle = "管理分类";
        $viewName = 'tags';
        return $this->actionIndex($viewName, $defaultTitle);
    }

    //添加分类
    public function actionAddtag() {
        $defaultTitle = "添加分类";
        $viewName = 'tag_new';
        return $this->actionIndex($viewName, $defaultTitle);
    }

    //管理收藏
    public function actionFavs() {
        $defaultTitle = "管理收藏";
        $viewName = 'favs';
        return $this->actionIndex($viewName, $defaultTitle);
    }

}