/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__component_board__ = __webpack_require__(1);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }



var __ = wp.i18n.__;
var _wp$element = wp.element,
    Fragment = _wp$element.Fragment,
    Component = _wp$element.Component;
var toggleFormat = wp.richText.toggleFormat;
var RichTextToolbarButton = wp.blockEditor.RichTextToolbarButton;
var _wp$richText = wp.richText,
    registerFormatType = _wp$richText.registerFormatType,
    applyFormat = _wp$richText.applyFormat,
    removeFormat = _wp$richText.removeFormat;


function fetchComments() {

    var newNode = document.createElement('div');
    newNode.setAttribute("id", 'md-span-comments');
    newNode.setAttribute("class", 'comments-loader');
    var referenceNode = document.querySelector('.block-editor-writing-flow');
    if (null !== referenceNode) {
        referenceNode.appendChild(newNode);

        var selectedText = void 0;
        var txtselectedText = void 0;
        var allThreads = [];

        jQuery('.wp-block mdspan').each(function () {

            selectedText = jQuery(this).attr('datatext');

            if (jQuery('#' + selectedText).length === 0) {

                var newNode = document.createElement('div');
                newNode.setAttribute("id", selectedText);
                newNode.setAttribute("class", "cls-board-outer is_active");

                var referenceNode = document.getElementById('md-span-comments');
                referenceNode.appendChild(newNode);

                ReactDOM.render(wp.element.createElement(__WEBPACK_IMPORTED_MODULE_0__component_board__["a" /* default */], { datatext: selectedText }), document.getElementById(selectedText));
            }
            allThreads.push(selectedText);
        });

        var loadAttempts = 0;
        var loadComments = setInterval(function () {
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
        var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        var data = {
            'action': 'reset_drafts_meta',
            'currentPostID': CurrentPostID
        };
        jQuery.post(ajaxurl, data, function (response) {});
    }
}

// Load.
jQuery(window).load(function () {

    var customHistoryButton = '<div class="components-dropdown"><button type="button" aria-expanded="false" class="components-button has-icon" aria-label="Tools"><span class="dashicons dashicons-admin-comments" id="history-toggle"></span></button></div>';
    jQuery('.edit-post-header-toolbar').append(customHistoryButton);

    var customHistoryPopup = '<div id="custom-history-popup"></div>';
    jQuery('.edit-post-layout').append(customHistoryPopup);

    fetchComments();

    var $ = jQuery;
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
    var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
    var data = {
        'action': 'merge_draft_stacks',
        'currentPostID': CurrentPostID
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
                    $('#' + t).remove();
                    //$('#' + t).removeClass('publish').addClass('reverted_back deleted');
                });
            });
        }

        if (response.edited) {
            $.each(response.edited, function (el, timestamps) {

                $.each(timestamps, function (el, t) {
                    $('#' + t).removeClass('publish').addClass('reverted_back edited');

                    // Update the component with new text.
                    var someElement = document.getElementById(t);
                    var myComp = FindReact(someElement);
                    myComp.setState({ showEditedDraft: true });

                    $('#' + t + ' .commentText').append(' <i style="font-size:12px;color:#23282dba">(edited)</i>');
                });
            });
        }
    });

    return false;
}

