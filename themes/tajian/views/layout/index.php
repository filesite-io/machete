<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';

$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}
?><!DocType html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="/favicon.ico?v1.0">
<link href="/css/tajian.css?v<?=Html::getStaticFileVersion('tajian.css', 'css')?>" rel="stylesheet">
</head>
<body class="layout_index">
    <header class="top_nav">
        <a class="log_tn clearfix" href="/" title="<?php echo $pageTitle; ?>">
            <span class="verBaseline">Ta荐</span>
            - TaJian.tv
        </a>
        <div class="right_class_tn">
            <a class="connect_me_tn" href="https://github.com/filesite-io/machete" target="_blank" title="从GitHub下载Machete源码"><img src="/img/download.png" alt="下载Machete源码" class="verBottom">下载源码</a>
        </div>
    </header>

<?php
//### Render view file
if (!empty($viewFile) && file_exists($viewFile)) {
    include_once $viewFile;
}
?>

    <div class="footer">
        <div class="copyright">
            从GitHub下 <a href="https://github.com/filesite-io/machete" target="_blank">Machete源码</a> 部署到本地
            <br>
            数据采集由
            <a href="https://herounion.filesite.io" target="_blank">HeroUnion英雄联盟</a>
            提供技术支持
            <br>
            <a href="https://filesite.io" target="_blank">&copy;FileSite.io</a> 2022 - execute time: {page_time_cost} ms
        </div>
    </div>

    <div class="footimg vercenter">
        <a class="log_tn clearfix" href="/" title="<?php echo $pageTitle; ?>">
            <span class="verBaseline">Ta荐</span>
            - TaJian.tv
        </a>
        <p>你看到的，也许只是冰山一角！</p>
        <img src="/img/bg/ice_3.jpeg" alt="水面上的冰山一角">
    </div>

    <?php
    $user_id = '';
    if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
        $user_id = FSC::$app['user_id'];
    }
    ?>
    <script>var current_user_id = '<?=$user_id?>';</script>
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/lazyload.js"></script>
    <script src="/js/tajian.js?v<?=Html::getStaticFileVersion('tajian.js', 'js')?>"></script>
    <?php echo Html::getGACode(); ?>
</body>
</html>
