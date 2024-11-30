<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';
require_once __DIR__ . '/../../../plugins/Html.php';

Class ListController extends Controller {
    protected $dateIndexCacheKey = 'MainBotDateIndex';      //索引数据的key单独缓存，缓存key为此{cacheKey}_keys
    protected $allFilesCacheKey = 'MainBotAllFiles';
    protected $noOriginalCtimeFilesCacheKey = 'MainBotNoOriginalCtimeFiles';

    public function actionIndex() {
        $cateId = $this->get('id', '');
        $cacheParentDataId = $this->get('cid', '');
        if (empty($cateId) || empty($cacheParentDataId)) {
            throw new Exception("参数缺失！", 403);
        }

        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $scanner = new DirScanner();

        //根据参数cid获取id对应的目录realpath，从而只扫描这个目录
        $cacheSeconds = 86400;
        $cachedParentData = Common::getCacheFromFile($cacheParentDataId, $cacheSeconds);
        if (empty($cachedParentData)) {
            $err = '缓存数据已失效，如果重新点击目录依然打不开，请联系管理员。';
            return $this->redirect('/?err=' . urlencode($err));
        }

        if (strpos($cacheParentDataId, $cateId) === false && empty($cachedParentData[$cateId])) {
            throw new Exception("缓存数据中找不到当前目录，请返回上一页重新进入！", 404);
        }else if (strpos($cacheParentDataId, $cateId) !== false) {      //为播放页面查询当前目录下所有视频
            $currentDir = $cachedParentData;
        }else if (!empty($cachedParentData)) {
            $currentDir = $cachedParentData[$cateId];

            //扫描当前目录
            $scanner->setWebRoot($this->getCurrentWebroot($currentDir['realpath']));
            $scanner->setRootDir($currentDir['realpath']);

            //密码授权检查
            $isAllowed = Common::isUserAllowedToDir($currentDir['directory']);
            if (!$isAllowed) {
                $goUrl = "/site/pwdauth/?dir=" . urlencode($currentDir['directory']) . "&back=" . urlencode(FSC::$app['requestUrl']);
                return $this->redirect($goUrl);
            }
        }

        //获取目录面包屑
        $breadcrumbs = $this->getBreadcrumbs($currentDir, $cachedParentData, $scanner);

        //父目录密码授权检查
        $isAllowed = true;
        $needAuthDir = '';
        foreach($breadcrumbs as $subdir) {
            $isAllowed = Common::isUserAllowedToDir($subdir['name']);
            if (!$isAllowed) {
                $needAuthDir = $subdir['name'];
                break;
            }
        }
        if (!$isAllowed && !empty($needAuthDir)) {
            $goUrl = "/site/pwdauth/?dir=" . urlencode($needAuthDir) . "&back=" . urlencode(FSC::$app['requestUrl']);
            return $this->redirect($goUrl);
        }


        //优先从缓存读取数据
        $maxScanDeep = 0;       //最大扫描目录级数
        $cacheKey = $this->getCacheKey($cateId, 'tree', $maxScanDeep);
        $cachedData = Common::getCacheFromFile($cacheKey, $cacheSeconds);

        if (!empty($cachedData)) {
            $dirTree = $cachedData;
            $scanner->setTreeData($cachedData);
        }else {
            $dirTree = $scanner->scan($currentDir['realpath'], $maxScanDeep);
            Common::saveCacheToFile($cacheKey, $dirTree);
        }

        //优先从缓存读取数据
        $cacheKey = $cacheDataId = $this->getCacheKey($cateId, 'data', $maxScanDeep);
        $cachedData = Common::getCacheFromFile($cacheKey, $cacheSeconds);
        if (!empty($cachedData)) {
            $scanResults = $cachedData;
            $scanner->setScanResults($cachedData);
        }else {
            $scanResults = $scanner->getScanResults();
            Common::saveCacheToFile($cacheKey, $scanResults);
        }

        //按照scanResults格式把当前目录扫描结果中的目录数据拼接到当前目录数据里: currentDir
        if (!empty($scanResults)) {
            $dirs = array();
            $files = array();
            $dir_exts = array();
            foreach ($scanResults as $id => $item) {
                if (!empty($item['directory'])) {
                    array_push($dirs, $item);
                }else if (!empty($item['filename'])) {
                    array_push($files, $item);
                }else {
                    $dir_exts = array_merge($item, $dir_exts);
                }
            }

            if (!empty($dirs)) {
                $currentDir['directories'] = $dirs;
            }

            if (!empty($files)) {
                $currentDir['files'] = $files;
            }

            if (!empty($dir_exts)) {    //合并目录的说明文件
                foreach ($dir_exts as $key => $val) {
                    $currentDir[$key] = $val;
                }
            }
    
            $scanResults = array($cateId => $currentDir);       //重新组装数据
        }

        //非首页统一从缓存获取目录数据，有效期 1 天
        $cacheKey = $this->getCacheKey('all', 'menu', $maxScanDeep);
        $expireSeconds = 86400;
        $menus = Common::getCacheFromFile($cacheKey, $expireSeconds);

        //获取根目录下的readme
        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $expireSeconds = 86400;
        $readmeFile = Common::getCacheFromFile($cacheKey, $expireSeconds);
        if (!empty($readmeFile)) {
            $htmlReadme = $readmeFile['htmlReadme'];
        }

        //图片、视频类型筛选支持
        $allFiles = !empty($scanResults[$cateId]['files']) ? $scanResults[$cateId]['files'] : [];
        $showType = $this->get('show', 'all');
        if ($showType == 'image' && !empty($scanResults[$cateId]['files'])) {
            $scanResults[$cateId]['files'] = array_filter($scanResults[$cateId]['files'], function($item) {
                $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                return !empty($item['extension']) && in_array($item['extension'], $imgExts);
            });
        }else if ($showType == 'video' && !empty($scanResults[$cateId]['files'])) {
            $scanResults[$cateId]['files'] = array_filter($scanResults[$cateId]['files'], function($item) {
                $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
                return !empty($item['extension']) && in_array($item['extension'], $videoExts);
            });
        }else if ($showType == 'audio' && !empty($scanResults[$cateId]['files'])) {
            $scanResults[$cateId]['files'] = array_filter($scanResults[$cateId]['files'], function($item) {
                $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
                return !empty($item['extension']) && in_array($item['extension'], $audioExts);
            });
        }


        //文件排序支持：默认按创建时间倒序、按文件名排序、按目录说明文件_sort.txt排序
        if (!empty(FSC::$app['config']['sortFilesByName'])) {
            $sortOrder = !empty(FSC::$app['config']['sortOrderOfFiles']) ? FSC::$app['config']['sortOrderOfFiles'] : 'asc';
            $sortField = 'filename';
            $scanResults[$cateId]['files'] = Common::sortArrayByValue($scanResults[$cateId]['files'], $sortField, $sortOrder);
        }else if (!empty($scanResults[$cateId]['sort'])) {
            $sortByArray = explode("\n", $scanResults[$cateId]['sort']);
            $scanResults[$cateId]['files'] = Common::sortArrayByFilenameList($scanResults[$cateId]['files'], $sortByArray);
        }


        //获取当前目录下的readme
        $cateReadmeFile = $scanner->getDefaultReadme();
        if (!empty($cateReadmeFile)) {
            $Parsedown = new Parsedown();
            $content = file_get_contents($cateReadmeFile['realpath']);
            $htmlCateReadme = $Parsedown->text($content);
            $htmlCateReadme = $scanner->fixMDUrls($cateReadmeFile['realpath'], $htmlCateReadme);
        }

        //获取默认mp3文件
        //优先从缓存获取默认mp3文件
        $cacheKey = $this->getCacheKey('root', 'mp3', $maxScanDeep);
        $expireSeconds = 86400;
        $mp3File = Common::getCacheFromFile($cacheKey, $expireSeconds);

        //当前目录数据
        $subcate = !empty($scanResults[$cateId]) ? $scanResults[$cateId] : array();

        //翻页支持
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', FSC::$app['config']['default_page_size']);
        $page = (int)$page;
        $pageSize = (int)$pageSize;


        //底部版权申明配置支持
        $copyright = '';
        if (!empty($readmeFile['copyright'])) {
            $copyright = $readmeFile['copyright'];
        }

        $pageTitle = !empty($readmeFile['titles']) ? $readmeFile['titles'][0]['name']: $currentDir['directory'];
        if (!empty($readmeFile['title'])) {
            $pageTitle = $readmeFile['title'];
        }


        //dataType支持：[image, video]
        $dataType = $this->get('dataType', 'html');
        if ($dataType == 'image' && !empty($subcate['files'])) {
            $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
            $imgs = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($subcate['files'] as $id => $item) {
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
        }else if ($dataType == 'video' && !empty($subcate['files'])) {
            $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
            $videos = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($subcate['files'] as $id => $item) {
                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $pageSize) {
                    break;
                }

                if (!empty($item['extension']) && in_array($item['extension'], $videoExts)) {
                    if ($item['extension'] == 'm3u8') {
                        $item['path'] .= "&cid={$cacheParentDataId}";
                    }

                    $item['videoType'] = Html::getMediaSourceType($item['extension']);

                    array_push($videos, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'videos'));
        }else if ($dataType == 'audio' && !empty($subcate['files'])) {
            $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
            $audios = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($subcate['files'] as $id => $item) {
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

        $viewName = '//site/index';     //共享视图
        $params = compact(
            'cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme',
            'mp3File', 'page', 'pageSize', 'cacheDataId', 'copyright', 'showType', 'isAdminIp', 'allFiles',
            'cacheDataByDate'
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
    //缓存key统一生成，方便按规则获取上一级目录的缓存cid
    protected function getBreadcrumbs($currentDir, $scanResults, $scanner) {
        $webroot = FSC::$app['config']['content_directory'];
        $arr = !empty($currentDir['realpath']) ? explode($webroot, $currentDir['realpath']) : [];
        $breads = array();

        if (count($arr) < 2) {
            return $breads;
        }

        $cates = explode('/', $arr[1]);
        $parentCate = preg_replace("/\/$/", '', "{$arr[0]}{$webroot}");     //删除最后一个斜杠
        foreach ($cates as $index => $cate) {
            if (empty($cate)) {continue;}
            if ($cate == $currentDir['directory']) {break;}

            $subcate = "{$parentCate}/{$cate}";
            $cateId = $scanner->getId($subcate);

            //下一级子目录的id
            $parentCateId = $scanner->getId($parentCate);
            $maxScanDeep = 0;   //所有页面扫描深度都为 1
            $cacheKey = $this->getCacheKey($parentCateId, 'data', $maxScanDeep);

            array_push($breads, [
                'id' => $cateId,
                'name' => $cate,
                'url' => "/list/?id={$cateId}&cid={$cacheKey}",
            ]);

            $parentCate = $subcate;     //记录上一级目录
        }

        //最后一级
        array_push($breads, [
            'id' => $currentDir['id'],
            'name' => $currentDir['directory'],
            'url' => $currentDir['path'],
        ]);

        return $breads;
    }

    //按年份、月份展示
    public function actionBydate() {
        $para_year = $this->get('year', '');
        $para_month = $this->get('month', '');
        if (empty($para_year) && empty($para_month)) {
            throw new Exception("参数缺失！", 403);
        }

        $intYear = str_replace('y', '', $para_year);
        $intMonth = str_replace('m', '', $para_month);

        //先获取keys文件，以快速检查年份和月份数据是否存在，并用于展示月份导航栏
        $cacheKey = $this->dateIndexCacheKey . "_keys";
        $expireSeconds = 86400 * 365;    //缓存 365 天
        $cacheSubDir = 'index';
        $cacheData_keys = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
        if (empty($cacheData_keys)) {
            throw new Exception("索引数据已失效，请重新扫描所有文件以生成索引数据！", 404);
        }else if ( !empty($para_month) && !in_array($para_month, $cacheData_keys[$para_year]) ) {
            throw new Exception("{$intYear} 年没有 {$intMonth} 月的数据！", 404);
        }

        $cacheKey = $this->dateIndexCacheKey . "_{$para_year}";
        $expireSeconds = 86400 * 30;    //缓存 30 天
        $cacheSubDir = 'index';
        $cacheData = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheSubDir);
        if (empty($cacheData)) {
            throw new Exception("索引数据已失效，请重新扫描所有文件以生成索引数据！", 404);
        }

        //其它数据获取

        //优先从缓存获取目录数据
        $maxScanDeep = 0;       //最大扫描目录级数
        $expireSeconds = 86400;
        $cacheKey = $this->getCacheKey('all', 'menu', $maxScanDeep);
        $menus = Common::getCacheFromFile($cacheKey, $expireSeconds);

        //获取目录面包屑
        $breadcrumbs = [
            [
                'id' => $para_year,
                'name' => $intYear,
                'url' => "/list/bydate?year={$para_year}",
            ]
        ];
        if (!empty($para_month)) {
            array_push($breadcrumbs,
                [
                    'id' => $para_month,
                    'name' => $intMonth,
                    'url' => "/list/bydate?year={$para_year}&month={$para_month}",
                ]
            );
        }

        $isAdminIp = Common::isAdminIp($this->getUserIp());        //判断是否拥有管理权限

        $htmlReadme = array();   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $copyright = '';

        $cacheKey = $this->getCacheKey('root', 'readme', $maxScanDeep);
        $readmeFile = Common::getCacheFromFile($cacheKey, $expireSeconds);

        $cacheKey = $this->getCacheKey('root', 'mp3', $maxScanDeep);
        $mp3File = Common::getCacheFromFile($cacheKey, $expireSeconds);

        //翻页支持
        $page = $this->get('page', 1);
        $pageSize = $this->get('limit', FSC::$app['config']['default_page_size']);
        $page = (int)$page;
        $pageSize = (int)$pageSize;

        //支持图片、视频、音乐类型筛选
        $pageTitleSuffix = '照片和视频';
        $showType = $this->get('show', 'all');
        $filtExts = [];
        if ($showType == 'image') {
            $pageTitleSuffix = '照片';
            foreach($cacheData as $month => $arr) {
                $cacheData[$month] = array_filter($arr, function($item) {
                    $filtExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
                    return !empty($item['extension']) && in_array($item['extension'], $filtExts);
                });
            }
        }else if ($showType == 'video') {
            $pageTitleSuffix = '视频';
            foreach($cacheData as $month => $arr) {
                $cacheData[$month] = array_filter($arr, function($item) {
                    $filtExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
                    return !empty($item['extension']) && in_array($item['extension'], $filtExts);
                });
            }
        }else if ($showType == 'audio') {
            $pageTitleSuffix = '音乐';
            foreach($cacheData as $month => $arr) {
                $cacheData[$month] = array_filter($arr, function($item) {
                    $filtExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
                    return !empty($item['extension']) && in_array($item['extension'], $filtExts);
                });
            }
        }

        //按月份筛选数据
        if (!empty($para_month)) {
            $newData = [];
            foreach($cacheData as $month => $arr) {
                if ($month != $para_month) {continue;}
                $newData[$month] = $arr;
            }
            $cacheData = $newData;
        }


        //把所有文件拼接到一个数组里
        $allFiles = [];
        foreach($cacheData as $month => $files) {
            $allFiles = array_merge($allFiles, $files);
        }


        //dataType支持：[image, video, audio]
        $dataType = $this->get('dataType', 'html');
        if ($dataType == 'image' && !empty($allFiles)) {
            $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
            $imgs = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($allFiles as $id => $item) {
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
        }else if ($dataType == 'video' && !empty($allFiles)) {
            $videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
            $videos = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($allFiles as $id => $item) {
                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $pageSize) {
                    break;
                }

                if (!empty($item['extension']) && in_array($item['extension'], $videoExts)) {
                    $item['videoType'] = Html::getMediaSourceType($item['extension']);

                    array_push($videos, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'videos'));
        }else if ($dataType == 'audio' && !empty($allFiles)) {
            $audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
            $audios = array();
            $pageStartIndex = ($page-1) * $pageSize;
            $index = 0;
            foreach ($allFiles as $id => $item) {
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


        $pageTitlePrefix = "{$intYear}年的";
        if (!empty($para_month)) {
            $pageTitlePrefix = "{$intYear}年{$intMonth}月的";
        }
        $pageTitle = "{$pageTitlePrefix}{$pageTitleSuffix}";


        $viewName = 'bydate';
        $params = compact(
            'menus', 'breadcrumbs',
            'htmlReadme', 'htmlCateReadme', 'copyright', 'mp3File', 'isAdminIp',
            'page', 'pageSize', 'showType',
            'allFiles',
            'cacheData',
            'cacheData_keys',
            'para_year', 'para_month'
        );
        return $this->render($viewName, $params, $pageTitle);
    }

}
