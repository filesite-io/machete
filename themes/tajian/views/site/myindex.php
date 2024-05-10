<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

if (!empty($breadcrumbs)) {
    echo <<<eof
    <div class="breadcrumbs">
        <a href="{$linkPrefix}/">首页</a> &gt;&gt;
eof;

    foreach($breadcrumbs as $bread) {
        if ($bread['id'] != $selectedId) {
            echo <<<eof
        <a href="{$bread['url']}">{$bread['name']}</a> / 
eof;
        }else {
            echo <<<eof
        <strong>{$bread['name']}</strong>
eof;
        }
    }

    echo <<<eof
    </div>
eof;
}
?>

<main class="g_main_lay">
<?php
//如果是打开的自己的首页
if (!empty($viewData['loginedUser']) && FSC::$app['user_id'] == $viewData['loginedUser']['username']) {
    echo <<<eof
<p class="mt20 pb30">欢迎回来，把当前网址发给朋友，跟TA分享你的收藏吧。</p>
eof;
}
?>
<div class="videos_list clearfix">
<?php
$videoExts = array('url');

if (!empty($viewData['tags'])) {        //显示tags分类
    $tagIndex = 0;
    foreach($viewData['tags'] as $id => $item) {
        $category = $viewData['scanResults'][$item['id']];
        if (!empty($category['files'])) {        //一级目录支持，目录下直接存放视频文件

            //输出分类名称
            echo <<<eof
        <div class="tgroup">
            <a href="{$linkPrefix}/list/?id={$id}" class="morelink">更多&gt;&gt;</a>
            <h3>{$item['name']}</h3>
        </div>
        <div class="clearfix">
eof;



            $cate_files = Html::sortFilesByCreateTime($category['files'], 'desc');    //按创建时间排序
            foreach($cate_files as $index => $file) {
                //跳过非.url文件，且最多显示 8 个
                if (!in_array($file['extension'], $videoExts) || empty($file['shortcut']) || $index >= 8) {
                    continue;
                }

                $snapshot = !empty($file['cover']) ? $imgPreffix . $file['cover'] : '/img/default.png';
                $title = !empty($file['title']) ? Html::mb_substr($file['title'], 0, 33, 'utf-8') : $file['filename'];

                $platform = Html::getShareVideosPlatform($file['shortcut']['url']);
                
                $pubDate = date('m/d', $file['fstat']['ctime']);

                $imgSrc = $tagIndex == 0 && $index < 4 ? " src=\"{$snapshot}\"" : '';
                $imgAlt = $tagIndex == 0 && $index < 4 ? " alt=\"{$title}\"" : '';
                $imgCls = $tagIndex == 0 && $index < 4 ? '' : 'lazy';
                $itemCls = $index < 4 ? '' : 'hidden-xs';

                echo <<<eof
    <div class="vl_list_main advideo-item {$itemCls}">
        <div class="video_img_vl">
            <a href="{$file['shortcut']['url']}" target="_blank">
                <img data-original="{$snapshot}" class="{$imgCls}"{$imgSrc}{$imgAlt}>
            </a>
        </div>
        <div class="video_title_vl">
            <a href="{$file['shortcut']['url']}" target="_blank">
                <span class="duration">{$platform}</span>
                <strong>{$pubDate}，{$title}</strong>
            </a>
        </div>
    </div>
eof;
            }
        }

        echo <<<eof
        </div>
eof;

        $tagIndex ++;
    }
}
?>
</div>
</main>
