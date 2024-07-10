/*  
******The author

******Tan
 */

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
    $("img.lazy").lazyload({
        effect: "fadeIn",
        event: "scroll"
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
            $('#image_site .navbarJS').removeClass('navbar-default').addClass('navbar-inverse'); // 导航栏用bootstrap主题切换
        } else if (lanpnum == 0) {
            $('#markdowncss').attr('href', '/css/github-markdown-light.css');
            $(document.body).removeClass('lampshow');
            $('#image_site .navbarJS').addClass('navbar-default').removeClass('navbar-inverse');
        }
    };

    var lanpnum = getLanpnumFromLocalstorage();
    if (lanpnum !== false) {
        toggleLampshow(lanpnum);
    }
    $('#image_site .lampJS').click(function () {
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
                    $(el).find('.im_img_title').before('<img src="' + data.url + '" class="bor_radius im_img">');
                }else {
                    console.error('目录封面图获取失败：%s', data.msg);
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
            correctLevel : QRCode.CorrectLevel.H
        });
    }

}
