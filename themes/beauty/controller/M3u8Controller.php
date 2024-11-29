<?php
/**
 * M3u8 Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Common.php';

Class M3u8Controller extends Controller {
    protected $allFilesCacheKey = 'MainBotAllFiles';

    //参数
    //@id - 文件id
    //@cid - 数据缓存id
    //支持nginx secure防盗链：md5={$md5}&expires={$expires}
    //支持只传video id，从索引数据中获取文件信息
    public function actionIndex() {
        $videoId = $this->get('id', '');
        $cacheParentDataId = $this->get('cid', '');
        if (empty($videoId)) {
            throw new Exception("参数缺失！", 403);
        }

        //TODO: 防盗链检查


        if (!empty($cacheParentDataId)) {
            $cacheSeconds = 86400;
            $cachedParentData = Common::getCacheFromFile($cacheParentDataId, $cacheSeconds);
            if (empty($cachedParentData)) {
                $err = '缓存数据已失效，如果重新点击目录依然打不开，请联系管理员。';
                throw new Exception($err, 404);
            }
        }

        if (!empty($cachedParentData) && empty($cachedParentData[$videoId])) {
            $erro = "缓存数据中找不到当前视频，请返回上一页重新进入！";
            throw new Exception($err, 404);
        }else if (!empty($cachedParentData[$videoId])) {
            //渲染m3u8内容
            $m3u8 = $cachedParentData[$videoId];
            $m3u8Content = $this->getM3u8Content($m3u8['realpath'], $cachedParentData);
            if (!empty($m3u8Content)) {
                return $this->renderM3u8($m3u8Content);
            }else {
                $err = 'm3u8内容为空！';
                throw new Exception($err, 500);
            }
        }else {     //尝试从索引数据中获取文件信息
            $cacheDir = 'index';
            $expireSeconds = 86400 * 365;    //缓存 365 天
            $cacheData = Common::getCacheFromFile($this->allFilesCacheKey . '_stats', $expireSeconds, $cacheDir);
            if (!empty($cacheData)) {
                $dirNum = $cacheData['dirnum'];
                $index = Common::getIndexNumByFileId($videoId, $dirNum) + 1;
                $cacheKey = $this->allFilesCacheKey . "_{$index}";
                $cacheFiles = Common::getCacheFromFile($cacheKey, $expireSeconds, $cacheDir);

                if (!empty($cacheFiles[$videoId])) {
                    $m3u8 = $cacheFiles[$videoId];
                    $m3u8Content = $this->getM3u8Content($m3u8['realpath']);
                    if (!empty($m3u8Content)) {
                        return $this->renderM3u8($m3u8Content);
                    }else {
                        $err = 'm3u8内容为空！';
                        throw new Exception($err, 500);
                    }
                }else {
                    $err = '索引数据中找不到此视频！';
                    throw new Exception($err, 500);
                }
            }else {
                $err = '没有索引数据，请参考文档生成索引！';
                throw new Exception($err, 500);
            }
        }
    }

    //生成m3u8内容，支持ts防盗链
    /**
     * M3u8 content sample:
#EXTM3U
#EXT-X-VERSION:3
#EXT-X-TARGETDURATION:6
#EXT-X-PLAYLIST-TYPE:VOD
#EXTINF:5,
0.ts
#EXTINF:5,
1.ts
#EXTINF:5,
2.ts
#EXTINF:5,
3.ts
#EXTINF:5,
4.ts
#EXTINF:0.31697,
5.ts
#EXT-X-ENDLIST
     **/
    protected function getM3u8Content($m3u8_realpath, $cachedParentData = array()) {
        $m3u8Content = file_get_contents($m3u8_realpath);
        if (empty($m3u8Content) || strpos($m3u8Content, 'EXTM3U') === false) {
            return false;
        }

        $lines = preg_split("/[\r\n]/", $m3u8Content);
        
        $newContent = '';
        foreach($lines as $index => $line) {
            if (strpos($line, '.ts') !== false) {
                $newContent .= $this->getRelativePathOfTs($line, $m3u8_realpath, $cachedParentData) . "\n";
            }else if (!empty($line)) {
                $newContent .= $line . "\n";
            }
        }

        return $newContent;
    }

    //返回ts相对当前m3u8文件的相对路径
    //TODO: 支持防盗链
    protected function getRelativePathOfTs($ts_filename, $m3u8_realpath, $cachedParentData = array()) {
        if (!empty($cachedParentData)) {
            $matchedTs = null;
            foreach($cachedParentData as $item) {
                if (!empty($item['extension']) && $item['extension'] == 'ts' && strpos($item['path'], $ts_filename) !== false) {
                    $matchedTs = $item;
                    break;
                }
            }

            if (!empty($matchedTs)) {
                return $matchedTs['path'];
            }
        }

        $webroot = FSC::$app['config']['content_directory'];
        $rootDir = __DIR__ . '/../../../www/' . $webroot;
        $rootDir = realpath($rootDir);
        $m3u8Dir = dirname($m3u8_realpath);
        $relativeDir = str_replace("{$rootDir}/", '', $m3u8Dir);
        return "/{$webroot}{$relativeDir}/{$ts_filename}";
    }

}