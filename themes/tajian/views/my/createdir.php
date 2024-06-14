<?php
$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

?><main class="g_main_lay">
    <div class="breadcrumbs">
        <a href="<?=$linkPrefix?>/my/">&lt;&lt;返回</a>
    </div>
    <form class="g_form_style mt65" id="dir_new_form" action="" method="POST">
        <div class="mb-3 pt20">
            <label for="text_input_dir" class="form-label">账号昵称</label>
            <input id="text_input_dir" name="nickname" placeholder="请填写 2 - 5 个汉字" value="">
            <p class="mt10">
                <strong>说明：</strong>
                <br>
                VIP可以添加 <strong><?=$viewData['max_num_vip']?></strong> 个账号，
                当前可添加 <strong><?=$viewData['max_num']?></strong> 个账号。
            </p>
        </div>
        <div class="avform_bt">
            <button class="jsbtn" aria-label="保存" type="submit">
                <div class="loading_bt bt_class_JS elementNone verMiddle">
                    <svg viewBox="25 25 50 50">
                        <circle cx="50" cy="50" r="20"></circle>
                    </svg>
                </div>
                <span class="bt_text_JS">保存</span>
                <div class="bt_loading_cover bt_class_JS elementNone"></div>
            </button>
        </div>
    </form>
</main>