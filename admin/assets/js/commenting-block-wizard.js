var $ = jQuery;

$(document).ready(function () {

    var result = bowser.getParser(window.navigator.userAgent);
    jQuery('.cf_browser_name_version').val( result.parsedResult.browser.name + ' ' + result.parsedResult.browser.version );

    $(document.body).on('click', '.tab-pane .btn-primary', function () {
        var curruntButton = jQuery(this).closest('.tab-pane').attr('id');
        var nextButton = 'step' + (parseInt(curruntButton.slice(4, 5)) + 1); // Masteringjs.io

        if ('step4' === curruntButton) {

            const freeWizardData = {
                'action': 'cf_free_plugin_wizard_submit',
                'subscribe_email': jQuery('.last_step_email_subscription').val(),
                'opt_in': jQuery('.count_me_in_free').is(":checked"),
                'broser_name': jQuery('.cf_browser_name_version').val(),
                'country': jQuery('.cf_country_name').val(),
            };

            $.ajax({
                url: ajaxurl,
                data: freeWizardData,
                success: function ( success ) {
                    var curruntUrl = location.protocol + '//' + location.host + location.pathname + '?page=editorial-comments';
                    window.location.href = curruntUrl;
                    return false;
                },
                beforeSend: function () {
                    document.body.classList.add('cf_settings_loader');
                },
                complete: function () {
                    document.body.classList.remove('cf_settings_loader');
                }
            });


        } else {
            jQuery('#' + curruntButton).hide();
            jQuery('#' + nextButton).show();
        }

        if (jQuery('.count_me_in_free').is(":checked")) {
            jQuery('.last_step_description').hide();
            jQuery('.last_step_email_subscription').hide();
        } else {
            jQuery('.last_step_description').show();
            jQuery('.last_step_email_subscription').show();
        }

    });

});

// Added style to admin html /@Minal Diwan Version 3.2
function javascriptLoad() {
    const bodyHassetup_wizard = document.body.classList.contains('admin_page_multicollab_setup_wizard');
    const el = document.querySelector('html');
    if (bodyHassetup_wizard) {
        el.style.paddingTop='0px';
    } else if (window.innerWidth <= 600) {
        el.style.paddingTop = '0px';
    } else {
        el.style.paddingTop='32px';
    }
  }

document.addEventListener("DOMContentLoaded", javascriptLoad);
