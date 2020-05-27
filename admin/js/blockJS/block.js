import Board from './component/board';

const {__} = wp.i18n;
const {Fragment, Component} = wp.element;
const {toggleFormat} = wp.richText;
const {RichTextToolbarButton} = wp.blockEditor;
const {registerFormatType, applyFormat, removeFormat} = wp.richText;

function fetchComments() {

    var newNode = document.createElement('div');
    newNode.setAttribute("id", 'md-span-comments');
    newNode.setAttribute("class", 'comments-loader');
    var referenceNode = document.querySelector('.block-editor-writing-flow');
    if (null !== referenceNode) {
        referenceNode.appendChild(newNode);

        let selectedText;
        let txtselectedText;
        var allThreads = [];

        jQuery('.wp-block mdspan').each(function () {
            // `this` is the div
            selectedText = jQuery(this).attr('datatext');
            if (jQuery('#' + selectedText).length === 0) {
                txtselectedText = 'txt' + jQuery(this).attr('datatext');
                jQuery('#' + selectedText + ' textarea').attr('id', txtselectedText);

                var newNode = document.createElement('div');
                newNode.setAttribute("id", selectedText);
                newNode.setAttribute("class", "cls-board-outer is_active");

                var referenceNode = document.getElementById('md-span-comments');
                referenceNode.appendChild(newNode);

                ReactDOM.render(
                    <Board datatext={selectedText} /*onChanged={onChange}*//>,
                    document.getElementById(selectedText)
                )
            }
            allThreads.push(selectedText);
        });

        var loadAttempts = 0;
        const loadComments = setInterval(function () {
            loadAttempts++;
            if (0 !== jQuery('.cls-board-outer').length) {
                jQuery('#md-span-comments').removeClass('comments-loader');
            }
            if (loadComments <= 10) {
                clearInterval(loadComments);
            }
        }, 500);

        jQuery('.cls-board-outer').addClass('is_active');

        // Reset Draft Comments Data.
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        var data = {
            'action': 'reset_drafts_meta',
            'currentPostID': CurrentPostID,
        };
        jQuery.post(ajaxurl, data, function (response) {
        });
    }
}

// Load.
jQuery(window).load(function () {

    const customHistoryButton = '<div class="components-dropdown"><button type="button" aria-expanded="false" class="components-button has-icon" aria-label="Tools"><span class="dashicons dashicons-admin-comments" id="history-toggle"></span></button></div>';
    jQuery('.edit-post-header-toolbar').append(customHistoryButton);

    const customHistoryPopup = '<div id="custom-history-popup"></div>';
    jQuery('.edit-post-layout').append(customHistoryPopup);

    fetchComments();

    const $ = jQuery;
    $(document).on('click', '.components-notice__action', function () {

        if ('View the autosave' === $(this).text()) {
            bring_back_comments();
        }
        if ('Restore the backup' === $(this).text()) {

            setTimeout(function () {
                // Sync popups with highlighted texts.
                jQuery('.wp-block mdspan').each(function () {
                    var selectedText = jQuery(this).attr('datatext');
                    if (jQuery('#' + selectedText).length === 0) {
                        createBoard(selectedText, 'value', 'onChange');
                    }
                });

                bring_back_comments();
            }, 500);

        }

    });

});

function bring_back_comments() {
    var $ = jQuery;

    // Reset Draft Comments Data.
    const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
    var data = {
        'action': 'merge_draft_stacks',
        'currentPostID': CurrentPostID,
    };
    $.post(ajaxurl, data, function (response) {

        response = JSON.parse(response);

        if (response.resolved) {
            $.each(response.resolved, function (k, el) {
                el = el.replace('_', '');
                $('#' + el).addClass('reverted_back resolved');
                // Hide popups if their tags don't exist.
                if (0 === $('[datatext="' + el + '"]').length) {
                    $('#' + el).hide();
                }
            });
        }

        if (response.comments) {
            $.each(response.comments, function (el, timestamps) {
                $.each(timestamps, function (el, t) {
                    $('#' + t).removeClass('publish').addClass('reverted_back added');
                });
            });
        }

        if (response.deleted) {
            $.each(response.deleted, function (el, timestamps) {
                $.each(timestamps, function (el, t) {
                    $('#' + t).removeClass('publish').addClass('reverted_back deleted');
                });
            });
        }

        if (response.edited) {
            //Object.keys(response.edited).map(timestamp => {
            $.each(response.edited, function (el, timestamps) {

                $.each(timestamps, function (el, t) {
                    $('#' + t).removeClass('publish').addClass('reverted_back edited');

                    //const someElement = document.querySelector("#el1588681363428 .board");
                    //const someElement = document.getElementById("1588696591");
                    const someElement = document.getElementById(t);
                    const myComp = FindReact(someElement);
                    myComp.setState({showEditedDraft: true});
                });
            });

        }

    });

    return false;
}

