import React, { useEffect, useState } from 'react';
import axios from 'axios';
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { PanelBody, PanelRow } = wp.components;


class Comments extends React.Component {
    constructor( props ) {
        super( props )
    }

    componentDidMount() {
        const url = `${activityLocalizer.apiUrl}/cf/v2/activities`;
        axios.get( url, {
            params: {
                postID: 58
            }
        } )
        .then( ( res ) => {
            console.log( res.data )
        } )
        .catch( ( error ) => {
            console.log( error )
        } )
    }

    render() {
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
                        <PanelRow>
                            { __( "Activity Center", "cf-activity-center" ) }
                        </PanelRow>
                    </PanelBody>
                </PluginSidebar>
            </Fragment>
        )
    }
}

registerPlugin( "cf-activity-center", {
    icon: "admin-plugins",
    render: Comments
});