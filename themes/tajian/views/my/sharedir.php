<?php
$linkPrefix = '';
//多用户路径支持
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $linkPrefix = '/' . FSC::$app['user_id'];
}

$max_num = !empty(FSC::$app['config']['tajian']['max_dir_num']) ? FSC::$app['config']['tajian']['max_dir_num'] : 10;
?><main class="g_main_lay">
    <div class="breadcrumbs">
        <a href="<?=$linkPrefix?>/my/">&lt;&lt;返回</a>
    </div>
    <form class="g_form_style mt65" id="share_dir_form" action="" method="POST">
        <?php if (empty($viewData['isVipUser'])) { ?>
        <div class="alert warning">此功能限VIP使用，限时免费开通请联系客服哦</div>
        <?php } ?>

        <div class="mb-3 pt20">
            <label for="text_input_phone" class="form-label">朋友手机号码</label>
            <input id="text_input_phone" name="cellphone" placeholder="请填写朋友的手机号码" value="">
        </div>

        <div class="mb-3 pt20">
            <select name="dir">
                <option value="">选择账号</option>
                <?php if (!empty($viewData['myNicks'])) {
                foreach($viewData['myNicks'] as $dir => $nickname) {
                    //忽略不属于自己的账号
                    if (!empty($viewData['isMine']) && empty($viewData['isMine'][$dir])) {continue;}

                    echo <<<eof
                <option value="{$dir}">{$nickname}</option>
eof;
                    }
                } ?>
            </select>
        </div>
        <p class="mt10">说明：<br>把聚宝盆共享给朋友之后，你们可以共同维护里面的内容。</p>

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