<?php
//MP3播放器

?>
<!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <div class="navbar-toggle">
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
            </div>

            <a class="navbar-brand" href="/" tabindex="-1">
                <span class="verMiddle">正在播放</span>
            </a>
            <span class="navbar-text videotitle"><?php echo $viewData['videoFilename']; ?></span>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="nb_right nav navbar-nav navbar-right hidden-xs">
                <img class="svg icon1 svgimg iconr2 lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<?php if (!empty($viewData['cacheParentDataId']) || !empty($viewData['page'])) { ?>
<div class="morevideos audiolist">
    <div class="btn_autoplay text_dark ml-1">
        自动播放：
        <div class="btn-group" role="group">
            <button class="btn btn-default btn-xs autoplay_disabled">关闭</button>
            <button class="btn btn-primary btn-xs autoplay_enabled">开启</button>
        </div>
    </div>

    <div
        data-type="audio"
        data-id="<?php echo $viewData['videoId']; ?>"
        data-pid="<?php echo $viewData['cateId']; ?>"
        data-cid="<?php echo $viewData['cacheParentDataId']; ?>"
        data-page="<?php echo $viewData['page']; ?>"
        data-page-size="<?php echo $viewData['pageSize']; ?>"
        data-api="<?php echo !empty($viewData['cacheParentDataId']) ? '/list/' : '/list/bydate/'; ?>"
        data-year="<?php echo $viewData['para_year']; ?>"
        data-month="<?php echo $viewData['para_month']; ?>"
        class="row othervideos"><div class="ml-2">...</div></div>
</div>
<?php } ?>
<div class="audioplayer" style="background:url('/img/beauty/audio_bg.jpg') top center">
    <div class="row">
        <div class="col col-md-offset-3 col-md-6 col-xs-12">
            <div class="videoplayer">
                <video
                    data-id="<?php echo $viewData['videoId']; ?>"
                    data-screenshot-start="<?php echo FSC::$app['config']['screenshot_start']; ?>"
                    class="video-js vjs-fill vjs-big-play-centered"
                    controls
                    playsinline
                    data-setup='{"inactivityTimeout":0}'
                    poster="<?php echo $viewData['poster']; ?>"
                    data-src="<?php echo $viewData['videoUrl']; ?>"
                    data-type="<?php echo $viewData['videoSourceType']; ?>"
                    id="my-player">
                </video>
            </div>
        </div>
        <div class="col col-md-1 col-xs-4 vercenter hidden-xs">
            <a class="btn btn-default downloadbtn" href="<?php echo $viewData['videoUrl']; ?>&download=1">
                <img src="/img/download.png" alt="download icon" width="20">
                <span class="">下载</span>
            </a>
        </div>
        <a class="btn btn-default downloadbtn visible-xs-block" href="<?php echo $viewData['videoUrl']; ?>&download=1">
            <img src="/img/download.png" alt="download icon" width="20">
        </a>
    </div>
</div>

<script type="text/template" id="template_video_item">
    <div class="col-xs-12">
        <div class="mb-1 audio-item clearfix">
            <a href="{videoUrl}" title="{title}">
                <img src="{snapshot}" class="bor_radius video-poster" id="poster_{videoId}"
                    width="60" height="60"
                    data-video-id="{videoId}"
                    data-video-url="{videoPath}"
                    alt="">
                <span class="duration">00:00:00</span>
                <span class="title">{title}</span>
            </a>
        </div>
    </div>
</script>
