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

    public static function mb_substr($string, $start, $length) {
        if (mb_strlen($string, 'utf-8') <= $length) {return $string;}

        return mb_substr($string, $start, $length, 'utf-8') . "...";
    }

    public static function getShareVideosPlatform($url) {
        $platform = '-';

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
            $ctimes[$id] = $item['fstat']['ctime'];
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
    public static function getGACode() {
        if (!empty(FSC::$app['config']['debug'])) {return '';}
        $msid = !empty(FSC::$app['config']['GA_MEASUREMENT_ID']) ? FSC::$app['config']['GA_MEASUREMENT_ID'] : '';
        if (empty($msid)) {return '';}

        $gacode = <<<eof
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$msid}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{$msid}');
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

}