function FindReact(dom, traverseUp = 0) {
    const key = Object.keys(dom).find(key => key.startsWith("__reactInternalInstance$"));
    const domFiber = dom[key];
    if (domFiber == null) return null;

    // react <16
    if (domFiber._currentElement) {
        let compFiber = domFiber._currentElement._owner;
        for (let i = 0; i < traverseUp; i++) {
            compFiber = compFiber._currentElement._owner;
        }
        return compFiber._instance;
    }

    // react 16+
    const GetCompFiber = fiber => {
        //return fiber._debugOwner; // this also works, but is __DEV__ only
        let parentFiber = fiber.return;
        while (typeof parentFiber.type == "string") {
            parentFiber = parentFiber.return;
        }
        return parentFiber;
    };
    let compFiber = GetCompFiber(domFiber);
    for (let i = 0; i < traverseUp; i++) {
        compFiber = GetCompFiber(compFiber);
    }
    return compFiber.stateNode;
}

function createBoard(selectedText, value, onChange) {
    var referenceNode = document.getElementById('md-span-comments');
    var newNode = document.createElement('div');
    newNode.setAttribute("id", selectedText);
    newNode.setAttribute("class", "cls-board-outer is_active");

    referenceNode.appendChild(newNode);
    ReactDOM.render(
        <Board datatext={selectedText} lastVal={value} onChanged={onChange}/>,
        document.getElementById(selectedText)
    )
}

