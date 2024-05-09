<?php
//常用方法
require_once __DIR__ . '/../../plugins/Html.php';

?><!DocType html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link href="/css/main.css?v.1.0" rel="stylesheet">
</head>
<body>


<?php
//### Render view file
$viewFile = __DIR__ . '/../' . FSC::$app['controller'] . '/' . $viewName . '.php';
include_once $viewFile;
?>

    <div class="footer">
        &copy;FSC 2022 - execute time: {page_time_cost} ms
    </div>

    <script src="/js/js.cookie.min.js"></script>
    <script src="/js/main.js?v.1.0"></script>
    <?php echo Html::getGACode(); ?>
</body>
</html>