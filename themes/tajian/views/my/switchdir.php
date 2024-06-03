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

        <?php if (empty($viewData['isVipUser'])) { ?>
        <div class="alert warning">此功能限VIP使用，限时免费开通请联系客服哦</div>
        <?php } ?>

        <div>
            点击昵称切换：
        </div>
        <ul class="mg_menus">
            <?php
            if (!empty($viewData['myNicks'])) {
                foreach($viewData['myNicks'] as $dir => $nickname) {
                    $icon = !empty($viewData['isMine'][$dir]) ? 'person-fill.svg' : 'person-check.svg';
                    echo <<<eof
            <li><a href="{$linkPrefix}/my/index?dir={$dir}">
                <img src="/img/{$icon}" alt="icon of {$dir}" width="16"> 
                {$nickname}
            </a></li>
eof;
                }
            }else {
                echo <<<eof
            <li><a href="/site/logout">退出重新登录</a></li>
eof;
            }
            ?>
        </ul>
        <p class="mt20">
            <img src="/img/person-fill.svg" alt="icon" width="20" class="verBottom"> 为你创建的，
            <img src="/img/person-check.svg" alt="icon" width="20" class="verBottom"> 为朋友共享给你的；
            <br>
            你可以拥有多个“聚宝盆”，为每个聚宝盆设定不同的主题。
        </p>
    </div>
</main>
