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

    /*  Trigger to close sidebar on post editor focus */
    window.addEventListener('click', function(e){   
    if(jQuery('.edit-post-layout').hasClass('is-sidebar-opened')){
        jQuery('.interface-interface-skeleton__sidebar').removeClass('cf-sidebar-closed'); 
    }
        if (document.getElementsByClassName('edit-post-visual-editor').length > 0 && document.getElementsByClassName('edit-post-visual-editor')[0].contains(e.target)){
          // Clicked in editor
          jQuery('.interface-interface-skeleton__sidebar').addClass('cf-sidebar-closed');
          closeMulticollabSidebar();
        } 
       // $('.interface-interface-skeleton__sidebar').removeClass('cf-sidebar-closed'); 
      });
     
    /** last suggestion reply button tooltip */
    $(document).on('mouseover', '.shareCommentContainer .btn', function () {
        var boxHeight = jQuery(this).parents(".cls-board-outer").outerHeight() + 30;
        var parentHeight = jQuery("#md-comments-suggestions-parent").outerHeight() - boxHeight - 50;
        var boxPosition = jQuery(this).parents(".cls-board-outer").css('top');
        if( parentHeight < parseInt(boxPosition) - boxHeight + 30 ) {            
            var currentTop = jQuery(this).parents(".cls-board-outer").css('top');
            currentTop = parseInt( currentTop.replace('px', '') ) - 50;
            jQuery(this).parents(".cls-board-outer").css('top', currentTop + 'px'); 
        }
    });  
    /* Trigger to add/remove class from block
       when block level sugggestions are created  */
    

    // Display Delete Overlay Box 
    $(document).on('click', '.js-resolve-comment', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .comment-overlay-text").text("Delete this thread?");
    });

    $(document).on('click', '.resolve-cb', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .comment-overlay-text").text("Resolve this thread?");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-delete").text("Yes");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-cancel").text("No");
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
    });

    $(document).on('click', '.js-trash-comment', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
    });

    $(document).on('click', '.comment-delete-overlay .btn-cancel', function () {
        $(this).parents(".commentContainer").find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-resolve .resolve-cb").prop("checked", false);
    });

    // show hide toggle for commentendOn text in dashboard activity log
    $(document).on('click', '#cf-dashboard .user-data-row .cf-show-more', function () {

        var $this = $(this);
        if ($this.closest('.user-commented-on').find(".show-all").hasClass('js-hide')) {
            $this.closest('.user-commented-on').find(".show-all").removeClass('js-hide');
            $this.closest('.user-commented-on').find(".show-less").addClass('js-hide');
            $(this).text('Collapse');
        } else {
            $this.closest('.user-commented-on').find(".show-all").addClass('js-hide');
            $this.closest('.user-commented-on').find(".show-less").removeClass('js-hide');
            $(this).text('Show all');
        }
    });


    /* Hide/Show comments on board trigger   */
    $(document).on("showHideComments", function () {
        if (!$("#md-span-comments").is(':empty')) {
            $("#md-span-comments .cls-board-outer:not(.focus)").each(function () {
                let commentCount = parseInt($(this).find('.boardTop .commentContainer').length);

                if (commentCount > getCommentsLimit() && !$(this).hasClass('focus')) {
                    $(this).find('.show-all-comments').html(`Show all ${commentCount - 1} replies`);
                    $(this).find('.show-all-comments').show(); //phpcs:ignore
                    $(this).find('.boardTop .commentContainer').hide();
                    $(this).find('.boardTop .commentContainer').slice(0, getCommentsLimit()).show();
                } else {
                    $(this).find('.boardTop .commentContainer').show();
                    $(this).find('.show-all-comments').hide();
                }
            });
        }
    });

    // show all comment on button click
    $(document).on("click", "#md-span-comments .cls-board-outer .show-all-comments", function () {
        $(this).parents(".boardTop").find(".commentContainer").show();
    });
    // show all comment on button click
    $(document).on("click", "#cf-dashboard .user-data-row .show-all-comments", function () {
        $(this).parents(".user-data-row").find(".user-data-box").show();
        $(this).hide();
    });

    // show all comment on button click in activity center
    $(document).on("click", ".js-activity-centre .user-data-row .show-all-comments", function () {
        $(this).parents(".user-data-row").find(".user-data-box").show();
        $(this).parents(".user-data-row").find(".show-all-comments").hide();
    });
    
    /* editor layout update action trigger for commentOn class  */
    $(document).on("editorLayoutUpdate", function () {

        if ($("#md-span-comments").is(':empty') ||
            ($('body').hasClass('hide-sg') && $('body').hasClass('hide-comments'))
        ) {
            $('body').removeClass("commentOn");
        } else {

            if ((!$('body').hasClass('hide-sg') && $("#md-span-comments .sg-board").length > 0) ||
                (!$('body').hasClass('hide-comments') && $("#md-span-comments .cm-board").length > 0)) {
                $('body').addClass("commentOn");
            } else if (($('body').hasClass('hide-comments') && !$('body').hasClass('hide-sg') && $("#md-span-comments .sg-board").length === 0) ||
                ($('body').hasClass('hide-sg') && !$('body').hasClass('hide-comments') && $("#md-span-comments .cm-board").length === 0)) {
                $('body').removeClass("commentOn");
            }
        }
    });


    // Stripping out unwanted <mdspan> tags from the content.
    $(window).on('load', function () {
        var findMdSpan = 'mdspan';
        $(findMdSpan).each(function () {
            var datatext = $(this).attr('datatext');
            if (undefined === datatext) {
                $(this).replaceWith(DOMPurify.sanitize($(this).text())); // phpcs:ignore
            }

        });
    })

    // Resetting All Class From Activity Center
    $(document).on('click', '.cls-board-outer', function () {
        var boardID = $(this).attr('id');

        $('.js-activity-centre .user-data-row').removeClass('active');
        $(`#cf-${boardID}`).addClass('active');
        $('.commentIcon').removeClass('is-selected');
        wp.data.dispatch('mdstore').setDataText(boardID);
        //check if URL has a datatext param
        const queryString = window.location.search; //phpcs:ignore
        const urlParams = new URLSearchParams(queryString);
        const current_url = urlParams.get('current_url');
        if (current_url) {
            urlParams.delete('current_url');
            window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
        }
        $('[datatext="' + boardID + '"]').addClass('is-selected');
        jQuery.event.trigger({ type: "showHideComments" });

    });
    $(document).on('focus', '.cf-share-comment', function () {
        $('.cf-share-comment').addClass('comment-focus');
        $('.js-cf-edit-comment a.js-mentioned').css('white-space', 'pre-wrap');
        $('.btn-wrapper').css('display','block');
    })
    $(document).on('focusout', '.cf-share-comment', function () {
        $('.cf-share-comment').removeClass('comment-focus');
    })

    $(document).on('click', '.cf-sidebar-settings', function () {
        if ($('body').hasClass('commentOn')) {
            $('.comment-toggle .components-form-toggle').removeClass('is-checked');
            //$('.comment-toggle .components-base-control__help').html('All comments will show on the content area.');
        }
    })

    // Add temporary style tag to hide resolved tag color on load.
    //comment below line to resolved copy url board hilight issue.
    //$('html').prepend('<style id="loader_style">body mdspan{background: transparent !important;}.components-editor-notices__dismissible{display: none !important;</style>');
    // On Document Ready Event.
    $(document).ready(function () {
        let doingAjax = false;
        // If thread focused via an activity center,
        // it is in lock mode, so clicking any para
        // would unlock it.
        $(document).on('click', '.block-editor-block-list__layout .wp-block', function (e) {

            if ($('.cls-board-outer').hasClass('locked')) {

                // Reset Comments Float. This will reset the positions of all comments.
                $('#md-span-comments .cls-board-outer').css('opacity', '1');
                $('#md-span-comments .cls-board-outer').removeClass('focus');
                $('#md-span-comments .cls-board-outer').removeClass('is-open');
                $('#md-span-comments .cls-board-outer').removeAttr('style');
                $('#md-span-comments .cls-board-outer .buttons-wrapper').removeClass('active');

                if (e.target.localName === 'mdspan') {
                    const dataid = $(e.target).attr('datatext');
                    // Trigger card click to focus.
                    $('#' + dataid).trigger('click');
                }
                $('.cls-board-outer').removeClass('locked');
            }
        });


        // Show/Hide comment toggler if the counter value is zero.
        $(document.body).on('click', '#history-toggle', function () {
            var dataCount = $(this).attr('data-count');
            if (0 >= dataCount) {
                $('#comments-toggle').hide();
            } else {
                $('#comments-toggle').show();
            }
        })

        // Settings page tabs toggle.
        $(document).on('click', '.cf-tabs span', function () {
            const tabID = $(this).data('id');
            $('.cf-tab-inner').hide();
            $('#' + tabID).show();
        });

        // Save Settings.
        $('#cf_settings').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_settings',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                $('#cf_settings .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf_settings .cf-success').slideUp(300);
                }, 3000);
            });
        });

        $(document).on('click', '.reset-filter', function () {
            $(this).parent().find('select').prop('selectedIndex', 0);
            $(this).parent().submit();
        });
        // Save permissions.
             $('#cf_permissions').on('submit', function (e) {
                e.preventDefault();
                $(this).find('[type="submit"]').addClass('loading');
                const settingsData = {
                    'action': 'cf_save_permissions',
                    'formData': $(this).serialize()
                };
                $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                    $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                    $('#cf-permissions-notice .cf-success').slideDown(300);
                    setTimeout(function () {
                        $('#cf-permissions-notice .cf-success').slideUp(300);
                    }, 3000);
                });
            });
            $(document).on('click', '.reset-filter', function () {
                $(this).parent().find('select').prop('selectedIndex', 0);
                $(this).parent().submit();
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
            closeMulticollabSidebar();
            // Exclude focus on specific elements.
            var target = $(e.target);
            if (target.is(".commentContainer .comment-actions, .commentContainer .comment-actions *")) {
                return;
            }

            const _this = $(this);
            // Reset Comments Float.
            $('#md-span-comments .cls-board-outer').removeAttr('style');
            $('#md-span-comments .cls-board-outer').removeClass('focus');
            $('#md-span-comments .cls-board-outer').removeClass('is-open');
            $('#md-span-comments .comment-delete-overlay').removeClass('show');
            $('#md-span-comments .comment-resolve .resolve-cb').prop("checked", false);
            $('#md-span-comments .cls-board-outer .buttons-wrapper').removeClass('active');
            $('.btn-wrapper').css('display','none');
            _this.addClass('focus');
            _this.addClass('is-open');

            const selectedText = _this.attr('id');
            let topOfText;
            if (selectedText.match(/^el/m) !== null) {
                topOfText = $('[datatext="' + selectedText + '"]').offset().top;
            } else {
                let sid = $('#' + selectedText).attr('data-sid');
                topOfText = $('[id="' + sid + '"]').offset().top;
            }

            $('#md-span-comments .cls-board-outer').css('opacity', '0.4');
            _this.css('opacity', '1');
            _this.offset({ top: topOfText });
            scrollBoardToPosition(topOfText);
            /*var scrollTopClass = '';
            if (0 !== $('.interface-interface-skeleton__content').length) {
                // Latest WP Version
                scrollTopClass = '.interface-interface-skeleton__content';

            } else if (0 !== $('.block-editor-editor-skeleton__content').length) {
                // Latest WP Version
                scrollTopClass = '.block-editor-editor-skeleton__content';

            } else if (0 !== $('.edit-post-layout__content').length) {
                // Old WP Versions
                scrollTopClass = '.edit-post-layout__content';

            } else {
                // Default
                scrollTopClass = 'body';
            }

            topOfText = topOfText + $(scrollTopClass).scrollTop();

            $(scrollTopClass).animate({
                scrollTop: topOfText - 150
            }, 1000);*/

            $('[data-rich-text-format-boundary="true"]').removeAttr('data-rich-text-format-boundary');
            $('[datatext="' + selectedText + '"]').attr('data-rich-text-format-boundary', true);

            if ($(`#${selectedText}`).hasClass('sg-board')) {
                let sid = $(`#${selectedText}`).attr('data-sid');
                $(`#${sid}`).attr('data-rich-text-format-boundary', 'true');
            }

        });

        // Scroll to the commented text and its popup from History Popup.
        $(document.body).on('click', '.user-commented-on', function (e) {
            $('#custom-history-popup, #history-toggle, .custom-buttons').toggleClass('active');
            e.preventDefault();

            // Triggering comments-toggle if it is closed when clicking on a particular commented link from activity center.
            if ($('#comments-toggle').hasClass('active')) {
                $('#comments-toggle').trigger('click');
            }

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
        var emailList = function (appendTo, data) {
            var listItem = '';
            if (data.length > 0) {
                data.forEach(function (user, listIndex) {
                    if (listIndex == 0) {
                        listItem += `
                        <li class="cf-user-list-item active" role="option" data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                            <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                            <div class="cf-user-info">
                                <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${user.role}</small></p>
                            </div>
                        </li>`;
                    } else {
                        listItem += `
                        <li class="cf-user-list-item" role="option" data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                            <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                            <div class="cf-user-info">
                                <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${user.role}</small></p>
                            </div>
                        </li>`;
                    }
                })
                var emailList = `
                    <div class="cf-mentioned-user-popup">
                        <ul class="cf-system-user-email-list" role="listbox" tabindex="0">
                            ${listItem}
                        </ul>
                    </div>
                `;
                emailList = DOMPurify.sanitize(emailList);
                $(emailList).insertAfter(appendTo); // phpcs:ignore
            }
        }


        // Make matched text highlighted.
        var makeMatchedTextHighlighted = function (term, markEmail, markName) {
            term = term.substring(1);
            var $markEmail = $(markEmail);
            var $markName = $(markName);

            if (term) {
                $markEmail.mark(term);
                $markName.mark(term);
            }
        }

        // Detect Browser.
        var browser = (function (agent) {
            switch (true) {
                case agent.indexOf("edge") > -1: return "MS Edge (EdgeHtml)";
                case agent.indexOf("edg") > -1: return "MS Edge Chromium";
                case agent.indexOf("opr") > -1 && !!window.opr: return "opera";
                case agent.indexOf("chrome") > -1 && !!window.chrome: return "chrome";
                case agent.indexOf("trident") > -1: return "Internet Explorer";
                case agent.indexOf("firefox") > -1: return "firefox";
                case agent.indexOf("safari") > -1: return "safari";
                default: return "other";
            }
        })(window.navigator.userAgent.toLowerCase());

        // Get Caret Position
        var ie = (typeof document.selection != "undefined" && document.selection.type != "Control") && true;
        var w3 = (typeof window.getSelection != "undefined") && true;
        var cursorPos = 0;
        var range = '';


        var getCaretPosition = function (editableDiv) {
            var caretPos = 0, sel;
            if (window.getSelection) {
                sel = window.getSelection();

                if (sel.rangeCount) {

                    range = sel.getRangeAt(0);
                    if (range.commonAncestorContainer.parentNode === editableDiv) {

                        caretPos = range.endOffset;

                    }
                }
            } else if (document.selection && document.selection.createRange) {
                range = document.selection.createRange();
                if (range.parentElement() === editableDiv) {
                    var tempEl = document.createElement("span");
                    editableDiv.insertBefore(tempEl, editableDiv.firstChild); // phpcs:ignore
                    var tempRange = range.duplicate();
                    tempRange.moveToElementText(tempEl);
                    tempRange.setEndPoint("EndToEnd", range);
                    caretPos = tempRange.text.length;
                }
            }
            return caretPos;
        }
        // Insert Display Name.
        var insertDisplayName = function (setRange, email, fullName, displayName) {

            var gapElContent = document.createTextNode("\u00A0"); // Adding whitespace after the name.
            var anchor = document.createElement('a');

            var splitDisplayName = displayName.split(' ');
            anchor.setAttribute('contenteditable', false);
            anchor.setAttribute('href', `mailto:${email}`);
            anchor.setAttribute('title', fullName);
            anchor.setAttribute('data-email', email);
            anchor.setAttribute('class', 'js-mentioned');
            var anchorContent = document.createTextNode(splitDisplayName[0]);
            anchor.appendChild(anchorContent);
            setRange.insertNode(anchor);
            anchor.after(gapElContent); // phpcs:ignore

        }

        // Cases when we should show the suggestion list.
        var showSuggestion = function (tracker) {
            var allowedStrings = ['', '@', ' @', ';', '>'];
            if (allowedStrings.includes(tracker)) {
                return true;
            }
            return false;

        }
        // Format Pasted Content.
        var formatPastedContent = function () {
            $(document.body).on('paste', '.js-cf-share-comment, .js-cf-edit-comment', function (e) {
                e.preventDefault();
                let paste = (e.originalEvent || e).clipboardData.getData('text/plain');
                const selection = window.getSelection();
                const pastedRange = selection.getRangeAt(0);
                if (!selection.rangeCount) return false;
                selection.deleteFromDocument();
                pastedRange.insertNode(document.createTextNode(paste));
                pastedRange.collapse(false);
                selection.removeAllRanges();
                selection.addRange(pastedRange);
                e.preventDefault();
            })

        }
        formatPastedContent();
        // Create @mentioning email features.
        var createAutoEmailMention = function () {
            var el = '';
            var currentBoardID = '';
            var currentCommentBoardID = '';
            var typedText = '';
            var trackedStr = '';
            var isEmail = false;
            var createTextarea = '';
            var appendIn = '';
            var assignablePopup = '';
            var editLink = '';
            var keysToAvoid = ['Enter', 'Tab', 'Shift', 'Control', 'Alt', 'CapsLock', 'Meta', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
            var currentPostID = $('#post_ID').val();
            var parentBoardClass = '.cls-board-outer';
            var mood = 'create';
            //var cachedUsersList       = adminLocalizer.cached_users_list;


            // Grab the current board ID.
            $(document.body).on('click', parentBoardClass, function () {
                el = $(this).attr('id');
                currentBoardID = `#${el}`;
                appendIn = `${currentBoardID} .cf-mentioned-user-popup`;
                assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
                editLink = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
                mood = 'create';
                if ('create' === mood) {
                    createTextarea = `${currentBoardID} .js-cf-share-comment`;
                }
            });
            if ('' === el) {
                $(document.body).on('focus', '.shareCommentContainer', function () {
                    el = $(this).parents(parentBoardClass).attr('id');
                    currentBoardID = `#${el}`;
                    appendIn = `${currentBoardID} .cf-mentioned-user-popup`;
                    assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
                    editLink = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
                    mood = 'create';
                    if ('create' === mood) {
                        createTextarea = `${currentBoardID} .js-cf-share-comment`;
                    }
                })
            }
            $(document.body).on('focus keyup paste', '.js-cf-edit-comment', function () {

                mood = 'edit';
                el = $(this).parents(parentBoardClass).attr('id');
                currentCommentBoardID = $(this).parents('.commentContainer').attr('id');
                if ('edit' === mood) {
                    createTextarea = `#${currentCommentBoardID} .js-cf-edit-comment`;
                }

            });
            // Restrict Br in newline in firefox
            if ('firefox' === browser) {
                $(document.body).on("keydown", '.cf-share-comment', function (e) {
                    if (e.keyCode == 13 && !e.shiftKey) {
                        document.execCommand('insertHTML', false, '<br><br>');
                        return false;
                    }
                });
            }

            // Clearing out assignable dom on edit save or edit cancel.
            $(document.body).on('click', `${currentBoardID} .js-cancel-comment, ${currentBoardID} .save-btn`, function () {
                $(`${currentBoardID} .cf-assign-to`).remove();
            })

            // Remove emails list on edit link click.
            $(document.body).on('click', editLink, function () {
                $(appendIn).remove();
                $(assignablePopup).remove();
            });
            var mentioncounter = 0;
            $(document.body).on('click', '.cls-board-outer, .commentInnerContainer .btn-comment, .cf-share-comment-wrapper .btn, .block-editor-writing-flow .is-root-container', function () {
                mentioncounter = 0;
            });
            /**
             * ========================================
             * Triggering textarea keyup event.
             * ========================================
             */
            $(document.body).on('keyup', createTextarea, function (e) {

                var _self = $(createTextarea);
                typedText = _self.html();

                // Clearing out any junk left when clearing the textarea.
                if ('<br>' === _self.html() || '&nbsp;' === _self.html()) {

                    typedText = '';
                    $(createTextarea).html('');
                }



                // Removing assignable checkbox if that user's email is not in the content or removed.
               
                if (undefined !== typedText && typedText.length > 0) {
                    var assignCheckBoxId = `${currentBoardID}-cf-assign-to-user`;
                    var emailSet = typedText.match(/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/igm);
                    emailSet = new Set(emailSet);
                    var emailAddresses = Array.from(emailSet);

                    // Get the assigner email of the current board.
                    var currentBoardAssingerEmail = $(`${currentBoardID} .cf-board-assigned-to`).data('user-email');

                    if (undefined !== emailAddresses && emailAddresses.length > 0) {
                        if (assignCheckBoxId.length > 0) {
                            var assignCheckBoxUserEmail = $(assignCheckBoxId).attr('data-user-email');

                            let checkEmailPattern = new RegExp(assignCheckBoxUserEmail, 'igm');
                            let isThere = typedText.match(checkEmailPattern);
                            if (!isThere && !doingAjax) {

                                doingAjax=true;
                                var appendInCheckbox = [];
                                $.ajax({
                                    url: ajaxurl, // eslint-disable-line
                                    type: 'post',
                                    data: {
                                        action: 'cf_get_user_email_list',
                                        postID: currentPostID,
                                        nonce: adminLocalizer.nonce, // eslint-disable-line
                                    },
                                    beforeSend: function () { },
                                    success: function (res) {
                                        $(appendIn).remove(); // Remove previous DOM.
                                        $(assignablePopup).remove(); // Remove previous DOM.
                                        var data = JSON.parse(res);

                                        data.forEach(function (item) {
                                            if (currentBoardAssingerEmail === emailAddresses[0]) {
                                                if (item.user_email === emailAddresses[1]) {
                                                    appendInCheckbox.push(item);

                                                }
                                            } else {
                                                if (item.user_email === emailAddresses[0]) {
                                                    appendInCheckbox.push(item);

                                                }
                                            }

                                        })
                                        if (appendInCheckbox.length > 0) {
                                            $(assignCheckBoxId).prop('checked', false);
                                            $(assignCheckBoxId).data('user-email', appendInCheckbox[0].user_email)
                                            $(assignCheckBoxId).val(appendInCheckbox[0].ID);
                                            $(assignCheckBoxId).next('i').text(`Assign to  ${appendInCheckbox[0].display_name}`);
                                        }

                                    }
                                })
                               
                            }
                        }
                    } else {
                        $(`${currentBoardID} .cf-assign-to`).remove();
                    }

                    // Remove assigner dom if there is not email in the editor.
                    var findEmails = typedText.match(/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i);

                    if (!findEmails || undefined === findEmails) {
                        $(`${currentBoardID} .cf-assign-to`).remove();
                    }

                    // Removing assinger chcekbox if it is matched with current board assignees email.
                    if (emailAddresses.length === 1 && emailAddresses[0] === currentBoardAssingerEmail) {
                        $(`${currentBoardID} .cf-assign-to`).remove();
                    }

                }

                // If textarea is blank then remove email list.
                if (undefined !== typedText && typedText.length <= 0) {
                    $(appendIn).remove();
                    $(assignablePopup).remove();
                    $('.cf-assign-to').remove();
                }

                if (typedText && typedText.length > 0) {
                    var refinedText = typedText.replace(/<br>/igm, '');
                    typedText = refinedText;
                }

                // Handeling space. As if someone type space has no intension to write email.
                // So we make isEmail false and trackedStr to blank.
                if (32 === e.which) {
                    isEmail = false;
                    trackedStr = '';
                }

                // Get current cursor position.
                var el = $(createTextarea).get(0);
                cursorPos = getCaretPosition(el);



               // If @ is pressed and shiftkey is true.remove true === e.shiftKey to support swiss keyboard
               if (('@' === e.key || 'KeyG' === e.code) && typedText.length > 0 && $(createTextarea).is(':focus') === true) {
                    doingAjax = false;
                    var prevCharOfEmailSymbol = typedText.substr(-1, 1);
                    var showSuggestionFunc;
                    var index = typedText.indexOf("@");
                    var preText = typedText.charAt(index);
                    mentioncounter++;

                    if (preText.indexOf(" ") > 0 || preText.length > 0) {
                        var words = preText.split(" ");
                        var prevWords = (words[words.length - 1]);
                    }

                    if ('@' === prevWords) {
                        showSuggestionFunc = showSuggestion(prevWords);
                    }
                    if ('@' === prevCharOfEmailSymbol) {
                        showSuggestionFunc = showSuggestion(prevCharOfEmailSymbol);
                    }
                    if (showSuggestionFunc && mentioncounter <= 1 && !doingAjax) {
                        doingAjax = true;
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
                            beforeSend: function () { },
                            success: function (res) {
                                $(appendIn).remove(); // Remove previous DOM.
                                $(assignablePopup).remove(); // Remove previous DOM.
                                var data = JSON.parse(res);
                                emailList(createTextarea, data);
                            }
                        })
                    } else {
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                    }
                }

                if ((32 === e.which) || (13 === e.which) || (8 === e.which)) {
                    mentioncounter = 0;
                }
                if (true === isEmail && typedText.length > 0 && $(createTextarea).is(':focus') === true) {
                    var checkKeys = function (key) {
                        if (key === e.key) {
                            return true;
                        }
                        return false;
                    }

                    if (!keysToAvoid.find(checkKeys)) {

                        // Check for backspace.
                        if ('Backspace' === e.key) {

                            let prevCharOfEmailSymbol = typedText.substr(-1, 1);
                            if ('@' === prevCharOfEmailSymbol) {
                                if ('' !== typedText) {
                                    trackedStr = '@';
                                } else {
                                    trackedStr = '';
                                }
                            } else {
                                trackedStr = trackedStr.slice(0, -1);
                            }
                        } else {
                            trackedStr += e.key;
                        }

                        // Check for ctrl+backspace.
                        if ('Backspace' === e.key && true === e.ctrlKey) {
                            let prevCharOfEmailSymbol = typedText.substr(-1, 1);
                            if ('@' === prevCharOfEmailSymbol) {
                                if ('' !== typedText) {
                                    trackedStr = '@';
                                } else {
                                    trackedStr = '';
                                }
                            } else {
                                trackedStr = '';
                            }
                        }
                    }
                    if (13 === e.which) {
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                    }
               
                    // If trackedStr is left to @ commented by pooja
                   /* if ('@' === trackedStr && $(createTextarea).is(':focus') === true && cursorPos != 0 && !doingAjax) {
                            doingAjax = true;
                        //if(!keysToAvoid.includes(e.key) && 'Backspace' != e.key){
                        $.ajax({
                            url: ajaxurl, // eslint-disable-line
                            type: 'post',
                            data: {
                                action: 'cf_get_user_email_list',
                                postID: currentPostID,
                                nonce: adminLocalizer.nonce, // eslint-disable-line
                            },
                            beforeSend: function () { },
                            success: function (res) {
                                $(appendIn).remove(); // Remove previous DOM.
                                $(assignablePopup).remove(); // Remove previous DOM.
                                var data = JSON.parse(res);
                                emailList(createTextarea, data);
                               
                            }
                            
                        })
                       
                        //}   
                       
                    }*/
                    doingAjax = false;
                    // If trackedStr contains other chars with @ as well.
                    if ('@' !== trackedStr && $(createTextarea).is(':focus') === true) {
                        let checkEmailSymbol = trackedStr.match(/^@\w+$/ig);
                        if (checkEmailSymbol && cursorPos != 0) {
                            var refinedCachedusersList = [];
                            let niddle = trackedStr.substr(1);
                            if ('' !== niddle && niddle.length >3) {
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
                                    success: function (res) {
                                        $(appendIn).remove(); // Remove user list popup DOM.
                                        $(assignablePopup).remove(); // Remove assignable user list popup DOM.
                                        var data = JSON.parse(res);
                                        emailList(createTextarea, data);
                                        makeMatchedTextHighlighted(trackedStr, '.cf-user-email', '.cf-user-display-name');
                                    }
                                })

                            }
                        } else {
                            $(appendIn).remove(); // Remove user list popup DOM.
                            $(assignablePopup).remove(); // Remove assignable user list popup DOM.

                        }
                    }

                    // Clearing the popups when trackedStr is empty.
                    if (!trackedStr || '' === trackedStr) {
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                    }
                }

                // Clearing popup when user types any space or use enterkey.
                if (32 === e.which) {
                    $(appendIn).remove();
                    $(assignablePopup).remove();
                }
            });
            // email list controlling by keyboard keys
            $(document.body).on('keydown', `.cf-share-comment`, function (e) {
                if ([38, 40].indexOf(e.keyCode) > -1) {
                    e.preventDefault();
                }
                if (e.which == 40) {
                    $("li.cf-user-list-item.active").removeClass('active');
                    $(".cf-system-user-email-list").find('.cf-user-list-item:eq(1)').addClass('active');
                    $(".cf-share-comment").focusout();
                    $(".cf-system-user-email-list").focus();
                }
            });
            $(document.body).on('keydown', `.cf-mentioned-user-popup .cf-system-user-email-list`, function (e) {
                const firstIndex = $(this).find('.cf-user-list-item').first().index();
                const lastIndex = $(this).find('.cf-user-list-item').last().index();
                var index = $(this).children('li.cf-user-list-item.active').index();

                if ([38, 40].indexOf(e.keyCode) > -1) {
                    e.preventDefault();
                }
                switch (e.which) {
                    case 38:
                        index = (index == firstIndex ? lastIndex : index - 1);
                        $(".cf-system-user-email-list").focus();
                        e.stopPropagation();
                        break;
                    case 40:
                        index = (index == lastIndex ? 0 : index + 1);
                        $(".cf-system-user-email-list").focus();
                        break;
                    case 13:
                        var fullName = $(this).find('.cf-user-list-item:eq( ' + index + ' )').attr("data-full-name");
                        var displayName = '@' + $(this).find('.cf-user-list-item:eq( ' + index + ' )').attr("data-display-name");
                        var email = $(this).find('.cf-user-list-item:eq( ' + index + ' )').attr("data-email");
                        // Insert Display Name.
                        insertDisplayName(range, email, fullName, displayName, createTextarea);
                        var typedContent = $(createTextarea).html();
                        // Remove @ before display name anchor tag and insterted in to anchor tag
                        // commented this below line because of bug fixing of <br> removing after user tag
                        //typedContent = typedContent.replace(/[<]br[^>]*[>]<a/gim,"<a");
                        typedContent = typedContent.replace(/@<a/g, '<a');
                        if ('firefox' !== browser) {
                            typedContent = chromeEdgeClearFix(typedContent);
                        }
                        var refinedContent = typedContent.replace(/(^@|\s@)([a-z0-9]\w*)/gim, ' ');
                        refinedContent = typedContent.replace(/@\w+<a/gim, ' <a');
                        var fragments = document.createRange().createContextualFragment(refinedContent);
                        var getCurrentTextAreaID = $(createTextarea).attr('id');
                        var currentTextAreaNode = document.getElementById(getCurrentTextAreaID);
                        currentTextAreaNode.innerHTML = '';
                        currentTextAreaNode.appendChild(fragments);
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                        trackedStr = '';
                        // Setup the caret position after appending the Display Name.
                        var getCurrentTextAreaID = $(createTextarea).attr('id');
                        var currentTextareaNode = document.getElementById(getCurrentTextAreaID);
                        var children = currentTextareaNode.lastElementChild;
                        //Add fix to remove last <br> tag after appending the Display Name.
                        if (children.tagName && children.tagName === "BR") {
                            currentTextareaNode.removeChild(children)
                        }
                        e.preventDefault();
                        $(".cf-share-comment").focus();
                        var selectChild = currentTextareaNode.childNodes.length - 1;
                        var el = currentTextareaNode.childNodes[selectChild];
                        var cursorSel = window.getSelection();
                        range = cursorSel.rangeCount ? cursorSel.getRangeAt(0) : null;

                        //remove condition to set cursore position in firefox
                        range.setStart(el, range.startOffset);
                        range.collapse(true);
                        cursorSel.removeAllRanges();
                        cursorSel.addRange(range);
                        break;
                }

                $(this).find('.active').removeClass('active');
                $(this).find('.cf-user-list-item:eq( ' + index + ' )').addClass('active');

            });

            // Append email in textarea.
            $(document.body).on('click keypress', '.cf-system-user-email-list li', function (e) {
                e.stopPropagation();


                if (e.which === 1) {
                    var fullName = $(this).data('full-name');
                    var displayName = '@' + $(this).data('display-name');
                    var email = $(this).data('email');

                    // Insert Display Name.
                    insertDisplayName(range, email, fullName, displayName, createTextarea);

                    var typedContent = $(createTextarea).html();
                    // Remove @ before display name anchor tag and insterted in to anchor tag
                    // commented this below line because of bug fixing of <br> removing after user tag
                    //typedContent = typedContent.replace(/[<]br[^>]*[>]<a/gim,"<a");
                    typedContent = typedContent.replace(/@<a/g, '<a');

                    if ('firefox' !== browser) {
                        typedContent = chromeEdgeClearFix(typedContent);
                    }

                    var refinedContent = typedContent.replace(/(^@|\s@)([a-z0-9]\w*)/gim, ' ');
                    refinedContent = typedContent.replace(/@\w+<a/gim, ' <a');
                    var fragments = document.createRange().createContextualFragment(refinedContent);
                    var getCurrentTextAreaID = $(createTextarea).attr('id');
                    var currentTextAreaNode = document.getElementById(getCurrentTextAreaID);
                    currentTextAreaNode.innerHTML = '';
                    currentTextAreaNode.appendChild(fragments);
                    $(appendIn).remove();
                    $(assignablePopup).remove();
                    trackedStr = '';
                }
                // Setup the caret position after appending the Display Name.
                var getCurrentTextAreaID = $(createTextarea).attr('id');
                var currentTextareaNode = document.getElementById(getCurrentTextAreaID);
                var children = currentTextareaNode.lastElementChild;
                //Add fix to remove last <br> tag after appending the Display Name.
                if (children.tagName && children.tagName === "BR") {

                    currentTextareaNode.removeChild(children)
                }

                var selectChild = currentTextareaNode.childNodes.length - 1;
                var el = currentTextareaNode.childNodes[selectChild];
                var cursorSel = window.getSelection();
                range = cursorSel.getRangeAt(0);
                //remove condition to set cursore position in firefox
                range.setStart(el, range.startOffset);
                range.collapse(true);
                cursorSel.removeAllRanges();
                cursorSel.addRange(range);

            });
        }
        createAutoEmailMention();
        // Chrome, Edge Clearfix.
        var chromeEdgeClearFix = function (typedContent) {
            typedContent = typedContent.replace(/(<div>)/ig, '<br>');
            typedContent = typedContent.replace(/(<\/div>)/ig, '');
            return typedContent;
        }
        $(document.body).on('keydown', '.cf-system-user-email-list', function (e) {
            let el = '';
            var parentBoardClass = '.cls-board-outer';
            let appendTo = '.cf-share-comment';
            var mentionedEmail = '.cf-system-user-email-list li';
            let checkBoxContainer = '.cf-assign-to';
            if (e.which == 13) {
                el = $(".cls-board-outer.focus.is-open").attr('id');
                let thisUserId = $(this).find(".cf-user-list-item.active").attr('data-user-id');
                let thisDisplayName = $(this).find(".cf-user-list-item.active").attr('data-display-name');
                let thisUserEmail = $(this).find(".cf-user-list-item.active").attr('data-email');
                let currentBoardAssinger = $(`#${el} .cf-board-assigned-to`).attr('data-user-id');
                const assigntoText = (currentBoardAssinger) ? 'Reassign to  ' : 'Assign to  ';
                let checkbox = `
                <div class="cf-assign-to">
                <div class="cf-assign-to-inner">
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${assigntoText} ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                </div>
                <span class="assignMessage">Your @mention will add people to this discussion and send an email.</span>     
                </div>`;
                if ('' !== el) {
                    if ($(`#${el} ${checkBoxContainer}`).children().length <= 1) {
                        $(`#${el} ${checkBoxContainer}`).empty();
                        checkbox = DOMPurify.sanitize(checkbox);
                        $(checkbox).insertAfter(`#${el} ${appendTo}`); // phpcs:ignore
                    }
                }
            }

        });
        // User Assign Function.
        var assignThisToUser = function () {
            let el = '';
            var parentBoardClass = '.cls-board-outer';
            let appendTo = '.cf-share-comment';
            var mentionedEmail = '.cf-system-user-email-list li';
            let checkBoxContainer = '.cf-assign-to';

            // Grab the current board ID.
            $(document.body).on('focus', appendTo, function () {
                el = $(this).parents(parentBoardClass).attr('id');
            })

            // On Suggested Email Click.
            $(document.body).on('click', mentionedEmail, function () {
                let checkbox ;
                let thisUserId = $(this).data('user-id');
                let thisDisplayName = $(this).data('display-name');
                let thisUserEmail = $(this).data('email');
                let currentBoardAssinger = $(`#${el} .cf-board-assigned-to`).data('user-id');
                const assigntoText = (currentBoardAssinger) ? 'Reassign to ' : 'Assign to ';
                if (multicollab_fs.can_use_premium_code) {
                 checkbox = `
                <div class="cf-assign-to">
                <div class="cf-assign-to-inner">
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${assigntoText} ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                </div>
                <span class="assignMessage">Your @mention will add people to this discussion and send an email.</span>     
                </div>`;
                }else{
                     checkbox = `
                <div class="cf-assign-to">
                <div class="cf-assign-to-inner">
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${assigntoText} ${thisDisplayName}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                </div>
                </div>`;

                } 
                // Get the assigner id of the current board.
                let currentBoardAssingerID = $(`#${el} .cf-board-assigned-to`).data('user-id');
                if (thisUserId !== currentBoardAssingerID) {
                    if ('' !== el) {
                        if ($(`#${el} ${checkBoxContainer}`).children().length <= 1) {
                            $(`#${el} ${checkBoxContainer}`).empty();
                            checkbox = DOMPurify.sanitize(checkbox);
                            $(checkbox).insertAfter(`#${el} ${appendTo}`); // phpcs:ignore
                        }
                    }
                }
                //change assignee message when checkbox selected
                if (multicollab_fs.can_use_premium_code) {
                    $(document).on("click", '#' + el + '-cf-assign-to-user', function () {
                        var checked = $('#' + el + ' .cf-assign-to-user').is(':checked');
                        $('#' + el + ' .assignMessage').text(checked ? 'The Assigned person will be notified and responsible for marking as done.' : 'Your @mention will add people to this discussion and send an email.');
                    });
                }
            });

            // On Assignable Email Click.
            $(document.body).on('click', '.cf-assignable-list li', function (e) {

                if ($(this).parents(parentBoardClass).hasClass('cm-board')) {
                    // e.preventDefault();
                    el = $(this).parents(parentBoardClass).attr('id');
                    let appendTo = `#${el} .cf-assign-to`;
                    let assignablePopup = `#${el} .cf-assignable-list-popup`;
                    let thisUserId = $(this).data('user-id');
                    let thisUserEmail = $(this).data('email');
                    let thisDisplayName = $(this).data('display-name');
                    let currentBoardAssingerID = $(`#${el} .cf-board-assigned-to`).data('user-id');
                    const assigntoText = (currentBoardAssingerID) ? 'Reassign to ' : 'Assign to ';
                    let checkbox = `
                        <div class="cf-assign-to-inner">
                            <label for="${el}-cf-assign-to-user">
                                <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${assigntoText} ${thisDisplayName}</i>
                            </label>
                            <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
                        </div>    
                        <span class="assignMessage">Your @mention will add people to this discussion and send an email.</span>  
                    `;

                    let checkboxFragments = document.createRange().createContextualFragment(checkbox);
                    let appendToSelector = document.querySelector(appendTo);
                    appendToSelector.innerHTML = '';
                    appendToSelector.appendChild(checkboxFragments);
                    $(assignablePopup).remove();
                }
            });

        }
        assignThisToUser();

        // Asignable Email List Template.
        var assignalbeList = function (_self, data) {
            var listItem = '';
            if (data.length > 0) {
                listItem += `<ul class="cf-assignable-list">`;
                data.forEach(function (user) {
                    listItem += `
                    <li data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}">
                        <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                        <div class="cf-user-info">
                            <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${user.role}</small></p>
                        </div>
                    </li>
                    `;
                });
                listItem += `</ul>`
            } else {
                listItem += `<strong class="cf-no-assignee">Sorry! No user found!</strong>`;
            }
            var assignListTemplate = `
                <div class="cf-assignable-list-popup">
                    ${listItem}
                </div>
            `;
            assignListTemplate = DOMPurify.sanitize(assignListTemplate);
            setTimeout(function () {
                $(assignListTemplate).insertAfter(_self + ' .cf-assign-to-inner'); // phpcs:ignore
            }, 200)
        }

        // Show Assiganable Email List
        var showAssingableEmailList = function () {
            var triggerLink = '.js-cf-show-assign-list';
            var textarea = '';
            var appendTo = '';
            var parentBoardClass = '.cls-board-outer';

            $(document.body).on('click', triggerLink, function (e) {
                e.preventDefault();
                var el = $(this).parents(parentBoardClass).attr('id');
                textarea = `#${el} .js-cf-share-comment`;
                appendTo = `#${el} .shareCommentContainer .cf-assign-to`;
                var content = $(textarea).html();

                $(this).removeClass('js-cf-show-assign-list').addClass('js-cf-hide-assign-list');

                // Get the assigner id of the current board.
                let currentBoardAssingerID = $(`#${el} .cf-board-assigned-to`).data('user-id');

                // Checked cached user list first.
                var emailSet = content.match(/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/igm);
                emailSet = new Set(emailSet);
                var emailAddresses = Array.from(emailSet);
                var dataItems = [];
                // Send Ajax Request.
                $.ajax({
                    url: ajaxurl, // eslint-disable-line
                    type: 'post',
                    data: {
                        action: 'cf_get_assignable_user_list',
                        content: content,
                        nonce: adminLocalizer.nonce // eslint-disable-line
                    },
                    beforeSend: function () { },
                    success: function (res) {
                        var data = JSON.parse(res);
                        emailAddresses.forEach(function (email) {
                            var pattern = new RegExp(email)
                            data.forEach(function (item) {
                                var userEmail = item.user_email;
                                var isMatched = userEmail.match(pattern);
                                if (isMatched) {
                                    if (item.ID !== currentBoardAssingerID) {

                                        dataItems.push(item);

                                    }
                                }
                            })
                        });
                        assignalbeList(appendTo, dataItems);
                    }
                })
            })
        }
        showAssingableEmailList();

        // Hide Assignable Email list.
        var hideAssignableEmailList = function () {
            var el = '';
            var triggerLink = '.js-cf-hide-assign-list';
            var parentBoardClass = '.cls-board-outer';
            var assignableListPopup = '';
            $(document.body).on('click', triggerLink, function (e) {
                e.preventDefault();
                el = $(this).parents(parentBoardClass).attr('id');
                assignableListPopup = `#${el} .cf-assignable-list-popup`;
                $(assignableListPopup).remove();
                $(this).removeClass('js-cf-hide-assign-list').addClass('js-cf-show-assign-list');
            })
        }
        hideAssignableEmailList();
        // Open comment box when user redirect from email.
        var openComment = function () {
            var commentedId = adminLocalizer.comment_id; // eslint-disable-line
            $(window).load(function () {
                setTimeout(function () {
                    $(`#${commentedId} .js-edit-comment`).trigger('click');
                    $(`#${commentedId}`).addClass('comment-flash');
                }, 2000);
                setTimeout(function () {
                    $(`#${commentedId}`).removeClass('comment-flash');
                }, 4000);
            })
        }
        openComment();

        // Dealing with content-editable console issue.
        var manageContentEditableConsoleIssue = function () {
            console.error = (function () {
                var error = console.error;
                return function (exception) {
                    if ((exception + '').indexOf('Warning: A component is `contentEditable`') != 0) {
                        error.apply(console, arguments);
                    }
                }
            })();
        }
        manageContentEditableConsoleIssue();

        // Read More Comments
        $(document).on('click', '.readmoreComment, .readlessComment', function () {
            $(this).parents('.commentText').find('.readMoreSpan').toggleClass('active');
        });

        // More options toggle event
        $(document).on("click", ".cls-board-outer .buttons-wrapper", function () {

            if ($(this).hasClass('active')) {
                $(this).toggleClass("active").parents(".commentContainer").siblings().find(".buttons-wrapper").removeClass("active");
            } else {
                $('#md-span-comments .cls-board-outer .buttons-wrapper').removeClass('active');
                $(this).toggleClass("active");
            }

        });
        // Remove active class when click outside
        var removeactiveClassout = function () {
            $('.cls-board-outer .commentContainer .buttons-wrapper').removeClass('active');
        }
        $(document).click(function (event) {
            if (!$(event.target).closest('.cls-board-outer .commentContainer .buttons-wrapper').length) {
                removeactiveClassout();
            }
        });

        // Enable/Disable comment button
        var adjustSuccessButtonStatus = function () {
            $(window).on("load", function () {
                setTimeout(function () {
                    let commentBox = $(".cls-board-outer .board").find(".cf-share-comment");
                    if (commentBox.html() === '') {
                        commentBox.parents(".shareCommentContainer").find(".btn-success").addClass('btn-disabled');
                        commentBox.parents(".commentContainer").find(".save-btn").addClass('btn-disabled');
                    } else {
                        commentBox.parents(".shareCommentContainer").find(".btn-success").removeClass('btn-disabled');
                    }
                }, 2000);
            });
            $(document).on("keyup paste", ".cf-share-comment-wrapper .cf-share-comment", function (e) {
                let commentVal = $(this).html();
                if (32 !== e.which) {
                    if (commentVal === '') {
                        $(this).parents(".shareCommentContainer").find(".btn-success").addClass('btn-disabled');
                        $(this).parents(".commentContainer").find(".save-btn").addClass('btn-disabled');
                    } else {
                        $(this).parents(".shareCommentContainer").find(".btn-success").removeClass('btn-disabled');
                        $(this).parents(".commentContainer").find(".save-btn").removeClass('btn-disabled');
                    }
                }
            });
            $(document).on("click", ".shareCommentContainer .btn-success", function () {
                let commentBox = $(".cls-board-outer .board").find(".cf-share-comment");
                if (commentBox.html() === '') {
                    commentBox.parents(".shareCommentContainer").find(".btn-success").addClass('btn-disabled');
                    commentBox.parents(".commentContainer").find(".save-btn").addClass('btn-disabled');
                } else {
                    commentBox.parents(".shareCommentContainer").find(".btn-success").removeClass('btn-disabled');
                    commentBox.parents(".commentContainer").find(".save-btn").removeClass('btn-disabled');
                }
            });
        }
        //adjustSuccessButtonStatus();

    });

    // On Window Load Event.
    $(window).on('load', function () {
        $('.cid_popup_hover').parents('.wp-block.editor-block-list__block.block-editor-block-list__block').addClass('parent_cid_popup_hover');

        // Storing necessary user info in local storage.
        $.post(ajaxurl, { // eslint-disable-line
            'action': 'cf_get_user'
        }, function (user) {
            user = JSON.parse(user);
            localStorage.setItem("userID", user.id);
            localStorage.setItem("userName", user.name);
            localStorage.setItem("userRole", user.role);
            localStorage.setItem("userURL", user.url);
        });
    });

    $(document).on('click', '.load-more-activity', function () {
        let pointer = $(this).attr('data-pointer');
        let date = $(this).attr('data-date');
        let postID = $(this).attr('data-post');
        let categoryID = $(this).attr('data-category');
        let cpt = $(this).attr('data-cpt');
        let boardPosition = $(this).attr('data-board-position');

        // Get all printed dates.
        var displayedDates = [];
        $('.board-items-day').each(function () {
            displayedDates.push($(this).text());
        });
        displayedDates = displayedDates.join('|');

        $('.load-more-activity').remove();

        $.ajax({
            url: ajaxurl, // eslint-disable-line
            type: 'post',
            data: {
                action: 'cf_get_activities',
                pointer: pointer,
                date: date,
                postID: postID,
                categoryID: categoryID,
                cpt: cpt,
                boardPosition: boardPosition,
                displayedDates: displayedDates,
            },
            success: function (result) {
                $('#cf-dashboard .board-items-main.list-view').append(result);
            }
        });
    });

    function isIntoView(elem) {
        var documentViewTop = $(window).scrollTop();
        var documentViewBottom = documentViewTop + $(window).height();

        var elementTop = $(elem).offset().top;
        var elementBottom = elementTop + $(elem).height();

        return ((elementBottom <= documentViewBottom) && (elementTop >= documentViewTop));
    }

    $(window).scroll(function () {
        if (0 !== $('.load-more-activity').length) {
            if (isIntoView($('.load-more-activity'))) {
                $('.load-more-activity').trigger('click');
            }
        }
    });
    if (multicollab_fs.can_use_premium_code) {
        if (multicollab_fs.is_plan_pro || multicollab_fs.is_plan_plus) {
            $( document ).ready(function() {
                var postType = $('.filter-allpagepost').val();
                if( postType === 'page' ){
                    $('.filter-allcategory').hide();
                } 
            });  
            $(document).on('change', '.filter-allpagepost', function () {
                if( this.value === 'page' ){
                    $('.filter-allcategory').hide();
                }else{
                    $('.filter-allcategory').show();
                }
            });         

            $(document).on('click', '.show_activity_details', function () {
                let postID = $(this).attr('data-id');
                $.ajax({
                    url: ajaxurl, // eslint-disable-line
                    type: 'post',
                    data: {
                        action: 'cf_get_activity_details',
                        postID: postID,
                    },
                    success: function (result) {
                        $("html, body").animate({ scrollTop: 200 });
                        $('.board-items-main.list-view').fadeOut();
                        $('.board-items-main.detail-view').fadeIn();
                        $('#board-item-detail').fadeIn().html(result);
                        $('.bulkactions').hide();
                    }
                });
            });
        }
        $(document).on('click', '#pro-migration-button', function () {
            $('#migration-progress-bar span').attr('data-percentage', '0').css('width', '0%');
            $('#migration-progress-bar').addClass('active').show();
            initMigration(0);
        });

        function migratePostMC(postID) {

            let suggestionsIncluded = false;
            $.ajax({
                url: ajaxurl, // eslint-disable-line
                type: 'post',
                data: {
                    action: 'cf_migrate_to_pro',
                    postID: postID,
                    suggestionsIncluded: suggestionsIncluded,
                },
                success: function (result) {
                    result = JSON.parse(result);
                    if (0 === postID) {
                        let pendingArray = '' !== result.pending ? result.pending.split(',') : [];
                        if (0 !== pendingArray.length) {
                            localStorage.setItem("pendingMigrationPosts", result.pending);

                            $('#migration-progress-info').html('<p>Total ' + pendingArray.length + ' items need to be fixed.</p>');
                            $('#migration-progress-info').append('<p><span id="pending">0</span> out of <span id="total">' + pendingArray.length + '</span> items migrated successfully!</p>');

                            let nextID = pendingArray[0];
                            migratePostMC(nextID);
                        } else {
                            $('#migration-progress-info').append('<p>Everything is upto date.</p>');
                            $('#migration-progress-bar span').attr('data-percentage', 100).css('width', '100%');
                            setTimeout(function () {
                                $('#migration-progress-bar').removeClass('active');
                            }, 4200)
                        }
                    } else {
                        initMigration(result.migratedPost);
                    }
                }
            });
        }

        function initMigration(removePostID) {

            let pendingMigrationPosts = localStorage.getItem("pendingMigrationPosts");
            let arrayPendingMigrationPosts = [];
            if (null !== pendingMigrationPosts) {
                arrayPendingMigrationPosts = pendingMigrationPosts.split(',');

                if (0 !== removePostID) {
                    arrayPendingMigrationPosts = $.grep(arrayPendingMigrationPosts, function (value) {
                        return value != removePostID;
                    });

                    // If this is a second attempt on a fresh load.
                    if (0 === $('#migration-progress-info span').length) {
                        $('#migration-progress-info').html('<p>Total ' + arrayPendingMigrationPosts.length + ' items need to be fixed.</p>');
                        $('#migration-progress-info').append('<p><span id="pending">0</span> out of <span id="total">' + arrayPendingMigrationPosts.length + '</span> items migrated successfully!</p>');
                    }
                    let totalPosts = parseInt($('#migration-progress-info span#total').text());
                    let completedPosts = totalPosts - arrayPendingMigrationPosts.length;
                    $('#migration-progress-info span#pending').text(completedPosts);

                    if (0 === arrayPendingMigrationPosts.length) {
                        $('#migration-progress-info').append('<p>All posts migrated successfully.<p>');
                        localStorage.removeItem('pendingMigrationPosts');
                        $('#migration-progress-bar span').attr('data-percentage', 100).css('width', '100%');
                        setTimeout(function () {
                            $('#migration-progress-bar').removeClass('active');
                        }, 4200)
                        return;
                    }

                    let migratePercentage = completedPosts * 100 / totalPosts;
                    migratePercentage = migratePercentage.toFixed(2);
                    $('#migration-progress-bar span').attr('data-percentage', migratePercentage).css('width', migratePercentage + '%');

                    pendingMigrationPosts = arrayPendingMigrationPosts.join(',');
                    localStorage.setItem("pendingMigrationPosts", arrayPendingMigrationPosts);
                }
            }
            let nextID = null === pendingMigrationPosts ? 0 : arrayPendingMigrationPosts[0];
            migratePostMC(nextID);
        }
    }
    $(document).on('click', '#activity-go-back', function () {
        $('.board-items-main.list-view').fadeIn();
        $('.board-items-main.detail-view').fadeOut();
        $('#board-item-detail').fadeIn().html('');
        $('.bulkactions').show();
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
        $('#' + getTabID).addClass('cf-tab-active').show().siblings().removeClass('cf-tab-active').hide();
    });

    $( document ).ready(function() {
        // Add Testimonial Slider for Free Dashboard
        $('.pricing-testi-slider').bxSlider({
            adaptiveHeight:true
        });
    }); 
 

})(jQuery); // eslint-disable-line

