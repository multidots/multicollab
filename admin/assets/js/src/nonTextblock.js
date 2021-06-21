import Board from './component/board';
import React from 'react'
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import allowedBlocks from './component/allowedBlocks';

import assign from "lodash.assign";
const {__} = wp.i18n;                                                   // eslint-disable-line
const {Fragment } = wp.element;       									// eslint-disable-line
const {BlockControls} = wp.blockEditor;                                 // eslint-disable-line
const {ToolbarGroup, ToolbarButton } = wp.components;                   // eslint-disable-line
const { addFilter } = wp.hooks;                                         // eslint-disable-line
const { createHigherOrderComponent,compose } = wp.compose;                      // eslint-disable-line
const { getBlockType } = wp.data.select('core/blocks') ; 			    // eslint-disable-line
const $ = jQuery;                                                       // eslint-disable-line


function onToggleNonTextBlock() {

 
    var currentTime = Date.now();
    currentTime = 'el' + currentTime;
	var block   = wp.data.select( 'core/block-editor' ).getSelectedBlock();
	var blockName = block.name;
	var blockType = getBlockType(blockName);
	var commentedOnText= getCommentedText(blockType.name,block);
	var blockIndex = block.clientId;
	var blockId = "block-"+blockIndex;
	var existingDatatext= wp.data.select( 'core/block-editor' ).getBlockAttributes( blockIndex).datatext;

	//Restrict multiple comment on same block
	if(existingDatatext){
		alert('You can not give multiple comment on same Block.');
		return;
	   }
	
	$("#" + blockId).attr('datatext',currentTime);
	var newNode = document.createElement('div');
	newNode.setAttribute("id", currentTime);
    newNode.setAttribute("class", 'cls-board-outer');
	var referenceNode = document.getElementById('md-span-comments');
    referenceNode.appendChild(newNode);

	$('#' + currentTime + '.cls-board-outer').addClass('focus');
	$('#md-span-comments .cls-board-outer').css('opacity', '0.4');
	$('#md-span-comments .cls-board-outer.focus').css('opacity', '1');
	$('#' + currentTime).offset({top: $('[datatext="' + currentTime + '"]').offset().top});
	$('#' + currentTime).addClass('has_text').show();
    $('#history-toggle').attr('data-count', $('.cls-board-outer:visible').length);
     //Activate Show All comment button in setting panel
    $('.components-form-toggle').addClass('is-checked');
    $('#inspector-toggle-control-0__help').html('All comments will show on the content area.');
	
		wp.data.dispatch( 'core/block-editor' ).updateBlock( blockIndex, {
			attributes: {
				datatext:currentTime,
			}
		} );
	

    ReactDOM.render(
        <Board datatext={currentTime}  freshBoard={1} commentedOnText={commentedOnText}/>,
        document.getElementById(currentTime)
    )
	if($("#md-span-comments").is(':empty'))
	{
		$('body').removeClass("commentOn");
	   
	}else{
		$('body').addClass("commentOn");
	}

	// Toogle hide-comments class if the comments is hidden when try to add new one.
	if( $( 'body' ).hasClass( 'hide-comments' ) ) {
		$( 'body' ).removeClass( 'hide-comments' )
	}


};
function getCommentedText(blockType,block)
{
	
	var commentedOnText,url;
	if(block.attributes){
		if(block.attributes.alt){
			commentedOnText = block.attributes.alt
		}
		else if(block.attributes.mediaAlt){
			commentedOnText = block.attributes.mediaAlt;
		}
		else{
			
			if( 'core/video' === blockType || 'core/audio' ===blockType ){
				 url = block.attributes.src;
			}else if('core/media-text' == blockType){
				 url = block.attributes.mediaUrl;
			}else if('core/gallery' == blockType){
				 url =block.attributes.images[0].url;
			}
			else {
			
				 url = block.attributes.url;
			}
	
			var srcText = url.split("/");
			commentedOnText= srcText[srcText.length-1];
		}	
		return commentedOnText;
	}

}

const addNewAttribute = createHigherOrderComponent((BlockListBlock) => {


	return (props) => {
	
		if ( ! allowedBlocks.includes( props.name)) {
			return(<BlockListBlock {...props} />);
		}
		
		const { attributes } = props;
		let newDatatext = (attributes.datatext != undefined) ? attributes.datatext : '';

		if(attributes.datatext ){
			return <BlockListBlock {...props} wrapperProps={{ 'datatext': newDatatext , 'data-rich-text-format-boundary': true }} className={'commentIcon'} />;
		}
		else{
			return <BlockListBlock {...props} />;
		}
	
	};
}, 'addNewAttribute');

wp.hooks.addFilter(
'editor.BlockListBlock',
'mdComment/add-new-attribute',
addNewAttribute
);

    
const nonTextBlock = createHigherOrderComponent( BlockEdit => {
	
 
	return ( props ) => {
      
        const {
			name,
			attributes,
			isSelected,
		} = props;

		//const {datatext} = attributes;

		if ( ! allowedBlocks.includes(props.name) ) {
		
			return (
				
				<BlockEdit  { ...props } />
				
			);
		}
	
			return (
			
				<Fragment>
					
				<BlockEdit {...props} />
				{ isSelected && allowedBlocks.includes( name ) && (props.attributes.url || props.attributes.src || props.attributes.mediaUrl || props.attributes.images) && 
					<BlockControls>
                          <ToolbarGroup>
				            <ToolbarButton
					         icon="admin-comments"
							 isActive={ isSelected }
					       	 label={__('Comment')}
                             onClick={onToggleNonTextBlock}
							 className={`toolbar-button-with-text toolbar-button__${name}`}
							 onChange={e => props.setAttributes({datatext: currentTime})}
							/>
			            </ToolbarGroup>
					</BlockControls>
				}
				</Fragment>
			);
	};
}, 'nonTextBlock' );

addFilter( 'editor.BlockEdit', 'mdComment/nontext-block', nonTextBlock );

addFilter(
	'blocks.registerBlockType',
	'mdComment/gallery-extension',
	( props, name ) => {

		if ( ! allowedBlocks.includes( name ) ) {	
			return props;
		}
		
		const attributes = {
			...props.attributes,
			datatext: {
				type: 'string',
				default : '',
							
			},
		};

		return { ...props, attributes };
	}
);
addFilter('blocks.getSaveContent.extraProps', 'mdComment/saveDatatext', function (extraProps , blockType, attributes) {

	if(!allowedBlocks.includes( blockType.name)){
		return extraProps;
	}
	
		
		if('undefined' !== attributes.datatext || null !== attributes.datatext){
			Object.assign(extraProps , {
				datatext:attributes.datatext,
			});
		/*extraProps = _extends(extraProps, {
			datatext:attributes.datatext,
		});*/
		return extraProps ;
	}	
});

/*var _extends = Object.assign || function (target) {
	for (var i = 1; i < arguments.length; i++) {
		var source = arguments[i];
		for (var key in source) {
			if (Object.prototype.hasOwnProperty.call(source, key)) {
				target[key] = source[key];
			}
		}
	}
	return target;
};*/


export default compose() (nonTextBlock) ;
