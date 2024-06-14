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
    <div class="g_form_style mt65">
        <form id="share_dir_form" action="" method="POST">
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

        <hr class="mt20">
        <h3 class="mt20">已共享账号<small>（点击取消）</small></h3>
        <div class="mt10 my_share_dirs">
            <?php
            foreach($viewData['myShareDirs'] as $friends_cellphone => $dirs) {
                if (empty($dirs)) {continue;}

                $maskPhone = Common::maskCellphone($friends_cellphone);

                $btn_html = '';
                foreach($dirs as $dir) {
                    $btn_html .= <<<eof
                <button type="button" data-cellphone="{$friends_cellphone}" data-dir="{$dir}" class="btn-danger btn-del mt10">
                    {$viewData['myNicks'][$dir]}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"></path>
                    </svg>
                </button>
eof;
                }

                echo <<<eof
            <div class="fav-group">
                朋友手机号：<strong>{$maskPhone}</strong>
                <p>{$btn_html}</p>
            </div>
eof;
            }
            ?>
        </div>
    </div>
</main>