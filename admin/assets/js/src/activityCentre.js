import React from 'react';
import axios from 'axios';
import renderHTML from 'react-render-html';


const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { PanelBody, TabPanel, ToggleControl } = wp.components;
import icons from './component/icons';
const $ = jQuery; // eslint-disable-line

class Comments extends React.Component {
    constructor( props ) {
        super( props )
        this.state = {
            threads: [],
            isLoading: true,
            showComments: true,
            collapseLimit: 45,
        }
      

        // Get the Page ID.
        this.postID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

        // Binding Methods.
        this.edit               = this.edit.bind( this );
        this.reply              = this.reply.bind( this );
        this.delete             = this.delete.bind( this );
        this.toggleCollapseLink = this.toggleCollapseLink.bind( this );
        this.resolveThread      = this.resolveThread.bind( this );
        this.handleShowComments = this.handleShowComments.bind( this );
     
        // Grab the current user id.
        this.currentUserID = activityLocalizer.currentUserID

    }

    /**
     * Collapse Comment Board in mobile on load.
     */
    collapseBoardOnMobile() {
        var checkWidth = window.innerWidth;
        if( 768 >= checkWidth ) {
            this.setState( { showComments: false } );
            $( 'body' ).addClass( 'hide-comments' );
            $('body').removeClass('commentOn');
        }
    }

    /**
     * Collapse Selected Text.
     */
    collapseText( str ) {
        let text = str;
        if( null !== this.state.collapseLimit && null !== text ) {
            text = str.slice( 0, this.state.collapseLimit ) + ( str.length > this.state.collapseLimit ? '...' : '' );
        }
        return ( __( text, 'content-collaboration-inline-commenting' ) );
    }

    /**
     * Changing collapse link text.
     */
    toggleCollapseLink( e ) {
        var targetID = e.target.dataset.id;
        var _this = e.target;
        if( _this.innerHTML === 'Show all' ) {
            _this.innerHTML = __( 'Collapse', 'content-collaboration-inline-commenting' ); // phpcs:ignore
            $( `#show-all-${targetID}` ).removeClass( 'js-hide' );
            $( `#show-less-${targetID}` ).addClass( 'js-hide' );
        } else {
            _this.innerHTML = __( 'Show all', 'content-collaboration-inline-commenting' ); // phpcs:ignore
            $( `#show-all-${targetID}` ).addClass( 'js-hide' );
            $( `#show-less-${targetID}` ).removeClass( 'js-hide' );
        }
    }

    /**
     * Get All Comments Related to this Post.
     */
    getComments () {
        // Set Loaidng to true;
      
        this.setState( { isLoading: true } );
      
        const url = `${activityLocalizer.apiUrl}/cf/v2/activities`;
        axios.get( url, {
            params: {
                postID: this.postID,
              
            }
        } )
        .then( ( res ) => {
            
           if( res.data.threads.length > 0 ) {
                this.setState({
                    threads: res.data.threads,
                    isLoading: false,
                })
            } else {
                this.setState({
                    threads: null,
                    isLoading: false
                })
            }
        } )
       
        .catch( ( error ) => {
            console.log( error )
        } )
   
    }

    /**
     * Setup active activity board.
     */
     setActiveBoard( elID ) {
        
        var findMdSpan = '.mdspan-comment';
        
        $( findMdSpan ).each( function() {
           var datatext = $( this ).attr( 'datatext' );
            if( elID === datatext ) {
               $( '.js-activity-centre .user-data-row' ).removeClass( 'active' );
               $( `#cf-${elID}` ).addClass( 'active' );
           }
       });
     
    }

    /**
     * Highlight Selected Text From Editor.
     */
    highlightSelectedText( elID ) {
        var findMdSpan = '.mdspan-comment';
        $( findMdSpan ).attr( 'data-rich-text-format-boundary', 'false' );
        $( findMdSpan ).each( function() {
            var datatext = $( this ).attr( 'datatext' );
            if( elID === datatext ) {
                $( this ).attr( 'data-rich-text-format-boundary', 'true' );
            }
        } );
    }

    /**
     * CLosing Sidebar On Mobile.
     */
    closingSidebarOnMobile() {
        var checkWidth = window.innerWidth;
        if( 768 >= checkWidth ) {
            wp.data.dispatch('core/edit-post').closeGeneralSidebar()
        }
    }

    /**
     * Add active class on activity center thread on post status change.
     */
    addActiveClassOnPostStatusChange() {
        const addActiveClass = setInterval( () => {
            var activeBoard = $( '.cls-board-outer.focus' ).attr( 'id' );
            if( undefined !== activeBoard ) {
                if( $( `#cf-${activeBoard}` ).hasClass( 'active' ) ) {
                    clearInterval( addActiveClass );
                }
                $( `#cf-${activeBoard}` ).addClass( 'active' );
            } else {
                clearInterval( addActiveClass )
            }
        }, 1000 )
    }

