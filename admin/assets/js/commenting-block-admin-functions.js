/**
 * Removes the specified classes from the elements matching the given selector.
 *
 * @param {string} selector - The CSS selector to match the elements.
 * @param {string} classes - The classes to be removed from the elements.
 */
function cfRemoveClass(selector, classes) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(function(element) {
        element.classList.remove(...classes.split(' '));
    });
}

/**
 * Returns the current timestamp adjusted for the timezone offset.
 * @returns {number} The timestamp with timezone offset.
 */
function getTimestampWithTimezone() {
    return Math.floor((new Date()).getTime() / 1000) + (3600 * wp_time_setting.timezoneOffset);
}

/**
 * Converts a timestamp to a formatted date and time string.
 *
 * @param {number} date - The timestamp to convert.
 * @returns {string} The formatted date and time string.
 */
function convertedDatetime(date) {
    date = new Date(date * 1000);
    let dateFormat = wp_time_setting ? wp_time_setting.dateFormat : 'F j, Y';
    let timeFormat = wp_time_setting ? wp_time_setting.timeFormat : 'g:i a';
    let dateTime = wp.date.gmdate(timeFormat + ' ' + dateFormat, date);

    return dateTime;
}

/**
 * Translates a given time string into a localized format.
 * 
 * @param {Date} time - The time to be translated.
 * @returns {string} - The translated time string.
 */
function translateTimeString(time) {
    let newtime = time.toLocaleString('en-US', { minute: 'numeric', hour: 'numeric', hour12: true });
    splitarray = newtime.split(" ");
    newTimeString = splitarray[0] + ' ' + wp.i18n.__(splitarray[1], 'content-collaboration-inline-commenting');
    return newTimeString;
}

/**
 * Retrieves the current user's ID.
 * @returns {number} The current user's ID.
 */
function getCurrentUserId() {
    return parseInt(currentUserData.id);
}

/**
 * Retrieves the current user information by ID.
 * @returns {Array} An array containing the user information.
 */
function getCurrentUserInfoById() {
    let userInfo = new Array();
    userInfo['id'] = parseInt(currentUserData.id);
    userInfo['username'] = currentUserData.username;
    userInfo['avtarUrl'] = currentUserData.avtarUrl;
    userInfo['role'] = currentUserData.role;

    return userInfo;
}

/**
 * Scrolls the board to a specific position.
 * 
 * @param {number} topOfText - The top position of the text to scroll to.
 */
function scrollBoardToPosition(topOfText) {

    let scrollTopClass = '';

    // Check for the existence of the various containers in the DOM
    if (document.querySelectorAll('.interface-interface-skeleton__content').length > 0) {
        // Latest WP Version
        scrollTopClass = '.interface-interface-skeleton__content';
    } else if (document.querySelectorAll('.block-editor-editor-skeleton__content').length > 0) {
        // Latest WP Version
        scrollTopClass = '.block-editor-editor-skeleton__content';
    } else if (document.querySelectorAll('.edit-post-layout__content').length > 0) {
        // Old WP Versions
        scrollTopClass = '.edit-post-layout__content';
    } else {
        // Default
        scrollTopClass = 'body';
    }

    // Get the current scroll position
    const container = document.querySelector(scrollTopClass);
    if (!container) return;

    const currentScrollTop = container.scrollTop;

    // Add the offset to the topOfText position
    topOfText = topOfText + currentScrollTop;

    var iframe = jQuery('iframe[name="editor-canvas"]')[0];
    // Ensure the iframe is loaded and accessible
    var iframeDoc = iframe?.contentDocument || iframe?.contentWindow?.document;

    if(iframeDoc){
        jQuery(iframeDoc.documentElement).stop(true).animate({
            scrollTop: topOfText + 50
        }, 1000);
    }else{
        jQuery(scrollTopClass).stop(true).animate({
            scrollTop: topOfText - 320
        }, 1000);
    }
}

/**
 * Retrieves the appropriate CSS selector for the content area of the WordPress editor.
 * 
 * This function checks for the presence of various content area selectors in the DOM and returns the first one that is found. If none of the expected selectors are found, it defaults to returning 'body'.
 * 
 * @returns {string} The CSS selector for the content area of the WordPress editor.
 */
function getScrollClass(){
    const selectors = [
        '.interface-interface-skeleton__content',
        '.block-editor-editor-skeleton__content',
        '.edit-post-layout__content'
    ];
    for (const selector of selectors) {
        if (document.querySelector(selector)) {
            return selector;
        }
    }
    return 'body'; // Default
}

/**
 * Removes the floating icon from the DOM.
 */
function removeFloatingIcon() {
    var floatingNode = document.getElementsByClassName('cf-floating-wrapper-main');

    if (floatingNode.length > 0) {
        // Loop through all elements with the class 'cf-floating-wrapper' and remove them
        Array.from(floatingNode).forEach(function(node) {
            node.remove();
        });
    }
    
}

const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);

/**
 * Filters the text before saving.
 * 
 * @param {string} newText - The text to be filtered.
 * @returns {string} - The filtered text.
 */
function filterTextBeforeSave(newText) {
    newText = newText.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '');
    newText = newText.replace(/<br>/igm, ' <br> ');
    if (isSafari || isChrome) {
        newText = newText.replace(/<div>/igm, '<br>');
        newText = newText.replace(/<\/div>/igm, '');
        newText = newText.replace(/<br> <br>/igm, '<br>');
        newText = newText.replace(/<\/?span[^>]*>/g, ''); // Resolved #512 multiline comment bug @author - Mayank / since 3.6        
    }
    var link;
    // Adding anchor tag around the linkable text.
    // For bug fixing of semicolon there is a little chnage in regex   

    newText = newText.replace(/<a\s.*?>(.*?)<\/a>/g, function (match) {
        return ' ' + match + ' ';
    });

         // Detect Browser.
         var browser = (function (agent) {
            switch (true) {
                case agent.indexOf("firefox") > -1: return "firefox";
                default: return "other";
            }
        })(window.navigator.userAgent.toLowerCase());
    
        if ('firefox' === browser) {
            newText = newText.replace(/<a\s.*?>(.*?)<\/a>/g, function (match) {
            return ' ' + `${match}\u00a0` + ' ';
          });
        }

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

