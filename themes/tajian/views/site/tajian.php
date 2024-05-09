<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

?>
<main class="tajian_index">
    <h1 class="h1title vercenter mt65">
        一个好用的视频收藏夹
        <small>- 帮你整理不同App/网站的好视频，还能分享给朋友</small>
    </h1>

    <div class="btns clearfix">
        <div class="favbtn">
            <a href="/site/register/">
                <img src="/img/favorite.png" alt="Create your favorite tajian link." width="100">
                <br>
                创建专属收藏夹
            </a>
        </div>
        <div class="downbtn">
            <a href="/site/login/">
                <img src="/img/share.png" alt="Download machete source code" width="100">
                <br>
                获取分享链接
            </a>
        </div>
    </div>

    <h3 class="h3title pl20">使用方法</h3>
    <div class="pl20 lh18">
        1. 点上面“创建专属收藏夹”，
        <br>&nbsp;&nbsp;&nbsp;&nbsp;用手机号码 + 邀请码（朋友手机号码末6位）<a href="/site/register/" class="loginbtn">注册</a>
        <br class="hidden-xs">
        &nbsp;&nbsp;&nbsp;&nbsp;还可以加客服微信索要注册邀请码
        <button class="mt10 bt_kf_JS" type="button" data-hide="隐藏二维码">显示二维码</button>
        <img src="/tajian/wx_jialuoma.jpeg" alt="Ta荐客服微信二维码" width="200" class="kfwx kf_wx_JS hide">
        <br>
        2. 用手机号码 <a href="/site/login/" class="loginbtn">登录</a>
        <br class="hidden-xs">
        3. 从各大视频App、网站复制分享链接
        <br>
        4. 在“添加”里粘贴后保存
        <br>
        5. 分享你的专属链接给朋友
    </div>

    <h3 class="h3title pl20">支持的视频App/网站</h3>
    <ul class="ulist pl20">
        <li>抖音</li>
        <li>B站- 哔哩哔哩</li>
        <li>快手</li>
        <li>西瓜视频</li>
    </ul>
    <p class="pl20 pt20">其它App和网站将陆续增加。。。</p>

</main>
