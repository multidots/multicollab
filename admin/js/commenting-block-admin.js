// const { before } = require("lodash");

(function ($) {
    'use strict';
    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    // Add temporary style tag to hide resolved tag color on load.
    $('html').prepend('<style id="loader_style">body mdspan{background: transparent !important;}.components-editor-notices__dismissible{display: none !important;</style>');

    // Ready.
    $(document).ready(function () {

        // Save show_avatar option in a localstorage.
        var data = {
            'action': 'cf_store_in_localstorage'
        };
        $.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            localStorage.setItem("showAvatars", response.showAvatars);
            localStorage.setItem("commentingPluginUrl", response.commentingPluginUrl);
        });

        // Focus comment popup on click.
        $(document).on('click', '#md-span-comments .cls-board-outer:not(.focus)', function () {

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
            if( 0 !== $('.block-editor-editor-skeleton__content').length ) {
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
        $(document).on('click', '.user-comented-on', function (e) {
            $('#custom-history-popup, #history-toggle').toggleClass('active');
            e.preventDefault();

            const dataid = $(this).attr('data-id');

            if (0 !== $("#" + dataid).length) {
                const topOfPopup = $("#" + dataid).offset().top
                $('.edit-post-layout__content').animate({
                    scrollTop: topOfPopup
                }, 1000);
            }

            $('#' + dataid + ', [datatext="' + dataid + '"]').addClass('focus');
            setTimeout(function () {
                $('[datatext="' + dataid + '"]').removeClass('focus');
            }, 1500);

        });

        $('.shareCommentContainer textarea').on('click', function () {
            $(this).parent().addClass('hovered');
        });

        // Email List Template Function
        var emailList = function( _self, data ) {
            var listItem = '';
            if( data.length > 0 ) {
                data.forEach( function( email ) {
                    listItem += `<li>${email.user_email}</li>`
                } )

                var emailList = `
                    <ul class="cf-system-user-email-list">
                        ${listItem}
                    </ul>
                `;

                $( emailList ).insertAfter( _self )
            }
        }

        // @mentioning email features
        var createAutoEmailMention = function() {
            var createTextarea = '.shareCommentContainer textarea';
            var typedText = ''
            var trackedStr = ''
            var isEmail = false;
            var appendIn = '.cf-system-user-email-list'
            var keysToAvoid = [ 'Enter', 'Tab', 'Shift', 'Control', 'Alt', 'CapsLock', 'Meta', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown' ];
            $( document.body ).on( 'keyup', createTextarea, function(e) {
                var _self = $( this )
                typedText = _self.val()

                // If textarea is blank then remove email list
                if( '' == typedText ) {
                    $( appendIn ).remove();
                }

                // Handeling space. As if someone type space has no intension to write email.
                // So we make isEmail false and trackedStr to blank
                if( '' === e.key || ' ' === e.key ) {
                    isEmail = false;
                    trackedStr = '';
                }

                var cursorPos = _self.prop( 'selectionStart' );
                if( '@' === e.key && true === e.shiftKey ) {
                    var prevCharOfEmailSymbol = typedText.substr( cursorPos - 2 )

                    if(
                        ' @' == prevCharOfEmailSymbol
                        || '' == prevCharOfEmailSymbol
                        || '@' == prevCharOfEmailSymbol
                    ) { // meaning @ is typed at the begining or as independent
                        // fetch the all email list
                        isEmail = true
                        $.ajax({
                            url: ajaxurl,
                            type: 'post',
                            data: {
                                action: 'cf_get_user_email_list',
                            },
                            beforeSend: function() {},
                            success: function( res ) {
                                $( appendIn ).remove(); // Remove previous DOM
                                var data = JSON.parse( res );
                                emailList( _self, data )
                            }
                        })
                    } else { // Meaning @ is typed inside an email address
                        // do nothing
                    }
                }

                if( true == isEmail ) {
                    var checkKeys = function( key ) {
                        if( key === e.key ) {
                            return true;
                        }
                        return false;
                    }
                    if( ! keysToAvoid.find( checkKeys ) ) {
                        if( 'Backspace' === e.key ) {
                            trackedStr = trackedStr.slice( 0, -1 )
                        } else {
                            trackedStr += e.key
                        }

                        if( '@' !== trackedStr ) {
                            // Sending Ajax Call to get the matched email list(s)
                            $.ajax({
                                url: ajaxurl,
                                type: 'post',
                                data: {
                                    action: 'cf_get_matched_user_email_list',
                                    niddle: trackedStr
                                },
                                beforeSend: function() {},
                                success: function( res ) {
                                    $( appendIn ).remove(); // Removing previous DOM
                                    var data = JSON.parse( res );
                                    emailList( _self, data )
                                }
                            })
                        }
                    }


                }
            } )
            // Append email in textarea
            $( document.body ).on( 'click', '.cf-system-user-email-list li', function(e) {
                var cursorPos = $( createTextarea ).prop( 'selectionStart' );
                e.stopPropagation();
                var email            = $( this ).text();
                var trackedStrLength = trackedStr.length - 1; // Calculating length without @
                if( trackedStrLength > 0 ) {
                    email = email.slice( trackedStrLength );
                }
                var textBeforeEmail  = typedText.substr( 0, cursorPos );
                var textAfterEmail   = typedText.substr( cursorPos, cursorPos.length )
                var refinedContent   = `${textBeforeEmail}${email}${textAfterEmail} `;
                $( createTextarea ).val( refinedContent );
                $( appendIn ).remove();
                trackedStr = '';
            } )
        }
        createAutoEmailMention();

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

})(jQuery);