/**
 * Creates a new "attributes" object to update, based on the passed attribute name and final content
 *
 * @param string attributeName The custom Gutenberg block attribute name to be changed
 * @param string finalContent The final content generated by cleaning out the string from mdspan
 * @return {object}
 */
var createNewAttributeWithFinalContent = function (attributeName, finalContent) {
    const conf = {
        attributes: {
        }
    };
    conf.attributes[attributeName] = finalContent;
    return conf;
}

/**
 * Remove the <mdspan> tag from the text.
 *
 * @param sting elIDRemove The ID of the comment thread.
 */
var removeTag = function (elIDRemove) { // eslint-disable-line

    const clientId = jQuery('[datatext="' + elIDRemove + '"]').parents('[data-block]').attr('data-block'); // eslint-disable-line
    var blockType = jQuery('[datatext="' + elIDRemove + '"]').parents('[data-block]').attr('data-type'); // eslint-disable-line
    const findAttributes = window.adminLocalizer.allowed_attribute_tags;
    const blockAttributes = wp.data.select('core/block-editor').getBlockAttributes(clientId); // eslint-disable-line
    
    if('core/gallery' === blockType){
        removeGalleryTag(blockAttributes,clientId,elIDRemove)
    }
    if('core/table' === blockType){
        removeTableTag(blockAttributes,clientId,elIDRemove)
    }
    if (null !== blockAttributes) {

        jQuery(findAttributes).each(function (i, attrb) { // eslint-disable-line
            var content = blockAttributes[attrb];

            if (undefined !== content && -1 !== content.indexOf(elIDRemove)) {

                if ('' !== content) {
                    let tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content; // phpcs:ignore
                    let childElements = tempDiv.getElementsByTagName('mdspan');

                    for (let i = 0; i < childElements.length; i++) {
                        if (elIDRemove === childElements[i].attributes.datatext.value) {
                            //Change logic to keep other HTML Tag in content..only remove mdspan tag

                            var parent = childElements[i].parentNode;

                            while (childElements[i].firstChild) {
                                parent.insertBefore(childElements[i].firstChild, childElements[i]);
                            }
                            parent.removeChild(childElements[i]);

                            const finalContent = tempDiv.innerHTML;

                            if (findAttributes.indexOf(attrb) !== -1) {
                                wp.data.dispatch('core/editor').updateBlock(clientId, createNewAttributeWithFinalContent(attrb, finalContent));
                            }
                            break;
                        }
                    }
                }
            }
        });
    }
}
var removeGalleryTag = function(blockAttributes,clientId,elIDRemove){        
    jQuery('.blocks-gallery-item').each(function(index, el){
        if( jQuery(el).find('figure figcaption').length ){
       blockAttributes.images?.forEach( ( image ) => {
           const caption = image.caption;
           let tempDiv = document.createElement('div');
           tempDiv.innerHTML = caption; // phpcs:ignore
           let childElements = tempDiv.getElementsByTagName('mdspan');
           for (let i = 0; i < childElements.length; i++) {
                if (elIDRemove === childElements[i].attributes.datatext.value) {
                    //Change logic to keep other HTML Tag in content..only remove mdspan tag

                    var parent = childElements[i].parentNode;

                    while (childElements[i].firstChild) {
                        parent.insertBefore(childElements[i].firstChild, childElements[i]);
                    }
                    parent.removeChild(childElements[i]);
                     image.caption = tempDiv.innerHTML;
                    wp.data.dispatch('core/editor').updateBlockAttributes( clientId,{
                        attributes:{
                            images :{
                                id: image.id,
                                caption: image.caption,
                            },
                        },
                    } );
                    
                    break;
                }
            }
        })
       }
    });
}
var removeTableTag = function(blockAttributes,clientId,elIDRemove){
    let table_attrb = ['head','body','foot'];
    jQuery(table_attrb).each(function (i, attrb) {
       
        blockAttributes[attrb]?.forEach( ( tableCells ) => {
        var cells = tableCells.cells;
        cells.forEach(function(data){
            var content = data.content;
          
            if ('' !== content) {
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = content; // phpcs:ignore
                let childElements = tempDiv.getElementsByTagName('mdspan');
                for (let i = 0; i < childElements.length; i++) {
                    if (elIDRemove === childElements[i].attributes.datatext.value) {
                        //Change logic to keep other HTML Tag in content..only remove mdspan tag

                        var parent = childElements[i].parentNode;

                        while (childElements[i].firstChild) {
                            parent.insertBefore(childElements[i].firstChild, childElements[i]);
                        }

                        parent.removeChild(childElements[i]);
                        data.content = tempDiv.innerHTML;
                        wp.data.dispatch('core/editor').updateBlockAttributes( clientId,{
                            attributes:{
                                attrb :{
                                    content:data.content
                                },
                            },
                        } );
                    }
                }
            }

        })
    })
})
}
var getCommentsLimit = function () {
    return 5;
}

