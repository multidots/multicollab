<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Comment/Suggestion permission.
 *
 * @link       #
 * @since      2.0.3
 *
 * @package    content-collaboration-inline-commenting
 */
class Commenting_Block_Permissions extends Commenting_block_Functions {

	/**
	 * Get the latest activities of comments.
	 *
	 * @return array Activity data.
	 */
	public function cf_get_pp_roles() {
		global $wp_roles;
		// set defaukt permission
		$this->cf_default_permissions();
		$all_roles      = $wp_roles->roles;
		$data           = array();
		$editable_roles = apply_filters( 'editable_roles', $all_roles );
		foreach ( $editable_roles as $key => $role ) {

			$data[ $key ]                        = array();
			$data[ $key ]['role']                = $role;
			$data[ $key ]['add_comment']         = '';
			$data[ $key ]['resolved_comment']    = '';
			$data[ $key ]['hide_comment']        = '';
			$data[ $key ]['add_suggestion']      = '';
			$data[ $key ]['resolved_suggestion'] = '';
			$data[ $key ]['hide_suggestion']     = '';
		}

		return $data;
	}

	/**
	 * Set default permissions.
	 *
	 * @return void
	 */
	public function cf_default_permissions() {

		global $wp_roles,$wpdb;
		$default_data   = array();
		$all_roles      = $wp_roles->roles;
		$editable_roles = apply_filters( 'editable_roles', $all_roles );
		$initial_count  = $wpdb->get_var( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name = 'cf_permissions'" );// db call ok; no-cache ok.
        if( 0 === (int) $initial_count ){ //phpcs:ignore
			foreach ( $editable_roles as $key => $role ) {
				if ( 1 === (int) isset( $role['capabilities']['edit_posts'] ) || 1 === (int) isset( $role['capabilities']['edit_pages'] ) ) { // Removed phpcs:ignore by Rishi Shah.
					$default_data[ $key ]['add_comment']         = 1;
					$default_data[ $key ]['resolved_comment']    = 1;
					$default_data[ $key ]['add_suggestion']      = 1;
					$default_data[ $key ]['resolved_suggestion'] = 1;

				}
			}
			update_option( 'cf_permissions', $default_data );
		}

	}
}
