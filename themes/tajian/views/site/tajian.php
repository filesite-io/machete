<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$loginBackUrl = !empty($viewData['loginedUser']['username']) ? "/{$viewData['loginedUser']['username']}/my/share" : '/my/share';
?>
<main class="tajian_index">
    <h1 class="h1title vercenter mt65">
        你的聚宝盆
        <small>- 帮你收纳不同App/网站有价值的视频</small>
    </h1>

    <div class="btns clearfix">
        <div class="favbtn">
            <a href="/site/register/">
                <img src="/img/favorite.png" alt="Create your favorite tajian link." width="100">
                <br>
                创建聚宝盆
            </a>
        </div>
        <div class="downbtn">
            <a href="/site/login/?go=<?=$loginBackUrl?>">
                <img src="/img/share.png" alt="Download machete source code" width="100">
                <br>
                分享聚宝盆
            </a>
        </div>
    </div>

    <h3 class="h3title pl20">谁在用？</h3>
    <ul class="ulist pl20">
        <li>布道者、意见领袖</li>
        <li>发烧友、分享达人</li>
        <li>有收藏、整理知识库习惯的朋友</li>
    </ul>

    <h3 class="h3title pl20">他们的聚宝盆</h3>
    <div class="pl20 clearfix">
        <div class="pfav mt10">
            <a href="/1003/" target="_blank">
                <h4>秒懂AI</h4>
                <small>人工智能相关的，统统帮你整理到位。</small>
            </a>
        </div>
        <div class="pfav mt10">
            <a href="/1000/" target="_blank">
                <h4>一灯大师</h4>
                <small>帮你整理“非主流”的、“非专家”的不一样的观点。</small>
            </a>
        </div>

    </div>

    <h3 class="h3title pl20">使用步骤</h3>
    <div class="pl20 lh18">
        <p>
            1. 从各视频App、网站<strong>复制视频的分享链接</strong>
            <br>
            2. 登录后在“<strong>添加收藏</strong>”里<strong>粘贴</strong>保存
            <br>
            3. 分享你的专属链接给朋友
            <?php
            if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['config']['defaultUserId'])) {
                $defaultUserId = FSC::$app['config']['defaultUserId'];
                echo <<<eof
            （<a href="/{$defaultUserId}/" target="_blank">点我体验</a>）
eof;
            }
            ?>
        </p>

        <h4 class="mt20">没注册？</h4>    
        <p>
            用手机号码 + 邀请码（邀请者手机号末 6 位）
            <a href="/site/register/" class="loginbtn">去注册</a>
            <br class="hidden-xs">
            还可以加客服微信获取注册邀请码
            <button class="mt10 bt_kf_JS" type="button" data-hide="隐藏二维码">显示二维码</button>
            <img src="/tajian/wx_jialuoma.jpeg" alt="Ta荐客服微信二维码" width="200" class="kfwx kf_wx_JS hide">
        </p>
    </div>

    <h3 class="h3title pl20">支持的视频App/网站</h3>
    <ul class="ulist pl20">
        <li>B站- 哔哩哔哩</li>
        <li>抖音</li>
        <li>快手</li>
        <li>西瓜视频</li>
    </ul>
    <p class="pl20 pt20">
        更多视频App和网站将陆续增加；
        <br><strong>任意网站</strong>收藏限VIP用户使用，如需开通请联系客服邮箱（machete#filesite.io，替换#为@）。
    </p>

    <h3 class="h3title pl20">Ta荐核心数据</h3>
    <div class="stats pl20">
        <span class="col success">
            <strong><?=$viewData['stats']['video']?></strong>
            <label>视频</label>
        </span>
        <span class="col info">
            <strong><?=$viewData['stats']['user']?></strong>
            <label>用户</label>
        </span>
        <span class="col">
            <strong><?=$viewData['stats']['tag']?></strong>
            <label>分类</label>
        </span>
    </div>
    <div class="pl20 lh18">
        因为有你，从此世界变得不一样，<a href="/site/register/">马上注册加入吧～</a>
    </div>

    <h3 class="h3title pl20">搭建视频分享网站</h3>
    <div class="pl20 lh18">
        付费协助搭建<strong>视频分享网站</strong>，请发Email给我们吧：
        <strong>machete#filesite.io</strong>，替换#为@。
    </div>

</main>