    /**
     * Resolving Thread.
     */
    resolveThread( e ) {
        // Open comment if not opened.
        if( ! this.state.showComments ) {
            this.handleShowComments();
        }
        var elID = e.target.dataset.elid;
        elID     = elID.replace( 'cf-', '' );
        var alertMessage = __( 'Are you sure you want to resolve this thread ?', 'content-collaboration-inline-commenting' );
        if ( confirm( alertMessage ) ) {

            var data = {
                'action': 'cf_resolve_thread',
                'currentPostID': this.postID,
                'metaId': `_${elID}`
            };
            $.post( ajaxurl, data, function () { // eslint-disable-line
                $( `#${elID}` ).remove();
                $( '#history-toggle' ).attr( 'data-count', $( '.cls-board-outer:visible' ).length );
                // Reset Comments Float.
                $( '#md-span-comments .cls-board-outer' ).removeClass( 'focus' );
                $( '#md-span-comments .cls-board-outer' ).removeAttr( 'style' );
                $( '[data-rich-text-format-boundary]' ).removeAttr( 'data-rich-text-format-boundary' );
                //if there is no comment editor will be at center position
                if($("#md-span-comments").is(':empty'))
                {
                    $('body').removeClass("commentOn");
                           
                }else{
                    $('body').addClass("commentOn");
                }
                        
            });
            // Remove Tag.
            removeTag( elID ); // eslint-disable-line
        } else {
            $( `#${elID} [type="checkbox"]` ).prop( 'checked', false );
        }

        // Setting active class.
        this.setActiveBoard( elID );

        // Closing Sidebar On Mobile.
        this.closingSidebarOnMobile();

    }

    /**
     * Reply on a thread.
     */
    reply( e ) {
        e.preventDefault();

        // Resetting all reply comment textarea.
        $( '.js-cancel-comment' ).trigger( 'click' );
      

        // Open comment if not opened.
        if( ! this.state.showComments ) {
            this.handleShowComments();
        }

        var elID = e.target.dataset.elid;
        elID     = elID.replace( 'cf-', '' );

        $( '.cls-board-outer' ).removeClass( 'focus' ).css( { opacity: 0.4, top: 0 } ); // Resetting before trigger.
        $( `#${elID}` ).trigger( 'click' );
        $( `mdspan[datatext=${elID}]` ).trigger( 'click' );
        
        // Highlight selected text from editor.
        this.highlightSelectedText( elID );

        // Setting active class on activity center.
        this.setActiveBoard( elID );

        // Closing Sidebar On Mobile.
        this.closingSidebarOnMobile();
    }

    /**
     * Edit a message.
     */
    edit( e ) {
        e.preventDefault();
        // Open comment if not opened.
        if( ! this.state.showComments ) {
            this.handleShowComments();
        }

        var elID = e.target.dataset.elid;
        elID     = elID.replace( 'cf-', '' );

        var editID = e.target.dataset.editid;
        editID     = editID.replace( 'cf-', '' );

        $( '.js-cancel-comment' ).trigger( 'click' );
        $( '.cls-board-outer' ).removeClass( 'focus' ).css( { opacity: 0.4, top: 0 } ); // Resetting before trigger.
        $( `#${elID}` ).trigger( 'click' );
        $( `#${elID} #${editID} .js-edit-comment` ).trigger( 'click' );

         
        // Highlight selected text from editor.
        this.highlightSelectedText( elID );

        // Setting active class.
        this.setActiveBoard( elID );

        // Closing Sidebar On Mobile.
        this.closingSidebarOnMobile();
    }

    /**
     * Delete a message.
     */
    delete( e ) {
        e.preventDefault();
        // Open comment if not opened.
        if( ! this.state.showComments ) {
            this.handleShowComments();
        }

        var elID = e.target.dataset.elid;
        elID     = elID.replace( 'cf-', '' );

        var deleteID = e.target.dataset.deleteid;
        deleteID     = deleteID.replace( 'cf-', '' );

        $( `#${deleteID} .js-cancel-comment` ).trigger( 'click' );
        $( `#${elID} #${deleteID} .js-trash-comment` ).trigger( 'click' );

      
        // Highlight selected text from editor.
        this.highlightSelectedText( elID );

        // Setting active class.
        this.setActiveBoard( elID );

        // Closing Sidebar On Mobile.
        this.closingSidebarOnMobile();
    }

