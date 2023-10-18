<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * This file contians all global functions for realtime feature
 *
 * @link              #
 * @since             1.0.5
 * @package           content-collaboration-inline-commenting
 */


/**
 * Check if real-time feature is enabled.
 *
 * @return bool
 */
if (!function_exists('is_real_time_enabled')) {
	function is_real_time_enabled() {
		global $post;
		$real_time_mode = ! empty( $post ) ? get_post_meta( $post->ID, '_is_real_time_mode', true ) : '';
		if ( '1' === $real_time_mode ) {
			return true;
		} else {
			return false;
		}
	}
}
