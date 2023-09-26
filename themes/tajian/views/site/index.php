<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

?><div class="menu">
<?php
$selectedId = $viewData['cateId'];
$breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
if (!empty($viewData['tags'])) {        //显示tags分类
    foreach($viewData['tags'] as $id => $item) {
        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'selected' : '';
        echo <<<eof
        <a href="/list/?id={$item['id']}" class="{$selected}">{$item['name']}</a>
eof;
    }
}
?>
</div>

<div class="hr"></div>

<?php
if (!empty($breadcrumbs)) {
    echo <<<eof
    <div class="breadcrumbs">
        <a href="/">首页</a> &gt;&gt;
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

include_once __DIR__ . '/form_addfav.php';
?>

<div class="content">
    <?php
        $imgExts = array('jpg', 'jpeg', 'png', 'gif');
        $videoExts = array('url');
        $category = $viewData['scanResults'][$selectedId];

        //当前目录的描述介绍
        if (!empty($category['description'])) {
            echo <<<eof
    <p class="catedesc">{$category['description']}</p>
eof;
        }

        if (!empty($category['files'])) {        //一级目录支持，目录下直接存放视频文件

            $cate_files = Html::sortFilesByCreateTime($category['files'], 'desc');    //按创建时间排序
            foreach($cate_files as $file) {
                //跳过非.url文件
                if (!in_array($file['extension'], $videoExts) || empty($file['shortcut'])) {
                    continue;
                }

                $snapshot = !empty($file['cover']) ? $imgPreffix . $file['cover'] : '/img/default.png';
                $title = !empty($file['title']) ? Html::mb_substr($file['title'], 0, 50, 'utf-8') : $file['filename'];

                $platform = Html::getShareVideosPlatform($file['shortcut']['url']);
                
                echo <<<eof
    <a href="{$file['shortcut']['url']}" class="img-item img-preview" target="_blank">
        <span class="img-con video-js vjs-big-play-centered">
            <img data-src="{$snapshot}" class="lazyload" alt="封面图 - {$title}">
            <span class="duration">{$platform}</span>
            <button class="vjs-big-play-button" type="button" title="Play Video" aria-disabled="false" style="display:none">
                <span class="vjs-icon-placeholder" aria-hidden="true"></span>
                <span class="vjs-control-text" aria-live="polite">Play Video</span>
            </button>
        </span>
        <strong>{$title}</strong>
    </a>
eof;
            }
        }

        //当前目录的readme详细介绍
        if (!empty($viewData['htmlCateReadme'])) {
            echo <<<eof
    <div class="cateinfo markdown-body">{$viewData['htmlCateReadme']}</div>
eof;
        }
    ?>
</div>
