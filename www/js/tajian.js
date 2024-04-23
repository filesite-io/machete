/* for theme TaJian */
(function() {

// 公用功能
var taJian = {
    debug: false,
    domain: 'https://tajian.tv', 
    apis: {
        addVideos: '/frontapi/addfav',  //添加视频
        register: '/frontapi/register',  //注册
        login: '/frontapi/login'  //登入
    }
};

//多用户支持
if (typeof(current_user_id) != 'undefined' && current_user_id) {
    for (var apiKey in taJian.apis) {
        taJian.apis[apiKey] = '/' + current_user_id + taJian.apis[apiKey];
    }
}

//--v2.0--
// 图片异步加载
if ($("img.lazy").get(0)) {
    $("img.lazy").lazyload({
        effect: "fadeIn",
        event: "scroll"
    });
}


// 返回顶部
// var scrolltop = $('.scroll_topJS');
// $(window).scroll(function () {
//     if ($(this).scrollTop() > 100) {
//         scrolltop.fadeIn();
//     } else {
//         scrolltop.fadeOut();
//     }
// });


if ($('.connectmeJS').get(0)) {
    // 点击打开右侧弹出框
    $('.connectmeJS').click(function () {
        $('.blank_coverJS, .right_sideboxJS').removeClass('elementNone');
        window.setTimeout(function () {
            $('.blank_coverJS').addClass('opacityshow');
            $('.right_sideboxJS').addClass('sideboxShow');

        }, 0);

    });

    // 点击关闭右侧弹出框
    $('.rtcloseJS').click(function () {
        $('.blank_coverJS').removeClass('opacityshow');
        $('.right_sideboxJS').removeClass('sideboxShow');
        window.setTimeout(function () {
            $('.blank_coverJS, .right_sideboxJS').addClass('elementNone');

        }, 500);
    });
}

// 打开移动端搜索框
if ($('.search_mob_JS').get(0)) {
    
    $('.search_mob_JS').click(function () {
        $('.blank_coverJS, .mobile_search_JS').removeClass('elementNone');
        window.setTimeout(function () {
            $('.blank_coverJS').addClass('opacityshow');
            $('.mobile_search_JS').addClass('sideboxShow');

        }, 0);
        $('#mobile_search .ms_input_js').focus();

    });

    
    $('.ms_close_JS').click(function () {
        $('.blank_coverJS').removeClass('opacityshow');
        $('.mobile_search_JS').removeClass('sideboxShow');
        window.setTimeout(function () {
            $('.blank_coverJS, .mobile_search_JS').addClass('elementNone');

        }, 500);
    });
}


// 搜索相关
$('#search_form .jsbtn').click(function (e) {

    e.preventDefault();
    if (!$('#search_form .input_sf_JS').val()) {
        
        return;
    }
    $('#search_form').submit();
});

// 添加视频
if ($('#add_video_form').get(0)) {

    // 添加视频表单处理
    $('#add_video_form .jsbtn').click(function (e) {
        e.preventDefault();
        let inputList = $('#add_video_form textarea');
        if (!inputList[0].value) {
            alert('请填写分享内容或网址！');
            return false;
        } else if (/https:\/\/[\w\.]+\/[\w]+/ig.test(inputList[0].value) == false) {
            alert('目前只支持抖音、快手、西瓜视频和Bilibili的分享网址哦！');
            return false;
        }
        
        let bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        let datas = {
            'content': inputList[0].value,
            'tag': $('#add_video_form select').val()
        }

        let apiUrl = taJian.debug ? taJian.domain + taJian.apis.addVideos : taJian.apis.addVideos;
        // console.log(apiUrl);
        publicAjax(apiUrl, 'POST', datas, function (data) {
            bt.prop('disabled', false);
            btText.text('提交');
            btLoading.addClass('elementNone');
            if (data.code == 1) {
                $(inputList[0]).val('');
                alert(data.msg || data.err);
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('提交');
            btLoading.removeClass('elementNone');
            console.log(jqXHR);

        });
    });
}

// form表单 
if ($('.g_form_js').get(0)) {

    $('.g_form_js .jsbtn').click(function (e) {

        e.preventDefault();

        let inputList = $('.g_form_js input');
        for (let i = 0; i < inputList.length; i++) {
            if (!inputList[i].value) {
                alert($(inputList[i]).attr('Warning'));
                return false;
                
            }
        }
        

        let bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('请求中...');

        
    });
}

function publicAjax(apiUrl, method, datas, callback, fail) {
    let self = this;

    let Options = {
        url: apiUrl,
        method: method,
        data: datas,
        dataType: 'json'
    };
    if (self.debug) {
        Options.crossDomain = true;
        Options.xhrFields = {
            withCredentials: true
        };
    }

    $.ajax(Options).done(function (data) {
        callback(data);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        
        fail(jqXHR, textStatus, errorThrown);
       
    });
};

})();
