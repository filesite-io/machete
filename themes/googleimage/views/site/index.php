<div class="menu">
<?php
$selectedId = $viewData['cateId'];
$breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
if (!empty($viewData['menus'])) {		//只显示第一级目录
	foreach($viewData['menus'] as $index => $item) {
		$selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'selected' : '';
		echo <<<eof
		<a href="/?id={$item['id']}" class="{$selected}">{$item['directory']}</a>
eof;
	}
}
?>
</div>

<div class="hr"></div>

<?php
if (!empty($breadcrumbs)) {
	echo <<<eof
	<div class="breadcrumbs">
		<small>当前位置：</small>
eof;

	foreach($breadcrumbs as $bread) {
		if ($bread['id'] != $selectedId) {
			echo <<<eof
		<a href="{$bread['url']}">{$bread['name']}</a> / 
eof;
		}else {
			echo <<<eof
		<strong>{$bread['name']}</strong>
eof;
		}
	}

	echo <<<eof
	</div>
eof;
}
?>

<div class="content">
	<?php
		$imgExts = ['jpg', 'jpeg', 'png', 'gif'];
		$category = $viewData['scanResults'][$selectedId];

		//当前目录的描述介绍
		if (!empty($category['description'])) {
			echo <<<eof
	<p class="catedesc">{$category['description']}</p>
eof;
		}

		//当前目录的readme详细介绍
		if (!empty($viewData['htmlCateReadme'])) {
			echo <<<eof
	<div class="cateinfo markdown-body">{$viewData['htmlCateReadme']}</div>
eof;
		}

		if (!empty($category['directories'])) {		//两级目录支持
			foreach($category['directories'] as $dir) {
				echo <<<eof
				<a href="{$dir['path']}" class="img-item">
eof;

				if (!empty($dir['snapshot'])) {
					echo <<<eof
	<img data-src="{$dir['snapshot']}" class="lazyload" alt="{$dir['directory']}">
eof;
				}else if (!empty($dir['files'])) {
					$first_img = array_shift($dir['files']);
					if (!in_array($first_img['extension'], $imgExts)) {
						foreach($dir['files'] as $file) {
							if (in_array($file['extension'], $imgExts)) {
								$first_img = $file;
								break;
							}
						}
					}

					if (in_array($first_img['extension'], $imgExts)) {
						echo <<<eof
	<img data-src="{$first_img['path']}" class="lazyload" alt="{$first_img['filename']}">
eof;
					}
				}

				$title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
				echo <<<eof
				<strong>{$title}</strong>
			</a>
eof;
			}
		}

		if (!empty($category['files'])) {		//一级目录支持
			foreach($category['files'] as $file) {
				if (!in_array($file['extension'], $imgExts)) {continue;}

				$title = !empty($file['title']) ? $file['title'] : $file['filename'];
				echo <<<eof
	<a href="{$file['path']}" class="img-item img-preview" target="_blank">
		<img data-src="{$file['path']}" class="lazyload" alt="{$file['filename']}">
		<strong>{$title}</strong>
	</a>
eof;
			}
		}
	?>
</div>