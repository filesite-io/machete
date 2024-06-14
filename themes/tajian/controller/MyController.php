<?php
/**
 * My Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/SiteController.php';

Class MyController extends SiteController {

    public function actionIndex($viewName = 'index', $defaultTitle = '个人中心', $viewData = array()) {
        //判断是否已经登录，自动跳转到自己的添加视频网址
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            return $this->redirect('/site/login/');
        }else if (!empty(FSC::$app['config']['multipleUserUriParse']) && FSC::$app['user_id'] != $loginedUser['username']) {
            $shareUrl = "/{$loginedUser['username']}/my/" . FSC::$app['action'];
            return $this->redirect($shareUrl);
        }

        //账号切换支持
        $goDir = $this->get('dir', '');
        if (!empty($goDir) && !empty($loginedUser['cellphone'])) {
            $myDirs = Common::getMyDirs($loginedUser['cellphone'], $loginedUser['username']);
            if (in_array($goDir, $myDirs)) {
                Common::switchUserDir($goDir);
                return $this->redirect("/{$goDir}/my/");
            }
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

        //显示手机号码
        $cellphone_hide = Common::maskCellphone($loginedUser['cellphone']);

        $pageTitle = "{$defaultTitle} | " . FSC::$app['config']['site_name'];
        $params = compact(
                'cateId', 'dirTree', 'scanResults',
                'htmlReadme', 'tags', 'nickname', 'cellphone_hide'
        );

        if (!empty($viewData)) {
            $params = array_merge($params, $viewData);
        }

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
        //分类筛选支持
        $selectTag = $this->get('tag', '');
        $searchKeyword = $this->get('keyword', '');
        if (!empty($searchKeyword)) {
            $searchKeyword = Common::cleanSpecialChars($searchKeyword);
        }

        $defaultTitle = "管理收藏";
        $viewName = 'favs';
        return $this->actionIndex($viewName, $defaultTitle, compact('selectTag', 'searchKeyword'));
    }

    //分享收藏夹
    public function actionShare() {
        $defaultTitle = "分享聚宝盆";
        $viewName = 'share';
        return $this->actionIndex($viewName, $defaultTitle);
    }

    //切换收藏夹
    public function actionDirs() {
        $myDirs = $myNicks = $isMine = array();

        $loginedUser = Common::getUserFromSession();
        if (!empty($loginedUser['cellphone'])) {
            $myDirs = Common::getMyDirs($loginedUser['cellphone'], $loginedUser['username']);
            if (!empty($myDirs)) {
                foreach($myDirs as $dir) {
                    $myNicks[$dir] = Common::getNicknameByDir($dir, $loginedUser['username']);
                    $isMine[$dir] = Common::isMyFavDir($loginedUser['cellphone'], $loginedUser['username'], $dir);
                }
            }
        }

        $defaultTitle = "切换账号";
        $viewName = 'switchdir';
        return $this->actionIndex($viewName, $defaultTitle, compact('myDirs', 'myNicks', 'isMine'));
    }

    //添加收藏夹
    public function actionCreatedir() {
        //VIP身份判断
        $loginedUser = Common::getUserFromSession();
        $isVipUser = Common::isVipUser($loginedUser);

        //普通用户：每个手机号最多创建 3 个收藏夹
        $max_num = !empty(FSC::$app['config']['tajian']['max_dir_num']) ? FSC::$app['config']['tajian']['max_dir_num'] : 3;
        $max_num_vip = 20;
        //VIP用户：每个手机号最多创建 20 个收藏夹
        if ($isVipUser) {   //vip用户判断
            $max_num = $max_num_vip = !empty(FSC::$app['config']['tajian']['max_dir_num_vip']) ? FSC::$app['config']['tajian']['max_dir_num_vip'] : 20;
        }

        $defaultTitle = "添加账号";
        $viewName = 'createdir';
        return $this->actionIndex($viewName, $defaultTitle, compact('isVipUser', 'max_num', 'max_num_vip'));
    }

    //共享收藏夹
    public function actionSharedir() {
        $myDirs = $myNicks = $isMine = array();

        $loginedUser = Common::getUserFromSession();
        if (!empty($loginedUser['cellphone'])) {
            $myDirs = Common::getMyDirs($loginedUser['cellphone'], $loginedUser['username']);
            if (!empty($myDirs)) {
                foreach($myDirs as $dir) {
                    $myNicks[$dir] = Common::getNicknameByDir($dir, $loginedUser['username']);
                    $isMine[$dir] = Common::isMyFavDir($loginedUser['cellphone'], $loginedUser['username'], $dir);
                }
            }
        }

        $myShareDirs = Common::getMyShareDirs($loginedUser['cellphone'], $loginedUser['username']);

        $defaultTitle = "共享账号";
        $viewName = 'sharedir';
        return $this->actionIndex($viewName, $defaultTitle, compact('myDirs', 'myNicks', 'isMine', 'myShareDirs'));
    }

}