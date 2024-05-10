<main class="g_main_lay">
    <form class="add_video_form g_form_style mt65" id="register_form" action="" method="POST">
        <div class="mb-3 pt20 twocol">
            <label for="text_input_code" class="form-label">邀请码</label>
            <input id="text_input_code" name="friendscode" placeholder="邀请者手机号末 6 位" type="number">
            <button class="smsbtn bt_kf_JS" type="button" data-hide="隐藏客服微信">加客服索要</button>
            <p class="mt10 hide kf_wx_JS">
                <img src="/tajian/wx_jialuoma.jpeg" alt="Ta荐客服微信二维码" width="200" class="kfwx">
            </p>
        </div>
        <div class="mb-3">
            <label for="text_input_username" class="form-label">手机号码</label>
            <input id="text_input_username" name="username" placeholder="请填写手机号码" type="text">
        </div>
        <div class="mb-3 twocol">
            <label for="text_input_sm" class="form-label">验证码</label>
            <input id="text_input_sm" name="smscode" placeholder="输入收到的短信验证码" type="number">
            <button type="button" class="smsbtn bt_sms_JS">发送验证码</button>
        </div>
        <div class="avform_bt vercenter">
            <button class="jsbtn" aria-label="注册" type="button">
                <div class="loading_bt bt_class_JS elementNone verMiddle">
                    <svg viewBox="25 25 50 50">
                        <circle cx="50" cy="50" r="20"></circle>
                    </svg>
                </div>
                <span class="bt_text_JS">注册</span>
                <div class="bt_loading_cover bt_class_JS elementNone"></div>
            </button>

            <span class="ml20">
                已有账号，
                <a href="/site/login/">
                    <img src="/img/PersonalCenter.svg" alt="用户图标" width="20" class="verBottom">去登录
                </a>
            </span>
        </div>
        <div class="mt20 vercenter">
        <?php
        if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['config']['defaultUserId'])) {
            $defaultUserId = FSC::$app['config']['defaultUserId'];
            echo <<<eof
        <a href="/{$defaultUserId}/" target="_blank">点我体验，先看看收藏夹长什么样？</a>
eof;
        }
        ?>
        </div>
    </form>
</main>