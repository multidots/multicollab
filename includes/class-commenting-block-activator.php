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
		$cf_edd = new CF_EDD();
        $cf_websocket_url  = CF_STORE_URL . 'wp-json/cf-websocket-url/v2/cf-websocket-url?' . wp_rand();
        if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
            $cf_websocket_url_request = vip_safe_wp_remote_get( $cf_websocket_url, 3, 1, 20 ); //phpcs:ignore
        } else {
            $cf_websocket_url_request = wp_remote_get( $cf_websocket_url, array( 'timeout' => 20 ) ); // phpcs:ignore
        }
        
        $cf_websocket_url_request_data = json_decode( $cf_websocket_url_request['body'], true );
        
        update_option( 'cf_websocket_options', 'cf_websocket_default', true );
        if ( $cf_edd->is__premium_only() ) {
            update_option( 'cf_multiedit_websocket', $cf_websocket_url_request_data['pro']['wsurl'] );
        } else {
            update_option( 'cf_multiedit_websocket', $cf_websocket_url_request_data['free']['wsurl'] );
        }

    }
}
