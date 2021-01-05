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
        var emailList = function( appendTo, data ) {
            var listItem = '';
            if( data.length > 0 ) {
                data.forEach( function( user ) {
                    listItem += `
                        <li data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                            <img src="${user.avatar}" alt="${user.display_name}" />
                            <div class="cf-user-info">
                                <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">(${user.role})</small></p>
                                <p class="cf-user-email">${user.user_email}</p>
                            </div>
                        </li>`
                } )

                var emailList = `
                    <div class="cf-mentioned-user-popup">
                        <ul class="cf-system-user-email-list">
                            ${listItem}
                        </ul>
                    </div>
                `;

                $( emailList ).insertAfter( appendTo )
            }
        }


        // Make matched text highlighted
        var makeMatchedTextHighlighted = function( term, markEmail, markName ) {

            var term       = term.substring(1);
            var $markEmail = $( markEmail );
            var $markName  = $( markName );

            if ( term ) {
                $markEmail.mark( term );
                $markName.mark( term );
            }
        }

        // Get Caret Position
        var ie = ( typeof document.selection != "undefined" && document.selection.type != "Control" ) && true;
        var w3 = ( typeof window.getSelection != "undefined" ) && true;
        var cursorPos = 0;
        var range = '';
        var getCaretPosition = function( element ) {
            var caretOffset = 0;
            if ( w3 ) {
                range             = window.getSelection().getRangeAt(0);
                var preCaretRange = range.cloneRange();
                if( typeof element == 'Node' ) {
                    preCaretRange.selectNodeContents(element);
                }
                preCaretRange.setEnd(range.endContainer, range.endOffset);
                caretOffset       = preCaretRange.toString().length;
        
            } else if ( ie ) {
                var textRange         = document.selection.createRange();
                var preCaretTextRange = document.body.createTextRange();
                preCaretTextRange.moveToElementText(element);
                preCaretTextRange.setEndPoint("EndToEnd", textRange);
                caretOffset           = preCaretTextRange.text.length;
            }
            return caretOffset;
        }

        // Insert Display Name
        var insertDisplayName = function( setRange, email, fullName, displayName ) {
            var anchor = document.createElement( 'a' );
            anchor.setAttribute( 'contenteditable', false );
            anchor.setAttribute( 'href', `mailto:${email}` )
            anchor.setAttribute( 'title', fullName );
            anchor.setAttribute( 'data-email', email );
            anchor.setAttribute( 'class', 'js-mentioned' );
        
            var anchorContent = document.createTextNode( displayName );
            anchor.appendChild( anchorContent )
            setRange.insertNode( anchor )
        }

        // Create @mentioning email features
        var createAutoEmailMention = function() {
            var el              = '';
            var currentBoardID   = '';
            var typedText        = ''
            var trackedStr       = ''
            var isEmail          = false;
            var createTextarea   = '';
            var appendIn         = '';
            var assignablePopup  = '';
            var editLink         = '';
            var keysToAvoid      = [ 'Enter', 'Tab', 'Shift', 'Control', 'Alt', 'CapsLock', 'Meta', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown' ];
            var currentPostID    = $( '#post_ID' ).val();
            var parentBoardClass = '.cls-board-outer';
            var editTextarea     = '.commentContainer .commentText textarea';
            var mood             = 'create';
            // Grab the current board ID
            $( document.body ).on( 'click', parentBoardClass, function(e) {
                el              = $( this ).attr( 'id' );
                currentBoardID  = `#${el}`;
                appendIn        = `${currentBoardID} .cf-mentioned-user-popup`;
                assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
                editLink        = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
                mood            = 'create';
                if( 'create' === mood ) {
                    createTextarea  = `${currentBoardID} .js-cf-share-comment`;
                }
            } )
            if( '' === el ) {
                $( document.body ).on( 'focus', '.shareCommentContainer', function(e) {
                    el              = $( this ).parents(parentBoardClass).attr( 'id' );
                    currentBoardID  = `#${el}`;
                    appendIn        = `${currentBoardID} .cf-mentioned-user-popup`;
                    assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
                    editLink        = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
                    mood            = 'create'
                    if( 'create' === mood ) {
                        createTextarea  = `${currentBoardID} .js-cf-share-comment`;
                    }
                } )
            }
            $( document.body ).on( 'focus keyup', '.js-cf-edit-comment', function(e) {
                el   = $( this ).parents( parentBoardClass ).attr( 'id' );
                mood = 'edit';
                if( 'edit' === mood ) {
                    createTextarea  = `${currentBoardID} .js-cf-edit-comment`;
                }
            } )
            // Remove emails list on edit link click
            $( document.body ).on( 'click', editLink, function(e) {
                $( appendIn ).remove();
                $( assignablePopup ).remove();
            } )
            // Triggering textarea keyup event
            $( document.body ).on( 'keyup', createTextarea, function(e) {
                var _self = $( createTextarea )
                typedText = _self.val()
                // If textarea is blank then remove email list
                if( '' == typedText ) {
                    $( appendIn ).remove();
                    $( assignablePopup ).remove();
                }

                // Handeling space. As if someone type space has no intension to write email.
                // So we make isEmail false and trackedStr to blank
                if( '' === e.key || ' ' === e.key ) {
                    isEmail = false;
                    trackedStr = '';
                }

                // Get current cursor position
                var el    = $( createTextarea ).get(0);
                cursorPos = getCaretPosition(el);
                if( '@' === e.key && true === e.shiftKey ) {
                    var prevCharOfEmailSymbol = typedText.substr( cursorPos - 2, 2 )

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
                                postID: currentPostID
                            },
                            beforeSend: function() {},
                            success: function( res ) {
                                $( appendIn ).remove(); // Remove previous DOM
                                $( assignablePopup ).remove(); // Remove previous DOM
                                $( assignablePopup ).remove();
                                var data = JSON.parse( res );
                                emailList( createTextarea, data )
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
                                    niddle: trackedStr,
                                    postID: currentPostID
                                },
                                success: function( res ) {
                                    $( appendIn ).remove(); // Removing previous DOM.
                                    $( assignablePopup ).remove(); // Removing previous DOM.
                                    var data = JSON.parse( res );
                                    emailList( createTextarea, data )
                                    makeMatchedTextHighlighted( trackedStr, '.cf-user-email', '.cf-user-display-name' )
                                }
                            })

                        }
                    }
                }
            } )
            // Append email in textarea.
            $( document.body ).on( 'click', '.cf-system-user-email-list li', function(e) {
                e.stopPropagation();
                var fullName    = $( this ).data( 'full-name' );
                var displayName = $( this ).data( 'display-name' );
                var email       = $( this ).data( 'email' );

                // Insert Display Name.
                insertDisplayName( range, email, fullName, displayName );

                var typedContent   = $( createTextarea ).html();
                var refinedContent = typedContent.replace( /(?<=@)\w+(?=\<)/gi, '' );
                $( createTextarea ).html( refinedContent );
                $( appendIn ).remove();
                $( assignablePopup ).remove();
                trackedStr = '';
            } )
        }
        createAutoEmailMention();

        // User Assign Function.
        var assignThisToUser = function() {
            let el                = '';
            var parentBoardClass  = '.cls-board-outer';
            let appendTo          = '.js-cf-share-comment';
            var mentionedEmail    = '.cf-system-user-email-list li';
            let checkBoxContainer = '.cf-assign-to';
            // Grab the current board ID.
            $( document.body ).on( 'focus', appendTo, function(e) {
                el = $( this ).parents( parentBoardClass ).attr( 'id' );
            } )

            // On Suggested Email Click.
            $( document.body ).on( 'click', mentionedEmail, function(e) {
                let thisEmail       = $( this ).data( 'email' );
                let thisUserId      = $( this ).data( 'user-id' );
                let thisDisplayName = $( this ).data( 'display-name' );
                let checkbox        = `
                <div class="cf-assign-to">
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i> Assign to ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
                </div>`;

                if( '' !== el ) {
                    if( $( `#${el} ${checkBoxContainer}` ).length ) {
                        $( `#${el} ${checkBoxContainer}` ).remove();
                    }
                    $( checkbox ).insertAfter( `#${el} ${appendTo}` );
                }

            } )

            // On Assignable Email Click.
            $( document.body ).on( 'click', '.cf-assignable-list li', function(e) {
                e.preventDefault();
                el = $( this ).parents( parentBoardClass ).attr( 'id' );
                let appendTo        = `#${el} .cf-assign-to`;
                let assignablePopup = `#${el} .cf-assignable-list-popup`;
                let thisEmail       = $( this ).data( 'email' );
                let thisUserId      = $( this ).data( 'user-id' );
                let thisDisplayName = $( this ).data( 'display-name' );
                let checkbox = `
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i> Assign to ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
                `;

                $( appendTo ).html('').append( checkbox );
                $( assignablePopup ).remove();
            } )
        }
        assignThisToUser();

        // Asignable Email List Template
        var assignalbeList = function( _self, data ) {
            var listItem = ''
            if( data.length > 0 ) {
                data.forEach( function( user ) {
                    listItem += `
                    <li data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}">
                        <img src="${user.avatar}" alt="${user.display_name}" />
                        <div class="cf-user-info">
                            <p class="cf-user-display-name">${user.display_name}</p>
                            <p class="cf-user-email">${user.user_email}</p>
                        </div>
                    </li>
                    `
                } )
            }
            var assignListTemplate = `
                <div class="cf-assignable-list-popup">
                    <ul class="cf-assignable-list">
                        ${listItem}
                    </li>
                </div>
            `;

            $( assignListTemplate ).insertAfter( _self )
        }
        // Show Assiganable Email List
        var showAssingableEmailList = function() {
            var triggerLink = '.js-cf-show-assign-list';
            var textarea    = '';
            var appendTo    = '';
            var parentBoardClass  = '.cls-board-outer';
            $( document.body ).on( 'click', triggerLink, function(e) {
                e.preventDefault();
                var el      = $( this ).parents( parentBoardClass ).attr( 'id' );
                textarea    = `#${el} .js-cf-share-comment`;
                appendTo    = `#${el} .shareCommentContainer .cf-assign-to`;
                var content = $( textarea ).html();

                $( this ).removeClass( 'js-cf-show-assign-list' ).addClass( 'js-cf-hide-assign-list' );
                // Send Ajax Request
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'cf_get_assignable_user_list',
                        content: content
                    },
                    beforeSend: function() {},
                    success: function( res ) {
                        var data = JSON.parse( res );
                        assignalbeList( appendTo, data );
                    }
                })
            } )
        }
        showAssingableEmailList();

        // Hide Assignable Email list
        var hideAssignableEmailList = function() {
            var el                  = '';
            var triggerLink         = '.js-cf-hide-assign-list';
            var parentBoardClass    = '.cls-board-outer';
            var assignableListPopup = '';
            $( document.body ).on( 'click', triggerLink, function(e) {
                e.preventDefault();
                el                  = $( this ).parents( parentBoardClass ).attr( 'id' );
                assignableListPopup = `#${el} .cf-assignable-list-popup`;
                $( assignableListPopup ).remove();
                $( this ).removeClass( 'js-cf-hide-assign-list' ).addClass( 'js-cf-show-assign-list' );
            } )
        }
        hideAssignableEmailList();

        // Open comment box when user redirect from email
        var openComment = function() {
            var commentedId = adminLocalizer.comment_id;
            $( window ).load( function() {
                setTimeout( function() {
                    $( `#${commentedId} .js-edit-comment` ).trigger( 'click' );
                    $( `#${commentedId}` ).addClass( 'comment-flash' );
                }, 2000 )
                setTimeout( function() {
                    $( `#${commentedId}` ).removeClass( 'comment-flash' );
                }, 4000 )
            } )
        }
        openComment();

        // Dealing with content-editable console issue.
        var manageContentEditableConsoleIssue = function() {
            console.error = (function() {
                var error = console.error
                return function(exception) {
                    if ((exception + '').indexOf('Warning: A component is `contentEditable`') != 0) {
                        error.apply(console, arguments)
                    }
                }
            })()
        }
        manageContentEditableConsoleIssue();

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
            let userID = wp.data.select("core").getCurrentUser().id;
        } catch (e) {
            // Fetch User details from AJAX.
            jQuery.post(ajaxurl, {
                'action': 'cf_get_user'
            }, function (user) {
                user = JSON.parse(user);
                localStorage.setItem("userID", user.id);
                localStorage.setItem("userName", user.name);
                localStorage.setItem("userRole", user.role);
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
