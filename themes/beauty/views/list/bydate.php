<?php
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

$selectedId = $viewData['para_year'];
if (!empty($viewData['para_month'])) {
    $selectedId = $viewData['para_month'];
}

$cacheData = !empty($viewData['cacheData']) ? $viewData['cacheData'] : [];
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
                <button type="button" class="collapsed mr_button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <img class="svg icon1 svgimg verMiddle" src="/img/beauty/navshow.svg" alt="展开列表" title="展开列表">
                </button>
            </div>

            <a class="navbar-brand" href="/">
                <span class="verMiddle">小泓的个人相册</span>
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="nb_right nav navbar-nav navbar-right hidden-xs">
                <?php if (!empty($viewData['isAdminIp'])) { ?>
                <img class="svg icon1 svgimg verMiddle cleanCacheJS" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="padding-top:2px;margin-top:2px">
                <?php } ?>
                <img class="svg icon1 svgimg iconr2 lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
            </div>

            <!--侧边栏-->
            <ul class="nav navbar-fixed-left <?=$menu_expand_icon_cls?>">
                <li class="menu-title">
                    年月
                    <?php if (!empty($viewData['isAdminIp'])) { ?>
                    <img class="svg icon1 svgimg verMiddle hide" src="/img/beauty/refresh.svg" alt="清空缓存数据" title="刷新缓存数据" style="margin-left:10px;width:16px">
                    <?php } ?>
                </li>
                <?php
                if (!empty($viewData['cacheData_keys'])) {
                    $arrYears = array_keys($viewData['cacheData_keys']);
                    arsort($arrYears);
                    foreach($arrYears as $year) {
                        $intYear = str_replace('y', '', $year);
                        $selected = $year == $viewData['para_year'] ? 'active' : '';
                        $htmlFileTotal = '';
                        if (!empty($viewData['cacheData_keys'][$year]['total'])) {
                            $htmlFileTotal = <<<eof
                            <small class="badge">{$viewData['cacheData_keys'][$year]['total']}</small>
eof;
                        }
                        echo <<<eof
                <li class="{$selected}"><a href="/list/bydate?year={$year}">
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
                    <button class="btnStartScan btn btn-xs btn-primary">点我开始扫描</button>
                </li>
eof;
                }
                ?>
                <li class="menu-title mt-1">目录</li>
                <?php
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

                        echo <<<eof
        <li><a href="{$item['path']}">
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
$btnSetSnap = '';

$total = 0;
if (!empty($cacheData)) {
    foreach($cacheData as $month => $files) {
        $total += Html::getDataTotal($files, $supportedExts);     //翻页支持
    }
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

$breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
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

    <div class="im_mainl row">
<?php
        //显示图片、视频、音乐筛选链接
        $arrShowTypes = array(
            'all' => '所有',
            'image' => '照片',
            'video' => '视频',
            'audio' => '音乐',
        );

        echo '<ul class="nav nav-tabs ml-1 mb-1">';
        foreach ($arrShowTypes as $key => $title) {
            $showLink = Html::getLinkByParams(FSC::$app['requestUrl'], array(
                'show' => $key,
                'page' => 1,
                'month' => '',
            ));
            $activedClass = $key == $viewData['showType'] ? 'active' : '';
            echo <<<eof
            <li role="presentation" class="{$activedClass}"><a href="{$showLink}">{$title}</a></li>
eof;
        }
        echo '</ul>';


        //显示月份导航菜单
        if (!empty($viewData['allFiles']) && !empty($viewData['para_year']) && !empty($viewData['cacheData_keys'][$viewData['para_year']])) {
            echo '<ul class="nav nav-pills ml-1 mb-1">';

            $activedClass = empty($viewData['para_month']) ? 'active' : '';
            $monthLink = Html::getLinkByParams(FSC::$app['requestUrl'], array(
                    'show' => $viewData['showType'],
                    'page' => 1,
                    'month' => ''
                ));
            echo <<<eof
            <li role="presentation" class="{$activedClass}"><a href="{$monthLink}">所有</a></li>
eof;

            $months = $viewData['monthsByType'];
            if ($viewData['showType'] == 'all') {
                $months = $viewData['cacheData_keys'][$viewData['para_year']];
                sort($months);        //排序
            }

            foreach ($months as $month) {
                if (strpos($month, 'm') === false) {continue;}
                $intMonth = str_replace('m', '', $month);
                $activedClass = $month == $viewData['para_month'] ? 'active' : '';
                $monthLink = Html::getLinkByParams(FSC::$app['requestUrl'], array(
                    'show' => $viewData['showType'],
                    'page' => 1,
                    'month' => $month,
                ));
                echo <<<eof
            <li role="presentation" class="{$activedClass}"><a href="{$monthLink}">{$intMonth}月</a></li>
eof;
            }

            echo '</ul>';
        }


        //显示图片、视频、音乐
        $allFiles = $viewData['allFiles'];
        if(!empty($allFiles)) {        //输出所有文件
            $pageStartIndex = ($viewData['page']-1) * $viewData['pageSize'];
            $index = 0;

            foreach ($allFiles as $file) {
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

                    //权限检查
                    $originUrl = $file['path'];
                    $isAllowedToVisit = Common::isUserAllowedToFile($file['realpath']);
                    if (!$isAllowedToVisit) {
                        $smallUrl = '/img/beauty/lock-fill.svg';
                        $bigUrl = $originUrl = '/img/beauty/lazy.svg';
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="javascript:;" class="bor_radius" data-fancybox="gallery"
        data-src="{$bigUrl}"
        data-thumb="{$smallUrl}"
        data-download-src="{$originUrl}"
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
                    $videoUrl = urlencode($file['path']);
                    $linkUrl = "/site/player?id={$file['id']}&pid={$file['pid']}&url={$videoUrl}";
                    if ($viewData['showType'] == 'video') {
                        $linkUrl .= "&page={$viewData['page']}&limit={$viewData['pageSize']}";
                        //支持按年、月查看视频时，获取更多视频以便自动播放
                        $linkUrl .= "&year={$viewData['para_year']}&month={$viewData['para_month']}";
                    }

                    if ($file['extension'] == 'm3u8') {
                        $linkUrl .= "&name=" . urlencode($file['filename']);
                    }

                    //权限检查
                    $linkTarget = '_blank';
                    $videoCover = '/img/beauty/video_snap.jpg';
                    $lockedAttr = '';
                    $isAllowedToVisit = Common::isUserAllowedToFile($file['realpath']);
                    if (!$isAllowedToVisit) {
                        $linkUrl = 'javascript:;';
                        $linkTarget = '_self';
                        $videoCover = '/img/beauty/lock-fill.svg';
                        $lockedAttr = 'data-lock="true"';
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="{$linkUrl}" target="{$linkTarget}" class="bor_radius" title="{$title} - {$file['filename']}">
        <img src="{$videoCover}" class="bor_radius im_img video-poster"
            id="poster_{$file['id']}"
            data-video-id="{$file['id']}"
            data-video-url="{$file['path']}"
            {$lockedAttr}
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
                    $linkUrl = "/site/audioplayer?id={$file['id']}&pid={$file['pid']}&url={$videoUrl}";
                    if ($viewData['showType'] == 'audio') {
                        $linkUrl .= "&page={$viewData['page']}&limit={$viewData['pageSize']}";
                        //支持按年、月查看视频时，获取更多视频以便自动播放
                        $linkUrl .= "&year={$viewData['para_year']}&month={$viewData['para_month']}";
                    }

                    $snapshot = '/img/beauty/audio_icon.jpeg';
                    if (!empty($file['snapshot'])) {
                        $snapshot = $file['snapshot'];
                    }else {     //尝试找出同名的图片文件
                        $matchedImage = Html::searchImageByFilename($file['filename'], $allFiles, $imgExts);
                        if (!empty($matchedImage)) {
                            $snapshot = $matchedImage['path'];
                        }
                    }

                    //权限检查
                    $linkTarget = '_blank';
                    $lockedAttr = '';
                    $isAllowedToVisit = Common::isUserAllowedToFile($file['realpath']);
                    if (!$isAllowedToVisit) {
                        $linkUrl = 'javascript:;';
                        $linkTarget = '_self';
                        $snapshot = '/img/beauty/lock-fill.svg';
                        $lockedAttr = 'data-lock="true"';
                    }

                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2 audio-list-item">
    <a href="{$linkUrl}" target="{$linkTarget}" class="bor_radius vercenter" title="{$title} - {$file['filename']}">
        <img src="{$snapshot}" class="bor_radius im_img video-poster" id="poster_{$file['id']}"
            data-video-id="{$file['id']}"
            data-video-url="{$file['path']}"
            {$lockedAttr}
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
        }else {
            echo <<<eof
        <div class="alert alert-warning mt-1 mr-1 ml-1">
            <h2>咦？没有文件哦</h2>
            <p class="mt-1">人面不知何处去，桃花依旧笑春风...</p>
        </div>
eof;
        }
?>
    </div><!--im_mainl-->
</div><!--img_main-->

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