// Register Custom Format Type: Comment.
const name = 'multidots/comment';
const title = __('Comment');
const mdComment = {
    name,
    title,
    tagName: 'mdspan',
    className: 'mdspan-comment',
    attributes: {
        datatext: 'datatext',
        style: 'style'
    },
    edit: (class myClass extends Component {
        constructor(props) {
            super(props);

            this.onToggle = this.onToggle.bind(this);
            this.getSelectedText = this.getSelectedText.bind(this);
            this.storeSelectionValue = this.storeSelectionValue.bind(this);
            this.removeSuggestion = this.removeSuggestion.bind(this);
            this.hidethread = this.hidethread.bind(this);
            this.floatComments = this.floatComments.bind(this);

            this.latestValue = this.latestBoard = '';

        }

        onToggle() {

            var currentTime = Date.now();
            currentTime = 'el' + currentTime;
            var newNode = document.createElement('div');
            newNode.setAttribute("id", currentTime);
            newNode.setAttribute("class", 'cls-board-outer');

            var referenceNode = document.getElementById('md-span-comments');

            referenceNode.appendChild(newNode);

            const simpleCurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            const {value, onChange} = this.props;
            let {text, start, end} = value;

            start = start < 15 ? 0 : (start - 15);
            end = end + 15;

            const commentedOnText = text.substring(start, end);

            window.onChange = onChange;

            onChange(toggleFormat(value, {type: name}),
                ReactDOM.render(
                    <Board datatext={currentTime} onChanged={onChange} lastVal={value} freshBoard={1} commentedOnText={commentedOnText}/>,
                    document.getElementById(currentTime)
                )
            );

            onChange(applyFormat(value, {type: name, attributes: {datatext: currentTime, style: 'background:#fdf0b6'}}));

            this.latestBoard = currentTime;
            this.latestValue = value;

        }

        getSelectedText() {

            var referenceNode = document.getElementById('md-span-comments');

            const {onChange, value, activeAttributes} = this.props;

            if (undefined !== activeAttributes.datatext && activeAttributes.datatext === jQuery('body').attr('remove-comment')) {
                onChange(removeFormat(value, name));
            }

            if (undefined !== this.props.value.start && null !== referenceNode) {
                let selectedText;
                let txtselectedText;

                jQuery('.cls-board-outer').removeClass('has_text');

                // Sync popups with highlighted texts.
                jQuery('.wp-block mdspan').each(function () {

                    // `this` is the div
                    selectedText = jQuery(this).attr('datatext');

                    // This will help to create CTRL-Z'ed Text's popup.
                    // remove this logic... <-- ne_pending, instead, remove highlight after CTRL-Z
                    // because we will not have comments in Board so we should not create new!
                    // user will have to add comment from scratch.
                    if (jQuery('#' + selectedText).length === 0) {

                        if (selectedText !== jQuery('body').attr('remove-comment')) {
                            createBoard(selectedText, value, onChange);
                        } else {
                            //onChange(applyFormat(value, {type: name, attributes: {datatext: currentTime, style: 'background:green'}}));
                            jQuery('[datatext="' + selectedText + '"]').css('background', 'green');
                        }
                    }

                    jQuery('#' + selectedText).addClass('has_text').show();
                });

                //selectedText = jQuery('mdspan[data-rich-text-format-boundary="true"]').attr('datatext');
                selectedText = activeAttributes.datatext;

                // Delete the popup and its highlight if user
                // leaves the new popup without adding comment.
                if (
                    '' !== this.latestBoard
                    && selectedText !== this.latestBoard
                    && 0 !== jQuery('#' + this.latestBoard).length
                    && 0 === jQuery('#' + this.latestBoard + ' .commentContainer').length
                ) {
                    onChange(removeFormat(this.latestValue, name));
                    jQuery('#' + this.latestBoard).remove();
                }

                // If the text removed, remove comment from db and its popup.
                // new_logic ->
                // just hide these popups and only display on CTRLz
                jQuery('.cls-board-outer:not(.has_text)').each(function () {
                    jQuery(this).hide();
                });

                // Adding lastVal and onChanged props to make it deletable,
                // these props were not added on load.
                // It also helps to 'correct' the lastVal of CTRL-Z'ed Text's popup.
                if (jQuery('#' + selectedText).length !== 0) {
                    ReactDOM.render(
                        <Board datatext={selectedText} lastVal={value} onChanged={onChange}/>,
                        document.getElementById(selectedText)
                    )
                }

                // Adding focus on selected text's popup.
                jQuery('.cls-board-outer').removeClass('focus');
                jQuery('#' + selectedText + '.cls-board-outer').addClass('focus');

                // Removing dark highlights from other texts.
                jQuery('mdspan:not([datatext="' + selectedText + '"])').removeAttr('data-rich-text-format-boundary');

                // Float comments column.
                this.floatComments(selectedText);

            }
        }

        storeSelectionValue(value) {
            var objValue = JSON.stringify(value);
            localStorage.setItem('commentVal', objValue);
        }

        floatComments(selectedText) {

            //jQuery(window).scroll(function() {
            if (jQuery('mdspan[data-rich-text-format-boundary="true"]').length !== 0) {
                //let scrollTop = jQuery(window).scrollTop();
                let scrollTop = jQuery('.edit-post-layout__content').scrollTop();
                let commentTop = jQuery('mdspan[data-rich-text-format-boundary="true"]').offset().top;
                let currentPopupTop = jQuery('#' + selectedText + '.cls-board-outer').offset().top;
                let commentColTop = jQuery('#md-span-comments').offset().top;
                let diff = commentTop - currentPopupTop;
                diff = commentColTop + diff + scrollTop;

                jQuery('#md-span-comments').css({
                    'top': diff - 200
                });
            }

        }

        removeSuggestion() {
            const {onChange, value} = this.props;
            onChange(removeFormat(value, name));
        }

        hidethread() {
            jQuery('.cls-board-outer').removeClass('is_active');

        }

        render() {
            const {isActive, inputValue, onChange, value} = this.props;

            return (
                <Fragment>
                    <RichTextToolbarButton
                        title={__('Comment')}
                        isActive={isActive}
                        icon="admin-links"
                        onClick={this.onToggle}
                        shortcutType="primary"
                        shortcutCharacter="m"
                        className={`toolbar-button-with-text toolbar-button__${name}`}
                    />
                    {
                        <Fragment>
                            {this.getSelectedText()}
                        </Fragment>
                    }

                    {!isActive &&
                    <Fragment>
                        {/*{this.hidethread()}*/}
                    </Fragment>
                    }

                </Fragment>
            );
        }
    }),
};
registerFormatType(name, mdComment);
