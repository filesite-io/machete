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
		$videoExts = ['mp4', 'm3u8'];
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
			$playBtnCls = '';
			$playBtn = '';
			//如果已经是二级目录了，则当三级目录为视频目录，打开播放网页
			if (!empty($selectedId) && count($breadcrumbs) >= 2) {
				$playBtnCls = ' video-js vjs-big-play-centered';
				$playBtn = <<<eof
			<button class="vjs-big-play-button" type="button" title="Play Video" aria-disabled="false" style="display:none">
				<span class="vjs-icon-placeholder" aria-hidden="true"></span>
				<span class="vjs-control-text" aria-live="polite">Play Video</span>
			</button>
eof;
			}
			

			foreach($category['directories'] as $dir) {
				$playUrl = !empty($playBtn) ? "/view/?id={$dir['id']}" : $dir['path'];
				$openInBlank = !empty($playBtn) ? ' target="_blank"' : '';
				echo <<<eof
			<a href="{$playUrl}" class="img-item"{$openInBlank}>
				<span class="img-con{$playBtnCls}">
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

				if (!empty($dir['duration'])) {
					echo <<<eof
	<span class="duration">{$dir['duration']}</span>
eof;
				}

				$title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
				echo <<<eof
					{$playBtn}
				</span>
				<strong>{$title}</strong>
			</a>
eof;
			}
		}

		if (!empty($category['files'])) {		//一级目录支持，目录下直接存放视频文件
			$first_img = '';
			foreach($category['files'] as $file) {
				if (!in_array($file['extension'], $videoExts)) {
					//如果是最后一层视频目录，取第一张图片做封面
					if (empty($first_img) && empty($category['snapshot']) && in_array($file['extension'], $imgExts)) {
						$first_img = $file;
					}
					continue;
				}

				$duration = !empty($category['duration']) ? $category['duration'] : '';
				$snapshot = !empty($file['snapshot']) ? $file['snapshot'] : (!empty($category['snapshot']) ? $category['snapshot'] : $first_img['path']);

				$title = !empty($category['title']) ? $category['title'] : $file['filename'];
				echo <<<eof
	<a href="/view/?id={$file['id']}" class="img-item img-preview" target="_blank">
		<span class="img-con video-js vjs-big-play-centered">
			<img data-src="{$snapshot}" class="lazyload" alt="snapshot of {$title}">
			<span class="duration">{$duration}</span>
			<button class="vjs-big-play-button" type="button" title="Play Video" aria-disabled="false" style="display:none">
				<span class="vjs-icon-placeholder" aria-hidden="true"></span>
				<span class="vjs-control-text" aria-live="polite">Play Video</span>
			</button>
		</span>
		<strong>{$title}</strong>
	</a>
eof;
			}
		}
	?>
</div>