import Comment from "./comment";

const {removeFormat} = wp.richText;

export default class Board extends React.Component {

    constructor(props) {

        super(props);
        this.displayComments = this.displayComments.bind(this);
        this.updateComment = this.updateComment.bind(this);
        this.removeComment = this.removeComment.bind(this);
        this.addNewComment = this.addNewComment.bind(this);
        this.removeSuggestion = this.removeSuggestion.bind(this);
        this.acceptSuggestion = this.acceptSuggestion.bind(this);
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
            const allPosts = wp.apiFetch({path: 'career-data-by-select1/my-route1/?currentPostID=' + currentPostID + '&elID=' + metaselectedText}).then(fps => {

                const {userDetails, value, onChange, resolved} = fps;
                this.props.lastVal = value;
                this.props.resolved = resolved;
                if ('true' === resolved) {
                    let elIDRemove = selectedText
                    jQuery('body').attr('remove-comment', elIDRemove);
                    jQuery('body').append('<style>body[remove-comment*="' + elIDRemove + '"] [datatext="' + elIDRemove + '"] {background-color:transparent !important;}</style>');
                    jQuery('[datatext="' + elIDRemove + '"]').addClass('removed');
                    jQuery('#' + elIDRemove).remove();

                    return false;
                }

                jQuery.each(userDetails, function (key, val) {
                    //	postSelections.push(val);
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
        }

        this.state = {comments: []};
    }

    removeComment(idx, cTimestamp, elID) {
        var arr = this.state.comments;

        arr.splice(idx, 1);
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        const {value, onChange} = this.props;
        elID = '_' + elID;
        var data = {
            'action': 'my_action_delete',
            'currentPostID': CurrentPostID,
            'timestamp': cTimestamp,
            metaId: elID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {

        });
        this.setState({comments: arr});
    }

    updateComment(newText, idx, cTimestamp, dateTime, metaID) {
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
        const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
        metaID = '_' + metaID;
        var data = {
            'action': 'my_action_edit',
            'currentPostID': CurrentPostID,
            'editedComment': JSON.stringify(newArr),
            'metaId': metaID
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        let _this = this;

        jQuery.post(ajaxurl, data, function () { });
        this.setState({comments: arr})
    }

    addNewComment(event) {
        event.preventDefault();

        const {lastVal, onChanged, datatext} = this.props;

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

            newArr['commentedOnText'] = this.commentedOnText;

            newArr['userName'] = userName;
            newArr['profileURL'] = userProfile;
            newArr['value'] = lastVal;
            newArr['onChange'] = onChanged;
            newArr['status'] = 'draft reverted_back';

            arr.push(newArr);

            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();

            var el = currentTextID.substring(3);
            var metaId = '_' + el;
            var data = {
                'action': 'my_action',
                'currentPostID': CurrentPostID,
                'commentList': JSON.stringify(arr),
                'metaId': metaId
            };

            jQuery('#' + el + ' .shareCommentContainer').addClass('loading');
            let _this = this;
            jQuery.post(ajaxurl, data, function (data) {

                jQuery('#' + el + ' .shareCommentContainer').removeClass('loading');

                data = jQuery.parseJSON(data);
                arr[arr.length - 1]['dtTime'] = data.dtTime;
                arr[arr.length - 1]['timestamp'] = data.timestamp;

                // Update hasComment prop for dynamic button text.
                _this.hasComments = 1;

                // Set the state.
                _this.setState({comments: arr});

                // Flushing the text from the textarea
                jQuery('#' + currentTextID).val('');
                jQuery('#' + datatext + ' .no-comments').remove();

            });

        } else alert("Please write a comment to share!")

    }

    removeSuggestion(event) {
        if (confirm('Are you sure you want to delete this thread ?')) {
            var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
            elID = elID[0].id;
            var elIDRemove = elID;
            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            alert(elID);
            const {lastVal, onChanged} = this.props;

            onChanged(removeFormat(lastVal, name2));
        }
    }

    acceptSuggestion(event) {
        if (confirm('Are you sure you want to delete this thread ?')) {
            var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
            elID = elID[0].id;
            var elIDRemove = elID;
            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            const {value, onChange} = this.props;
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
            const {lastVal, onChanged} = this.props;
            onChanged(removeFormat(lastVal, name2));
        }
    }

    displayComments(text, i) {

        const {isActive, inputValue, myval2, value} = this.props; /*onChange*/
        const {lastVal, onChanged, selectedText, suserProfile, suserName} = this.props;

        let username, postedTime, postedComment, profileURL, userID, status, cTimestamp, editedDraft; /*value, onChange*/
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
                /*lastVal={value}
                onChanged={onChange}*/
                selectedText={selectedText}
                timestamp={cTimestamp}
                editedDraft={editedDraft}
            >{
                postedComment = postedComment ? postedComment : text
            }
            </Comment>

        );

    }

    render() {
        const {isActive, inputValue, onChange, value, myval2, selectedText, datatext} = this.props;
        const buttonText = 1 === this.hasComments && 1 !== this.props.freshBoard ? 'Reply' : 'Comment';

        return (
            <div className="board">
                <div className="boardTop">
                    {0 === this.hasComments &&
                        <div className="no-comments"><i>The are no comments!</i></div>
                    }
                    {
                        this.state.comments && this.state.comments.map((item, index) => {
                            return this.displayComments(item, index);
                        })
                    }
                </div>

                <div className="shareCommentContainer">
                    <textarea id={"txt" + datatext} placeholder="Write a comment.."></textarea>
                    <button onClick={this.addNewComment} className="btn btn-success">{buttonText}</button>
                </div>
            </div>


        );
    }
}
