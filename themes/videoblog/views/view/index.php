<?php
$videoUrl = '';
$poster = '';
$imgExts = array('jpg', 'jpeg', 'png', 'gif');
$videoExts = array('mp4', 'm3u8');

if (!empty($viewData['video'])) {
    $video = $viewData['video'];
    if (!empty($video['directory'])) {      //如果是目录，则找出里面第一个mp4作为播放地址
        $poster = $video['snapshot'];
        if (!empty($video['files'])) {
            foreach ($video['files'] as $id => $item) {
                if (empty($poster) && in_array($item['extension'], $imgExts)) {
                    $poster = $item['path'];
                }

                if (in_array($item['extension'], $videoExts)) {
                    $videoUrl = $item['path'];
                    break;
                }
            }
        }
        
    }else {
        $videoUrl = $video['path'];
    }
}
?><div class="video">
    <video class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
        controls
        playsinline
        data-setup='{"autoplay":"muted"}'
        poster="<?php echo !empty($viewData['video']['snapshot']) ? $viewData['video']['snapshot'] : ''; ?>" 
        id="myvideo">
        <source src="<?php echo $videoUrl; ?>" type="video/mp4">
    </video>
</div>
