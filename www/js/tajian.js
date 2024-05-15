/* for theme TaJian */
(function() {

// 公用功能
var taJian = {
    debug: false,
    domain: 'https://tajian.tv', 
    apis: {
        addVideos: '/frontapi/addfav',          //添加视频
        setNickname: '/frontapi/setnickname',   //设置昵称
        saveTags: '/frontapi/savetags',         //保存分类
        deleteTag: '/frontapi/deletetag',       //删除分类
        addTag: '/frontapi/addtag',             //添加分类
        updateFavsTag: '/frontapi/updatefavstag',               //修改视频的分类
        deleteFav: '/frontapi/deletefav',                       //删除收藏的视频
        sendSmsCode: '/frontapi/sendsmscode',   //发送短信验证码
        register: '/frontapi/createuser',       //注册
        login: '/frontapi/loginuser'            //登入
    }
};

var publicAjax = function(apiUrl, method, datas, callback, fail) {
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

    $.ajax(Options).done(function(data) {
        callback(data);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        
        fail(jqXHR, textStatus, errorThrown);
       
    });
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
        var inputList = $('#add_video_form textarea');
        var selectedTag = $('#add_video_form select').val();
        if (!inputList[0].value) {
            alert('请填写分享内容或网址！');
            return false;
        }else if (/https:\/\/[\w\.]+\/[\w]+/ig.test(inputList[0].value) == false) {
            alert('目前只支持抖音、快手、西瓜视频和Bilibili的分享网址哦！');
            return false;
        }else if (!selectedTag) {
            alert('请先选择分类！');
            return false;
        }
        
        let bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        let datas = {
            'content': inputList[0].value,
            'tag': selectedTag
        }

        let apiUrl = taJian.debug ? taJian.domain + taJian.apis.addVideos : taJian.apis.addVideos;
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
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
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

// 注册/登录等页面展示客服微信二维码切换按钮
if ($('.bt_kf_JS').get(0)) {
    var kf_hide_text = $('.bt_kf_JS').attr('data-hide'),
        kf_text = $('.bt_kf_JS').text();
    $('.bt_kf_JS').click(function(e) {
        if ($('.kf_wx_JS').hasClass('hide')) {
            $('.kf_wx_JS').removeClass('hide');
            $('.bt_kf_JS').text(kf_hide_text);
        }else {
            $('.kf_wx_JS').addClass('hide');
            $('.bt_kf_JS').text(kf_text);
        }
    });

    var win_width = $(window).width();
    if (win_width > 768 && $('.tajian_index').get(0)) {
        $('.bt_kf_JS').click().addClass('hide');
    }
}

//验证码发送
if ($('.bt_sms_JS').get(0)) {
    var timer_restore_smsBtn = null, timer_num = 60;
    var autoRestoreSmsBtn = function() {
        if (timer_restore_smsBtn) {clearTimeout(timer_restore_smsBtn);}
        if (timer_num <= 0) {
            $('.bt_sms_JS').removeAttr('disabled').text('发送验证码');
            timer_num = 60;
            return false;
        }

        timer_num --;
        $('.bt_sms_JS').prop('disabled', true).text('已发送(' + timer_num + ')');
        timer_restore_smsBtn = setTimeout(autoRestoreSmsBtn, 1000);
    };

    $('.bt_sms_JS').click(function(e) {
        var cellphone = $('input[name=username]').val();
        if (/^1[3-9][0-9]{9}$/.test(cellphone) == false) {
            alert('请填写正确的手机号码！');
            $('input[name=username]').focus();
            return false;
        }

        autoRestoreSmsBtn();
        
        //调用api发送验证码
        var cellphone = $('input[name=username]').val();
        var datas = {
            'phoneNum': cellphone,
            'action': $('#login_form').get(0) ? 'login' : 'register'
        };
        publicAjax(taJian.apis.sendSmsCode, 'POST', datas, function (data) {
            if (data.code == 0 && data.err) {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            alert('网络请求失败，请重试。');
        });
    });
}


// 注册
if ($('#register_form').get(0)) {
    $('#register_form .jsbtn').click(function(e) {
        e.preventDefault();

        var friends_code = $('input[name=friendscode]').val(),
            cellphone = $('input[name=username]').val(),
            smscode = $('input[name=smscode]').val();

        if (!friends_code || !cellphone || !smscode) {
            alert('请填写完整再注册！');
            return false;
        }else if (!friends_code) {
            alert('请填写邀请码：朋友的手机尾号 6 位（或加客服微信索要）！');
            return false;
        }else if (!cellphone) {
            alert('请填写手机号码！');
            return false;
        }else if (!smscode) {
            alert('请填写你手机收到的短信验证码！');
            return false;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'friendscode': friends_code,
            'username': cellphone,
            'smscode': smscode
        };
        publicAjax(taJian.apis.register, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            if (data.code == 1 && data.shareUrl) {
                btText.text('完成');
                alert(data.msg);
                setTimeout(function() {
                    location.href = data.shareUrl;
                }, 100);
            } else {
                btText.text('注册');
                bt.prop('disabled', false);
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('注册');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    });
}

// 登录
if ($('#login_form').get(0)) {
    $('#login_form .jsbtn').click(function(e) {
        e.preventDefault();

        var cellphone = $('input[name=username]').val(),
            smscode = $('input[name=smscode]').val();

        if (!cellphone || !smscode) {
            alert('请填写完整再登录！');
            return false;
        }else if (!cellphone) {
            alert('请填写手机号码！');
            return false;
        }else if (!smscode) {
            alert('请填写你手机收到的短信验证码！');
            return false;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'username': cellphone,
            'smscode': smscode
        };
        publicAjax(taJian.apis.login, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            if (data.code == 1 && data.shareUrl) {
                btText.text('完成');
                alert(data.msg);
                setTimeout(function() {
                    location.href = data.shareUrl;
                }, 100);
            } else {
                btText.text('登录');
                bt.prop('disabled', false);
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('登录');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    });
}


// 设置昵称
if ($('#nickname_form').get(0)) {
    $('#nickname_form .jsbtn').click(function(e) {
        e.preventDefault();

        var nickname = $('input[name=nickname]').val();

        if (!nickname) {
            alert('请填写 2 - 5 个汉字的昵称！');
            return false;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'nickname': nickname
        };
        publicAjax(taJian.apis.setNickname, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            bt.prop('disabled', false);
            btText.text('保存');
            if (data.code == 1) {
                alert(data.msg);
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('保存');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    });
}

// tag分类管理
if ($('#tags_form').get(0)) {
    $('#tags_form .tag-item img').click(function(e) {
        var action = $(this).attr('data-action');
        var current_el = $(this).parents('.tag-item'),
            next_el = current_el.next('.tag-item'),
            prev_el = current_el.prev('.tag-item');

        var current_tag = current_el.find('input').val(), another_tag;
        if (action == 'up' && prev_el.length > 0) {
            another_tag = prev_el.find('input').val();
            prev_el.find('input').val(current_tag);
        }else if (action == 'down' && next_el.length > 0) {
            another_tag = next_el.find('input').val();
            next_el.find('input').val(current_tag);
        }

        current_el.find('input').val(another_tag);
    });

    $('#tags_form .tag-item button.btn-danger').click(function(e) {
        var current_el = $(this).parents('.tag-item'),
            current_tag = current_el.find('input').val();

        if ($('#tags_form input').length == 1) {
            alert('请至少保留一个分类！');
            return false;
        }

        if (confirm('确定删除分类“' + current_tag + '”吗？')) {
            var datas = {
                'tag': current_tag
            };
            publicAjax(taJian.apis.deleteTag, 'POST', datas, function (data) {
                if (data.code == 1) {
                    current_el.remove();
                    location.reload();
                } else {
                    alert(data.err);
                }
            }, function (jqXHR, textStatus, errorThrown) {
                alert('网络请求失败，请重试。');
            });
        }
    });

    $('#tags_form .jsbtn').click(function(e) {
        e.preventDefault();

        var tag_els = $('#tags_form input'),
            tags = [];

        if (tag_els.length == 0) {
            alert('请至少保留一个分类！');
            return false;
        }

        var allTagsOk = true, tagName = '';
        for (var index=0;index<tag_els.length;index++) {
            tagName = tag_els[index].value;
            if (!tagName || tagName.length < 2 || tagName.length > 15 || isNaN(tagName) == false) {
                allTagsOk = false;
                break;
            }

            tags.push(tagName);
        }

        if (!allTagsOk) {
            alert('请按规则填写分类名称');
            return;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'tags': tags
        };
        publicAjax(taJian.apis.saveTags, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            bt.prop('disabled', false);
            btText.text('保存');
            if (data.code == 1) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('保存');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    });
}

// 添加tag分类
if ($('#tag_new_form').get(0)) {
    $('#tag_new_form .jsbtn').click(function(e) {
        e.preventDefault();

        var tag = $('#tag_new_form input[name=tag]').val();

        if (!tag || tag.length < 2 || tag.length > 15 || isNaN(tag) == false) {
            alert('请按规则填写分类名称！');
            return false;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'tag': tag
        };
        publicAjax(taJian.apis.addTag, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            bt.prop('disabled', false);
            btText.text('保存');
            if (data.code == 1) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('保存');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    });
}

// 视频管理
if ($('#favmg').get(0)) {
    $('#favmg .act_tags input[type=checkbox]').change(function(e) {
        var checkbox = e.target;
        var action = checkbox.checked ? 'add' : 'remove';

        var label = $(checkbox).parents('label'),
            video_id = label.attr('data-video-id'),
            filename = label.attr('data-filename'),
            tag = label.attr('data-tag');

        $(checkbox).prop('disabled', true);
        var datas = {
            'do': action,
            'id': video_id,
            'filename': filename,
            'tag': tag
        };
        publicAjax(taJian.apis.updateFavsTag, 'POST', datas, function (data) {
            $(checkbox).prop('disabled', false);
            if (data.code != 1) {
                $(checkbox).prop('checked', !checkbox.checked);
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            $(checkbox).prop('disabled', false);
            alert('网络请求失败，请重试。');
        });
    });

    $('#favmg .favmg-item .btn-del').click(function(e) {
        var target = e.target;

        if (target.tagName.toLowerCase() != 'button') {
            target = $(target).parents('.btn-del');
        }

        var video_id = $(target).attr('data-video-id'),
            filename = $(target).attr('data-filename');

        //console.log('clicked', video_id, filename);
        if (confirm('确定删除此视频吗？')) {
            $(target).prop('disabled', true);
            var datas = {
                'id': video_id,
                'filename': filename
            };
            publicAjax(taJian.apis.deleteFav, 'POST', datas, function (data) {
                $(target).prop('disabled', false);
                if (data.code == 1) {
                    location.reload();
                }else {
                    alert(data.err);
                }
            }, function (jqXHR, textStatus, errorThrown) {
                $(target).prop('disabled', false);
                alert('网络请求失败，请重试。');
            });
        }
    });
}

// 分享收藏夹
if ($('#share_form').get(0)) {
    var share_content = $('#share_form textarea').val();
    $('#share_form textarea').val(location.protocol + '//' + location.host + (location.port ? ':' + location.port : '') + share_content);

    var clipboard = new ClipboardJS('#share_form .jsbtn');

    clipboard.on('success', function(e) {
        e.clearSelection();
        $('#share_form .jsbtn').text('已复制');
        setTimeout(function() {
            $('#share_form .jsbtn').text('复制');
        }, 5000);
    });

    clipboard.on('error', function(e) {
        alert('复制失败，请手动选择后复制。');
    });
}

})();
