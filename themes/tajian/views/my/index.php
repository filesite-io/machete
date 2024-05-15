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
    <div class="g_form_style">
        <div class="vercenter">
            <strong class="nickname"><?=htmlspecialchars($viewData['nickname'], ENT_QUOTES)?></strong>
            (<a href="<?=$linkPrefix?>/my/setnickname">修改</a>)
            <p class="mt10 verright">
                <a href="/site/logout"><img src="/img/logout.svg" alt="logout" width="18" class="verTop"> 退出</a>
            </p>
        </div>
        <hr>
        <ul class="mg_menus">
            <li><a href="<?=$linkPrefix?>/site/new"><img src="/img/addvideos.svg" alt="add favorite" width="20"> 添加收藏</a></li>
            <li><a href="<?=$linkPrefix?>/my/addtag"><img src="/img/edit.svg" alt="add tag" width="18"> 添加分类</a></li>
            <li><a href="<?=$linkPrefix?>/my/favs"><img src="/img/favorite.png" alt="favorite" width="20"> 管理收藏</a></li>
            <li><a href="<?=$linkPrefix?>/my/tags"><img src="/img/collection.svg" alt="collection" width="18"> 管理分类</a></li>
            <li><a href="<?=$linkPrefix?>/my/share"><img src="/img/share-fill.svg" alt="share favorite" width="18"> 分享收藏</a></li>
        </ul>
    </div>
</main>
