import Comment from "./comment";
import React from 'react'
import PropTypes from 'prop-types';

const $ = jQuery; // eslint-disable-line
const {removeFormat} = wp.richText; // eslint-disable-line
export default class Board extends React.Component {

    constructor(props) {

        super(props);
        this.displayComments = this.displayComments.bind(this);
        this.updateComment = this.updateComment.bind(this);
        this.removeComment = this.removeComment.bind(this);
        this.addNewComment = this.addNewComment.bind(this);
        this.cancelComment = this.cancelComment.bind(this);

        const currentPostID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line
        const postSelections = [];
        let selectedText;
        let txtselectedText;
        let metaselectedText;

        // `this` is the div
        selectedText = this.props.datatext;
        txtselectedText = 'txt' + selectedText;
        metaselectedText = '_' + selectedText;
        setTimeout(function () {
            $('#' + selectedText + ' textarea').attr('id', txtselectedText);
        }, 3000);

        this.commentedOnText = this.props.commentedOnText;

        if (1 !== this.props.freshBoard) {
            wp.apiFetch({path: 'cf/cf-get-comments-api/?currentPostID=' + currentPostID + '&elID=' + metaselectedText}).then(fps => { // eslint-disable-line

                const {userDetails, resolved, commentedOnText, assignedTo} = fps;

                // Update the 'commented on text' if not having value.
                this.commentedOnText = undefined !== this.commentedOnText ? this.commentedOnText : commentedOnText;
                this.assignedTo = assignedTo;

                if ('true' === resolved || 0 === userDetails.length) {
                    let elIDRemove = selectedText;
                    removeTag(elIDRemove); // eslint-disable-line
                    $('#' + elIDRemove).remove();

                    return false;
                }

                $.each(userDetails, function (key, val) {
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
                this.currentUserName = wp.data.select("core").getCurrentUser().name; // eslint-disable-line
                const currentUserProfile = wp.data.select("core").getCurrentUser().avatar_urls; // eslint-disable-line
                this.currentUserProfile = currentUserProfile[Object.keys(currentUserProfile)[1]];
            } catch (e) {
                this.currentUserName = localStorage.getItem("userName");
                this.currentUserProfile = localStorage.getItem("userURL");
            }
        }

        this.state = {comments: []};
    }

    removeComment(idx, cTimestamp, elID) {

        var arr = this.state.comments;

        arr.splice(idx, 1);
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line
        elID = '_' + elID;
        var data = {
            'action': 'cf_delete_comment',
            'currentPostID': CurrentPostID,
            'timestamp': cTimestamp,
            metaId: elID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function () { // eslint-disable-line
            // Activate 'Save Draft' or 'Publish' button
            wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } }); // eslint-disable-line
        });
        this.setState({comments: arr});
    }

