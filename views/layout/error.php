<?php
//常用方法
require_once __DIR__ . '/../../plugins/Html.php';

?><!DocType html>
<head>
<meta charset="utf-8">
<title><?php echo $errors['title']; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<style>
body{max-width:768px;margin:0 auto;padding:10px}
h1{color:#FF0000}
.error{padding:4px 10px;color:yellow;background-color:gray;min-height:40px}
</style>
</head>
<body>
    <h1><?php echo $errors['title']; ?></h1>
    <h3>Exception <?php echo $errors['code']; ?>:</h3>
    <div class="error"><?php echo $errors['content']; ?></div>
    <?php echo Html::getGACode(); ?>
</body>
</html>