/**
 * Removes anchor tags and their target attributes from the given editable text.
 * 
 * @param {string} editedValue - The editable text to remove anchor tags from.
 * @returns {string} The modified text with anchor tags removed.
 */
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

/**
 * Filters the text for editing by removing unnecessary elements and formatting.
 * 
 * @param {string} newText - The text to be filtered.
 * @returns {string} - The filtered text.
 */
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

/**
 * Validates the comment reply text by removing unwanted HTML tags, special characters, and leading/trailing whitespace.
 * 
 * @param {string} newText - The comment reply text to be validated.
 * @returns {string} - The validated comment reply text.
 */
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

/**
 * Translates the string format based on certain conditions.
 * 
 * @param {string} str - The string to be translated.
 * @returns {string} - The translated string.
 */
function translateStringFormat(str) {
    let translatedStringFormats = str;
    let splitString, concatBreakedString, removedHTMLTag;
    if (translatedStringFormats?.includes("Space (")) {
        splitString = translatedStringFormats.split(" ");

        var splitString0 = splitString[0];
        if( undefined === splitString0 ) { splitString0 = ''; }

        var splitString1 = splitString[1];
        if( undefined === splitString1 ) { splitString1 = ''; }

        var splitString2 = splitString[2];
        if( undefined === splitString2 ) { splitString2 = ''; }

        translatedStringFormats = sprintf('%s %s %s', wp.i18n.__(splitString0, 'content-collaboration-inline-commenting'), splitString1, wp.i18n.__(splitString2, 'content-collaboration-inline-commenting'));
    } else if (translatedStringFormats?.includes("Remove Link with URL")) {
        splitString = translatedStringFormats.split("Remove Link with URL ");

        var splitString1 = splitString[1];
        if( undefined === splitString1 ) { splitString1 = ''; }

        translatedStringFormats = sprintf('%s %s', wp.i18n.__('Remove Link with URL', 'content-collaboration-inline-commenting'), splitString1);
    } else if (translatedStringFormats?.includes("with URL")) {
        splitString = translatedStringFormats.split("with URL ");
        
        var splitString1 = splitString[1];
        if( undefined === splitString1 ) { splitString1 = ''; }

        translatedStringFormats = sprintf('%s %s', wp.i18n.__('with URL', 'content-collaboration-inline-commenting'), splitString1);
    } else if (translatedStringFormats?.includes("Replace")) {
        splitString = translatedStringFormats.split(" ");

        var splitString1 = splitString[1];
        if( undefined === splitString1 ) { splitString1 = ''; }

        var splitString3 = splitString[3];
        if( undefined === splitString3 ) { splitString3 = ''; }

        translatedStringFormats = sprintf('%s %s %s %s', wp.i18n.__('Replace', 'content-collaboration-inline-commenting'), splitString1, wp.i18n.__('to', 'content-collaboration-inline-commenting'), splitString3);
    } else if (translatedStringFormats?.includes("Line Break (")) {
        splitString = translatedStringFormats.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];

        var splitString2 = splitString[2];
        if( undefined === splitString2 ) { splitString2 = ''; }

        var splitString3 = splitString[3];
        if( undefined === splitString3 ) { splitString3 = ''; }

        translatedStringFormats = sprintf('%s %s %s', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), splitString2, wp.i18n.__(splitString3, 'content-collaboration-inline-commenting'));
    } else if (translatedStringFormats?.includes("Block Alignment")) {
        removedHTMLTag = translatedStringFormats.replace(/<[^>]*>/g, '');
        splitString = removedHTMLTag.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];

        var splitString3 = splitString[3];
        if( undefined === splitString3 ) { splitString3 = ''; }

        var splitString4 = splitString[4];
        if( undefined === splitString4 ) { splitString4 = ''; }

        var splitString2 = splitString[2];
        if( undefined === splitString2 ) { splitString2 = ''; }

        translatedStringFormats = sprintf('<em>%s</em> <em>%s</em> %s <em>%s</em>', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString2, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString3, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString4, 'content-collaboration-inline-commenting'));

        if( splitString.length >= 5 ) {
            for (let index = 5; index < splitString.length; index++) {
                const element = splitString[index];
                translatedStringFormats += sprintf( '<em> %s </em>', wp.i18n.__(element, 'content-collaboration-inline-commenting') );
            }
        }

    } else if (translatedStringFormats?.includes("Change Heading")) {
        removedHTMLTag = translatedStringFormats.replace(/<[^>]*>/g, '');
        splitString = removedHTMLTag.split(" ");
        concatBreakedString = splitString[0] + ' ' + splitString[1];

        var splitString2 = splitString[2];
        if( undefined === splitString2 ) { splitString2 = ''; }

        var splitString3 = splitString[3];
        if( undefined === splitString3 ) { splitString3 = ''; }

        var splitString4 = splitString[4];
        if( undefined === splitString4 ) { splitString4 = ''; }

        var splitString5 = splitString[5];
        if( undefined === splitString5 ) { splitString5 = ''; }

        var splitString7 = splitString[7];
        if( undefined === splitString7 ) { splitString7 = ''; }

        var splitString8 = splitString[8];
        if( undefined === splitString8 ) { splitString8 = ''; }

       translatedStringFormats = sprintf('<em> %s </em> <em> %s %s </em> %s <em> %s %s %s</em>', wp.i18n.__(concatBreakedString, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString2, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString3, 'content-collaboration-inline-commenting'), splitString4, wp.i18n.__(splitString5, 'content-collaboration-inline-commenting'), wp.i18n.__(splitString7, 'content-collaboration-inline-commenting'), splitString8);

       if( splitString.length >= 9 ) {
        for (let index = 9; index < splitString.length; index++) {
            const element = splitString[index];
            translatedStringFormats += sprintf( '<em> %s </em>', wp.i18n.__(element, 'content-collaboration-inline-commenting') );
        }
       }

    }
    str = translatedStringFormats;
    return str;
}

