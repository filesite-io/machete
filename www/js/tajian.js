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
        createNewFav: '/frontapi/createdir',                    //创建新的收藏夹
        shareFav2Friend: '/frontapi/sharedir',                  //共享收藏夹给朋友
        deleteSharedFav: '/frontapi/delsharedir',               //取消共享收藏夹给朋友

        adPostback: '/frontapi/adpostback',                     //广告跟踪回传
        cookiesAccept: '/frontapi/acceptcookies',               //同意或不同意cookies协议

        sendSmsCode: '/frontapi/sendsmscode',   //发送短信验证码
        register: '/frontapi/createuser',       //注册
        login: '/frontapi/loginuser'            //登入
    }
};

var publicAjax = function(apiUrl, method, datas, callback, fail) {
    var self = this;

    var Options = {
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

/*
//TODO: 待实现
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
        //TODO: do something
        return;
    }
    $('#search_form').submit();
});
*/

// 添加视频
if ($('#add_video_form').get(0)) {
    var handle_add_fav = function (e) {
        e.preventDefault();
        var inputList = $('#add_video_form textarea');
        var selectedTag = $('#add_video_form select').val();
        if (!inputList[0].value) {
            alert('请填写分享内容或网址！');
            return false;
        }else if (!selectedTag) {
            alert('请先选择分类！');
            return false;
        }
        
        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'content': inputList[0].value,
            'tag': selectedTag
        }

        //var apiUrl = taJian.debug ? taJian.domain + taJian.apis.addVideos : taJian.apis.addVideos;
        var apiUrl = taJian.apis.addVideos;
        publicAjax(apiUrl, 'POST', datas, function (data) {
            bt.prop('disabled', false);
            btText.text('提交');
            btLoading.addClass('elementNone');
            if (data.code == 1) {
                if (data.err) {
                    alert(data.err);
                }else {
                    $(inputList[0]).val('');
                }
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('提交');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    };

    $('#add_video_form .jsbtn').click(handle_add_fav);
    $('#add_video_form').submit(handle_add_fav);
}

// 注册帮用户填默认邀请码
if ($('.bt_kf_JS').get(0)) {
    var kf_default_code = $('.bt_kf_JS').attr('data-default-code');
    $('.bt_kf_JS').click(function(e) {
        $('input[name=friendscode]').val(kf_default_code);
    });
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
            }else {
                $('.sms_tip_JS').text(data.msg);
                if (typeof(data.autofill) != 'undefined' && data.autofill) {
                    $('input[name=smscode]').val(data.autofill);
                }
            }
        }, function (jqXHR, textStatus, errorThrown) {
            alert('网络请求失败，请重试。');
        });
    });
}


// 注册
if ($('#register_form').get(0)) {
    var handle_register = function(e) {
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
                //alert(data.msg);
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
    };

    $('#register_form .jsbtn').click(handle_register);
    $('#register_form').submit(handle_register);
}

// 登录
if ($('#login_form').get(0)) {
    var handle_login = function(e) {
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
    };

    $('#login_form .jsbtn').click(handle_login);
    $('#login_form').submit(handle_login);
}


// 设置昵称
if ($('#nickname_form').get(0)) {
    var handle_save_nickname = function(e) {
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
                //alert(data.msg);
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
    };

    $('#nickname_form .jsbtn').click(handle_save_nickname);
    $('#nickname_form').submit(handle_save_nickname);
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

    var handle_save_tags = function(e) {
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
                //alert(data.msg);
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
    };

    $('#tags_form .jsbtn').click(handle_save_tags);
    $('#tags_form').submit(handle_save_tags);
}

