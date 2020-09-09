import Comment from "./comment";
import React from 'react'
import PropTypes from 'prop-types';

const {removeFormat} = wp.richText;

export default class Board extends React.Component {

    constructor(props) {

        super(props);
        this.displayComments = this.displayComments.bind(this);
        this.updateComment = this.updateComment.bind(this);
        this.removeComment = this.removeComment.bind(this);
        this.addNewComment = this.addNewComment.bind(this);
        this.cancelComment = this.cancelComment.bind(this);
        this.removeTag = this.removeTag.bind(this);

        const currentPostID = wp.data.select('core/editor').getCurrentPostId();
        const postSelections = [];
        let selectedText;
        let txtselectedText;
        let metaselectedText;

        // `this` is the div
        selectedText = this.props.datatext;
        txtselectedText = 'txt' + selectedText;
        metaselectedText = '_' + selectedText;
        setTimeout(function () {
            jQuery('#' + selectedText + ' textarea').attr('id', txtselectedText);
        }, 3000);

        this.commentedOnText = this.props.commentedOnText;

        if (1 !== this.props.freshBoard) {
            wp.apiFetch({path: 'cf/cf-get-comments-api/?currentPostID=' + currentPostID + '&elID=' + metaselectedText}).then(fps => {

                const {userDetails, resolved, commentedOnText} = fps;

                // Update the 'commented on text' if not having value.
                this.commentedOnText = undefined !== this.commentedOnText ? this.commentedOnText : commentedOnText;

                if ('true' === resolved || 0 === userDetails.length) {
                    let elIDRemove = selectedText;
                    this.removeTag(elIDRemove);
                    jQuery('#' + elIDRemove).remove();

                    return false;
                }

                jQuery.each(userDetails, function (key, val) {
                    postSelections.push(val);
                });

                // Add text that the comment is removed.
                if (0 !== postSelections.length) {
                    this.hasComments = 1;
                } else {
                    this.hasComments = 0;
                }

                this.state = {comments: [postSelections]};
                this.setState({comments: postSelections});
            });
        } else {
            try {
                this.currentUserName = wp.data.select("core").getCurrentUser().name;
                const currentUserProfile = wp.data.select("core").getCurrentUser().avatar_urls;
                this.currentUserProfile = currentUserProfile[Object.keys(currentUserProfile)[1]];
            } catch (e) {
                this.currentUserName = localStorage.getItem("userName");
                this.currentUserProfile = localStorage.getItem("userURL");
            }
        }

        this.state = {comments: []};
    }