function FindReact(dom) {
    var traverseUp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

    var key = Object.keys(dom).find(function (key) {
        return key.startsWith("__reactInternalInstance$");
    });
    var domFiber = dom[key];
    if (domFiber == null) return null;

    // react <16
    if (domFiber._currentElement) {
        var _compFiber = domFiber._currentElement._owner;
        for (var i = 0; i < traverseUp; i++) {
            _compFiber = _compFiber._currentElement._owner;
        }
        return _compFiber._instance;
    }

    // react 16+
    var GetCompFiber = function GetCompFiber(fiber) {
        //return fiber._debugOwner; // this also works, but is __DEV__ only
        var parentFiber = fiber.return;
        while (typeof parentFiber.type == "string") {
            parentFiber = parentFiber.return;
        }
        return parentFiber;
    };
    var compFiber = GetCompFiber(domFiber);
    for (var _i = 0; _i < traverseUp; _i++) {
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
    ReactDOM.render(wp.element.createElement(__WEBPACK_IMPORTED_MODULE_0__component_board__["a" /* default */], { datatext: selectedText, lastVal: value, onChanged: onChange }), document.getElementById(selectedText));
}

// Register Custom Format Type: Comment.
var name = 'multidots/comment';
var title = __('Comment');
var mdComment = {
    name: name,
    title: title,
    tagName: 'mdspan',
    className: 'mdspan-comment',
    attributes: {
        datatext: 'datatext',
        style: 'style'
    },
    edit: function (_Component) {
        _inherits(myClass, _Component);

        function myClass(props) {
            _classCallCheck(this, myClass);

            var _this = _possibleConstructorReturn(this, (myClass.__proto__ || Object.getPrototypeOf(myClass)).call(this, props));

            _this.onToggle = _this.onToggle.bind(_this);
            _this.getSelectedText = _this.getSelectedText.bind(_this);
            _this.storeSelectionValue = _this.storeSelectionValue.bind(_this);
            _this.removeSuggestion = _this.removeSuggestion.bind(_this);
            _this.hidethread = _this.hidethread.bind(_this);
            _this.floatComments = _this.floatComments.bind(_this);

            _this.latestValue = _this.latestBoard = '';

            return _this;
        }

        _createClass(myClass, [{
            key: 'onToggle',
            value: function onToggle() {

                var currentTime = Date.now();
                currentTime = 'el' + currentTime;
                var newNode = document.createElement('div');
                newNode.setAttribute("id", currentTime);
                newNode.setAttribute("class", 'cls-board-outer');

                var referenceNode = document.getElementById('md-span-comments');

                referenceNode.appendChild(newNode);

                var simpleCurrentPostID = wp.data.select('core/editor').getCurrentPostId();
                var _props = this.props,
                    value = _props.value,
                    onChange = _props.onChange;
                var text = value.text,
                    start = value.start,
                    end = value.end;


                var commentedOnText = text.substring(start, end);

                onChange(toggleFormat(value, { type: name }), ReactDOM.render(wp.element.createElement(__WEBPACK_IMPORTED_MODULE_0__component_board__["a" /* default */], { datatext: currentTime, onChanged: onChange, lastVal: value, freshBoard: 1, commentedOnText: commentedOnText }), document.getElementById(currentTime)));

                onChange(applyFormat(value, { type: name, attributes: { datatext: currentTime, style: 'background:#fdf0b6' } }));

                this.latestBoard = currentTime;
                this.latestValue = value;
            }
        }, {
            key: 'getSelectedText',
            value: function getSelectedText() {

                var referenceNode = document.getElementById('md-span-comments');

                var _props2 = this.props,
                    onChange = _props2.onChange,
                    value = _props2.value,
                    activeAttributes = _props2.activeAttributes;

                var removedComments = jQuery('body').attr('remove-comment');
                if (undefined !== activeAttributes.datatext && undefined !== removedComments && removedComments.indexOf(activeAttributes.datatext) !== -1) {
                    onChange(removeFormat(value, name));
                }

                if (undefined !== this.props.value.start && null !== referenceNode) {
                    var selectedText = void 0;
                    var txtselectedText = void 0;

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

                            var _removedComments = jQuery('body').attr('remove-comment');
                            if (undefined === _removedComments || undefined !== _removedComments && _removedComments.indexOf(selectedText) === -1) {
                                createBoard(selectedText, value, onChange);
                            } else {
                                jQuery('[datatext="' + selectedText + '"]').css('background', 'transparent');
                            }
                        }

                        jQuery('#' + selectedText).addClass('has_text').show();
                    });

                    //selectedText = jQuery('mdspan[data-rich-text-format-boundary="true"]').attr('datatext');
                    selectedText = activeAttributes.datatext;

                    // Delete the popup and its highlight if user
                    // leaves the new popup without adding comment.
                    if ('' !== this.latestBoard && selectedText !== this.latestBoard && 0 !== jQuery('#' + this.latestBoard).length && 0 === jQuery('#' + this.latestBoard + ' .commentContainer').length) {
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
                        ReactDOM.render(wp.element.createElement(__WEBPACK_IMPORTED_MODULE_0__component_board__["a" /* default */], { datatext: selectedText, lastVal: value, onChanged: onChange }), document.getElementById(selectedText));
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
        }, {
            key: 'storeSelectionValue',
            value: function storeSelectionValue(value) {
                var objValue = JSON.stringify(value);
                localStorage.setItem('commentVal', objValue);
            }
        }, {
            key: 'floatComments',
            value: function floatComments(selectedText) {

                //jQuery(window).scroll(function() {
                if (jQuery('mdspan[data-rich-text-format-boundary="true"]').length !== 0) {
                    //let scrollTop = jQuery(window).scrollTop();
                    var scrollTop = jQuery('.edit-post-layout__content').scrollTop();
                    var commentTop = jQuery('mdspan[data-rich-text-format-boundary="true"]').offset().top;
                    var currentPopupTop = jQuery('#' + selectedText + '.cls-board-outer').offset().top;
                    var commentColTop = jQuery('#md-span-comments').offset().top;
                    var diff = commentTop - currentPopupTop;
                    diff = commentColTop + diff + scrollTop;

                    jQuery('#md-span-comments').css({
                        'top': diff - 200
                    });
                }
            }
        }, {
            key: 'removeSuggestion',
            value: function removeSuggestion() {
                var _props3 = this.props,
                    onChange = _props3.onChange,
                    value = _props3.value;

                onChange(removeFormat(value, name));
            }
        }, {
            key: 'hidethread',
            value: function hidethread() {
                jQuery('.cls-board-outer').removeClass('is_active');
            }
        }, {
            key: 'render',
            value: function render() {
                var _props4 = this.props,
                    isActive = _props4.isActive,
                    inputValue = _props4.inputValue,
                    onChange = _props4.onChange,
                    value = _props4.value;


                return wp.element.createElement(
                    Fragment,
                    null,
                    wp.element.createElement(RichTextToolbarButton, {
                        title: __('Comment'),
                        isActive: isActive,
                        icon: 'admin-links',
                        onClick: this.onToggle,
                        shortcutType: 'primary',
                        shortcutCharacter: 'm',
                        className: 'toolbar-button-with-text toolbar-button__' + name
                    }),
                    wp.element.createElement(
                        Fragment,
                        null,
                        this.getSelectedText()
                    ),
                    !isActive && wp.element.createElement(Fragment, null)
                );
            }
        }]);

        return myClass;
    }(Component)
};
registerFormatType(name, mdComment);

/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__comment__ = __webpack_require__(2);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }



