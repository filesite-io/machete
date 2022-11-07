<div class="menu">
<?php
$selectedId = $viewData['cateId'];
$breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
if (!empty($viewData['menus'])) {        //只显示第一级目录
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
        $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
        $category = !empty($viewData['scanResults'][$selectedId]) ? $viewData['scanResults'][$selectedId] : [];

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

        if (!empty($category['directories'])) {        //两级目录支持
            $index = 0;
            foreach($category['directories'] as $dir) {
                echo <<<eof
                <a href="{$dir['path']}" class="img-item">
eof;

                if (!empty($dir['snapshot'])) {
                    if ($index > 0) {
                        echo <<<eof
    <img data-src="{$dir['snapshot']}" class="lazyload" alt="{$dir['directory']}">
eof;
                    }else {
                        echo <<<eof
    <img src="{$dir['snapshot']}" alt="{$dir['directory']}">
eof;
                    }
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
                        if ($index > 0) {
                            echo <<<eof
    <img data-src="{$first_img['path']}" class="lazyload" alt="{$first_img['filename']}">
eof;
                        }else {
                            echo <<<eof
    <img src="{$first_img['path']}" alt="{$first_img['filename']}">
eof;
                        }
                    }else {
                        echo <<<eof
    <img src="/img/default.png" alt="default image">
eof;
                    }
                }

                $title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
                echo <<<eof
                <strong>{$title}</strong>
            </a>
eof;
                $index ++;
            }
        }

        if (!empty($category['files'])) {        //一级目录支持
            $index = 0;
            foreach($category['files'] as $file) {
                if (!in_array($file['extension'], $imgExts)) {continue;}

                $title = !empty($file['title']) ? $file['title'] : $file['filename'];

                if ($index > 0) {
                    echo <<<eof
    <a href="{$file['path']}" class="img-item img-preview" target="_blank">
        <img data-src="{$file['path']}" class="lazyload" alt="{$file['filename']}">
        <strong>{$title}</strong>
    </a>
eof;
                }else {
                    echo <<<eof
    <a href="{$file['path']}" class="img-item img-preview" target="_blank">
        <img src="{$file['path']}" alt="{$file['filename']}">
        <strong>{$title}</strong>
    </a>
eof;
                }

                $index ++;
            }
        }
    ?>
</div>

<?php if (!empty($viewData['mp3File'])) { ?>
<audio autoplay controls loop preload="auto" id="music" class="hide">
    <source src="<?=$viewData['mp3File']['path']?>" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>
<div class="mbtns" onclick="playMusic()">
    <div class="mbtn playing hide" id="btn_playing">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cassette-fill" viewBox="0 0 16 16">
            <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h.191l1.862-3.724A.5.5 0 0 1 4 10h8a.5.5 0 0 1 .447.276L14.31 14h.191a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13ZM4 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm8 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM6 6a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2H7a1 1 0 0 1-1-1Z"/>
            <path d="m13.191 14-1.5-3H4.309l-1.5 3h10.382Z"/>
        </svg>
    </div>
    <div class="mbtn paused" id="btn_paused">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cassette" viewBox="0 0 16 16">
            <path d="M4 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7 6a1 1 0 0 0 0 2h2a1 1 0 1 0 0-2H7Z"/>
            <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13ZM1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-.691l-1.362-2.724A.5.5 0 0 0 12 10H4a.5.5 0 0 0-.447.276L2.19 13H1.5a.5.5 0 0 1-.5-.5v-9ZM11.691 11l1 2H3.309l1-2h7.382Z"/>
        </svg>
    </div>
</div>
<script>
function playMusic() {
    var mp3 = document.getElementById('music');
    var playingBtn = document.getElementById('btn_playing'),
        pausedBtn = document.getElementById('btn_paused');
    try {
        if (mp3.paused) {
            mp3.play();
            mp3.volume = 0.5;
            playingBtn.className = playingBtn.className.replace(' hide', '');
            pausedBtn.className = pausedBtn.className.replace(' hide', '') + ' hide';
        }else {
            mp3.pause();
            pausedBtn.className = pausedBtn.className.replace(' hide', '');
            playingBtn.className = playingBtn.className.replace(' hide', '') + ' hide';
        }
    }catch(e){}
}

function detectMusicAutoPlaying() {
    var mp3 = document.getElementById('music');
    var playingBtn = document.getElementById('btn_playing'),
        pausedBtn = document.getElementById('btn_paused');
    try {
        if (!mp3.paused) {
            mp3.volume = 0.5;
            playingBtn.className = pausedBtn.className.replace(' hide', '');
            pausedBtn.className = playingBtn.className.replace(' hide', '') + ' hide';
            if (typeof(timer) != 'undefined') {clearInterval(timer);}
        }
    }catch(e){}
}
var timer = setInterval(detectMusicAutoPlaying, 50);
</script>
<?php } ?>