<main class="g_main_lay g_main_border">
    <form class="add_video_form g_form_style" id="add_video_form" action="" method="get">
        <div class="">
            <label for="text_input_link" class="form-label">分享网址/内容</label>
            <textarea rows="5" id="text_input_link" name="share_content" placeholder="请粘贴分享网址/内容"></textarea>
        </div>
        <div class="mb-3">
            <label for="text_input_title" class="form-label">选分类（可选）</label>
            <select name="tag">
                <option value="">选分类</option>
    <?php
    if (!empty($viewData['tags'])) {        //显示tags分类
        foreach($viewData['tags'] as $id => $item) {
            echo <<<eof
        <option value="{$item['name']}">{$item['name']}</option>
eof;
        }
    }
    ?>
            </select>
        </div>
        <div class="avform_bt vercenter">
            <button class="jsbtn" aria-label="提交" type="button">
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
<?php
$user_id = '';
if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
    $user_id = FSC::$app['user_id'];
}
?>
<script>
    var current_user_id = '<?=$user_id?>';
</script>