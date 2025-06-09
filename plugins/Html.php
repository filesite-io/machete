<?php
/**
 * 常用的处理HTML的方法
 */
Class Html {
    //获取js、css文件的修改时间作版本号
    public static function getStaticFileVersion($filename, $type = 'css') {
        $ver = 0;
        $filepath = '';

        switch ($type) {
            case 'css':
                $filepath = __DIR__ . '/../www/css/' . $filename;
                break;
            
            default:
                $filepath = __DIR__ . '/../www/js/' . $filename;
                break;
        }

        if (!empty($filepath) && file_exists($filepath)) {
            $fp = fopen($filepath, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $ver = $fstat['mtime'];
        }

        return $ver;
    }

    //优先获取皮肤目录下的favicon.ico
    public static function getFaviconUrl() {
        $filename = 'favicon.ico';
        $filepath = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'] . $filename;
        $url = '/' . FSC::$app['config']['content_directory'] . $filename;

        if (file_exists($filepath) == false) {
            $filepath = __DIR__ . '/../www/' . $filename;
            $url = '/favicon.ico';
        }

        if (file_exists($filepath)) {
            $fp = fopen($filepath, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $ver = $fstat['mtime'];
            $url .= "?v{$ver}";
        }

        return $url;
    }

    public static function mb_substr($string, $start, $length) {
        if (mb_strlen($string, 'utf-8') <= $length) {return $string;}

        return mb_substr($string, $start, $length, 'utf-8') . "...";
    }

    //默认支持所有网站
    public static function getShareVideosPlatform($url) {
        $platform = '其它';

        if (preg_match("/douyin\.com/i", $url)) {
            $platform = '抖音';
        }else if (preg_match("/kuaishou\.com/i", $url)) {
            $platform = '快手';
        }else if (preg_match("/ixigua\.com/i", $url)) {
            $platform = '西瓜视频';
        }else if (preg_match("/b23\.tv/i", $url) || preg_match("/bilibili\.com/i", $url)) {
            $platform = 'B站';
        }

        return $platform;
    }

    //根据ctime进行排序，默认按时间先后倒序排列
    public static function sortFilesByCreateTime($files, $way = 'desc') {
        $ctimes = array();
        foreach($files as $id => $item) {
            $ctimes[$id] = min($item['fstat']['ctime'], $item['fstat']['mtime']);
        }

        if ($way == 'desc') {
            arsort($ctimes, SORT_NUMERIC);
        }else {
            asort($ctimes, SORT_NUMERIC);
        }

        $sorted = array();

        foreach($ctimes as $id => $ctime) {
            array_push($sorted, $files[$id]);
        }

        return $sorted;
    }

    //生成GA统计代码
    //支持conversion代码，示例：gtag('event', 'conversion', {'send_to': 'xxx/NUaEOrczuQD'});
    public static function getGACode() {
        if (!empty(FSC::$app['config']['debug'])) {return '';}
        $msid = !empty(FSC::$app['config']['GA_MEASUREMENT_ID']) ? FSC::$app['config']['GA_MEASUREMENT_ID'] : '';
        if (empty($msid)) {return '';}

        $adwords_id = !empty(FSC::$app['config']['GAD_MEASUREMENT_ID']) ? FSC::$app['config']['GAD_MEASUREMENT_ID'] : '';

        $gacode = <<<eof
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$msid}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{$msid}');
eof;

        if (!empty($adwords_id)) {
            $gacode .= <<<eof

  gtag('config', '{$adwords_id}');
eof;
        }


        $conversions = !empty(FSC::$app['config']['GA_MEASUREMENT_CONVERSIONS']) ? FSC::$app['config']['GA_MEASUREMENT_CONVERSIONS'] : array();
        if (!empty($conversions)) {
            //格式：{'controller': 'xx', 'action': 'yy', 'send_to': 'zzz'}
            foreach ($conversions as $item) {
                if (FSC::$app['controller'] == $item['controller'] && FSC::$app['action'] == $item['action']) {
                    $gacode .= <<<eof

  gtag('event', 'conversion', {'send_to': '{$item['send_to']}'});
eof;
                    break;
                }
            }

        }

        $gacode .= <<<eof
</script>
eof;

        return $gacode;
    }


    //根据收藏和分类，获取单个收藏视频的所在分类
    public static function getFavsTags($filename, $tags) {
        $fileTags = array();

        foreach($tags as $tag_id => $item) {
            if (in_array($filename, $item['files'])) {
                array_push($fileTags, $item['name']);
            }
        }

        return $fileTags;
    }

    //获取只包含分类名的数组
    public static function getTagNames($tags) {
        $tmp_arr = array();

        foreach ($tags as $id => $tag) {
            array_push($tmp_arr, $tag['name']);
        }

        return $tmp_arr;
    }

    //获取用户图片的cdn地址
    public static function getCDNImageUrl($localImgUrl) {
        if (!empty(FSC::$app['config']['debug'])) {return $localImgUrl;}

        $cdn = FSC::$app['config']['img_cdn_budget_url'];
        if (empty($cdn)) {return $localImgUrl;}

        return "{$cdn}{$localImgUrl}";
    }

    //根据文件类型，获取数组中符合条件文件总数
    public static function getDataTotal($files, $fileTypes) {
        $total = 0;

        foreach ($files as $file) {
            if (empty($file['extension']) || !in_array($file['extension'], $fileTypes)) {
                continue;
            }

            $total ++;
        }

        return $total;
    }

    //根据指定参数，以及当前网址，生成新的网址
    public static function getLinkByParams($url, $getParams = array()) {
        $arr = explode('?', $url);
        if (count($arr) == 1) {     //不含问号
            return "{$url}?" . http_build_query($getParams);
        }

        $newParms = array();
        $baseUrl = $arr[0];
        $queryString = $arr[1];
        $params = explode('&', $queryString);
        if (count($params) > 0) {
            foreach ($params as $item) {
                list($name, $val) = explode('=', $item);
                if (!isset($getParams[$name])) {
                    array_push($newParms, $item);
                }
            }
        }

        return "{$baseUrl}?" . http_build_query($getParams) . (!empty($newParms) ? '&'.implode('&', $newParms) : '');
    }

    //参数：page、limit
    public static function getPaginationLink($url, $page, $limit = 24) {
        $paginationParams = compact('page', 'limit');

        return self::getLinkByParams($url, $paginationParams);
    }

    //输出翻页组件，page从1开始
    public static function getPaginationHtmlCode($page, $limit, $total) {
        $currentUrl = FSC::$app['requestUrl'];
        $maxPage = ceil($total / $limit);

        //上一页
        $previousLink = <<<eof
        <li class="page-item disabled">
            <span class="page-link">上<span class="hidden-xs">一</span>页</span>
        </li>
eof;
        if ($page > 1) {
            $url = self::getPaginationLink($currentUrl, $page-1, $limit);
            $previousLink = <<<eof
        <li class="page-item">
            <a class="page-link" href="{$url}">上<span class="hidden-xs">一</span>页</a>
        </li>
eof;
        }

        //下一页
        $nextLink = <<<eof
        <li class="page-item disabled">
            <span class="page-link">下<span class="hidden-xs">一</span>页</span>
        </li>
eof;
        if ($page < $maxPage) {
            $url = self::getPaginationLink($currentUrl, $page+1, $limit);
            $nextLink = <<<eof
        <li class="page-item">
            <a class="page-link" href="{$url}">下<span class="hidden-xs">一</span>页</a>
        </li>
eof;
        }

        //包括当前页一共显示 10 页
        $otherLinks = '';
        $startPage = floor(($page-1) / 10)*10 + 1;
        $endPage = $startPage + 9 < $maxPage ? $startPage + 9 : $maxPage;
        for ($i = $startPage; $i <= $endPage; $i ++) {
            $url = self::getPaginationLink($currentUrl, $i, $limit);
            if ($i != $page) {
                $otherLinks .= <<<eof
        <li class="page-item"><a class="page-link" href="{$url}">{$i}</a></li>
eof;
            }else {
                $otherLinks .= <<<eof
        <li class="page-item active" aria-current="page">
            <span class="page-link">{$i}</span>
        </li>
eof;
            }
        }

        $html = <<<eof
<nav aria-label="翻页">
    <ul class="pagination">
        {$previousLink}
        {$otherLinks}
        {$nextLink}
    </ul>
</nav>
eof;

        return $html;
    }

    public static function getMediaSourceType($fileExtension) {
        $sourceType = 'video/mp4';

        if ($fileExtension == 'mov') {
            $sourceType = 'video/mp4';
        }else if ($fileExtension == 'm3u8') {
            $sourceType = 'application/x-mpegURL';
        }else if ($fileExtension == 'mp3') {
            $sourceType = 'audio/mp3';
        }

        return $sourceType;
    }

    //根据文件名，找出同名的图片文件
    public static function searchImageByFilename($filename, $files, $imgExts = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'svg')) {
        $matchedImage = null;

        if (!empty($files)) {
            foreach($files as $item) {
                if (!empty($item['filename']) && $item['filename'] == $filename && in_array($item['extension'], $imgExts)) {
                    $matchedImage = $item;
                    break;
                }
            }
        }

        return $matchedImage;
    }

}