/* function for calculating diff of time. */
var timeAgo = function (time) {
   
    try {
        /* for time formats of time in seconds and minutes */
        var templates = {
            prefix: "",
            suffix: " ago",
            seconds: "few seconds",
            minute: "about a minute",
            minutes: "%d minutes"
        };
        /* for time format like hrs + today */
        var forhrsToday = function (timeInHrs) {
            return timeInHrs + " Today";
        }
        var template = function (t, n) {
            return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
        };
       
        if (!time) return;
        /* function for converting timestamp into required format */
        var convertedDatetime = function (date) {
            date = new Date(date * 1000);
            let dateFormat = 'm/d/Y';
            let timeFormat = 'H:i:s';
            let dateTime = wp.date.gmdate(dateFormat + ' ' + timeFormat, date);

            return dateTime;
        }
        var convertedTime = convertedDatetime(time);
        time = new Date(convertedTime * 1000 || convertedTime);
        var now = new Date(convertedDatetime(getTimestampWithTimezone()));
        var timeinDate = String(time).split(" ");
        var dispTime = String(time);
        dispTime = dispTime.match(/\s([A-z]*)\s[0-9]{1,2}/g);
        dispTime = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true }) + dispTime[0];
        if (timeinDate[2] != now.getDate()) {
            if ((now.getTime() - time.getTime()) < 0) {
                return dispTime;
            } else {
                if (timeinDate[2] < now.getDate() && (parseInt(now.getDate()) - parseInt(timeinDate[2])) < 2) {
                    return time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true }) + " Yesterday";
                } else {
                    return dispTime;
                }
            }
        } else {
            if ((now.getTime() - time.getTime()) < 0) {
                return time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true }) + " Today";
            } else {
                var seconds = ((now.getTime() - time) * .001) >> 0;
                var minutes = seconds / 60;
                var hrsFormat = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true });
               // console.log((seconds < 60 && template('seconds', seconds) || minutes < 60 && template('minutes', minutes) || minutes > 60 && forhrsToday(hrsFormat)) + (minutes < 60 ? templates.suffix : ""));
                return templates.prefix + (
                    seconds < 60 && template('seconds', seconds) || minutes < 60 && template('minutes', minutes) || minutes > 60 && forhrsToday(hrsFormat)) + (minutes < 60 ? templates.suffix : "");
            }
        }
    } catch (error) {
        console.log(error);
    }

}

