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
<div class="videos_list clearfix">
    <?php
        $imgExts = array('jpg', 'jpeg', 'png', 'gif');
        $videoExts = array('url');
        $category = !empty($viewData['scanResults'][$selectedId]) ? $viewData['scanResults'][$selectedId] : array();

        //当前目录的描述介绍
        if (!empty($category['description'])) {
            echo <<<eof
    <p class="catedesc">{$category['description']}</p>
eof;
        }

        if (!empty($category['files'])) {        //一级目录支持，目录下直接存放视频文件

            $cate_files = Html::sortFilesByCreateTime($category['files'], 'desc');    //按创建时间排序
            foreach($cate_files as $index => $file) {
                //跳过非.url文件
                if (!in_array($file['extension'], $videoExts) || empty($file['shortcut'])) {
                    continue;
                }

                $snapshot = !empty($file['cover']) ? Html::getCDNImageUrl($imgPreffix . $file['cover']) : '/img/default.png';
                $title = !empty($file['title']) ? Html::mb_substr($file['title'], 0, 33, 'utf-8') : $file['filename'];

                $platform = Html::getShareVideosPlatform($file['shortcut']['url']);
                $pubDate = date('m/d', min($file['fstat']['mtime'], $file['fstat']['ctime']));

                $imgSrc = $index < 4 ? " src=\"{$snapshot}\"" : '';
                $imgAlt = $index < 4 ? " alt=\"{$title}\"" : '';
                $imgCls = $index < 4 ? '' : 'lazy';

                echo <<<eof
    <div class="vl_list_main advideo-item">
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
        }else {
            echo <<<eof
    <div class="vercenter mt20">
        <h3>你还没有收藏视频到当前分类哦。</h3>
        <p class="mt10">
            <a href="{$linkPrefix}/site/new">
                <img src="/img/addvideos.svg" alt="添加收藏图标" width="50">
                <br>
                现在添加
            </a>
        </p>
    </div>
eof;
        }
    ?>
</div>
</main>