/**
 * Truncates a comma-separated string of user roles to first role + '...' if multiple roles exist.
 * 
 * @param {string} myStr - The comma-separated string of user roles.
 * @returns {string} The truncated user role string.
 */
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

/**
 * Closes the multicollab sidebar in the Gutenberg editor if it is open.
 * 
 * Checks if the active sidebar is the "cf-activity-center" sidebar from the 
 * Commenting Feature plugin, and if so dispatches the core action to close it.
 */
function closeMulticollabSidebar() {

    //to close sidebar in gutenberg
    //const isSidebarOpened = wp.data.select( 'core/edit-post' ).isEditorSidebarOpened();
    const sidebarName = wp.data.select('core/edit-post').getActiveGeneralSidebarName();
    if (sidebarName === 'cf-activity-center/cf-activity-center') {
        wp.data.dispatch('core/edit-post').closeGeneralSidebar();
    }
}

/**
 * Returns whether the post is in a saved state.
 * 
 * Checks if the post is new, dirty, saving, or saved,
 * and returns true if it is in the process of saving or is saved.
 */
function getPostSaveStatus() {
    const isNew = wp.data.select('core/editor').isEditedPostNew();
    const isDirty = wp.data.select('core/editor').isEditedPostDirty();
    const isSaving = wp.data.select('core/editor').isSavingPost();
    const isSaved = (!isNew && !isDirty);
    const isSavedState = isSaving || isSaved;
    return isSavedState;
}

/**
 * Appends a notification div to the DOM to warn the user they have unsaved comments.
 * 
 * Gets the post status label, creates a new div element, sets attributes on it 
 * for id, class and inline styles, sets innerHTML containing localized text 
 * warning of unsaved comments, finds parent element to append to by id, and
 * appends new div if parent found.
 */
function appendInfoBoardDiv() {
    var postStatusLabel = getPostStatuslabel();
    var pinboardNode = document.createElement('div');
    pinboardNode.setAttribute("id", 'cf-span__status');
    pinboardNode.setAttribute("class", 'cf-board-notice');
    pinboardNode.setAttribute('style', 'display:none');
    pinboardNode.innerHTML = sprintf('%s <span>x</span> %s <br> %s <strong>%s</strong> %s', wp.i18n.__('You have', 'content-collaboration-inline-commenting'), wp.i18n.__('unsaved comments.', 'content-collaboration-inline-commenting'), wp.i18n.__('Click', 'content-collaboration-inline-commenting'), wp.i18n.__(postStatusLabel, 'content-collaboration-inline-commenting'), wp.i18n.__('to save.', 'content-collaboration-inline-commenting')); //phpcs:ignore
    var parentNodeRef = document.getElementById('cf-comments-suggestions-parent');
    if (null !== parentNodeRef) {
        parentNodeRef.appendChild(pinboardNode);
    }
}

/**
 * Returns the translated label for the post status.
 * 
 * Gets the current post status from the editor state.
 * Checks the status and returns the translated label string for that status.
 * Defaults to the status string if no translation exists.
 */
function getPostStatuslabel() {

    var postStatus = wp.data.select('core/editor').getEditedPostAttribute('status');
    var postStatusLabel = '';

    if (postStatus === 'private') {
        postStatusLabel = 'Privately Published';
    } else if (postStatus === 'publish') {
        postStatusLabel = 'Published / Update';
    } else if (postStatus === 'future' || postStatus === 'scheduled') {
        postStatusLabel = 'Scheduled';
    } else if (postStatus === 'draft') {
        postStatusLabel = 'Save draft';
    } else if (postStatus === 'pending') {
        postStatusLabel = 'Pending Review';
    } else {
        postStatusLabel = postStatus;
    }

    return postStatusLabel;
}

/**
 * Appends a notice board <div> element to the page if it doesn't already exist.
 * The notice board is hidden by default.
 */
function appendNoticeBoardDiv() {
    var noticeboardNode = document.createElement('div');
    noticeboardNode.setAttribute("id", 'cf-board-notice');
    noticeboardNode.setAttribute("class", 'cf-board-notice');
    noticeboardNode.setAttribute('style', 'display:none');
    noticeboardNode.innerHTML = 'default notice here';
    var parentNodeRef = document.getElementById('cf-comments-suggestions-parent');
    if (null !== parentNodeRef) {
        parentNodeRef.appendChild(noticeboardNode);
    }
}

/**
 * Appends a notice board div to the page if needed, 
 * and subscribes to data to show/hide the notice board
 * when new comments are received.
 */
/**
 * Appends a notice board div to the page if needed, 
 * and subscribes to data to show/hide the notice board
 * when new comments are received.
 */
function showNoticeBoardonNewComments() {
    appendNoticeBoardDiv();
    wp.data.subscribe(function () {

        let noticeboard = document.getElementById("cf-board-notice");
        if (null === noticeboard) {
            appendNoticeBoardDiv();
            noticeboard = document.getElementById("cf-board-notice");
        }

    });
}

