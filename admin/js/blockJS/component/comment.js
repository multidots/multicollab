const {Fragment} = wp.element; // eslint-disable-line
import React from 'react';
import PropTypes from 'prop-types';
import renderHTML from 'react-render-html';
import ContentEditable from 'react-contenteditable';

const $ = jQuery; // eslint-disable-line
export default class Comment extends React.Component {

    constructor(props) {

        super(props);
        this.contentEditable = React.createRef();
        this.edit = this.edit.bind(this);
        this.save = this.save.bind(this);
        this.remove = this.remove.bind(this);
        this.resolve = this.resolve.bind(this);
        this.cancelEdit = this.cancelEdit.bind(this);
        this.state = {editing: false, showEditedDraft: false, contentHtml: ''};

    }

    componentDidUpdate() {
        const editedCommentID = this.props.timestamp;
        const commenttedText = $('#' + editedCommentID + ' textarea').val();
        $('#' + editedCommentID + ' textarea').focus().val('').val(commenttedText);
    }

    edit() {
        this.setState({editing: true})
        // Handling edited value.
        var editedValue     = this.state.showEditedDraft ? this.props.editedDraft: this.props.children;
        var editedContainer = '#edit-' + this.props.timestamp;
        setTimeout( function() {
            $( editedContainer ).html( editedValue ); // phpcs:ignore
        }, 500 )
    }

    save(event) {
        var newText = this.state.contentHtml;
        if ( '' === newText ) {
            alert( "Please write a comment to share!" );
            return false;
        }
        var elID = event.currentTarget.parentElement.parentElement.parentElement.parentElement.id;
        this.props.updateCommentFromBoard( newText, this.props.index, this.props.timestamp, this.props.dateTime, elID );

        this.setState( { editing: false } );
    }

    remove(event) {

        if (confirm('Are you sure you want to delete this comment ?')) {
            const elID = $(event.currentTarget).closest('.cls-board-outer');
            this.props.removeCommentFromBoard(this.props.index, this.props.timestamp, elID[0].id);
        }
    }

