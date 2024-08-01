<?php
//视频播放器

?>
<!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <div class="navbar-toggle">
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
            </div>

            <a class="navbar-brand" href="/">
                <span class="verMiddle"><?php echo $pageTitle; ?></span>
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


<div class="row">
    <div class="col col-md-8">
        <div class="videoplayer">
            <video
                data-id="<?php echo $viewData['videoId']; ?>"
                data-screenshot-start="<?php echo FSC::$app['config']['screenshot_start']; ?>"
                class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
                controls
                playsinline
                poster="" 
                id="my-player">
                <source src="<?php echo $viewData['videoUrl']; ?>" type="video/mp4">
            </video>
            <div class="text-right mt-2 mr-1">
                <button class="btn btn-default mr-1 btn-snapshot">
                    <img src="/img/beauty/video_dir.png" alt="download icon" width="20">
                    生成封面图
                </button>
                <a class="btn btn-default" href="<?php echo $viewData['videoUrl']; ?>&download=1">
                    <img src="/img/download.png" alt="download icon" width="20">
                    下载视频
                </a>
            </div>
        </div>
    </div>
    <div class="col col-md-4 morevideos">
        <hr class="visible-xs">
        <div class="btn_autoplay text_dark">
            自动播放：
            <div class="btn-group" role="group">
                <button class="btn btn-default btn-xs autoplay_disabled">关闭</button>
                <button class="btn btn-primary btn-xs autoplay_enabled">开启</button>
            </div>
        </div>

        <div
            data-id="<?php echo $viewData['videoId']; ?>"
            data-pid="<?php echo $viewData['cateId']; ?>"
            data-cid="<?php echo $viewData['cacheParentDataId']; ?>"
            data-page="<?php echo $viewData['page']; ?>"
            class="othervideos">其它视频</div>
    </div>
</div>

<script type="text/template" id="template_video_item">
    <div class="im_item col-xs-6">
        <a href="{videoUrl}" class="bor_radius" title="{title}">
            <img src="/img/beauty/video_snap.jpg" class="bor_radius im_img video-poster" id="poster_{videoId}"
                data-video-id="{videoId}"
                data-video-url="{videoPath}"
                alt="">
            <div class="im_img_title">
                <span class="right-bottom">
                    {title}
                </span>
            </div>
            <img src="/img/video-play.svg" class="playbtn hide" alt="video play button">
            <span class="duration">00:00:00</span>
        </a>
    </div>
</script>
