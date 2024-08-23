<?php

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Fired during plugin activation
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 *
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    content-collaboration-inline-commenting
 *
 * @author     multidots
 */
class Commenting_block_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {

        // Set default websocket when plugin activates.
        // Get multicollab plan details.

        // Set default permissions.
        global $wp_roles,$wpdb;
        $default_data   = array();
        $all_roles      = $wp_roles->roles;
        $editable_roles = apply_filters( 'editable_roles', $all_roles );
        $initial_count  = $wpdb->get_var( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name = 'cf_permissions'" );// db call ok; no-cache ok.
        if( 0 === (int) $initial_count ){ //phpcs:ignore
            if ((is_array($editable_roles) && !empty($editable_roles)) || (is_object($editable_roles) && !empty((array)$editable_roles))) {
                foreach ( $editable_roles as $key => $role ) {
                    if ( 1 === (int) isset( $role['capabilities']['edit_posts'] ) || 1 === (int) isset( $role['capabilities']['edit_pages'] ) ) { // Removed phpcs:ignore by Rishi Shah.
                        $default_data[ $key ]['add_comment']         = 1;
                        $default_data[ $key ]['resolved_comment']    = 1;
                        $default_data[ $key ]['resolved_suggestion'] = 1;

                    }
                }
            }
            update_option( 'cf_permissions', $default_data );
        }
    }
}
