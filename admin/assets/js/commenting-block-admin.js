/**
 * Main function to be called for required JS actions.
 */

(function ($) {
    'use strict';
    const { __ } = wp.i18n;
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
    window.addEventListener('click', function (e) {
        if (jQuery('.edit-post-layout').hasClass('is-sidebar-opened')) {
            jQuery('.interface-interface-skeleton__sidebar').removeClass('cf-sidebar-closed');
        }
        if (document.getElementsByClassName('edit-post-visual-editor').length > 0 && document.getElementsByClassName('edit-post-visual-editor')[0].contains(e.target)) {
            jQuery('.interface-interface-skeleton__sidebar').addClass('cf-sidebar-closed');
            // Clicked in editor
            closeMulticollabSidebar();
        }
    });

    // To remove blank suggestions board after changing block. // @author - Mayank @since3.4
    $(document).on('click', '.cls-board-outer', function (event) {
        const suggestion_id = $(this).attr('data-sid') || '';
        if (suggestion_id) {
            let sgAlignsElements = document.querySelector(`[lock_sg_id="${suggestion_id}"]`) || document.querySelector(`[textAlign_sg_id="${suggestion_id}"]`) || document.querySelector(`[align_sg_id="${suggestion_id}"]`); // For the block level align, lock suggestions @author - Mayank / since 3.6
            let element = document.querySelector(`[suggestion_id="${suggestion_id}"]`) || document.querySelector(`[id="${suggestion_id}"]`) || sgAlignsElements;
            if (!element) {
                $(this).remove();
            }
        }
    });

    /** last suggestion reply button tooltip */
    $(document).on('mouseover', '.shareCommentContainer .btn', function () {
        var boxHeight = jQuery(this).parents(".cls-board-outer").outerHeight() + 30;
        var parentHeight = jQuery("#cf-comments-suggestions__parent").outerHeight() - boxHeight - 50;
        var boxPosition = jQuery(this).parents(".cls-board-outer").css('top');
        if (parentHeight < parseInt(boxPosition) - boxHeight + 30) {
            var currentTop = jQuery(this).parents(".cls-board-outer").css('top');
            currentTop = parseInt(currentTop.replace('px', '')) - 50;
            jQuery(this).parents(".cls-board-outer").css('top', currentTop + 'px');
        }
    });
    /* Trigger to add/remove class from block
       when block level sugggestions are created  */
    /* <fs_premium_only> */
    $(document).on("onBlockLevelSuggestionUpdate", function () {
        const checkHTMLNodes = ['UL', 'FIGURE', 'BLOCKQUOTE', 'DIV', 'NAV'];
        $(".wp-block").each(function () {


            if (($(this).find('.wp-block').length === 0 || $(this).find('.wp-block').length === 2) && $(this).find('.alignupdate').length > 0 || $(this).find('.textalignupdate').length > 0) { // To resolve the border issue #495. @author - Mayank / since 3.5
                $(this).addClass('mdaligned');
                //fix for 5.6
                $(this).attr('data-sgalign', true);
            } else {
                $(this).removeClass('mdaligned');
                //fix for 5.6
                $(this).removeAttr('data-sgalign');
            }

            if (($(this).find('.wp-block').length === 0 || $(this).find('.wp-block').length === 2 || $(this).find('.wp-block').length === 1) && $(this).find('.lockupdate').length > 0) { // To resolve the border issue #495. @author - Mayank / since 3.5
                $(this).addClass('mdformatblockClass');
            } else {
                $(this).removeClass('mdformatblockClass');
            }

            if (!$(this).hasClass('blockAdded') && $(this).attr('data-block')) { // To solve the custom Block add suggestion problem @Author Mayank
                const blockDetail = wp.data.select('core/block-editor').getBlock($(this).attr('data-block'));
                if (blockDetail) {
                    const blockClasses = blockDetail?.attributes?.className;
                    if (blockClasses && blockClasses.includes('blockAdded')) {
                        $(this).addClass(blockClasses);
                    }
                }

            }

            // Code optimized for user id @author - Mayank / since 3.5
            let user_id = '';
            if ($(this).hasClass('blockAdded') || $(this).hasClass('blockremove')) {
                const $spanElement = $(this);
                // Loop through the classes on the span element
                $spanElement.each(function () {
                    const classes = $(this).attr('class').split(' ');
                    $.each(classes, function (i, className) {
                        // Check if the class matches the user_id or unique_id pattern
                        if (className.match(/^user_id-\d+$/)) {
                            // This is a user_id class, get the random number and save it to a variable
                            user_id = className.split('-')[1] ?? '';
                        }
                    });
                });
            }

            if ($(this).find('.wp-block').length === 0 && $(this).hasClass('blockAdded')) {

                if (user_id) {
                    $(this).attr('data-uid', user_id);
                }

                //fix for 5.6
                $(this).attr('data-sgblockAdded', true);

            } else if ($(this).find('.wp-block').length > 0 && checkHTMLNodes.includes($(this).first().prop("nodeName")) && $(this).hasClass('blockAdded')) {

                if (user_id) {
                    $(this).attr('data-uid', user_id);
                }

                //fix for 5.6
                $(this).attr('data-sgblockAdded', true);

            } else {
                //fix for 5.6
                $(this).removeAttr('data-sgblockAdded');
            }

            if ($(this).find('.wp-block').length === 0 && $(this).find('.headingupdate').length > 0) {
                $(this).addClass('mdheading');
                //fix for 5.6
                $(this).attr('data-sgheading', true);
            } else {
                $(this).removeClass('mdheading');
                //fix for 5.6
                $(this).removeAttr('data-sgheading');
            }

            if (!$(this).hasClass('blockremove') && $(this).attr('data-block')) { // To solve the custom Block remove suggestion problem @Author Mayank
                const blockDetail = wp.data.select('core/block-editor').getBlock($(this).attr('data-block'));
                if (blockDetail) {
                    const blockClasses = blockDetail?.attributes?.className;
                    if (blockClasses && blockClasses.includes('blockremove')) {
                        $(this).addClass(blockClasses);
                    }
                }

            }

            if ($(this).find('.wp-block').length === 0 && $(this).hasClass('blockremove')) {
                if (user_id) {
                    $(this).attr('data-uid', user_id);
                }
                $(this).addClass('mdremoved');
                $(this).addClass('mdBlockremoved');
                //fix for 5.6
                $(this).attr('data-sgremove', true);
            } else if ($(this).find('.wp-block').length > 0 && checkHTMLNodes.includes($(this).first().prop("nodeName")) && $(this).hasClass('blockremove')) {
                if (user_id) {
                    $(this).attr('data-uid', user_id);
                }
                $(this).addClass('mdremoved');
                $(this).addClass('mdBlockremoved');
                //fix for 5.6
                $(this).attr('data-sgremove', true);
            } else {
                $(this).removeClass('mdremoved');
                //fix for 5.6
                $(this).removeAttr('data-sgremove');
            }

        });


        //focus remove block or block alignment suggestion when block is focused
        $(".wp-block.focus-visible").each(function () {

            if (($(this).find('.blockremove').length > 0 || $(this).find('.alignupdate').length > 0)
                && $('#cf-span__comments .cls-board-outer.focus').length === 0) {
                var $this = $(this);
                setTimeout(function () {
                    $('#cf-span__comments .cls-board-outer').removeAttr('style');
                    $('#cf-span__comments .cls-board-outer').removeClass('focus');
                    $('#cf-span__comments .cls-board-outer').removeClass('is-open');
                    let suggestionId;
                    if ($this.find('.blockremove').length > 0) {
                        suggestionId = $this.find('.blockremove').attr('id');
                    } else if ($this.find('.alignupdate').length > 0) {
                        suggestionId = $this.find('.alignupdate').attr('id');
                    }
                    $('#sg' + suggestionId).addClass('focus');
                    $('#cf-span__comments .cls-board-outer:not(.focus)').css('opacity', '0.4');
                    $('#sg' + suggestionId).offset({ top: $this.offset().top });
                    wp.data.dispatch('mdstore').setDataText(['sg', suggestionId].join());
                }, 200);
            }
        });
    });


    // Trigger to remove onGoing class from suggestion board after
    // text add or delete is finished
    let timerID;
    $(document).on("removeOngoingClass", function () {
        clearTimeout(timerID);
        timerID = setTimeout(() => {
            $('#cf-span__comments .cls-board-outer.sg-board').removeClass('onGoing');
        }, 1000);
    });
    /* </fs_premium_only> */

    // Display Delete Overlay Box 
    $(document).on('click', '.js-resolve-comment', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .comment-overlay-text").text(__('Delete this thread?', 'content-collaboration-inline-commenting'));
    });

    $(document).on('click', '.resolve-cb', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .comment-overlay-text").text(__('Resolve this thread?', 'content-collaboration-inline-commenting'));
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-delete").text(__('Yes', 'content-collaboration-inline-commenting'));
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-cancel").text(__('No', 'content-collaboration-inline-commenting'));
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
    });

    $(document).on('click', '.js-trash-comment', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay").addClass("show");
    });

    $(document).on('click', '.js-trash-suggestion', function () {
        $(this).parents(".commentContainer").siblings().find(".comment-delete-overlay").removeClass("show");
        $(this).parents(".commentContainer").find(".comment-delete-overlay .comment-overlay-text").text(__('Delete this Suggestion?', 'content-collaboration-inline-commenting'));
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-delete").text(__('Yes', 'content-collaboration-inline-commenting'));
        $(this).parents(".commentContainer").find(".comment-delete-overlay .btn-cancel").text(__('No', 'content-collaboration-inline-commenting'));
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
            $(this).text(__('Show less', 'content-collaboration-inline-commenting'));
        } else {
            $this.closest('.user-commented-on').find(".show-all").addClass('js-hide');
            $this.closest('.user-commented-on').find(".show-less").removeClass('js-hide');
            $(this).text(__('Show more', 'content-collaboration-inline-commenting'));
        }
    });


    /* Hide/Show comments on board trigger   */
    $(document).on("showHideComments", function () {
        if (!$("#cf-span__comments").is(':empty')) {
            $("#cf-span__comments .cls-board-outer:not(.focus)").each(function () {
                let commentCount = parseInt($(this).find('.boardTop .commentContainer').length);

                if (commentCount > getCommentsLimit() && !$(this).hasClass('focus')) {
                    $(this).find('.show-all-comments').html(`${sprintf(__('Show all %d replies', 'content-collaboration-inline-commenting'), commentCount - 1)}`); //phpcs:ignore
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
    $(document).on("click", "#cf-span__comments .cls-board-outer .show-all-comments", function () {
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

    function addBorderToAlignmentBlock(block) {  // Resolved #633 issue @author - Mayank
        var children = block.querySelectorAll('*');
        for (var i = 0; i < children.length; i++) {
          if (children[i].classList.contains('alignupdate') || children[i].classList.contains('textalignupdate') || children[i].classList.contains('lockupdate') || children[i].classList.contains('headingupdate')) {
            block.style.border = '2px solid #188651';
            break;
          }
        }
    }

    /* editor layout update action trigger for commentOn class  */
    $(document).on("editorLayoutUpdate", function () {

        if ($("#cf-span__comments").is(':empty') ||
            ($('body').hasClass('hide-sg') && $('body').hasClass('hide-comments'))
        ) {
            $('body').removeClass("commentOn");
        } else {

            if ((!$('body').hasClass('hide-sg') && $("#cf-span__comments .sg-board").length > 0) ||
                (!$('body').hasClass('hide-comments') && $("#cf-span__comments .cm-board").length > 0)) {
                $('body').addClass("commentOn");
            } else if (($('body').hasClass('hide-comments') && !$('body').hasClass('hide-sg') && $("#cf-span__comments .sg-board").length === 0) ||
                ($('body').hasClass('hide-sg') && !$('body').hasClass('hide-comments') && $("#cf-span__comments .cm-board").length === 0)) {
                $('body').removeClass("commentOn");
            }
        }
        // Resolved #633 issue @author - Mayank
        var browser = (function (agent) {
            switch (true) {
                case agent.indexOf("firefox") > -1: return "firefox";
                default: return "other";
            }
        })(window.navigator.userAgent.toLowerCase());
        if ('firefox' === browser) {
            document.querySelectorAll('.wp-block').forEach(addBorderToAlignmentBlock);  // Resolved #633 issue @author - Mayank
        }
    });

    // alert(  );
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
        let shareUrl = getCookie("current_url");

        if (current_url) {
            urlParams.delete('current_url');
            window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
        }
        if (shareUrl) {
            deleteCookie('current_url');

        }

        $('[datatext="' + boardID + '"]').addClass('is-selected');
        jQuery.event.trigger({ type: "showHideComments" });

    });
    $(document).on('focus', '.cf-share-comment', function () {
        $('.cf-share-comment').addClass('comment-focus');

        // Solved overlap issue for adding comment on suggestion. @author: Rishi Shah.
        $('.cls-board-outer').removeClass('focus onGoing');
        $(this).parent().closest('.cls-board-outer').addClass('focus onGoing');

        // commented for (save button activated when mention user available at last and press arrow keys) /@author Meet Mehta /@since VIP Plan
        //$('.js-cf-edit-comment a.js-mentioned').css('white-space', 'pre-wrap');
        $('.btn-wrapper').css('display', 'block');

        // Add space between suggestion and comment loating board. @author: Rishi Shah.
        $('.cls-board-outer').css('opacity', '0.4');
        //var focusParentElement = $(this).parent().parent().parent().parent().attr('id');
        var focusParentElement = $(this).closest('.cls-board-outer').attr('id');
        $('#' + focusParentElement).css('opacity', '1');
        //$('#' + focusParentElement).css('z-index', '999999');
        $(".cls-board-outer:not(#" + focusParentElement + ")").css('top', '0');

    })
    $(document).on('focusout', '.cf-share-comment', function () {
        $('.cf-share-comment').removeClass('comment-focus');

    })

    $(document).on('click', '.cf-sidebar-settings', function () {
        if ($('body').hasClass('commentOn')) {
            // $('.comment-toggle .components-form-toggle').removeClass('is-checked'); // commented this to resolve comment toggle issue.
            //$('.comment-toggle .components-base-control__help').html('All comments will show on the content area.');
        }
    })

    // Add temporary style tag to hide resolved tag color on load.
    //comment below line to resolved copy url board hilight issue.
    //$('html').prepend('<style id="loader_style">body mdspan{background: transparent !important;}.components-editor-notices__dismissible{display: none !important;</style>');
    // On Document Ready Event.
    $(document).ready(function () {

        // Editor layout width changes sidebar multicollab btn click @author: Minal Diwan @since-3.3
        $(document).on('click', '.interface-pinned-items .components-button,.edit-post-header-toolbar__inserter-toggle', function (e) {
            setTimeout(function () {
                var ediLayot = document.querySelector(".editor-styles-wrapper");
                var cmntLayout = document.querySelector("#cf-comments-suggestions__parent");
                var ediLayotWidth = ediLayot?.offsetWidth;
                var cmntLyotWidth = cmntLayout?.offsetWidth;
                var calcLyotWidth = ediLayotWidth - cmntLyotWidth;
                var editSidebarchck = document.querySelector(".edit-post-layout");
                var blockinsertchck = document.querySelector(".interface-interface-skeleton__body");
                const firstChild = blockinsertchck?.firstElementChild;
                const elid = $('#cf-span__comments').find('.cls-board-outer.focus').attr('id');
                let topOfText;
                function mdboardOffset() {
                    setTimeout(function () {
                        var totalOpenBoardsIds = document.querySelectorAll('.cls-board-outer.is-open');
                        if (totalOpenBoardsIds.length >= 2) {

                            let topOfTextSingleBoardSuggestion;
                            let topOfTextSingleBoardComment;
                            let SuggestionBoardOuterHeight;
                            let singleBoardIdSuggestion;
                            let singleBoardIdComment;
                            let combineBoardId;
                            let counter = 0;
                            let FirstsingleBoardIdSuggestion;
                            let FirstSuggestionBoardOuterHeight;

                            for (var i = 0; i < totalOpenBoardsIds.length; ++i) {
                                var singleBoardId = totalOpenBoardsIds[i].id;
                                if (undefined !== singleBoardId) {
                                    if (singleBoardId.match(/^el/m) === null) {
                                        topOfTextSingleBoardSuggestion = $('#' + singleBoardId + '').offset().top;
                                        singleBoardIdSuggestion = 'sg' + singleBoardId;
                                        if (counter === 0) {
                                            FirstsingleBoardIdSuggestion = 'sg' + singleBoardId;
                                        }
                                        combineBoardId = 'sg' + singleBoardId;
                                        counter++;
                                    } else {
                                        topOfTextSingleBoardComment = $('[datatext="' + singleBoardId + '"]').offset().top;
                                        singleBoardIdComment = singleBoardId;
                                        combineBoardId = singleBoardId;
                                    }
                                }
                                if (FirstsingleBoardIdSuggestion) {
                                    FirstSuggestionBoardOuterHeight = document.querySelector('#' + FirstsingleBoardIdSuggestion)?.offsetHeight;
                                }
                                $('#' + combineBoardId).css('opacity', '1');
                                $('#' + combineBoardId).addClass('is-open');
                                $('#' + combineBoardId).addClass('focus onGoing');

                            }

                            if (document.querySelector('#' + singleBoardIdSuggestion)) {
                                SuggestionBoardOuterHeight = document.querySelector('#' + singleBoardIdSuggestion).offsetHeight;
                                $('#' + singleBoardIdSuggestion).offset({ top: topOfTextSingleBoardSuggestion });

                                // Add floating board adjustment for multi-suggestion. @author: Rishi Shah @since: 3.4
                                if (2 === counter && FirstsingleBoardIdSuggestion) {
                                    $('#' + FirstsingleBoardIdSuggestion).offset({ top: topOfTextSingleBoardSuggestion });
                                    $('#' + singleBoardIdSuggestion).offset({ top: topOfTextSingleBoardSuggestion + FirstSuggestionBoardOuterHeight + 20 });
                                }
                                if (false === $('#' + singleBoardIdComment + ', .board').hasClass('fresh-board')) {
                                    if (2 === counter && FirstsingleBoardIdSuggestion) {
                                        $('#' + singleBoardIdComment).offset({ top: topOfTextSingleBoardSuggestion + SuggestionBoardOuterHeight + FirstSuggestionBoardOuterHeight + 40 });
                                    } else {
                                        $('#' + singleBoardIdComment).offset({ top: topOfTextSingleBoardSuggestion + SuggestionBoardOuterHeight + 20 });
                                    }
                                } else {
                                    $('#' + singleBoardIdComment).offset({ top: topOfTextSingleBoardSuggestion });
                                }
                                // Adding sg-format-class class on selected text. @author: Minal Diwan @since: 3.4
                                //$('#' + singleBoardId).addClass('sg-format-class');
                                var underlineAllAttr = document.querySelectorAll('[data-rich-text-format-boundary="true"]');
                                if (underlineAllAttr) {
                                    for (var singleElement = 0; singleElement < underlineAllAttr.length; ++singleElement) {
                                        if (underlineAllAttr[singleElement].classList.contains('mdadded') || underlineAllAttr[singleElement].classList.contains('mdremoved')) {
                                            if (singleBoardId !== underlineAllAttr[singleElement].id) {
                                                jQuery('#' + underlineAllAttr[singleElement].id).attr('data-rich-text-format-boundary', false);
                                            } else {
                                                jQuery('#' + underlineAllAttr[singleElement].id).attr('data-rich-text-format-boundary', true);
                                            }
                                        } else if (underlineAllAttr[singleElement].parentNode.classList.contains('mdmodified')) {
                                            if (singleBoardId !== underlineAllAttr[singleElement].parentNode.id) {
                                                jQuery('#' + underlineAllAttr[singleElement].parentNode.id).children().attr('data-rich-text-format-boundary', false);
                                            } else {
                                                jQuery('#' + underlineAllAttr[singleElement].parentNode.id).children().attr('data-rich-text-format-boundary', true);
                                            }
                                        } else if (underlineAllAttr[singleElement].classList.contains('mdspan-comment')) {
                                            var suggestionId = underlineAllAttr[singleElement].getAttribute('datatext');
                                            if (singleBoardId !== suggestionId) {
                                                jQuery('[datatext="' + suggestionId + '"]').attr('data-rich-text-format-boundary', false);
                                            } else {
                                                jQuery('[datatext="' + suggestionId + '"]').attr('data-rich-text-format-boundary', true);
                                            }

                                        }
                                    }
                                }
                                scrollBoardToPosition(topOfTextSingleBoardSuggestion);
                            }
                        } else {
                            if ($('#cf-span__comments').find('.cls-board-outer').hasClass('focus')) {
                                if (elid && elid.match(/^el/m) !== null) {
                                    topOfText = $('[datatext="' + elid + '"]').offset().top;
                                } else {
                                    let sid = $('#' + elid).attr('data-sid');
                                    topOfText = $('[id="' + sid + '"]').offset()?.top;
                                    if (!topOfText) {
                                        topOfText = $('[suggestion_id="' + sid + '"]').offset()?.top;
                                    }
                                }
                                $('#cf-span__comments').find('.cls-board-outer.focus').offset({ top: topOfText });
                                scrollBoardToPosition(topOfText);
                            }
                        }

                    }, 800);
                }
                if (editSidebarchck?.classList?.contains('is-sidebar-opened') || firstChild) {
                    mdboardOffset();
                    document.querySelector(".is-root-container.block-editor-block-list__layout").style.width = calcLyotWidth + "px";
                } else {
                    mdboardOffset();
                    document.querySelector(".is-root-container.block-editor-block-list__layout").style.width = calcLyotWidth + "px";
                }
            }, 100);
        });

        // Add loader on setting page loading. @author: Rishi @since-3.0
        $(".cf_settings_loader").delay(100).fadeOut("slow");
        $('body').css('overflow-y', 'unset');

        let doingAjax = false;
        // If thread focused via an activity center,
        // it is in lock mode, so clicking any para
        // would unlock it.
        $(document).on('click', '.block-editor-block-list__layout .wp-block', function (e) {

            if ($('.cls-board-outer').hasClass('locked')) {

                // Reset Comments Float. This will reset the positions of all comments.
                $('#cf-span__comments .cls-board-outer').css('opacity', '1');
                $('#cf-span__comments .cls-board-outer').removeClass('focus');
                $('#cf-span__comments .cls-board-outer').removeClass('is-open');
                $('#cf-span__comments .cls-board-outer').removeAttr('style');
                $('#cf-span__comments .cls-board-outer .buttons-wrapper').removeClass('active');

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

        // Dashboard features popup /@Minal Diwan Version 3.0
        $(document).on('click', '.cf-board-overlap-feature .cf-board-overlapbox', function () {
            //  e.preventDefault();
            $('.cf-board-overlapboxhover').not($(this).find('.cf-board-overlapboxhover')).hide();
            $(this).find('.cf-board-overlapboxhover').toggle();
        });


        // Save General Settings.
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
        // Uncheck other suggestion setting if select one.
        $('input.cf_suggestion_stop_publish_options').on('change', function () {
            $('input.cf_suggestion_stop_publish_options').not(this).prop('checked', false);
        });

        // On change event for suggestion mode options.
        $(document).on('change', 'input.cf_suggestion_mode_options', function () {

            //Uncheck other options.
            $('input.cf_suggestion_mode_options').not(this).prop('checked', false);

            var val = $('input.cf_suggestion_mode_options:checked').val();
            if (val) {
                if ('cf_suggestion_specific_post_categories' === val) {
                    //jQuery('.cf_specific_post_categories_section').css('display', 'block');
                    jQuery('.cf_specific_post_categories_section').show();
                } else {
                    jQuery('.cf_specific_post_categories_section').hide();
                }

                if ('cf_suggestion_specific_post_types' === val) {
                    //jQuery('.cf_specific_post_categories_section').css('display', 'block');
                    jQuery('.cf_specific_post_type_section').show();
                } else {
                    jQuery('.cf_specific_post_type_section').hide();
                }

            } else {
                jQuery('.cf_specific_post_categories_section').hide();
                jQuery('.cf_specific_post_type_section').hide();
            }
        });

        // On change event for real-time mode options.
        $(document).on('change', 'input.cf_websocket_options', function () {

            //Uncheck other options.
            $('input.cf_websocket_options').not(this).prop('checked', false);

            var val = $('input.cf_websocket_options:checked').val();
            if( 'cf_websocket_custom' === val ) {
                jQuery('#cf_multiedit_websocket').prop("disabled", false);
                document.getElementById('cf_multiedit_websocket').setAttribute('required', 'required');
                
            } else {
                jQuery('#cf_multiedit_websocket').prop("disabled", true);
                document.getElementById('cf_multiedit_websocket').removeAttribute('required');
            }
        });

        // Save Publishing Settings.
        $('#cf_suggestion_settings').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_suggestions',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                $('#cf_suggestion_settings .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf_suggestion_settings .cf-success').slideUp(300);
                }, 3000);
            });
        });

        // Save Publishing Settings.
        $('#cf_email_notification').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_email_notification',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                $('#cf_email_notification .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf_email_notification .cf-success').slideUp(300);
                }, 3000);
            });
        });

        $('.cf-specific-post-categories-multiple').select2({
            placeholder: __('Please select a category', 'content-collaboration-inline-commenting')
        });

        $('.cf-specific-post-type-multiple').select2({
            placeholder: __('Please select a post type', 'content-collaboration-inline-commenting')
        });

        // Save Suggestion Mode.
        $('#cf_suggestion_mode').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_suggestions_mode',
                'formData': $(this).serialize()
            };

            $.post(ajaxurl, settingsData, function (data) { // eslint-disable-line
                if ('saved' === data) {
                    $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                    $('#cf_suggestion_mode .cf-success').slideDown(300);
                    setTimeout(function () {
                        $('#cf_suggestion_mode .cf-success').slideUp(300);
                    }, 3000);
                } else {
                    $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                    $('#cf_suggestion_mode .cf-error').slideDown(300);
                    setTimeout(function () {
                        $('#cf_suggestion_mode .cf-error').slideUp(300);
                    }, 3000);
                }
            });
        });

        $(document).on('click', '.reset-filter', function () {
            $(this).parent().find('select').prop('selectedIndex', 0);
            $(this).parent().submit();
        });
        // Uncheck other suggestion setting if select one.
        $('input.cf_suggestion_stop_publish_options').on('change', function () {
            $('input.cf_suggestion_stop_publish_options').not(this).prop('checked', false);
        });

        // Save Settings for slack intigration.
        $('#cf_slack_intigration').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_slack_intigration',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                $('#cf-slack-notice .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf-slack-notice .cf-success').slideUp(300);
                }, 3000);
            });
        });

        // Add slect2 for channel selectbox.
        jQuery('#cf_slack_channels').select2({
            templateResult: formatState,
            templateSelection: formatState
        });

        function formatState(opt) {
            if (!opt.id) {
                return opt.text.toLowerCase();
            }

            var optimage = $(opt.element).attr('data-image');
            if (!optimage) {
                return opt.text.toLowerCase();
            } else {
                var $opt = $(
                    '<span><img src="' + optimage + '" width="12px" /> ' + opt.text.toLowerCase() + '</span>'
                );
                return $opt;
            }
        };



        // Slack test intigration.
        $('#cf-slack-integration-disconnect').on('click', function (e) {

            var hidden_site_url = jQuery('.hidden_site_url').val();

            const settingsData = {
                'action': 'cf_slack_intigration_revoke',
            };
            $.post(ajaxurl, settingsData, function (data) { // eslint-disable-line
                if ('ok' === data) {
                    jQuery('.cf-slack-integration-button').html('<a href="https://slack.com/oauth/v2/authorize?client_id=3297732204756.3694963903943&scope=incoming-webhook,chat:write,commands&user_scope=groups:write,channels:read,groups:read,channels:write&state=' + hidden_site_url + '" class="cf-slack-integration-connect">' + __('Connect', 'content-collaboration-inline-commenting') + '</a>'); // phpcs:ignore
                    jQuery('.cf_slack_channel_setting').hide();
                }
            });
        });

        // Activate license.
        $('#cf_license').on('submit', function (e) {
            e.preventDefault();

            jQuery('.cf-license-notices').hide();
            jQuery('.cf-license-notices').html('');
            jQuery('.cf-license-sucess').hide();
            jQuery('.cf-license-sucess').html('');
            jQuery('#cf-license-activator-submit').attr('disabled', "disabled");

            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_license_activation',
                'formData': $(this).serialize()
            };
            $.post(ajaxurl, settingsData, function (response) { // eslint-disable-line
                if ('invalid' === response) {
                    jQuery('.cf-license-notices').html('<div class="cf-notice notice notice-error is-dismissible"><p>The license failed to activate, due to a status of <code>invalid</code>.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-notices').show();
                    jQuery('.license_check_status').hide();

                } else if ('deactivated' === response) {

                    jQuery('#cf-license-activator').val('');

                    // jQuery('.cf-license-sucess').html('<span>License key is deactivated.</span>');
                    // jQuery('.cf-license-sucess').show();
                    window.location.href += '&view=license';
                    // Change button text.
                    edd_change_active_license_text();

                } else if ('expired' === response) {

                    jQuery('#cf-license-activator').val('');

                    jQuery('.cf-license-sucess').html('<div class="cf-notice notice notice-error is-dismissible"><p>License key is expired please try with different key.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-sucess').show();

                    // Change button text.
                    edd_change_active_license_text();

                } else if ('revoked' === response) {

                    jQuery('#cf-license-activator').val('');

                    jQuery('.cf-license-sucess').html('<div class="cf-notice notice notice-error is-dismissible"><p>Your license key has been disabled.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-sucess').show();

                    // Change button text.
                    edd_change_active_license_text();

                } else if ('missing' === response) {

                    jQuery('#cf-license-activator').val('');

                    jQuery('.cf-license-sucess').html('<div class="cf-notice notice notice-error is-dismissible"><p>Invalid license.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-sucess').show();

                    // Change button text.
                    edd_change_active_license_text();

                } else if ('no_activations_left' === response) {

                    jQuery('#cf-license-activator').val('');

                    jQuery('.cf-license-sucess').html('<div class="cf-notice notice notice-error is-dismissible"><p>Your license key has reached its activation limit.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-sucess').show();

                    // Change button text.
                    edd_change_active_license_text();

                } else if ('site_inactive' === response) {

                    jQuery('#cf-license-activator').val('');

                    jQuery('.cf-license-sucess').html('<div class="cf-notice notice notice-error is-dismissible"><p>Your license is not active for this URL.<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button></p></div>');
                    jQuery('.cf-license-sucess').show();

                    // Change button text.
                    edd_change_active_license_text();

                } else {

                    // jQuery('.cf-license-sucess').html('<span>License key is activated.</span>');
                    // jQuery('.cf-license-sucess').show();

                    jQuery('#cf-license-deactivate-submit').val('Deactivate License');
                    jQuery('#cf-license-deactivate-submit').attr('data-activate', 'Deactivate License');
                    jQuery('#cf_license_action').val('deactivate');
                    jQuery('.license_check_status').show();
                    window.location.href += '&view=license';

                }

                jQuery('#cf-license-activator-submit').removeAttr('disabled');

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

        // Save Real-Time Co-editing settings.
        $('#cf_multiedit_mode').on('submit', function (e) {
            e.preventDefault();
            $(this).find('[type="submit"]').addClass('loading');
            const settingsData = {
                'action': 'cf_save_multiedit_settings',
                'formData': $(this).serialize(),
            };
            $.post(ajaxurl, settingsData, function () { // eslint-disable-line
                $('.cf-cnt-box-body').find('[type="submit"]').removeClass('loading');
                $('#cf_multiedit_mode .cf-success').slideDown(300);
                setTimeout(function () {
                    $('#cf_multiedit_mode .cf-success').slideUp(300);
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

        $(document).on('click', '#cf-span__comments .cls-board-outer:not(.focus)', function (e) {
            closeMulticollabSidebar();
            // Exclude focus on specific elements.
            var target = $(e.target);
            if (target.is(".commentContainer .comment-actions, .commentContainer .comment-actions *")) {
                return;
            }
            const _this = $(this);
            // Reset Comments Float.
            $('#cf-span__comments .cls-board-outer').removeAttr('style');
            $('#cf-span__comments .cls-board-outer').removeClass('focus');
            $('#cf-span__comments .cls-board-outer').removeClass('is-open');
            $('#cf-span__comments .comment-delete-overlay').removeClass('show');
            $('#cf-span__comments .comment-resolve .resolve-cb').prop("checked", false);
            $('#cf-span__comments .cls-board-outer .buttons-wrapper').removeClass('active');
            $('#cf-span__comments .cls-board-outer').css('opacity', '0.4');
            let realTimeMode = wp.data.select('core/editor').getEditedPostAttribute('meta')?._is_real_time_mode ;
            if(true !== realTimeMode){
                $('.btn-wrapper').css('display', 'none');
            }

            const selectedText = _this.attr('id');
            const currentUser = wp.data.select('core').getCurrentUser()?.id;
            if(realTimeMode){
               var hide = commentLock(selectedText, currentUser);
               if(hide){
                return;
               }
            }
            removeFloatingIcon();
            _this.addClass('focus');
            _this.addClass('is-open');
            _this.css('opacity', '1');

            
            let topOfText;
            if (selectedText.match(/^el/m) !== null) {
                topOfText = $('[datatext="' + selectedText + '"]').offset().top;
                if ($('[datatext="' + selectedText + '"]').hasClass('cf-icon-wholeblock__comment')) { // To support block suggetion and whole block comment @author - Mayank / since 3.5
                    $('[datatext="' + selectedText + '"]').addClass('focus');
                }
            } else {
                let sid = $('#' + selectedText).attr('data-sid');
                topOfText = $('[id="' + sid + '"]').offset()?.top;
                if (!topOfText) { // To support block suggetion and whole block comment @author - Mayank / since 3.5
                    topOfText = $('[suggestion_id="' + sid + '"]').offset()?.top;
                    $('[data-suggestion_id="' + sid + '"]').addClass('focus');
                }
                $('#' + sid).addClass('sg-format-class');

            }

            setTimeout(function () {
                scrollBoardToPosition(topOfText);
                _this.offset({ top: topOfText });
            }, 800);

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

        //Set board top offset from activity center @author: Minal Diwan @since-3.4
        $(document).on('click', '.user-commented-on,.cf-activity-centre .user-action a', function (e) {
            var elID = e.target.dataset.elid;
            if (elID) {
                elID = elID.replace('cf-', '');
                $(`#${elID}`).trigger('click');
                setTimeout(function () {
                    let topOfText;
                    if (elID.match(/^el/m) !== null) {
                        topOfText = $('[datatext="' + elID + '"]').offset().top;
                    } else {
                        let sid = $('#' + elID).attr('data-sid');
                        topOfText = $('[id="' + sid + '"]').offset()?.top;
                        if (!topOfText) {
                            topOfText = $('[suggestion_id="' + sid + '"]').offset()?.top;
                        }
                        $('#' + sid).addClass('sg-format-class');

                    }
                    $('#cf-span__comments .cls-board-outer').removeAttr('style');
                    $('#cf-span__comments .cls-board-outer').removeClass('focus');
                    $('#cf-span__comments .cls-board-outer').removeClass('is-open');
                    $('#cf-span__comments .cls-board-outer').css('opacity', '0.4');

                    let realTimeMode = wp.data.select('core/editor').getEditedPostAttribute('meta')?._is_real_time_mode ;
                    const currentUser = wp.data.select('core').getCurrentUser()?.id;
                    if(realTimeMode){
                       var hide = commentLock(elID, currentUser);
                       if(hide){
                        return;
                       }
                    }

                    $('#' + elID + '.cls-board-outer').addClass('focus');
                    $('#' + elID + '.cls-board-outer').addClass('is-open');
                    $('#' + elID + '.cls-board-outer').css('opacity', '1');


                    scrollBoardToPosition(topOfText);

                    $('#' + elID + '.cls-board-outer').offset({ top: topOfText });

                }, 800);
            }
        });
        
        // Function for comment Lock
        var commentLock = function (selectedText, currentUser) {
            document.querySelector('.comment-lock')?.remove();
            const activeUsers = wp.data.select("multiedit/block-collab/add-block-selections").getState();
            var hide = false;
            
            for (let i = 0; i < activeUsers.length; i++) {
                const comment = activeUsers[i];
                if (comment.commentId === selectedText && comment.userId !== currentUser) {
                    const commentInnerContainer = document.querySelector('#' + selectedText + ' .commentInnerContainer');
                    const commentHeader = commentInnerContainer.querySelector('.comment-header');
                    const commentLock = document.createElement('div');
                    commentLock.setAttribute("class", "comment-lock");
                    commentLock.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 18 18"><path fill="none" stroke="currentColor" stroke-width="1.5" d="M16.25 16.25v-6.5a1.5 1.5 0 0 0-1.5-1.5h-9.5a1.5 1.5 0 0 0-1.5 1.5v6.5a1 1 0 0 0 1 1h10.5a1 1 0 0 0 1-1Zm-10-8V6a3.75 3.75 0 1 1 7.5 0v2.25"></path></svg>';
                    commentInnerContainer.style.pointerEvents = 'none';
                    commentInnerContainer.insertBefore(commentLock, commentHeader);
                    var noticeMsg = __(
                    `${comment.username} is adding comment to same thread, please try after some time.`,
                    "content-collaboration-inline-commenting"
                    );
                    document.getElementById("cf-board__notice").innerHTML = noticeMsg;
                    document
                    .getElementById("cf-board__notice")
                    .setAttribute("style", "display:block");
                    setTimeout(function () {
                    document
                        .getElementById("cf-board__notice")
                        .setAttribute("style", "display:none");
                    document.getElementById("cf-board__notice").innerHTML = "";
                    }, 3000);
                    hide = true;
                    
                }
            }
            return hide;
        } 

        // Email List Template Function.
        var emailList = function (appendTo, data) {
            setTimeout(function () {
                var listItem = '';
                if (data.length > 0) {
                    data.forEach(function (user, listIndex) {
                        if (listIndex == 0) {
                            listItem += `
                            <li class="cf-user-list-item active" role="option" data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                                <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                                <div class="cf-user-info">
                                    <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${userroleDisplay(user.role)}</small></p>
                                </div>
                            </li>`;
                        } else {
                            listItem += `
                            <li class="cf-user-list-item" role="option" data-user-id="${user.ID}" data-email="${user.user_email}" data-display-name="${user.display_name}" data-full-name="${user.full_name}">
                                <img src="${user.avatar}" alt="${user.display_name}" width="24" height="24" />
                                <div class="cf-user-info">
                                    <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${userroleDisplay(user.role)}</small></p>
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
            }, 100);
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

                    }else if (range.commonAncestorContainer.ownerDocument.activeElement === editableDiv) {
                        console.log('sel', sel.focusOffset);
                        caretPos = sel.focusOffset;
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
        var insertDisplayName = function (setRange, email, userId, fullName, displayName) {

            var gapElContent = document.createTextNode("\u00A0"); // Adding whitespace after the name.
            var anchor = document.createElement('a');

            var splitDisplayName = displayName.split(' ');
            anchor.setAttribute('contenteditable', false);
            anchor.setAttribute('href', `mailto:${email}`);
            anchor.setAttribute('title', fullName);
            anchor.setAttribute('data-email', email);
            anchor.setAttribute('class', 'js-mentioned');
            anchor.setAttribute('data-display-name', displayName.substr(1));
            anchor.setAttribute('data-user-id', userId);
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
        /*var formatPastedContent = function () {
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
        formatPastedContent();*/
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
                // Issue no. 579 solved At mention functionality. @author: Nirav Soni.
                if ('create' === mood && '' === createTextarea) {
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
            if ('firefox' === browser || 'safari' === browser) {
                $(document.body).on("keydown", '.cf-share-comment', function (e) {
                    if (e.keyCode == 13 && !e.shiftKey) {
                        document.execCommand('insertHTML', false, '<br><br>');
                        return false;
                    }
                });
            }

            // Clearing out assignable dom on edit save or edit cancel.
            // remove body from document.body - modified by /@author Meet Mehta/@since VIP Plan
            $(document).on('click', `${currentBoardID} .js-cancel-comment, ${currentBoardID} .save-btn`, function () {
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
                removeFloatingIcon();
            });
            /**
             * ========================================
             * Triggering textarea keyup event.
             * ========================================
             */
            $(document.body).on('keyup keypress', createTextarea, function (e) {

                var _self = $(createTextarea);
                var that = this;
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

                                doingAjax = true;
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

                                            // code - added by meet - solution of assign to when delete
                                            $(assignCheckBoxId).next('i').text(`${sprintf(__('Assign to %s', 'content-collaboration-inline-commenting'), appendInCheckbox[0].display_name)}`);
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

                // If @ is pressed and shiftkey is true.remove true === e.shiftKey to support swiss keyboard.

                // var charCode = (e.which) ? e.which : e.keyCode;
                // if (String.fromCharCode(charCode).match(/[^0-9]/g)){
                //     return false;
                // }


                var keynum;
                if (window.event) { // IE                  
                    keynum = e.keyCode;
                } else if (e.which) { // Netscape/Firefox/Opera                 
                    keynum = e.which;
                }

                if ('@' === String.fromCharCode(keynum) || '@' === e.key || 50 === e.which && (typedText && typedText.length > 0) && $(createTextarea).is(':focus') === true && '2' !== e.key) { // Removed 'KeyG' === e.code consition in first or consitions. @author: Rishi Shah.
                    doingAjax = true;
                    // Fetch all email list.
                    doingAjax = false;
                    var prevCharOfEmailSymbol;
                    mentioncounter++;
                    var showSuggestionFunc;
                    if (undefined !== typedText) {
                        prevCharOfEmailSymbol = typedText.substr(-1, 1);

                        if ('@' === prevCharOfEmailSymbol) {
                            showSuggestionFunc = showSuggestion(prevCharOfEmailSymbol);
                        } else {
                            var index = typedText.indexOf("@");
                            var preText = typedText.charAt(index);

                            if (preText.indexOf(" ") > 0 || preText.length > 0) {
                                var words = preText.split(" ");
                                var prevWords = (words[words.length - 1]);
                            }
                            if ('@' === prevWords || ('keypress' === e.type && '@' === String.fromCharCode(keynum))) {
                                prevWords = '@';
                                showSuggestionFunc = showSuggestion(prevWords);
                            }

                        }
                    }
                    if (showSuggestionFunc && mentioncounter <= 1 && !doingAjax) {
                        if ('keypress' === e.type || 'keyup' === e.type) {
                            mentioncounter = 0;
                        }
                        if ('keypress' === e.type && true === $(createTextarea).is(':focus')) {
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
                                    mentioncounter = 0;  // Issue solved for add 2 mentions continues without space. @author: Rishi Shah.
                                }
                            })
                        }
                    } else {
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                    }
                }

                if ('keypress' !== e.type && 'Backspace' === e.key && (typedText && typedText.length > 0)) {
                    let currentTextAt = typedText.substr(-1, 1);
                    var sel = document.getSelection();
                    var selNodeChar = sel?.baseNode?.data?.charAt(sel.anchorOffset - 1) || sel?.anchorNode?.data?.charAt(sel.anchorOffset - 1);
                    var startTextAt = sel?.baseNode?.data?.charAt(0) || sel?.anchorNode?.data?.charAt(0);
                    
                    if ('@' === startTextAt || '@' === currentTextAt || '@' === typedText.charAt(cursorPos - 1) || '@' === selNodeChar) {
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
                                mentioncounter = 0;  // Issue solved for add 2 mentions continues without space. @author: Rishi Shah.
                            }
                        })
                    }
                }

                if ((32 === e.which) || (13 === e.which) || (8 === e.which)) {
                    mentioncounter = 0;
                }

                // Issue solved after backspace and last work @. @author: Rishi Shah.
                if ('Backspace' === e.key) {
                    if (!$(createTextarea).text()) {
                        trackedStr = '';
                    }
                }

                if (true === isEmail && (typedText && typedText.length > 0) && $(createTextarea).is(':focus') === true) {
                    var checkKeys = function (key) {
                        if (key === e.key) {
                            return true;
                        }
                        return false;
                    }

                    if (!keysToAvoid.find(checkKeys)) {

                        // Check for backspace.
                        if ('keypress' !== e.type) {
                            if ('Backspace' === e.key) {
                                //alert("single backspace");
                                let prevCharOfEmailSymbol = typedText.substr(-1, 1);
                                //trackedStr = '';
                                if ('@' === prevCharOfEmailSymbol) {
                                    //trackedStr = '';
                                    trackedStr = '@'; // Issue solved after backspace and last work @. @author: Rishi Shah.
                                } else {
                                    trackedStr = trackedStr.slice(0, -1);
                                }
                            } else if (50 === e.which) {
                                if ('@' !== trackedStr) {
                                    trackedStr += '@';
                                }
                            } else {
                                if (50 !== e.which) {
                                    trackedStr += e.key;
                                }
                            }
                        }

                        //trackedStr.replace("@@", "@");

                        // Check for ctrl+backspace.
                        // if ('Backspace' === e.key && true === e.ctrlKey) {
                        //     //alert("Cntl backspace");
                        //     let prevCharOfEmailSymbol = typedText.substr(-1, 1);
                        //     //trackedStr = '';
                        //     if ('@' === prevCharOfEmailSymbol) {

                        //         if ('' !== typedText) {
                        //             trackedStr = '@';
                        //         } else {
                        //             trackedStr = '';
                        //         }
                        //     } else {
                        //         trackedStr = '';
                        //     }
                        // }
                    }
                    if (13 === e.which) {
                        $(appendIn).remove();
                        $(assignablePopup).remove();
                    }
                    doingAjax = false;
                    // If trackedStr contains other chars with @ as well.
                    if ('@' !== trackedStr && $(createTextarea).is(':focus') === true) {

                        let checkEmailSymbol = trackedStr.match(/^@\w+$/ig);
                        if (checkEmailSymbol && cursorPos != 0) {
                            var refinedCachedusersList = [];
                            let niddle = trackedStr.substr(1);
                            if ('' !== niddle && niddle.length > 2) {
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
                // Replace '>' with '<' for UP and DOWN arrow key not working in Comment/Reply Board /@author Meet Mehta /@since VIP Plan
                if ([38, 40].indexOf(e.keyCode) < -1) {
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
                        var userId = $(this).find('.cf-user-list-item:eq( ' + index + ' )').attr("user-id");
                        // Insert Display Name.
                        insertDisplayName(range, email, userId, fullName, displayName, createTextarea);
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
                        // commented this below line because of bug fixing of board gets shifted issue #997. @author - Nirav Soni Since-4.3 
                        //$(".cf-share-comment").focus();
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
                    var userId = $(this).data("user-id");
                    // Insert Display Name.
                    insertDisplayName(range, email, userId, fullName, displayName, createTextarea);

                    var typedContent = $(createTextarea).html();
                    // Remove @ before display name anchor tag and insterted in to anchor tag
                    // commented this below line because of bug fixing of <br> removing after user tag
                    //typedContent = typedContent.replace(/[<]br[^>]*[>]<a/gim,"<a");
                    typedContent = typedContent.replace(/@<a/g, '<a');
                    typedContent = typedContent.replace(/<\/?span .[^>]*>/g, '');
                    typedContent = typedContent.replaceAll("</span>", "");

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
            let checkbox;
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
                const assigntoText = (currentBoardAssinger) ? __('Reassign to', 'content-collaboration-inline-commenting') : __('Assign to', 'content-collaboration-inline-commenting');
                
                checkbox = `
                <div class="cf-assign-to">
                <div class="cf-assign-to-inner">
                    <label for="${el}-cf-assign-to-user">
                        <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(__('%1$s %2$s', 'content-collaboration-inline-commenting'), assigntoText, thisDisplayName)}</i>
                    </label>
                    <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                </div>
                <span class="assignMessage">${__('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting')}</span>     
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
                let checkbox;
                let thisUserId = $(this).data('user-id');
                let thisDisplayName = $(this).data('display-name');
                let thisUserEmail = $(this).data('email');
                let currentBoardAssinger = $(`#${el} .cf-board-assigned-to`).data('user-id');
                const assigntoText = (currentBoardAssinger) ? __('Reassign to', 'content-collaboration-inline-commenting') : __('Assign to', 'content-collaboration-inline-commenting');
                
                let assignToElement = $(`#${el} ${checkBoxContainer}`);
                if (assignToElement.length === 0) {
                    checkbox = `
                    <div class="cf-assign-to">
                    <div class="cf-assign-to-inner">
                        <label for="${el}-cf-assign-to-user">
                            <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(__('%1$s %2$s', 'content-collaboration-inline-commenting'), assigntoText, thisDisplayName)}</i>
                        </label>
                        <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                    </div>
                    <span class="assignMessage">${__('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting')}</span>     
                    </div>`;
                    // Get the assigner id of the current board. @author - Nirav Soni Since-4.0.1 
                    
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
                }
                
                //change assignee message when checkbox selected
                
                $(document).on("click", '#' + el + '-cf-assign-to-user', function () {
                    var checked = $('#' + el + ' .cf-assign-to-user').is(':checked');
                    $('#' + el + ' .assignMessage').text(checked ? __('The Assigned person will be notified and responsible for marking as done.', 'content-collaboration-inline-commenting') : __('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting'));
                });
                
            });

             // paste comment text with check assign user @author - Nirav Soni Since-4.0.1
            $(document.body).on('keyup paste', '.js-cf-share-comment, .js-cf-edit-comment', function (e) {

                if( $('.js-cf-share-comment a').hasClass('js-mentioned') || $('.js-cf-edit-comment a').hasClass('js-mentioned') ){
                    
                    let checkbox;
                    let thisUserId = $(this).parents('.shareCommentContainer').find('a.js-mentioned').data('user-id');
                    let thisDisplayName = $(this).parents('.shareCommentContainer').find('a.js-mentioned').data('display-name');
                    let thisUserEmail = $(this).parents('.shareCommentContainer').find('a.js-mentioned').data('email');
                    let currentBoardAssinger = $(`#${el} .cf-board-assigned-to`).data('user-id');
                    const assigntoText = (currentBoardAssinger) ? __('Reassign to', 'content-collaboration-inline-commenting') : __('Assign to', 'content-collaboration-inline-commenting');

                    let assignToElement = $(`#${el} ${checkBoxContainer}`);
                    if (assignToElement.length === 0) {
                        checkbox = `
                        <div class="cf-assign-to">
                        <div class="cf-assign-to-inner">
                            <label for="${el}-cf-assign-to-user">
                                <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(__('%1$s %2$s', 'content-collaboration-inline-commenting'), assigntoText, thisDisplayName)}</i>
                            </label>
                            <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
                        </div>
                        <span class="assignMessage">${__('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting')}</span>     
                        </div>`;

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
                    }

                    //change assignee message when checkbox selected

                    $(document).on("click", '#' + el + '-cf-assign-to-user', function () {
                        var checked = $('#' + el + ' .cf-assign-to-user').is(':checked');
                        $('#' + el + ' .assignMessage').text(checked ? __('The Assigned person will be notified and responsible for marking as done.', 'content-collaboration-inline-commenting') : __('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting'));
                    });
               }
            })

            // On Assignable Email Click.
            $(document.body).on('click', '.cf-assignable-list li', function (e) {

                if ($(this).parents(parentBoardClass).hasClass('cm-board')) {
                    // e.preventDefault();
                    el = $(this).parents(parentBoardClass).attr('id');
                    let checkbox;
                    let appendTo = `#${el} .cf-assign-to`;
                    let assignablePopup = `#${el} .cf-assignable-list-popup`;
                    let thisUserId = $(this).data('user-id');
                    let thisUserEmail = $(this).data('email');
                    let thisDisplayName = $(this).data('display-name');
                    let currentBoardAssingerID = $(`#${el} .cf-board-assigned-to`).data('user-id');
                    const assigntoText = (currentBoardAssingerID) ? __('Reassign to', 'content-collaboration-inline-commenting') : __('Assign to', 'content-collaboration-inline-commenting');
                    
                    checkbox = `
                        <div class="cf-assign-to-inner">
                            <label for="${el}-cf-assign-to-user">
                                <input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(__('%1$s %2$s', 'content-collaboration-inline-commenting'), assigntoText, thisDisplayName)}</i>
                            </label>
                            <span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
                        </div>    
                        <span class="assignMessage">${__('Your @mention will add people to this discussion and send an email.', 'content-collaboration-inline-commenting')}</span>  
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
                            <p class="cf-user-display-name">${user.display_name} <small class="cf-user-role">${userroleDisplay(user.role)}</small></p>
                        </div>
                    </li>
                    `;
                });
                listItem += `</ul>`
            } else {
                listItem += `<strong class="cf-no-assignee"> ${__('Sorry! No user found!', 'content-collaboration-inline-commenting')} </strong>`;
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
        // manageContentEditableConsoleIssue();

        // Read More Comments
        $(document).on('click', '.readmoreComment, .readlessComment', function () {
            $(this).parents('.commentText').find('.readMoreSpan').toggleClass('active');
        });

        // More options toggle event
        $(document).on("click", ".cls-board-outer .buttons-wrapper", function () {

            if ($(this).hasClass('active')) {
                $(this).toggleClass("active").parents(".commentContainer").siblings().find(".buttons-wrapper").removeClass("active");
            } else {
                $('#cf-span__comments .cls-board-outer .buttons-wrapper').removeClass('active');
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
        $(document).on("click", ".slack-accordion-settings", function () {
            $(this).parents(".cf-cnt-box-body").find(".cf-slack-inner-integration-box").slideToggle();
        });


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
                $('#cf-dashboard .board-items-main.list-view').append(result); //phpcs:ignore
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
                $('#board-item-detail').fadeIn().html(result); //phpcs:ignore
                $('.bulkactions').hide();
            }
        });
    });

    $(document).on('click', '.cf-tabs li:not(.cf_subscription_tab)', function () {
        var dataId = jQuery('.cf-tab-active a').attr('data-id');
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var current_url = urlParams.get('page');
        var curruntUrl = location.protocol + '//' + location.host + location.pathname + '?page=' + current_url;

        if ('cf-dashboard' === dataId) {
            window.location.href = curruntUrl + '&view=web-activity';
        } else if ('cf-reports' === dataId) {
            window.location.href = curruntUrl + '&view=post-activity';
        } else if ('cf-settings' === dataId) {
            window.location.href = curruntUrl + '&view=settings';
        } else if ('cf-roles-slack-integration' === dataId) {
            window.location.href = curruntUrl + '&view=intigrations';
        }
    });

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

    $(document).ready(function () {

        //Show hide notification Tooltip /@author Minal Diwan/@since VIP Plan
        $(document).on('click', '.md-plugin-tooltip svg', function (e) {
            e.preventDefault();
            $('.cf_suggestion-tooltip-box').not($(e.target).parents('.md-plugin-tooltip').find('.cf_suggestion-tooltip-box')).hide();
            $(e.target).parents('.md-plugin-tooltip').find('.cf_suggestion-tooltip-box').toggle();
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
    var prefixAcf = 'acf/';
    
    if ('core/gallery' === blockType) {
        removeGalleryTag(blockAttributes, clientId, elIDRemove)
    }
    if ('core/table' === blockType) {
        removeTableTag(blockAttributes, clientId, elIDRemove)
    }
    if ( blockType.startsWith( prefixAcf ) ) {
        removeAcfTag(blockAttributes, clientId, elIDRemove)
    }

    if (null !== blockAttributes && !blockType.startsWith( prefixAcf ) ) {

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
                                parent.insertBefore(childElements[i].firstChild, childElements[i]); //phpcs:ignore
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

var removeAcfTag = function (blockAttributes, clientId, elIDRemove) {
    const updatedAttributes = {
        data: deepCopy(blockAttributes.data), // Ensure that the original object is not mutated
    };

    let targetObject = null;

    // Recursive function to traverse nested objects
    const checkAndRemoveDatatext = (obj, parentObject, parentKey) => {
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                const value = obj[key];

                // Check if the attribute value contains both 'mdspan' and 'datatext'
                if (typeof value === 'string' && value.includes('<mdspan') && value.includes('datatext="' + elIDRemove + '"')) {
                    // Use DOM manipulation to remove mdspan tags only for the specific datatext value
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = value;

                    const mdspans = tempDiv.querySelectorAll('mdspan[datatext="' + elIDRemove + '"]');
                    for (let i = 0; i < mdspans.length; i++) {
                        const mdspan = mdspans[i];
                        // Replace mdspan with its content (keeping only the text content)
                        mdspan.parentNode.replaceChild(document.createTextNode(mdspan.textContent), mdspan);
                    }
                    targetObject = parentObject;

                    var $currentElement = jQuery('[datatext="' + elIDRemove + '"]');
                    var $currentMdspan = $currentElement.closest('mdspan');
                    $currentMdspan.replaceWith($currentMdspan.contents());

                    const matchnum = key.match(/(\d+)_/);
                    const numericValue = matchnum ? matchnum[1] : null;
                    const matchResult = key.match(/\d+_(.+)/);
                    const dataName = matchResult ? matchResult[1] : key;

                    let selector = key.startsWith('field_') ? `[data-key="${key}"]` : `[data-name="${dataName}"]`;
                    if (parentKey !== null) {
                        selector += `${selector} input[type="text"][id*="${parentKey}"], ${selector} textarea[id*="${parentKey}"]`;
                    } else {
                        if (numericValue !== null) {
                            selector += `${selector} input[type="text"][id*="row-${numericValue}"], ${selector} textarea[id*="row-${numericValue}"]`;
                        } else {
                            selector += `${selector} input[type="text"], ${selector} textarea`;
                        }
                    }
                    const $inputField = jQuery(selector);
                    $inputField.val(tempDiv.innerHTML);

                    obj[key] = tempDiv.innerHTML;

                } else if (typeof value === 'object') {
                    // If the value is an object, recursively check and remove datatext
                    checkAndRemoveDatatext(value, obj, key);
                }
            }
        }
    };

    // Start the recursive check
    checkAndRemoveDatatext(updatedAttributes.data, null, null);

    // Update block attributes if needed
    wp.data.dispatch('core/block-editor').updateBlockAttributes(clientId, updatedAttributes);

    return targetObject;
}

// Deep copy function to avoid mutating the original object
function deepCopy(obj) { 
    if( obj ) {
        return JSON.parse(JSON.stringify(obj));
    } else {
        return obj;
    }
}

var removeGalleryTag = function (blockAttributes, clientId, elIDRemove) {
    jQuery('.blocks-gallery-item').each(function (index, el) {
        if (jQuery(el).find('figure figcaption').length) {
            blockAttributes.images?.forEach((image) => {
                const caption = image.caption;
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = caption; // phpcs:ignore
                let childElements = tempDiv.getElementsByTagName('mdspan');
                for (let i = 0; i < childElements.length; i++) {
                    if (elIDRemove === childElements[i].attributes.datatext.value) {
                        //Change logic to keep other HTML Tag in content..only remove mdspan tag

                        var parent = childElements[i].parentNode;

                        while (childElements[i].firstChild) {
                            parent.insertBefore(childElements[i].firstChild, childElements[i]); //phpcs:ignore
                        }
                        parent.removeChild(childElements[i]);
                        image.caption = tempDiv.innerHTML;
                        wp.data.dispatch('core/editor').updateBlockAttributes(clientId, {
                            attributes: {
                                images: {
                                    id: image.id,
                                    caption: image.caption,
                                },
                            },
                        });

                        break;
                    }
                }
            })
        }
    });
}
var removeTableTag = function (blockAttributes, clientId, elIDRemove) {
    let table_attrb = ['head', 'body', 'foot'];
    jQuery(table_attrb).each(function (i, attrb) {

        blockAttributes[attrb]?.forEach((tableCells) => {
            var cells = tableCells.cells;
            cells.forEach(function (data) {
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
                                parent.insertBefore(childElements[i].firstChild, childElements[i]); //phpcs:ignore
                            }

                            parent.removeChild(childElements[i]);
                            data.content = tempDiv.innerHTML;
                            wp.data.dispatch('core/editor').updateBlockAttributes(clientId, {
                                attributes: {
                                    attrb: {
                                        content: data.content
                                    },
                                },
                            });
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
            suffix: wp.i18n.__(' ago', 'content-collaboration-inline-commenting'),
            seconds: wp.i18n.__('few seconds', 'content-collaboration-inline-commenting'),
            minute: wp.i18n.__('about a minute', 'content-collaboration-inline-commenting'),
            minutes: wp.i18n.__('%d minutes', 'content-collaboration-inline-commenting')
        };
        /* for time format like hrs + today */
        var forhrsToday = function (timeInHrs) {
            return timeInHrs + ' ' + wp.i18n.__('Today', 'content-collaboration-inline-commenting');
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
        let newtime;
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
                    newtime = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true });
                    newTimeString = translateTimeString(newtime) + ' ' + wp.i18n.__('Yesterday', 'content-collaboration-inline-commenting');
                    return newTimeString;
                } else {
                    return dispTime;
                }
            }
        } else {
            if ((now.getTime() - time.getTime()) < 0) {
                newtime = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true });
                newTimeString = translateTimeString(newtime) + ' ' + wp.i18n.__('Today', 'content-collaboration-inline-commenting');
                return newTimeString;
            } else {
                var seconds = ((now.getTime() - time) * .001) >> 0;
                var minutes = seconds / 60;
                var hrsFormat = translateTimeString(time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true }));
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

function translateTimeString(time) {

    let newtime = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true });
    splitarray = newtime.split(" ");
    newTimeString = splitarray[0] + ' ' + wp.i18n.__(splitarray[1], 'content-collaboration-inline-commenting');
    return newTimeString;
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

    jQuery(scrollTopClass).stop(true).animate({
        scrollTop: topOfText - 320
    }, 1000);
}

function removeFloatingIcon() {
    var floatingNode = document.getElementsByClassName('cf-floating__wrapper');

    if (floatingNode.length > 0) {
        jQuery(floatingNode).remove()
    }
}

function translateStringFormat(str) {
    let translatedStringFormats = str;
    let splitString, concatBreakedString, removedHTMLTag;
    if (translatedStringFormats?.includes("Space (")) {
        splitString = translatedStringFormats.split(" ");
        translatedStringFormats = sprintf('%s %s %s', wp.i18n.__(splitString[0], 'content-collaboration-inline-commenting'), splitString[1], wp.i18n.__(splitString[2], 'content-collaboration-inline-commenting'));
    } else if (translatedStringFormats?.includes("Remove Link with URL")) {
        splitString = translatedStringFormats.split("Remove Link with URL ");
        translatedStringFormats = sprintf('%s %s', wp.i18n.__('Remove Link with URL', 'content-collaboration-inline-commenting'), splitString[1]);
    } else if (translatedStringFormats?.includes("with URL")) {
        splitString = translatedStringFormats.split("with URL ");
        translatedStringFormats = sprintf('%s %s', wp.i18n.__('with URL', 'content-collaboration-inline-commenting'), splitString[1]);
    } else if (translatedStringFormats?.includes("Replace")) {
        splitString = translatedStringFormats.split(" ");
        translatedStringFormats = sprintf('%s %s %s %s', wp.i18n.__('Replace', 'content-collaboration-inline-commenting'), splitString[1], wp.i18n.__('to', 'content-collaboration-inline-commenting'), splitString[3]);
    } else if (translatedStringFormats?.includes("Line Break (")) {
        splitString = translatedStringFormats.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];
        translatedStringFormats = sprintf('%s %s %s', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), splitString[2], wp.i18n.__(splitString[3], 'content-collaboration-inline-commenting'));
    } else if (translatedStringFormats?.includes("Block Alignment")) {
        removedHTMLTag = translatedStringFormats.replace(/<[^>]*>/g, '');
        splitString = removedHTMLTag.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];
        translatedStringFormats = sprintf('%s <em>%s</em> %s <em>%s</em>', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString[2], 'content-collaboration-inline-commenting'), wp.i18n.__(splitString[3], 'content-collaboration-inline-commenting'), wp.i18n.__(splitString[4], 'content-collaboration-inline-commenting'));
    } else if (translatedStringFormats?.includes("Change Heading")) {
        removedHTMLTag = translatedStringFormats.replace(/<[^>]*>/g, '');
        splitString = removedHTMLTag.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];
        translatedStringFormats = sprintf('%s <em> %s %s </em> %s <em> %s %s </em>', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString[3], 'content-collaboration-inline-commenting'), splitString[4], wp.i18n.__(splitString[5], 'content-collaboration-inline-commenting'), wp.i18n.__(splitString[7], 'content-collaboration-inline-commenting'), splitString[8]);
    }
    str = translatedStringFormats;
    return str;
}

const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);

function filterTextBeforeSave(newText) {
    newText = newText.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '');
    newText = newText.replace(/<br>/igm, ' <br> ');
    if (isSafari || isChrome) {
        newText = newText.replace(/<div>/igm, ' <br> ');
        newText = newText.replace(/<\/div>/igm, '');
        newText = newText.replace(/<\/?span[^>]*>/g, ''); // Resolved #512 multiline comment bug @author - Mayank / since 3.6
    }
    var link;
    // Adding anchor tag around the linkable text.
    // For bug fixing of semicolon there is a little chnage in regex   

    newText = newText.replace(/<a\s.*?>(.*?)<\/a>/g, function (match) {

        return ' ' + match + ' ';
    });

    // replace nbsp; with space for separate links
    //newText = newText.replace(/&nbsp;|&nbsp/igm, ' ');



    //newText = newText.replace(/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/gi, function (match) {
    newText = newText.replace(/(?!]*>[^<])(((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?))(?![^<]*<\/a>)/gi, function (match) {
        link = match;
        if ((link.includes("www.") || link.includes("WWW.")) && !link.includes("http://") && !link.includes("https://")) {
            link = link.replace('WWW.', 'http://')
            link = link.replace('www.', 'http://')
        }

        return `<a href="${link}" target="_blank">${match}</a>`;
    });


    newText = newText.replace(/&nbsp;|&nbsp/igm, ' ');
    newText = newText.replace(/^\s*(?:<br\s*\/?\s*>\s*)+|(?:<br\s*\/?\s*>\s*)+\s*$/gi, '');
    newText.trim();

    return newText;
}

function removeLinkFromEditableText(editedValue) {

    // Filtering anchor tag and return the url text only.
    // For bug fixing of semicolon there is a little chnage in regex
    // this wont apply over mentioned user link
    editedValue = editedValue.replace(/<a href=\"https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)([^&nbsp;|^<br>])\" target=\"_blank\">https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)([^&nbsp;|^<br>])<\/a>/igm, function (match) {
        return match.replace(/(<([^>]+)>)/ig, '');
    });

    // regex to remove anchor tag with target
    editedValue = editedValue.replace(/(<a([^>]+)target=([^>]+)>)([^<]+)<\/a>/ig, function (match) {
        return match.replace(/(<([^>]+)>)/ig, '');
    });

    editedValue.trim();

    return editedValue;
}

function filterTextForEdit(newText) {

    newText = newText.replace(/  +/g, ' ');
    // Added for remove br and div for line breaks and button gets enable/@author Meet Mehta /@since VIP Plan
    if (isSafari || isChrome) {
        newText = newText.replace(/<\/?div[^>]*>/g, '');
        newText = newText.replace(/<\/?span[^>]*>/g, '');
    }
    newText = newText.replace(/&nbsp;|&nbsp/igm, ' ');
    newText = newText.replace(/^\s*(?:<br\s*\/?\s*>\s*)+|(?:<br\s*\/?\s*>\s*)+\s*$/gi, '');
    newText = newText.replace(/\s/g, '');
    newText = newText.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '');
    newText = newText.trim();
    return newText;
}

function validateCommentReplyText(newText) {

    if (isSafari || isChrome) {
        newText = newText.replace(/<div>/igm, ' <br> ');
        newText = newText.replace(/<\/div>/igm, '');
    }
    newText = newText.replace(/<br>/igm, '');
    newText = newText.replace(/&nbsp;|&nbsp/igm, '');
    newText = newText.replace(/\s/igm, '');
    newText = newText.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '');
    newText = newText.trim();

    return newText;
}



// display ... if multiple user exists.
function userroleDisplay(myStr) {
    let newRole;
    let myRole = myStr.split(",");
    if (myRole.length > 1) {
        newRole = myRole[0] + '...';
    } else {
        newRole = myRole[0];
    }
    return newRole;
}

function closeMulticollabSidebar() {

    //to close sidebar in gutenberg
    //const isSidebarOpened = wp.data.select( 'core/edit-post' ).isEditorSidebarOpened();
    const sidebarName = wp.data.select('core/edit-post').getActiveGeneralSidebarName();
    if (sidebarName === 'cf-activity-center/cf-activity-center') {
        wp.data.dispatch('core/edit-post').closeGeneralSidebar();
    }
}

function getPostSaveStatus() {
    const isNew = wp.data.select('core/editor').isEditedPostNew();
    const isDirty = wp.data.select('core/editor').isEditedPostDirty();
    const isSaving = wp.data.select('core/editor').isSavingPost();
    const isSaved = (!isNew && !isDirty);
    const isSavedState = isSaving || isSaved;
    return isSavedState;
}

function appendInfoBoardDiv() {
    var postStatusLabel = getPostStatuslabel();
    var pinboardNode = document.createElement('div');
    pinboardNode.setAttribute("id", 'cf-span__status');
    pinboardNode.setAttribute("class", 'cf-board__notice');
    pinboardNode.setAttribute('style', 'display:none');
    pinboardNode.innerHTML = sprintf('%s <span>x</span> %s <br> %s <strong>%s</strong> %s', wp.i18n.__('You have', 'content-collaboration-inline-commenting'), wp.i18n.__('unsaved comments.', 'content-collaboration-inline-commenting'), wp.i18n.__('Click', 'content-collaboration-inline-commenting'), wp.i18n.__(postStatusLabel, 'content-collaboration-inline-commenting'), wp.i18n.__('to save.', 'content-collaboration-inline-commenting'));
    var parentNodeRef = document.getElementById('cf-comments-suggestions__parent');
    if (null !== parentNodeRef) {
        parentNodeRef.appendChild(pinboardNode);
    }
}

function getPostStatuslabel() {

    var postStatus = wp.data.select('core/editor').getEditedPostAttribute('status');
    var postStatusLabel = '';

    if( postStatus === 'private' ){
        postStatusLabel = 'Privately Published'; 
    }else if( postStatus === 'publish' ){
        postStatusLabel = 'Published / Update';
    }else if( postStatus === 'future' || postStatus === 'scheduled' ){
        postStatusLabel = 'Scheduled';
    }else if( postStatus === 'draft' ){
        postStatusLabel = 'Save draft';
    }else if( postStatus === 'pending' ){
        postStatusLabel = 'Pending Review';
    }else{
        postStatusLabel = postStatus;
    }

    return postStatusLabel;
}

function showInfoBoardonNewComments() {

    appendInfoBoardDiv();

    wp.data.subscribe(function () {

        let pinboard = document.getElementById("cf-span__status");
        if (null === pinboard) {
            appendInfoBoardDiv();
            pinboard = document.getElementById("cf-span__status");
        }

        setTimeout(function () {
            let count = document.querySelectorAll('.draftComment').length;
            if (pinboard && !getPostSaveStatus() && count > 0) {
                var postStatusLabel = getPostStatuslabel();
                pinboard.getElementsByTagName("SPAN")[0].innerHTML = count; //phpcs:ignore
                pinboard.getElementsByTagName("STRONG")[0].innerHTML = postStatusLabel; //phpcs:ignore
                pinboard.setAttribute('style', '');
            }
            if (pinboard && count === 0) {
                pinboard.setAttribute('style', 'display:none');
            }

        }, 300);

        var isSavingPost = wp.data.select('core/editor').isSavingPost();
        var isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
        var didPostSaveRequestSucceed = wp.data.select('core/editor').didPostSaveRequestSucceed();

        if (isSavingPost || isAutosavingPost) {
            if (didPostSaveRequestSucceed) {
                if (null !== pinboard) {
                    pinboard.setAttribute('style', 'display:none');
                    Array.from(document.querySelectorAll('.draftComment')).forEach((el) => el.classList.remove('draftComment'));
                }
            }
        }

    });
}

function appendNoticeBoardDiv() {
    var noticeboardNode = document.createElement('div');
    noticeboardNode.setAttribute("id", 'cf-board__notice');
    noticeboardNode.setAttribute("class", 'cf-board__notice');
    noticeboardNode.setAttribute('style', 'display:none');
    noticeboardNode.innerHTML = 'default notice here';
    var parentNodeRef = document.getElementById('cf-comments-suggestions__parent');
    if (null !== parentNodeRef) {
        parentNodeRef.appendChild(noticeboardNode);
    }
}

function showNoticeBoardonNewComments() {
    appendNoticeBoardDiv();
    wp.data.subscribe(function () {

        let noticeboard = document.getElementById("cf-board__notice");
        if (null === noticeboard) {
            appendNoticeBoardDiv();
            noticeboard = document.getElementById("cf-board__notice");
        }
        setTimeout(function () {
            if (noticeboard !== null) {
                // if( noticeboard.innerHTML === "" ){
                //     noticeboard.setAttribute('style','display:none');
                // } else {
                //    noticeboard.setAttribute('style','display:block');
                // }
            }

        }, 300);

    });
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
function cf_removeAllNotices() {
    var notices = wp.data.select('core/notices').getNotices();
    if (undefined !== notices) {
        notices.forEach(function (data) {
            wp.data.dispatch('core/notices').removeNotice(data.id);
        })
    }

}
jQuery(document).ready(function () {
    if ('1' !== showinfoboard.showinfoboard) { showInfoBoardonNewComments(); }
});


function displaySuggestionBoards() {

    wp.domReady(function () {
        wp.data.dispatch('core/editor').editPost({
            meta: { _sb_show_suggestion_boards: false },
        });
    });

    jQuery('body').removeClass('hide-sg');
}
function createCommentNode() {
    var parentNode = document.createElement('div');
    parentNode.setAttribute("id", 'cf-comments-suggestions__parent');
    var referenceNode = document.querySelector('.block-editor-writing-flow');
    if (null !== referenceNode) {
        referenceNode.appendChild(parentNode);
        var commentNode = document.createElement('div');
        commentNode.setAttribute("id", 'cf-span__comments');
        var parentNodeRef = document.getElementById('cf-comments-suggestions__parent');
        parentNodeRef.appendChild(commentNode);
    }
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
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function deleteCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}
function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function fetchBoardsCommonCode() {
    let selectedNontextblock = [];
      let selectedDataText;
  
      // ====== FOR Add block suggestion functionality. @author Mayank Jain since 3.4
      jQuery(".wp-block").each(function () {
        let uniqueId;
        if ( jQuery(this).hasClass("blockAdded") ) {
            uniqueId = jQuery(this).attr("suggestion_id");
        } else if( jQuery(this).hasClass("blockremove") ) {
            uniqueId = $(this).attr("suggestion_id");
        } else if (jQuery(this).attr("align_sg_id") !== undefined) {
            uniqueId = jQuery(this).attr("align_sg_id");
        } else if (jQuery(this).attr("textAlign_sg_id") !== undefined) {
            uniqueId = jQuery(this).attr("textAlign_sg_id");
        } else if (jQuery(this).attr("lock_sg_id") !== undefined) {
            uniqueId = jQuery(this).attr("lock_sg_id");
        }
        selectedNontextblock.push(uniqueId);
      });
  
      jQuery(
        ".commentIcon, .wp-block mdspan,.cf-onwhole-block__comment, .wp-block .mdadded, .wp-block .mdmodified, .wp-block .mdremoved"
      ).each(function () {
        if (
          jQuery(this).hasClass("mdadded") ||
          jQuery(this).hasClass("mdremoved") ||
          jQuery(this).hasClass("mdmodified")
        ) {
          selectedDataText = $(this).attr("id");
          if (
            jQuery(this).has("suggestion_id") &&
            jQuery(this).hasClass("cf-onwhole-block__comment")
          ) {
            selectedDataText = jQuery(this).attr("datatext");
          }
        } else {
          selectedDataText = jQuery(this).attr("datatext");
        }
        selectedNontextblock.push(selectedDataText);
      });
  
      return selectedNontextblock;
  
}
  
  function floatCommentsBoard(selectedText) {

    jQuery('.cls-board-outer').removeClass('focus');
    jQuery('.cls-board-outer').removeClass('is-open');
    jQuery('.cls-board-outer').removeClass('onGoing');
    jQuery('.cf-icon__addBlocks, .cf-icon__removeBlocks').removeClass('focus');
    jQuery('.cf-icon-wholeblock__comment,.cf-onwhole-block__comment').removeClass('focus');
    
    jQuery('#cf-span__comments .comment-delete-overlay').removeClass('show');
    jQuery('#cf-span__comments .comment-resolve .resolve-cb').prop("checked", false);
    jQuery('#cf-span__comments .cls-board-outer').css('opacity', '0.4');

    var singleBoardId = selectedText;
    let topOfTextSingleBoard;
    let singleBoardIdWithSg;
    
    if(undefined !== singleBoardId ){
        if (singleBoardId.match(/^el/m) === null) {
            if(document.querySelector(`[suggestion_id="${singleBoardId}"]`)) {
                topOfTextSingleBoard = jQuery(`[suggestion_id="${singleBoardId}"]`).offset()?.top;
            } else if(document.querySelector(`[align_sg_id="${singleBoardId}"]`)) {     // Added for the align block level suggestions @author - Mayank / since 3.6
                topOfTextSingleBoard = jQuery(`[align_sg_id="${singleBoardId}"]`).offset()?.top;
            } else if(document.querySelector(`[textAlign_sg_id="${singleBoardId}"]`)) { // Added for the text align block level suggestions @author - Mayank / since 3.6
                topOfTextSingleBoard = jQuery(`[textAlign_sg_id="${singleBoardId}"]`).offset()?.top;
            } else if(document.querySelector(`[lock_sg_id="${singleBoardId}"]`)) { // Added for the lock level suggestions @author - Mayank / since 3.6
                topOfTextSingleBoard = jQuery(`[lock_sg_id="${singleBoardId}"]`).offset()?.top;
            } else {
                topOfTextSingleBoard = jQuery('#' + singleBoardId + '').offset()?.top;
            }
            singleBoardIdWithSg = 'sg' + singleBoardId;

            // Add active class on activity bar. @author: Rishi Shah @since: 3.4
            jQuery(`#cf-sg${singleBoardId}`).addClass('active');
        } else {
            topOfTextSingleBoard = jQuery('[datatext="' + singleBoardId + '"]').offset().top;
            singleBoardIdWithSg = singleBoardId;
        }
    }
    
    jQuery('#' + singleBoardIdWithSg).css('opacity', '1');
    jQuery('#' + singleBoardIdWithSg + '.cls-board-outer').addClass('is-open');
    jQuery('#' + singleBoardIdWithSg).addClass('focus onGoing');
    jQuery('#' + singleBoardIdWithSg).offset({ top: topOfTextSingleBoard });

    var underlineAllAttr = document.querySelectorAll('[data-rich-text-format-boundary="true"]');
    if( underlineAllAttr ) {
        for (var singleElement = 0; singleElement < underlineAllAttr.length; ++singleElement) {
            if( underlineAllAttr[singleElement].classList.contains('mdadded') || underlineAllAttr[singleElement].classList.contains('mdremoved') ) {
                if( singleBoardId !== underlineAllAttr[singleElement].id ) {
                    jQuery( '#' + underlineAllAttr[singleElement].id ).attr('data-rich-text-format-boundary', false);
                } else {
                    jQuery( '#' + underlineAllAttr[singleElement].id ).attr('data-rich-text-format-boundary', true);
                }
            } else if( underlineAllAttr[singleElement].parentNode.classList.contains('mdmodified') ) {
                if( singleBoardId !== underlineAllAttr[singleElement].parentNode.id ) {
                    jQuery( '#' + underlineAllAttr[singleElement].parentNode.id ).children().attr('data-rich-text-format-boundary', false);
                } else {
                    jQuery( '#' + underlineAllAttr[singleElement].parentNode.id ).children().attr('data-rich-text-format-boundary', true);
                }
            } else if( underlineAllAttr[singleElement].classList.contains('mdspan-comment') ) {
                var suggestionId = underlineAllAttr[singleElement].getAttribute('datatext');
                if( singleBoardId !== suggestionId ) {
                    jQuery('[datatext="'+suggestionId+'"]').attr('data-rich-text-format-boundary', false);
                } else {
                    jQuery('[datatext="'+suggestionId+'"]').attr('data-rich-text-format-boundary', true);
                }
                
            }
        }
    }
    scrollBoardToPosition(topOfTextSingleBoard);   
}

function showNoticeMsg() {
    const { __ } = wp.i18n;
    var noticeMsg = __(
        "Multiple comments are not possible on the same block.",
        "content-collaboration-inline-commenting"
    );
    document.getElementById("cf-board__notice").innerHTML = noticeMsg;
    document
        .getElementById("cf-board__notice")
        .setAttribute("style", "display:block");
    setTimeout(function () {
        document
            .getElementById("cf-board__notice")
            .setAttribute("style", "display:none");
        document.getElementById("cf-board__notice").innerHTML = "";
    }, 3000);
}

function nonTextNoticeMsg() {
    const { __ } = wp.i18n;
    var noticeMsg = __(
        "Please Select a Text",
        "content-collaboration-inline-commenting"
    );
    document.getElementById("cf-board__notice").innerHTML = noticeMsg;
    document
        .getElementById("cf-board__notice")
        .setAttribute("style", "display:block");
    setTimeout(function () {
        document
            .getElementById("cf-board__notice")
            .setAttribute("style", "display:none");
        document.getElementById("cf-board__notice").innerHTML = "";
    }, 3000);
}