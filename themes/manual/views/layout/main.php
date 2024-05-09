<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';

?><!DocType html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="/favicon.ico?v1.0">
<link href="/css/main.css?v.1.1" rel="stylesheet">
<!--for theme manual-->
<link href="/css/github-markdown-light.css" rel="stylesheet">
<link href="/css/manual.css?v<?=Html::getStaticFileVersion('manual.css', 'css')?>" rel="stylesheet">
</head>
<body>

    <div class="header">
        <a href="/" class="logo">
            <img src="/content/machete_icon.png" alt="Logo of FileSite.io" height="34">
            FileSite.io
        </a>
    </div>

<?php
//### Render view file
$viewFile = __DIR__ . '/../' . FSC::$app['controller'] . '/' . $viewName . '.php';
include_once $viewFile;
?>

    <div class="footer">
        <?php if (!empty(FSC::$app['config']['theme'])) { ?>
            Theme name <strong><?php echo FSC::$app['config']['theme']; ?></strong>
            <br>
        <?php } ?>
        &copy;FSC 2022 - execute time: {page_time_cost} ms
    </div>

    <script src="/js/js.cookie.min.js"></script>
    <script src="/js/main.js?v.1.0"></script>
    <!--for theme manual-->
    <script src="/js/manual.js?v<?=Html::getStaticFileVersion('manual.js', 'js')?>"></script>
    <?php echo Html::getGACode(); ?>
</body>
</html>
