/*  
******The author

******Tan
 */

if ($('#image_site').get(0)) {

    // 图片浏览
    $('[data-fancybox]').fancybox({
        toolbar: true,
        loop: false,
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
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            scrolltop.fadeIn();
        } else {
            scrolltop.fadeOut();
        }
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
    var lanpnum = 0;
    $('#image_site .lampJS').click(function () {
        if (lanpnum == 0) {
            $('#markdowncss').attr('href', '/css/github-markdown-dark.css');
            $(document.body).addClass('lampshow');
            $('#image_site .navbarJS').removeClass('navbar-default').addClass('navbar-inverse'); // 导航栏用bootstrap主题切换
            lanpnum = 1;
        } else if (lanpnum == 1) {
            $('#markdowncss').attr('href', '/css/github-markdown-light.css');
            $(document.body).removeClass('lampshow');
            $('#image_site .navbarJS').addClass('navbar-default').removeClass('navbar-inverse');
            lanpnum = 0;
        }

        return;
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

}
