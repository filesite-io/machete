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
    <form class="g_form_style mt65 pb30" id="tags_form" action="" method="POST">
        <?php if (!empty($viewData['tags'])) {

            $tagsTotal = 0;
            foreach($viewData['tags'] as $id => $tag) {
                $tagsTotal ++;
            }

            $index = 0;
            foreach($viewData['tags'] as $id => $tag) {
                $upIconCls = $index == 0 ? 'hide' : '';
                $downIconCls = $index < $tagsTotal - 1 ? '' : 'hide';
                
                $index ++;
                echo <<<eof
        <div class="mb-3 tag-item">
            <button class="btn-danger" type="button">删除</button>
            <label class="form-label">分类 {$index}</label>
            <img src="/img/arrow-up.svg" alt="向上移动" width="18" data-action="up" class="verMiddle ml20 {$upIconCls}">
            <img src="/img/arrow-down.svg" alt="向下移动" width="18" data-action="down" class="verMiddle ml20 {$downIconCls}">
            <input name="tags[]" placeholder="请填写 2 - 5 个汉字" value="{$tag['name']}">
        </div>
eof;
            }
        ?>

        <p>
            说明：
            <br>
            分类名请填 2 - 15 个汉字、数字、英文字符；
            <br>
            点击上下箭头图标改变分类顺序，删除某个分类并不会删除这个分类里的视频。
        </p>
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

            <span class="ml20">
                <a href="<?=$linkPrefix?>/my/">返回</a>
            </span>
        </div>
        <?php }else { ?>
        <div class="vercenter mt20">
            <h3>你还没添加任何分类哦</h3>
            <p class="mt10">
                <a href="<?=$linkPrefix?>/my/addtag">
                    <img src="/img/edit.svg" alt="添加分类图标" width="50">
                    <br>
                    添加分类
                </a>
            </p>
        </div>
        <?php } ?>
    </form>
</main>