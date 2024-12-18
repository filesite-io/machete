<?php
/**
 * Command Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class CommandController extends Controller {
    protected $logPrefix = '[MainBot]';
    protected $scanedDirCacheKey = 'MainBotScanedDirs';
    protected $dateIndexCacheKey = 'MainBotDateIndex';          //索引数据的key单独缓存，缓存key为此{cacheKey}_keys
    protected $dirCounterCacheKey = 'MainBotDirCounter';        //缓存所有目录包含的文件数量
    protected $noOriginalCtimeFilesCacheKey = 'MainBotNoOriginalCtimeFiles';
    protected $allFilesCacheKey = 'MainBotAllFiles';
    protected $allDirTreeCacheKey = 'MainBotAllDirTree';

    public function actionIndex() {
        $commands = <<<eof
Actions:
    - config [do=get&key=theme] //获取或修改系统配置
    - mainBot //扫描机器人程序

Usage:
    php command.php action parameters


eof;
        echo $commands;
        exit;
    }

    //查看、修改/runtime/custom_config.json里的内容
    public function actionConfig() {
        $themeName = FSC::$app['config']['theme'];

        $code = 1;
        $data = '';

        //修改配置文件
        $param_do = $this->get('do', 'set');    //支持：set, get, all, del
        $param_key = $this->get('key', '');
        $param_value = $this->get('val', '');

        if ($param_do == 'set' && empty($param_value)) {
            throw new Exception("缺少val参数！", 403);
        }else if (in_array($param_do, array('set', 'get', 'del')) && empty($param_key)) {
            throw new Exception("缺少key参数！", 403);
        }

        //val数据格式转换
        if ($param_value === 'false') {
            $param_value = false;
        }else if ($param_value === 'true') {
            $param_value = true;
        }

        $config_file = __DIR__ . "/../../../runtime/custom_config.json";
        if (file_exists($config_file)) {
            $content = file_get_contents($config_file);
            $configs = @json_decode($content, true);
            if (empty($configs)) {
                $config_file_template = __DIR__ . "/../../../conf/custom_config_{$themeName}.json";
                $content = file_get_contents($config_file_template);
                $configs = @json_decode($content, true);
            }
        }

        if (!empty($configs)) {
            switch($param_do) {
                case 'set':
                    $configs[$param_key] = $param_value;
                    file_put_contents($config_file, json_encode($configs, JSON_PRETTY_PRINT));
                    $data = $configs;
                    break;

                case 'get':
                    $data = !empty($configs[$param_key]) ? $configs[$param_key] : '';
                    break;

                case 'del':
                    unset($configs[$param_key]);
                    file_put_contents($config_file, json_encode($configs, JSON_PRETTY_PRINT));
                    $data = $configs;
                    break;

                case 'all':
                default:
                    $data = $configs;
                    break;
            }
        }


        $res = compact('code', 'data');

        echo "命令参数：\n";
        print_r($this->get());
        echo "\n";
        echo "命令执行结果：\n";
        print_r($res);
        echo "\n\n";
        exit;
    }


    //服务器端机器人程序
    /**
     * 扫描照片目录里所有子目录和文件
     * 建立年份、月份索引数据
     * 汇总每个目录下的照片、视频、MP3音乐文件数量
     * 记录没有original_ctime的文件，把它们单独归类
     */
    public function actionMainBot() {
        $thisTime = date('Y-m-d H:i:s');
        $botLogPrefix = $this->logPrefix;
        echo "{$botLogPrefix} Main bot started @{$thisTime}\n";


        //$menus = array();        //菜单，一级目录
        //$htmlReadme = array();   //Readme.md 内容，底部网站详细介绍
        //$htmlCateReadme = '';   //当前目录下的Readme.md 内容
        //$menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        while (true) {
            $time = date('Y-m-d H:i:s');
            echo "{$botLogPrefix} {$time}\n";

            $statsFile = __DIR__ . '/../../../runtime/cache/stats_scan.json';
            if (!file_exists($statsFile)) {
                //执行一次扫描任务
                $this->cleanScanCaches();
                $this->scanMediaFiles();
                $this->saveDateIndexIntoCacheFile();
                $this->saveNoOriginalCtimeFilesIntoFile();
                //缓存所有文件id跟文件信息，便于根据id列表来渲染，并按id首字母分子目录存放，以支持大量文件的场景
                $this->saveAllFilesIntoCacheFile();
                //缓存所有目录的文件数量
                $this->saveDirCounter();
            }else {
                try {
                    $json = file_get_contents($statsFile);
                    $stats = json_decode($json, true);
                    if ($stats['status'] == 'running') {
                        echo "{$botLogPrefix} It's already running...\n";
                    }else {
                        $date = date('Y-m-d H:i:s', $stats['updatetime']);
                        echo "{$botLogPrefix} It's finished at {$date}.\n";
                    }
                }catch(Exception $e) {
                    echo "{$botLogPrefix} Exception: " . $e->getMessage();
                }
            }

            sleep(5);
        }
    }

    //清空内存中的临时缓存数据
    protected function cleanScanCaches() {
        Common::setCache($this->scanedDirCacheKey, array());
        Common::setCache($this->dateIndexCacheKey, array());
        Common::setCache($this->noOriginalCtimeFilesCacheKey, array());
        Common::setCache($this->allFilesCacheKey, array());
        Common::setCache($this->dirCounterCacheKey, array());
        Common::setCache($this->allDirTreeCacheKey, array());
    }

    protected function getParentDir($dirpath) {
        $rootDir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'];
        $rootDir = realpath($rootDir);

        if ($dirpath == $rootDir) {
            return '';
        }

        if (strpos($dirpath, $rootDir) !== false) {
            $dirs = str_replace($rootDir, '', $dirpath);
            $dirs = preg_replace('/\/$/', '', $dirs);
            $arr = explode('/', $dirs);
            $num = count($arr);
            if ($num >= 1) {
                $left = array_slice($arr, 0, $num-1);
                return realpath( $rootDir . '/' . implode('/', $left) );
            }else {
                return '';
            }
        }

        return '';
    }

    //扫描媒体文件：图片、视频、音乐
    //TODO: 把它们按年份、月份归类，并缓存到/runtime/cache/目录，方便前端展示读取
    //把当前扫描进度保存到单独的缓存文件，便于用户随时获取
    //TODO: 没有original_ctime的视频文件调用exiftool获取拍摄时间
    protected function scanMediaFiles($dirpath = '') {
        $rootDir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'];
        if (empty($dirpath)) {
            $dirpath = realpath($rootDir);
        }

        echo "\n\n== Scanning directory {$dirpath} ...\n";
        $scanner = new DirScanner();
        $scanner->setWebRoot($this->getCurrentWebroot($dirpath));
        $scanner->setRootDir($dirpath);

        //尝试使用exiftool来获取视频的拍摄时间
        $scanner->exiftoolSupported = true;

        $maxScanDeep = 0;       //最大扫描目录级数
        $dirTree = $scanner->scan($dirpath, $maxScanDeep);

        //统计文件数量
        $dirId = $scanner->getId($dirpath);
        $this->updateAllDirTreeCache($dirId, $dirpath, $dirTree, $scanner);

        $scanResults = $scanner->getScanResults();
        echo 'Total directories or files: ' . count($scanResults);
        echo "\n";

        $supportedImageExts = FSC::$app['config']['supportedImageExts'];
        $supportedVideoExts = FSC::$app['config']['supportedVideoExts'];
        $supportedAudioExts = FSC::$app['config']['supportedAudioExts'];
        $cacheKey = $this->scanedDirCacheKey;

        if (!empty($scanResults)) {
            $scanIndex = 0;
            $scanTotal = count($scanResults);

            foreach ($scanResults as $id => $item) {
                $hadScanedDirs = Common::getCache($cacheKey);

                //忽略.txt描述文件
                if (
                    !empty($item['filename']) && !empty($item['extension'])
                    && (
                        in_array($item['extension'], $supportedImageExts)
                        || in_array($item['extension'], $supportedVideoExts)
                        || in_array($item['extension'], $supportedAudioExts)
                    )
                ) {
                    //保存所有文件到索引
                    $this->updateAllFilesCache($item);
                    //更新年份、月份时间索引
                    $this->updateDateIndex($item);
                    //更新没有拍摄时间的文件索引
                    $this->updateNoOriginalCtimeFiles($item);
                }

                if (
                    !empty($item['filename'])
                    && empty($item['original_ctime'])
                    && in_array($item['extension'], $supportedImageExts)
                    && !in_array($item['extension'], $scanner->exifSupportFileExtensions)
                ) {
                    echo "Image file no original_ctime: {$item['filename']}.{$item['extension']}, {$item['realpath']}\n";
                }else if (
                    !empty($item['filename'])
                    && empty($item['original_ctime'])
                    && (in_array($item['extension'], $supportedVideoExts) || in_array($item['extension'], $supportedAudioExts))
                ) {
                    echo "Video or audio file no original_ctime: {$item['filename']}.{$item['extension']}, {$item['realpath']}\n";
                }else if (!empty($item['directory']) && empty($hadScanedDirs[$id])) {     //if it's directory
                    $hadScanedDirs[$id] = true;
                    Common::setCache($cacheKey, $hadScanedDirs);

                    $this->scanMediaFiles($item['realpath']);
                }

                //更新扫描进度
                $scanIndex ++;
                $stats = $this->updateScanStats($dirpath, $scanTotal, $scanIndex);
            }

            sleep(1);
        }
    }

    //更新扫描进度
    protected function updateScanStats($dirpath, $total, $index) {
        if (empty($total)) {return false;}

        $stats = array(
            'updatetime' => time(),
            'currentDir' => $dirpath,
            'total' => $total,
            'current' => $index,
            'percent' => floor($index*100/$total),
            'status' => 'running',
        );

        $botLogPrefix = $this->logPrefix;
        $cacheDir = __DIR__ . '/../../../runtime/cache/';
        $statsFile = "{$cacheDir}stats_scan.json";
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $rootDir = realpath(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory']);
        if ($dirpath == $rootDir) {
            if ($index == $total) {
                $stats['status'] = 'finished';
            }

            echo "{$botLogPrefix} Scan has finished {$stats['percent']}%, total {$stats['total']}, current {$stats['current']}\n";

            //保存进度文件
            file_put_contents($statsFile, json_encode($stats) . "\n");
            chmod($statsFile, 0777);
        }else if (file_exists($statsFile)) {        //更新当前扫描目录
            $json = file_get_contents($statsFile);
            if (!empty($json)) {
                $jsonData = json_decode(trim($json), true);
                if ($jsonData['currentDir'] != $dirpath) {
                    $jsonData['currentDir'] = $dirpath;
                    $json = json_encode($jsonData) . "\n";
                    file_put_contents($statsFile, $json);
                }
            }
        }

        return $stats;
    }

    //建立年份、月份时间索引
    /**
     * 数据格式：
     * {"y2024": {"m1": [id1, id2, ...], "m10": [id1, id2, ...]}}
     */
    protected function updateDateIndex($file) {
        $ctime = !empty($file['original_ctime']) ? $file['original_ctime'] : Common::getFileCreateTime($file);

        $cacheKey = $this->dateIndexCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            $cacheData = array();
        }

        $year = 'y' . date('Y', $ctime);
        $month = 'm' . date('m', $ctime);
        if (empty($cacheData[$year])) {
            $cacheData[$year] = array();
        }
        if (empty($cacheData[$year][$month])) {
            $cacheData[$year][$month] = array();
        }

        if (in_array($file['id'], $cacheData[$year][$month])) {
            return false;
        }

        array_push($cacheData[$year][$month], $file['id']);

        return Common::setCache($cacheKey, $cacheData);
    }

    //按年、月保存文件数据缓存，以便前端展示
    protected function saveAllFilesGroupedByDate($dateIndexes) {
        $cacheKey = $this->allFilesCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        $dateCacheKey = $this->dateIndexCacheKey;
        $cacheDir = 'index';
        foreach($dateIndexes as $year => $fileIdsInMonth) {
            $tmpData = [];
            foreach($fileIdsInMonth as $month => $ids) {
                $tmpData[$month] = [];
                foreach($ids as $id) {
                    $tmpData[$month][$id] = $cacheData[$id];
                }
            }

            Common::saveCacheToFile("{$dateCacheKey}_{$year}", $tmpData, $cacheDir);
        }
    }

    protected function saveDateIndexIntoCacheFile() {
        $cacheKey = $this->dateIndexCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        $cacheDir = 'index';

        //save index keys
        $indexKeys = [];
        foreach ($cacheData as $year => $item) {
            $indexKeys[$year] = array_keys($item);
            $indexKeys[$year]['total'] = 0;
            foreach($item as $month => $ids) {
                $indexKeys[$year]['total'] += count($ids);
            }
        }
        Common::saveCacheToFile("{$cacheKey}_keys", $indexKeys, $cacheDir);

        //按年、月保存文件数据缓存，以便前端展示
        $this->saveAllFilesGroupedByDate($cacheData);

        return Common::saveCacheToFile($cacheKey, $cacheData, $cacheDir);
    }

    protected function updateAllFilesCache($file) {
        $cacheKey = $this->allFilesCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            $cacheData = array();
        }

        $cacheData[$file['id']] = $file;
        return Common::setCache($cacheKey, $cacheData);
    }

    //根据文件id首字母以及需要分批存储的数量，来对文件进行分组
    protected function getFilesByFirstCharcter($files, $dirNum) {
        $byFirst = [];

        foreach ($files as $id => $item) {
            $index = Common::getIndexNumByFileId($id, $dirNum);
            if (empty($byFirst[$index])) {
                $byFirst[$index] = [];
            }
            $byFirst[$index][$id] = $item;
        }

        return $byFirst;
    }

    protected function saveAllFilesIntoCacheFile() {
        $cacheKey = $this->allFilesCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        $total = count($cacheData);
        $dirNum = 1;

        if ($total > 1000 && $total <= 10000) {
            $dirNum = 10;
        }else if ($total > 10000){
            $dirNum = 100;
        }

        $filesByFirstChar = $this->getFilesByFirstCharcter($cacheData, $dirNum);
        $cacheDir = 'index';
        for ($i=1;$i<=$dirNum;$i++) {
            if (!empty($filesByFirstChar[$i-1])) {
                Common::saveCacheToFile("{$cacheKey}_{$i}", $filesByFirstChar[$i-1], $cacheDir);
            }
        }

        //保存文件总数，以及分批数量，以便前端根据id来索引数据
        $statsData = [
            'filetotal' => $total,
            'dirnum' => $dirNum,
        ];
        Common::saveCacheToFile("{$cacheKey}_stats", $statsData, $cacheDir);

        return true;
    }

    protected function updateAllDirTreeCache($dirId, $dirpath, $dirTree, $scanner) {
        $cacheKey = $this->allDirTreeCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            $cacheData = array();
        }

        $cacheData = array_merge($cacheData, $dirTree);
        if (empty($cacheData[$dirId])) {
            $cacheData[$dirId] = $dirTree;
        }

        $supportedImageExts = FSC::$app['config']['supportedImageExts'];
        $supportedVideoExts = FSC::$app['config']['supportedVideoExts'];
        $supportedAudioExts = FSC::$app['config']['supportedAudioExts'];
        $imgNum = $videoNum = $audioNum = 0;
        foreach ($dirTree as $id => $item) {
            if (empty($item['pid'])) {
                echo "Ignored file no pid: {$id}\n";
                //print_r($item);
                //echo "\n";
                continue;
            }

            if (
                !empty($item['filename']) && in_array($item['extension'], $supportedImageExts)
            ) {
                $imgNum ++;
            }else if (
                !empty($item['filename']) && in_array($item['extension'], $supportedVideoExts)
            ) {
                $videoNum ++;
            }else if (
                !empty($item['filename']) && in_array($item['extension'], $supportedAudioExts)
            ) {
                $audioNum ++;
            }
        }

        $cacheData[$dirId]['image_total'] = $imgNum;
        $cacheData[$dirId]['video_total'] = $videoNum;
        $cacheData[$dirId]['audio_total'] = $audioNum;
        echo "File total: {$dirId}: image {$imgNum}, video {$videoNum}, audio {$audioNum}\n";

        //更新所有父目录数据
        $parentDir = $this->getParentDir($dirpath);
        while (!empty($parentDir)) {
            //echo "{$dirpath} => {$parentDir}\n";
            $parentId = $scanner->getId($parentDir);
            if (!empty($cacheData[$parentId])) {
                if (isset($cacheData[$parentId]['image_total'])) {
                    $cacheData[$parentId]['image_total'] += $imgNum;
                }else {
                    $cacheData[$parentId]['image_total'] = $imgNum;
                }

                if (isset($cacheData[$parentId]['video_total'])) {
                    $cacheData[$parentId]['video_total'] += $videoNum;
                }else {
                    $cacheData[$parentId]['video_total'] = $videoNum;
                }

                if (isset($cacheData[$parentId]['audio_total'])) {
                    $cacheData[$parentId]['audio_total'] += $audioNum;
                }else {
                    $cacheData[$parentId]['audio_total'] = $audioNum;
                }
            }

            $dirpath = $parentDir;
            $parentDir = $this->getParentDir($dirpath);
        }

        return Common::setCache($cacheKey, $cacheData);
    }

    protected function getAllFilesTotalInSubDirs() {

    }

    //汇总每个目录下图片、视频、音乐文件数量
    /**
     * 数据格式：
     * {dirid: {image: 10, video: 20, audio: 0}, ...}
     */
    protected function saveDirCounter() {
        $cacheKey = $this->allDirTreeCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        $dirCounter = array();
        foreach ($cacheData as $id => $item) {
            if ( isset($item['image_total']) ) {
                $dirCounter[$id] = array(
                    'image_total' => $item['image_total'],
                    'video_total' => $item['video_total'],
                    'audio_total' => $item['audio_total'],
                );
            }
        }

        $saveKey = $this->dirCounterCacheKey;
        $cacheDir = 'index';
        Common::saveCacheToFile($saveKey, $dirCounter, $cacheDir);
    }

    //归类没有original_ctime的图片、视频文件
    /**
     * 数据格式：
     * [id1, id2, ...]
     */
    protected function updateNoOriginalCtimeFiles($file) {
        if (!empty($file['original_ctime'])) {
            return false;
        }

        $cacheKey = $this->noOriginalCtimeFilesCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            $cacheData = array();
        }

        if (in_array($file['id'], $cacheData)) {
            return false;
        }

        array_push($cacheData, $file['id']);

        return Common::setCache($cacheKey, $cacheData);
    }

    protected function saveNoOriginalCtimeFilesIntoFile() {
        $cacheKey = $this->noOriginalCtimeFilesCacheKey;
        $cacheData = Common::getCache($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        $cacheDir = 'index';
        return Common::saveCacheToFile($cacheKey, $cacheData, $cacheDir);
    }

    public function actionTest() {
        $cacheKey = 'TestSTData';
        $time = Common::getCache($cacheKey);
        if (empty($time)) {
            $time = date('Y-m-d H:i:s');
            Common::setCache($cacheKey, $time);
        }

        echo "Cache time {$time}\n";
    }

}
