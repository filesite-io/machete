<main class="g_main_lay">
    <form class="add_video_form g_form_style" id="add_video_form" action="" method="get">
        <div class="">
            <label for="text_input_link" class="form-label">视频分享网址/文字</label>
            <textarea rows="5" id="text_input_link" name="share_content" placeholder="请粘贴从视频App或网站复制的分享网址/文字（例如在抖音App里，先点击视频的分享图标，再点：复制链接，然后在这里粘贴）"></textarea>
            <p class="mt10">
                目前只支持：
                <?php
                $supportPlatforms = FSC::$app['config']['tajian']['supportedPlatforms'];
                echo implode('，', array_slice($supportPlatforms, 0, -1));
                ?>
                ，如需支持其它平台或<strong>任意网址</strong>，以及<strong>搭建视频分享网站</strong>，请 <a href="mailto://machete@filesite.io">Email联系</a>。
            </p>
        </div>
        <div class="mb-3 mt20">
            <select name="tag" class="tagselect">
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
        <div class="avform_bt">
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