function getSelectionHtml() {
   
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    var html = "";
    
    // Ensure iframe is available
    if (iframe && iframe.contentWindow && iframe.contentWindow.getSelection) {
        var sel = iframe.contentWindow.getSelection();
        
        if (sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                container.appendChild(sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;
        }
    } else if (iframe && iframe.contentWindow && iframe.contentWindow.document.selection) {
        if (iframe.contentWindow.document.selection.type == "Text") {
            html = iframe.contentWindow.document.selection.createRange().htmlText;
        }
    } else if (typeof window.getSelection != "undefined") {
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

/**
 * Removes all notices from the WordPress notices state. 
 * Loops through all notices and dispatches a removeNotice action for each.
 */
function cf_removeAllNotices() {
    var notices = wp.data.select('core/notices').getNotices();
    if (undefined !== notices) {
        notices.forEach(function (data) {
            wp.data.dispatch('core/notices').removeNotice(data.id);
        })
    }

}

function publishBtnClick(e) {
    var openBoards = document.querySelectorAll('.cls-board-outer').length;
    if (0 !== openBoards) {
        e.stopImmediatePropagation();
        e.preventDefault();
        e.stopPropagation();
        prePostChecks(e);
    }
}

/**
 * prePostChecks is a function that performs checks before allowing the post to be published. 
 * 
 * It stops event propagation and default to prevent the post from being published right away.
 * 
 * It checks if there are any open comment boards on the post.
 * 
 * Based on the multicollab_cf_alert settings, it will:
 * - Give a reminder if there are open boards, allowing the user to confirm publishing
 * - Prevent publishing if there are open boards 
 * - Allow publishing if there are no open boards
 * 
 * It uses wp.data dispatch to lock/unlock post saving as needed during the process.
 */
function prePostChecks(e) {
    e.stopPropagation()
    e.preventDefault()
    var openBoards = document.querySelectorAll('.cls-board-outer').length;
    let locked = false;
    if (multicollab_fs.is_plan_lite) {
        var msgReminderPublisher = wp.i18n.__('There are a few pending comments in this post. Do you still want to publish the post?', 'content-collaboration-inline-commenting');
        var msgStopPublisher = wp.i18n.__("You can't publish this post before resolving all comments. Please review and resolve all open comments before moving forward.", "content-collaboration-inline-commenting");
    } else {
        var msgReminderPublisher = wp.i18n.__('There are a few pending suggestions or comments in this post. Do you still want to publish the post?', 'content-collaboration-inline-commenting');
        var msgStopPublisher = wp.i18n.__("You can't publish this post before resolving all suggestions or comments. Please review and resolve all open suggestions and comments before moving forward.", "content-collaboration-inline-commenting");
    }

    if ('remind' === multicollab_cf_alert.cf_give_alert_message && 0 !== openBoards) {
        if (!confirm(msgReminderPublisher)) {
            if (!locked) {
                locked = true;
                wp.data.dispatch('core/editor').lockPostSaving('title-lock');
                setTimeout(function () {
                    wp.data.dispatch('core/editor').unlockPostSaving('title-lock');
                }, 3000);
            }

        } else {

            locked = false;
            wp.data.dispatch('core/editor').unlockPostSaving('title-lock');
            wp.data.dispatch("core/editor").editPost({
                status: "publish",
            });
            wp.data.dispatch('core/editor').savePost();
        }
    } else if ('stop' === multicollab_cf_alert.cf_give_alert_message && 0 !== openBoards) {
        alert(msgStopPublisher);
        wp.data.dispatch('core/editor').lockPostSaving('title-lock');

        setTimeout(function () {
            wp.data.dispatch('core/editor').unlockPostSaving('title-lock');
        }, 3000);
    } else {
        wp.data.dispatch('core/editor').unlockPostSaving('title-lock');
        wp.data.dispatch("core/editor").editPost({
            status: "publish",
        });
        wp.data.dispatch('core/editor').savePost();
    }

}

/**
 * Hides suggestion boards and removes a class from the body.
 * Dispatches an action to set a post meta field 
 * to false to hide suggestion boards.
 * Removes the "hide-sg" class from the body.
 */
function displaySuggestionBoards() {
    wp.domReady(function () {
        wp.data.dispatch('core/editor').editPost({
            meta: { _sb_show_suggestion_boards: false },
        });
    });

    document.body.classList.remove('hide-sg');
}

/**
 * Creates a parent and child div to hold comments.
 * 
 * The parent div is appended to the `.edit-post-visual-editor` element. 
 * The child div is appended to the parent div.
 */
function createCommentNode() {
    var parentNode = document.createElement('div');
    parentNode.setAttribute("id", 'cf-comments-suggestions-parent');
    var referenceNode = document.querySelector('.edit-post-visual-editor');
    if (null !== referenceNode) {
        referenceNode.appendChild(parentNode);
        var commentNode = document.createElement('div');
        commentNode.setAttribute("id", 'cf-comment-board-wrapper');
        var parentNodeRef = document.getElementById('cf-comments-suggestions-parent');
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

/**
 * Gets the value of the cookie with the given name.
 * 
 * @param {string} name - The name of the cookie to get the value for.
 * @returns {string|null} The value of the cookie, or null if no cookie with the given name exists.
 */
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

/**
 * Deletes a cookie by name.
 * 
 * @param {string} name - The name of the cookie to delete.
 */
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

//function for custom alert
/**
 * Creates a custom alert dialog overlay and modal popup.
 * Allows displaying a custom alert message and title.
 * Provides methods to open, close (OK button), and set content.
 */
function createCustomAlert() {
    // Create alert elements only once
    const cf_dialogoverlay = document.createElement('div');
    cf_dialogoverlay.id = 'cf_dialogoverlay';

    const cf_dialogbox = document.createElement('div');
    cf_dialogbox.id = 'cf_dialogbox';

    cf_dialogbox.innerHTML = `
        <div>
            <div id="cf_dialogboxhead"></div>
            <div id="cf_dialogboxbody"></div>
            <div id="cf_dialogboxfoot">
                <button id="cf_alert_ok_button" class="cf_pure-material-button-contained active">OK</button>
            </div>
        </div>
    `;

    document.body.appendChild(cf_dialogoverlay);
    document.body.appendChild(cf_dialogbox);

    // Set up the OK button event listener once
    document.getElementById('cf_alert_ok_button').addEventListener('click', this.ok);

    this.alert = function (message, title) {
        // Show overlay and dialog box
        cf_dialogoverlay.style.display = 'block';
        cf_dialogbox.style.display = 'block';

        // Set dialog content
        const dialogHead = document.getElementById('cf_dialogboxhead');
        if (title) {
            dialogHead.style.display = 'block';
            dialogHead.innerHTML = `<i class="fa fa-exclamation-circle" aria-hidden="true"></i> ${title}`;
        } else {
            dialogHead.style.display = 'none';
        }

        document.getElementById('cf_dialogboxbody').textContent = message;
    };

    this.ok = function () {
        // Hide overlay and dialog box
        cf_dialogoverlay.style.display = 'none';
        cf_dialogbox.style.display = 'none';
    };
}


// Function for display floating suggestion board for redo task. @author: Rishi Shah @since: 3.4
/**
 * Displays a floating suggestion board for the given text selection.
 * 
 * @param {string} selectedText - The ID of the text selection to display the board for.
 */
// Function for display floating suggestion board for redo task. @author: Rishi Shah @since: 3.4
/**
 * Displays a floating suggestion board for the given text selection.
 * 
 * @param {string} selectedText - The ID of the text selection to display the board for.
 */
function floatCommentsBoard(selectedText) {
    var iframe = jQuery('iframe[name="editor-canvas"]');
    var iframeDocument = iframe.length ? iframe[0].contentWindow.document : null;

    let boardOuterHeight = 0;

    // Remove classes from elements
    document.querySelectorAll('.cls-board-outer').forEach(function(element) {
        element.classList.remove('focus', 'is-open', 'onGoing');
    });
    document.querySelectorAll('.cf-icon__addBlocks, .cf-icon__removeBlocks').forEach(function(element) {
        element.classList.remove('focus');
    });
    document.querySelectorAll('.cf-icon-wholeblock__comment, .cf-onwhole-block__comment').forEach(function(element) {
        element.classList.remove('focus');
    });

    document.querySelectorAll('#cf-comment-board-wrapper .comment-delete-overlay').forEach(function(element) {
        element.classList.remove('show');
    });
    document.querySelectorAll('#cf-comment-board-wrapper .comment-resolve .resolve-cb').forEach(function(element) {
        element.checked = false;
    });
    document.querySelectorAll('#cf-comment-board-wrapper .cls-board-outer').forEach(function(element) {
        element.style.opacity = '0.4';
        //element.style.removeProperty('top');
    });

    var singleBoardId = selectedText;
   
    let singleBoardIdWithSg;
    if (singleBoardId.match(/^el/m) === null) {
        singleBoardIdWithSg = 'sg' + singleBoardId;
    } else {
        singleBoardIdWithSg = singleBoardId;
    }

    const elementcombineBoardId = document.getElementById(singleBoardIdWithSg);
    if (elementcombineBoardId) {
        elementcombineBoardId.style.opacity = "1";
        elementcombineBoardId.classList.add("focus", "onGoing", "is-open");
        let referenceElement = iframeDocument 
          ? (singleBoardId.match(/^el/m) === null 
              ? iframeDocument.getElementById(singleBoardId) 
              : iframeDocument.querySelector(`[datatext="${singleBoardId}"]`)) 
          : (singleBoardId.match(/^el/m) === null 
              ? document.getElementById(singleBoardId) 
              : document.querySelector(`[datatext="${singleBoardId}"]`));

        // Add the 'data-rich-text-format-boundary' attribute with value 'true'
        if (referenceElement) {
          referenceElement.dataset.richTextFormatBoundary = 'true';
        } else {
            const customAttrSuggestion = cfgetCustomAttribute();
            let checkExist;
            customAttrSuggestion.map((attrValue) => {
                if (iframeDocument) {
                    checkExist = iframeDocument.querySelector(`[${attrValue}="${singleBoardId}"]`);;
                    if (checkExist) {
                        referenceElement = checkExist;
                    }
                } else {
                    checkExist = document.querySelector(`[${attrValue}="${singleBoardId}"]`);;
                    if (checkExist) {
                        referenceElement = checkExist;
                    }
                }
            });
        }
        var allowedBlocks = allowedMedia();
        var blockName = wp.data.select( 'core/block-editor' ).getSelectedBlock()?.name;
        if (singleBoardIdWithSg.match(/^el/m) !== null) {
            var element = returnCommntTextElement( singleBoardIdWithSg );
          } else {
            var sID = singleBoardIdWithSg.replace('sg', '');
            if( (allowedBlocks.media).includes(blockName) ) {
                var clientId = wp.data.select('core/block-editor').getSelectedBlockClientId();
                if( iframeDocument ) {
                    //const iframeDocument = iframe?.contentDocument || iframe?.contentWindow?.document;
                    var element = iframeDocument.getElementById( 'block-' + clientId );
                } else {
                    var element = document.getElementById( 'block-' + clientId );
                }
            } else {
                if( iframeDocument ) {
                    //const iframeDocument = iframe?.contentDocument || iframe?.contentWindow?.document;
                    var element = iframeDocument.getElementById(sID);
                } else {
                    var element = document.getElementById(sID);
                }
            }
           
        }
        if (element) {
            element.setAttribute('data-rich-text-format-boundary', 'true');
            element.scrollIntoView({
              behavior: "smooth", // Optional: to scroll smoothly (instead of instantly)
              block: "center" // Optional: specifies vertical alignment (start, center, end, nearest)
            });
            setTimeout(() => {
              const rect = element.getBoundingClientRect();
              topOfText = rect.top + window.scrollY + 50;  // Adds scroll position to get position relative to the document
              jQuery('#' + singleBoardIdWithSg + '.cls-board-outer').addClass('focus');
              jQuery('#' + singleBoardIdWithSg + '.cls-board-outer').addClass('is-open');
              jQuery('#' + singleBoardIdWithSg + '.cls-board-outer').css('opacity', '1');
              jQuery('#' + singleBoardIdWithSg + '.cls-board-outer').offset({ top: topOfText });
            }, 1000);
                                      
        }
    }

    // Handle rich text format boundaries
    const underlineAllAttr = document.querySelectorAll('[data-rich-text-format-boundary="true"]');
    if (underlineAllAttr.length > 0) {
        underlineAllAttr.forEach(function(singleElement) {
            if (singleElement.classList.contains('mdadded') || singleElement.classList.contains('mdremoved')) {
                if (singleBoardId !== singleElement.id) {
                    singleElement.setAttribute('data-rich-text-format-boundary', 'false');
                } else {
                    singleElement.setAttribute('data-rich-text-format-boundary', 'true');
                }
            } else if (singleElement.parentNode && singleElement.parentNode.classList.contains('mdmodified')) {
                const parentElement = singleElement.parentNode;
                if (singleBoardId !== parentElement.id) {
                    parentElement.querySelectorAll('*').forEach(function(child) {
                        child.setAttribute('data-rich-text-format-boundary', 'false');
                    });
                } else {
                    parentElement.querySelectorAll('*').forEach(function(child) {
                        child.setAttribute('data-rich-text-format-boundary', 'true');
                    });
                }
            } else if (singleElement.classList.contains('mdspan-comment')) {
                var suggestionId = singleElement.getAttribute('datatext');
                if (singleBoardId !== suggestionId) {
                    document.querySelector('[datatext="' + suggestionId + '"]').setAttribute('data-rich-text-format-boundary', 'false');
                } else {
                    document.querySelector('[datatext="' + suggestionId + '"]').setAttribute('data-rich-text-format-boundary', 'true');
                }
            }
        });
    }
    // Optional: Remove scroll position effect (uncomment if needed)
    //scrollBoardToPosition(topOfTextSingleBoard);
}

// Generate multi formate suggestion board string/@author:Pooja bhimani/since @3.5
/**
 * Converts an array of tags to a comma-separated string, removing duplicates.
 * 
 * @param {Array} tagArray - The array of tags to convert.
 * @returns {string} The tags as a comma-separated string with no duplicates.
 */
function tagArrayToString(tagArray) {
    var updateTagArray = tagArray.filter((value, index, array) => array.indexOf(value) === index);
    var title = updateTagArray.join(', ');
    // Again split the array to remove any duplicate value from title. @author: Rishi Shah @since: 3.4
    var tagArraySplit = title.split(', ');
    tagArraySplit = tagArraySplit.filter((value, index, array) => array.indexOf(value) === index);
    title = tagArraySplit.join(', ');
    return title;
}


/**
 * Returns an array of custom attribute names used for block suggestions.
 * 
 * These attributes are added to blocks to associate them with suggestions
 * and track changes.
 */
function cfgetCustomAttribute() {

    var customAttribute = [
        'suggestion_id',
        'align_sg_id',
        'textAlign_sg_id',
        'lock_sg_id',
        'width_sg_id',
        'url_sg_id',
        'link_sg_id',
        'tracks_sg_id',
        'contentPosition_sg_id',
        'style_sg_id',
        'minHeight_sg_id',
        'table_style_sg_id'
    ];
    return customAttribute;

}

// return custom attribute ID /@author:Nirav Soni/since @4.3
/**
 * Returns the custom attribute ID for the given selected text.
 * Searches for elements with custom attributes like suggestion_id, 
 * lock_sg_id etc that contain the selected text. 
 * Returns the data-block attribute value of the first matching element.
 */
function cfgetCustomAttributeId(selectedText) {

    let clientId = document
        .querySelector(
            '[suggestion_id="' +
            selectedText +
            '"], [lock_sg_id="' +
            selectedText +
            '"], [textAlign_sg_id="' +
            selectedText +
            '"], [align_sg_id="' +
            selectedText +
            '"], [width_sg_id="' +
            selectedText +
            '"], [url_sg_id="' +
            selectedText +
            '"], [link_sg_id="' +
            selectedText +
            '"], [style_sg_id="' +
            selectedText +
            '"], [minheight_sg_id="' +
            selectedText +
            '"], [contentposition_sg_id="' +
            selectedText +
            '"], [tracks_sg_id="' +
            selectedText +
            '"], [table_style_sg_id="' +
            selectedText +
            '"]'
        )
        ?.getAttribute("data-block");
    return clientId;

 }


    //Calculated layout width function @ Minal Diwan
    function mdboardOffset() {
        const wrapper = document?.querySelector('#cf-comment-board-wrapper');
        const focusBoard = wrapper?.querySelector('.cls-board-outer.focus');
        const elid = focusBoard ? focusBoard.getAttribute('id') : null;
        const commentEditFocus = focusBoard?.querySelector('.js-cf-edit-comment.comment-focus');
    
        setTimeout(function () {
    
            const totalOpenBoardsIds = document.querySelectorAll('.cls-board-outer.focus');
            let counter = 0;
    
            if (totalOpenBoardsIds.length >= 2) {
                let topOfTextSingleBoardSuggestion;
                let topOfTextSingleBoardComment;
                let SuggestionBoardOuterHeight;
                let singleBoardIdSuggestion;
                let singleBoardIdComment;
                let combineBoardId;
                let FirstsingleBoardIdSuggestion;
                let FirstSuggestionBoardOuterHeight;
    
                totalOpenBoardsIds.forEach(board => {
                    const singleBoardId = board.id;
                    if (singleBoardId) {
                        if (!singleBoardId.match(/^el/m)) {
                            topOfTextSingleBoardSuggestion = document.getElementById(singleBoardId).offsetTop;
                            singleBoardIdSuggestion = 'sg' + singleBoardId;
                            if (counter === 0) {
                                FirstsingleBoardIdSuggestion = 'sg' + singleBoardId;
                            }
                            combineBoardId = 'sg' + singleBoardId;
                            counter++;
                        } else {
                            topOfTextSingleBoardComment = document.querySelector('[datatext="' + singleBoardId + '"]').offsetTop;
                            singleBoardIdComment = singleBoardId;
                            combineBoardId = singleBoardId;
                        }
    
                        const combineBoardElement = document.getElementById(combineBoardId);
                        if (combineBoardElement) {
                            combineBoardElement.style.opacity = '1';
                            combineBoardElement.classList.add('is-open', 'focus', 'onGoing');
                        }
                    }
                    if (FirstsingleBoardIdSuggestion) {
                        FirstSuggestionBoardOuterHeight = document.querySelector('#' + FirstsingleBoardIdSuggestion)?.offsetHeight;
                    }
                });
    
                if (document.querySelector('#' + singleBoardIdSuggestion)) {
                    SuggestionBoardOuterHeight = document.querySelector('#' + singleBoardIdSuggestion).offsetHeight;
                    const suggestionBoard = document.getElementById(singleBoardIdSuggestion);
                    suggestionBoard.style.top = topOfTextSingleBoardSuggestion + 'px';
    
                    // Add floating board adjustment for multi-suggestion
                    if (counter === 2 && FirstsingleBoardIdSuggestion) {
                        const firstSuggestionBoard = document.getElementById(FirstsingleBoardIdSuggestion);
                        firstSuggestionBoard.style.top = topOfTextSingleBoardSuggestion + 'px';
                        suggestionBoard.style.top = topOfTextSingleBoardSuggestion + FirstSuggestionBoardOuterHeight + 20 + 'px';
                    }
    
                    if (!document.querySelector('#' + singleBoardIdComment + ', .board').classList.contains('fresh-board')) {
                        if (counter === 2 && FirstsingleBoardIdSuggestion) {
                            const commentBoard = document.getElementById(singleBoardIdComment);
                            commentBoard.style.top = topOfTextSingleBoardSuggestion + SuggestionBoardOuterHeight + FirstSuggestionBoardOuterHeight + 40 + 'px';
                        } else {
                            const commentBoard = document.getElementById(singleBoardIdComment);
                            commentBoard.style.top = topOfTextSingleBoardSuggestion + SuggestionBoardOuterHeight + 20 + 'px';
                        }
                    } else {
                        const commentBoard = document.getElementById(singleBoardIdComment);
                        commentBoard.style.top = topOfTextSingleBoardSuggestion + 'px';
                    }
    
                    document.querySelectorAll('[data-rich-text-format-boundary="true"]').forEach(attr => {
                        if (attr.classList.contains('mdadded') || attr.classList.contains('mdremoved')) {
                            const id = attr.id;
                            if (singleBoardId !== id) {
                                attr.setAttribute('data-rich-text-format-boundary', 'false');
                            } else {
                                attr.setAttribute('data-rich-text-format-boundary', 'true');
                            }
                        } else if (attr.parentNode.classList.contains('mdmodified')) {
                            const id = attr.parentNode.id;
                            if (singleBoardId !== id) {
                                attr.parentNode.children.forEach(child => {
                                    child.setAttribute('data-rich-text-format-boundary', 'false');
                                });
                            } else {
                                attr.parentNode.children.forEach(child => {
                                    child.setAttribute('data-rich-text-format-boundary', 'true');
                                });
                            }
                        } else if (attr.classList.contains('mdspan-comment')) {
                            const suggestionId = attr.getAttribute('datatext');
                            if (singleBoardId !== suggestionId) {
                                document.querySelector('[datatext="' + suggestionId + '"]').setAttribute('data-rich-text-format-boundary', 'false');
                            } else {
                                document.querySelector('[datatext="' + suggestionId + '"]').setAttribute('data-rich-text-format-boundary', 'true');
                            }
                        }
                    });
                    if(commentEditFocus){
                        scrollBoardToPosition(topOfTextSingleBoardSuggestion);
                    }
                }
            } else if (focusBoard) {
                let topOfText;
                if (elid && elid.match(/^el/m) !== null) {
                    //topOfText = document.querySelector('[datatext="' + elid + '"]').offsetTop;
                    topOfText = jQuery('[datatext="' + elid + '"]').offset()?.top;
                } else {
                    // const sid = document.getElementById(elid)?.getAttribute('data-sid');
                    // //topOfText = document.querySelector('[id="' + sid + '"]')?.offsetTop;
                    // topOfText = document.getElementById( elid )?.offsetTop;
                    const sid = jQuery('#' + elid).attr('data-sid');
                    topOfText = jQuery('[id="' + sid + '"]').offset()?.top;

                    cfgetCustomAttribute().forEach(attrValue => {
                        if (!topOfText && jQuery(`[${attrValue}="${sid}"]`).length > 0) {
                            topOfText = jQuery(`[${attrValue}="${sid}"]`).offset()?.top;
                        }
                    });
                }
                // const focusBoardElement = document.querySelector('#cf-comment-board-wrapper').querySelector('.cls-board-outer.focus');
                // focusBoardElement.style.top = topOfText + 'px';
                jQuery('#cf-span__comments').find('.cls-board-outer.focus').offset({ top: topOfText });
                
                if(commentEditFocus){
                    scrollBoardToPosition(topOfText);
                }
            }
        }, 800);
    }   
      

    // Function to handle layout changes
    function handleEditorLayoutChange( fromIconClick = false ) {

        var $ = jQuery;

        const notCommentOncls = document.querySelector(".multicollab_body_class");
        var checkCommntAval = !notCommentOncls?.classList?.contains('commentOn');
        const ediLayot = document.querySelector(".editor-styles-wrapper");
        const cmntLayout = document.querySelector("#cf-comments-suggestions-parent");
        const ediLayotWidth = ediLayot?.offsetWidth;
        const cmntLyotWidth = cmntLayout?.offsetWidth;
        let calcLyotWidth = ediLayotWidth - cmntLyotWidth;
        const editSidebarchck = document.querySelector(".edit-post-layout");
        const blockinsertchck = document.querySelector(".interface-interface-skeleton__body");
        const firstChild = blockinsertchck?.firstElementChild;
        const checkVisualedit = wp.data.select("core/edit-post").getEditorMode();
        const $boardOuter = document.querySelector( '#cf-comment-board-wrapper .cls-board-outer' );
        const calcAuto = "auto";

        const prevSidebarOpen = document.querySelector('.editor-sidebar');

        if (!checkCommntAval) {
           if (editSidebarchck?.classList?.contains('is-sidebar-opened') || firstChild) {
            mdboardOffset();
           }
        }

    }


function getBoardIdFromContent(text, keys) {
    const results = new Set(); // Set will automatically handle uniqueness

    // 1. Regex to match key-value pairs in the text (both formats)
    const keyValueRegex = new RegExp(keys.map(key => `"${key}":"([^"]+)"|${key}="([^"]+)"`).join('|'), 'g');
    
    // 2. Extract values for keys from the provided text
    let match;
    while ((match = keyValueRegex.exec(text)) !== null) {
        for (let i = 1; i < match.length; i++) {
            if (match[i]) {
                results.add(match[i].trim());  // Add non-empty value
            }
        }
    }
    // Combine all tag-based regex patterns for <ins>, <del>, and <span> tags
    const tagRegex = /<(ins|del)[^>]*\sid="([^"]+)"|<(span)[^>]*(?=.*\b(?:mdadded|mdmodified|mdremoved)\b)(?=.*\b(?:textalignupdate|alignupdate|lockupdate)\b)[^>]*\sid="([^"]+)"/g;
    
    // Extract `id` values from <ins>, <del>, and <span> tags with specific classes
    while ((match = tagRegex.exec(text)) !== null) {
        const id = match[2] || match[4];  // Either match[2] for <ins>/<del> or match[4] for <span>
        if (id) {
            results.add(id.trim());
        }
    }

    // Convert the Set to an array and return it
    return [...results];
}

/**
 * Asynchronously opens a comment board by extracting relevant data from the current block editor content.
 *
 * This function retrieves the current blocks from the block editor, parses their content to find elements with
 * specific attributes and classes, and then updates the `mdstore` with the extracted data.
 *
 * The extracted data includes:
 * - Custom attributes defined in `cfgetCustomAttribute()`
 * - `datatext` attribute values from elements
 * - `id` attribute values from elements with specific classes (`mdadded`, `mdmodified`, `mdremoved`)
 *
 * The extracted data is stored in the `boardIds` array, which is then passed to the `setBoardIds` action
 * to update the state.
 */
async function openCommentBoard() {
    
    const { setBoardIds } = wp.data.dispatch('mdstore');
    const blocks = wp.data.select('core/block-editor').getBlocks();
    const customAttrSuggestion = cfgetCustomAttribute();
    customAttrSuggestion.push('datatext');

    const getContentString = wp.data.select('core/editor').getEditedPostAttribute('content') || '';
    const boardIds = getBoardIdFromContent(getContentString, customAttrSuggestion);

    setBoardIds(boardIds);
}

/**
 * Retrieves the client ID associated with the given board ID.
 *
 * This function searches the `boardIds` array stored in the `mdstore` state to find the
 * array element that matches the provided `board_id`. If a match is found, the
 * corresponding client ID is returned. If no match is found, `null` is returned.
 *
 * @param {string} board_id - The board ID to search for.
 * @returns {string|null} The client ID associated with the given board ID, or `null` if no match is found.
 */
function cfGetClientId(board_id) {
    // Get the boardIds array from the store
    const openBoardIds = wp.data.select('mdstore').getBoardIds();
    // Find the array that matches the specific ID
    const matchedArray = openBoardIds.find(boardId => boardId[0] === board_id);

    // If a match is found, return the client ID
    if (matchedArray) {
        return matchedArray[1];
    }

    // If no match is found, return null or handle accordingly
    return null;
}

/**
 * Adds a new board ID and client ID to the `boardIds` array in the `mdstore` state.
 *
 * This function checks if the provided `value` already exists in the `boardIds` array. If it
 * does not, the function adds a new array with the `value` and `clientId` to the `boardIds`
 * array, and then updates the `mdstore` state with the new `boardIds` array.
 *
 * @param {string} value - The board ID to add to the `boardIds` array.
 * @param {string} clientId - The client ID to associate with the new board ID.
 */
function setBoardIdByValue(value) {
    const { setBoardIds } = wp.data.dispatch('mdstore');
    const boardIds = wp.data.select('mdstore').getBoardIds();

    // Check if the value already exists in the boardIds array
    const valueExists = boardIds.some(board => board === value);
    if (!valueExists) {
        // Add the new boardId and clientId to the boardIds array
        const updatedBoardIds = [...boardIds, value];
        // Update the state with the updated boardIds array
        setBoardIds(updatedBoardIds);
    }   
}

/**
 * Removes a board ID and its associated client ID from the `boardIds` array in the `mdstore` state.
 *
 * This function takes a `value` parameter, which represents the board ID to be removed. It
 * retrieves the current `boardIds` array from the `mdstore` state, filters out the array
 * element that matches the provided `value`, and then updates the `mdstore` state with the
 * updated `boardIds` array.
 *
 * @param {string} value - The board ID to be removed from the `boardIds` array.
 */
function unsetBoardIdByValue(value) {
    const { setBoardIds } = wp.data.dispatch('mdstore');
    const boardIds = wp.data.select('mdstore').getBoardIds();

    // Remove the boardId and clientId to the boardIds array
    const updatedBoardIds = boardIds.filter(boardId => boardId !== value);

    // Update the state with the updated boardIds array
    setBoardIds(updatedBoardIds);
}

function cfDelay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function returnCommntTextElement( elID ) {
    const iframe = document.querySelector('[name="editor-canvas"]');
    let element;
    if (iframe) {			
        // Access the iframe's document
        const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
        
        // Query inside the iframe's document
        element = iframeDocument.querySelector('[datatext="' + elID + '"]');
    } else {
        // Query in the main document
        element = document.querySelector('[datatext="' + elID + '"]');				
    }

    return element;
}

function isElementCompletelyVisible(el) {
    const rect = el?.getBoundingClientRect();
    // Also check if the element's height is greater than 100px
    const isHeightGreaterThan100 = rect.height > 100;
    return isHeightGreaterThan100;
}


function allowedMedia() {

    const allowedBlocks = {}
    //restrict to specific block names
    allowedBlocks.media = [ 'core/image', 'core/video', 'core/audio' ,'core/gallery', 'core/cover' , 'core/media-text' ];
    //User can comment on whole block/@author Pooja Bhimani/@since EDD - 3.0.1
    allowedBlocks.text = ['core/paragraph','core/heading','core/list','core/list-item','core/quote','core/preformatted','core/verse','core/table','core/pullquote','core/file','core/button','core/code', 'core/freeform'];
    allowedBlocks.excluded = [''];

    return allowedBlocks;

}

function getBoardIds(singleBoardId) {

    let boardIds = { singleBoardIdSuggestion: null, singleBoardIdComment: null, combineBoardId: null };
  
    if (singleBoardId.match(/^el/m) === null) {
      boardIds.singleBoardIdSuggestion = "sg" + singleBoardId;
      boardIds.combineBoardId = "sg" + singleBoardId;
    } else {
      boardIds.singleBoardIdComment = singleBoardId;
      boardIds.combineBoardId = singleBoardId;
    }
  
    return boardIds;
}