function getTimestampWithTimezone() {
    return Math.floor((new Date()).getTime() / 1000) + (3600 * wp_time_setting.timezoneOffset);
}

function convertedDatetime(date) {
    date = new Date(date * 1000);
    let dateFormat = wp_time_setting ? wp_time_setting.dateFormat : 'F j, Y';
    let timeFormat = wp_time_setting ? wp_time_setting.timeFormat : 'g:i a';
    let dateTime = wp.date.gmdate(timeFormat + ' ' + dateFormat, date);

    return dateTime;
}

function getCurrentUserId() {
    return parseInt(currentUserData.id);
}
function getCurrentUserInfoById() {

    let userInfo = new Array();
    userInfo['id'] = parseInt(currentUserData.id);
    userInfo['username'] = currentUserData.username;
    userInfo['avtarUrl'] = currentUserData.avtarUrl;
    userInfo['role'] = currentUserData.role;

    return userInfo;
}
function scrollBoardToPosition(topOfText) {
    var scrollTopClass = '';
    if (0 !== jQuery('.interface-interface-skeleton__content').length) {
        // Latest WP Version
        scrollTopClass = '.interface-interface-skeleton__content';

    } else if (0 !== jQuery('.block-editor-editor-skeleton__content').length) {
        // Latest WP Version
        scrollTopClass = '.block-editor-editor-skeleton__content';

    } else if (0 !== jQuery('.edit-post-layout__content').length) {
        // Old WP Versions
        scrollTopClass = '.edit-post-layout__content';

    } else {
        // Default
        scrollTopClass = 'body';
    }

    topOfText = topOfText + jQuery(scrollTopClass).scrollTop();

    jQuery(scrollTopClass).animate({
        scrollTop: topOfText - 320
    }, 1000);
}


