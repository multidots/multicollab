(function ($) {
    'use strict';

    // Add temporary style tag to hide resolved tag color on load.
    $('html').prepend('<style id="loader_style">body mdspan{background: transparent !important;}.components-editor-notices__dismissible{display: none !important;</style>');

    // Document Ready.
    $(document).ready(function () {

        // on upload button click
        $('body').on( 'click', '#cf-upload-media', function(e){

            e.preventDefault();

            var button = $(this),
                custom_uploader = wp.media({
                    title: 'Insert/Upload file',
                    /*library : {
                        // uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
                        type : 'image'
                    },*/
                    button: {
                        text: 'Attach' // button label text
                    },
                    multiple: false
                }).on('select', function() { // it also has "open" and "close" events
                    var attachment = custom_uploader.state().get('selection').first().toJSON();

                    // attachment.id
                    // attachment.icon: "http://one.wordpress.test/wp-includes/images/media/document.png"
                    // mime: "application/pdf"
                    // filename: "10099953968904004.pdf"
                    // name: "10099953968904004"
                    // title: "10099953968904004"
                    // subtype: "pdf"
                    // type: "application" // type: "image"
                    // url: "http://one.wordpress.test/wp-content/uploads/2020/12/10099953968904004.pdf"
                    //

                    var attachmentHTML = '<div class="cf-attachment-item" data-id="'+ attachment.id +'">';
                    attachmentHTML += '<img class="cf-attachment-icon" src="' + attachment.icon + '" />';
                    attachmentHTML += '<span class="cf-attachment-title"><a href="' + attachment.url + '" target="_blank">' + attachment.title + '</a><a href="javascript:void(0)" class="cf-attachment-remove">REMOVE</a></span>';
                    attachmentHTML += '</div>';

                    $(button).parent().find('#cf-attachments').append(attachmentHTML);
                }).open();

        });

        $(document).on('click', '.cf-attachment-remove', function () {
            $(this).parents('.cf-attachment-item').remove();
        });

        // on remove button click
        $('body').on('click', '#cf-remove-media', function(e){

            e.preventDefault();

            var button = $(this);
            button.next().val(''); // emptying the hidden field
            button.hide().prev().html('Attach file');
        });

        // If thread focused via an activity center,
        // it is in lock mode, so clicking any para
        // would unlock it.
        $(document).on('click', '.block-editor-block-list__layout .wp-block', function (e) {

            if($('.cls-board-outer').hasClass('locked')) {

                // Reset Comments Float. This will reset the positions of all comments.
                $('#md-span-comments .cls-board-outer').css('opacity', '1');
                $('#md-span-comments .cls-board-outer').removeClass('focus');
                $('#md-span-comments .cls-board-outer').removeAttr('style');

                if( e.target.localName === 'mdspan' ) {
                    const dataid = $(e.target).attr('datatext');
                    // Trigger card click to focus.
                    $('#' + dataid).trigger('click');
                }
                $('.cls-board-outer').removeClass('locked');
            }
        });

        // Settings page tabs toggle.
        $(document).on('click', '.cf-tabs span', function () {
            const tabID = $(this).data('id');
            $('.cf-tab-inner').hide();
            $('#' + tabID).show();
        });

        // Save Settings.
        $('#cf-settings-form').on('submit', function (e){
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_settings',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function () {
                $('#cf-settings-form').find('[type="submit"]').removeClass('loading');
                $('#cf-notice .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf-notice .cf-success').slideUp(300);
                }, 3000);
            });
        });


        // Save show_avatar option in a localstorage.
        const data = {
            'action': 'cf_store_in_localstorage'
        };
        $.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            localStorage.setItem("showAvatars", response.showAvatars);
            localStorage.setItem("commentingPluginUrl", response.commentingPluginUrl);
        });

        // Focus comment popup on click.
        $(document).on('click', '#md-span-comments .cls-board-outer:not(.focus)', function (e) {

            var target = $(e.target), article;
            if( 'dashicons dashicons-trash' === target[0].className ) {
                return;
            }

            const _this = $(this);

            // Reset Comments Float.
            jQuery('#md-span-comments .cls-board-outer').css('opacity', '1');
            jQuery('#md-span-comments .cls-board-outer').removeClass('focus');
            jQuery('#md-span-comments .cls-board-outer').removeAttr('style');

            _this.addClass('focus');

            const selectedText = _this.attr('id');
            let topOfText = $('[datatext="' + selectedText + '"]').offset().top;

            $('#md-span-comments .cls-board-outer').css('opacity', '0.4');
            _this.css('opacity', '1');
            _this.offset({top: topOfText});

            var scrollTopClass = '';
            if( 0 !== $('.interface-interface-skeleton__content').length ) {
                // Latest WP Version
                scrollTopClass = '.interface-interface-skeleton__content';

            } else if( 0 !== $('.block-editor-editor-skeleton__content').length ) {
                // Latest WP Version
                scrollTopClass = '.block-editor-editor-skeleton__content';

            } else if( 0 !== $('.edit-post-layout__content').length ) {
                // Old WP Versions
                scrollTopClass = '.edit-post-layout__content';

            } else {
                // Default
                scrollTopClass = 'body';
            }

            topOfText = topOfText + $(scrollTopClass).scrollTop();

            $(scrollTopClass).animate({
                scrollTop: topOfText - 150
            }, 1000);

            $('[data-rich-text-format-boundary="true"]').removeAttr('data-rich-text-format-boundary');
            $('[datatext="' + selectedText + '"]').attr('data-rich-text-format-boundary', true);
        });

        // Scroll to the commented text and its popup from History Popup.
        $(document).on('click', '.user-commented-on', function (e) {
            $('#custom-history-popup, #history-toggle, .custom-buttons').toggleClass('active');
            e.preventDefault();

            const dataid = $(this).attr('data-id');


            // Trigger card click to focus.
            $('#' + dataid).trigger('click');

            // Focus and Lock the popup to prevent on hover issue.
            $('.cls-board-outer').removeClass('locked');
            $('#' + dataid).addClass('locked');

            $('[datatext="' + dataid + '"]').addClass('focus');
            setTimeout(function () {
                $('[datatext="' + dataid + '"]').removeClass('focus');
            }, 1500);

        });

        $('.shareCommentContainer textarea').on('click', function () {
            $(this).parent().addClass('hovered');
        });

        // History Toggle
        $(document).on('click', '#history-toggle', function () {
            $('#custom-history-popup, #history-toggle').toggleClass('active');
            $(this).parents('.custom-buttons').toggleClass('active');

            if ($('#custom-history-popup').hasClass('active')) {
                $('#custom-history-popup').addClass('loaded');

                const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();

                // Fetch comments from db.
                var data = {
                    'action': 'cf_comments_history',
                    'currentPostID': CurrentPostID,
                    'limit': 10,
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post(ajaxurl, data, function (response) {
                    $('#custom-history-popup-inner').html(response);
                });

            }
        });

        // Comments Toggle
        $(document).on('click', '#comments-toggle', function () {
            $('body').toggleClass('hide-comments');
            $(this).toggleClass('active'); /* If active, comments are hidden. */

            if( $(this).hasClass('active') ) {
                $('a', this).text('Show All Comments');
            } else {
                $('a', this).text('Hide All Comments');
            }

            // Hide Activity Center.
            $('#history-toggle').trigger('click');
        });

        // Hide Comments from Dropdown
        $(document).on('click', '[aria-label="More rich text controls"]', function () {
            const _this = $(this);
            setTimeout( function() {
                if( $('body').hasClass('hide-comments') ) {
                    $('button.components-dropdown-menu__menu-item .dashicons-admin-comments').parents('button').toggleClass('hide-me');
                }
            }, 10);
        });

        // Read More Comments
        $(document).on('click', '.readmoreComment, .readlessComment', function () {
            $(this).parents('.commentText').find('.readMoreSpan').toggleClass('active');
        });

    });

    // Load.
    $(window).load(function () {
        $('.cid_popup_hover').parents('.wp-block.editor-block-list__block.block-editor-block-list__block').addClass('parent_cid_popup_hover');

        // Handling Older WordPress Versions.
        // The function wp.data.select("core").getCurrentUser() is not
        // defined for v5.2.2, so getting data from PHP.
        try {
            wp.data.select("core").getCurrentUser().id;
        } catch (e) {

            // Fetch User details from AJAX.
            jQuery.post(ajaxurl, {
                'action': 'cf_get_user'
            }, function (user) {
                user = JSON.parse(user);
                localStorage.setItem("userID", user.id);
                localStorage.setItem("userName", user.name);
                localStorage.setItem("userURL", user.url);
            });
        }
    });

    $(document).on('click', '.markup', function () {
        $('.markup').removeClass('my-class');
        $(this).attr('data_name', true);
        $(this).addClass('my-class');
    });
    $(document).mouseup(function (e) {
        var container = $(".edit-popup-option");
        var markup = $(".markup");
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            markup.attr('data_name', false);
            markup.removeClass('my-class');
        }
    });
    $(document).on('click', '.dashicon.dashicons-ellipsis', function (e) {
        $(this).parents('.buttons-holder').toggleClass('is_active');
        e.stopPropagation();
    });

    $(document).on('click', function (e) {
        if ($(e.target).is(".dashicon.dashicons-ellipsis") === false) {
            $(".buttons-holder").removeClass('is_active');
        }
    });

    $(document).on('click', '.cf-tabs-main .cf-tabs .cf-tab-item', function (e) {
        var getTabID = $(this).attr('data-id');
        $(this).parent().addClass('cf-tab-active').siblings().removeClass('cf-tab-active');
        $('#'+getTabID).addClass('cf-tab-active').show().siblings().removeClass('cf-tab-active').hide();
    });

})(jQuery);
