<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

$selectedId = !empty($viewData['cateId']) ? $viewData['cateId'] : '';

$imgExts = array('jpg', 'jpeg', 'png', 'gif');
$videoExts = array('url');
$category = $viewData['scanResults'][$selectedId];
$total_my_videos = 0;
if (!empty($category['files'])) {
    foreach ($category['files'] as $file) {
        if (!in_array($file['extension'], $videoExts) || empty($file['shortcut'])) {
            continue;
        }

        $total_my_videos ++;
    }
}

$allTags = Html::getTagNames($viewData['tags']);
?>

<main class="g_main_lay">
    <div class="breadcrumbs">
        <a href="<?=$linkPrefix?>/my/">&lt;&lt;返回</a>
    </div>
<h3 class="mt20">你已收藏 <?=$total_my_videos?> 个视频</h3>
<p class="mt10">勾选视频下方的分类，将该视频归类到对应的分类；取消勾选，则将视频从该分类中移除。</p>

<form action="" metho="GET">
    <select name="tag">
        <option value="">选择分类</option>
        <?php
        foreach($allTags as $tagName) {
            $selected = !empty($viewData['selectTag']) && $viewData['selectTag'] == $tagName ? ' selected' : '';
            echo <<<eof
        <option value="{$tagName}"{$selected}>{$tagName}</option>
eof;
        }
        ?>
    </select>
    <input type="text" name="keyword" class="ipt" placeholder="输入关键词" value="<?=htmlspecialchars($viewData['searchKeyword'])?>">
    <button type="submit" class="btn-primary">筛选</button>
</form>

<div class="videos_list clearfix" id="favmg">
    <?php
        

        if (!empty($category['files'])) {        //一级目录支持，目录下直接存放视频文件

            $cate_files = Html::sortFilesByCreateTime($category['files'], 'desc');    //按创建时间排序
            foreach($cate_files as $index => $file) {
                //跳过非.url文件
                if (!in_array($file['extension'], $videoExts) || empty($file['shortcut'])) {
                    continue;
                }

                $myTags = Html::getFavsTags($file['filename'], $viewData['tags']);

                //分类筛选支持
                if (!empty($viewData['selectTag']) && !in_array($viewData['selectTag'], $myTags)) {
                    continue;
                }

                //关键词搜索支持
                if (!empty($viewData['searchKeyword']) && strpos($file['title'], $viewData['searchKeyword']) === false) {
                    continue;
                }

                $snapshot = !empty($file['cover']) ? Html::getCDNImageUrl($imgPreffix . $file['cover']) : '/img/default.png';
                $title = !empty($file['title']) ? Html::mb_substr($file['title'], 0, 33, 'utf-8') : $file['filename'];

                $platform = Html::getShareVideosPlatform($file['shortcut']['url']);
                
                $pubDate = date('m/d', $file['fstat']['ctime']);

                $imgSrc = $index < 4 ? " src=\"{$snapshot}\"" : '';
                $imgAlt = $index < 4 ? " alt=\"{$title}\"" : '';
                $imgCls = $index < 4 ? '' : 'lazy';

                $tagsHtml = '';

                foreach ($allTags as $tagName) {
                    $tagChecked = in_array($tagName, $myTags) ? ' checked="checked"' : '';
                    $tagsHtml .= <<<eof
    <label data-filename="{$file['filename']}" data-video-id="{$file['id']}" data-tag="{$tagName}"><input type="checkbox" value="{$tagName}"{$tagChecked}> {$tagName}</label>
eof;
                }

                echo <<<eof
    <div class="vl_list_main advideo-item favmg-item">
        <div class="video_img_vl">
            <a href="{$file['shortcut']['url']}" target="_blank">
                <img data-original="{$snapshot}" class="{$imgCls}"{$imgSrc}{$imgAlt}>
            </a>
            <button type="button" class="btn-danger btn-del" data-video-id="{$file['id']}" data-filename="{$file['filename']}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"></path>
                </svg>
                删除
            </button>
        </div>
        <div class="video_title_vl">
            <a href="{$file['shortcut']['url']}" target="_blank">
                <span class="duration">{$platform}</span>
                <strong>{$pubDate}，{$title}</strong>
            </a>
        </div>
        <div class="act_tags">
            {$tagsHtml}
        </div>
    </div>
eof;
            }
        }
    ?>
</div>
</main>