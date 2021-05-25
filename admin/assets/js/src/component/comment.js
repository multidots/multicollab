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
        this.copy = this.copy.bind(this);
        this.cancelEdit = this.cancelEdit.bind(this);
        this.state = {editing: false, showEditedDraft: false, contentHtml: '<br/>' , editedTime:'',copySuccess:''};
        this.val = props.value;
    
    }
   
    componentDidUpdate() {
      
        if ($('mdspan[data-rich-text-format-boundary="true"]').length !== 0) {
            const editedCommentID = this.props.timestamp;
            const commenttedText = $('#' + editedCommentID + ' textarea').val();
            $('#' + editedCommentID + ' textarea').focus().val('').val(commenttedText);
        }
    }

    edit() {
       
        this.setState({editing: true});

        // Handling edited value.
        var editedValue        = this.state.showEditedDraft ? this.props.editedDraft: this.props.children;
        // Filtering anchor tag and return the url text only.
        editedValue = editedValue.replace( /<a href=\"https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)\" target=\"_blank\">https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)<\/a>/igm, function( match ) {
            return match.replace( /(<([^>]+)>)/ig, '');
        } )
      
        this.state.contentHtml = editedValue;
       
    }

    save(event) {
        var elID = event.currentTarget.parentElement.parentElement.parentElement.parentElement.id;
        if( $( `#${elID} .js-cf-edit-comment` ).text().trim().length !== 0 ) {
            var newText = this.state.contentHtml;
         
            if ( '' === newText ) {
                alert( "Please write a comment to share!" );
                return false;
            }
           if(true === this.state.editing){
            var date=   new Date();
            var editedTime = Math.floor(date.getTime()/1000);
            this.state.editedTime = editedTime;
           
            }

             // Adding anchor tag around the linkable text.
           newText = newText.replace( /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/ig, function( match ) {
                match = match.replace( /&nbsp/igm, '' );
                return `<a href="${match}" target="_blank">${match}</a>`;
            } );
            newText = newText.replace( /&nbsp;|(;)/igm, ' ' );
           
            this.props.updateCommentFromBoard( newText, this.props.index, this.props.timestamp, this.props.dateTime, elID,this.state.editedTime );
    
            this.setState( { editing: false } );
        } else {
            alert( 'Please write a comment to share' );
        }
    }

    remove(event) {

        if (confirm('Are you sure you want to delete this comment ?')) {
           
            const elID = $(event.currentTarget).closest('.cls-board-outer');
            this.props.removeCommentFromBoard(this.props.index, this.props.timestamp, elID[0].id);
           
        }
    }
    copy(event) {
       
        var elID         = $(event.currentTarget).closest('.cls-board-outer');
        elID             = elID[0].id;
        const elIDRemove = elID;
        var current_url = window.location.href+'&current_url='+elIDRemove;

        $('.copytext').text('');
        $('#'+elIDRemove).find('.copytext').text(current_url);
        
        $('#text_element').text('');
        $('#'+elIDRemove).find('#text_element').text(current_url);

        var $temp = $("<input>");
        var $url = current_url;
          $("body").append($temp);
          $temp.val($url).select();
          document.execCommand("copy");
          event.target.focus();
          this.setState({ copySuccess: 'Copied!' });
          clearInterval(this.resetState());
          $temp.remove();

        // Create an auxiliary hidden input
        var aux = document.createElement("input");
        // Get the text from the element passed into the input
        aux.setAttribute("value", document.getElementById('text_element').innerHTML);
        aux.select();

        // Execute the copy command
        document.execCommand("copy");

        // Remove the input from the body
        //document.body.removeChild(aux);

        $('#'+elIDRemove).find('.copyinput').val(current_url);
        document.querySelector('input.copyinput').select();
        document.execCommand('copy');
    }
    resetState(){
        setTimeout(() =>  this.setState({ copySuccess: '' }), 3000);
    }

    resolve(event) {
        var alertMessage = 'Are you sure you want to resolve this thread ?';
        if( $( event.target ).hasClass( 'js-resolve-comment' ) ) {
            alertMessage = 'Are you sure, you want to delete this thread? Deleting this thread will also resolve it!'
        }
        var elID         = $(event.currentTarget).closest('.cls-board-outer');
        elID             = elID[0].id;
        const elIDRemove = elID;

        if (confirm(alertMessage)) {
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
                //comment below code to keep other rich text format like <strong>,<em>
               // $('[data-rich-text-format-boundary]').removeAttr('data-rich-text-format-boundary');
               if($("#md-span-comments").is(':empty')){
                    $('body').removeClass("commentOn");
                } else{
                    $('body').addClass("commentOn");
                }
            });
           
            // Remove Tag.
            removeTag(elIDRemove); // eslint-disable-line
        } else {
            $('#' + elIDRemove + ' [type="checkbox"]').prop('checked', false);
        }
    }

    cancelEdit() {
        this.setState({editing: false})
    }
    renderNormalMode() {

        //code for copy URL
		const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const current_url = urlParams.get('current_url');
		  if(current_url){
            const boardID = current_url;
            const selectedText = boardID;
            const _this = $(this);
            $('#md-span-comments .cls-board-outer#'+current_url).addClass('focus');
            let topOfText = $('[datatext="' + selectedText + '"]').offset().top;
            $('#md-span-comments .cls-board-outer').css('opacity', '0.4');
            if($('.cls-board-outer').hasClass('focus')){
                $('#md-span-comments .cls-board-outer#'+current_url).css('opacity', '1');
                $('#md-span-comments .cls-board-outer#'+current_url).offset({top: topOfText});
            }
           
            /*$('#md-span-comments .cls-board-outer#'+current_url).css('opacity', '0.4');*/
            
            var scrollTopClass = '';
            if( 0 !== $('.interface-interface-skeleton__content').length ) {
                // Latest WP Version
                scrollTopClass = '.interface-interface-skeleton__content';

            } else if( 0 !== $('.block-editor-editor-skeleton__content').length ) {
                // Latest WP Version
                scrollTopClass = '.block-editor-editor-skeleton__content';

            } else if( 0 !== $('.edit-post-layout__content').length ) {
                // Old WP Versions
                scrollTopClass = '.edit-post-layout__content';

            } else {
                // Default
                scrollTopClass = 'body';
            }

            topOfText = topOfText + $(scrollTopClass).scrollTop();

            $(scrollTopClass).animate({
                scrollTop: topOfText - 150
            }, 1000);

            $('[data-rich-text-format-boundary="true"]').removeAttr('data-rich-text-format-boundary');
            $('[datatext="' + selectedText + '"]').attr('data-rich-text-format-boundary', true);
    }

        // Display the textarea for new comments.
        $('.cls-board-outer.focus .shareCommentContainer').show();

        const {index} = this.props;
        const commentStatus = this.props.status ? this.props.status : 'publish';
      

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
       // str = str.replace( /contenteditable=\"false\"/ig, 'data-edit="false"' ); // eslint-disable-line
        
        // Limiting User Role Character.
        var userRolePartial = this.props.userRole;
        if( 8 < userRolePartial.length ) {
            userRolePartial = userRolePartial.slice( 0, 8 ) + '...';
        }
       
        return (
            
            <div className={"commentContainer "  } id={this.props.timestamp}>
                <div className="comment-header">
                    <div className="comment-actions">
                        {index === 0 &&
                            <div className="comment-resolve">
                                <input id={"resolve_cb_" + this.props.timestamp + '_' + index} type="checkbox" onClick={this.resolve.bind(this)} className="resolve-cb" value="1" />
                                <label className="resolve-label" htmlFor={"resolve_cb_" + this.props.timestamp + '_' + index}>{'Mark as a Resolved'}</label>
                            </div>
                        }
                        {this.props.userID === owner && index === 0 &&
                            (
                                <div className="buttons-wrapper">
                                     <i className="dashicons  dashicons-admin-page" id="url" title="Copy Link" onClick={this.copy.bind(this)}></i>
                                    <i className="dashicons dashicons-edit js-edit-comment" title="Edit" onClick={this.edit}></i>
                                    <i className="dashicons dashicons-trash js-resolve-comment" title="Resolve" onClick={this.resolve.bind(this)}></i>
                                    { '' !== this.state.copySuccess &&
                                     this.state.copySuccess}
                                    <span className="copytext"></span>
                                    <input name="exampleClipboard" className="copyinput" value="" type="text"  style={{display:'none'}} readOnly/>
                                    <p id="text_element"></p>   
                                </div>
                            )
                        }
                        {this.props.userID === owner && index > 0 &&
                            (
                                <div className="buttons-wrapper">
                                    <i className="dashicons dashicons-edit js-edit-comment" title="Edit" onClick={this.edit}></i>
                                    <i className="dashicons dashicons-trash js-trash-comment" title="Delete" onClick={this.remove.bind(this)}></i>
                                </div>
                            )
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
                            
                            <div className="comment-time">{this.props.dateTime}
                           </div>
                        </div>
                    </div>
                </div>
                <div className="commentText">
               
                    <span className='readlessTxt readMoreSpan active' >{renderHTML(str)} {'' !== readmoreStr && <span className='readmoreComment'>show more</span>}</span>
                    <span className='readmoreTxt readMoreSpan'>{renderHTML(readmoreStr)} {'' !== readmoreStr && <span className='readlessComment'>show less</span>}</span>
                </div>
               
                { ''!== this.props.editedTime && undefined !== this.props.editedTime &&
                <time className="updated-time">(edited at {this.props.editedTime})</time>
             }
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
                    <div className="cf-share-comment-wrapper js-cf-share-comment-wrapper">
                        <ContentEditable
                            innerRef={ this.contentEditable }
                            html= {this.state.contentHtml}
                            disabled={ false }
                            onChange={  ( e ) =>  this.setState( { contentHtml: e.target.value } ) }
                            id={ `edit-${this.props.timestamp}` }
                            className="cf-share-comment js-cf-edit-comment"
                            placeholder="Edit your comments..."
                            
                        />
                    </div>
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
    showAvatars: PropTypes.string,
    userID: PropTypes.number,
    status: PropTypes.string,
    lastVal: PropTypes.object,
    onChanged: PropTypes.func,
    selectedText: PropTypes.string,
    timestamp: PropTypes.number,
    editedDraft: PropTypes.string,
    children: PropTypes.string,
    editedTime: PropTypes.string,
};
