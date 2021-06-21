import Board from './component/board';
import React from 'react'
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import nonTextBlock from "./nonTextblock";
import allowedBlocks from './component/allowedBlocks';

const {__} = wp.i18n;                                                   // eslint-disable-line
const {Fragment, Component} = wp.element;                               // eslint-disable-line
const {toggleFormat} = wp.richText;                                     // eslint-disable-line
const {RichTextToolbarButton,BlockControls} = wp.blockEditor;           // eslint-disable-line
const {registerFormatType, applyFormat, removeFormat} = wp.richText;    // eslint-disable-line
const {ToolbarGroup, ToolbarButton } = wp.components;                   // eslint-disable-line
const $ = jQuery;                                                       // eslint-disable-line



// Window Load functions.
$( window ).on('load', function () {



    let loadAttempts = 0;
    const loadComments = setInterval(function () {
        loadAttempts++;
        if ( 1 <= $('.block-editor-writing-flow').length ) {
            // Clearing interval if found.
            clearInterval( loadComments );

            // Fetching comments
            fetchComments();
        }
       
        if($("#md-span-comments").is(':empty')){
            $('body').removeClass("commentOn");
        }
        else{
            $('body').addClass("commentOn");
        }
       // Clearing interval if not found in 10 attemps.
        if ( loadAttempts >= 10 ) {
            clearInterval( loadComments );
        }
    }, 1000);

  
     $(document).on('click', '.components-notice__action', function () {

      
        if ('Restore the backup' === $(this).text()) {

            setTimeout(function () {
                // Sync popups with highlighted texts.
                $('.wp-block mdspan').each(function () {
                    var selectedText = $(this).attr('datatext');
                    if ($('#' + selectedText).length === 0) {
                        createBoard(selectedText, 'value', 'onChange');
                    }
                });
            }, 500);

        }

    });

});



