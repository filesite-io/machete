<form action="" method="post" class="px-2">
    <input type="text" name="share_content" value="" placeholder="请粘贴分享网址/内容" class="form-controll txt-input">
    <select name="tag" class="form-controll">
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
    <button class="btn" type="button" id="btn_addfav">保存</button>
</form>