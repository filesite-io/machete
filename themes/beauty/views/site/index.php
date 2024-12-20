<?php
$selectedId = !empty($viewData['cateId']) ? $viewData['cateId'] : '';
$total = 0;     //翻页支持

$imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
$videoExts = !empty(FSC::$app['config']['supportedVideoExts']) ? FSC::$app['config']['supportedVideoExts'] : array('mp4', 'mov', 'm3u8');
$audioExts = !empty(FSC::$app['config']['supportedAudioExts']) ? FSC::$app['config']['supportedAudioExts'] : array('mp3');
$supportedExts = array_merge($imgExts, $videoExts, $audioExts);
if ($viewData['showType'] == 'image') {
    $supportedExts = $imgExts;
}else if ($viewData['showType'] == 'video') {
    $supportedExts = $videoExts;
}else if ($viewData['showType'] == 'audio') {
    $supportedExts = $audioExts;
}

//需密码授权的目录显示lock图标
$authConfig = !empty(FSC::$app['config']['password_auth']) ? FSC::$app['config']['password_auth'] : array();

$dir_ext_status = !empty($_COOKIE['dir_ext_status']) ? $_COOKIE['dir_ext_status'] : 'opened';
$menu_ext_status = !empty($_COOKIE['menu_ext_status']) ? $_COOKIE['menu_ext_status'] : FSC::$app['config']['defaultMenuStatusInPC'];

$menu_expand_icon_cls = $menu_ext_status == 'opened' ? '' : 'closed';
$menu_expand_icon_url = $menu_ext_status == 'opened' ? 'arrow-left-circle.svg' : 'arrow-right-circle.svg';
$main_view_cls = $menu_ext_status == 'opened' ? '' : 'full';
?><!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <div class="navbar-toggle">
                <?php if (!empty($viewData['isAdminIp'])) { ?>
                <img class="svg icon1 svgimg verMiddle cleanCacheJS" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="padding-top:2px;margin-top:2px">
                <?php } ?>
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
                <img class="icon1 svg connectmeJS svgimg verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
                <button type="button" class="collapsed mr_button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <img class="svg icon1 svgimg verMiddle" src="/img/beauty/navshow.svg" alt="展开列表" title="展开列表">
                </button>
            </div>

            <a class="navbar-brand" href="/">
                <span class="verMiddle"><?php echo $pageTitle; ?></span>
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="nb_right nav navbar-nav navbar-right hidden-xs">
                <?php if (!empty($viewData['isAdminIp'])) { ?>
                <img class="svg icon1 svgimg verMiddle cleanCacheJS" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="padding-top:2px;margin-top:2px">
                <?php } ?>
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

            <!--侧边栏-->
            <ul class="nav navbar-fixed-left <?=$menu_expand_icon_cls?>">
            <?php if (!empty(FSC::$app['config']['showYearMonthMenus'])) { ?>
                <li class="menu-title">
                    年月
                    <?php if (!empty($viewData['isAdminIp'])) { ?>
                    <img class="svg icon1 svgimg verMiddle hide" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="margin-left:10px;width:16px">
                    <?php } ?>
                </li>
                <?php
                if (!empty($viewData['cacheDataByDate'])) {
                    $arrYears = array_keys($viewData['cacheDataByDate']);
                    arsort($arrYears);
                    foreach($arrYears as $year) {
                        $intYear = str_replace('y', '', $year);
                        $htmlFileTotal = '';
                        if (!empty($viewData['cacheDataByDate'][$year]['total'])) {
                            $htmlFileTotal = <<<eof
                            <small class="badge">{$viewData['cacheDataByDate'][$year]['total']}</small>
eof;
                        }
                        echo <<<eof
                <li><a href="/list/bydate?year={$year}">
                    <img src="/img/beauty/calendar.svg?gray" alt="calendar" width="14" class="menu-icon">
                    {$intYear}年
                    {$htmlFileTotal}
                </a></li>
eof;
                    }
                }else {
                    echo <<<eof
                <li class="text-center">
                    还没有索引数据！
                    <br>
                    <button class="btnStartScan btn btn-xs btn-primary">点我开始扫描</button>
                    <br>
                    请在完成后刷新网页查看
                </li>
eof;
                }
            }
                ?>
                <li class="menu-title mt-1">目录</li>
                <?php
                $breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
                if (!empty($viewData['menus'])) {        //只显示第一级目录
                    foreach ($viewData['menus'] as $index => $item) {
                        $htmlFileTotal = '';
                        if ( !empty($viewData['dirCounters']) && !empty($viewData['dirCounters'][$item['id']]) ) {
                            $dirTotal = $viewData['dirCounters'][$item['id']];
                            $fileTotal = $dirTotal['image_total'] + $dirTotal['video_total'] + $dirTotal['audio_total'];
                            $htmlFileTotal = <<<eof
                            <small class="badge">{$fileTotal}</small>
eof;
                        }

                        //目录图标支持加密目录
                        $dirIcon = "folder.svg";
                        if (!empty($authConfig['enable']) && $authConfig['enable'] !== 'false'
                            && (
                                ( empty($authConfig['default']) && !empty($authConfig['allow'][$item['directory']]) )
                                ||
                                !empty($authConfig['default'])       //如果所有目录都需要密码
                            )
                        ) {
                            $dirIcon = "lock-fill.svg";
                        }

                        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'active' : '';
                        echo <<<eof
        <li class="{$selected}"><a href="{$item['path']}">
            <img src="/img/beauty/{$dirIcon}" alt="directories" width="17" class="menu-icon">
            {$item['directory']}
            {$htmlFileTotal}
        </a></li>
eof;
                    }
                }
                ?>
                <li class="expand-icon hidden-xs" data-status="<?=$menu_ext_status?>"><img src="/img/beauty/<?=$menu_expand_icon_url?>" width="18" alt="arrow"></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<!-- 内容主题 -->
