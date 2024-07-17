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
        $htmlReadme = array();   //Readme.md 内容，底部网站详细介绍
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

                $title = !empty($readmeFile['title']) ? $readmeFile['title'] : '';
                $copyright = !empty($readmeFile['copyright']) ? $readmeFile['copyright'] : '';
                Common::saveCacheToFile($cacheKey, array(
                        'htmlReadme' => $htmlReadme,
                        'titles' => $titles,
                        'title' => $title,
                        'copyright' => $copyright,
                    ));
            }
        }else {
            $readmeFile = $cachedData;
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

        //底部版权申明配置支持
        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $viewName = 'index';
        $params = compact(
            'page', 'pageSize', 'cacheDataId',
            'dirTree', 'scanResults', 'menus', 'htmlReadme', 'htmlCateReadme', 'mp3File', 'copyright'
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
            //优先从缓存获取
            $cacheKey = $this->getCacheKey($cateId, 'dirsnap');
            $url = Common::getCacheFromFile($cacheKey);

            if (empty($url)) {
                //从缓存数据中获取目录的realpath
                $cachedData = Common::getCacheFromFile($cacheId);
                if (!empty($cachedData)) {
                    $realpath = $cachedData[$cateId]['realpath'];
                    $scanner = new DirScanner();
                    $scanner->setWebRoot($this->getCurrentWebroot($realpath));
                    $scanner->setRootDir($realpath);

                    $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                    $imgFile = $scanner->getSnapshotImage($realpath, $imgExts);

                    //支持视频目录
                    if (empty($imgFile)) {
                        $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
                        $firstVideo = $scanner->getSnapshotImage($realpath, $videoExts);
                        if (!empty($firstVideo)) {
                            $url = '/img/beauty/video_dir.png';

                            //尝试从缓存数据中获取封面图
                            $cacheKey_snap = $this->getCacheKey($firstVideo['id'], 'vmeta');;
                            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
                            $cacheSubDir = 'video';
                            $cachedData = Common::getCacheFromFile($cacheKey_snap, $expireSeconds, $cacheSubDir);
                            if (!empty($cachedData)) {
                                $url = $cachedData['snapshot'];
                                Common::saveCacheToFile($cacheKey, $url);
                            }
                        }
                    }else {
                        $url = $imgFile['path'];

                        //小尺寸图片支持
                        $cacheKey = $this->getCacheKey($imgFile['id'], 'imgsm');
                        $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
                        $cacheSubDir = 'image';
                        $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
                        if (!empty($cachedData)) {
                            $url = $cachedData;
                        }

                        Common::saveCacheToFile($cacheKey, $url);
                    }
                }else {
                    $code = 0;
                    $msg = '缓存数据已失效，请刷新网页';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'url'));
    }

    //优先从缓存获取小尺寸的图片
    public function actionSmallimg() {
        $imgId = $this->get('id', '');
        $imgUrl = $this->get('url', '');
        if (empty($imgId) || empty($imgUrl)) {
            return $this->redirect('/img/beauty/lazy.svg');
        }

        $cacheKey = $this->getCacheKey($imgId, 'imgsm');
        $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
        $cacheSubDir = 'image';
        $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
        if (!empty($cachedData)) {
            $imgType = preg_replace('/^data:(image\/.+);base64,.+$/i', "$1", $cachedData);
            $base64_img = preg_replace('/^data:image\/.+;base64,/i', '', $cachedData);

            header("Content-Type: {$imgType}");
            echo base64_decode($base64_img);
            exit;
        }

        return $this->redirect($imgUrl);
    }

    //保存小尺寸图片数据到缓存
    public function actionSavesmallimg() {
        $code = 0;
        $msg = 'OK';

        $imgId = $this->post('id', '');
        $imgData = $this->post('data', '');     //base64格式的图片数据
        if (empty($imgId) || empty($imgData)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            $cacheKey = $this->getCacheKey($imgId, 'imgsm');
            $cacheSubDir = 'image';
            $saved = Common::saveCacheToFile($cacheKey, $imgData, $cacheSubDir);

            if ($saved !== false) {
                $code = 1;
            }
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    public function actionPlayer() {
        $videoUrl = $this->get('url', '');
        $videoId = $this->get('id', '');
        if (empty($videoUrl) || empty($videoId)) {
            throw new Exception("缺少视频地址url或id参数！", 403);
        }

        $arr = parse_url($videoUrl);
        $videoFilename = basename($arr['path']);


        //获取联系方式
        $maxScanDeep = 0;       //最大扫描目录级数
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $readmeFile = Common::getCacheFromFile($cacheKey);

        //底部版权申明配置支持
        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $pageTitle = "视频播放器";
        $this->layout = 'player';
        $viewName = 'player';
        $params = compact(
            'videoUrl', 'videoId', 'videoFilename', 'copyright'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //根据视频文件id返回缓存数据：
    //{duration: 单位秒的时长, snapshot: base64格式的jpg封面图}
    public function actionVideometa() {
        $code = 1;
        $msg = 'OK';
        $meta = array();

        $videoId = $this->get('id', '');
        if (empty($videoId)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            $cacheKey = $this->getCacheKey($videoId, 'vmeta');
            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
            $cacheSubDir = 'video';
            $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
            if (!empty($cachedData)) {
                $meta = $cachedData;
            }else {
                $code = 0;
                $msg = '此视频无缓存或缓存已过期';
            }
        }

        return $this->renderJson(compact('code', 'msg', 'meta'));
    }

    //保存视频meta数据到缓存，支持手动生成
    public function actionSavevideometa() {
        $code = 0;
        $msg = 'OK';

        $videoId = $this->post('id', '');
        $metaData = $this->post('meta', '');
        $manual = $this->post('manual', 0);
        if (empty($videoId) || empty($metaData)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            $cacheKey = $this->getCacheKey($videoId, 'vmeta');
            $cacheSubDir = 'video';
            $saved = true;

            if (!empty($manual)) {
                $metaData['manual'] = 1;
                $saved = Common::saveCacheToFile($cacheKey, $metaData, $cacheSubDir);
            }else {
                $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
                $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
                if (empty($cachedData) || empty($cachedData['manual'])) {
                    $saved = Common::saveCacheToFile($cacheKey, $metaData, $cacheSubDir);
                }
            }

            if ($saved !== false) {
                $code = 1;
            }
        }

        return $this->renderJson(compact('code', 'msg'));
    }

}
