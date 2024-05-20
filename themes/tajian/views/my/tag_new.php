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
    <form class="g_form_style mt65" id="tag_new_form" action="" method="POST">
        <div class="mb-3 pt20">
            <label for="text_input_tag" class="form-label">分类名</label>
            <input id="text_input_tag" name="tag" placeholder="请填写 2 - 15 个汉字、数字、英文" value="">
            <p class="mt10">说明：<br>最多添加 <strong>50</strong> 个分类；<br>如果以英文开头，首字母请大写。</p>
        </div>
        <div class="avform_bt vercenter">
            <button class="jsbtn" aria-label="保存" type="button">
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