<?php

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    content-collaboration-inline-commenting
 *
 * @author     multidots
 */
class Commenting_block_i18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'COMMENTING_BLOCK',
            false,
            dirname(dirname(COMMENTING_BLOCK_BASE)) . '/languages/'
        );
    }
}
