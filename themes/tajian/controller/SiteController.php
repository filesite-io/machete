<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';
require_once __DIR__ . '/../../../plugins/TajianStats.php';

Class SiteController extends Controller {

    //广告跟踪，通过cookie保存广告参数
    protected function trackAdParameters() {
        if (empty(FSC::$app['config']['ad_tracker']) || empty(FSC::$app['config']['ad_tracker']['enable'])) {
            return false;
        }

        $params = FSC::$app['config']['ad_tracker']['parameters'];
        if (empty($params)) {
            return false;
        }

        $adParaData = array();
        foreach ($params as $key) {
            $val = $this->get($key);
            if (!empty($val)) {
                $adParaData[$key] = $val;
            }
        }

        if (!empty($adParaData)) {
            //30天内有效
            setcookie('ad_tracker', json_encode($adParaData), time() + 86400*30, '/');
        }
    }

    //广告跟踪回调
    protected function adTrackPostBack() {
        if (empty(FSC::$app['config']['ad_tracker']) || empty(FSC::$app['config']['ad_tracker']['enable'])) {
            return false;
        }

        $params = FSC::$app['config']['ad_tracker']['parameters'];
        if (empty($params)) {
            return false;
        }

        $postbackApi = FSC::$app['config']['ad_tracker']['postbackApi'];
        if (empty($postbackApi)) {
            return false;
        }

        //为追加get参数做准备
        if (strpos($postbackApi, "?") === false) {
            $postbackApi .= "?timestamp=" . time();
        }

        //从cookie中获取跟踪到的广告参数
        $adParaDataFromCookie = !empty($_COOKIE['ad_tracker']) ? json_decode( urldecode($_COOKIE['ad_tracker']), true ) : array();

        //用记录下来的广告参数值替换回调API中的变量
        $postbackParaMap = FSC::$app['config']['ad_tracker']['postbackParaMap'];
        if (!empty($postbackParaMap)) {
            foreach ($postbackParaMap as $find => $replace) {
                if (!empty($adParaDataFromCookie[$replace])) {
                    $postbackApi = str_replace("{{$find}}", $adParaDataFromCookie[$replace], $postbackApi);
                }
            }
        }

        //把广告参数追加到回调API网址中
        /*
        foreach($adParaDataFromCookie as $key => $val) {
            $postbackApi .= "&{$key}=" . urlencode($val);
        }
        */

        //GET方式请求回调API
        $timeout = 10;
        return $this->request($postbackApi, null, $timeout);
    }

    //增加cookie跟踪同意/不同意选择，确保用户知道cookie跟踪了哪些数据
    //在3个页面显示cookies协议：首页、注册、登录
    public function actionIndex() {
        if (function_exists('mb_strlen') == false) {
            throw new Exception('Please install php extension php-mbstring first!', 500);
        }

        //只在广告着陆页跟踪广告参数
        $this->trackAdParameters();

        //如果没有开启多用户支持，或者当前用户不为空
        if (empty(FSC::$app['config']['multipleUserUriParse']) || !empty(FSC::$app['user_id'])) {
            if (!empty(FSC::$app['user_id']) && Common::existCurrentUser() == false) {
                $user_id = FSC::$app['user_id'];
                throw new Exception("你要访问的用户 {$user_id} 聚宝盆不存在！", 404);
            }

            return $this->renderFavVideos();
        }else {
            return $this->renderTajianIndex();
        }
    }

    //显示当前用户收藏的视频
    protected function renderFavVideos() {
        $loginedUser = Common::getUserFromSession();

        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
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


        //获取目录
        $menus = $scanner->getMenus();

        $titles = array();
        $htmlReadme = '';
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

        //默认显示的目录
        $cateId = $menus[0]['id'];

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //根据tag获取相关数据，并传给视图；调整视图兼容tag的数据结构
        if (!empty($tags)) {
            foreach($tags as $id => $tag) {
                $scanResults[$id]['files'] = $this->getTagFiles($tag, $scanResults);
            }
        }

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = $defaultTitle = "{$nickname}的聚宝盆 | " . FSC::$app['config']['site_name'];

        $viewName = 'myindex';
        $params = compact(
                'cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'tags',
                'nickname', 'loginedUser'
            );
        return $this->render($viewName, $params, $pageTitle);
    }

    //显示TA荐首页
    protected function renderTajianIndex() {
        $loginedUser = Common::getUserFromSession();

        $pageTitle = "Ta荐：你的聚宝盆，帮你收纳不同平台有价值的视频，轻松分享！";

        $stats = TajianStats::init();

        $this->layout = 'index';
        $viewName = 'tajian';
        $params = compact(
                'pageTitle', 'stats', 'loginedUser'
            );
        return $this->render($viewName, $params, $pageTitle);
    }

    //获取tag分类
    protected function getTags($dirTree, $noFiles = false) {
        $tags = array();

        $tagDir = null;
        $tagSaveDirName = str_replace('/', '', FSC::$app['config']['tajian']['tag_dir']);
        foreach($dirTree as $id => $item) {
            if (!empty($item['directory']) && $item['directory'] == $tagSaveDirName) {
                $tagDir = $item;
                break;
            }
        }

        if (!empty($tagDir) && !empty($tagDir['files'])) {
            foreach($tagDir['files'] as $id => $item) {
                if (empty($item['realpath'])) {        //如果是txt描述文件
                    $tag = $this->getTagItem($item, $noFiles);
                    $tags[$tag['id']] = $tag;
                }
            }
        }

        return $tags;
    }

    protected function getTagItem($tagFile, $noFiles = false) {
        $tag = array();

        foreach($tagFile as $name => $item) {
            if ($name == 'id') {
                $tag['id'] = $item;
            }else {
                $tag['name'] = $name;
                if ($noFiles == false) {
                    $tag['files'] = !empty($item) ? explode("\n", trim($item)) : array();
                }
            }
        }

        return $tag;
    }

    protected function sortTags($menus_sorted, $tags) {
        $sorted_tags = array();

        foreach($menus_sorted as $tag) {
            foreach($tags as $id => $item) {
                if ($item['name'] == $tag) {
                    $sorted_tags[$id] = $item;
                }
            }
        }

        return array_merge($sorted_tags, $tags);
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

    //保存分类，如果tag是纯英文，则自动把首字母改为大写
    //@tags 新分类数组
    //@tags_current 当前分类数组
    protected function saveTags($tags, $tags_current) {
        $done = false;

        try {
            $rootDir = FSC::$app['config']['content_directory'];
            $tagSaveDirName = str_replace('/', '', FSC::$app['config']['tajian']['tag_dir']);

            //添加新分类
            foreach ($tags as $index => $tag) {
                //首字母转大写
                $tag = ucfirst($tag);
                $tags[$index] = $tag;

                if (in_array($tag, $tags_current)) {continue;}          //忽略已存在的分类

                $tagFile = "{$rootDir}{$tagSaveDirName}/{$tag}.txt";
                if (file_exists($tagFile) == false) {   //添加新分类
                    touch($tagFile);
                }
            }

            //删除或者改名新分类中被移除的老分类
            foreach ($tags_current as $index => $tag) {
                if (in_array($tag, $tags)) {continue;}                  //跳过新分类中保留的

                $tagFile = "{$rootDir}{$tagSaveDirName}/{$tag}.txt";
                if (file_exists($tagFile)) {
                    if (!empty($tags[$index])) {
                        $newTagFile = "{$rootDir}{$tagSaveDirName}/{$tags[$index]}.txt";
                        rename($tagFile, $newTagFile);
                    }else {
                        unlink($tagFile);

                        //更新统计数据
                        $stats = TajianStats::init();
                        TajianStats::decrease('tag');
                        $saved = TajianStats::save();
                    }
                }
            }

            //更新排序文件
            $sortFile = "{$rootDir}README_sort.txt";
            file_put_contents($sortFile, implode("\n", $tags));

            $done = true;
        }catch(Exception $e) {
            $this->logError('Save tags failed: ' . $e->getMessage());
        }

        return $done;
    }

    //删除分类
    protected function deleteTag($tag) {
        if (empty($tag)) {return false;}

        $done = false;

        try {
            $rootDir = FSC::$app['config']['content_directory'];
            $tagSaveDirName = str_replace('/', '', FSC::$app['config']['tajian']['tag_dir']);

            $tagFile = "{$rootDir}{$tagSaveDirName}/{$tag}.txt";
            if (file_exists($tagFile)) {
                unlink($tagFile);
            }

            //更新排序文件
            $sortFile = "{$rootDir}README_sort.txt";
            if (file_exists($sortFile)) {
                $content = file_get_contents($sortFile);
                $content = preg_replace("/{$tag}\n?/", '', $content);
                file_put_contents($sortFile, $content);
            }
            
            $done = true;
        }catch(Exception $e) {
            $this->logError("Delete tag {$tag} failed: " . $e->getMessage());
        }

        return $done;
    }

    //添加分类
    protected function addTag($tag) {
        $done = false;

        try {
            $rootDir = FSC::$app['config']['content_directory'];
            $tagSaveDirName = str_replace('/', '', FSC::$app['config']['tajian']['tag_dir']);

            $tagFile = "{$rootDir}{$tagSaveDirName}/{$tag}.txt";
            if (file_exists($tagFile) == false) {
                touch($tagFile);
            }

            //更新排序文件
            $sortFile = "{$rootDir}README_sort.txt";
            if (file_exists($sortFile)) {
                $content = file_get_contents($sortFile);
                $content = "{$content}\n{$tag}";
                file_put_contents($sortFile, $content);
            }else {
                file_put_contents($sortFile, $tag);
            }
            
            $done = true;
        }catch(Exception $e) {
            $this->logError("Add tag {$tag} failed: " . $e->getMessage());
        }

        return $done;
    }

    protected function getNickname($readmeFile) {
        $nickname = '';

        if (!empty($readmeFile['nickname'])) {
            $nickname = $readmeFile['nickname'];
        }else if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
            $nickname = FSC::$app['user_id'];
        }

        return $nickname;
    }

    protected function saveNickname($nickname) {
        $done = false;

        try {
            $filename = FSC::$app['config']['content_directory'] . 'README_nickname.txt';
            $savedBytes = file_put_contents($filename, $nickname);
            if ($savedBytes !== false) {
                $done = true;
            }
        }catch(Exception $e) {
            $this->logError('Save nickname failed: ' . $e->getMessage());
        }

        return $done;
    }

    //添加新视频
    //增加必须登录才能使用限制
    public function actionNew() {
        //判断是否已经登录，自动跳转到自己的添加视频网址
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            return $this->redirect('/site/login/');
        }else if (!empty(FSC::$app['config']['multipleUserUriParse']) && FSC::$app['user_id'] != $loginedUser['username']) {
            $shareUrl = "/{$loginedUser['username']}/site/new/";
            return $this->redirect($shareUrl);
        }


        //获取数据
        $menus = array();        //菜单，一级目录
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
        $htmlReadme = '';
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

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = '添加视频收藏 | ' . FSC::$app['config']['site_name'];
        $viewName = 'new';
        $params = compact('dirTree', 'scanResults', 'htmlReadme', 'tags', 'nickname');
        return $this->render($viewName, $params, $pageTitle);
    }

    //邀请制新用户注册，使用手机号码 + 邀请码 + 短信验证码注册
    public function actionRegister() {
        //判断是否已经登录
        $loginedUser = Common::getUserFromSession();
        if (!empty($loginedUser['username'])) {
            $shareUrl = "/{$loginedUser['username']}/my/";
            return $this->redirect($shareUrl);
        }

        $pageTitle = "注册Ta荐 | TaJian.tv";

        $this->layout = 'index';
        $viewName = 'register';
        $params = compact(
                'pageTitle'
            );
        return $this->render($viewName, $params, $pageTitle);
    }

    //用户登陆：使用手机号码 + 短信验证码登录
    public function actionLogin() {
        //判断是否已经登录
        $loginedUser = Common::getUserFromSession();
        if (!empty($loginedUser['username'])) {
            $shareUrl = "/{$loginedUser['username']}/my/";

            $goUrl = $this->get('go', '');
            if (!empty($goUrl) && preg_match("/^\/.+$/", urldecode($goUrl))) {
                $shareUrl = urldecode($goUrl);
            }

            return $this->redirect($shareUrl);
        }

        $pageTitle = "登录Ta荐 | TaJian.tv";

        $this->layout = 'index';
        $viewName = 'login';
        $params = compact(
                'pageTitle'
            );
        return $this->render($viewName, $params, $pageTitle);
    }

    public function actionLogout() {
        $logout = Common::logoutUserFromSession();
        return $this->redirect('/site/login');
    }

}
