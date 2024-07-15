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

<div class="videoplayer">
    <video class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
        controls
        playsinline
        data-setup='{"autoplay":"muted"}'
        poster="" 
        id="myvideo">
        <source src="<?php echo $viewData['videoUrl']; ?>" type="video/mp4">
    </video>
    <div class="text-right mt-2 mr-1">
        <a class="btn btn-default" href="<?php echo $viewData['videoUrl']; ?>">
            ⬇️
            下载视频
        </a>
    </div>
</div>