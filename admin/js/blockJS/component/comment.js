const {Fragment} = wp.element;
import React from 'react'
import PropTypes from 'prop-types';

export default class Comment extends React.Component {

    constructor(props) {

        super(props);

        this.edit = this.edit.bind(this);
        this.save = this.save.bind(this);
        this.remove = this.remove.bind(this);
        this.resolve = this.resolve.bind(this);
        this.cancelEdit = this.cancelEdit.bind(this);
        this.removeTag = this.removeTag.bind(this);
        this.state = {editing: false, showEditedDraft: false};

    }

    componentDidUpdate() {
        const editedCommentID = this.props.timestamp;
        const commenttedText = jQuery('#' + editedCommentID + ' textarea').val();
        jQuery('#' + editedCommentID + ' textarea').focus().val('').val(commenttedText);
    }

    edit() {
        this.setState({editing: true})
    }

    save(event) {

        var newText = this.newText.value;
        if ('' === newText) {
            alert("Please write a comment to share!");
            return false;
        }
        var elID = event.currentTarget.parentElement.parentElement.parentElement.parentElement.id;
        this.props.updateCommentFromBoard(newText, this.props.index, this.props.timestamp, this.props.dateTime, elID);

        this.setState({editing: false});
    }

    remove(event) {

        if (confirm('Are you sure you want to delete this comment ?')) {
            const elID = jQuery(event.currentTarget).closest('.cls-board-outer');
            this.props.removeCommentFromBoard(this.props.index, this.props.timestamp, elID[0].id);
        }
    }

    resolve(event) {

        var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
        elID = elID[0].id;
        const elIDRemove = elID;

        if (confirm('Are you sure you want to resolve this thread ?')) {
            const CurrentPostID = wp.data.select('core/editor').getCurrentPostId();
            elID = '_' + elID;

            var data = {
                'action': 'cf_resolve_thread',
                'currentPostID': CurrentPostID,
                'metaId': elID
            };
            jQuery.post(ajaxurl, data, function () {
                jQuery('#' + elIDRemove).remove();
                jQuery('#history-toggle').attr('data-count', jQuery('.cls-board-outer:visible').length);
            });

            // Remove Tag.
            this.removeTag(elIDRemove);
        } else {
            jQuery('#' + elIDRemove + ' [type="checkbox"]').prop('checked', false);
        }
    }

    removeTag(elIDRemove) {

        const clientId = jQuery('[datatext="' + elIDRemove + '"]').parents('[data-block]').attr('data-block');

        let blockAttributes = wp.data.select('core/block-editor').getBlockAttributes(clientId);
        //let multiHierarchy = false;
        if (null !== blockAttributes) {

            /*if( 'images' in blockAttributes ) {
                blockAttributes = blockAttributes['images'];
            }*/

            const findAttributes = ['content', 'citation', 'caption', 'value', 'values', 'fileName', 'text', 'downloadButtonText'];
            jQuery(findAttributes).each(function (i, attrb) {
                let content = blockAttributes[attrb];

                //jQuery(blockAttributes).each(function (i, content) {

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
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                content: finalContent
                                            }
                                        });
                                    } else if (attrb === 'citation') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                citation: finalContent
                                            }
                                        });
                                    } else if (attrb === 'value') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                value: finalContent
                                            }
                                        });
                                    } else if (attrb === 'caption') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                caption: finalContent
                                            }
                                        });
                                    } else if (attrb === 'values') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                values: finalContent
                                            }
                                        });
                                    } else if (attrb === 'fileName') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                fileName: finalContent
                                            }
                                        });
                                    } else if (attrb === 'text') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
                                            attributes: {
                                                text: finalContent
                                            }
                                        });
                                    } else if (attrb === 'downloadButtonText') {
                                        wp.data.dispatch('core/editor').updateBlock(clientId, {
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

                //});
            });
        }
    }

    cancelEdit() {
        this.setState({editing: false})
    }

    renderNormalMode() {

        // Display the textarea for new comments.
        jQuery('.cls-board-outer.focus .shareCommentContainer').show();

        const {index} = this.props;
        const commentStatus = this.props.status ? this.props.status : 'draft';

        var owner = '';
        try {
            owner = wp.data.select("core").getCurrentUser().id;
        } catch (e) {
            owner = localStorage.getItem("userID");
        }

        let str = this.state.showEditedDraft ? this.props.editedDraft : this.props.children;
        let readmoreStr = '';
        const maxLength = 100;
        if(maxLength < str.length) {
            readmoreStr = str;
            str = str.substring(0, maxLength) + '...';
        }

        return (
            <div className={"commentContainer " + commentStatus} id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="comment-actions">
                        {index === 0 &&
                            <div className="comment-resolve">
                                <input id={"resolve_cb_" + this.props.timestamp + '_' + index} type="checkbox" onClick={this.resolve.bind(this)} className="btn-comment" value="1" />
                                <label htmlFor={"resolve_cb_" + this.props.timestamp + '_' + index}>{'Mark as a Resolved'}</label>
                            </div>
                        }
                        {this.props.userID === owner &&
                            <div className="buttons-wrapper">
                                <i className="dashicons dashicons-edit" onClick={this.edit}></i>
                                <i className="dashicons dashicons-trash" onClick={index === 0 ? this.resolve.bind(this) : this.remove.bind(this)}></i>
                            </div>
                        }
                    </div>
                    <div className="comment-details">
                        {"1" === this.props.showAvatars &&
                        <div className="avtar">
                            <img src={this.props.profileURL} alt="avatar"/>
                        </div>
                        }
                        <div className="commenter-name-time">
                            <div className="commenter-name">{this.props.userName}</div>
                            <div className="comment-time">{this.props.dateTime}</div>
                        </div>
                    </div>
                </div>
                <div className="commentText">
                    <span className='readlessTxt readMoreSpan active'>{str} {'' !== readmoreStr && <a className='readmoreComment' href='javascript:void(0)'>show more</a>}</span>
                    <span className='readmoreTxt readMoreSpan'>{readmoreStr} {'' !== readmoreStr && <a className='readlessComment' href='javascript:void(0)'>show less</a>}</span>
                </div>
            </div>
        );
    }

    renderEditingMode() {

        // Hide the textarea for new comments.
        jQuery('.cls-board-outer.focus .shareCommentContainer').hide();

        return (
            <div className="commentContainer" id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="comment-details">
                        <div className="avtar"><img src={this.props.profileURL} alt="avatar"/></div>
                        <div className="commenter-name-time">
                            <div className="commenter-name">{this.props.userName}</div>
                            <div className="comment-time">{this.props.dateTime}</div>
                        </div>
                    </div>
                </div>
                <div className="commentText">
                  <textarea
                      ref={(input) => {
                          this.newText = input;
                      }}
                      onChange={this.handleChange}
                      defaultValue={this.state.showEditedDraft ? this.props.editedDraft : this.props.children}>
                  </textarea>
                </div>
                <button onClick={this.save.bind(this)} className="btn-comment save-btn">
                    {'Save'}
                </button>
                <button onClick={this.cancelEdit.bind(this)} className="btn-comment">
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
