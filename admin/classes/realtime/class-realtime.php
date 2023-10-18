<?php
/**
 * Real-Time Class.
 *
 * @package content-collaboration-inline-commenting
 */

/**
 * Class for Real-Time Co-editing.
 */
class Realtime {

	/**
	 * Construct method.
	 */
	public function __construct() {

		// load class.
		$this->setup_hooks();
	}

	/**
	 * To register action/filter.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function setup_hooks() {

		/**
		 * Actions/filters.
		 */
		add_filter( 'allowed_block_types_all', array( $this, 'blacklist_blocks' ) );
		add_filter( 'wp_check_post_lock_window', array( $this, 'enable_post_lock' ) );
		add_action( 'save_post', array( $this, 'update_edit_post_lock' ), 10, 2 );

	}

	/**
	 * Blacklist blocks.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function blacklist_blocks() {
		// get all the registered blocks.
		$blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$roles = (array) $user->roles;

			if ( in_array( 'guest', $roles, true ) ) {
				unset( $blocks['core/navigation'] ); // guest cannot create new menu.
			}
		}

		// return the new list of allowed blocks.
		return array_keys( $blocks );

	}

	/**
	 * Enable post lock if real-time feature disable for a post.
	 *
	 * @return bool|int
	 * @since 1.0.0
	 */
	public function enable_post_lock( $interval ) {
		if ( is_real_time_enabled() ) {
			$interval = false;
		}
		return $interval;
	}

	/**
	 * Edit post lock so the post takeover is visible for other users on refresh if real-time feature disable for a post.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function update_edit_post_lock( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$is_autosave = wp_is_post_autosave( $post_id );
		if ( ! $post || $is_autosave ) {
			return false;
		}

		if ( ! is_real_time_enabled() ) {
			$now     = time();
			$user_id = get_current_user_id();
			$lock    = "$now:$user_id";
			update_post_meta( $post->ID, '_edit_lock', $lock );
			update_post_meta( $post->ID, '_edit_lock_realtime_user', $user_id );
		}
	}

}
new Realtime();
