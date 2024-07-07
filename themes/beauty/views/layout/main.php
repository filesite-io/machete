<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';

?><!DocType html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $pageTitle; ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v1.0">
    <link href="/css/main.css?v.1.1" rel="stylesheet">
    <!--for theme beauty-->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/fubox.min.css" rel="stylesheet">
    <link href="/css/beauty.css?v<?= Html::getStaticFileVersion('beauty.css', 'css') ?>" rel="stylesheet">
    <link href="/css/github-markdown-light.css" rel="stylesheet" id="markdowncss">
</head>

<body>

    <div id="image_site" class="main_style">

        <?php
        //### Render view file
        if (!empty($viewFile) && file_exists($viewFile)) {
            include_once $viewFile;
        }
        ?>

        <!-- 尾部网站信息 -->
        <footer class="web_info vercenter">
            <div class="qrcode text-center">
                <p>用手机扫码打开</p>
                <div id="qrimg"></div>
            </div>
            <p>
                Copyright <span>&copy;2022</span>
                by <a href="https://filesite.io/" target="_blank">FileSite.io</a>
                <br>
                <?php if (!empty(FSC::$app['config']['theme'])) { ?>
                    当前皮肤 <strong><?php echo FSC::$app['config']['theme']; ?></strong>
                    - 执行耗时: {page_time_cost} ms
                <?php } ?>
            </p>
            <?php if (!empty(FSC::$app['config']['googleimage']['contact'])) {
                $contactInfo = FSC::$app['config']['googleimage']['contact'];
                echo <<<eof
            <p>{$contactInfo}</p>
eof;
            } ?>
        </footer>

        <!-- 右侧弹出框 -->
        <div class="blank_cover elementNone blank_coverJS rtcloseJS"></div>
        <div class="right_sidebox right_sideboxJS elementNone">
            <h5>
                <span>联系我们</span>
                <a class="rtcloseJS" href="javascript:;">
                    <img class="icon svgimg verMiddle" src="/img/beauty/clos.svg" alt="关闭" title="关闭">
                </a>
            </h5>
            <div class="modal-body markdown-body">
                <?php echo !empty($viewData['htmlReadme']) ? $viewData['htmlReadme'] : ''; ?>
            </div>
        </div>

        <!-- 右下角回到顶部悬浮块 -->
        <div class="btrt_side">
            <ul class="btrt_side_ul">
                <li class="scroll_top scroll_topJS">
                    <img class="icon svg" src="/img/beauty/huojian.svg" alt="回到顶部" title="点击回到顶部" />
                </li>
                <?php if (!empty($viewData['mp3File'])) { ?>
                <li class="music_switch musicJS">
                    <img class="icon svg verMiddle" src="/img/beauty/music.svg" alt="音乐" title="开启关闭音乐" />
                </li>
                <?php } ?>
                <li class="connectmeJS">
                    <img class="icon3 svg verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
                </li>
            </ul>
        </div>

        <?php if (!empty($viewData['mp3File'])) { ?>
        <!-- 音乐 -->
        <audio class="music_main musicMJS" id="music_main" src="<?=$viewData['mp3File']['path']?>" preload autoplay loop>
            你的浏览器不支持<code>audio</code>标签
        </audio>
        <?php } ?>
    </div>

    <!--for theme googleimage-->
    <script src="/js/jquery-3.1.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/lazyload.js"></script>
    <script src="/js/fubox.min.js"></script>
    <script src="/js/qrcode.min.js"></script>
    <script src="/js/beauty.js?v<?= Html::getStaticFileVersion('beauty.js', 'js') ?>"></script>
    <?php echo Html::getGACode(); ?>
</body>
</html>