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
 * @package           Commenting_block
 *
 * @wordpress-plugin
 * Plugin Name:       Gutenberg Commenting Feature
 * Plugin URI:        #
 * Description:       This plugin serves the commenting feature like Google Docs within the Gutenberg Editor!
 * Version:           1.0.0
 * Author:            multidots
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       Commenting_block
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'COMMENTING_BLOCK_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-commenting_block-activator.php
 */
function activate_commenting_block() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commenting_block-activator.php';
	Commenting_block_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-commenting_block-deactivator.php
 */
function deactivate_commenting_block() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commenting_block-deactivator.php';
	Commenting_block_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_commenting_block' );
register_deactivation_hook( __FILE__, 'deactivate_commenting_block' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-commenting_block.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_commenting_block() {

	$plugin = new Commenting_block();
	$plugin->run();

}
run_commenting_block();
