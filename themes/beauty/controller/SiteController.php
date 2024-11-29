<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';
require_once __DIR__ . '/../../../plugins/Html.php';

Class SiteController extends Controller {
    protected $dateIndexCacheKey = 'MainBotDateIndex';      //索引数据的key单独缓存，缓存key为此{cacheKey}_keys
    protected $allFilesCacheKey = 'MainBotAllFiles';
    protected $noOriginalCtimeFilesCacheKey = 'MainBotNoOriginalCtimeFiles';

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

        if (!empty($scanResults) && !empty($scanResults[$defaultCateId])) {
            //TODO: 获取根目录下的txt说明文件内容
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


        //提示信息支持
        $alertWarning = $this->get('err', '');
        $alertWarning = Common::cleanSpecialChars($alertWarning);

        //翻页支持
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', FSC::$app['config']['default_page_size']);
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

        //mp3支持
        $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');

        $allFiles = $scanResults;
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
        }else if ($showType == 'audio') {
            $scanResults = array_filter($scanResults, function($item) {
                $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
                return !empty($item['extension']) && in_array($item['extension'], $audioExts);
            });
        }


        //dataType支持：[image, video, audio]
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
        }else if ($dataType == 'audio') {
            $audios = array();
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

                if (!empty($item['extension']) && in_array($item['extension'], $audioExts)) {
                    //为音乐文件获取封面图
                    if (empty($item['snapshot'])) {
                        $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                        $matchedImage = Html::searchImageByFilename($item['filename'], $allFiles, $imgExts);
                        if (!empty($matchedImage)) {
                            $item['snapshot'] = $matchedImage['path'];
                        }else {
                            $item['snapshot'] = '/img/beauty/audio_icon.jpeg?v1';
                        }
                    }

                    array_push($audios, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'audios'));
        }


        $isAdminIp = Common::isAdminIp($this->getUserIp());        //判断是否拥有管理权限


        //从缓存文件获取按年份、月份归类的索引数据
        $cacheDataByDate = Common::getCacheFromFile($this->dateIndexCacheKey . '_keys', 86400*365, 'index');

        $viewName = 'index';
        $params = compact(
            'page', 'pageSize', 'cacheDataId', 'showType',
            'dirTree', 'scanResults', 'menus', 'htmlReadme', 'htmlCateReadme', 'mp3File', 'copyright',
            'alertWarning', 'isAdminIp', 'allFiles',
            'cacheDataByDate'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //清空所有缓存
    public function actionCleancache() {
        $code = 1;
        $msg = 'OK';

        try {
            if (Common::isAdminIp($this->getUserIp()) == false) {
                $code = 0;
                $msg = '403 Forbidden，禁止访问';
            }else {
                $cacheDir = __DIR__ . '/../../../runtime/cache/';
                $files = scandir($cacheDir);
                foreach($files as $file) {
                    if (!preg_match('/\.json$/i', $file)) {continue;}

                    unlink("{$cacheDir}{$file}");
                }

                //删除图片缓存: image/
                $imgCacheDir = "{$cacheDir}image/";
                if (is_dir($imgCacheDir)) {
                    $files = scandir($imgCacheDir);
                    foreach($files as $file) {
                        if (!preg_match('/\.json$/i', $file)) {continue;}

                        unlink("{$imgCacheDir}{$file}");
                    }

                    rmdir($imgCacheDir);
                }
            }
        }catch(Exception $e) {
            $code = 0;
            $msg = '缓存清空失败：' . $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    //根据目录id，获取第一张图网址作为封面图返回
    /**
     * size可选值：
     * original - 原图
     * small    - 缩略图
     * vm       - 视频封面图(vmeta)
     * am       - 音乐封面图
     **/
    public function actionDirsnap() {
        $code = 1;
        $msg = 'OK';
        $url = '';
        $img_id = '';
        $size = 'orignal';

        $cacheId = $this->get('cid', '');
        $cateId = $this->get('id', '');
        $customHeaders = array();
        $httpStatus = 200;

        if (empty($cacheId) || empty($cateId)) {
            $code = 0;
            $msg = '参数不能为空';
        }else {
            //优先从缓存获取
            $cacheKey = $this->getCacheKey($cateId, 'snap');
            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
            $cacheSubDir = 'dir';
            $withCreateTime = true;     //返回数据的缓存时间
            $cache = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir, $withCreateTime);
            $cachedData = !empty($cache) ? $cache['data'] : null;
            $cachedCtime = !empty($cache) ? $cache['ctime'] : 0;
            $now = time();

            //如果关闭缩略图
            if (empty(FSC::$app['config']['enableSmallImage']) || FSC::$app['config']['enableSmallImage'] === 'false') {
                if (!empty($cachedData) && !empty($cachedData['size']) && $cachedData['size'] == 'small') {
                    $cachedData = null;
                }
            }else if ( !empty($cachedData) && !empty($cachedData['size']) && in_array($cachedData['size'], array('vm', 'am')) ) {
                //如果是视频、音乐封面图，则缓存 10 分钟
                if ($cachedCtime > 0 && $now - $cachedCtime > 600) {
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

                    //支持视频、音乐目录
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
                                $size = 'vm';   //视频封面图
                                Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                            }
                        }else {
                            $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
                            $firstVideo = $scanner->getSnapshotImage($realpath, $audioExts);
                            if (!empty($firstVideo)) {
                                $url = '/img/beauty/audio_icon.jpeg';
                            }
                        }
                    }else {
                        $url = $imgFile['path'];
                        $img_id = $imgFile['id'];
                        $size = 'orignal';      //原尺寸

                        //小尺寸图片支持
                        if (!empty(FSC::$app['config']['enableSmallImage']) && FSC::$app['config']['enableSmallImage'] !== 'false') {
                            $cacheKey_smimg = $this->getCacheKey("{$imgFile['id']}_small", 'imgsm');
                            $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
                            $cacheSubDir = 'image';
                            $cachedData = Common::getCacheFromFile($cacheKey_smimg, $expireSeconds, $cacheSubDir);
                            if (!empty($cachedData)) {      //已经有缩略图
                                $url = $cachedData;
                                $size = 'small';    //缩略图

                                //当前目录有缩略图的时候才缓存
                                $cacheSubDir = 'dir';
                                Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);
                            }else {
                                //实时生成缩略图
                                $img_filepath = $imgFile['realpath'];
                                $imgSize = 'small';
                                $sizeOptions = $this->getImageSizeOptions($imgSize);
                                $img_data = $this->createSmallJpg($img_filepath, $sizeOptions['min_width'], $sizeOptions['min_height']);
                                if (!empty($img_data)) {
                                    //保存到缓存文件
                                    $cacheKey_smimg = $this->getCacheKey("{$imgFile['id']}_small", 'imgsm');
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
                $etag = md5($url);
                $etag_from_client = !empty($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';  //get etag from client
                if (!empty($etag) && $etag == $etag_from_client) {
                    $httpStatus = 304;
                }

                $dir_snapshot_client_cache_seconds = FSC::$app['config']['dir_snapshot_client_cache_seconds'];
                $customHeaders = array(
                    "Cache-Control: max-age={$dir_snapshot_client_cache_seconds}",
                    "Etag: {$etag}",
                );
            }
        }

        return $this->renderJson(compact('code', 'msg', 'url'), $httpStatus, $customHeaders);
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
        }else if (Common::isAdminIp($this->getUserIp()) == false) {
            $code = 0;
            $msg = '403 Forbidden，禁止访问';
        }else {
            $cacheKey = $this->getCacheKey($cateId, 'snap');
            $cacheSubDir = 'dir';

            $size = 'orignal';
            if (!empty(FSC::$app['config']['enableSmallImage']) && FSC::$app['config']['enableSmallImage'] !== 'false') {
                $size = 'small';
            }

            $saved = Common::saveCacheToFile($cacheKey, compact('url', 'size'), $cacheSubDir);

            if ($saved !== false) {
                $code = 1;
            }
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    //借助gd库，获取图片类型、尺寸，并实时生成缩略图
    //支持imagick库
    protected function createSmallJpg($img_filepath, $min_width = 100, $min_height = 100) {
        //如果服务器端生成缩略图关闭
        if (!empty(FSC::$app['config']['disableGenerateSmallImageInServer']) && FSC::$app['config']['disableGenerateSmallImageInServer'] !== 'false') {
            return false;
        }

        $img_data = null;

        try {
            if (!empty(FSC::$app['config']['enable_lib_imagick']) && class_exists('Imagick')) {                  //Imagick库支持

                $imagick = new Imagick($img_filepath);
                $imgProps = $imagick->getImageGeometry();
                $naturalWidth = $imgProps['width'];
                $naturalHeight = $imgProps['height'];

                //小图片则保持原图尺寸
                if ($naturalWidth <= $min_width || $naturalHeight <= $min_height) {
                    return false;
                }

                //生成同比例缩略图尺寸
                $width = $min_width;
                $height = $min_height;
                $aspect = $naturalHeight / $naturalWidth;
                if ($naturalWidth <= $naturalHeight) {
                    $height = (int)($width * $aspect);
                }else {
                    $width = (int)($height / $aspect);
                }

                $imagick->scaleImage($width, $height, true);        //生成缩略图，并自适应
                $imagick->setImageFormat('jpeg');
                $quality = !empty(FSC::$app['config']['smallImageQuality']) ? FSC::$app['config']['smallImageQuality'] : 90;
                $imagick->setImageCompressionQuality($quality);
                $img_data = $imagick->getImageBlob();
                $imagick->clear();

            }else if (function_exists('gd_info')) {         //gd库支持

                list($naturalWidth, $naturalHeight, $imgTypeIndex, $style) = getimagesize($img_filepath);
                $imgType = image_type_to_extension($imgTypeIndex);

                //小图片则保持原图尺寸
                if ($naturalWidth <= $min_width || $naturalHeight <= $min_height) {
                    return false;
                }

                //生成同比例缩略图尺寸
                $width = $min_width;
                $height = $min_height;
                $aspect = $naturalHeight / $naturalWidth;
                if ($naturalWidth <= $naturalHeight) {
                    $height = (int)($width * $aspect);
                }else {
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
                        if (function_exists('imagecreatefromwebp')) {
                            $imgSource = imagecreatefromwebp($img_filepath);
                        }
                        break;
                    case '.bmp':
                        if (function_exists('imagecreatefrombmp')) {
                            $imgSource = imagecreatefrombmp($img_filepath);
                        }
                        break;
                }

                //保存base64格式的缩略图到缓存文件
                if (!empty($imgSource)) {
                    //方法1: 使用imagecopyresampled复制部分图片
                    //$dst_img = imagecreatetruecolor($width, $height);
                    //$copy_done = imagecopyresampled($dst_img, $imgSource, 0, 0, 0, 0, $width, $height, $naturalWidth, $naturalHeight);

                    //方法2: 直接缩小图片
                    $dst_img = imagescale($imgSource, $width, $height, IMG_CATMULLROM);
                    $copy_done = !empty($dst_img) ? true : false;
                    if ($copy_done) {
                        ob_start();
                        $quality = !empty(FSC::$app['config']['smallImageQuality']) ? FSC::$app['config']['smallImageQuality'] : 90;
                        imagejpeg($dst_img, null, $quality);
                        $img_data = ob_get_clean();
                        ob_end_clean();
                    }

                    imagedestroy($dst_img);
                }

            }
        }catch(Exception $e) {
            $this->logError('创建缩略图失败：' . $e->getMessage());
        }

        return $img_data;
    }

    //根据图片大小类型获取最大、最小尺寸设置
    protected function getImageSizeOptions($imgSize) {
        $options = array(
            'min_width' => FSC::$app['config']['small_image_min_width'],
            'min_height' => FSC::$app['config']['small_image_min_height'],
        );

        if ($imgSize == 'middle') {
            $options = array(
                'min_width' => FSC::$app['config']['middle_image_min_width'],
                'min_height' => FSC::$app['config']['middle_image_min_height'],
            );
        }

        return $options;
    }

    //优先从缓存获取小尺寸的图片
    //增加父目录封面图缓存更新
    //增加图片尺寸类型参数: size
    //增加缩略图生成失败检查，如果缩略图文件大小小于 5 Kb，则认为生成了无效的图片（如黑图）
    public function actionSmallimg() {
        $imgId = $this->get('id', '');
        $imgUrl = $this->get('url', '');
        $imgSize = $this->get('size', 'small');
        if (empty($imgId) || empty($imgUrl)) {
            return $this->redirect('/img/beauty/lazy.svg');
        }

        $cacheKey = $this->getCacheKey("{$imgId}_{$imgSize}", 'imgsm');
        $expireSeconds = FSC::$app['config']['screenshot_expire_seconds'];  //有效期3650天
        $cacheSubDir = 'image';
        $cachedData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);

        $small_image_client_cache_seconds = FSC::$app['config']['small_image_client_cache_seconds'];

        //检查文件大小，如果小于 5 Kb，则重新生成图片
        if (!empty($cachedData)) {
            $imgType = preg_replace('/^data:(image\/.+);base64,.+$/i', "$1", $cachedData);
            $base64_img = preg_replace('/^data:image\/.+;base64,/i', '', $cachedData);
            $img_data = base64_decode($base64_img);
            $minNormalImgSize = 5 * 1024;       //最小图片尺寸：5Kb
            if (strlen($img_data) < $minNormalImgSize) {
                $cachedData = null;
            }
        }

        //无缓存，则实时生成缩略图
        $minCacheImgSize = 2 * 1024;       //最小图片尺寸：20Kb
        if (empty($cachedData)) {
            $tmpUrl = parse_url($imgUrl);
            $img_filepath = __DIR__ . '/../../../www' . $tmpUrl['path'];
            $sizeOptions = $this->getImageSizeOptions($imgSize);
            $img_data = $this->createSmallJpg($img_filepath, $sizeOptions['min_width'], $sizeOptions['min_height']);
            if (!empty($img_data)) {
                //保存到缓存文件
                $cacheSubDir = 'image';
                $base64_img = base64_encode($img_data);
                Common::saveCacheToFile($cacheKey, "data:image/jpeg;base64,{$base64_img}", $cacheSubDir);

                //返回图片数据
                header("Content-Type: image/jpeg");
                if (strlen($img_data) >= $minCacheImgSize) {
                    header("Cache-Control: max-age={$small_image_client_cache_seconds}");
                    header("Etag: " . md5($img_data));
                }
                echo $img_data;
                exit;
            }
        }else {     //有缓存，则返回缓存数据
            $imgType = preg_replace('/^data:(image\/.+);base64,.+$/i', "$1", $cachedData);
            $base64_img = preg_replace('/^data:image\/.+;base64,/i', '', $cachedData);

            $img_data = base64_decode($base64_img);
            $etag = md5($img_data);
            $etag_from_client = !empty($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';  //get etag from client
            if (!empty($etag) && $etag == $etag_from_client) {
                header("HTTP/1.0 304 Not Modified", true, 304);
            }

            header("Content-Type: {$imgType}");
            if (strlen($img_data) >= $minCacheImgSize) {
                header("Cache-Control: max-age={$small_image_client_cache_seconds}");
                header("Etag: {$etag}");
            }
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

            $imgSize = 'small';
            $cacheKey = $this->getCacheKey("{$imgId}_{$imgSize}", 'imgsm');
            $cacheSubDir = 'image';
            $saved = Common::saveCacheToFile($cacheKey, $imgData, $cacheSubDir);

            if ($saved !== false) {
                $code = 1;
            }
        }

        return $this->renderJson(compact('code', 'msg'));
    }

    //TODO: 增加mp3播放器，以及mp3时长获取
    public function actionAudioplayer() {
        $videoUrl = $this->get('url', '');
        $videoId = $this->get('id', '');

        $cateId = $this->get('pid', '');
        $cacheParentDataId = $this->get('cid', '');
        $page = $this->get('page', 0);
        $pageSize = $this->get('limit', 100);

        //增加按年、月查看视频自动播放更多视频支持
        $para_year = $this->get('year', '');
        $para_month = $this->get('month', '');

        if (empty($videoUrl) || empty($videoId) || empty($cateId)) {
            throw new Exception("缺少参数！", 403);
        }

        $arr = parse_url($videoUrl);
        $videoFilename = basename($arr['path']);

        //增加文件后缀格式检查，区分：mp4, mov, m3u8, mp3
        $videoExtension = pathinfo($arr['path'], PATHINFO_EXTENSION);
        $videoSourceType = Html::getMediaSourceType($videoExtension);

        //从缓存数据获取封面图
        $poster = '/img/beauty/audio_bg.jpg';
        $cacheSeconds = 86400;

        if (!empty($cacheParentDataId)) {
            $cachedParentData = Common::getCacheFromFile($cacheParentDataId, $cacheSeconds);
            if (!empty($cachedParentData)) {
                $mp3 = $cachedParentData[$videoId];
                if (!empty($mp3['snapshot'])) {
                    $poster = $mp3['snapshot'];
                }else {
                    $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                    $matchedImage = Html::searchImageByFilename($mp3['filename'], $cachedParentData, $imgExts);
                    if (!empty($matchedImage)) {
                        $poster = $matchedImage['path'];
                    }
                }
            }
        }


        //获取联系方式
        $maxScanDeep = 0;       //最大扫描目录级数
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $readmeFile = Common::getCacheFromFile($cacheKey);

        //底部版权申明配置支持
        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $isAdminIp = Common::isAdminIp($this->getUserIp());        //判断是否拥有管理权限


        $pageTitle = "正在播放：{$videoFilename}";
        $this->layout = 'player';
        $viewName = 'mp3player';
        $params = compact(
            'videoUrl', 'videoId', 'videoFilename',
            'cateId', 'cacheParentDataId', 'page', 'pageSize',
            'copyright', 'isAdminIp', 'videoExtension', 'videoSourceType', 'poster',
            'para_year', 'para_month'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //视频播放器
    public function actionPlayer() {
        $videoUrl = $this->get('url', '');
        $videoId = $this->get('id', '');
        $videoName = $this->get('name', '');

        $cateId = $this->get('pid', '');
        $cacheParentDataId = $this->get('cid', '');
        $page = $this->get('page', 0);
        $pageSize = $this->get('limit', 10);

        //增加按年、月查看视频自动播放更多视频支持
        $para_year = $this->get('year', '');
        $para_month = $this->get('month', '');

        if (empty($videoUrl) || empty($videoId) || empty($cateId)) {
            throw new Exception("缺少参数！", 403);
        }

        $arr = parse_url($videoUrl);
        $videoFilename = basename($arr['path']);

        //增加文件后缀格式检查，区分：mp4, mov
        $videoExtension = pathinfo($arr['path'], PATHINFO_EXTENSION);

        //支持m3u8地址：/m3u8/?id=xxx
        if ($videoFilename == 'm3u8') {
            $videoExtension = 'm3u8';

            //从缓存数据获取文件名
            if (!empty($cacheParentDataId)) {
                $cacheSeconds = 86400;
                $cachedParentData = Common::getCacheFromFile($cacheParentDataId, $cacheSeconds);
                if (!empty($cachedParentData)) {
                    $m3u8 = $cachedParentData[$videoId];
                    $videoFilename = $m3u8['filename'] . '.m3u8';
                }
            }
        }

        $videoSourceType = Html::getMediaSourceType($videoExtension);

        //获取联系方式
        $maxScanDeep = 0;       //最大扫描目录级数
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $readmeFile = Common::getCacheFromFile($cacheKey);

        //底部版权申明配置支持
        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $isAdminIp = Common::isAdminIp($this->getUserIp());        //判断是否拥有管理权限

        $pageTitle = "正在播放：{$videoFilename}";
        $this->layout = 'player';
        $viewName = 'player';
        $params = compact(
            'videoUrl', 'videoId', 'videoFilename',
            'cateId', 'cacheParentDataId', 'page', 'pageSize',
            'copyright', 'isAdminIp', 'videoExtension', 'videoSourceType',
            'para_year', 'para_month', 'videoName'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

    //根据视频文件id返回缓存数据：
    //{duration: 单位秒的时长, snapshot: base64格式的jpg封面图}
    public function actionVideometa() {
        $code = 1;
        $msg = 'OK';
        $meta = array();

        $httpStatus = 200;
        $customHeaders = array();

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

                //增加客户端缓存header
                $etag = md5(json_encode($meta));
                $etag_from_client = !empty($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';  //get etag from client
                if (!empty($etag) && $etag == $etag_from_client) {
                    $httpStatus = 304;
                }

                $meta_client_cache_seconds = FSC::$app['config']['meta_client_cache_seconds'];
                $customHeaders = array(
                    "Cache-Control: max-age={$meta_client_cache_seconds}",
                    "Etag: {$etag}",
                );
            }else {
                $code = 0;
                $msg = '此视频无缓存或缓存已过期';
            }
        }

        return $this->renderJson(compact('code', 'msg', 'meta'), $httpStatus, $customHeaders);
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
        }else if (Common::isAdminIp($this->getUserIp()) == false) {
            $code = 0;
            $msg = '403 Forbidden，禁止访问';
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

    //密码授权
    public function actionPwdauth() {
        $checkDir = $this->get('dir', '');
        $checkDir = Common::cleanSpecialChars($checkDir);

        $goBackUrl = $this->get('back', '');
        $password = '';

        if (empty($checkDir) || empty($goBackUrl)) {
            throw new Exception("缺少参数！", 403);
        }

        $errorMsg = '';
        $post = $this->post();
        if (!empty($post)) {
            //增加频率限制
            $user_ip = $this->getUserIp();
            $ipLockKey = $this->getCacheKey($user_ip, $checkDir);
            $lockCacheDir = 'lock';
            $expireSeconds = 600;       //缓存 10 分钟
            $maxFailNum = 5;            //最多失败次数
            $ipTryData = Common::getCacheFromFile($ipLockKey, $expireSeconds, $lockCacheDir);
            if (!empty($ipTryData) && $ipTryData['fail'] >= $maxFailNum && time() - $ipTryData['at'] < $expireSeconds) {
                $authed = false;
                $minutes = $expireSeconds/60;
                $errorMsg = "密码错误已达 {$maxFailNum} 次，请 {$minutes} 分钟后再试！";
            }else {
                $password = $this->post('password', '');
                $authed = Common::pwdAuthToDir($checkDir, $password);

                if ($authed == false) {
                    if (empty($ipTryData)) {
                        $ipTryData = array(
                            'at' => time(),
                            'fail' => 1,
                        );
                    }else {
                        if (time() - $ipTryData['at'] < $expireSeconds) {
                            $ipTryData['fail'] ++;
                        }else {
                            $ipTryData['fail'] = 1;
                        }

                        $ipTryData['at'] = time();
                    }
                    Common::saveCacheToFile($ipLockKey, $ipTryData, $lockCacheDir);

                    $errorMsg = "第 {$ipTryData['fail']} 次密码错误，请仔细检查后重试。";
                }else {
                    return $this->redirect($goBackUrl);
                }
            }
        }

        $maxScanDeep = 0;

        //获取根目录下的readme
        $htmlReadme = '';
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $expireSeconds = 86400;
        $readmeFile = Common::getCacheFromFile($cacheKey, $expireSeconds);
        if (!empty($readmeFile)) {
            $htmlReadme = $readmeFile['htmlReadme'];
        }

        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $pageTitle = '密码授权';
        $viewName = 'pwdauth';
        $params = compact(
            'htmlReadme',
            'copyright',
            'checkDir',
            'goBackUrl',
            'password',
            'errorMsg'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

}
