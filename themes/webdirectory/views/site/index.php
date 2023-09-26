<div class="indexes">
<?php
if (!empty($viewData['menus'])) {
    foreach($viewData['menus'] as $menu) {
        $link = urlencode($menu['directory']);
        echo <<<eof
        <a href="#{$link}">{$menu['directory']}</a>
eof;
    }
}
?>
</div>

<div class="content markdown-body">
    <?php
    if (!empty($viewData['dirTree'])) {
        foreach($viewData['dirTree'] as $id => $item) {
            //跳过一级目录下的文件
            if (!empty($item['filename'])) {continue;}

            echo <<<eof
    <div class="url-group clearfix">
        <h3 id="{$item['directory']}">{$item['directory']}</h3>
eof;

            //二级目录
            if (!empty($item['directories'])) {
                foreach($item['directories'] as $fid => $dir) {
                    echo <<<eof
        <div class="url-group clearfix">
            <h4>- {$dir['directory']}</h4>
eof;

                    //URL链接
                    if (!empty($dir['files'])) {
                        foreach($dir['files'] as $urlItem) {
                        	if (empty($urlItem['shortcut'])) {continue;}
                            echo <<<eof
                <div class="url">
                    <a href="{$urlItem['shortcut']['url']}" target="_blank">
                        <strong>{$urlItem['shortcut']['name']}</strong>
                        <small>{$urlItem['shortcut']['url']}</small>
                    </a>
                </div>
eof;
                        }
                    }


                    echo <<<eof
        </div>
eof;
                }
            }

            //URL链接
            if (!empty($item['files'])) {
                foreach($item['files'] as $fid => $urlItem) {
                	if (empty($urlItem['shortcut'])) {continue;}
                    echo <<<eof
        <div class="url">
            <a href="{$urlItem['shortcut']['url']}" target="_blank">
                <strong>{$urlItem['shortcut']['name']}</strong>
                <small>{$urlItem['shortcut']['url']}</small>
            </a>
        </div>
eof;
                }
            }

            echo <<<eof
    </div>
eof;

        }
    }
    ?>
</div>