/* for theme TaJian */
(function() {

    //show modal
    $('.btn-open').click(function(evt) {
        var target_id = $(evt.target).parents('.btn-open').attr('href');
        if ($(evt.target).hasClass('btn-open')) {
            target_id = $(evt.target).attr('href');
        }
        $(target_id).css('display', 'block');
    });

    //hide modal
    $('.btn-close').click(function(evt) {
        $(evt.target).parents('.modal-mask').css('display', 'none');
    });
    $('.modal-mask').click(function(evt) {
        if ($(evt.target).hasClass('modal-mask')) {
            $(evt.target).css('display', 'none');
        }
    });

    $('#btn_addfav').click(function(e) {
        var share_content = $('input[name=share_content]').val(),
            select_tag = $('select[name=tag]').val();
        if (!share_content) {
            alert('请填写分享内容或网址！');
            return false;
        }else if (/https:\/\/[\w\.]+\/[\w]+/ig.test(share_content) == false) {
            alert('目前只支持抖音、快手、西瓜视频和Bilibili的分享网址哦！');
            return false;
        }

        var btn = this;
        $(btn).prop('disabled', true);

        var postData = {content: share_content};
        if (select_tag) {
            postData.tag = select_tag;
        }

        $.ajax({
            url: '/api/addfav',
            method: 'POST',
            dataType: 'JSON',
            data: postData
        }).done(function(data, textStatus, jqXHR) {
            $(btn).removeAttr('disabled');

            if (data.code == 1) {
                $('input[name=share_content]').val('');
                alert(data.msg || data.err);
            }else {
                alert('保存失败！' + data.err);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $(btn).removeAttr('disabled');
            alert('服务器异常了：' + errorThrown);
        });
    });

})();