const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

function filterTextBeforeSave(newText){
    newText = newText.replace( /<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '' ); 
    newText = newText.replace( /<br>/igm, ' <br> ' );
    var link;
    // Adding anchor tag around the linkable text.
    // For bug fixing of semicolon there is a little chnage in regex               
    newText = newText.replace( /<a\s.*?>(.*?)<\/a>/g, function( match ) {   
        return ' '+match+' ';
    });

    // replace nbsp; with space for separate links
    newText = newText.replace( /&nbsp;|&nbsp/igm, ' ' );

    newText = newText.replace( /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/gi, function( match ) {   
        link = match;
        if((link.includes("www.") || link.includes("WWW.") )&& !link.includes("http://") && !link.includes("https://"))  {
          link = link.replace('WWW.','http://')
          link = link.replace('www.','http://')
        }      
        return `<a href="${link}" target="_blank">${match}</a>`;
    }); 

    //remove leading and trailing <br/> 
    if(isSafari){
          newText = newText.replace(/(<div>)/ig, '<br>');
          newText = newText.replace(/(<\/div>)/ig, '');
    }
    newText = newText.replace( /&nbsp;|&nbsp/igm, ' ' );
    newText = newText.replace(/^\s*(?:<br\s*\/?\s*>\s*)+|(?:<br\s*\/?\s*>\s*)+\s*$/gi, ''); 
    newText.trim();
    return newText;
}