// 添加tag分类
if ($('#tag_new_form').get(0)) {
    var handle_new_tag = function(e) {
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
                //alert(data.msg);
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
    };

    $('#tag_new_form .jsbtn').click(handle_new_tag);
    $('#tag_new_form').submit(handle_new_tag);
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

// 创建新收藏夹账号
if ($('#dir_new_form').get(0)) {
    var handle_new_dir = function(e) {
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
        publicAjax(taJian.apis.createNewFav, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            bt.prop('disabled', false);
            btText.text('保存');
            if (data.code == 1) {
                //alert(data.msg);
                location.href = '/' + current_user_id + '/my/dirs';
            } else {
                alert(data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            bt.prop('disabled', false);
            btText.text('保存');
            btLoading.addClass('elementNone');
            alert('网络请求失败，请重试。');
        });
    };

    $('#dir_new_form .jsbtn').click(handle_new_dir);
    $('#dir_new_form').submit(handle_new_dir);
}

// 共享收藏夹账号
if ($('#share_dir_form').get(0)) {
    var handle_share_dir = function(e) {
        e.preventDefault();

        var cellphone = $('input[name=cellphone]').val(),
            favName = $('select[name=dir]').val();

        if (!cellphone) {
            alert('请填写需要共享的朋友手机号码！');
            return false;
        }else if (!favName) {
            alert('请选择需要共享的账号！');
            return false;
        }

        var bt = $(this), btLoading = bt.children('.bt_class_JS'), btText = bt.children('.bt_text_JS');
        btLoading.removeClass('elementNone');
        bt.prop('disabled', true);
        btText.text('提交中...');

        var datas = {
            'cellphone': cellphone,
            'dir': favName
        };
        publicAjax(taJian.apis.shareFav2Friend, 'POST', datas, function (data) {
            btLoading.addClass('elementNone');
            bt.prop('disabled', false);
            btText.text('保存');
            if (data.code == 1) {
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
    };

    $('#share_dir_form .jsbtn').click(handle_share_dir);
    $('#share_dir_form').submit(handle_share_dir);

    //取消共享
    var handle_delete_share = function(e) {
        var btn = $(e.target);
        if (e.target.tagName.toLowerCase() != 'button') {
            btn = btn.parents('button');
        }
        var cellphone = btn.attr('data-cellphone'),
            dir = btn.attr('data-dir'),
            favName = btn.text().replace(/\s/g, '');

        if (!cellphone || !dir) {
            alert('系统异常，请刷新网页！');
            return false;
        }

        if (confirm('确定取消账号共享【' + favName + '】吗？')) {
            btn.prop('disabled', true);
            var datas = {
                'cellphone': cellphone,
                'dir': dir
            };
            publicAjax(taJian.apis.deleteSharedFav, 'POST', datas, function (data) {
                btn.prop('disabled', false);
                if (data.code == 1) {
                    location.reload();
                } else {
                    alert(data.err);
                }
            }, function (jqXHR, textStatus, errorThrown) {
                btn.prop('disabled', false);
                alert('网络请求失败，请重试。');
            });
        }
    };
    $('.my_share_dirs .btn-del').click(handle_delete_share);
}

// 广告跟踪回传
if ($('.ad_postback_JS').get(0)) {
    var datas = {};
    publicAjax(taJian.apis.adPostback, 'POST', datas, function (data) {
        if (data.code != 1) {
            console.error('Ad postback error', data.err);
        }
    }, function (jqXHR, textStatus, errorThrown) {
        console.error('Ad postback exception', errorThrown);
    });
}

if ($('.cookie-banner').get(0)) {
    $('.cookie-banner .button').click(function(e) {
        $('.cookie-banner').addClass('elementNone');
        var btn = e.target;
        var datas = {
            'accept': $(btn).hasClass('button-primary') ? 'yes' : 'no'
        };
        publicAjax(taJian.apis.cookiesAccept, 'POST', datas, function (data) {
            if (data.code != 1) {
                console.error('Cookie accept error', data.err);
            }
        }, function (jqXHR, textStatus, errorThrown) {
            console.error('Cookie accept exception', errorThrown);
        });
    });
}

})();
