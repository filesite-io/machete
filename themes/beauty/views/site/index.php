<?php
$selectedId = !empty($viewData['cateId']) ? $viewData['cateId'] : '';
$total = 0;     //翻页支持

$imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
$videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
$supportedExts = array_merge($imgExts, $videoExts);

$dir_ext_status = !empty($_COOKIE['dir_ext_status']) ? $_COOKIE['dir_ext_status'] : 'opened';
?><!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <div class="navbar-toggle">
                <img class="svg icon1 svgimg verMiddle cleanCacheJS" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="padding-top:2px;margin-top:2px">
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
                <img class="icon1 svg connectmeJS svgimg verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
                <button type="button" class="collapsed mr_button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <img class="svg icon1 svgimg verMiddle" src="/img/beauty/navshow.svg" alt="展开列表" title="展开列表">
                </button>
            </div>

            <a class="navbar-brand" href="/">
                <!--img class="verMiddle" src="/content/machete_icon.png" alt="logo图片"-->
                <span class="verMiddle"><?php echo $pageTitle; ?></span>
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="nb_right nav navbar-nav navbar-right hidden-xs">
                <img class="svg icon1 svgimg verMiddle cleanCacheJS" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="padding-top:2px;margin-top:2px">
                <img class="svg icon1 svgimg iconr2 lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
                <img class="icon1 svg connectmeJS svgimg iconr2 verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
            </div>
            
            <?php /*
            <form class="navbar-form navbar-right">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="搜索图片名称">
                </div>
                <button type="submit" class="btn btn-default">搜索</button>
            </form>
            */ ?>

            <ul class="nav navbar-fixed-left">
                <?php
                $breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
                if (!empty($viewData['menus'])) {        //只显示第一级目录
                    foreach ($viewData['menus'] as $index => $item) {
                        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'active' : '';
                        echo <<<eof
        <li class="{$selected}"><a href="{$item['path']}">{$item['directory']}</a></li>
eof;
                    }
                }
                ?>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<!-- 内容主题 -->
<div class="img_main">

<?php
$category = !empty($viewData['scanResults'][$selectedId]) ? $viewData['scanResults'][$selectedId] : [];
$btnSetSnap = '<button class="btn btn-xs btn-info btn-set-snap">选作封面</button>';

//如果是首页
if (empty($selectedId) && !empty($viewData['menus'])) {
    $category = array(
        'directories' => $viewData['menus'],
        'files' => $viewData['scanResults'],
    );
    $btnSetSnap = '';
}

if (!empty($category['files'])) {
    $total = Html::getDataTotal($category['files'], $supportedExts);     //翻页支持
}
        

