/**
 * General function to be called for required JS actions.
 */

var $ = jQuery;
$(document).ready(function () {
    //Hide free guide notification popup
    $(document).on('click', '.cf-pluginpop-close', function () {
        
        var popupName = $(this).attr('data-popup-name');
        setCookieGeneral('banner_' + popupName, "yes", 60 * 24 * 7);
        $('.' + popupName).hide();
    });

});

//set cookies
function setCookieGeneral(name, value, minutes) {
    var expires = "";
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

