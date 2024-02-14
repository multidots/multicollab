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
}