    /**
     * Track if post updated or published.
     */
    isPostUpdated() {
     
        const _this = this;
        //set flag to restrict multiple call
        var checked = true;
         wp.data.subscribe( function () {
           
            let select                    = wp.data.select('core/editor');
            var isSavingPost              = select.isSavingPost();
            var isAutosavingPost          = select.isAutosavingPost();
            var didPostSaveRequestSucceed = select.didPostSaveRequestSucceed();
            var status = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'status' );
            if ( isSavingPost ) {
                checked = false;
            } else {
            
            if (  ! checked  && !isAutosavingPost ) {
                if( didPostSaveRequestSucceed ) {
                
                    if( 'draft' === status || 'publish' === status ) {
                       
                        _this.setState({
                            threads: [],
                        })
                    
                        _this.getComments();
                        _this.addActiveClassOnPostStatusChange();
                        
                    }
                }
                checked = true;
            }
        }
          
        })
    }

    /**
     * Handle Show Comments
     */
    handleShowComments() {
       
       var openBoards = $('.cls-board-outer:visible').length;
    
        if ( true === this.state.showComments ) {
            $( 'body' ).addClass( 'hide-comments' );
            $( 'body' ).removeClass( 'commentOn' );
        } else {
           
            $( 'body' ).removeClass( 'hide-comments' );
            if(0 === openBoards){
                $( 'body' ).removeClass( 'commentOn' );
            }else{
                $( 'body' ).addClass( 'commentOn' );
                $('.components-form-toggle').addClass('is-checked');
                $('#inspector-toggle-control-0__help').html('All comments will show on the content area.');
               
            }
        }
        this.setState({
            showComments: ! this.state.showComments
        })
    }

    /**
     * Appned Counter on Activity Center.
     */
    appendCounter() {
     
        wp.data.subscribe( function() {
            var isPluginSidebarOpen = wp.data.select( 'core/edit-post' ).isPluginSidebarOpened();
            var isEditorSidebarOpen = wp.data.select( 'core/edit-post' ).isEditorSidebarOpened();
            if( isPluginSidebarOpen && !isEditorSidebarOpen ) {
                var openBoards = $('.cls-board-outer:visible').length;
               
                setTimeout( function() {
                    if( $( '#history-toggle' ).length <= 0 ) {
                        if(0=== openBoards){
                            $('body').removeClass("commentOn");
                        }
                        const notificationCounter = `<span id="history-toggle" data-test="testing" data-count="${openBoards}"></span>`;
                        $( '.cf-sidebar-activity-centre' ).append( DOMPurify.sanitize( notificationCounter ) ); // phpcs:ignore
                    }
                }, 300 )
            }
        } );
    }

    /**
     * Add active class in activities thread on selected text click.
     */
    activeBoardOnSelectedText() {
        $( document.body ).on( 'click', '.mdspan-comment', function() {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const current_url = urlParams.get('current_url');
            if(current_url){
                urlParams.delete('current_url');
                window.history.replaceState({}, '', `${location.pathname}?${urlParams}`);
            }
           
            var datatext = $( this ).attr( 'datatext' );
            $( `#cf-${datatext}` ).addClass( 'active' );
        } )
    }

    componentDidMount() {
        this.collapseBoardOnMobile();
        this.getComments(); // Calling getComments() to get the comments related to this post.
        this.isPostUpdated(); // Calling isPostUpdated() when the post saving status chagned.
        this.appendCounter(); // Appending counter.
        this.activeBoardOnSelectedText(); // Add active class in activities thread on selected text click.
      
    }
    render() {
        const { threads, showComments, isLoading, collapseLimit } = this.state;
            return (
            <Fragment>
                <PluginSidebarMoreMenuItem target="cf-activity-center">
                    { __( "Multicollab", "cf-activity-center" ) }
                </PluginSidebarMoreMenuItem>
                <PluginSidebar
                    name="cf-activity-center"
                    title={ __( `Multicollab`, "cf-activity-center" ) }
                >
                    <TabPanel className="my-tab-panel"
                        tabs={ [
                            {
                                name: 'cf-activity-centre',
                                title: 'Activities',
                                className: 'cf-sidebar-activity-centre'
                            },
                            {
                                name: 'cf-settings',
                                title: 'Settings',
                                className: 'cf-sidebar-settings'
                            },
                        ] }>
                        {
                            ( tab ) => {
                                if( 'cf-activity-centre' === tab.name ) {
                                    return (
                                        <div className="cf-activity-centre js-activity-centre">
                                            { null === threads && (
                                                <div className="user-data-row">
                                                    <strong>{ __( 'No recent activities found!', 'content-collaboration-inline-commenting' ) }</strong>
                                                </div>
                                            ) }

                                            { true === isLoading && (
                                                <div className="user-data-row">
                                                    <strong>{ __( 'Loading...', 'content-collaboration-inline-commenting' ) }</strong>
                                                </div>
                                            ) }

                                            { undefined !== threads && null !== threads && threads.map( ( th ) => {
                                              
                                                return (
                                                    <div className={ 'true' === th.resolved ? 'user-data-row cf-thread-resolved' : 'user-data-row' } id={ `cf-${`${th.elID}`}` } key={ `cf-${`${th.elID}`}` }>
                                                        {
                                                            th.activities.map( ( c, index ) => {
                                                             
                                                                if( 'permanent_draft' !== c.status && 'draft' !== c.status ) {
                                                                    return (
                                                                        <div className={ 0 < index ? 'user-data-box user-reply' : 'user-data-box' } key={ index }>
                                                                            <div className="user-data">
                                                                                <div className="user-data-header">
                                                                                    <div className="user-avatar">
                                                                                        <img src={ c.userData.avatarUrl } alt={ c.userData.username } />
                                                                                    </div>
                                                                                    <div className="user-display-name">
                                                                                        <span class="user-name">{ c.userData.username } </span>
                                                                                        <time class="user-commented-date">{ c.timestamp }</time>

                                                                                    </div>
                                                                                </div>
                                                                                <div className="user-data-wrapper">
                                                                                    <div className="user-commented-on">
                                                                                        { 0 >= index && (
                                                                                            <React.Fragment>
                                                                                                <blockquote>
                                                                                                    { 'deleted' === c.status || 'true' === th.resolved ?
                                                                                                        (
                                                                                                            <React.Fragment>
                                                                                                                <span id={`show-all-${c.id}`} class="user-commented-on show-all js-hide" data-id={ `cf-${th.elID}` }>{ __( th.selectedText, 'content-collaboration-inline-commenting' ) }</span>
                                                                                                                <span id={`show-less-${c.id}`}class="user-commented-on show-less" data-id={ `cf-${th.elID}` }>{ this.collapseText( th.selectedText ) }</span>
                                                                                                                { null !== th.selectedText && collapseLimit <= th.selectedText.length && (
                                                                                                                    <a
                                                                                                                        href="javascript:void(0)"
                                                                                                                        className="cf-show-more"
                                                                                                                        data-id={ c.id }
                                                                                                                        onClick={ this.toggleCollapseLink.bind( this ) }
                                                                                                                    >
                                                                                                                        { __( 'Show all', 'content-collaboration-inline-commenting' ) }
                                                                                                                    </a>
                                                                                                                ) }
                                                                                                            </React.Fragment>
                                                                                                        ) : (
                                                                                                            <React.Fragment>
                                                                                                                <a id={`show-all-${c.id}`} class="user-commented-on show-all js-hide" data-elid={ `cf-${th.elID}` } href="javascript:void(0)" onClick={ this.reply.bind( this ) }>{ __( th.selectedText, 'content-collaboration-inline-commenting' ) }</a>
                                                                                                                <a id={`show-less-${c.id}`}class="user-commented-on show-less" data-elid={ `cf-${th.elID}` } href="javascript:void(0)" onClick={ this.reply.bind( this ) }>{ this.collapseText( th.selectedText ) }</a>
                                                                                                                { null !== th.selectedText && collapseLimit <= th.selectedText.length && (
                                                                                                                    <a
                                                                                                                        href="javascript:void(0)"
                                                                                                                        className="cf-show-more"
                                                                                                                        data-id={ c.id }
                                                                                                                        onClick={ this.toggleCollapseLink.bind( this ) }
                                                                                                                    >
                                                                                                                        { __( 'Show all', 'content-collaboration-inline-commenting' ) }
                                                                                                                    </a>
                                                                                                                ) }
                                                                                                            </React.Fragment>

                                                                                                        )
                                                                                                    }
                                                                                                </blockquote>
                                                                                            </React.Fragment>
                                                                                        ) }
                                                                                       
                                                                                    </div>
                                                                                    <div class="user-comment">
                                                                                        { 0 < index && 'deleted' === c.status ? (

                                                                                            <del>{renderHTML(c.thread) }</del> // phpcs:ignore
                                                                                        ) : (
                                                                                            <span > {renderHTML( c.thread )}</span> // phpcs:ignore
                                                                                        ) }
                                                                                        
                                                                                    </div>
                                                                                    {c.editedTime.length>0 &&  'deleted' !== c.status &&
                                                                                      <time class="user-commented-date"> edited {c.editedTime}</time>
                                                                                    }
                                                                                    { 'publish' === c.status && 0 >= index && undefined !== th.assignedTo.username && (
                                                                                        <div class="user-assigned-to">
                                                                                            <span class="icon"></span>
                                                                                            <span class="assign-avatar-data">
                                                                                                { __( 'Assigned to', 'content-collaboration-inline-commenting' ) }
                                                                                                <a href={`mailto:${th.assignedTo.email}`} title={th.assignedTo.username}> {th.assignedTo.username}</a>
                                                                                            </span>
                                                                                        </div>
                                                                                    ) }
                                                                                    { 'true' !== th.resolved && (
                                                                                        <div className="user-action">
                                                                                            { 'publish' === c.status && 0 >= index && (
                                                                                                <React.Fragment>
                                                                                                    <a href="javascript:void(0)"
                                                                                                        className="user-cmnt-reply"
                                                                                                        data-elid={ `cf-${th.elID}` }
                                                                                                        onClick={ this.reply.bind( this ) }
                                                                                                        title={ __( 'Reply', 'content-collaboration-inline-commenting' ) }
                                                                                                    >
                                                                                                        { __( 'Reply', 'content-collaboration-inline-commenting' ) }
                                                                                                    </a>
                                                                                                    <a href="javascript:void(0)"
                                                                                                        className="user-thread-resolve js-resolve-comment"
                                                                                                        onClick={ this.resolveThread.bind( this ) }
                                                                                                        data-elid={ `cf-${th.elID}` }
                                                                                                        title={ __( 'Mark as done', 'content-collaboration-inline-commenting' ) }
                                                                                                    >
                                                                                                        { __( 'Mark as done', 'content-collaboration-inline-commenting' ) }
                                                                                                    </a>
                                                                                                </React.Fragment>
                                                                                            ) }
                                                                                            { 'publish' === c.status && 0 < index && parseInt( this.currentUserID, 10 ) === c.userData.id && (
                                                                                                <React.Fragment>
                                                                                                    <a href="javascript:void(0)"
                                                                                                        className="user-cmnt-reply"
                                                                                                        data-elid={ `cf-${th.elID}` }
                                                                                                        data-editid={ c.id }
                                                                                                        onClick={ this.edit.bind( this ) }
                                                                                                        title={ __( 'Edit', 'content-collaboration-inline-commenting' ) }
                                                                                                    >
                                                                                                        { __( 'Edit', 'content-collaboration-inline-commenting' ) }
                                                                                                    </a>
                                                                                                    <a href="javascript:void(0)"
                                                                                                        className="user-cmnt-delete"
                                                                                                        data-elid={ `cf-${th.elID}` }
                                                                                                        data-deleteid={ c.id }
                                                                                                        onClick={ this.delete.bind( this ) }
                                                                                                        title={ __( 'Delete', 'content-collaboration-inline-commenting' ) }
                                                                                                    >
                                                                                                        { __( 'Delete', 'content-collaboration-inline-commenting' ) }
                                                                                                    </a>
                                                                                                </React.Fragment>
                                                                                            ) }
                                                                                        </div>
                                                                                    ) }
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                }
                                                            } )
                                                        }
                                                        { 'true' === th.resolved && undefined !== th.resolvedBy && (
                                                            <div className="user-data-box cf-mark-as-resolved">
                                                                <div className="user-avatar">
                                                                    <img src={ th.resolvedBy.avatarUrl } alt={ th.resolvedBy.username } />
                                                                </div>
                                                                <div className="user-data">
                                                                    <div className="user-data-header">
                                                                        <span class="user-name">{ th.resolvedBy.username } </span>
                                                                        <time class="user-commented-date">{ th.resolvedTimestamp }</time>
                                                                    </div>
                                                                    <div className="user-comment">
                                                                        <strong>{ __( 'Marked as resolved.', 'content-collaboration-inline-commenting' ) }</strong>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        ) }
                                                    </div>
                                                )
                                            } ) }
                                        </div>
                                    )
                                }
                                if( 'cf-settings' === tab.name ) {
                                    return(
                                        <PanelBody>
                                            <ToggleControl
                                                label="Show All Comments"
                                                help={ showComments ? 'All comments will show on the content area.' : 'All comments will be hidden.' }
                                                checked={ showComments }
                                                onChange={ this.handleShowComments.bind( this ) }
                                            />
                                        </PanelBody>
                                    )
                                }
                            }
                        }
                    </TabPanel>

                </PluginSidebar>
            </Fragment>
        )
    }
}

registerPlugin( "cf-activity-center", {
    icon: icons.multicollab,
    render: Comments
});