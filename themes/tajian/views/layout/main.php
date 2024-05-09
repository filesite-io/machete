<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';

$linkPrefix = '';
$menuIcon = '/img/contactUs.svg';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
    $menuIcon = '/img/beauty/navshow.svg';
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
<body class="g_app_lay">
    <header class="top_nav">
        <a class="log_tn clearfix" href="/">
            <span class="verBaseline">Ta荐</span>
            <?php if (!empty($viewData['nickname'])) { ?>
                - <strong><?=htmlspecialchars($viewData['nickname'], ENT_QUOTES)?></strong>
            <?php } ?>
        </a>
        <div class="search hide_movi_xs hide">
            <form id="search_form" class="search_form" action="" method="GET">
                <div class="input_sf">
                    <input class="input_sf_JS" type="search" placeholder="搜索" name="keyword">
                </div>
                <button class="input_sm jsbtn" aria-label="搜索"><img src="/img/search.svg" alt="图片" /></button>
            </form>
        </div>
        <div class="right_class_tn">
            <a class="search_move_tn search_mob_JS visible_movi_xs hide" href="javascript:;" title="搜索"><img src="/img/search.svg" alt="图片" /></a>
            <a class="connect_me_tn hidden-xs connectmeJS" href="javascript:;" title="联系我们"><img src="/img/contactUs.svg" alt="联系我们"></a>
            <a class="connect_me_tn visible_movi_xs connectmeJS" href="javascript:;" title="我的分类"><img src="/img/beauty/navshow.svg" alt="展开分类菜单"></a>
        </div>
    </header>

    <div class="app_layout_side">
        <div class="menu_ls g_ls_menus">
            <a class="<?php echo FSC::$app['controller'] == 'site' && FSC::$app['action'] == 'index' ? 'this_set' : ''; ?>" href="<?=$linkPrefix?>/" title="我的首页">
                <img src="/img/choice.svg" alt="星星图标" />
                <span>首页</span>
            </a>
            <a class="<?php echo FSC::$app['controller'] == 'site' && FSC::$app['action'] == 'new' ? 'this_set' : ''; ?>" href="<?=$linkPrefix?>/site/new" title="收藏视频">
                <img src="/img/addvideos.svg" alt="添加图标" />
                <span>添加</span>
            </a>
            <a class="<?php echo FSC::$app['controller'] == 'my' ? 'this_set' : ''; ?>" href="<?=$linkPrefix?>/my/" title="个人中心">
                <img src="/img/PersonalCenter.svg" alt="用户图标" />
                <span>我的</span>
            </a>
        </div>
        <div class="g_ls_menus hide_movi_xs">
            <div class="meuns_title">视频分类</div>
<?php
$selectedId = !empty($viewData['cateId']) ? $viewData['cateId'] : '';
$breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
if (!empty($viewData['tags'])) {        //显示tags分类
    foreach($viewData['tags'] as $id => $item) {
        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'this_set' : '';
        echo <<<eof
        <a href="{$linkPrefix}/list/?id={$item['id']}" class="{$selected}">
            <img src="/img/collection-fill.svg" alt="菜单图标" />
            <span>{$item['name']}</span>
        </a>
eof;
    }
}
?>
        </div>
    </div>

<?php
//### Render view file
if (!empty($viewFile) && file_exists($viewFile)) {
    include_once $viewFile;
}
?>

    <!-- 右侧弹出框 -->
    <div class="blank_cover elementNone blank_coverJS rtcloseJS"></div>
    <div class="right_sidebox right_sideboxJS elementNone">
        <h5>
            <span class="hidden-xs">联系我们</span>
            <span class="visible_movi_xs">视频分类</span>
            <a class="rtcloseJS" href="javascript:;">
                <img class="icon svgimg verMiddle" src="/img/clos.svg" alt="关闭" title="关闭" />
            </a>
        </h5>
        <div class="modal-body markdown-body hidden-xs">
            <?php echo !empty($viewData['htmlReadme']) ? $viewData['htmlReadme'] : ''; ?>
        </div>
        <div class="modal-body g_ls_menus visible_movi_xs">
<?php
if (!empty($viewData['tags'])) {        //显示tags分类
    foreach($viewData['tags'] as $id => $item) {
        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'this_set' : '';
        echo <<<eof
        <a href="{$linkPrefix}/list/?id={$item['id']}" class="{$selected}">
            <img src="/img/collection-fill.svg" alt="菜单图标" />
            <span>{$item['name']}</span>
        </a>
eof;
    }
}
?>
        </div>
    </div>

    <!-- 移动端搜索框 -->
    <div class="mobile_search elementNone mobile_search_JS" id="mobile_search">
        <form id="mob_search_form" class="mob_search_form search_form" action="" method="get">
            <a class="close ms_close_JS" href="javascript:;">
                <img class="icon svgimg verMiddle" src="/img/clos.svg" alt="关闭" title="关闭" />
            </a>
            <div class="input_sf">
                <input class="ms_input_js" type="search" placeholder="搜索" name="keyword">
            </div>
            <button class="input_sm jsbtn" aria-label="搜索"><img src="/img/search.svg" alt="图片" /></button>
        </form>
    </div>

    <?php if (in_array(FSC::$app['controller'], array('site', 'my')) && FSC::$app['action'] == 'index') { ?>
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
    <?php } ?>

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
