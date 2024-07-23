jQuery(function ($) {
    $(document).ready(function () {

        $(document).on('click', '[data-plugin="' + multicollab_plugin_path.plugin_path + '"] span.deactivate a', function () {
            var snooze_free_deactivate_popup = getCookieName("snooze_free_deactivate_popup");
            if( 'yes' !== snooze_free_deactivate_popup ) {
                jQuery('.modal-dialog input:radio').prop('checked', false);
                jQuery('#cf_plugin_deacmodal').addClass('cf_plugin_deacmodal cf_visible');
                return false;
            }
            
        });

        
        $('#step-1 input[type=radio][name=fs_deactive_free_plugin_step1]').change(function() {
            if (this.value == 'yes') {
                const settingsData = {
                    'action': 'cf_deactive_plugin_free',
                    'first_option_value': 'yes',
                };
                $.post(ajaxurl, settingsData, function (success) { // eslint-disable-line
                    if ( 'success' === success ) {
                        location.reload();
                    }
                });
            }
            else if (this.value == 'no') {
                document.querySelector( '.modal-dialog #step-1' ).style.display = 'none';
                document.querySelector( '.modal-dialog #step-2' ).style.display = 'block';
                document.querySelector( '.modal-dialog .modal-footer' ).style.display = 'block';
                document.querySelector( '.modal-dialog .modal-header .modal-title' ).innerHTML = 'What was the primary reason for deactivating Multicollab?';
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

            document.querySelector( '.modal-dialog #step-1' ).style.display = 'block';
            document.querySelector( '.modal-dialog #step-2' ).style.display = 'none';
            document.querySelector( '.modal-dialog #step-3' ).style.display = 'none';
            document.querySelector( '.modal-dialog .modal-footer' ).style.display = 'none';

            jQuery('.modal-dialog input:radio').prop('checked', false);

            jQuery( '.fs_feedback_message_1' ).hide();
            jQuery( '.fs_feedback_message_2' ).hide();
            jQuery( '.fs_feedback_message_3' ).hide();
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
            //jQuery('.modal-footer .btn-primary').addClass('btn-active');
            
            var subscriptionOption = $('input[name="fs_deactive_free_plugin"]:checked').val();
            var fs_feedback_message = '';
            if( 'Lack of essential features' === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_1' ).attr('disabled', false);
                jQuery( '.fs_feedback_message_1' ).show();
                jQuery( '.fs_feedback_message_2' ).hide();
                jQuery( '.fs_feedback_message_3' ).hide();
                jQuery('.modal-footer .btn-primary').removeClass('btn-active');
            } else if( "Found a better plugin" === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_2' ).attr('disabled', false);
                jQuery( '.fs_feedback_message_2' ).show();
                jQuery( '.fs_feedback_message_1' ).hide();
                jQuery( '.fs_feedback_message_3' ).hide();
                jQuery('.modal-footer .btn-primary').removeClass('btn-active');
            } else if( 'Something else' === subscriptionOption ) {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_3' ).attr('disabled', false);
                jQuery( '.fs_feedback_message_3' ).show();
                jQuery( '.fs_feedback_message_1' ).hide();
                jQuery( '.fs_feedback_message_2' ).hide();
                jQuery('.modal-footer .btn-primary').removeClass('btn-active');
            } else {
                jQuery( '.feedback_message' ).val('');
                jQuery( '.feedback_message' ).attr('disabled', true);
                jQuery( '.fs_feedback_message_1' ).hide();
                jQuery( '.fs_feedback_message_2' ).hide();
                jQuery( '.fs_feedback_message_3' ).hide();

                jQuery('.modal-footer .btn-primary').addClass('btn-active');
            }

            if( "It's temporary deactivation - troubleshooting an issue" === subscriptionOption ) {
                jQuery('.snooze_option_section').show();
            } else {
                jQuery('.snooze_option_section').hide();
            }

        });

        $('.feedback_message').on('keyup keypress', function(e) {
            if($(this).val().length >0) {
                jQuery('.modal-footer .btn-primary').addClass('btn-active');
            } else {
                jQuery('.modal-footer .btn-primary').removeClass('btn-active');
            }
        
        });


        $(document).on('click', '.free_plugin_deactivate_step3', function (event) {
            
            var free_plugin_deactivate_step3 = jQuery(this).attr('data-value');
            if( free_plugin_deactivate_step3 ) {
                var subscriptionOption = $('input[name="fs_deactive_free_plugin"]:checked').val();
                var fs_feedback_message = '';
                if( 'Lack of essential features' === subscriptionOption ) {
                    fs_feedback_message = $('.fs_feedback_message_1').val();
                } else if( "Found a better plugin" === subscriptionOption ) {
                    fs_feedback_message = $('.fs_feedback_message_2').val();
                } else if( 'Something else' === subscriptionOption ) {
                    fs_feedback_message = $('.fs_feedback_message_3').val();
                }

                if ( $('.snooze_option_checkbox').is(':checked') ) {
                    var cookieTime = jQuery('.snooze_option_period').val();
                    setCookieName( 'snooze_free_deactivate_popup', "yes", parseInt(cookieTime));
                }
                
                const settingsData = {
                    'action': 'cf_deactive_plugin_free',
                    'subscription_option': subscriptionOption,
                    'fs_feedback_message': fs_feedback_message,
                    'free_plugin_deactivate_step3': free_plugin_deactivate_step3,
                };
                $.post(ajaxurl, settingsData, function (success) { // eslint-disable-line
                    if ( 'success' === success ) {
                        location.reload();
                    }
                });


            }

            
            
        });

        $(document).on('click', '.modal-footer .btn-active', function (event) {
            event.preventDefault();

            document.querySelector( '.modal-dialog #step-3' ).style.display = 'block';

            document.querySelector( '.modal-dialog #step-1' ).style.display = 'none';
            document.querySelector( '.modal-dialog #step-2' ).style.display = 'none';
            document.querySelector( '.modal-dialog .modal-footer' ).style.display = 'none';
            document.querySelector( '.modal-dialog .modal-header .modal-title' ).innerHTML = 'How would you rate your overall experience with Multicollab?';
        });

    });
});

function getCookieName(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function setCookieName(name, value, minutes) {
    var expires = "";
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}