/*  
 * The author
 * Tan
 */
//关闭videojs的ga统计
window.HELP_IMPROVE_VIDEOJS = false;

//fancybox自定义显示原图按钮
var customToolbar_show1to1 = {
    tpl: $('#btn_show1to1_tmp').html(),
    click: function() {
        var fancybox = this.instance;
        var slide = fancybox.getSlide();

        if (slide.src == slide.downloadSrc) {
            slide.panzoom.toggleZoom();
            return false;
        }

        fancybox.showLoading(slide);
        //如果没有自动显示loading图标，主动加上
        if ($(slide.el).find('.fancybox-spinner').length == 0) {
            var spinner = '<div class="f-spinner fancybox-spinner"><svg viewBox="0 0 50 50"><circle cx="25" cy="25" r="20"></circle><circle cx="25" cy="25" r="20"></circle></svg></div>';
            $(spinner).insertBefore(slide.contentEl);
        }

        $(slide.imageEl).one('load', function() {
            //console.log('image loaded');
            fancybox.hideLoading(slide);
            $(slide.el).find('.fancybox-spinner').remove();
            slide.panzoom.toggleZoom();
        });

        slide.src = slide.downloadSrc;
        slide.imageEl.src = slide.downloadSrc;
    }
};
var fancyboxToolbar = {
    items: {'show1to1': customToolbar_show1to1},
    display: {
        left: ["infobar"],
        middle: [
            "zoomIn",
            "zoomOut",
            "show1to1",
            "rotateCCW",
            "rotateCW",
            "flipX",
            "flipY",
            "fitX",
            "fitY",
            "reset"
        ],
        right: ["slideshow", "fullscreen", "thumbs", "download", "close"],
    },
};
if ($(window).width() < 640) {  //小屏幕只显示部分按钮
    fancyboxToolbar = {
        items: {'show1to1': customToolbar_show1to1},
        display: {
            left: ["infobar"],
            middle: [
                "zoomIn",
                "zoomOut",
                "show1to1",
                "rotateCW"
            ],
            right: ["download", "close"]
        }
    };
}

