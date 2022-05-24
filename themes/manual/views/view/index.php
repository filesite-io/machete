<div class="indexes">
	<p><a href="/">&lt;&lt;返回</a></p>
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
</div>

<div class="content markdown-body">
	<?php echo $viewData['html']; ?>
</div>