function removeLinkFromEditableText(editedValue){

    // Filtering anchor tag and return the url text only.
    // For bug fixing of semicolon there is a little chnage in regex
    // this wont apply over mentioned user link
    editedValue = editedValue.replace( /<a href=\"https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)([^&nbsp;|^<br>])\" target=\"_blank\">https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)([^&nbsp;|^<br>])<\/a>/igm, function( match ) {
        return match.replace( /(<([^>]+)>)/ig, '');
    } );
    
    // regex to remove anchor tag with target
    editedValue = editedValue.replace(/(<a([^>]+)target=([^>]+)>)([^<]+)<\/a>/ig, function( match ) {   
     return match.replace( /(<([^>]+)>)/ig, ''); 
    } );

    editedValue.trim();

    return editedValue;
}

function filterTextForEdit(newText){

    newText = newText.replace(/  +/g, ' ');
    newText = newText.replace( /&nbsp;|&nbsp/igm, ' ' ); 
    newText = newText.replace(/^\s*(?:<br\s*\/?\s*>\s*)+|(?:<br\s*\/?\s*>\s*)+\s*$/gi, ''); 
    newText = newText.replace(/\s/g, '');
    newText = newText.replace( /<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '' );      
    newText = newText.trim();
    return newText;
}

function validateCommentReplyText(newText){

    newText = newText.replace( /<br>/igm, '' );
    newText = newText.replace( /&nbsp;|&nbsp/igm, '' );
    newText = newText.replace( /\s/igm, '' );
    newText = newText.replace( /<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '' );      
    newText = newText.trim();

    return newText;
}