<div class="img_main <?=$main_view_cls?>">

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

if (empty($viewData['isAdminIp'])) {
    $btnSetSnap = '';
}

if (!empty($category['files'])) {
    $total = Html::getDataTotal($category['files'], $supportedExts);     //翻页支持
}
        

$totalNum = '';
if ($total > 0) {
    $totalNum = <<<eof
    <span class="pull-right total">总数 <strong>{$total}</strong></span>
eof;
}


if (!empty($viewData['alertWarning'])) {
    echo <<<eof
    <div class="alert alert-warning">{$viewData['alertWarning']}</div>
eof;
}

echo <<<eof
    <div class="breadcrumbs text_dark">
        {$totalNum}
        <small>当前位置：</small>
        <a href="/">首页</a>
eof;

if (!empty($breadcrumbs)) {
    foreach ($breadcrumbs as $bread) {
        if ($bread['id'] != $selectedId) {
            echo <<<eof
        / <a href="{$bread['url']}">{$bread['name']}</a>
eof;
        } else {
            echo <<<eof
        / <strong>{$bread['name']}</strong>
eof;
        }
    }
}

echo <<<eof
    </div>
eof;
?>

    <?php
        //如果没有选中任何目录，则把所有目录显示出来
        if (empty($selectedId) && !empty($viewData['menus'])) {
            $category = array(
                'directories' => $viewData['menus'],
                'files' => $viewData['scanResults'],
            );
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
                        if (empty(FSC::$app['config']['enableSmallImage']) || FSC::$app['config']['enableSmallImage'] === 'false') {
                            $smallUrl = $first_img['path'];
                        }
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

                //判断是否需要加密访问的目录
                $lockIcon = '';
                if (!empty($authConfig['enable']) && $authConfig['enable'] !== 'false'
                    && (
                        ( empty($authConfig['default']) && !empty($authConfig['allow'][$dir['directory']]) )
                        ||
                        !empty($authConfig['default'])       //如果所有目录都需要密码
                    )
                ) {
                    $lockIcon = <<<eof
<div class="locked_dir"><img src="/img/beauty/lock2-fill.svg" alt="加密目录" width="30"></div>
eof;
                }

                $title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
                $htmlFileTotal = '';
                if ( !empty($viewData['dirCounters']) && !empty($viewData['dirCounters'][$dir['id']]) ) {
                    $dirTotal = $viewData['dirCounters'][$dir['id']];
                    $fileTotal = $dirTotal['image_total'] + $dirTotal['video_total'] + $dirTotal['audio_total'];
                    $htmlFileTotal = <<<eof
                    <small class="badge">{$fileTotal}</small>
eof;
                }
                echo <<<eof
                <div class="im_img_title">
                    <span class="folder_title">
                        <img src="/img/beauty/folder.svg" alt="folder" width="20">
                        {$title}
                        {$htmlFileTotal}
                    </span>
                </div>
                {$lockIcon}
            </a>
        </div>
eof;
                $index++;
            }

        }


        //分割目录和文件
        echo '</div>';

        if (!empty($category['directories'])) {        //两级目录支持
            $arrowImg = $dir_ext_status == 'opened' ? 'arrow-up.svg' : 'arrow-down.svg';
            $btnTxt = $dir_ext_status == 'opened' ? '收拢目录' : '展开目录';
            echo <<<eof
<div class="gap-hr">
    <hr>
    <button class="btn btn-default btn-xs btn-dir-ext" data-status="{$dir_ext_status}" data-opened-title="收拢目录" data-closed-title="展开目录"><img src="/img/{$arrowImg}" alt="directory toggle"> <span>{$btnTxt}</span></button>
</div>
eof;
        }


        //显示图片、视频、音乐筛选链接
        $arrShowTypes = array(
            'all' => '所有',
            'image' => '照片',
            'video' => '视频',
            'audio' => '音乐',
        );

        echo '<ul class="nav nav-tabs ml-1 mb-1">';
        foreach ($arrShowTypes as $key => $title) {
            $showLink = Html::getLinkByParams(FSC::$app['requestUrl'], array('show' => $key, 'page' => 1));
            $activedClass = $key == $viewData['showType'] ? 'active' : '';
            echo <<<eof
            <li role="presentation" class="{$activedClass}"><a href="{$showLink}">{$title}</a></li>
eof;
        }
        echo '</ul>';


        //空目录显示提示信息
        if (
            ( empty($selectedId) && empty($category['directories']) ) || 
            ( !empty($selectedId) && empty($category['files']) )
        ) {
            echo <<<eof
    <div class="alert alert-warning mt-1 mr-1 ml-1">
        <h2>咦？没有文件哦</h2>
        <p class="mt-2">
            空目录吗？复制照片、视频等文件到目录后点右上角“<img width="18" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据">刷新”图标清空缓存。
            <br>
            如果不是空目录，点右上角“<img width="18" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据">刷新”图标清空缓存，网页有 10 分钟缓存。
        </p>
    </div>
eof;
        }


        echo '<div class="im_mainl row">';


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

                //图片、视频显示文件修改日期
                if (!empty($file['original_ctime'])) {      //优先使用照片的拍摄日期
                    $title = '摄于' . date('Y-m-d H:i', $file['original_ctime']);
                }else {
                    $title = Common::getDateFromString($file['filename']);      //根据文件名获取拍摄日期
                    if (empty($title) && !empty($file['fstat']['mtime']) && !empty($file['fstat']['ctime'])) {
                        $title = date('Y-m-d', Common::getFileCreateTime($file));
                    }
                }

                if (in_array($file['extension'], $imgExts)) {
                    //缩略图
                    $imgUrl = urlencode($file['path']);
                    $smallUrl = "/site/smallimg/?id={$file['id']}&url={$imgUrl}";
                    if (empty(FSC::$app['config']['enableSmallImage']) || FSC::$app['config']['enableSmallImage'] === 'false') {
                        $smallUrl = $file['path'];
                    }

                    //大图（支持中尺寸的缩略图）
                    $bigUrl = "/site/smallimg/?id={$file['id']}&url={$imgUrl}&size=middle";
                    if (empty(FSC::$app['config']['enableSmallImageForWan']) || FSC::$app['config']['enableSmallImageForWan'] === 'false') {
                        $bigUrl = $file['path'];
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="javascript:;" class="bor_radius" data-fancybox="gallery"
        data-src="{$bigUrl}"
        data-thumb="{$smallUrl}"
        data-download-src="{$file['path']}"
        data-download-filename="{$file['filename']}.{$file['extension']}"
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
                    //m3u8支持
                    if ($file['extension'] == 'm3u8') {
                        $videoUrl = urlencode("{$file['path']}&cid={$viewData['cacheDataId']}");
                    }else {
                        $videoUrl = urlencode($file['path']);
                    }

                    $linkUrl = "/site/player?id={$file['id']}&pid={$file['pid']}&cid={$viewData['cacheDataId']}&url={$videoUrl}";
                    if ($viewData['showType'] == 'video') {
                        $linkUrl .= "&page={$viewData['page']}&limit={$viewData['pageSize']}";
                    }

                    if ($file['extension'] == 'm3u8') {
                        $linkUrl .= "&name=" . urlencode($file['filename']);
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="{$linkUrl}" target="_blank" class="bor_radius" title="{$title} - {$file['filename']}">
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
                }else if (in_array($file['extension'], $audioExts)) {       //输出音乐
                    $title = !empty($file['title']) ? $file['title'] : $file['filename'];
                    $videoUrl = urlencode($file['path']);
                    $linkUrl = "/site/audioplayer?id={$file['id']}&pid={$file['pid']}&cid={$viewData['cacheDataId']}&url={$videoUrl}";
                    if ($viewData['showType'] == 'audio') {
                        $linkUrl .= "&page={$viewData['page']}&limit={$viewData['pageSize']}";
                    }

                    $snapshot = '/img/beauty/audio_icon.jpeg';
                    if (!empty($file['snapshot'])) {
                        $snapshot = $file['snapshot'];
                    }else {     //尝试找出同名的图片文件
                        $matchedImage = Html::searchImageByFilename($file['filename'], $viewData['allFiles'], $imgExts);
                        if (!empty($matchedImage)) {
                            $snapshot = $matchedImage['path'];
                        }
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2 audio-list-item">
    <a href="{$linkUrl}" target="_blank" class="bor_radius vercenter" title="{$title} - {$file['filename']}">
        <img src="{$snapshot}" class="bor_radius im_img video-poster" id="poster_{$file['id']}"
            data-video-id="{$file['id']}"
            data-video-url="{$file['path']}"
            alt="{$file['filename']}">
        <span class="title">{$title}</span>
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

<script type="text/template" id="btn_show1to1_tmp">
    <button title="Toggle zoom 1 to 1" class="f-button"><svg tabindex="-1" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3.51 3.07c5.74.02 11.48-.02 17.22.02 1.37.1 2.34 1.64 2.18 3.13 0 4.08.02 8.16 0 12.23-.1 1.54-1.47 2.64-2.79 2.46-5.61-.01-11.24.02-16.86-.01-1.36-.12-2.33-1.65-2.17-3.14 0-4.07-.02-8.16 0-12.23.1-1.36 1.22-2.48 2.42-2.46Z"></path><path d="M5.65 8.54h1.49v6.92m8.94-6.92h1.49v6.92M11.5 9.4v.02m0 5.18v0"></path></svg></button>
</script>