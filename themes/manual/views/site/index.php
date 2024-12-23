<div class="indexes">
<?php
if (!empty($viewData['titles'])) {
    foreach($viewData['titles'] as $title) {
        $link = urlencode($title['name']);
        echo <<<eof
        <{$title['heading']}><a href="#{$link}">{$title['name']}</a></{$title['heading']}>
eof;
    }
}
?>
    <div class="author">
        <?php
        if (!empty($viewData['author'])) {
            echo <<<eof
        作者：<small>{$viewData['author']}</small>
        <br>
eof;
        }
        ?>
        修改：<small><?php echo date('Y-m-d H:i:s', $viewData['updateTime']); ?></small>
    </div>
</div>

<div class="content markdown-body">
    <?php echo $viewData['html']; ?>
</div>