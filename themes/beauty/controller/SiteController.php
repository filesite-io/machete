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

        $rootDir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'];
        $scanner->setRootDir($rootDir);

        //优先从缓存读取数据
        $defaultCateId = $scanner->getId(preg_replace("/\/$/", '', realpath($rootDir)));
        $maxScanDeep = 0;       //最大扫描目录级数
        $cacheKey = $this->getCacheKey($defaultCateId, 'tree', $maxScanDeep);
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (!empty($cachedData)) {
            $dirTree = $cachedData;
            $scanner->setTreeData($cachedData);
        }else {
            $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], $maxScanDeep);
            Common::saveCacheToFile($cacheKey, $dirTree);
        }

        //优先从缓存读取数据
        $cacheKey = $cacheDataId = $this->getCacheKey($defaultCateId, 'data', $maxScanDeep);
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (!empty($cachedData)) {
            $scanResults = $cachedData;
            $scanner->setScanResults($cachedData);
        }else {
            $scanResults = $scanner->getScanResults();
            Common::saveCacheToFile($cacheKey, $scanResults);
        }

        //优先从缓存获取目录数据
        $cacheKey = $this->getCacheKey('all', 'menu', $maxScanDeep);
        $menus = Common::getCacheFromFile($cacheKey);

        if (empty($menus) && !empty($scanResults)) {
            //获取目录
            $menus = $scanner->getMenus();

            //在path网址中追加cid缓存key参数
            if (!empty($menus)) {
                foreach ($menus as $index => $menu) {
                    $menus[$index]['cid'] = $cacheDataId;
                    $menus[$index]['path'] .= "&cid={$cacheDataId}";
                }
            }

            $readmeFile = $scanner->getDefaultReadme();
            if (!empty($readmeFile)) {
                if (!empty($readmeFile['sort'])) {
                    $menus_sorted = explode("\n", $readmeFile['sort']);
                }
            }

            //排序
            $sortedTree = $this->sortMenusAndDirTree($menus_sorted, $menus, $dirTree);
            if (!empty($sortedTree)) {
                $menus = $sortedTree['menus'];
                $dirTree = $sortedTree['dirTree'];
            }

            Common::saveCacheToFile($cacheKey, $menus);     //保存目录数据
        }

        //获取联系方式
        $titles = array();
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $cachedData = Common::getCacheFromFile($cacheKey);
        if (empty($cachedData)) {
            $readmeFile = $scanner->getDefaultReadme();
            if (!empty($readmeFile)) {
                $titles = $scanner->getMDTitles($readmeFile['id']);

                $Parsedown = new Parsedown();
                $content = file_get_contents($readmeFile['realpath']);
                $htmlReadme = $Parsedown->text($content);
                $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);

                Common::saveCacheToFile($cacheKey, array('htmlReadme' => $htmlReadme, 'titles' => $titles));
            }
        }else {
            $htmlReadme = $cachedData['htmlReadme'];
            $titles = $cachedData['titles'];
        }


        //优先从缓存获取默认mp3文件
        $cacheKey = $this->getCacheKey('root', 'mp3', $maxScanDeep);
        $mp3File = Common::getCacheFromFile($cacheKey);
        if (empty($mp3File)) {
            $mp3File = $scanner->getDefaultFile('mp3');
            if (!empty($mp3File)) {
                Common::saveCacheToFile($cacheKey, $mp3File);
            }
        }


        //翻页支持
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', 24);

        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io";
        if (!empty($readmeFile['title'])) {
            $pageTitle = $readmeFile['title'];
        }
        if (!empty($subcate)) {
            $pageTitle = "{$subcate['directory']}照片，来自{$pageTitle}";
        }
        $viewName = 'index';
        $params = compact(
            'page', 'pageSize', 'cacheDataId',
            'dirTree', 'scanResults', 'menus', 'htmlReadme', 'htmlCateReadme', 'mp3File'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //清空所有缓存
    public function actionCleancache() {
        $code = 1;
        $msg = 'OK';

        try {
            $cacheDir = __DIR__ . '/../../../runtime/cache/';
            $files = scandir($cacheDir);
            foreach($files as $file) {
                if (!preg_match('/\.json$/i', $file)) {continue;}

                unlink("{$cacheDir}{$file}");
            }
        }catch(Exception $e) {
            $code = 0;
            $msg = '缓存清空失败：' . $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    //根据目录id，获取第一张图网址作为封面图返回
    public function actionDirsnap() {
        $code = 1;
        $msg = 'OK';
        $url = '';

        $cacheId = $this->post('cid', '');
        $cateId = $this->post('id', '');
        if (empty($cacheId) || empty($cateId)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            //从缓存数据中获取目录的realpath
            $cachedData = Common::getCacheFromFile($cacheId);
            if (!empty($cachedData)) {
                $realpath = $cachedData[$cateId]['realpath'];
                $scanner = new DirScanner();
                $scanner->setWebRoot($this->getCurrentWebroot($realpath));
                $scanner->setRootDir($realpath);

                $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                $url = $scanner->getSnapshotImage($realpath, $imgExts);
                
                //支持视频目录
                if (empty($url)) {
                    $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
                    $firstVideoPath = $scanner->getSnapshotImage($realpath, $videoExts);
                    if (!empty($firstVideoPath)) {
                        $url = '/img/beauty/video_dir.png';
                    }
                }
            }else {
                $code = 0;
                $msg = '缓存数据已失效，请刷新网页';
            }
        }

        return $this->renderJson(compact('code', 'msg', 'url'));
    }

    public function actionPlayer() {
        $videoUrl = $this->get('url', '');
        if (empty($videoUrl)) {
            throw new Exception("缺少视频地址url参数！", 403);
        }

        $arr = parse_url($videoUrl);
        $videoFilename = basename($arr['path']);


        $pageTitle = "视频播放器";
        $this->layout = 'player';
        $viewName = 'player';
        $params = compact(
            'videoUrl', 'videoFilename'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

}
