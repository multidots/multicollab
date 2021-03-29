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
            limit: 10,
            offset: 0,
            threads: [],
            isLoading: false,
            showComments: true,
        }
        // Get the Page ID.
        this.postID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

        // Binding Methods.
        this.reply              = this.reply.bind( this );
        this.loadmore           = this.loadmore.bind( this );
        this.resolveThread      = this.resolveThread.bind( this );
        this.handleShowComments = this.handleShowComments.bind( this );
    }

    /**
     * Get All Comments Related to this Post.
     */
    getComments() {
        const url = `${activityLocalizer.apiUrl}/cf/v2/activities`;
        axios.get( url, {
            params: {
                postID: this.postID,
                limit: this.state.limit,
                offset: this.state.offset
            }
        } )
        .then( ( res ) => {
            var threads = [ ...this.state.threads ];
            threads.push( res.data.threads )
            this.setState({
                threads: threads,
                total: res.data.total,
                isLoading: false
            })
        } )
        .catch( ( error ) => {
            console.log( error )
        } )
    }

    /**
     * Load More Comments.
     */
    loadmore( e ) {
        e.preventDefault();
        const newLimit = this.state.limit;
        const newOffset = parseInt( this.state.offset, 10 ) + parseInt( this.state.limit, 10 );
        this.setState({
            limit: newLimit,
            offset: newOffset,
            isLoading: true
        })
    }

    /**
     * Resolving Thread.
     */
    resolveThread( e ) {
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
        var elID = e.target.dataset.elid;
        var findMdSpan = '.mdspan-comment';
        $( findMdSpan ).each( function() {
            var datatext = $( this ).attr( 'datatext' );
            if( elID === datatext ) {
                $( '.cls-board-outer' ).removeClass( 'focus' ).removeAttr( 'style' ); // Resetting before trigger.
                $( this ).attr( 'data-rich-text-format-boundary', 'true' );
                $( `#${elID}` ).addClass( 'focus' ).offset( { top: $( `[datatext="${elID}"]` ).offset().top } );
            }
        } );
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
                        if( counter % 2 === 0 ) {
                            _this.setState({
                                threads: [],
                                limit: 10,
                                offset: 0
                            })
                            _this.getComments();
                        }
                    }
                }
                counter++;
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

    componentDidUpdate( prevProps, prevState ) {
        // If offset changes then load more comments.
        if( prevState.offset !== this.state.offset ) {
            // this.getComments();
        }
    }


    render() {
        const { threads, total, offset, limit, isLoading, showComments } = this.state;
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
                            },
                            {
                                name: 'cf-settings',
                                title: 'Settings',
                            },
                        ] }>
                        {
                            ( tab ) => {
                                if( 'cf-activity-centre' === tab.name ) {
                                    return (
                                        <div className="cf-activity-centre js-activity-centre">
                                            { undefined !== threads && threads.length < 0 && (
                                                <div className="user-data-row">
                                                    <strong>{ __( 'No recent activities found!', 'content-collaboration-inline-commenting' ) }</strong>
                                                </div>
                                            ) }
                                            { undefined !== threads && threads.map( ( thread ) => {
                                                return (
                                                    thread.map( ( th ) => {
                                                        return(
                                                            <div className="user-data-row" id={ th.elID } key={ th.elID }>
                                                                {
                                                                    th.activities.map( ( c, index ) => {
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
                                                                                                            __( th.selectedText, 'content-collaboration-inline-commenting' )
                                                                                                        ) : (
                                                                                                            <a class="user-commented-on" data-id={ th.elID } href="javascript:void(0)">{ __( th.selectedText, 'content-collaboration-inline-commenting' ) }</a>
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
                                                                                                    >
                                                                                                        { __( 'Edit', 'content-collaboration-inline-commenting' ) }
                                                                                                    </a>
                                                                                                    <a href="javascript:void(0)"
                                                                                                        className="user-cmnt-reply"
                                                                                                        data-elid={ th.elID }
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

                                            { total > (offset + limit) && (
                                                <a href="javascript:void(0)" className="cf-loadmore-activity js-loadmore" onClick={ this.loadmore.bind(this) }>
                                                    { true ===  isLoading ? (
                                                        __( 'Loading...', 'content-collaboration-inline-commenting' )
                                                    ) :(
                                                        __( 'Load More', 'content-collaboration-inline-commenting' )
                                                    )}
                                                </a>
                                            )}
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