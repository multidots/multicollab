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
 * Description:       A plugin serves the commenting and suggestion feature like Google Docs within the Gutenberg Editor! Content Collaboration made easy for WordPress.
 * Version:           2.0.2
 * Author:            Multidots
 * Author URI:        https://www.multidots.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       content-collaboration-inline-commenting
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'cf_fs' ) ) {
    cf_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'cf_fs' ) ) {
        // Create a helper function for easy SDK access.
        function cf_fs()
        {
            global  $cf_fs ;
            
            if ( !isset( $cf_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $cf_fs = fs_dynamic_init( array(
                    'id'             => '8961',
                    'slug'           => 'commenting-feature',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_6a91f1252c5c1715f64a8bc814685',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                    'menu'           => array(
                    'slug'       => 'commenting-feature',
                    'first-path' => 'admin.php?page=editorial-comments',
                    'support'    => false,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $cf_fs;
        }
        
        // Init Freemius.
        cf_fs();
        // Signal that SDK was initiated.
        do_action( 'cf_fs_loaded' );
    }
    
    // ... Your plugin's main file logic ...
}

// Plugin version.
define( 'COMMENTING_BLOCK_VERSION', '2.0.2' );
// Define constants.
define( 'COMMENTING_BLOCK_URL', plugin_dir_url( __FILE__ ) );
define( 'COMMENTING_BLOCK_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMMENTING_BLOCK_BASE', plugin_basename( __FILE__ ) );
define( 'COMMENTING_NONCE', 'BFaYbfonJ=n@R<8kId|nN8x #W[-S>1%Sazm%<' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-commenting-block-activator.php
 */
function cf_activate_commenting_block()
{
    require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-activator.php';
    // phpcs:ignore
    Commenting_block_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-commenting-block-deactivator.php
 */
function cf_deactivate_commenting_block()
{
    require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-deactivator.php';
    // phpcs:ignore
    Commenting_block_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'cf_activate_commenting_block' );
register_deactivation_hook( __FILE__, 'cf_deactivate_commenting_block' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require COMMENTING_BLOCK_DIR . 'includes/class-commenting-block.php';
// phpcs:ignore
/**
 * Load global function file.
 */
require COMMENTING_BLOCK_DIR . 'includes/commenting-block-functions.php';
// phpcs:ignore
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function cf_run_commenting_block()
{
    $plugin = new Commenting_block();
    $plugin->run();
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
function cf_load_textdomain()
{
    load_plugin_textdomain( 'content-collaboration-inline-commenting', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'cf_load_textdomain' );
/**
 * Change Plugin action link.
 *
 * @since 2.0
 */
add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    'cf_add_setting_links',
    1000,
    5
);
add_filter(
    'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ),
    'cf_add_setting_links',
    1000,
    5
);
function cf_add_setting_links( $actions )
{
    
    if ( !empty($actions) ) {
        if ( isset( $actions['upgrade'] ) ) {
            unset( $actions['upgrade'] );
        }
        $settings = array(
            'settings' => '<a href="admin.php?page=editorial-comments">' . __( 'Settings', 'General' ) . '</a>',
        );
        $site_link = array(
            'Get Premium' => '<a href="' . cf_fs()->get_upgrade_url() . '" >' . __( 'Get premium', 'General' ) . '</a>',
        );
        $actions = array_merge( $settings, $actions );
        $actions = array_merge( $site_link, $actions );
    }
    
    return $actions;
}

// Not like register_uninstall_hook(), you do NOT have to use a static function.
cf_fs()->add_action( 'after_uninstall', 'cf_fs_uninstall_cleanup' );