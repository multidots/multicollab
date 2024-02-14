<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              #
 * @since             1.0.0
 * @package           content-collaboration-inline-commenting
 *
 * @wordpress-plugin
 * Plugin Name:       Multicollab
 * Description:       Google Docs-Style Collaboration in WordPress. No copy-paste between apps. Faster Publishing. Works in Your Workflow. <strong>Features:</strong> Inline Commenting. Real-time Editing. Suggest Edits. Email & Slack Notifications. Guest Collaboration. Custom Permission. And a lot more!
 * Version:           4.3
 * Author:            Multicollab
 * Author URI:        https://www.multicollab.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       content-collaboration-inline-commenting
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}

// Plugin version.
define( 'COMMENTING_BLOCK_VERSION', '4.3' );

// Define constants.
define( 'COMMENTING_BLOCK_URL', plugin_dir_url( __FILE__ ) );
define( 'COMMENTING_BLOCK_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMMENTING_BLOCK_BASE', plugin_basename( __FILE__ ) );
define( 'COMMENTING_NONCE', 'BFaYbfonJ=n@R<8kId|nN8x #W[-S>1%Sazm%<' );

add_filter( 'plugin_row_meta', 'cf_custom_plugin_row_meta', 10, 4 );

/**
 * Added extra links to default meta row.
 *
 * @author: Himanshu shekhar
 * @version 4.1
 *
 * @param array  $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file The path to the main plugin file.
 */
if ( ! function_exists( 'cf_custom_plugin_row_meta' ) ) {
    function cf_custom_plugin_row_meta( $plugin_meta, $plugin_file ) {
    	if ( strpos( $plugin_file, plugin_basename( __FILE__ ) ) !== false ) {
    		$insert_position = array_search( 'By Multicollab', array_keys( $plugin_meta ) );
    		$links_array     = array();

    		// Add your custom links to the array.
    		$links_array['upgrade_link'] = '<a href="https://www.multicollab.com/upgrade-to-premium/" style="color:#4abe17" target="_blank">Upgrade to Pro</a>';

    		// Insert the "Upgrade to Pro" link at the desired position.
    		array_splice( $plugin_meta, $insert_position + 1, 0, $links_array );

    	}

    	return $plugin_meta;
    }
}

/**
 * Set Store variable and EDD plan IDs for localhost and Live servers.
 *
 * @author: Rishi Shah
 * @version 3.4
 */
define( 'CF_PROMOTIONAL_BANNER_API_URL', 'https://www.multicollab.com/' );
define( 'CF_STORE_URL', 'https://www.multicollab.com/' );
define( 'EDD_PLAN_PRO', 3793 );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-commenting-block-activator.php
 */
if ( ! function_exists( 'cf_activate_commenting_block' ) ) {
	function cf_activate_commenting_block() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-commenting-block-activator.php'; // phpcs:ignore
		Commenting_block_Activator::activate();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-commenting-block-deactivator.php
 */
if ( ! function_exists( 'cf_deactivate_commenting_block' ) ) {
	function cf_deactivate_commenting_block() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-commenting-block-deactivator.php'; // phpcs:ignore
		Commenting_block_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'cf_activate_commenting_block' );
register_deactivation_hook( __FILE__, 'cf_deactivate_commenting_block' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-commenting-block.php'; // phpcs:ignore

/**
 * Load global function file.
 */
require plugin_dir_path( __FILE__ ) . 'includes/commenting-block-functions.php'; // phpcs:ignore

/**
 * Load realtime feature function file.
 */
require plugin_dir_path( __FILE__ ) . 'includes/realtime-functions.php'; // phpcs:ignore

/**
 * Load EDD plan details class file.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-edd-plan-details.php'; // phpcs:ignore


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if ( ! function_exists( 'cf_run_commenting_block' ) ) {
	function cf_run_commenting_block() {

		delete_option( 'cf_activated_license_details' );
		$plugin = new Commenting_block();
		$plugin->run();
	}
}

cf_run_commenting_block();

/**
 * Redirect after plugin activation.
 *
 * @since 1.0.4
 */
add_action( 'activated_plugin', array( 'Commenting_block', 'cf_redirect_on_activate' ) );

/**
 * Load plugin textdomain.
 *
 * @since 1.2.0
 */
if ( ! function_exists( 'cf_load_textdomain' ) ) {
	function cf_load_textdomain() {

		$cf_edd = new CF_EDD();
		if( ! $cf_edd->is_free() ) {
			load_plugin_textdomain( 'content-collaboration-inline-commenting', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
	}
}

add_action( 'init', 'cf_load_textdomain' );
