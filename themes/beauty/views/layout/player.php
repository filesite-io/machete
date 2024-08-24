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
    <link href="/css/video-js.min.css" rel="stylesheet">
    <link href="/css/beauty.css?v<?= Html::getStaticFileVersion('beauty.css', 'css') ?>" rel="stylesheet">
</head>

<body>

    <div class="main_style">
        <?php
        //### Render view file
        if (!empty($viewFile) && file_exists($viewFile)) {
            include_once $viewFile;
        }
        ?>

        <!-- 尾部网站信息 -->
        <footer class="web_info vercenter">
            <?php if (!empty(FSC::$app['config']['showQRImageInFooter']) && FSC::$app['config']['showQRImageInFooter'] !== 'false') { ?>
            <div class="qrcode text-center">
                <p>用手机扫码打开</p>
                <div id="qrimg"></div>
            </div>
            <?php } ?>
            <p class="copyright">
                <?php
                if (!empty($viewData['copyright'])) {
                    echo $viewData['copyright'];
                }else {
                ?>
                <span>&copy;2022 - <?=date('Y')?></span>
                by <a href="https://filesite.io/" target="_blank">FileSite.io</a>
                <br>
                <?php if (!empty(FSC::$app['config']['theme'])) { ?>
                    当前皮肤 <strong><?php echo FSC::$app['config']['theme']; ?></strong>
                    - 执行耗时: {page_time_cost} ms
                <?php } ?>
                <br>友情链接：<a href="https://tajian.tv" target="_blank">Ta荐 - 你的聚宝盆</a>
            <?php } ?>
            </p>
        </footer>
    </div>

    <!--for theme googleimage-->
    <script src="/js/jquery-3.1.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/qrcode.min.js"></script>
    <script src="/js/video.min.js"></script>
    <script src="/js/js.cookie.min.js"></script>
    <script src="/js/beauty.js?v<?= Html::getStaticFileVersion('beauty.js', 'js') ?>"></script>
    <?php echo Html::getGACode(); ?>
</body>
</html>