var removeFormat = wp.richText.removeFormat;

var Board = function (_React$Component) {
    _inherits(Board, _React$Component);

    function Board(props) {
        _classCallCheck(this, Board);

        var _this2 = _possibleConstructorReturn(this, (Board.__proto__ || Object.getPrototypeOf(Board)).call(this, props));

        _this2.displayComments = _this2.displayComments.bind(_this2);
        _this2.updateComment = _this2.updateComment.bind(_this2);
        _this2.removeComment = _this2.removeComment.bind(_this2);
        _this2.addNewComment = _this2.addNewComment.bind(_this2);
        _this2.removeSuggestion = _this2.removeSuggestion.bind(_this2);
        _this2.acceptSuggestion = _this2.acceptSuggestion.bind(_this2);
        var currentPostID = wp.data.select('core/editor').getCurrentPostId();
        var postSelections = [];
        var selectedText = void 0;
        var txtselectedText = void 0;
        var metaselectedText = void 0;

        // `this` is the div
        selectedText = _this2.props.datatext;
        txtselectedText = 'txt' + selectedText;
        metaselectedText = '_' + selectedText;
        setTimeout(function () {
            jQuery('#' + selectedText + ' textarea').attr('id', txtselectedText);
        }, 3000);

        _this2.commentedOnText = _this2.props.commentedOnText;

        if (1 !== _this2.props.freshBoard) {
            var allPosts = wp.apiFetch({ path: 'career-data-by-select1/my-route1/?currentPostID=' + currentPostID + '&elID=' + metaselectedText }).then(function (fps) {
                var userDetails = fps.userDetails,
                    resolved = fps.resolved,
                    commentedOnText = fps.commentedOnText;

                // Update the 'commented on text' if not having value.

                _this2.commentedOnText = undefined !== _this2.commentedOnText ? _this2.commentedOnText : commentedOnText;

                _this2.props.resolved = resolved;
                if ('true' === resolved || 0 === userDetails.length) {
                    var elIDRemove = selectedText;
                    var removed_comments = jQuery('body').attr('remove-comment');
                    removed_comments = undefined !== removed_comments ? removed_comments + ',' + elIDRemove : elIDRemove;
                    jQuery('body').attr('remove-comment', removed_comments);
                    jQuery('body').append('<style>[datatext="' + elIDRemove + '"] {background-color:transparent !important;}</style>');
                    jQuery('[datatext="' + elIDRemove + '"]').addClass('removed');
                    jQuery('#' + elIDRemove).remove();

                    return false;
                }

                jQuery.each(userDetails, function (key, val) {
                    postSelections.push(val);
                });

                // Add text that the comment is removed.
                if (0 !== postSelections.length) {
                    _this2.hasComments = 1;
                } else {
                    _this2.hasComments = 0;
                }

                _this2.state = { comments: [postSelections] };
                _this2.setState({ comments: postSelections });
            });
        }

        _this2.state = { comments: [] };
        return _this2;
    }

    _createClass(Board, [{
        key: 'removeComment',
        value: function removeComment(idx, cTimestamp, elID) {
            var arr = this.state.comments;

            arr.splice(idx, 1);
            var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            var _props = this.props,
                value = _props.value,
                onChange = _props.onChange;

            elID = '_' + elID;
            var data = {
                'action': 'my_action_delete',
                'currentPostID': CurrentPostID,
                'timestamp': cTimestamp,
                metaId: elID
            };
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function (response) {});
            this.setState({ comments: arr });
        }
    }, {
        key: 'updateComment',
        value: function updateComment(newText, idx, cTimestamp, dateTime, metaID) {
            var arr = this.state.comments;
            var userID = wp.data.select("core").getCurrentUser().id;
            var userName = wp.data.select("core").getCurrentUser().name;
            var userProfile = wp.data.select("core").getCurrentUser().avatar_urls;
            userProfile = userProfile[Object.keys(userProfile)[1]];

            var newArr = {};
            newArr['userName'] = userName;
            newArr['profileURL'] = userProfile;
            newArr['dtTime'] = dateTime;
            newArr['thread'] = newText;
            newArr['userData'] = userID;
            newArr['index'] = idx;
            newArr['status'] = 'draft reverted_back';
            newArr['timestamp'] = cTimestamp;
            arr[idx] = newArr;
            var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            metaID = '_' + metaID;
            var data = {
                'action': 'my_action_edit',
                'currentPostID': CurrentPostID,
                'editedComment': JSON.stringify(newArr),
                'metaId': metaID
            };
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            var _this = this;

            jQuery.post(ajaxurl, data, function () {});
            this.setState({ comments: arr });
        }
    }, {
        key: 'addNewComment',
        value: function addNewComment(event) {
            event.preventDefault();

            var datatext = this.props.datatext;


            var currentTextID = 'txt' + datatext;

            var newText = jQuery('#' + currentTextID).val();

            if ('' !== newText) {

                var userID = wp.data.select("core").getCurrentUser().id;
                var userName = wp.data.select("core").getCurrentUser().name;
                var userProfile = wp.data.select("core").getCurrentUser().avatar_urls;
                userProfile = userProfile[Object.keys(userProfile)[1]];

                var arr = this.state.comments;
                var newArr = {};
                newArr['userData'] = userID;
                newArr['thread'] = newText;
                newArr['commentedOnText'] = undefined !== this.commentedOnText ? this.commentedOnText : '';
                newArr['userName'] = userName;
                newArr['profileURL'] = userProfile;
                newArr['status'] = 'draft reverted_back';

                arr.push(newArr);

                var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();

                var el = currentTextID.substring(3);
                var metaId = '_' + el;
                var data = {
                    'action': 'my_action',
                    'currentPostID': CurrentPostID,
                    'commentList': JSON.stringify(arr),
                    'metaId': metaId
                };

                jQuery('#' + el + ' .shareCommentContainer').addClass('loading');
                var _this = this;
                jQuery.post(ajaxurl, data, function (data) {

                    jQuery('#' + el + ' .shareCommentContainer').removeClass('loading');

                    data = jQuery.parseJSON(data);
                    if (undefined !== data.error) {
                        alert(data.error);
                        return false;
                    }
                    arr[arr.length - 1]['dtTime'] = data.dtTime;
                    arr[arr.length - 1]['timestamp'] = data.timestamp;

                    // Update hasComment prop for dynamic button text.
                    _this.hasComments = 1;

                    // Set the state.
                    _this.setState({ comments: arr });

                    // Flushing the text from the textarea
                    jQuery('#' + currentTextID).val('');
                    jQuery('#' + datatext + ' .no-comments').remove();
                });
            } else alert("Please write a comment to share!");
        }
    }, {
        key: 'removeSuggestion',
        value: function removeSuggestion(event) {
            if (confirm('Are you sure you want to delete this thread ?')) {
                var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
                elID = elID[0].id;
                var elIDRemove = elID;
                var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
                alert(elID);
                var _props2 = this.props,
                    lastVal = _props2.lastVal,
                    onChanged = _props2.onChanged;


                onChanged(removeFormat(lastVal, name2));
            }
        }
    }, {
        key: 'acceptSuggestion',
        value: function acceptSuggestion(event) {
            if (confirm('Are you sure you want to delete this thread ?')) {
                var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
                elID = elID[0].id;
                var elIDRemove = elID;
                var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
                var _props3 = this.props,
                    value = _props3.value,
                    onChange = _props3.onChange;

                elID = '_' + elID;

                var data = {
                    'action': 'resolve_thread',
                    'currentPostID': CurrentPostID,
                    'metaId': elID
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(ajaxurl, data, function (response) {
                    if (response == true) {
                        jQuery('div#' + elIDRemove).remove();
                    } else {
                        alert('wrong');
                    }
                });
                var _props4 = this.props,
                    lastVal = _props4.lastVal,
                    onChanged = _props4.onChanged;

                onChanged(removeFormat(lastVal, name2));
            }
        }
    }, {
        key: 'displayComments',
        value: function displayComments(text, i) {
            var _props5 = this.props,
                isActive = _props5.isActive,
                inputValue = _props5.inputValue,
                myval2 = _props5.myval2,
                value = _props5.value; /*onChange*/

            var _props6 = this.props,
                lastVal = _props6.lastVal,
                onChanged = _props6.onChanged,
                selectedText = _props6.selectedText,
                suserProfile = _props6.suserProfile,
                suserName = _props6.suserName;


            var username = void 0,
                postedTime = void 0,
                postedComment = void 0,
                profileURL = void 0,
                userID = void 0,
                status = void 0,
                cTimestamp = void 0,
                editedDraft = void 0; /*value, onChange*/
            Object.keys(text).map(function (i) {
                if ('userName' === i) {
                    username = text[i];
                } else if ('dtTime' === i) {
                    postedTime = text[i];
                } else if ('thread' === i) {
                    postedComment = text[i];
                } else if ('profileURL' === i) {
                    profileURL = text[i];
                } else if ('userData' === i) {
                    userID = text[i];
                } else if ('status' === i) {
                    status = text[i];
                } else if ('timestamp' === i) {
                    cTimestamp = text[i];
                } else if ('editedDraft' === i) {
                    editedDraft = text[i];
                }
            });

            return wp.element.createElement(
                __WEBPACK_IMPORTED_MODULE_0__comment__["a" /* default */],
                {
                    key: i,
                    index: i,
                    removeCommentFromBoard: this.removeComment,
                    updateCommentFromBoard: this.updateComment,
                    userName: username,
                    dateTime: postedTime,
                    profileURL: profileURL,
                    userID: userID,
                    status: status,
                    lastVal: lastVal,
                    onChanged: onChanged
                    /*lastVal={value}
                    onChanged={onChange}*/
                    , selectedText: selectedText,
                    timestamp: cTimestamp,
                    editedDraft: editedDraft
                },
                postedComment = postedComment ? postedComment : text
            );
        }
    }, {
        key: 'render',
        value: function render() {
            var _this3 = this;

            var _props7 = this.props,
                isActive = _props7.isActive,
                inputValue = _props7.inputValue,
                onChange = _props7.onChange,
                value = _props7.value,
                myval2 = _props7.myval2,
                selectedText = _props7.selectedText,
                datatext = _props7.datatext;

            var buttonText = 1 === this.hasComments && 1 !== this.props.freshBoard ? 'Reply' : 'Comment';

            return wp.element.createElement(
                'div',
                { className: 'board' },
                wp.element.createElement(
                    'div',
                    { className: 'boardTop' },
                    0 === this.hasComments && wp.element.createElement(
                        'div',
                        { className: 'no-comments' },
                        wp.element.createElement(
                            'i',
                            null,
                            'The are no comments!'
                        )
                    ),
                    this.state.comments && this.state.comments.map(function (item, index) {
                        return _this3.displayComments(item, index);
                    })
                ),
                wp.element.createElement(
                    'div',
                    { className: 'shareCommentContainer' },
                    wp.element.createElement('textarea', { id: "txt" + datatext, placeholder: 'Write a comment..' }),
                    wp.element.createElement(
                        'button',
                        { onClick: this.addNewComment, className: 'btn btn-success' },
                        buttonText
                    )
                )
            );
        }
    }]);

    return Board;
}(React.Component);

