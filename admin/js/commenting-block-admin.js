/**
 * Main function to be called for required JS actions.
 */
(function ($) {
    'use strict';
    /**
     * All of the code for the admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     */

    // Add temporary style tag to hide resolved tag color on load.
    $('html').prepend('<style id="loader_style">body mdspan{background: transparent !important;}.components-editor-notices__dismissible{display: none !important;</style>');

    // On Document Ready Event.
    $( document ).ready(function () {

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
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
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
        $.post(ajaxurl, data, function (response) { // eslint-disable-line
            response = JSON.parse(response);
            localStorage.setItem("showAvatars", response.showAvatars);
            localStorage.setItem("commentingPluginUrl", response.commentingPluginUrl);
        });

        // Focus comment popup on click.
        $(document).on('click', '#md-span-comments .cls-board-outer:not(.focus)', function (e) {

            // Exclude focus on specific elements.
            var target = $(e.target);
            if( 'dashicons dashicons-trash' === target[0].className
                || 'resolve-label' === target[0].className
                || 'resolve-cb' === target[0].className
            ) {
                return;
            }

            const _this = $(this);

            // Reset Comments Float.
            $('#md-span-comments .cls-board-outer').removeAttr('style');
            $('#md-span-comments .cls-board-outer').removeClass('focus');

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

        // Email List Template Function.
        var emailList = function( appendTo, data ) {
            var listItem = '';
            if( data.length > 0 ) {
                data.forEach( function( user ) {
                    listItem += `
                        <li tabindex="0" role="option" data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                            <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                            <div class="cf-user-info">
                                <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">(${user.role})</small></p>
                            </div>
                        </li>`;
                } )

                var emailList = `
                    <div class="cf-mentioned-user-popup">
                        <ul class="cf-system-user-email-list" role="listbox">
                            ${listItem}
                        </ul>
                    </div>
                `;
                $( emailList ).insertAfter( appendTo );
            }
        }


        // Make matched text highlighted.
        var makeMatchedTextHighlighted = function( term, markEmail, markName ) {
            term       = term.substring( 1 );
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
        var range     = '';
        var getCaretPosition = function( element ) {
            var caretOffset = 0;
            if ( w3 ) {
                range             = window.getSelection().getRangeAt(0);
                var preCaretRange = range.cloneRange();
                if( typeof element === 'Node' ) { // eslint-disable-line
                    preCaretRange.selectNodeContents( element );
                }
                preCaretRange.setEnd( range.endContainer, range.endOffset );
                caretOffset       = preCaretRange.toString().length;

            } else if ( ie ) {
                var textRange         = document.selection.createRange();
                var preCaretTextRange = document.body.createTextRange();
                preCaretTextRange.moveToElementText( element );
                preCaretTextRange.setEndPoint( "EndToEnd", textRange );
                caretOffset           = preCaretTextRange.text.length;
            }
            return caretOffset;
        }

        // Insert Display Name.
        var insertDisplayName = function( setRange, email, fullName, displayName, createTextarea ) {
            var gapValue = '';
            var gapEl = document.createElement( 'br' );
            var gapElContent = document.createTextNode( gapValue );
            gapEl.appendChild( gapElContent );
            setRange.insertNode( gapEl );

            var anchor = document.createElement( 'a' );
            anchor.setAttribute( 'contenteditable', false );
            anchor.setAttribute( 'href', `mailto:${email}` );
            anchor.setAttribute( 'title', fullName );
            anchor.setAttribute( 'data-email', email );
            anchor.setAttribute( 'class', 'js-mentioned' );
            var anchorContent = document.createTextNode( displayName );
            anchor.appendChild( anchorContent );
            setRange.insertNode( anchor );

            var getCurrentTextAreaID = $( createTextarea ).attr( 'id' );
            var currentTextareaNode  = document.getElementById( getCurrentTextAreaID );
            currentTextareaNode.focus();
        }

        /**
         * Check is the suggestion popup list is eligable to show or not.
         * @param string tracker
         * @return boolean
         */
        var show_suggestion = function( tracker ) {
            var allowedStrings = [ '', '@', ' @', ';@', 'v>' ];
            if( allowedStrings.includes( tracker ) ) {
                return true;
            }
            return false;
        }

        // Create @mentioning email features.
        var createAutoEmailMention = function() {
            var el                    = '';
            var currentBoardID        = '';
            var currentCommentBoardID = '';
            var typedText             = '';
            var trackedStr            = '';
            var isEmail               = false;
            var createTextarea        = '';
            var appendIn              = '';
            var assignablePopup       = '';
            var editLink              = '';
            var keysToAvoid           = [ 'Enter', 'Tab', 'Shift', 'Control', 'Alt', 'CapsLock', 'Meta', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown' ];
            var currentPostID         = $( '#post_ID' ).val();
            var parentBoardClass      = '.cls-board-outer';
            var mood                  = 'create';
            // Grab the current board ID.
            $( document.body ).on( 'click', parentBoardClass, function() {
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
                $( document.body ).on( 'focus', '.shareCommentContainer', function() {
                    el              = $( this ).parents(parentBoardClass).attr( 'id' );
                    currentBoardID  = `#${el}`;
                    appendIn        = `${currentBoardID} .cf-mentioned-user-popup`;
                    assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
                    editLink        = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
                    mood            = 'create';
                    if( 'create' === mood ) {
                        createTextarea  = `${currentBoardID} .js-cf-share-comment`;
                    }
                } )
            }
            $( document.body ).on( 'focus keyup', '.js-cf-edit-comment', function() {
                mood = 'edit';
                el   = $( this ).parents( parentBoardClass ).attr( 'id' );
                currentCommentBoardID = $( this ).parents( '.commentContainer' ).attr( 'id' );
                if( 'edit' === mood ) {
                    createTextarea  = `#${currentCommentBoardID} .js-cf-edit-comment`;
                }
            } )
            // Remove emails list on edit link click.
            $( document.body ).on( 'click', editLink, function() {
                $( appendIn ).remove();
                $( assignablePopup ).remove();
            } )
            // Triggering textarea keyup event.
            $( document.body ).on( 'keyup', createTextarea, function(e) {
                var _self = $( createTextarea );
                typedText = _self.html();
                // If textarea is blank then remove email list.
                if( undefined !== typedText && typedText.length <=0 ) {
                    $( appendIn ).remove();
                    $( assignablePopup ).remove();
                    $( '.cf-assign-to' ).remove();
                }

                // FireFox Browser Fix.
                var isFireFox = !!navigator.userAgent.match(/firefox/i);
                if( isFireFox ) {
                    if( typedText && typedText.length > 0 ) {
                        var refinedText = typedText.replace( /<br>/igm, '' );
                        typedText       = refinedText;
                    }
                }

                // Handeling space. As if someone type space has no intension to write email.
                // So we make isEmail false and trackedStr to blank.
                if( '' === e.key || ' ' === e.key ) {
                    isEmail = false;
                    trackedStr = '';
                }

                // Get current cursor position.
                var el    = $( createTextarea ).get(0);
                cursorPos = getCaretPosition(el);
                if( '@' === e.key && true === e.shiftKey ) {
                    var prevCharOfEmailSymbol = typedText.substr( cursorPos - 2, 2 );
                    if( show_suggestion( prevCharOfEmailSymbol ) ) { // meaning @ is typed at the begining or as independent.
                        // Fetch all email list.
                        isEmail = true;
                        $.ajax({
                            url: ajaxurl, // eslint-disable-line
                            type: 'post',
                            data: {
                                action: 'cf_get_user_email_list',
                                postID: currentPostID,
                                nonce: adminLocalizer.nonce, // eslint-disable-line
                            },
                            beforeSend: function() {},
                            success: function( res ) {
                                $( appendIn ).remove(); // Remove previous DOM.
                                $( assignablePopup ).remove(); // Remove previous DOM.
                                $( assignablePopup ).remove();
                                var data = JSON.parse( res );
                                emailList( createTextarea, data );
                            }
                        })
                    } else { // Meaning @ is typed inside an email address.
                        // do nothing.
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
                            trackedStr = trackedStr.slice( 0, -1 );
                        } else {
                            trackedStr += e.key;
                        }

                        if( '@' !== trackedStr ) {
                            // Sending Ajax Call to get the matched email list(s).
                            $.ajax({
                                url: ajaxurl, // eslint-disable-line
                                type: 'post',
                                data: {
                                    action: 'cf_get_matched_user_email_list',
                                    niddle: trackedStr,
                                    postID: currentPostID,
                                    nonce: adminLocalizer.nonce, // eslint-disable-line
                                },
                                success: function( res ) {
                                    $( appendIn ).remove(); // Removing previous DOM.
                                    $( assignablePopup ).remove(); // Removing previous DOM.
                                    var data = JSON.parse( res );
                                    emailList( createTextarea, data );
                                    makeMatchedTextHighlighted( trackedStr, '.cf-user-email', '.cf-user-display-name' );
                                }
                            })

                        }
                    }
                }
            } )
            // Append email in textarea.
            $( document.body ).on( 'click keypress', '.cf-system-user-email-list li', function(e) {
                e.stopPropagation();
                if( e.which === 1 ) {
                    var fullName    = $( this ).data( 'full-name' );
                    var displayName = $( this ).data( 'display-name' );
                    var email       = $( this ).data( 'email' );

                    // Insert Display Name.
                    insertDisplayName( range, email, fullName, displayName, createTextarea );

                    var typedContent              = $( createTextarea ).html();
                    var refinedContent            = typedContent.replace( /(?<=@)\w+(?=<)/gim, '' ); // @\s*(\w+)
                    var fragments                 = document.createRange().createContextualFragment( refinedContent );
                    var getCurrentTextAreaID      = $( createTextarea ).attr( 'id' );
                    var currentTextAreaNode       = document.getElementById( getCurrentTextAreaID );
                    currentTextAreaNode.innerHTML = '';
                    currentTextAreaNode.appendChild( fragments );
                    $( appendIn ).remove();
                    $( assignablePopup ).remove();
                    trackedStr = '';
                }
            } );
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
            $( document.body ).on( 'focus', appendTo, function() {
                el = $( this ).parents( parentBoardClass ).attr( 'id' );
            } )

            // On Suggested Email Click.
            $( document.body ).on( 'click', mentionedEmail, function() {
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
                let thisUserId      = $( this ).data( 'user-id' );
                let thisDisplayName = $( this ).data( 'display-name' );
                let checkbox = `
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i> Assign to ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
                `;

                let checkboxFragments      = document.createRange().createContextualFragment( checkbox );
                let appendToSelector       = document.querySelector( appendTo );
                appendToSelector.innerHTML = '';
                appendToSelector.appendChild( checkboxFragments );
                $( assignablePopup ).remove();
            } )
        }
        assignThisToUser();

        // Asignable Email List Template.
        var assignalbeList = function( _self, data ) {
            var listItem = '';
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
                    `;
                } )
            }
            var assignListTemplate = `
                <div class="cf-assignable-list-popup">
                    <ul class="cf-assignable-list">
                        ${listItem}
                    </li>
                </div>
            `;

            $( assignListTemplate ).insertAfter( _self );
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
                    url: ajaxurl, // eslint-disable-line
                    type: 'post',
                    data: {
                        action: 'cf_get_assignable_user_list',
                        content: content,
                        nonce: adminLocalizer.nonce // eslint-disable-line
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

        // Open comment box when user redirect from email.
        var openComment = function() {
            var commentedId = adminLocalizer.comment_id; // eslint-disable-line
            $( window ).load( function() {
                setTimeout( function() {
                    $( `#${commentedId} .js-edit-comment` ).trigger( 'click' );
                    $( `#${commentedId}` ).addClass( 'comment-flash' );
                }, 2000 );
                setTimeout( function() {
                    $( `#${commentedId}` ).removeClass( 'comment-flash' );
                }, 4000 );
            } )
        }
        openComment();

        // Dealing with content-editable console issue.
        var manageContentEditableConsoleIssue = function() {
            console.error = (function() {
                var error = console.error;
                return function( exception ) {
                    if ( ( exception + '' ).indexOf( 'Warning: A component is `contentEditable`' ) != 0 ) {
                        error.apply( console, arguments );
                    }
                }
            })();
        }
        manageContentEditableConsoleIssue();

        // Collapseing history toggle on outside click.
        $( document.body ).on( 'click', function(e) {
            var historyPopup = $( '#custom-history-popup' );
            if( 'history-toggle' !== e.target.id ) {
                if ( ! historyPopup.is( e.target )
                    && historyPopup.has( e.target ).length === 0
                ) {
                    $( '#custom-history-popup, .custom-buttons' ).removeClass( 'active' );
                }
            }
        } );

        // History Toggle
        $(document).on('click', '#history-toggle', function () {
            $('#custom-history-popup, #history-toggle').toggleClass('active');
            $(this).parents('.custom-buttons').toggleClass('active');

            if ($('#custom-history-popup').hasClass('active')) {
                $('#custom-history-popup').addClass('loaded');

                const CurrentPostID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

                // Fetch comments from db.
                var data = {
                    'action': 'cf_comments_history',
                    'currentPostID': CurrentPostID,
                    'limit': 10,
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post(ajaxurl, data, function (response) { // eslint-disable-line
                    $( '#custom-history-popup-inner' ).html('');
                    $(response).appendTo('#custom-history-popup-inner');
                    if( ! $( '#history-popup-insider' ).children().hasClass( 'user-data-row' ) ) {
                        $( '#comments-toggle' ).hide();
                    } else {
                        $( '#comments-toggle' ).show();
                    }
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
        // $(document).on('click', '[aria-label="More rich text controls"]', function () {
        //     setTimeout( function() {
        //         if( $('body').hasClass('hide-comments') ) {
        //             $('button.components-dropdown-menu__menu-item .dashicons-admin-comments').parents('button').toggleClass('hide-me');
        //         }
        //     }, 10);
        // });

        // Read More Comments
        $(document).on('click', '.readmoreComment, .readlessComment', function () {
            $(this).parents('.commentText').find('.readMoreSpan').toggleClass('active');
        });

    });

    // On Window Load Event.
    $( window ).on( 'load', function () {
        $('.cid_popup_hover').parents('.wp-block.editor-block-list__block.block-editor-block-list__block').addClass('parent_cid_popup_hover');

        // Handling Older WordPress Versions.
        // The function wp.data.select("core").getCurrentUser() is not
        // defined for v5.2.2, so getting data from PHP.
        if( 'undefined' === typeof ( wp.data.select("core").getCurrentUser().id ) ) { // eslint-disable-line
            // Fetch User details from AJAX.
            $.post(ajaxurl, { // eslint-disable-line
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

    $(document).on('click', '.cf-tabs-main .cf-tabs .cf-tab-item', function () {
        let getTabID = $(this).attr('data-id');
        $(this).parent().addClass('cf-tab-active').siblings().removeClass('cf-tab-active');
        $('#'+getTabID).addClass('cf-tab-active').show().siblings().removeClass('cf-tab-active').hide();
    });

})(jQuery); // eslint-disable-line

/**
 * Remove the <mdspan> tag from the text.
 *
 * @param sting elIDRemove The ID of the comment thread.
 */
var removeTag = function( elIDRemove ) { // eslint-disable-line

    const clientId = jQuery('[datatext="' + elIDRemove + '"]').parents('[data-block]').attr('data-block'); // eslint-disable-line

    const blockAttributes = wp.data.select('core/block-editor').getBlockAttributes(clientId); // eslint-disable-line
    if (null !== blockAttributes) {

        const findAttributes = ['content', 'citation', 'caption', 'value', 'values', 'fileName', 'text', 'downloadButtonText'];
        jQuery(findAttributes).each(function (i, attrb) { // eslint-disable-line
            var content = blockAttributes[attrb];
            if (undefined !== content && -1 !== content.indexOf(elIDRemove)) {

                if ('' !== content) {
                    let tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content;
                    let childElements = tempDiv.getElementsByTagName('mdspan');
                    for (let i = 0; i < childElements.length; i++) {
                        if (elIDRemove === childElements[i].attributes.datatext.value) {
                            childElements[i].parentNode.replaceChild(document.createTextNode(childElements[i].innerText), childElements[i]);
                            const finalContent = tempDiv.innerHTML;

                            if (attrb === 'content') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        content: finalContent
                                    }
                                });
                            } else if (attrb === 'citation') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        citation: finalContent
                                    }
                                });
                            } else if (attrb === 'value') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        value: finalContent
                                    }
                                });
                            } else if (attrb === 'caption') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        caption: finalContent
                                    }
                                });
                            } else if (attrb === 'values') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        values: finalContent
                                    }
                                });
                            } else if (attrb === 'fileName') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        fileName: finalContent
                                    }
                                });
                            } else if (attrb === 'text') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        text: finalContent
                                    }
                                });
                            } else if (attrb === 'downloadButtonText') {
                                wp.data.dispatch('core/editor').updateBlock(clientId, { // eslint-disable-line
                                    attributes: {
                                        downloadButtonText: finalContent
                                    }
                                });
                            }
                            break;
                        }
                    }
                }
            }
        });
    }
}