function closeMulticollabSidebar () {
   
    //to close sidebar in gutenberg
    //const isSidebarOpened = wp.data.select( 'core/edit-post' ).isEditorSidebarOpened();
    const sidebarName = wp.data.select( 'core/edit-post' ).getActiveGeneralSidebarName();
    if ( sidebarName === 'cf-activity-center/cf-activity-center' ) { 
         wp.data.dispatch('core/edit-post').closeGeneralSidebar();
    }
}

function getPostSaveStatus(){
    const isNew = wp.data.select('core/editor').isEditedPostNew();
    const isDirty = wp.data.select('core/editor').isEditedPostDirty();
    const isSaving = wp.data.select('core/editor').isSavingPost();
    const isSaved = ( ! isNew && ! isDirty );
    const isSavedState = isSaving || isSaved;
    return isSavedState;
}

function appendInfoBoardDiv(){
    var pinboardNode = document.createElement('div');
    pinboardNode.setAttribute("id", 'md-span-status'); 
    pinboardNode.setAttribute('style','display:none');
    pinboardNode.innerHTML = wp.i18n.__( "You have <span>x</span> <strong>Save Draft</strong> to apply changes." );
    var parentNodeRef = document.getElementById('md-comments-suggestions-parent');
    if (null !== parentNodeRef) {
        parentNodeRef.appendChild(pinboardNode);
    }
}
function showInfoBoardonNewComments(){
 
    appendInfoBoardDiv();

    wp.data.subscribe( function () { 
            
        let pinboard = document.getElementById("md-span-status");  
        if (null === pinboard) {
            appendInfoBoardDiv();
            pinboard = document.getElementById("md-span-status");
        }

        setTimeout( function() { 
            let count = document.querySelectorAll('.draftComment').length;          
            if( pinboard && !getPostSaveStatus() && count > 0  ){  
                pinboard.getElementsByTagName("SPAN")[0].innerHTML = count; 
                pinboard.setAttribute('style','');   
            }
            if( pinboard && count === 0 ){
                pinboard.setAttribute('style','display:none'); 
            }
         }, 300 );

        var isSavingPost              = wp.data.select( 'core/editor' ).isSavingPost();
        var isAutosavingPost          = wp.data.select( 'core/editor' ).isAutosavingPost();
        var didPostSaveRequestSucceed = wp.data.select( 'core/editor' ).didPostSaveRequestSucceed(); 

        if( isSavingPost || isAutosavingPost ){
           //console.log('saving post');
           if( didPostSaveRequestSucceed ) {
               if(null !== pinboard){
                pinboard.setAttribute('style','display:none');
                Array.from(document.querySelectorAll('.draftComment')).forEach((el) => el.classList.remove('draftComment'));
               }
           }
        }

    } );   
}