/* harmony default export */ __webpack_exports__["a"] = (Board);

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var Fragment = wp.element.Fragment;
var removeFormat = wp.richText.removeFormat;

var Comment = function (_React$Component) {
    _inherits(Comment, _React$Component);

    function Comment(props) {
        _classCallCheck(this, Comment);

        var _this = _possibleConstructorReturn(this, (Comment.__proto__ || Object.getPrototypeOf(Comment)).call(this, props));

        _this.edit = _this.edit.bind(_this);
        _this.save = _this.save.bind(_this);
        _this.remove = _this.remove.bind(_this);
        _this.resolve = _this.resolve.bind(_this);
        _this.cancelEdit = _this.cancelEdit.bind(_this);
        _this.state = { editing: false, showEditedDraft: false };
        return _this;
    }

    _createClass(Comment, [{
        key: 'edit',
        value: function edit() {
            this.setState({ editing: true });
        }
    }, {
        key: 'save',
        value: function save(event) {

            var newText = this.newText.value;
            if ('' === newText) {
                alert("Please write a comment to share!");
                return false;
            }
            var metaId = this.newText.id.substring(3);
            var elID = event.currentTarget.parentElement.parentElement.parentElement.parentElement.id;
            this.props.updateCommentFromBoard(newText, this.props.index, this.props.timestamp, this.props.dateTime, elID);

            this.setState({ editing: false });
        }
    }, {
        key: 'remove',
        value: function remove(event) {

            if (confirm('Are you sure you want to delete this comment ?')) {
                var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
                //	var elID = event.currentTarget.parentElement.parentElement.parentElement.id;

                this.props.removeCommentFromBoard(this.props.index, this.props.timestamp, elID[0].id);
            }
        }
    }, {
        key: 'resolve',
        value: function resolve(event) {
            //const myvalue = this.props.myval2;
            if (confirm('Are you sure you want to delete this thread ?')) {
                var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
                elID = elID[0].id;
                var elIDRemove = elID;
                var CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
                var _props = this.props,
                    value = _props.value,
                    onChange = _props.onChange;

                elID = '_' + elID;

                var data = {
                    'action': 'resolve_thread',
                    'currentPostID': CurrentPostID,
                    'metaId': elID
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery('div#' + elIDRemove).remove();
                });

                var name = 'multidots/comment';

                var _props2 = this.props,
                    lastVal = _props2.lastVal,
                    onChanged = _props2.onChanged;

                var removedComments = jQuery('body').attr('remove-comment');
                removedComments = undefined !== removedComments ? removedComments + ',' + elIDRemove : elIDRemove;
                jQuery('body').attr('remove-comment', elIDRemove);
                jQuery('body').append('<style>[datatext="' + elIDRemove + '"] {background-color:transparent !important;}</style>');

                if (null === onChanged || undefined === onChanged) {
                    jQuery('[datatext="' + elIDRemove + '"]').addClass('removed');
                } else {
                    onChanged(removeFormat(lastVal, name));
                }
            }
        }
    }, {
        key: 'cancelEdit',
        value: function cancelEdit() {
            this.setState({ editing: false });
        }
    }, {
        key: 'renderNormalMode',
        value: function renderNormalMode() {
            var _props3 = this.props,
                lastVal = _props3.lastVal,
                onChanged = _props3.onChanged,
                selectedText = _props3.selectedText,
                index = _props3.index;

            this.props.status = this.props.status ? this.props.status : 'draft';

            var owner = wp.data.select("core").getCurrentUser().id;
            return wp.element.createElement(
                'div',
                { className: "commentContainer " + this.props.status, id: this.props.timestamp },
                wp.element.createElement(
                    'div',
                    { className: 'comment-header' },
                    wp.element.createElement(
                        'div',
                        { className: 'avtar' },
                        wp.element.createElement('img', { src: this.props.profileURL, alt: 'avatar' })
                    ),
                    wp.element.createElement(
                        'div',
                        { className: 'commenter-name-time' },
                        wp.element.createElement(
                            'div',
                            { className: 'commenter-name' },
                            this.props.userName
                        ),
                        wp.element.createElement(
                            'div',
                            { className: 'comment-time' },
                            this.props.dateTime
                        )
                    ),
                    index === 0 && wp.element.createElement(
                        'button',
                        { onClick: this.resolve.bind(this), className: 'btn-comment' },
                        'Resolve'
                    ),
                    wp.element.createElement(
                        'div',
                        { className: 'buttons-holder' },
                        wp.element.createElement(
                            'div',
                            { className: 'buttons-opner' },
                            wp.element.createElement(
                                Fragment,
                                null,
                                this.props.userID === owner && wp.element.createElement(
                                    'svg',
                                    { 'aria-hidden': 'true', role: 'img', focusable: 'false', className: 'dashicon dashicons-ellipsis',
                                        xmlns: 'http://www.w3.org/2000/svg', width: '20', height: '20', viewBox: '0 0 20 20' },
                                    wp.element.createElement('path', {
                                        d: 'M5 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm12-2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-7 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z' })
                                )
                            )
                        ),
                        wp.element.createElement(
                            Fragment,
                            null,
                            this.props.userID === owner && wp.element.createElement(
                                'div',
                                { className: 'buttons-wrapper' },
                                wp.element.createElement(
                                    'button',
                                    { onClick: this.edit, className: 'btn btn-comment' },
                                    'Edit'
                                ),
                                wp.element.createElement(
                                    'button',
                                    { onClick: index === 0 ? this.resolve.bind(this) : this.remove.bind(this), className: 'btn btn-comment' },
                                    'Delete'
                                )
                            )
                        )
                    )
                ),
                wp.element.createElement(
                    'div',
                    { className: 'commentText' },
                    this.state.showEditedDraft ? this.props.editedDraft : this.props.children
                )
            );
        }
    }, {
        key: 'renderEditingMode',
        value: function renderEditingMode() {
            var _this2 = this;

            return wp.element.createElement(
                'div',
                { className: 'commentContainer' },
                wp.element.createElement(
                    'div',
                    { className: 'commentText' },
                    wp.element.createElement('textarea', {
                        ref: function ref(input) {
                            _this2.newText = input;
                        },
                        onChange: this.handleChange,
                        defaultValue: this.state.showEditedDraft ? this.props.editedDraft : this.props.children })
                ),
                wp.element.createElement(
                    'button',
                    { onClick: this.save.bind(this), className: 'btn-comment' },
                    'Save'
                ),
                wp.element.createElement(
                    'button',
                    { onClick: this.cancelEdit.bind(this), className: 'btn-comment' },
                    'Cancel'
                )
            );
        }
    }, {
        key: 'render',
        value: function render() {

            if (this.state.editing) {
                return this.renderEditingMode();
            } else {
                return this.renderNormalMode();
            }
        }
    }]);

    return Comment;
}(React.Component);

/* harmony default export */ __webpack_exports__["a"] = (Comment);

/***/ })
/******/ ]);