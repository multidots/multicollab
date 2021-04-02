import React from 'react';
import axios from 'axios';
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
            total: 0,
            threads: [],
            isLoading: false,
            showComments: true,
            collapseLimit: 25,
        }
        // Triggering settings cog.
        this.triggerSettingsCog();

        // Get the Page ID.
        this.postID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

        // Binding Methods.
        this.edit               = this.edit.bind( this );
        this.reply              = this.reply.bind( this );
        this.delete             = this.delete.bind( this );
        this.toggleCollapseLink       = this.toggleCollapseLink.bind( this );
        this.resolveThread      = this.resolveThread.bind( this );
        this.handleShowComments = this.handleShowComments.bind( this );
    }

    /**
     * Trigger Default Settings Button on Sidebar.
     */
    triggerSettingsCog() {
        var _this = this;
        $( window ).on( 'load', function() {
            $( '.components-button' ).each( function() {
                var arialabel = $( this ).attr( 'aria-label' );
                if( 'Settings' === arialabel ) {
                    if( ! $( this ).hasClass( 'is-pressed' ) ) {
                        $( this ).trigger( 'click' );
                    }
                }
            } )
        } )

    }

    /**
     * Collapse Selected Text.
     */
    collapseText( str ) {
        let text = str;
        if( null !== this.state.collapseLimit ) {
            text = str.slice( 0, this.state.collapseLimit ) + ( str.length > this.state.collapseLimit ? '...' : '' );
        }
        return ( __( text, 'content-collaboration-inline-commenting' ) );
    }

    toggleCollapseLink( e ) {
        var targetID = e.target.dataset.id;
        var _this = e.target;
        if( _this.innerHTML === 'More' ) {
            _this.innerHTML = __( 'Collapse', 'content-collaboration-inline-commenting' );
            $( `#show-all-${targetID}` ).removeClass( 'js-hide' );
            $( `#show-less-${targetID}` ).addClass( 'js-hide' );
        } else {
            _this.innerHTML = __( 'More', 'content-collaboration-inline-commenting' );
            $( `#show-all-${targetID}` ).addClass( 'js-hide' );
            $( `#show-less-${targetID}` ).removeClass( 'js-hide' );
        }
    }

    /**
     * Get All Comments Related to this Post.
     */
    getComments() {
        const url = `${activityLocalizer.apiUrl}/cf/v2/activities`;
        axios.get( url, {
            params: {
                postID: this.postID,
            }
        } )
        .then( ( res ) => {
            var threads = [ ...this.state.threads ];
            if( res.data.threads.length > 0 ) {
                threads.push( res.data.threads )
            }
            this.setState({
                threads: threads,
                total: threads.length ? threads.length : 0,
                isLoading: false
            })
        } )
        .catch( ( error ) => {
            console.log( error )
        } )
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
            });
            // Remove Tag.
            removeTag( elID ); // eslint-disable-line
        } else {
            $( `#${elID} [type="checkbox"]` ).prop( 'checked', false );
        }
        
    }

    /**
     * Reply on a thread.
     */
    reply( e ) {
        e.preventDefault();
        $( '.js-cancel-comment' ).trigger( 'click' );
        // Open comment if not opened.
        if( ! this.state.showComments ) {
            this.handleShowComments();
        }

        var elID = e.target.dataset.elid;
        var findMdSpan = '.mdspan-comment';
        $( findMdSpan ).each( function() {
            var datatext = $( this ).attr( 'datatext' );
            if( elID === datatext ) {
                $( '.cls-board-outer' ).removeClass( 'focus' ).css( { opacity: 0.4, top: 0 } ); // Resetting before trigger.
                $( this ).attr( 'data-rich-text-format-boundary', 'true' );
                $( `#${elID}` ).addClass( 'focus' ).offset( { top: $( `[datatext="${elID}"]` ).offset().top } ).css( { opacity: 1 } );
            }
        } );
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
        var editID = e.target.dataset.editid;
        $( '.js-cancel-comment' ).trigger( 'click' );
        $( `#${elID}` ).addClass( 'focus' ).offset( { top: $( `[datatext="${elID}"]` ).offset().top } ).css( { opacity: 1 } );
        $( `#${elID} #${editID} .js-edit-comment` ).trigger( 'click' );
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
        var deleteID = e.target.dataset.deleteid;
        $( `#${deleteID} .js-cancel-comment` ).trigger( 'click' );

        $( `#${elID} #${deleteID} .js-trash-comment` ).trigger( 'click' );
    }

    /**
     * Track if post updated or published.
     */
    isPostUpdated() {
        const _this = this;
        var counter = 1;
        wp.data.subscribe( function () {
            let select                    = wp.data.select('core/editor');
            var isSavingPost              = select.isSavingPost();
            var isAutosavingPost          = select.isAutosavingPost();
            var didPostSaveRequestSucceed = select.didPostSaveRequestSucceed();
            var status = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'status' );
            if ( isSavingPost && !isAutosavingPost ) {
                if( didPostSaveRequestSucceed ) {
                    if( 'draft' === status || 'publish' === status ) {
                        console.log( 'trigger' )
                        if( counter % 3 === 0 ) {
                            _this.setState({
                                threads: [],
                                limit: 10,
                                offset: 0
                            })
                            _this.getComments();
                        }
                        counter++;
                    }
                }
            }
        })
    }

    /**
     * Handle Show Comments
     */
    handleShowComments() {
        if ( true === this.state.showComments ) {
            $( 'body' ).addClass( 'hide-comments' );
        } else {
            $( 'body' ).removeClass( 'hide-comments' );
        }
        this.setState({
            showComments: ! this.state.showComments
        })
    }

    componentDidMount() {
        this.getComments(); // Calling getComments() to get the comments related to this post.
        this.isPostUpdated(); // Calling isPostUpdated() when the post saving status chagned.
    }

    render() {
        const { threads, showComments } = this.state;
        return (
            <Fragment>
                <PluginSidebarMoreMenuItem target="cf-activity-center">
                    { __( "Multicollab", "cf-activity-center" ) }
                </PluginSidebarMoreMenuItem>
                <PluginSidebar
                        name="cf-activity-center"
                        title={ __( "Multicollab", "cf-activity-center" ) }
                >
                    <TabPanel className="my-tab-panel"
                        tabs={ [
                            {
                                name: 'cf-activity-centre',
                                title: 'Activity Centre',
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
                                            { threads.length <= 0 && (
                                                <div className="user-data-row">
                                                    <strong>{ __( 'No recent activities found!', 'content-collaboration-inline-commenting' ) }</strong>
                                                </div>
                                            ) }
                                            { undefined !== threads && threads.map( ( thread ) => {
                                                return (
                                                    thread.map( ( th ) => {
                                                        return(
                                                            <div className={ 'true' === th.resolved ? 'user-data-row cf-thread-resolved' : 'user-data-row' } id={ th.elID } key={ th.elID }>
                                                                {
                                                                    th.activities.map( ( c, index ) => {
                                                                        if( 'permanent_draft' !== c.status || 'draft' !== c.status ) {
                                                                            return (
                                                                                <div className="user-data-box" key={ index }>
                                                                                    <div className="user-avatar">
                                                                                        <img src={ c.userData.avatarUrl } alt={ c.userData.username } />
                                                                                    </div>
                                                                                    <div className="user-data">
                                                                                        <div className="user-data-header">
                                                                                            <span class="user-name">{ c.userData.username } </span>
                                                                                            <time class="user-commented-date">{ c.timestamp }</time>
                                                                                        </div>
                                                                                        <div className="user-commented-on">
                                                                                            { 0 >= index && (
                                                                                                <React.Fragment>
                                                                                                    <span className="user-comment-status">{ __( 'Selected Text:', 'content-collaboration-inline-commenting' ) }</span>
                                                                                                    <blockquote>
                                                                                                        { 'deleted' === c.status || 'true' === th.resolved ?
                                                                                                            (
                                                                                                                <React.Fragment>
                                                                                                                    <span id={`show-all-${c.id}`} class="user-commented-on show-all js-hide" data-id={ th.elID }>{ __( th.selectedText, 'content-collaboration-inline-commenting' ) }</span>
                                                                                                                    <span id={`show-less-${c.id}`}class="user-commented-on show-less" data-id={ th.elID }>{ this.collapseText( th.selectedText ) }</span>
                                                                                                                    { 25 <= th.selectedText.length && (
                                                                                                                        <a
                                                                                                                            href="javascript:void(0)"
                                                                                                                            className="cf-show-more"
                                                                                                                            data-id={ c.id }
                                                                                                                            onClick={ this.toggleCollapseLink.bind( this ) }
                                                                                                                        >
                                                                                                                            { __( 'More', 'content-collaboration-inline-commenting' ) }
                                                                                                                        </a>
                                                                                                                    ) }
                                                                                                                </React.Fragment>
                                                                                                            ) : (
                                                                                                                <React.Fragment>
                                                                                                                    <a id={`show-all-${c.id}`} class="user-commented-on show-all js-hide" data-elid={ th.elID } href="javascript:void(0)" onClick={ this.reply.bind( this ) }>{ __( th.selectedText, 'content-collaboration-inline-commenting' ) }</a>
                                                                                                                    <a id={`show-less-${c.id}`}class="user-commented-on show-less" data-elid={ th.elID } href="javascript:void(0)" onClick={ this.reply.bind( this ) }>{ this.collapseText( th.selectedText ) }</a>
                                                                                                                    { 25 <= th.selectedText.length && (
                                                                                                                        <a
                                                                                                                            href="javascript:void(0)"
                                                                                                                            className="cf-show-more"
                                                                                                                            data-id={ c.id }
                                                                                                                            onClick={ this.toggleCollapseLink.bind( this ) }
                                                                                                                        >
                                                                                                                            { __( 'More', 'content-collaboration-inline-commenting' ) }
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
                                                                                                <del dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize( c.thread ) }}></del> // phpcs:ignore
                                                                                            ) : (
                                                                                                <span dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize( c.thread ) }}></span> // phpcs:ignore
                                                                                            ) }
                                                                                        </div>
                                                                                        { 'true' !== th.resolved && (
                                                                                            <div className="user-action">
                                                                                                { 'publish' === c.status && 0 >= index && (
                                                                                                    <React.Fragment>
                                                                                                        <a href="javascript:void(0)"
                                                                                                            className="user-cmnt-reply"
                                                                                                            data-elid={ th.elID }
                                                                                                            onClick={ this.reply.bind( this ) }
                                                                                                        >
                                                                                                            { __( 'Reply', 'content-collaboration-inline-commenting' ) }
                                                                                                        </a>
                                                                                                        <a href="javascript:void(0)"
                                                                                                            className="user-thread-resolve js-resolve-comment"
                                                                                                            onClick={ this.resolveThread.bind( this ) }
                                                                                                            data-elid={ th.elID }
                                                                                                        >
                                                                                                            { __( 'Resolve', 'content-collaboration-inline-commenting' ) }
                                                                                                        </a>
                                                                                                    </React.Fragment>
                                                                                                ) }
                                                                                                { 'publish' === c.status && 0 < index && (
                                                                                                    <React.Fragment>
                                                                                                        <a href="javascript:void(0)"
                                                                                                            className="user-cmnt-reply"
                                                                                                            data-elid={ th.elID }
                                                                                                            data-editid={ c.id }
                                                                                                            onClick={ this.edit.bind( this ) }
                                                                                                        >
                                                                                                            { __( 'Edit', 'content-collaboration-inline-commenting' ) }
                                                                                                        </a>
                                                                                                        <a href="javascript:void(0)"
                                                                                                            className="user-cmnt-delete"
                                                                                                            data-elid={ th.elID }
                                                                                                            data-deleteid={ c.id }
                                                                                                            onClick={ this.delete.bind( this ) }
                                                                                                        >
                                                                                                            { __( 'Delete', 'content-collaboration-inline-commenting' ) }
                                                                                                        </a>
                                                                                                    </React.Fragment>
                                                                                                ) }
                                                                                            </div>
                                                                                        ) }
                                                                                    </div>
                                                                                </div>
                                                                            )
                                                                        }
                                                                    } )
                                                                }
                                                                { 'true' === th.resolved && undefined !== th.resolvedBy && (
                                                                    <div className="user-data-box">
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
                                                    } )
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