if ($('#image_site').get(0)) {

    //获取下一页图片/视频json数据
    var _slidesOfNextPage = [], _noMoreData = false, currentPage = 1;
    var getNextPagesJsonData = function(dataType) {
        if (_slidesOfNextPage.length > 0 || _noMoreData) {return false;}

        var url = new URL(location.href);
        var api = url.origin + url.pathname;

        if (currentPage == 1 && url.searchParams) {
            currentPage = url.searchParams.get('page');
            if (!currentPage) {
                currentPage = 1;
            }
        }

        var newParas = {};
        for (var key of url.searchParams.keys()) {
            if (key != 'dataType' && key != 'page') {
                newParas[key] = url.searchParams.get(key);
            }
        }

        newParas['show'] = dataType;
        newParas['dataType'] = dataType;
        newParas['page'] = parseInt(currentPage) + 1;
        currentPage = newParas['page'];

        $.ajax({
            url: api,
            method: 'GET',
            dataType: 'json',
            data: newParas
        }).done(function(data) {
            if (typeof(data.imgs) != 'undefined' && data.imgs.length > 0) {
                _slidesOfNextPage = data.imgs;
            }else if (typeof(data.videos) != 'undefined' && data.videos.length > 0) {
                _slidesOfNextPage = data.videos;
            }else if (typeof(data.msg) != 'undefined' && data.msg) {
                _noMoreData = true;
                console.warn('获取下一页json数据出错啦', data.msg);
            }else {
                _noMoreData = true;
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('获取下一页json数据失败，错误信息：' + errorThrown);
        });
    };

    //替换已经播放过的上一个图片地址为下一页对应的图片，如果下一页数量不足，则删除上一个图片
    var refreshFancyBoxStatus = 'off',
        prevSlide = null;
    var autoRefreshFancybox = function(fancybox) {
        if (refreshFancyBoxStatus == 'off') {return false;}
        getNextPagesJsonData('image');

        var currentSlide = fancybox.getSlide();
        if (prevSlide && fancybox.isCurrentSlide(prevSlide) == false) {     //替换上一个图片地址
            var nextImg = _slidesOfNextPage.shift();
            if (nextImg) {
                $(prevSlide.el).find('.fancybox__content img').attr('src', nextImg.path);
                $('.f-thumbs__slide[data-index=' + prevSlide.index + ']').find('img.f-thumbs__slide__img').attr('src', nextImg.path);

                prevSlide.src = nextImg.path;
                prevSlide.thumbElSrc = nextImg.path;
                prevSlide.thumbSrc = nextImg.path;
                prevSlide.caption = nextImg.caption;

                //console.log('prev src replace', prevSlide.index, nextImg.path);
            }
        }

        prevSlide = currentSlide;

        setTimeout(function() {
            autoRefreshFancybox(fancybox);
        }, 1000);
    };

    // 图片浏览
    Fancybox.bind('[data-fancybox]', {
        Toolbar: fancyboxToolbar,
        loop: true,
        smallBtn: false,
        on: {
            startSlideshow: function(fancybox) {
                var autoplay = fancybox.plugins.Slideshow.ref;
                if (autoplay) {
                    autoplay.options.timeout = typeof(slide_show_timeout) != 'undefined' && slide_show_timeout > 0 ? slide_show_timeout*1000 : 3000;
                }

                $('.fancybox__footer .fancybox__thumbs').addClass('is-masked'); //hide thumbs
                refreshFancyBoxStatus = 'on';
                autoRefreshFancybox(fancybox);
            },
            endSlideshow: function(fancybox) {
                refreshFancyBoxStatus = 'off';
            }
        }
    });

    //需要浏览器支持naturalWidth
    var saveSmallImg = function(imgEl, cateId) {
        var width = imgEl.width,
            height = imgEl.height,
            naturalWidth = imgEl.naturalWidth,
            naturalHeight = imgEl.naturalHeight;

        var plusRate = typeof(small_image_zoom_rate) != 'undefined' ? small_image_zoom_rate : 2.5;
        var min_width = typeof(small_image_min_width) != 'undefined' ? small_image_min_width : 200,
            min_height = typeof(small_image_min_height) != 'undefined' ? small_image_min_height : 200;

        if (!naturalWidth || naturalWidth <= min_width || naturalHeight <= min_height ||
            (typeof(disableSmallImage) != 'undefined' && disableSmallImage)
        ) {
            //console.warn('ignored', imgEl);
            return false;
        }

        var aspect = naturalHeight / naturalWidth;
        var canvas = document.createElement('canvas');

        if (naturalWidth <= naturalHeight) {
            canvas.width = width * plusRate <= min_width ? width * plusRate : min_width;
            canvas.height = canvas.width * aspect;
        }else {
            canvas.height = height * plusRate <= min_height ? height * plusRate : min_height;
            canvas.width = canvas.height / aspect;
        }

        var ctx = canvas.getContext('2d');
        ctx.drawImage( imgEl, 0, 0, canvas.width, canvas.height );

        var smallImg = canvas.toDataURL('image/jpeg');
        if (smallImg && /^data:image\/.+;base64,/i.test(smallImg)) {
            imgEl.src = smallImg;

            var params = {
                    id: $(imgEl).attr('data-id'),
                    data: smallImg
                };
            if (typeof(cateId) != 'undefined' && cateId) {
                params.pid = cateId;
            }

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


    //设置目录封面图
    $('.btn-set-snap').click(function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        var btn = $(evt.target),
            cateId = $(btn).parents('a').attr('data-pid'),
            imgUrl = $(btn).parents('a').find('img.im_img').attr('src');
        if (cateId && imgUrl) {
            var params = {
                    id: cateId,
                    url: imgUrl
                };

            $(btn).prop('disabled', true).hide();
            $.ajax({
                url: '/site/savedirsnap',
                method: 'POST',
                dataType: 'json',
                data: params
            }).done(function(data) {
                $(btn).prop('disabled', false);
                setTimeout(function() {
                    $(btn).show();
                }, 3000);
                if (data.code != 1) {
                    console.warn('目录封面图保存失败', data.msg);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $(btn).prop('disabled', false).show();
                console.error('目录封面图保存失败，错误信息：' + errorThrown);
            });
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
            method: 'GET',
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
            }else {
                var imgHtml = '<img src="/img/default.png">';
                $(el).find('.im_img_title').before(imgHtml);
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
    $('#music_main').one('canplay', function() {
        var cookieKey = 'audio_current_time';
        var currentTime = Cookies.get(cookieKey);
        if (currentTime > 0) {
            this.currentTime = currentTime;
            $('.musicJS').addClass('music_put');
        }
    });

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

    $(window).on('beforeunload', function() {
        $('#music_main').get(0).volume = 0.2;       // 减小音量
        var currentTime = $('#music_main').get(0).currentTime;
        var cookieKey = 'audio_current_time';
        Cookies.set(cookieKey, currentTime, { expires: 1 });
    });

    $(document.body).click(function(evt) {
        var elA = $(evt.target).parents('a');
        if (elA.length > 0 && elA.attr('target') == '_blank') {   //点击视频暂停音乐
            $('#music_main').get(0).pause();
            $('.musicJS').removeClass('music_put');
            musicState = 0;
        }
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
            var snapshotImg = '';

            //区分media类型，视频生成快照，音乐只获取时长
            var mediaType = myPlayer.currentType();
            if (mediaType.indexOf('mp3') > -1) {
                console.log('media type', mediaType);
            }else {
                var height = myPlayer.videoHeight(), width = myPlayer.videoWidth(),
                    aspect = height / width;
                var canvas = document.createElement('canvas');
                var video = $('video.vjs-tech').get(0);

                canvas.width = Math.ceil(360/aspect);
                canvas.height = 360;    //360p

                var ctx = canvas.getContext('2d');
                ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

                snapshotImg = canvas.toDataURL('image/jpeg');
            }

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
            }else if (duration) {       //保存音乐时长
                saveVideoMeta(mc_video_id, {
                    duration: duration
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
            console.warn('no meta data', data.msg);
            noSnapVideos.push({id: videoId, url: videoUrl});
        }else {     //jQuery会兼容传值为undefined的情况
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
        videoUrl = $(el).attr('data-video-url'),
        isLocked = $(el).attr('data-lock');
    if (!isLocked) {
        getVideoMetaAndShowIt(videoId, videoUrl);
    }
});

//保存视频/音乐meta数据
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
var moreVideos = [], _noMoreVideos = false;
if ($('#my-player').length > 0 && typeof(videojs) != 'undefined') {
    var myPlayer = videojs('my-player', {
        controls: true,
        autoplay: true,
        muted: true,
        preload: 'auto'
    });

    var takeScreenshot = function(manual) {
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
    };

    myPlayer.one('playing', function() {
        var screenshot_start = $('video.vjs-tech').attr('data-screenshot-start');

        if (screenshot_start) {
            screenshot_start = parseInt(screenshot_start);
        }

        if (!screenshot_start) {
            screenshot_start = 1000;
        }

        //区分media类型，视频生成快照，音乐只获取时长
        var mediaType = myPlayer.currentType();
        if (mediaType.indexOf('mp3') > -1) {
            saveVideoMeta($('video.vjs-tech').attr('data-id'), {
                duration: myPlayer.duration()
            });
        }else {
            setTimeout(takeScreenshot, screenshot_start);
        }
    });

    //自动播放
    try{
        var videoSrc = $('#my-player').attr('data-src'),
            videoType = $('#my-player').attr('data-type');
        myPlayer.src({
            src: videoSrc,
            type: videoType
        });
        myPlayer.muted(false);
        myPlayer.play();
    }catch(err) {
        console.error('自动播放视频失败！', err);
    }

    //生成封面图
    $('.btn-snapshot').click(function(e) {
        var clickedBtn = $(e.target);
        clickedBtn.prop('disabled', true);

        var manual = 1;
        takeScreenshot(manual);

        setTimeout(function() {
            clickedBtn.prop('disabled', false);
        }, 3000);
    });

    var getVideoUrl = function(videoId, videoPath) {
        var url = new URL(location.href);
        var api = url.origin + url.pathname;

        var newParas = [];
        for (var key of url.searchParams.keys()) {
            if (key != 'id' && key != 'url' && key != 'other') {
                newParas.push(key + '=' + url.searchParams.get(key));
            }
        }
        newParas.push('id=' + videoId);
        newParas.push('url=' + encodeURIComponent(videoPath));

        return api + '?other=1&' + newParas.join('&');
    };

    var renderVideos = function(ignoreId, videos) {
        var template = $('#template_video_item').html(),
            html = '', tmp = '';

        for (var index in videos) {
            if (videos[index].id == ignoreId) {continue;}
            tmp = template.replace(/\{videoUrl\}/ig, getVideoUrl(videos[index].id, videos[index].path));
            tmp = tmp.replace(/\{title\}/ig, videos[index].filename);
            tmp = tmp.replace(/\{videoId\}/ig, videos[index].id);
            tmp = tmp.replace(/\{videoPath\}/ig, videos[index].path);

            //支持mp3的封面图显示
            if (typeof(videos[index].snapshot) != 'undefined') {
                tmp = tmp.replace(/\{snapshot\}/ig, videos[index].snapshot);
            }

            html += tmp;
        }

        return html;
    };

    //加载更多视频
    var currentPage = $('.othervideos').attr('data-page'),
        currentPageSize = $('.othervideos').attr('data-page-size'),
        currentVideoId = $('.othervideos').attr('data-id'),
        currentMediaType = $('.othervideos').attr('data-type'),
        currentYear = $('.othervideos').attr('data-year'),
        currentMonth = $('.othervideos').attr('data-month');
    if (!currentMediaType) {
        currentMediaType = 'video';
    }
    var callback_loadNextPage = null;
    var getOtherVideos = function() {
        if (_noMoreVideos) {return false;}
        var videoId = $('.othervideos').attr('data-id'),
            cateId = $('.othervideos').attr('data-pid'),
            cacheId = $('.othervideos').attr('data-cid');
        var api = $('.othervideos').attr('data-api'),
            params = {
                id: cateId,
                cid: cacheId,
                show: currentMediaType,
                dataType: currentMediaType,
                page: currentPage,
                limit: currentPageSize,
                year: currentYear,
                month: currentMonth
            };
        $.ajax({
            url: api,
            method: 'GET',
            dataType: 'json',
            data: params
        }).done(function(data) {
            if (typeof(data.videos) != 'undefined' && data.videos.length > 0) {
                moreVideos = data.videos;
                $('.othervideos').html(renderVideos(videoId, data.videos));
                setTimeout(function() {
                    $('.othervideos .video-poster').each(function(index, el) {
                        var videoId = $(el).attr('data-video-id'),
                            videoUrl = $(el).attr('data-video-url'),
                            isLocked = $(el).attr('data-lock');
                        if (!isLocked) {
                            getVideoMetaAndShowIt(videoId, videoUrl);
                        }
                    });
                }, 50);

                if (callback_loadNextPage) {
                    callback_loadNextPage(data.videos);
                }
            }else if (typeof(data.audios) != 'undefined' && data.audios.length > 0) {
                moreVideos = data.audios;
                $('.othervideos').html(renderVideos(videoId, data.audios));
                setTimeout(function() {
                    $('.othervideos .video-poster').each(function(index, el) {
                        var videoId = $(el).attr('data-video-id'),
                            videoUrl = $(el).attr('data-video-url'),
                            isLocked = $(el).attr('data-lock');
                        if (!isLocked) {
                            getVideoMetaAndShowIt(videoId, videoUrl);
                        };
                    });
                }, 50);

                if (callback_loadNextPage) {
                    callback_loadNextPage(data.audios);
                }
            }else {
                if (currentPage > 1) {
                    currentPage = 1;        //重新从第一页循环播放
                    getOtherVideos();
                }else {
                    _noMoreVideos = true;
                    console.warn('获取更多视频数据出错啦', data.msg);
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('获取更多视频数据失败，错误信息：' + errorThrown);
        });
    };

    if ($('.othervideos').length > 0) {
        getOtherVideos();
    }

    //自动播放
    var playNextVideo = function() {
        var nextVideo = moreVideos.shift();
        if (nextVideo.id == currentVideoId && moreVideos.length > 0) {
            nextVideo = moreVideos.shift();
        }

        if (nextVideo) {
            //支持其它格式，fix /m3u8/这种路径
            myPlayer.src({
                src: nextVideo.path,
                type: nextVideo.videoType
            });
            $('.navbar-header .videotitle').text(nextVideo.filename + '.' + nextVideo.extension);
        }
    };
    myPlayer.on('ended', function() {
        var cachedAutoPlayStatus = Cookies.get('autoplay');
        if (cachedAutoPlayStatus == 'off') {return false;}

        if (moreVideos && moreVideos.length > 0) {
            playNextVideo();
        }else {
            callback_loadNextPage = function(videos) {
                playNextVideo();
            };
            currentPage = parseInt(currentPage) + 1;
            getOtherVideos();
        }
    });

    var switchAutoPlayBtns = function(status) {
        var cookieKey = 'autoplay';
        if (status == 'on') {
            $('.autoplay_disabled').removeClass('btn-primary');
            $('.autoplay_enabled').addClass('btn-primary');
            Cookies.set(cookieKey, 'on', { expires: 7 });
        }else {
            $('.autoplay_enabled').removeClass('btn-primary');
            $('.autoplay_disabled').addClass('btn-primary');
            Cookies.set(cookieKey, 'off', { expires: 7 });
        }
        $('#my-player').focus();
    };

    var cachedAutoPlayStatus = Cookies.get('autoplay');
    if (cachedAutoPlayStatus == 'off') {
        $('.autoplay_enabled').removeClass('btn-primary');
        $('.autoplay_disabled').addClass('btn-primary');
    }

    $('.autoplay_disabled').click(function() {
        switchAutoPlayBtns('off');
    });
    $('.autoplay_enabled').click(function() {
        switchAutoPlayBtns('on');
    });
}

//目录收拢、展开
$('.btn-dir-ext').click(function(evt) {
    var cookieKey = 'dir_ext_status';
    var status = $('.btn-dir-ext').attr('data-status'),
        opened_title = $('.btn-dir-ext').attr('data-opened-title'),
        closed_title = $('.btn-dir-ext').attr('data-closed-title');
    if (status == 'opened') {
        $('.btn-dir-ext').attr('data-status', 'closed');
        $('.btn-dir-ext').parents('.gap-hr').prev('.im_mainl').addClass('hide');
        $('.btn-dir-ext').find('img').attr('src', '/img/arrow-down.svg');
        $('.btn-dir-ext').find('span').text(closed_title);

        Cookies.set(cookieKey, 'closed', { expires: 1 });
    }else {
        $('.btn-dir-ext').attr('data-status', 'opened');
        $('.btn-dir-ext').parents('.gap-hr').prev('.im_mainl').removeClass('hide');
        $('.btn-dir-ext').find('img').attr('src', '/img/arrow-up.svg');
        $('.btn-dir-ext').find('span').text(opened_title);

        Cookies.set(cookieKey, 'opened', { expires: 1 });
    }
});

$('.expand-icon').click(function(evt) {
    var cookieKey = 'menu_ext_status';
    var status = $('.expand-icon').attr('data-status');

    if (!status || status == 'opened') {
        $('.expand-icon').attr('data-status', 'closed');
        $('.img_main').addClass('full');
        $('.expand-icon img').attr('src', '/img/beauty/arrow-right-circle.svg');
        $('.navbar-fixed-left').addClass('closed');

        Cookies.set(cookieKey, 'closed', { expires: 1 });
    }else {
        $('.expand-icon').attr('data-status', 'opened');
        $('.img_main').removeClass('full');
        $('.expand-icon img').attr('src', '/img/beauty/arrow-left-circle.svg');
        $('.navbar-fixed-left').removeClass('closed');

        Cookies.set(cookieKey, 'opened', { expires: 1 });
    }
});

/* MainBot扫描进度展示 */
if ($('.botstats').length > 0) {
    var didScan = false;
    var refreshBotStats = function() {
        $.ajax({
            url: '/site/botstats',
            method: 'GET',
            dataType: 'json'
        }).done(function(data) {
            if (data.code != 1) {
                console.warn('Bot stats获取失败', data.msg);
            }else {
                $('.botstats .progress-bar').css('width', data.percent + '%');
                if (data.percent >= 5) {
                    $('.botstats .progress-bar').text('扫描已完成 ' + data.percent + '%');
                }else {
                    $('.botstats .progress-bar').text('');
                }
                if (data.percent < 100) {
                    didScan = true;
                    $('.btnStartScan').prop('disabled', true);
                    $('.botstats').removeClass('hide');
                    $('.botstats .progress-bar').removeClass('progress-bar-success');
                    setTimeout(refreshBotStats, 10000);
                }else if (didScan) {
                    $('.botstats .progress-bar').addClass('progress-bar-success');
                    setTimeout(function() {
                        $('.botstats').addClass('hide');
                        $('.btnStartScan').removeAttr('disabled');
                        location.reload();
                    }, 4000);
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Bot stats获取失败，错误信息：' + errorThrown);
        });
    };

    refreshBotStats();
}

/* MainBot扫描启动 */
$('.btnStartScan').click(function() {
    $.ajax({
        url: '/site/startbot',
        method: 'POST',
        dataType: 'json'
    }).done(function(data) {
        if (data.code != 1) {
            alert('文件扫描开始失败：' + data.msg);
        }else {
            location.reload();
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        alert('文件扫描开始失败，错误信息：' + errorThrown);
        console.error('文件扫描开始失败，错误信息：' + errorThrown);
    });
});

/* tv remote control event handle */
//remote control key codes
var RC_LEFT = 37;
    RC_UP = 38;
    RC_RIGHT = 39;
    RC_DOWN = 40;
    RC_ENTER = 13;

    RC_PLAY_PC = 80,    //Key p
    RC_STOP_PC = 83,    //Key s

    //tv only
    RC_PLAY = 415,
    RC_STOP = 413;


//support a and button
var getNextSibling = function(el, way) {
    var next = null;
    var tagName = el.tagName.toLowerCase();
    if (way == 'right' && $(el).parents('.img_main').length > 0) {
        next = tagName == 'a' ? $(el).parent().next() : $(el).next();
    }else if (way == 'right' && $(el).parents('.navbar-fixed-left').length > 0) {
        next = $('.img_main li a').first();
        if (next.length > 0) {
            next.focus();
        }
    }else if (way == 'right' && $(el).parents('.pagination-con').length > 0) {
        next = $(el).parent().nextAll('li:not(.disabled)').not('.active').first();
    }else if (way == 'left' && $(el).parents('.pagination-con').length > 0) {
        next = $(el).parent().prevAll('li:not(.disabled)').not('.active').first();
    }else if (way == 'up' && $(el).parents('.pagination-con').length > 0) {
        next = $('.img_main .im_item').last();
    }else if (way == 'left' && $(el).parents('.img_main').length > 0) {
        next = tagName == 'a' ? $(el).parent().prev() : $(el).prev();

        if (next.length == 0) {
            next = $('.navbar-fixed-left li a').first();
            if (next.length > 0) {
                next.focus();
            }
        }
    }else if (way == 'up' && $(el).parents('.navbar-fixed-left').length > 0) {
        next = tagName == 'a' ? $(el).parent().prevAll('li:not(.menu-title)').first() : $(el).prev();
    }else if (way == 'up' && $(el).parents('.img_main').length > 0) {
        if ($(el).parent('li').length > 0) {
            next = $(el).parents('ul').prevAll('.nav').find('li').first();
            if (next.length == 0) {
                //next = $(el).parents('ul').prev('.im_mainl').find('.im_item').first();
                if ($(el).parents('.im_mainl').length > 0) {
                    next = $(el).parents('ul').first().prevAll('.im_item').first();
                }else {
                    next = $(el).parents('ul').first().prevAll('.im_mainl').find('.im_item').first();
                }
            }
        }else if ($(el).parent('.im_item').length > 0) {
            next = $(el).parent().prevAll('.im_item');       //6 images in one row
            if (next.length >= 6) {
                next = $(next.get(5));
            }else {
                next = null;
            }

            if (!next) {
                next = $(el).parent('.im_item').prevAll('ul').first().find('li').first();
                if (next.length == 0) {
                    next = $(el).parents('.im_mainl').prevAll('.nav').first().find('li.active');
                }
            }
        }

        if (next && next.length == 0) {
            next = $('.light-switcher').parent();
        }
    }else if (way == 'down' && $(el).parents('.navbar-fixed-left').length > 0) {
        next = tagName == 'a' ? $(el).parent().nextAll('li:not(.menu-title)').first() : $(el).next();
    }else if (way == 'down' && $(el).parents('.img_main').length > 0) {
        if ($(el).parent('li').length > 0) {
            next = $(el).parents('ul').nextAll('.nav').find('li').first();
            if (next.length == 0) {
                if ($(el).parents('.im_mainl').length > 0) {
                    next = $(el).parents('ul').first().nextAll('.im_item').first();
                }else {
                    next = $(el).parents('ul').first().nextAll('.im_mainl').find('.im_item').first();
                }
            }
        }else if ($(el).parent('.im_item').length > 0) {
            next = $(el).parent().nextAll('.im_item');       //6 images in one row
            if (next.length >= 6) {
                next = $(next.get(5));
            }else {
                next = null;
            }

            if (!next && $(el).parents('.im_mainl').nextAll('.nav').length > 0) {
                next = $(el).parents('.im_mainl').nextAll('.nav').first().find('li.active');
            }else if (!next && $('div.pagination-con').length > 0) {
                next = $('div.pagination-con').find('li.active').next();
            }
        }
    }else if (way == 'down' && $(el).hasClass('light-switcher')) {
        next = $('.im_item').first();
        console.log('light switcher');
    }

    if (next && next.length > 0) {
        next = next.find(tagName);
    }

    return next;
};

var keyPress = function(way) {
    var focusedEl = $(':focus');
    if (focusedEl.length == 0) {
        //get img el first
        focusedEl = $('.im_item a').first();

        //get nav item second
        if (focusedEl.length == 0) {
            focusedEl = $('.img_main li.active a').first();
            focusedEl.focus();
        }else {
            focusedEl.focus();
            return;
        }
    }

    if (focusedEl.length > 0) {
        var next = getNextSibling(focusedEl.get(0), way);
        if (next) {
            next.focus();
        }else {
            console.warn('No next sibling');
        }
    }else {
        console.warn('No focused element');
    }
};

//handle key press
$(document.body).on('keydown', function(e) {
    //console.log('Key pressed', e.keyCode);

    if (e.keyCode == RC_RIGHT) {
        keyPress('right');
    }else if (e.keyCode == RC_LEFT) {
        keyPress('left');
    }else if (e.keyCode == RC_UP) {
        keyPress('up');
    }else if (e.keyCode == RC_DOWN) {
        keyPress('down');
    }else if (e.keyCode == RC_PLAY || e.keyCode == RC_PLAY_PC) {
        //play images
        var fancybox = Fancybox.getInstance();
        if (fancybox) {
            var autoplay = fancybox.plugins.Slideshow.ref;
            autoplay.start();
        //}else {
            //Fancybox.fromSelector('[data-fancybox]');
        }
    }else if (e.keyCode == RC_STOP || e.keyCode == RC_STOP_PC) {
        //stop play images
        var fancybox = Fancybox.getInstance();
        if (fancybox) {
            var autoplay = fancybox.plugins.Slideshow.ref;
            autoplay.stop();
            if (e.keyCode == RC_STOP) {
                fancybox.close();
            }
        }
    }
});