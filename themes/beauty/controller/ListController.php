<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class ListController extends Controller {

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

            //密码授权检查
            $isAllowed = Common::isUserAllowedToDir($currentDir['directory']);
            if (!$isAllowed) {
                $goUrl = "/site/pwdauth/?dir=" . urlencode($currentDir['directory']) . "&back=" . urlencode(FSC::$app['requestUrl']);
                return $this->redirect($goUrl);
            }

            //扫描当前目录
            $scanner->setWebRoot($this->getCurrentWebroot($currentDir['realpath']));
            $scanner->setRootDir($currentDir['realpath']);
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
        $pageSize = $this->get('limit', 24);
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
                    array_push($videos, $item);
                    $index ++;
                }
            }
            return $this->renderJson(compact('page', 'pageSize', 'videos'));
        }


        //获取目录面包屑
        $breadcrumbs = $this->getBreadcrumbs($currentDir, $cachedParentData, $scanner);

        $isAdminIp = Common::isAdminIp($this->getUserIp());        //判断是否拥有管理权限

        $viewName = '//site/index';     //共享视图
        $params = compact(
            'cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'breadcrumbs', 'htmlCateReadme',
            'mp3File', 'page', 'pageSize', 'cacheDataId', 'copyright', 'showType', 'isAdminIp'
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
        $arr = explode($webroot, $currentDir['realpath']);
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

}
