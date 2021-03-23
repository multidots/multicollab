import React, { useEffect, useState } from 'react';
import axios from 'axios';
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { PanelBody, PanelRow } = wp.components;
import icons from './component/icons';
const $ = jQuery; // eslint-disable-line

class Comments extends React.Component {
    constructor( props ) {
        super( props )
        this.state = {
            isLoading: true,
            comments: [],
            total: 0,
            limit: 10,
            offset: 0,
        }
        // Get the Page ID.
        this.postID = wp.data.select('core/editor').getCurrentPostId(); // eslint-disable-line

        // Binding Methods.
        this.resolveThread = this.resolveThread.bind( this )
        this.loadmore = this.loadmore.bind( this )
    }

    /**
     * Get Initial Comments
     */
    getInitialComments() {
        const url = `${activityLocalizer.apiUrl}/cf/v2/activities`;
        axios.get( url, {
            params: {
                postID: this.postID,
                limit: this.state.limit,
                offset: this.state.offset
            }
        } )
        .then( ( res ) => {
            var comments = [ ...this.state.comments ];
            comments.push( Object.values( res.data.comments ).reverse() )
            this.setState({
                comments: comments,
                total: res.data.total
            })
        } )
        .catch( ( error ) => {
            console.log( error )
        } )
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
            var comments = [ ...this.state.comments ];
            comments.push( Object.values( res.data.comments ).reverse() )
            this.setState({
                comments: comments,
                total: res.data.total
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
            offset: newOffset
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

    componentDidMount() {
        this.getComments(); // Calling getComments() to get the comments related to this post.
    }

    componentDidUpdate( prevProps, prevState ) {
        var This = this;
        const unsubscribe = wp.data.subscribe( function () {
            let select = wp.data.select('core/editor');
            var isSavingPost = select.isSavingPost();
            var isAutosavingPost = select.isAutosavingPost();
            var didPostSaveRequestSucceed = select.didPostSaveRequestSucceed();
            if ( isSavingPost && !isAutosavingPost && didPostSaveRequestSucceed ) {
                This.setState({
                    comments: [],
                    limit: 10,
                    offset: 0
                })
                This.getInitialComments();
                console.log('submitted');

            }
            unsubscribe();
        })

        // If offset changes then load more comments.
        // if( prevState.offset !== this.state.offset ) {
        //     This.getComments();
        // }
    }

    render() {
        const { comments, total, offset, limit } = this.state;
        return (
            <Fragment>
                <PluginSidebarMoreMenuItem target="cf-activity-center">
                    { __( "Activity Center", "cf-activity-center" ) }
                </PluginSidebarMoreMenuItem>
                <PluginSidebar
                        name="cf-activity-center"
                        title={ __( "Activity Center", "cf-activity-center" ) }
                >
                    <PanelBody>
                        <div className="cf-activity-centre js-activity-centre">
                            { undefined !== comments && comments.map( ( cmnts ) => {
                                return(
                                    cmnts.map( ( cmntChunks ) => {
                                        const cmnt = Object.values( cmntChunks );
                                        return(
                                            cmnt.map( ( c ) => {
                                                return(
                                                    <div className="user-data-row" id={ c.dataid } key={ c.dataid }>
                                                        <div className="user-data-box">
                                                            <div className="user-avatar">
                                                                <img alt={ c.username } src={ c.profileURL } />
                                                            </div>
                                                            <div className="user-data">
                                                                <div className="user-data-header">
                                                                    <span class="user-name">{ c.username } </span>
                                                                    <span class="user-commented-date">{ c.dtTime }</span>
                                                                </div>
                                                                <div className="user-commented-on">
                                                                    <span className="user-comment-status">{ __( c.status, 'content-collaboration-inline-commenting' ) }</span>
                                                                    <blockquote>
                                                                        { __( 'resolved thread', 'content-collaboration-inline-commenting' ) === c.status
                                                                        || __( 'deleted comment of', 'content-collaboration-inline-commenting' ) === c.status
                                                                        || 'true' === c.resolved ?
                                                                            (
                                                                                __( c.commented_on_text, 'content-collaboration-inline-commenting' )
                                                                            ) : (
                                                                                <a class="user-commented-on" data-id={ c.dataid } href="javascript:void(0)">{ __( c.commented_on_text, 'content-collaboration-inline-commenting' ) }</a>
                                                                            )
                                                                        }
                                                                    </blockquote>
                                                                </div>
                                                                <div class="user-comment" dangerouslySetInnerHTML={{ __html: c.thread }}></div>
                                                                { 'true' !== c.resolved && (
                                                                    <div className="user-action">
                                                                        { __( 'commented on', 'content-collaboration-inline-commenting' ) === c.status && (
                                                                            <React.Fragment>
                                                                                <a href="javascript:void(0)"
                                                                                    className="user-cmnt-reply"
                                                                                    data-elid={ c.dataid }
                                                                                >
                                                                                    Reply
                                                                                </a>
                                                                                <a href="javascript:void(0)"
                                                                                    className="user-thread-resolve js-resolve-comment"
                                                                                    onClick={ this.resolveThread.bind( this ) }
                                                                                    data-elid={ c.dataid }
                                                                                >
                                                                                    Resolve
                                                                                </a>
                                                                            </React.Fragment>
                                                                        ) }
                                                                        { __( 'replied on', 'content-collaboration-inline-commenting' ) === c.status && (
                                                                            <React.Fragment>
                                                                                <a href="javascript:void(0)"
                                                                                    className="user-cmnt-reply"
                                                                                    data-elid={ c.dataid }
                                                                                >
                                                                                    Delete
                                                                                </a>
                                                                            </React.Fragment>
                                                                        ) }
                                                                    </div>
                                                                ) }
                                                            </div>
                                                        </div>
                                                    </div>
                                                )
                                            } )
                                        )
                                    } )
                                )
                            } )}
                            { total > (offset + limit) && (
                                <a href="javascript:void(0)" className="cf-loadmore-activity js-loadmore" onClick={ this.loadmore.bind(this) }>Load More</a>
                            )}
                        </div>
                    </PanelBody>
                </PluginSidebar>
            </Fragment>
        )
    }
}

registerPlugin( "cf-activity-center", {
    icon: icons.multicollab,
    render: Comments
});