if (!empty($breadcrumbs)) {
    $totalNum = '';
    if ($total > 0) {
        $totalNum = <<<eof
    <span class="pull-right total">总数 <strong>{$total}</strong></span>
eof;
    }

    echo <<<eof
    <div class="breadcrumbs text_dark">
        {$totalNum}
        <small>当前位置：</small>
        <a href="/">首页</a> / 
eof;

    foreach ($breadcrumbs as $bread) {
        if ($bread['id'] != $selectedId) {
            echo <<<eof
        <a href="{$bread['url']}">{$bread['name']}</a> / 
eof;
        } else {
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

    <?php
        //如果没有选中任何目录，则把所有目录显示出来
        if (empty($selectedId) && !empty($viewData['menus'])) {
            $category = array(
                'directories' => $viewData['menus'],
                'files' => $viewData['scanResults'],
            );
        }else if (empty($category['directories']) && $total == 0) {
            echo <<<eof
    <div class="alert alert-warning">
        <h2>咦？没有图片或视频</h2>
        <p class="mt-2">
            空目录吗？复制照片目录或文件到目录后点右上角“<img width="18" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据">刷新”图标清空缓存。
            <br>
            如果不是空目录，点右上角“<img width="18" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据">刷新”图标清空缓存，网页有 10 分钟缓存。
        </p>
    </div>
eof;
        }

        //当前目录的描述介绍
        if (!empty($category['description'])) {
            echo <<<eof
    <p class="modal-body text_dark">{$category['description']}</p>
eof;
        }

        //当前目录的readme详细介绍
        if (!empty($viewData['htmlCateReadme'])) {
            echo <<<eof
    <div class="modal-body text_dark markdown-body">{$viewData['htmlCateReadme']}</div>
eof;
        }

    $dirHideClass = $dir_ext_status == 'closed' ? 'hide' : '';
    ?>
    <div class="im_mainl row <?php echo $dirHideClass; ?>">
        <?php
        if (!empty($category['directories'])) {        //两级目录支持
            $index = 0;
            foreach ($category['directories'] as $dir) {
                $dirUrl = $dir['path'];
                if (strpos($dirUrl, 'cid=') === false) {
                    $dirUrl .= "&cid={$viewData['cacheDataId']}";
                }
                echo <<<eof
            <div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
                <a href="{$dirUrl}" class="bor_radius dir_item" data-id="{$dir['id']}" data-cid="{$viewData['cacheDataId']}">
eof;

                if (!empty($dir['snapshot'])) {
                    if ($index > 0) {
                        echo <<<eof
    <img src="/img/beauty/lazy.svg" data-original="{$dir['snapshot']}" class="bor_radius im_img lazy" alt="{$dir['directory']}">
eof;
                    } else {
                        echo <<<eof
    <img src="{$dir['snapshot']}" class="bor_radius im_img" alt="{$dir['directory']}">
eof;
                    }
                } else if (!empty($dir['files'])) {
                    $first_img = array_shift($dir['files']);
                    if (!in_array($first_img['extension'], $imgExts)) {
                        foreach ($dir['files'] as $file) {
                            if (in_array($file['extension'], $imgExts)) {
                                $first_img = $file;
                                break;
                            }
                        }
                    }

                    if (in_array($first_img['extension'], $imgExts)) {
                        $imgUrl = urlencode($first_img['path']);
                        $smallUrl = "/site/smallimg/?id={$first_img['id']}&url={$imgUrl}";
                        echo <<<eof
    <img src="/img/beauty/lazy.svg"
        data-id="{$first_img['id']}"
        data-original="{$smallUrl}"
        data-original_="{$first_img['path']}"
        class="bor_radius im_img lazy" alt="{$first_img['filename']}">
eof;
                    } else {
                        echo <<<eof
    <img src="/img/default.png" class="bor_radius im_img" alt="default image">
eof;
                    }
                }

                $title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
                echo <<<eof
                <div class="im_img_title">
                    <span>
                        <img src="/img/beauty/folder.svg" alt="folder" width="20">
                        {$title}
                    </span>
                </div>
            </a>
        </div>
eof;
                $index++;
            }


            //分割目录和文件
            echo '</div>';

            if (!empty($category['files']) && count($category['files']) > 3) {
                $arrowImg = $dir_ext_status == 'opened' ? 'arrow-up.svg' : 'arrow-down.svg';
                $btnTxt = $dir_ext_status == 'opened' ? '收拢' : '展开';
                echo <<<eof
<div class="gap-hr">
    <hr>
    <button class="btn btn-default btn-xs btn-dir-ext" data-status="{$dir_ext_status}"><img src="/img/{$arrowImg}" alt="directory toggle"> <span>{$btnTxt}</span></button>
</div>
eof;
            }

            echo '<div class="im_mainl row">';
        }

        //显示图片、视频
        if (!empty($category['files'])) {        //一级目录支持
            $pageStartIndex = ($viewData['page']-1) * $viewData['pageSize'];
            $index = 0;

            foreach ($category['files'] as $file) {
                if (empty($file['extension']) || !in_array($file['extension'], $supportedExts)) {
                    continue;
                }

                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $viewData['pageSize']) {
                    break;
                }

                $title = !empty($file['title']) ? $file['title'] : $file['filename'];
                //图片、视频显示文件修改日期
                $title = Common::getDateFromString($file['filename']);
                if (empty($title) && !empty($file['fstat']['mtime']) && !empty($file['fstat']['ctime'])) {
                    $title = date('Y-m-d', min($file['fstat']['mtime'], $file['fstat']['ctime']));
                }

                if (in_array($file['extension'], $imgExts)) {
                    $imgUrl = urlencode($file['path']);
                    $smallUrl = "/site/smallimg/?id={$file['id']}&url={$imgUrl}";
                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="javascript:;" class="bor_radius" data-fancybox="gallery"
        data-src="{$file['path']}"
        data-caption="{$title} - {$file['filename']}"
        data-pid="{$file['pid']}"
        title="{$title} - {$file['filename']}">
        <img src="/img/beauty/lazy.svg"
            data-id="{$file['id']}"
            data-original="{$smallUrl}"
            class="bor_radius im_img lazy" alt="{$file['filename']}">
        <div class="im_img_title">
            <span class="right-bottom">
                {$title}
            </span>
        </div>
        {$btnSetSnap}
    </a>
</div>
eof;
                }else if (in_array($file['extension'], $videoExts)) {       //输出视频
                    $videoUrl = urlencode($file['path']);
                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="/site/player?url={$videoUrl}&id={$file['id']}" target="_blank" class="bor_radius" title="{$title} - {$file['filename']}">
        <img src="/img/beauty/video_snap.jpg" class="bor_radius im_img video-poster" id="poster_{$file['id']}"
            data-video-id="{$file['id']}"
            data-video-url="{$file['path']}"
            alt="{$file['filename']}">
        <div class="im_img_title">
            <span class="right-bottom">
                {$title}
            </span>
        </div>
        <img src="/img/video-play.svg" class="playbtn hide" alt="video play button">
        <span class="duration">00:00:00</span>
    </a>
</div>
eof;
                }


                $index++;
            }
        }
        ?>

    </div>
</div>

<div class="text-center">
<?php
if ($total > $viewData['pageSize']) {
    $pagination = Html::getPaginationHtmlCode($viewData['page'], $viewData['pageSize'], $total);
    echo $pagination;
}
?>
</div>

<div class="video_previewer">
    <video
        class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
        playsinline
        poster="" 
        id="pr-player">
    </video>
</div>