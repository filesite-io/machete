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
        $page = (int)$page;
        $pageSize = (int)$pageSize;

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

        //图片、视频类型筛选支持
        $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
        $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');

        $showType = $this->get('show', 'all');
        if ($showType == 'image') {
            $scanResults = array_filter($scanResults, function($item) {
                $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                return !empty($item['extension']) && in_array($item['extension'], $imgExts);
            });
        }else if ($showType == 'video') {
            $scanResults = array_filter($scanResults, function($item) {
                $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
                return !empty($item['extension']) && in_array($item['extension'], $videoExts);
            });
        }


        //dataType支持：[image, video]
        $dataType = $this->get('dataType', 'html');
        if ($dataType == 'image') {
            $imgs = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($scanResults as $id => $item) {
                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $pageSize) {
                    break;
                }

                //增加caption：图片、视频显示文件修改日期
                $title = Common::getDateFromString($item['filename']);
                if (empty($title) && !empty($item['fstat']['mtime']) && !empty($item['fstat']['ctime'])) {
                    $title = date('Y-m-d', min($item['fstat']['mtime'], $item['fstat']['ctime']));
                }
                $item['caption'] = "{$title} - {$item['filename']}";

                if (!empty($item['extension']) && in_array($item['extension'], $imgExts)) {
                    array_push($imgs, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'imgs'));
        }else if ($dataType == 'video') {
            $videos = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($scanResults as $id => $item) {
                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $pageSize) {
                    break;
                }

                if (!empty($item['extension']) && in_array($item['extension'], $videoExts)) {
                    array_push($videos, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'videos'));
        }


        $viewName = 'index';
        $params = compact(
            'page', 'pageSize', 'cacheDataId', 'showType',
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
        $img_id = '';
        $size = 'orignal';

        $cacheId = $this->post('cid', '');
        $cateId = $this->post('id', '');
        if (empty($cacheId) || empty($cateId)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            //优先从缓存获取
            $cacheKey = $this->getCacheKey($cateId, 'snap');
            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
            $cacheSubDir = 'dir';
            $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);

            //如果关闭缩略图
            if (empty(FSC::$app['config']['enableSmallImage']) || FSC::$app['config']['enableSmallImage'] === 'false') {
                if (!empty($cachedData) && !empty($cachedData['size']) && $cachedData['size'] == 'small') {
                    $cachedData = null;
                }
            }

            //弃用老版本数据格式，抛弃没有size属性的
            if (!empty($cachedData) && empty($cachedData['size'])) {
                $cachedData = null;
            }

            if (empty($cachedData)) {
                //从缓存数据中获取目录的realpath
                $cachedData = Common::getCacheFromFile($cacheId, $expireSeconds);
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
                                $cacheSubDir = 'dir';
                                $size = 'vm';
                                Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                            }
                        }
                    }else {
                        $url = $imgFile['path'];
                        $img_id = $imgFile['id'];
                        $size = 'orignal';

                        //小尺寸图片支持
                        if (!empty(FSC::$app['config']['enableSmallImage']) && FSC::$app['config']['enableSmallImage'] !== 'false') {
                            $cacheKey_smimg = $this->getCacheKey($imgFile['id'], 'imgsm');
                            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
                            $cacheSubDir = 'image';
                            $cachedData = Common::getCacheFromFile($cacheKey_smimg, $expireSeconds, $cacheSubDir);
                            if (!empty($cachedData)) {      //已经有缩略图
                                $url = $cachedData;
                                $size = 'small';

                                //当前目录有缩略图的时候才缓存
                                $cacheSubDir = 'dir';
                                Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                            }else {
                                //实时生成缩略图
                                $img_filepath = $imgFile['realpath'];
                                $img_data = $this->createSmallJpg($img_filepath);
                                if (!empty($img_data)) {
                                    //保存到缓存文件
                                    $cacheKey_smimg = $this->getCacheKey($imgFile['id'], 'imgsm');
                                    $cacheSubDir = 'image';
                                    $base64_img = base64_encode($img_data);
                                    Common::saveCacheToFile($cacheKey_smimg, "data:image/jpeg;base64,{$base64_img}", $cacheSubDir);

                                    $url = "data:image/jpeg;base64,{$base64_img}";
                                    $size = 'small';

                                    //缓存目录封面图
                                    $cacheSubDir = 'dir';
                                    Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                                }
                            }
                        }else if (empty(FSC::$app['config']['enableSmallImage']) || FSC::$app['config']['enableSmallImage'] === 'false') {
                            //如果关闭了缩略图功能则缓存原图
                            $cacheSubDir = 'dir';
                            Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                        }
                    }
                }else {
                    $code = 0;
                    $msg = '缓存数据已失效，请刷新网页';
                }
            }else {
                $url = $cachedData['url'];
            }
        }

        return $this->renderJson(compact('code', 'msg', 'url'));
    }

    //保存目录封面图到缓存
    public function actionSavedirsnap() {
        $code = 0;
        $msg = 'OK';

        $cateId = $this->post('id', '');    //目录id
        $url = $this->post('url', '');      //base64格式的图片数据或者图片网址
        if (empty($cateId) || empty($url)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            $cacheKey = $this->getCacheKey($cateId, 'snap');
            $img_id = '';   //为保持数据格式一致，图片id传空
            $cacheSubDir = 'dir';

            $size = 'orignal';
            if (!empty(FSC::$app['config']['enableSmallImage']) && FSC::$app['config']['enableSmallImage'] !== 'false') {
                $size = 'small';
            }

            $saved = Common::saveCacheToFile($cacheKey, compact('url', 'img_id', 'size'), $cacheSubDir);

            if ($saved !== false) {
                $code = 1;
            }
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    //借助gd库，获取图片类型、尺寸，并实时生成缩略图
    protected function createSmallJpg($img_filepath, $min_width = 198, $min_height = 219, $max_width = 600, $max_height = 500) {
        $img_data = null;

        try {
            list($naturalWidth, $naturalHeight, $imgTypeIndex, $style) = getimagesize($img_filepath);
            $imgType = image_type_to_extension($imgTypeIndex);

            //小图片则保持原图尺寸
            if ($naturalWidth <= $max_width || $naturalHeight <= $max_height) {
                return false;
            }

            //生成同比例缩略图尺寸
            $zoomRate = FSC::$app['config']['small_image_zoom_rate'];        //缩略图在最小尺寸基础上放大比例，为确保清晰度
            $width = $min_width;
            $height = $min_height;
            $aspect = $naturalHeight / $naturalWidth;
            if ($naturalWidth <= $naturalHeight) {
                if ($width * $zoomRate >= $naturalWidth) {return false;}        //避免把小图片放大
                $width = $width * $zoomRate <= $max_width ? (int)($width * $zoomRate) : $max_width;
                $height = (int)($width * $aspect);
            }else {
                if ($height * $zoomRate >= $naturalHeight) {return false;}      //避免把小图片放大
                $height = $height * $zoomRate <= $max_height ? (int)($height * $zoomRate) : $max_height;
                $width = (int)($height / $aspect);
            }

            $imgSource = null;
            switch ($imgType) {
                case '.jpeg':
                    $imgSource = imagecreatefromjpeg($img_filepath);
                    break;
                case '.png':
                    $imgSource = imagecreatefrompng($img_filepath);
                    break;
                case '.gif':
                    $imgSource = imagecreatefromgif($img_filepath);
                    break;
                case '.webp':
                    //php >= 5.4
                    if (phpversion() >= 5.4) {
                        $imgSource = imagecreatefromwebp($img_filepath);
                    }
                    break;
                case '.bmp':
                    //php >= 7.2
                    if (phpversion() >= 7.2) {
                        $imgSource = imagecreatefrombmp($img_filepath);
                    }
                    break;
            }

            //保存base64格式的缩略图到缓存文件
            if (!empty($imgSource)) {
                $dst_img = imagecreatetruecolor($width, $height);
                $copy_done = imagecopyresized($dst_img, $imgSource, 0, 0, 0, 0, $width, $height, $naturalWidth, $naturalHeight);

                if ($copy_done) {
                    ob_start();
                    imagejpeg($dst_img);
                    $img_data = ob_get_clean();
                    ob_end_clean();
                }

                imagedestroy($dst_img);
            }
        }catch(Exception $e) {
            $this->logError('创建缩略图失败：' . $e->getMessage());
        }

        return $img_data;
    }

    //优先从缓存获取小尺寸的图片
    //增加父目录封面图缓存更新
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

        //无缓存，则实时生成缩略图
        if (empty($cachedData)) {
            $tmpUrl = parse_url($imgUrl);
            $img_filepath = __DIR__ . '/../../../www' . $tmpUrl['path'];
            $img_data = $this->createSmallJpg($img_filepath);
            if (!empty($img_data)) {
                //保存到缓存文件
                $cacheKey = $this->getCacheKey($imgId, 'imgsm');
                $cacheSubDir = 'image';
                $base64_img = base64_encode($img_data);
                Common::saveCacheToFile($cacheKey, "data:image/jpeg;base64,{$base64_img}", $cacheSubDir);

                //返回图片数据
                header("Content-Type: image/jpeg");
                header('Cache-Control: max-age=3600');  //缓存 1 小时
                header("Etag: " . md5($img_data));
                echo $img_data;
                exit;
            }
        }else {     //有缓存，则返回缓存数据
            $imgType = preg_replace('/^data:(image\/.+);base64,.+$/i', "$1", $cachedData);
            $base64_img = preg_replace('/^data:image\/.+;base64,/i', '', $cachedData);

            $img_data = base64_decode($base64_img);
            header("Content-Type: {$imgType}");
            header('Cache-Control: max-age=3600');  //缓存 1 小时
            header("Etag: " . md5($img_data));
            echo $img_data;
            exit;
        }

        return $this->redirect($imgUrl);
    }

    //保存小尺寸图片数据到缓存
    public function actionSavesmallimg() {
        $code = 0;
        $msg = 'OK';

        $cateId = $this->post('pid', '');
        $imgId = $this->post('id', '');
        $imgData = $this->post('data', '');     //base64格式的图片数据
        if (empty($imgId) || empty($imgData)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            //如果是目录封面图生成缩略图，则更新目录封面图缓存数据
            if (!empty($cateId)) {
                $cacheKey = $this->getCacheKey($cateId, 'snap');
                $img_id = '';   //为保持数据格式一致，图片id传空
                $cacheSubDir = 'dir';
                $size = 'small';
                Common::saveCacheToFile($cacheKey, array('url' => $imgData, 'img_id' => $img_id, 'size' => $size), $cacheSubDir);
            }

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

        $cateId = $this->get('pid', '');
        $cacheParentDataId = $this->get('cid', '');
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', 24);

        if (empty($videoUrl) || empty($videoId) || empty($cateId) || empty($cacheParentDataId)) {
            throw new Exception("缺少参数！", 403);
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
            'videoUrl', 'videoId', 'videoFilename',
            'cateId', 'cacheParentDataId', 'page', 'pageSize',
            'copyright'
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
