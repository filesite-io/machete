/*  
******The author

******Tan
 */

//关闭videojs的ga统计
window.HELP_IMPROVE_VIDEOJS = false;

if ($('#image_site').get(0)) {

    // 图片浏览
    $('[data-fancybox]').fancybox({
        toolbar: true,
        loop: true,
        smallBtn: false,
        buttons: ["zoom", "slideShow", "fullScreen", "download", "thumbs", "close"],
        iframe: {
            preload: false
        }
    })

    // 图片懒加载
    /*
    $("img.lazy").lazyload({
        effect: "fadeIn",
        event: "scroll"
    });
    */

    //需要浏览器支持naturalWidth
    var saveSmallImg = function(imgEl) {
        var width = imgEl.width,
            naturalWidth = imgEl.naturalWidth,
            naturalHeight = imgEl.naturalHeight;
        if (!naturalWidth || naturalWidth - width < 100) {return false;}

        var aspect = naturalHeight / naturalWidth;

        var canvas = document.createElement('canvas');

        canvas.width = width;
        canvas.height = width * aspect;

        var ctx = canvas.getContext('2d');
        ctx.drawImage( imgEl, 0, 0, canvas.width, canvas.height );

        var smallImg = canvas.toDataURL('image/jpeg');
        if (smallImg && /^data:image\/.+;base64,/i.test(smallImg)) {
            var params = {
                    id: $(imgEl).attr('data-id'),
                    data: smallImg
                };

            $.ajax({
                url: '/site/savesmallimg',
                method: 'POST',
                dataType: 'json',
                data: params
            }).done(function(data) {
                if (data.code != 1) {
                    console.warn('小尺寸图片数据保存失败', data.msg);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('小尺寸图片数据保存失败，错误信息：' + errorThrown);
            });
        }
    };

    //https://github.com/verlok/vanilla-lazyload
    var myLazyLoad = new LazyLoad({
        data_src: 'original',
        callback_error: function(el, ioe, lazyins) {
            el.src = '/img/default.png';
        },
        callback_loaded: function(el, ioe, lazyins) {
            saveSmallImg(el);
        }
    });

    // 返回顶部
    var scrolltop = $('#image_site .scroll_topJS');

    var timer_icon_fade = null;
    $(window).scroll(function () {
        if (timer_icon_fade) {clearTimeout(timer_icon_fade);}

        timer_icon_fade = setTimeout(function() {
            if ($(window).scrollTop() > 100) {
                scrolltop.fadeIn();
            } else {
                scrolltop.fadeOut();
            }
        }, 100);
    });

    scrolltop.on('touchstart click', function () {
        $('html, body').animate({ scrollTop: 0 }, 380);
        return false;
    });

    // 点击打开右侧弹出框
    $('#image_site .connectmeJS').click(function () {
        $('#image_site .blank_coverJS, #image_site .right_sideboxJS').removeClass('elementNone');
        window.setTimeout(function () {
            $('#image_site .blank_coverJS').addClass('opacityshow');
            $('#image_site .right_sideboxJS').addClass('sideboxShow');

        }, 0);

    });

    // 点击关闭右侧弹出框
    $('#image_site .rtcloseJS').click(function () {
        $('#image_site .blank_coverJS').removeClass('opacityshow');
        $('#image_site .right_sideboxJS').removeClass('sideboxShow');
        window.setTimeout(function () {
            $('#image_site .blank_coverJS, #image_site .right_sideboxJS').addClass('elementNone');

        }, 500);
    });

}


// 白天黑夜模式切换
var saveLanpnumToLocalstorage = function(lanpnum) {
    try {
        var key = 'user_lanpnum';
        localStorage.setItem(key, lanpnum);
    }catch(err) {
        console.error('保存本地存储失败', err);
    }
};

var getLanpnumFromLocalstorage = function() {
    try {
        var key = 'user_lanpnum';
        return localStorage.getItem(key);
    }catch(err) {
        console.error('保存本地存储失败', err);
    }

    return false;
};

var toggleLampshow = function(lanpnum) {
    if (lanpnum == 1) {
        $('#markdowncss').attr('href', '/css/github-markdown-dark.css');
        $(document.body).addClass('lampshow');
        $('.navbarJS').removeClass('navbar-default').addClass('navbar-inverse'); // 导航栏用bootstrap主题切换
    } else if (lanpnum == 0) {
        $('#markdowncss').attr('href', '/css/github-markdown-light.css');
        $(document.body).removeClass('lampshow');
        $('.navbarJS').addClass('navbar-default').removeClass('navbar-inverse');
    }
};

var lanpnum = getLanpnumFromLocalstorage();
if (lanpnum !== false) {
    toggleLampshow(lanpnum);
}
$('.lampJS').click(function () {
    lanpnum = !lanpnum || lanpnum != 1 ? 1 : 0;
    toggleLampshow(lanpnum);
    saveLanpnumToLocalstorage(lanpnum);
});

//异步加载目录的封面图
$('.dir_item').each(function(index, el) {
    var cid = $(el).attr('data-cid'), id = $(el).attr('data-id');
    if ($(el).find('.im_img').length == 0) {
        $.ajax({
            url: '/site/dirsnap',
            method: 'POST',
            dataType: 'json',
            data: {
                cid: cid,
                id: id
            }
        }).done(function(data) {
            if (data.code == 1 && data.url) {
                var imgHtml = '<img src="' + data.url + '"';
                if (data.img_id) {
                    imgHtml += ' data-id="' + data.img_id + '"'
                }
                imgHtml += ' class="bor_radius im_img">';
                $(el).find('.im_img_title').before(imgHtml);

                if (data.img_id) {
                    setTimeout(function() {
                        var imgs = $(el).find('.im_img');
                        if (imgs.length > 0) {
                            saveSmallImg(imgs[0]);
                        }
                    }, 100);
                }
            }else {
                console.warn('目录 %s 里没有任何图片', id);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('获取封面图失败，错误信息：' + errorThrown);
        });
    }
});


//刷新缓存
$('.cleanCacheJS').click(function () {
    $.ajax({
        url: '/site/cleancache',
        dataType: 'json',
        method: 'POST'
    }).done(function(data) {
        if (data.code == 1) {
            location.href = '/';
        }else {
            alert('缓存清空失败，请稍后重试，错误信息：' + data.msg);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        alert('请求失败，请稍后重试，错误信息：' + errorThrown);
    });
});

// 音乐播放
if ($('#music_main').length > 0) {
    var musicState = 0;
    $('#music_main').get(0).volume = 0.5; // 控制音量
    $('.musicJS').click(function () {
        if (musicState == 0) {
            $('#music_main').get(0).play();
            $('.musicJS').addClass('music_put');
            musicState = 1;
        } else {
            $('#music_main').get(0).pause();
            $('.musicJS').removeClass('music_put');
            musicState = 0;
        }
        return;
    })

    $(document).one('touchstart mousedown', function () {
        $('#music_main').get(0).play();
        $('.musicJS').addClass('music_put');
        musicState = 1;
    });
}

//二维码显示
if ($('#qrimg').length > 0 && typeof(QRCode) != 'undefined') {
    var qrcode = new QRCode("qrimg", {
        text: location.href,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.L
    });
}

var formatDuration = function(duration) {
    var str = '00:00:00';

    var hours = 0, minutes = 0;

    if (duration > 3600) {
        hours = Math.floor(duration / 3600);
    }

    if (duration > 60) {
        minutes = Math.floor((duration-hours*3600) / 60);
    }

    var seconds = Math.floor(duration - hours*3600 - minutes*60);

    str = hours.toString().padStart(2, '0') + ':'
            + minutes.toString().padStart(2, '0') + ':'
            + seconds.toString().padStart(2, '0');

    return str;
};

//自动为列表页视频生成封面图并保存
var noSnapVideos = [];
if ($('#pr-player').length > 0 && typeof(videojs) != 'undefined') {
    var myPlayer = videojs('pr-player', {
        controls: false,
        autoplay: false,
        muted: true,
        preload: 'auto'
    });

    var mc_video_id = '';
    var tryToGetVideoSnapshot = function() {
        if (noSnapVideos.length == 0 || mc_video_id) {return false;}

        var videoItem = noSnapVideos.shift();
        mc_video_id = videoItem.id;

        try {
            myPlayer.src(videoItem.url);
            myPlayer.play();
        }catch(err){
            console.error('自动生成视频封面图失败', err);
        }
    };

    myPlayer.on('playing', function() {
        myPlayer.pause();

        if (typeof(mc_video_id) != 'undefined' && mc_video_id) {
            var height = myPlayer.videoHeight(), width = myPlayer.videoWidth(),
                aspect = height / width;
            var canvas = document.createElement('canvas');
            var video = $('video.vjs-tech').get(0);

            canvas.width = Math.ceil(360/aspect);
            canvas.height = 360;    //360p

            var ctx = canvas.getContext('2d');
            ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

            var snapshotImg = canvas.toDataURL('image/jpeg');
            var duration = myPlayer.duration();

            //更新视频封面图和视频时长
            if (duration && snapshotImg && /^data:image\/.+;base64,/i.test(snapshotImg)) {
                $('#poster_'+mc_video_id).attr('src', snapshotImg);
                $('#poster_'+mc_video_id).parent('a').find('.duration').text(formatDuration(duration));
                $('#poster_'+mc_video_id).parent('a').find('.playbtn').removeClass('hide');

                saveVideoMeta(mc_video_id, {
                    duration: duration,
                    snapshot: snapshotImg
                });
            }

            mc_video_id = '';           //reset
        }
    });

    setInterval(tryToGetVideoSnapshot, 500);
}

//视频列表封面图加载
var getVideoMetaAndShowIt = function(videoId, videoUrl) {
    $.ajax({
        url: '/site/videometa',
        method: 'GET',
        dataType: 'json',
        data: {
            id: videoId
        }
    }).done(function(data) {
        if (data.code != 1) {
            console.warn(data.msg);
            noSnapVideos.push({id: videoId, url: videoUrl});
        }else {
            $('#poster_'+videoId).attr('src', data.meta.snapshot);
            $('#poster_'+videoId).parent('a').find('.duration').text(formatDuration(data.meta.duration));
            $('#poster_'+videoId).parent('a').find('.playbtn').removeClass('hide');
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('视频数据获取失败，错误信息：' + errorThrown);
    });
};

$('.video-poster').each(function(index, el) {
    var videoId = $(el).attr('data-video-id'),
        videoUrl = $(el).attr('data-video-url');
    getVideoMetaAndShowIt(videoId, videoUrl);
});

//保存视频数据
var saveVideoMeta = function(videoId, metaData, manual) {
    var params = {
            id: videoId,
            meta: metaData
        };
    if (typeof(manual) != 'undefined' && manual) {
        params.manual = 1;
    }

    $.ajax({
        url: '/site/savevideometa',
        method: 'POST',
        dataType: 'json',
        data: params
    }).done(function(data) {
        if (data.code != 1) {
            console.warn('视频数据保存失败', data.msg);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('视频数据保存失败，错误信息：' + errorThrown);
    });
};

//视频播放器
if ($('#my-player').length > 0 && typeof(videojs) != 'undefined') {
    var myPlayer = videojs('my-player', {
        controls: true,
        autoplay: true,
        muted: true,
        preload: 'auto'
    });

    var takeScreenshot = function(manual) {
        //myPlayer.pause();

        var height = myPlayer.videoHeight(), width = myPlayer.videoWidth(),
            aspect = height / width;
        var canvas = document.createElement('canvas');
        var video = $('video.vjs-tech').get(0);

        canvas.width = Math.ceil(360/aspect);
        canvas.height = 360;    //360p

        var ctx = canvas.getContext('2d');
        ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

        var snapshotImg = canvas.toDataURL('image/jpeg'),
            duration = myPlayer.duration();
        if (duration && snapshotImg && /^data:image\/.+;base64,/i.test(snapshotImg)) {
            saveVideoMeta($('video.vjs-tech').attr('data-id'), {
                duration: duration,
                snapshot: snapshotImg
            }, manual);
        }

        //myPlayer.play();
    };

    myPlayer.one('playing', function() {
        var screenshot_start = $('video.vjs-tech').attr('data-screenshot-start');

        if (screenshot_start) {
            screenshot_start = parseInt(screenshot_start);
        }

        if (!screenshot_start) {
            screenshot_start = 1000;
        }

        setTimeout(takeScreenshot, screenshot_start);
    });

    $('.btn-snapshot').click(function(e) {
        var clickedBtn = $(e.target);
        clickedBtn.prop('disabled', true);

        var manual = 1;
        takeScreenshot(manual);

        setTimeout(function() {
            clickedBtn.prop('disabled', false);
        }, 3000);
    });
}