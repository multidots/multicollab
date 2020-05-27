const {Fragment} = wp.element;
const {removeFormat} = wp.richText;

export default class Comment extends React.Component {

    constructor(props) {

        super(props);

        this.edit = this.edit.bind(this);
        this.save = this.save.bind(this);
        this.remove = this.remove.bind(this);
        this.resolve = this.resolve.bind(this);
        this.cancelEdit = this.cancelEdit.bind(this);
        this.state = {editing: false, showEditedDraft: false};
    }

    edit() {
        this.setState({editing: true})
    }

    save(event) {

        var newText = this.newText.value;
        var metaId = this.newText.id.substring(3);
        var elID = event.currentTarget.parentElement.parentElement.parentElement.parentElement.id;
        this.props.updateCommentFromBoard(newText, this.props.index, this.props.timestamp, elID);

        this.setState({editing: false})
    }

    remove(event) {

        if (confirm('Are you sure you want to delete this comment ?')) {
            var elID = jQuery(event.currentTarget).closest('.cls-board-outer');
            //	var elID = event.currentTarget.parentElement.parentElement.parentElement.id;

            this.props.removeCommentFromBoard(this.props.index, this.props.timestamp, elID[0].id);
        }
    }

    resolve(event) {
        //const myvalue = this.props.myval2;
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
                jQuery('div#' + elIDRemove).remove();
            });

            let name = 'multidots/comment';

            const {lastVal, onChanged} = this.props;
            window.lastVal = lastVal;
            jQuery('body').attr('remove-comment', elIDRemove);
            jQuery('body').append('<style>body[remove-comment*="' + elIDRemove + '"] [datatext="' + elIDRemove + '"] {background-color:transparent !important;}</style>');
            //let onChangedDynamic = null === onChanged || undefined === onChanged ? window.onChange : onChanged;

            if (null === onChanged || undefined === onChanged) {
                jQuery('[datatext="' + elIDRemove + '"]').addClass('removed');
            } else {
                onChanged(removeFormat(lastVal, name));
            }
        }
    }

    cancelEdit() {
        this.setState({editing: false})
    }

    renderNormalMode() {
        const {lastVal, onChanged, selectedText, index} = this.props;
        this.props.status = this.props.status ? this.props.status : 'draft';

        var owner = wp.data.select("core").getCurrentUser().id;
        return (
            <div className={"commentContainer " + this.props.status} id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="avtar"><img src={this.props.profileURL} alt="avatar"/></div>
                    <div className="commenter-name-time">
                        <div className="commenter-name">{this.props.userName}</div>
                        <div className="comment-time">{this.props.dateTime}</div>
                    </div>
                    {index === 0 &&
                    <button onClick={this.resolve.bind(this)} className="btn-comment">
                        {'Resolve'}
                    </button>
                    }
                    <div className="buttons-holder">

                        <div className="buttons-opner">

                            <Fragment>
                                {this.props.userID === owner &&
                                <svg aria-hidden="true" role="img" focusable="false" className="dashicon dashicons-ellipsis"
                                     xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                    <path
                                        d="M5 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm12-2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-7 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path>
                                </svg>
                                }
                            </Fragment>
                        </div>
                        <Fragment>
                            {this.props.userID === owner &&
                            <div className="buttons-wrapper">
                                <button onClick={this.edit} className="btn btn-comment">
                                    {'Edit'}
                                </button>
                                <button onClick={index === 0 ? this.resolve.bind(this) : this.remove.bind(this)} className="btn btn-comment">
                                    {'Delete'}
                                </button>
                                {/*<button onClick={this.resolve.bind(this)} className="btn btn-comment">
                  {'Resolve'}
                </button>*/}
                            </div>
                            }
                        </Fragment>
                    </div>
                </div>
                <div className="commentText">{this.state.showEditedDraft ? this.props.editedDraft : this.props.children}</div>
            </div>
        );
    }

    renderEditingMode() {
        return (
            <div className="commentContainer">
                <div className="commentText">
          <textarea
              ref={(input) => {
                  this.newText = input;
              }}
              onChange={this.handleChange}
              defaultValue={this.state.showEditedDraft ? this.props.editedDraft : this.props.children}>
          </textarea>
                </div>
                <button onClick={this.save.bind(this)} className="btn-comment">
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
