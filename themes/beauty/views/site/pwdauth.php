<?php
//密码授权

?><!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <a class="navbar-brand" href="/">
                <span class="verMiddle"><?php echo $pageTitle; ?></span>
            </a>
        </div>
    </div><!-- /.container-fluid -->
</nav>

<!-- 页面内容 -->
<div class="container">
    <form class="simple-form" action="" method="POST">
        <div class="alert alert-warning">
            <h3>【<?php echo $viewData['checkDir']; ?>】需要输入密码才能浏览</h3>
            <p class="mt-1">如果你还不知道密码，请联系管理员。</p>
        </div>
        <?php
        if (!empty($viewData['errorMsg'])) {
            echo <<<eof
        <div class="alert alert-danger">
            <h3>Oops，出错啦！</h3>
            <p class="mt-1">{$viewData['errorMsg']}</p>
        </div>
eof;
        }
        ?>
        <div class="form-group">
            <input name="password" placeholder="请填写密码" type="password" class="form-control">
        </div>
        <div class="">
            <button class="btn btn-primary" type="submit">
                继续浏览
            </button>
        </div>
    </form>
</div>
