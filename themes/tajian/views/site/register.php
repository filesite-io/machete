<main class="g_main_lay">
    <form class="add_video_form g_form_style mt65" id="register_form" action="" method="POST">
        <div class="mb-3 pt20 twocol">
            <label for="text_input_code" class="form-label">邀请码</label>
            <input id="text_input_code" name="friendscode" placeholder="邀请者手机号末 6 位" type="number">
            <button class="smsbtn bt_kf_JS button button-sm button-shadow" type="button" data-default-code="946847">帮我填</button>
        </div>
        <div class="mb-3">
            <label for="text_input_username" class="form-label">手机号码</label>
            <input id="text_input_username" name="username" placeholder="请填写手机号码" type="text">
        </div>
        <div class="mb-3 twocol">
            <label for="text_input_sm" class="form-label">验证码<small style="font-size:14px;font-weight:bold">（15秒内没收到，请刷新重试）</small></label>
            <input id="text_input_sm" name="smscode" placeholder="输入收到的短信验证码" type="number">
            <button type="button" class="smsbtn bt_sms_JS button button-sm button-shadow">发送验证码</button>
            <p><small class="sms_tip_JS">验证码<strong>当天有效</strong>，收到请保留 24 小时</small></p>
        </div>
        <div class="avform_bt vercenter">
            <button class="jsbtn" aria-label="注册" type="submit">
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
    </form>
</main>

<?php include_once __DIR__ . '/cookies_accept.php'; ?>