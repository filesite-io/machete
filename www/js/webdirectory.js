/* for theme GoogleImage */
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

})();