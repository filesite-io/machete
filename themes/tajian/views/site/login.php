<main class="g_main_lay">
    <form class="add_video_form g_form_style mt65" id="login_form" action="" method="POST">
        <div class="mb-3 pt20">
            <label for="text_input_username" class="form-label">手机号码</label>
            <input id="text_input_username" name="username" placeholder="请填写手机号码" type="text">
        </div>
        <div class="mb-3 twocol">
            <label for="text_input_sm" class="form-label">验证码</label>
            <input id="text_input_sm" name="smscode" placeholder="输入收到的短信验证码" type="number">
            <button class="smsbtn bt_sms_JS button button-sm button-shadow">发送验证码</button>
        </div>
        <div class="avform_bt vercenter">
            <button class="jsbtn" aria-label="登录" type="button">
                <div class="loading_bt bt_class_JS elementNone verMiddle">
                    <svg viewBox="25 25 50 50">
                        <circle cx="50" cy="50" r="20"></circle>
                    </svg>
                </div>
                <span class="bt_text_JS">登录</span>
                <div class="bt_loading_cover bt_class_JS elementNone"></div>
            </button>

            <span class="ml20">
                没注册？
                <a href="/site/register/">
                    <img src="/img/PersonalCenter.svg" alt="用户图标" width="20" class="verBottom">去注册
                </a>
            </span>
        </div>
    </form>
</main>