function fetchComments() {
 
    var parentNode = document.createElement('div');
    parentNode.setAttribute("id", 'md-comments-suggestions-parent');

    var referenceNode = document.querySelector('.block-editor-writing-flow');
    if (null !== referenceNode) {
        referenceNode.appendChild(parentNode);

        var commentNode = document.createElement('div');
        commentNode.setAttribute("id", 'md-span-comments');
        commentNode.setAttribute("class", 'comments-loader');
        var parentNodeRef = document.getElementById('md-comments-suggestions-parent');
        parentNodeRef.appendChild(commentNode);

        let selectedText;
        var allThreads = [];
        var selectedNontextblock =[];
        var selectedTextBlock = [];
        var selectedTexts = [];

        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const current_url = urlParams.get('current_url');

        // If no comment tag exist, remove the loader and temp style tag immediately.
        const span_count = $('.wp-block mdspan').length;
        //Non text block datatext count
       const nonText_count = $('.commentIcon').attr('datatext');
        if (0 === span_count && 'undefined' === nonText_count ) {
            $( '.commentOn .block-editor-writing-flow' ).css( { width: '100% !important' } )
            $('body').removeClass("commentOn");
            $('#md-span-comments').removeClass('comments-loader');
            $('#loader_style').remove();
            if(current_url){
                alert('Your Comment is Deleted or Resolved! Please check with different URL');
                urlParams.delete('current_url');
                window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
            }
        } else {
           
            $('body').addClass("commentOn");
            //nonTextBlock datatext attribute
            $('.commentIcon').each(function(){
               let selectedNontext = $(this).attr('datatext');
               selectedNontextblock.push(selectedNontext)
              });
              //textblock datatext attribute
            $('.wp-block mdspan').each(function () {
                let selectedDataText = $(this).attr('datatext');
                selectedTextBlock.push(selectedDataText);
       
            });
       
            selectedTexts = selectedNontextblock.concat(selectedTextBlock);
            selectedTexts = selectedTexts.filter(function(e){return e}); 
           
              
              selectedTexts.forEach(selectedText => {
               
                if ($('#' + selectedText).length === 0 || 'undefined' !== selectedText)  {
                 
                    var newNode = document.createElement('div');
                    newNode.setAttribute("id", selectedText);
                    newNode.setAttribute("class", "cls-board-outer is_active");
    
                    var referenceNode = document.getElementById('md-span-comments');
                    referenceNode.appendChild(newNode);
                  
                    ReactDOM.render(
                        <Board datatext={selectedText} onLoadFetch={1}/>,
                        document.getElementById(selectedText)
                    )
                }
                allThreads.push(selectedText);
              });
  
            const copyDatatext = allThreads.includes(current_url);
            if(current_url && false === copyDatatext){
                alert('Your Comment is Deleted or Resolved! Please check with different URL');
                urlParams.delete('current_url');
                window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
            }else{
                $( '.js-activity-centre .user-data-row' ).removeClass( 'active' );
                $( `#cf-${current_url}` ).addClass( 'active' );
              }
          
            let loadAttempts = 0;
            const loadComments = setInterval(function () {
                var openBoards = $('.cls-board-outer:visible').length;
                if(0 === openBoards){
                    $('body').removeClass("commentOn");
                    $('#md-span-comments').removeClass('comments-loader');
                }
                loadAttempts++;
               if (1 <= $('#md-span-comments .commentContainer').length) {
                    clearInterval(loadComments);
                    $('#loader_style').remove();
                    $('#md-span-comments').removeClass('comments-loader');
                    $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
                }
                if (loadAttempts >= 10) {
                    clearInterval(loadComments);
                    $('#loader_style').remove();
                    $('#md-span-comments').removeClass('comments-loader');
                }
            }, 1000);
        }

       
    }
}
// Float comment for  Non Text block Board
$( document ).on( 'click', '.commentIcon', function() {
    var block   = wp.data.select( 'core/block-editor' ).getSelectedBlock();
    var selectedText ;
    //var hasSelectedInnerBlock = wp.data.select('core/block-editor').hasSelectedInnerBlock(block.clientId);
  
 
    if(allowedBlocks.includes(block.name) ){
        selectedText = $('#block-'+block.clientId).attr('datatext');
     }
    else{
      return;
    }

      if('undefined' !== selectedText){
        
        $('.cls-board-outer').removeClass('focus');
        $('.cls-board-outer').removeClass('is-open');
        $('#' + selectedText + '.cls-board-outer').addClass('focus');
        $('#md-span-comments .cls-board-outer').css('opacity', '0.4');
        $('#md-span-comments .cls-board-outer.focus').css('opacity', '1');
        $('#md-span-comments .cls-board-outer').css('top', 0);
        var findMdSpan = '.cls-board-outer';
        $( findMdSpan ).each( function() {
             var boardDatatext = $( this ).attr( 'id' );
           
             if( boardDatatext.includes(selectedText) ) {
                $('#' + selectedText).offset({top:$('[datatext="' + selectedText + '"]').offset().top});
                //Adding class to prevent multiple click on same selected Text
                $('#' + selectedText +'.cls-board-outer').addClass('is-open');
                $( '.js-activity-centre .user-data-row' ).removeClass( 'active' );
                $( `#cf-${selectedText}` ).addClass( 'active' );
               
             }
           });
    }

})

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
        datatext: 'datatext'
    },
    edit: (class toggleComments extends Component {
        constructor(props) {
            super(props);

            this.onToggle = this.onToggle.bind(this);
            this.getSelectedText = this.getSelectedText.bind(this);
            this.floatComments = this.floatComments.bind(this);
           
            // Typecheck.
            toggleComments.propTypes = {
                value: PropTypes.object,
                activeAttributes: PropTypes.object,
                onChange: PropTypes.func,
                isActive: PropTypes.bool,
            };
        }

        onToggle() {
            const {value, onChange} = this.props;
            let {text, start, end} = value;
            const commentedOnText = text.substring(start, end);
            
            
            // If text is not selected, show notice.
            if (start === end) {
                alert('Please select text to comment on.');
                return;
            }
            //If comment box already open, show notice. 
           
            var blockContent = wp.data.select( 'core/block-editor' ).getSelectedBlock().attributes.content;
            var validId =  blockContent.search($('.is-open').attr('id'));
           
            if($('.cls-board-outer').hasClass('is-open') && 0 <= validId){
               alert('You can not give multiple comment on same Text.');
                return;
            }
           var html =this.getSelectionHtml();
           if(null !== html.match(/mdspan/g)){
            alert('You have already given comment on one of the word!');
            return;
        }
            var currentTime = Date.now();
            currentTime = 'el' + currentTime;
            var newNode = document.createElement('div');
            newNode.setAttribute("id", currentTime);
            newNode.setAttribute("class", 'cls-board-outer');

            var referenceNode = document.getElementById('md-span-comments');

            referenceNode.appendChild(newNode);
            $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
             //Activate Show All comment button in setting panel
            $('.components-form-toggle').addClass('is-checked');
            $('#inspector-toggle-control-0__help').html('All comments will show on the content area.');
            onChange(toggleFormat(value, {type: name}),
                ReactDOM.render(
                    <Board datatext={currentTime} onChanged={onChange} lastVal={value} freshBoard={1} commentedOnText={commentedOnText}/>,
                    document.getElementById(currentTime)
                )
            );

            onChange(applyFormat(value, {type: name, attributes: {datatext: currentTime}}));

            // Toogle hide-comments class if the comments is hidden when try to add new one.
            if( $( 'body' ).hasClass( 'hide-comments' ) ) {
                $( 'body' ).removeClass( 'hide-comments' )
            }

        }
        getSelectionHtml() {
            var html = "";
            if (typeof window.getSelection != "undefined") {
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
        getSelectedText() {
            const { onChange, value, activeAttributes } = this.props;
            //if copy URL exist remove from existing URL
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const current_url = urlParams.get('current_url');
            if(current_url){
                urlParams.delete('current_url');
                window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
            }
            // Stripping out unwanted <mdspan> tags from the content.
            var findMdSpan = 'mdspan';
            $( findMdSpan ).each( function() {
                var datatext = $( this ).attr( 'datatext' );
               if( undefined === datatext ) {
                    $( this ).replaceWith( $( this ).text());
                }
            } );

            // Prevent on locked mode + fix for unnecessary calls on hover.
            if ($('.cls-board-outer').hasClass('locked') ) {
                return;
            }

            // Ignore unnecessary event calls on hover.
            if ($('#' + activeAttributes.datatext + '.cls-board-outer').hasClass('focus')) {
                return;
            }
            
            // Reset Comments Float only if the selected text has no comments on it.
            if (undefined === activeAttributes.datatext) {
                //check try to give comment on word ,who has a comment  
             
                $('#md-span-comments .cls-board-outer').css('opacity', '1');
                $('#md-span-comments .cls-board-outer').removeClass('focus');
                $('#md-span-comments .cls-board-outer').removeClass('is-open');
                $('#md-span-comments .cls-board-outer').removeAttr('style');

                //ne_pending remove the attr true
                $('mdspan').removeAttr('data-rich-text-format-boundary');
            }

            const referenceNode = document.getElementById('md-span-comments');

            // Remove tags if selected tag ID exist in 'remove-comment' attribute of body.
            let removedComments = $('body').attr('remove-comment');
            if (undefined !== activeAttributes.datatext &&
                (undefined !== removedComments && removedComments.indexOf(activeAttributes.datatext) !== -1)
            ) {
                onChange(removeFormat(value, name));
            }

            if (undefined !== this.props.value.start && null !== referenceNode) {
                let selectedText;
                $('.cls-board-outer').removeClass('has_text');

                // Sync popups with highlighted texts.
                $('.wp-block mdspan').each(function () {

                    selectedText = $(this).attr('datatext');

                    // Bring back CTRL-Z'ed Text's popup.
                    if (undefined !== selectedText && $('#' + selectedText).length === 0) {

                        let removedComments = $('body').attr('remove-comment');
                        if (undefined === removedComments ||
                            (undefined !== removedComments && removedComments.indexOf(selectedText) === -1)
                        ) {
                            createBoard(selectedText, value, onChange);
                        } else {
                            $('[datatext="' + selectedText + '"]').css('background', 'transparent');
                        }
                    }
                    $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
                    $('#' + selectedText).addClass('has_text').show();
                });

                selectedText = activeAttributes.datatext;
               

                // Delete the popup and its highlight if user
                // leaves the new popup without adding comment.
              
                if (1 === $('.board.fresh-board').length && 0 === $('.board.fresh-board .loading').length) {
                    const latestBoard = $('.board.fresh-board').parents('.cls-board-outer').attr('id');
                    const span_count = $('.wp-block mdspan').length;
                  
                    if (selectedText !== latestBoard) {
                      
                        removeTag(latestBoard); // eslint-disable-line
                        $('#' + latestBoard).remove();
                        $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
                       if($("#md-span-comments").is(':empty'))
                        {
                            $('body').removeClass("commentOn");
                           
                        }else{
                            $('body').addClass("commentOn");
                        }
                    }
                 
                    
                }

                // Just hide these popups and only display on CTRLz
                $('#md-span-comments .cls-board-outer:not(.has_text):not([data-sid])').each(function () {
                  //comment below code because its hide non text block on CTRLz
                   //$(this).hide();
                    $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
                });

                
                

                // Adding lastVal and onChanged props to make it deletable,
                // these props were not added on load.
                // It also helps to 'correct' the lastVal of CTRL-Z'ed Text's popup.
                if ($('#' + selectedText).length !== 0) {
                    //if body hase hide comment class
                    if( $( 'body' ).hasClass( 'hide-comments' ) ) {
                      
                        $('body').removeClass("commentOn");
                    }else{
                        $('body').addClass("commentOn");
                        
                    }
                    ReactDOM.render(
                        <Board datatext={selectedText} lastVal={value} onChanged={onChange}/>,
                        document.getElementById(selectedText)
                    )
                }
             
                // Float comments column.
                this.floatComments(selectedText);
            }
            // $( '.js-cancel-comment' ).trigger( 'click' ); // Closing all opened edit comment box.
            $( '.js-activity-centre .user-data-row' ).removeClass( 'active' );


        }

        floatComments(selectedText) {
           
          
            if (document.querySelectorAll(`[data-rich-text-format-boundary='${true}']`).length!== 0) {
             

                // Removing dark highlights from other texts,
                // only if current active text has an attribute,
                // and no 'focus' class active on mdspan tag.
                // This condition prevents thread popup flickering
                // when navigating through the activity center.

                // Adding focus on selected text's popup.
                $('.cls-board-outer').removeClass('focus');
                $('.cls-board-outer').removeClass('is-open');
                $('#' + selectedText + '.cls-board-outer').addClass('focus');
                $('#md-span-comments .cls-board-outer').css('opacity', '0.4');
                $('#md-span-comments .cls-board-outer.focus').css('opacity', '1');
                $('#md-span-comments .cls-board-outer').css('top', 0);
                var findMdSpan = '.mdspan-comment';
                $( findMdSpan ).each( function() {
                 var datatext = $( this ).attr( 'datatext' );
                if( datatext === selectedText ) {
                    $('#' + selectedText).offset({top: $('[datatext="' + selectedText + '"]').offset().top});
                    //Adding class to prevent multiple click on same selected Text
                    $('#' + selectedText +'.cls-board-outer').addClass('is-open');
                }
               });
            }
        }

        render() {
            const {isActive} = this.props;
            var block   = wp.data.select( 'core/block-editor' ).getSelectedBlock();
           // var parentBlock = wp.data. select('core/block-editor').getBlockParents(block.clientId);
          
            return (
                <Fragment>
                      {(! allowedBlocks.includes( block.name))  &&
                    <div>
                        
                    <RichTextToolbarButton
                        title={__('Comment')}
                        isActive={isActive}
                        icon="admin-comments"
                        onClick={this.onToggle}
                        shortcutType="primary"
                        shortcutCharacter="m"
                        className={`toolbar-button-with-text toolbar-button__${name}`}
                        
                    />
                 
                    <BlockControls>
			            <ToolbarGroup>
				            <ToolbarButton
					         icon="admin-comments"
                             isActive={isActive}
					        label={__('Comment')}
					        onClick={this.onToggle}
                            className={`toolbar-button-with-text toolbar-button__${name}`}
				           />
			            </ToolbarGroup>
		            </BlockControls>
                    </div>
                }
                    {
                        <Fragment>
                            {this.getSelectedText()}
                        </Fragment>
                    }

                </Fragment>
            );
        }
    }),
};
registerFormatType(name, mdComment);