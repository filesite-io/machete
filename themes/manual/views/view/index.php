<div class="indexes">
    <p><a href="javascript:history.back(-1);">&lt;&lt;返回</a></p>
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
        author: <small>{$viewData['author']}</small>
        <br>
eof;
        }
        ?>
        updated: <small><?php echo date('Y-m-d H:i:s', $viewData['updateTime']); ?></small>
    </div>
</div>

<div class="content markdown-body">
    <?php echo $viewData['html']; ?>
</div>