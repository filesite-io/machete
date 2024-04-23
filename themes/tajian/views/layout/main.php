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
<body>
<div class="app_recommend g_app_lay" id="app_recommend">
    <header class="top_nav">
        <a class="log_tn clearfix" href="/">
            <span class="verMiddle">Ta荐</span>
            <?php if (!empty($viewData['nickname'])) { ?>
                - <strong><?=$viewData['nickname']?></strong>
            <?php } ?>
        </a>
        <div class="search hide_movi_xs hide">
            <form id="search_form" class="search_form" action="" method="GET">
                <div class="input_sf">
                    <input class="input_sf_JS" type="search" placeholder="搜索" />
                </div>
                <button class="input_sm jsbtn" aria-label="搜索"><img src="/img/search.svg" alt="图片" /></button>
            </form>
        </div>
        <div class="right_class_tn">
            <a class="search_move_tn search_mob_JS visible_movi_xs hide" href="javascript:;" title="搜索"><img src="/img/search.svg" alt="图片" /></a>
            <a class="connect_me_tn connectmeJS" href="javascript:;" title="联系我们"><img src="/img/contactUs.svg" alt="联系我们" /></a>
        </div>
    </header>

    <div class="app_layout_side">
        <div class="menu_ls g_ls_menus">
            <a class="this_set" href="<?=$linkPrefix?>/" title="">
                <img src="/img/choice.svg" alt="星星图标" />
                <span>推荐</span>
            </a>
            <a href="<?=$linkPrefix?>/site/new" title="">
                <img src="/img/addvideos.svg" alt="添加图标" />
                <span>添加</span>
            </a>
            <a href="###" title="">
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
            <img src="/img/other.svg" alt="菜单图标" />
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
            <span>联系我们</span>
            <a class="rtcloseJS" href="javascript:;">
                <img class="icon svgimg verMiddle" src="/img/clos.svg" alt="关闭" title="关闭" />
            </a>
        </h5>
        <div class="modal-body markdown-body">
            <?php echo !empty($viewData['htmlReadme']) ? $viewData['htmlReadme'] : ''; ?>
        </div>
    </div>

    <!-- 移动端搜索框 -->
    <div class="mobile_search elementNone mobile_search_JS" id="mobile_search">
        <form id="mob_search_form" class="mob_search_form search_form" action="" method="get">
            <a class="close ms_close_JS" href="javascript:;">
                <img class="icon svgimg verMiddle" src="/img/clos.svg" alt="关闭" title="关闭" />
            </a>
            <div class="input_sf">
                <input class="ms_input_js" type="search" placeholder="搜索" />
            </div>
            <button class="input_sm jsbtn" aria-label="搜索"><img src="/img/search.svg" alt="图片" /></button>
        </form>
    </div>

    <div class="footer">
        <div class="copyright">
            <?php if (!empty(FSC::$app['config']['theme'])) { ?>
                Theme name <strong><?php echo FSC::$app['config']['theme']; ?></strong>
                <br>
            <?php } ?>
            <a href="https://filesite.io" target="_blank">&copy;FileSite.io</a> 2022 - execute time: {page_time_cost} ms
            <br>
            下<a href="https://github.com/filesite-io/machete" target="_blank">Machete源码</a>搭建私有网址导航、文档、图片、视频网站
            <br>
            数据采集由
            <a href="https://herounion.filesite.io" target="_blank">HeroUnion英雄联盟</a>
            提供技术支持
        </div>
    </div>
</div><!--app_recommend-->

    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/lazyload.js"></script>
    <script src="/js/tajian.js?v<?=Html::getStaticFileVersion('tajian.js', 'js')?>"></script>
</body>
</html>