    removeTag(elIDRemove) {

        const clientId = jQuery('[datatext="' + elIDRemove + '"]').parents('[data-block]').attr('data-block');

        const blockAttributes = wp.data.select('core/block-editor').getBlockAttributes(clientId);
        if( null !== blockAttributes ) {
            const {content} = blockAttributes;
            if ('' !== content) {
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                let childElements = tempDiv.getElementsByTagName('mdspan');
                for (let i = 0; i < childElements.length; i++) {
                    if (elIDRemove === childElements[i].attributes.datatext.value) {
                        childElements[i].parentNode.replaceChild(document.createTextNode(childElements[i].innerText), childElements[i]);
                        let finalContent = tempDiv.innerHTML;
                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                            attributes: {
                                content: finalContent
                            }
                        });
                        break;
                    }
                }
            }
        }
    }

    removeComment(idx, cTimestamp, elID) {

        var arr = this.state.comments;

        arr.splice(idx, 1);
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        elID = '_' + elID;
        var data = {
            'action': 'cf_delete_comment',
            'currentPostID': CurrentPostID,
            'timestamp': cTimestamp,
            metaId: elID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function () {
            // Activate 'Save Draft' or 'Publish' button
            wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } });
        });
        this.setState({comments: arr});

        // Remove 'no comments' msg if available.
        if( 0 === jQuery('.wp-block mdspan').length && 0 === jQuery('.no-comment-found').length ) {
            jQuery('#md-span-comments').append('<p class="no-comment-found">No comments at</p>');
        }
    }

    updateComment(newText, idx, cTimestamp, dateTime, metaID) {

        var arr = this.state.comments;

        var userID = '';
        var userName = '';
        var userProfile = '';
        try {
            userID = wp.data.select("core").getCurrentUser().id;
            userName = wp.data.select("core").getCurrentUser().name;
            userProfile = wp.data.select("core").getCurrentUser().avatar_urls;
            userProfile = userProfile[Object.keys(userProfile)[1]];
        } catch (e) {
            userID = localStorage.getItem("userID");
            userName = localStorage.getItem("userName");
            userProfile = localStorage.getItem("userURL");
        }

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
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        metaID = '_' + metaID;
        var data = {
            'action': 'cf_update_comment',
            'currentPostID': CurrentPostID,
            'editedComment': JSON.stringify(newArr),
            'metaId': metaID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function () {
            // Activate 'Save Draft' or 'Publish' button
            wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } });
        });
        this.setState({comments: arr})
    }

    addNewComment(event) {

        event.preventDefault();

        const {datatext} = this.props;

        var currentTextID = 'txt' + datatext;

        var newText = jQuery('#' + currentTextID).val();

        if ('' !== newText) {

            var userID = '';
            var userName = '';
            var userProfile = '';
            try {
                userID = wp.data.select("core").getCurrentUser().id;
                userName = wp.data.select("core").getCurrentUser().name;
                userProfile = wp.data.select("core").getCurrentUser().avatar_urls;
                userProfile = userProfile[Object.keys(userProfile)[1]];
            } catch (e) {
                userID = localStorage.getItem("userID");
                userName = localStorage.getItem("userName");
                userProfile = localStorage.getItem("userURL");
            }

            var arr = this.state.comments;
            var newArr = {};
            newArr['userData'] = userID;
            newArr['thread'] = newText;
            newArr['commentedOnText'] = undefined !== this.commentedOnText ? this.commentedOnText : '';
            newArr['userName'] = userName;
            newArr['profileURL'] = userProfile;
            newArr['status'] = 'draft reverted_back';

            arr.push(newArr);

            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();

            var el = currentTextID.substring(3);
            var metaId = '_' + el;
            var data = {
                'action': 'cf_add_comment',
                'currentPostID': CurrentPostID,
                'commentList': JSON.stringify(arr),
                'metaId': metaId
            };

            jQuery('#' + el + ' .shareCommentContainer').addClass('loading');
            let _this = this;
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

                // Activate 'Save Draft' or 'Publish' button
                wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } });

                // Set the state.
                _this.setState({comments: arr});

                // Flushing the text from the textarea
                jQuery('#' + currentTextID).val('').focus();

                // Remove 'no comments' msg if available.
                if( 0 !== jQuery('.no-comment-found').length ) {
                    jQuery('.no-comment-found').remove();
                }
            });

        } else alert("Please write a comment to share!")

    }

    displayComments(text, i) {

        const {lastVal, onChanged, selectedText} = this.props;

        let username, postedTime, postedComment, profileURL, userID, status, cTimestamp, editedDraft;
        Object.keys(text).map(i => {
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

        return (

            <Comment
                key={i}
                index={i}
                removeCommentFromBoard={this.removeComment}
                updateCommentFromBoard={this.updateComment}
                userName={username}
                dateTime={postedTime}
                profileURL={profileURL}
                userID={userID}
                status={status}
                lastVal={lastVal}
                onChanged={onChanged}
                selectedText={selectedText}
                timestamp={cTimestamp}
                editedDraft={editedDraft}
            >{
                postedComment = postedComment ? postedComment : text
            }
            </Comment>

        );

    }

    cancelComment() {
        const {datatext, onChanged, lastVal} = this.props;
        const name = 'multidots/comment';
        jQuery('#'+ datatext).removeClass('focus');

        if ( 0 === jQuery('#'+ datatext + ' .boardTop .commentContainer').length ) {
            onChanged(removeFormat(lastVal, name));
        }
    }

    componentDidMount() {
        if(this.props.freshBoard) {
            const datatext = this.props.datatext;
            setTimeout(function(){
                jQuery( '#txt' + datatext ).focus();
            },500);
        }
    }

    render() {
        const {datatext} = this.props;
        const buttonText = 1 === this.hasComments && 1 !== this.props.freshBoard ? 'Reply' : 'Comment';

        return (
            <div className={`board ${undefined === this.hasComments && this.currentUserProfile && 'fresh-board'}`}>
                <div className="boardTop">
                    {
                        this.state.comments && this.state.comments.map((item, index) => {
                            return this.displayComments(item, index);
                        })
                    }
                </div>
                {undefined === this.hasComments && this.currentUserProfile &&
                    <div className="commentContainer">
                        <div className="comment-header">
                            <div className="comment-details">
                                <div className="avtar"><img src={this.currentUserProfile} alt="avatar"/></div>
                                <div className="commenter-name-time">
                                    <div className="commenter-name">{this.currentUserName}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                }
                <div className="shareCommentContainer">
                    <textarea id={"txt" + datatext} placeholder="Write a comment.."></textarea>
                    <button onClick={this.addNewComment} className="btn btn-success">{buttonText}</button>
                    <button onClick={this.cancelComment} className="btn btn-cancel">Cancel</button>
                </div>
            </div>
        );
    }
}

// Typecheck.
Board.propTypes = {
    lastVal: PropTypes.object,
    datatext: PropTypes.string,
    onChanged: PropTypes.func,
    selectedText: PropTypes.string,
    commentedOnText: PropTypes.string,
    freshBoard: PropTypes.number,
    onLoadFetch: PropTypes.number,
};