    resolve(event) {

        var elID = $(event.currentTarget).closest('.cls-board-outer');
        elID = elID[0].id;
        const elIDRemove = elID;

        if (confirm('Are you sure you want to resolve this thread ?')) {
            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line
            elID = '_' + elID;

            var data = {
                'action': 'cf_resolve_thread',
                'currentPostID': CurrentPostID,
                'metaId': elID
            };
            $.post(ajaxurl, data, function () { // eslint-disable-line
                $('#' + elIDRemove).remove();
                $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);

                // Reset Comments Float.
                $('#md-span-comments .cls-board-outer').removeClass('focus');
                $('#md-span-comments .cls-board-outer').removeAttr('style');
                $('[data-rich-text-format-boundary]').removeAttr('data-rich-text-format-boundary');
            });

            // Remove Tag.
            removeTag(elIDRemove);
        } else {
            $('#' + elIDRemove + ' [type="checkbox"]').prop('checked', false);
        }
    }

    cancelEdit() {
        this.setState({editing: false})
    }

    renderNormalMode() {

        // Display the textarea for new comments.
        $('.cls-board-outer.focus .shareCommentContainer').show();

        const {index} = this.props;
        const commentStatus = this.props.status ? this.props.status : 'draft';

        var owner = '';
        try {
            owner = wp.data.select("core").getCurrentUser().id; // eslint-disable-line
        } catch (e) {
            owner = localStorage.getItem("userID");
        }

        let str         = this.state.showEditedDraft ? this.props.editedDraft: this.props.children;
        let readmoreStr = '';
        const maxLength = 300;
        if(maxLength < str.length) {
            readmoreStr = str;
            str = str.substring(0, maxLength) + '...';
        }

        // Removing contenteditable attr from the link.
        str = str.replace( /contenteditable=\"false\"/ig, 'data-edit="false"' ); // eslint-disable-line

        // Limiting User Role Character.
        var userRolePartial = this.props.userRole;
        if( 8 < userRolePartial.length ) {
            userRolePartial = userRolePartial.slice( 0, 8 ) + '...';
        }

        return (
            <div className={"commentContainer " + commentStatus} id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="comment-actions">
                        {index === 0 &&
                            <div className="comment-resolve">
                                <input id={"resolve_cb_" + this.props.timestamp + '_' + index} type="checkbox" onClick={this.resolve.bind(this)} className="resolve-cb" value="1" />
                                <label className="resolve-label" htmlFor={"resolve_cb_" + this.props.timestamp + '_' + index}>{'Mark as a Resolved'}</label>
                            </div>
                        }
                        {this.props.userID === owner &&
                            <div className="buttons-wrapper">
                                <i className="dashicons dashicons-edit js-edit-comment" onClick={this.edit}></i>
                                <i className="dashicons dashicons-trash js-trash-comment" onClick={index === 0 ? this.resolve.bind(this) : this.remove.bind(this)}></i>
                            </div>
                        }
                    </div>
                    <div className="comment-details">
                        {"1" === this.props.showAvatars &&
                        <div className="avatar">
                            <img src={this.props.profileURL} alt="avatar"/>
                        </div>
                        }
                        <div className="commenter-name-time">
                            <div className="commenter-name" title={ `${this.props.userName} ( ${this.props.userRole} )` }>
                                {this.props.userName} <small>({ userRolePartial })</small>
                            </div>
                            <div className="comment-time">{this.props.dateTime}</div>
                        </div>
                    </div>
                </div>
                <div className="commentText">
                    <span className='readlessTxt readMoreSpan active'>{renderHTML(str)} {'' !== readmoreStr && <span className='readmoreComment'>show more</span>}</span>
                    <span className='readmoreTxt readMoreSpan'>{renderHTML(readmoreStr)} {'' !== readmoreStr && <span className='readlessComment'>show less</span>}</span>
                </div>
            </div>
        );
    }

    renderEditingMode() {

        // Hide the textarea for new comments.
        $('.cls-board-outer.focus .shareCommentContainer').hide();

        // Limiting User Role Character.
        var userRolePartial = this.props.userRole;
        if( 8 < userRolePartial.length ) {
            userRolePartial = userRolePartial.slice( 0, 8 ) + '...';
        }

        return (
            <div className="commentContainer" id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="comment-details">
                        <div className="avatar"><img src={this.props.profileURL} alt="avatar"/></div>
                        <div className="commenter-name-time">
                            <div className="commenter-name" title={ `${this.props.userName} ( ${this.props.userRole} )` }>
                                {this.props.userName} <small>({ userRolePartial })</small>
                            </div>
                            <div className="comment-time">{this.props.dateTime}</div>
                        </div>
                    </div>
                </div>
                <div className="commentText">
                    <ContentEditable
                        innerRef={ this.contentEditable }
                        html={ this.state.contentHtml }
                        disabled={ false }
                        onChange={ ( e ) => this.setState( { contentHtml: e.target.value } ) }
                        id={ `edit-${this.props.timestamp}` }
                        className="cf-share-comment js-cf-edit-comment"
                    />
                </div>
                <button onClick={this.save.bind(this)} className="btn-comment save-btn">
                    {'Save'}
                </button>
                <button onClick={this.cancelEdit.bind(this)} className="btn-comment js-cancel-comment">
                    {'Cancel'}
                </button>
            </div>
        );
    }

    render() {

        if (this.state.editing) {
            return this.renderEditingMode();
        } else {
            return this.renderNormalMode();
        }
    }
}

// Typecheck.
Comment.propTypes = {
    index: PropTypes.number,
    removeCommentFromBoard: PropTypes.func,
    updateCommentFromBoard: PropTypes.func,
    userName: PropTypes.string,
    userRole: PropTypes.string,
    dateTime: PropTypes.string,
    profileURL: PropTypes.string,
    userID: PropTypes.number,
    status: PropTypes.string,
    lastVal: PropTypes.object,
    onChanged: PropTypes.func,
    selectedText: PropTypes.string,
    timestamp: PropTypes.number,
    editedDraft: PropTypes.string,
    children: PropTypes.string,
};