    updateComment(newText, idx, cTimestamp, dateTime, metaID) {

        var arr = this.state.comments;

        var userID = '';
        var userName = '';
        var userRole = '';
        var userProfile = '';
        try {
            userID = wp.data.select("core").getCurrentUser().id; // eslint-disable-line
            userName = wp.data.select("core").getCurrentUser().name; // eslint-disable-line
            userRole = wp.data.select("core").getUser(userID).roles[0]; // eslint-disable-line
            userProfile = wp.data.select("core").getCurrentUser().avatar_urls; // eslint-disable-line
            userProfile = userProfile[Object.keys(userProfile)[1]];
        } catch (e) {
            userID = localStorage.getItem("userID");
            userName = localStorage.getItem("userName");
            userRole = localStorage.getItem("userRole");
            userProfile = localStorage.getItem("userURL");
        }

        var newArr           = {};
        newArr['userName']   = userName;
        newArr['userRole']   = userRole;
        newArr['profileURL'] = userProfile;
        newArr['dtTime']     = dateTime;
        newArr['thread']     = newText;
        newArr['userData']   = userID;
        newArr['index']      = idx;
        newArr['status']     = 'draft reverted_back';
        newArr['timestamp']  = cTimestamp;
        arr[idx]             = newArr;
        const CurrentPostID  = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line
        metaID               = '_' + metaID;
        var data = {
            'action': 'cf_update_comment',
            'currentPostID': CurrentPostID,
            'editedComment': JSON.stringify(newArr),
            'metaId': metaID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function () { // eslint-disable-line
            // Activate 'Save Draft' or 'Publish' button
            wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } }); // eslint-disable-line
        });
        this.setState({comments: arr})
    }

    addNewComment(event) {
        event.preventDefault();
        const {datatext}  = this.props;
        var currentTextID = 'txt' + datatext;
        var newText       = $('#' + currentTextID).html();
        newText           = newText.replace( /<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/gi, '' );
        newText           = newText.replace( /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/ig, function( match ) {
            return `<a href="${match}" target="_blank">${match}</a>`;
        } );

        if ($(`#${currentTextID}`).text().trim().length !== 0) {

            var userID = '';
            var userName = '';
            var userRole = '';
            var userProfile = '';
            try {
                userID = wp.data.select("core").getCurrentUser().id; // eslint-disable-line
                userRole = wp.data.select("core").getUser(userID).roles[0]; // eslint-disable-line
                userName = wp.data.select("core").getCurrentUser().name; // eslint-disable-line
            } catch (e) {
                userID = localStorage.getItem("userID");
                userName = localStorage.getItem("userName");
                userRole = localStorage.getItem("userRole");
            }

            if( '1' === localStorage.getItem("showAvatars") ) {
                userProfile = wp.data.select("core").getCurrentUser().avatar_urls; // eslint-disable-line
                userProfile = userProfile[Object.keys(userProfile)[1]];
            } else {
                userProfile = localStorage.getItem("userURL");
            }

            var arr = this.state.comments;
            var newArr = {};
            newArr['userData'] = userID;
            newArr['thread'] = newText;
            newArr['commentedOnText'] = undefined !== this.commentedOnText ? this.commentedOnText : '';
            newArr['userName'] = userName;
            newArr['userRole'] = userRole;
            newArr['profileURL'] = userProfile;
            newArr['status'] = 'draft reverted_back';

            arr.push(newArr);

            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

            var el = currentTextID.substring(3);
            var metaId = '_' + el;
            var assignTo = '';
            if( $( '#'+el+' .cf-assign-to-user' ).is(':checked') ) {
                assignTo = $( '#'+el+' .cf-assign-to-user' ).val()
            }
            var data = {
                'action': 'cf_add_comment',
                'currentPostID': CurrentPostID,
                'commentList': JSON.stringify(arr),
                'metaId': metaId,
                'assignTo': assignTo
            };

            $('#' + el + ' .shareCommentContainer').addClass('loading');
            let _this = this;
            $.post(ajaxurl, data, function (data) { // eslint-disable-line

                $('#' + el + ' .shareCommentContainer').removeClass('loading');
                $('.fresh-board').removeClass('fresh-board');

                data = $.parseJSON(data);
                if (undefined !== data.error) {
                    alert(data.error);
                    return false;
                }
                arr[arr.length - 1]['dtTime'] = data.dtTime;
                arr[arr.length - 1]['timestamp'] = data.timestamp;

                // Updating the assigned user info.
                if( null !== data.assignedTo ) {
                    var displayName = data.assignedTo.display_name ? data.assignedTo.display_name : 'Unknown User'
                    var assignedUserDetails = `
                        <div class="cf-board-assigned-to" data-user-id="${data.assignedTo.ID}" data-user-email="${data.assignedTo.user_email}">
                            <div class="assigned-user-details">
                                <div class="user-avatar">
                                    <img src="${data.assignedTo.avatar}" alt="${data.assignedTo.display_name}" />
                                </div>
                                <div class="user-info">
                                    <span class="badge">Assigned to</span>
                                    <p class="display-name">${displayName}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    if( $( `#${el} .cf-board-assigned-to` ).length ) {
                        $( `#${el} .cf-board-assigned-to` ).remove();
                    }
                    $( assignedUserDetails ).insertBefore( DOMPurify.sanitize( `#${el} .boardTop` ) ); // phpcs:ignore
                }

                // Update hasComment prop for dynamic button text.
                _this.hasComments = 1;

                // Activate 'Save Draft' or 'Publish' button
                wp.data.dispatch('core/editor').editPost({meta: {reflect_comments_changes: 1 } }); // eslint-disable-line

                // Set the state.
                _this.setState({comments: arr});

                // Flushing the text from the textarea
                $('#' + currentTextID).html('').focus();

                // Remove assign checkbox
                $( '.cf-assign-to' ).remove();
            });

        } else alert("Please write a comment to share!")

    }

    displayComments(text, i) {

        const {lastVal, onChanged, selectedText} = this.props;

        let username, userRole, postedTime, postedComment, profileURL, userID, status, cTimestamp, editedDraft;
        Object.keys(text).map(i => {
            if ('userName' === i) {
                username = text[i];
            } else if ('userRole' === i) {
                userRole = text[i];
            } else if ('dtTime' === i) {
                postedTime = text[i];
            } else if ('thread' === i) {
                postedComment = text[i];
            } else if ('profileURL' === i) {
                profileURL = text[i];
            } else if ('userData' === i) {
                userID = parseInt( text[i], 10 );
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
                userRole={userRole}
                dateTime={postedTime}
                profileURL={profileURL}
                userID={userID}
                status={status}
                lastVal={lastVal}
                onChanged={onChanged}
                selectedText={selectedText}
                timestamp={cTimestamp}
                editedDraft={editedDraft}
                showAvatars={localStorage.getItem("showAvatars")}
            >{
                postedComment = postedComment ? postedComment : text
            }
            </Comment>

        );

    }

    cancelComment() {

        // Reset Comments Float.
        $('#md-span-comments .cls-board-outer').removeClass('focus');
        $('#md-span-comments .cls-board-outer').removeAttr('style');
        $('[data-rich-text-format-boundary]').removeAttr('data-rich-text-format-boundary');

        const {datatext, onChanged, lastVal} = this.props;
        const name = 'multidots/comment';

        if ( 0 === $('#'+ datatext + ' .boardTop .commentContainer').length ) {
            onChanged(removeFormat(lastVal, name));
        }

        // Removing the active class form activity center on cancel.
        $( '.js-activity-centre .user-data-row' ).removeClass( 'active' );
    }

    componentDidMount() {
        if(this.props.freshBoard) {
            const datatext = this.props.datatext;
            setTimeout(function(){
                $( '#txt' + datatext ).focus();
            },500);
        }
    }

    render() {
        const {datatext} = this.props;
        const buttonText = 1 === this.hasComments && 1 !== this.props.freshBoard ? 'Reply' : 'Comment';
        const assignedTo = this.assignedTo
        return (
            <div className={`board ${undefined === this.hasComments && this.currentUserProfile && 'fresh-board'}`}>
                { undefined !== assignedTo && null !== assignedTo &&
                    <div className="cf-board-assigned-to" data-user-id={ assignedTo.ID } data-user-email={ assignedTo.user_email }>
                        <div className="assigned-user-details">
                            <div className="user-avatar">
                                <img src={ assignedTo.avatar } alt={ assignedTo.display_name } />
                            </div>
                            <div className="user-info">
                                <span className="badge">Assigned to</span>
                                <p className="display-name">{ assignedTo.display_name ? assignedTo.display_name : 'Unknwon User' }</p>
                            </div>
                        </div>
                    </div>
                }
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
                                <div className="avatar"><img src={this.currentUserProfile} alt="avatar"/></div>
                                <div className="commenter-name-time">
                                    <div className="commenter-name">{this.currentUserName}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                }
                <div className="shareCommentContainer">
                    <div className="cf-share-comment-wrapper js-cf-share-comment-wrapper">
                        <div tabIndex="0" contentEditable="true" placeholder="Comment or add others with @" className="cf-share-comment js-cf-share-comment" id={"txt" + datatext}></div>
                    </div>
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
