<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

?><main class="g_main_lay">
    <div class="breadcrumbs">
        <a href="<?=$linkPrefix?>/my/">&lt;&lt;返回</a>
    </div>
    <div class="g_form_style">

        <div>
            点击昵称切换：
        </div>
        <ul class="mg_menus">
            <?php
            if (!empty($viewData['myNicks'])) {
                foreach($viewData['myNicks'] as $dir => $nickname) {
                    echo <<<eof
            <li><a href="{$linkPrefix}/my/index?dir={$dir}">{$dir} {$nickname}</a></li>
eof;
                }
            }else {
                echo <<<eof
            <li><a href="/site/logout">退出重新登录</a></li>
eof;
            }
            ?>
        </ul>
        <p class="mt20">你可以拥有多个“聚宝盆”，每个聚宝盆可以设定不同的主题。</p>
    </div>
</main>
