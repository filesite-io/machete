(function() {
    var showAlertMsg = function(msgType, msgContent) {
        var els_h1 = document.getElementsByTagName('h1');
        var alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${msgType}`;
        alertDiv.innerHTML = msgContent;
        if (els_h1.length > 0) {
            var h1 = els_h1[0];
            h1.parentNode.insertBefore(alertDiv, h1.nextSibling);
        }else {
            var els_header = document.getElementsByClassName('header');
            if (els_header.length > 0) {
                els_header[0].appendChild(alertDiv);
            }
        }

        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    };

    //get alert message from cookie
    var cookieKeys = {
        'info': 'alert_msg_info',
        'success': 'alert_msg_success',
        'warning': 'alert_msg_warning',
        'danger': 'alert_msg_danger'
    };

    var alert_msg = '';
    for (var key in cookieKeys) {
        try {
            alert_msg = decodeURIComponent( atob( Cookies.get(cookieKeys[key]) ) );
            if (alert_msg) {
                showAlertMsg(key, alert_msg);
                Cookies.remove(cookieKeys[key]);
            }
        }catch(e) {}
    }
})();