function getSelectionHtml() {
    var html = "";
    if (typeof window.getSelection != "undefined") {
        var sel = window.getSelection();
        if (sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                container.appendChild(sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;

        }
    } else if (typeof document.selection != "undefined") {
        if (document.selection.type == "Text") {
            html = document.selection.createRange().htmlText;
        }
    }
    return html;
}

jQuery(document).ready(function () {
    if('1' === showinfoboard.showinfoboard){showInfoBoardonNewComments();}
   
});
jQuery(document).ready(function () {
        function freemiusCheckout(planId,licenses){
            // Pricing  version
            let handler;
             handler = FS.Checkout.configure({
                plugin_id:  '8961',
                plan_id:    planId,
                public_key: 'pk_6a91f1252c5c1715f64a8bc814685',
                image:      'https://www.multicollab.com/wp-content/uploads/sites/5/2020/12/commenting-logo.svg'
                });
                
                    handler.open({
                        name     : 'Multicollab',
                        licenses : licenses,
                        // You can consume the response for after purchase logic.
                        purchaseCompleted  : function (response) {
                            // The logic here will be executed immediately after the purchase confirmation.                                // alert(response.user.email);
                        },
                        success  : function (response) {
                            // The logic here will be executed after the customer closes the checkout, after a successful purchase.                                // alert(response.user.email);
                        }
                    });
                    e.preventDefault();
                
        }
        
        jQuery('.free-btn a').on('click', function (e) {
            e.preventDefault();
            freemiusCheckout('15022','');
        } );
        jQuery('.plus-btn a').on('click', function (e) {
            e.preventDefault();
            let licenses = jQuery('#licenses').val();
            freemiusCheckout('15023',licenses);
        } );
        jQuery('.pro-btn a').on('click', function (e) {
            e.preventDefault();
            let licenses = jQuery('#licenses').val();
            freemiusCheckout('15024',licenses);
        } );
    });


function displaySuggestionBoards(){
    wp.domReady( function() {
    wp.data.dispatch('core/editor').editPost({
        meta: { _sb_show_suggestion_boards: false },
    });
});
    jQuery( 'body' ).removeClass( 'hide-sg' ); 
}
function createCommentNode(){
    var parentNode = document.createElement('div');
    parentNode.setAttribute("id", 'md-comments-suggestions-parent');
    var referenceNode = document.querySelector('.block-editor-writing-flow');
    if(null !== referenceNode){
        referenceNode.appendChild(parentNode);
        var commentNode = document.createElement('div');
        commentNode.setAttribute("id", 'md-span-comments');
        var parentNodeRef = document.getElementById('md-comments-suggestions-parent');
        parentNodeRef.appendChild(commentNode);
    }
}