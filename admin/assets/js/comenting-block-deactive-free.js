jQuery(function ($) {
    $(document).ready(function () {

        $(document).on('click', '[data-plugin="' + multicollab_plugin_path.plugin_path + '"] .deactivate a', function () {
            var snooze_free_deactivate_popup = getCookieDeactive("snooze_free_deactivate_popup");
            if( 'yes' !== snooze_free_deactivate_popup ) {
                jQuery('#cf_plugin_deacmodal').addClass('cf_plugin_deacmodal cf_plugin_freedeacmodal cf_visible');
                return false;
            }
            
        });

        $(document).on('click', '.modal-footer .btn-cancel', function () {
            jQuery('#cf_plugin_deacmodal').removeClass('cf_visible');
            jQuery('input[name="fs_deactive_free_plugin"]:checked').prop('checked', false); 
            jQuery('#snooze_option_checkbox:checked').prop('checked', false);
            jQuery('.snooze_option_section').hide();
            jQuery('.snooze_option_period').hide();
            jQuery('.modal-footer .btn-primary').removeClass('btn-active');
            jQuery('.modal-footer .btn-primary').text( 'Submit & Deactivate' );
        });

        $('#snooze_option_checkbox').change(function() {
            if(this.checked) {
                
                $('.snooze_option_period').show();
                $('.modal-footer .btn-primary').text( 'Snooze & Deactivate' );
            } else {
                $('.snooze_option_period').hide();
                $('.modal-footer .btn-primary').text( 'Submit & Deactivate' );
                
            }

        });

        $('input[name=fs_deactive_free_plugin]').change(function () {
            jQuery('.modal-footer .btn-primary').addClass('btn-active');
            
            var subscriptionOption = $('input[name="fs_deactive_free_plugin"]:checked').val();
            var fs_feedback_message = '';
            if( 'Missing a few important features.' === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_1' ).attr('disabled', false);
            } else if( "I found a better plugin." === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_2' ).attr('disabled', false);
            } else if( 'Something else.' === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_3' ).attr('disabled', false);
            } else {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
            }

            if( "It's a temporary deactivation - I'm troubleshooting an issue." === subscriptionOption ) {
                jQuery('.snooze_option_section').show();
            } else {
                jQuery('.snooze_option_section').hide();
            }

        });


        $(document).on('click', '.modal-footer .btn-active', function (event) {
            event.preventDefault();

            if ( $('.snooze_option_checkbox').is(':checked') ) {
                var cookieTime = jQuery('.snooze_option_period').val();
                setCookie( 'snooze_free_deactivate_popup', "yes", parseInt(cookieTime));
            }

            var subscriptionOption = $('input[name="fs_deactive_free_plugin"]:checked').val();
            var fs_feedback_message = '';
            if( 'Missing a few important features.' === subscriptionOption ) {
                fs_feedback_message = $('.fs_feedback_message_1').val();
            } else if( "I found a better plugin." === subscriptionOption ) {
                fs_feedback_message = $('.fs_feedback_message_2').val();
            } else if( 'Something else.' === subscriptionOption ) {
                fs_feedback_message = $('.fs_feedback_message_3').val();
            }
            
            const settingsData = {
                'action': 'cf_deactive_plugin_free',
                'subscription_option': subscriptionOption,
                'fs_feedback_message': fs_feedback_message
            };
            $.post(ajaxurl, settingsData, function (success) { // eslint-disable-line
                if ( 'success' === success ) {
                    location.reload();
                }
            });
        });

    });
});

function getCookieDeactive(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

//set cookies
function setCookie(name, value, minutes) {
    var expires = "";
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
