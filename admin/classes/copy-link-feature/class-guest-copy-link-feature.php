<?php
/**
 * Copy link feature and request access functionality.
 *
 * @package multicollab
 */

/**
 * Class for copy link feature.
 */
class Copy_Link_Feature {

	/**
	 * Rewrite rules for the request access page.
	 *
	 * @return void
	 * @since 4.0
	 */
	public function cf_add_request_access_endpoint() {
	
		if ( function_exists( 'wpcom_vip_get_page_by_path' ) ) {
			$existing_page = wpcom_vip_get_page_by_title('Request Access', OBJECT, 'page');
		} else {
			$existing_page = get_page_by_path('request-access', OBJECT, 'page');   // phpcs:ignore
		}

		if ( !$existing_page ) {
			$page_data = array(
				'post_title'   => 'Request Access',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_name'    => 'request-access',
			);
			wp_insert_post($page_data);
		}

		add_rewrite_endpoint('request-access', EP_PAGES);

	}

	/**
	 * Assigning template if 'request-access' page is called.
	 *	@param string @template - consist with template path.
	 * 
	 * @return string
	 * @since 4.0
	 */
	public function cf_load_request_access_template($template) {
		global $wp_query;
		if (isset($wp_query->query_vars['name']) && 'request-access' === $wp_query->query_vars['name']) {
			return COMMENTING_BLOCK_DIR . 'admin/classes/copy-link-feature/page-request-access.php';
		}
		return $template;

	}

	/**
	 * Enqueue css and js for the request access page.
	 *
	 * @return string
	 * @since 4.0
	 */
	public function cf_enqueue_frontend_scripts(){
		global $wp_query;

		if( is_page('request-access') || is_page('Request Access') || (isset($wp_query->query_vars['name']) && 'request-access' === $wp_query->query_vars['name']) ){
			wp_enqueue_script( 'request-access-script', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/frontend-copy-link-feature.js', array(), wp_rand(), true );
			$script_data = array(
				'action_url' => admin_url('admin-ajax.php')
			);
			// Localize the script with the action URL
			wp_localize_script('request-access-script', 'requestAccess', $script_data);
			wp_enqueue_style( 'request-access-style', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/styles/frontStyle.build.min.css', array(), wp_rand(), false );
		}

	}

	// WordPress action to handle the form submission
	public function cf_request_access_form_action_handler() {

		if ( null === filter_input( INPUT_POST, 'request_access_nonce', FILTER_SANITIZE_SPECIAL_CHARS ) || !wp_verify_nonce(filter_input( INPUT_POST, 'request_access_nonce', FILTER_SANITIZE_SPECIAL_CHARS ), 'request_access_form_action')) {
			// Nonce verification failed, return an error response
			wp_send_json_error(['message' => 'Nonce verification failed.']); 
		}
		// Retrieve the posted data
		$post_id = ( null !== filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_SPECIAL_CHARS ) ) ? intval(filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_SPECIAL_CHARS )) : 0;
		$email   = ( null !== filter_input( INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS ) ) ? sanitize_email(filter_input( INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS )) : '';
		$token   = ( null !== filter_input( INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS ) ) ? filter_input( INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS ) : 0;

		if ( !$post_id || empty($email) || !is_email($email) || !$token ) {
			$response['status'] = 'failed';
			$response['msg']    = 'Invalid request';
		} else {
			$post_token   = get_post_meta($post_id, 'copy_link_post_token', true) ?? '';
			if ( $post_token && $post_token === $token ) {

				$users_requests = (array) get_post_meta($post_id, 'users_requests', true) ?? '';
				if ( isset($users_requests[$email]) ) {
					$response['status'] = 'failed';
					$response['msg']    = 'You have already requested.';
				} else {
					$users_requests[$email] = 'No';
					update_post_meta($post_id, 'users_requests', $users_requests);

					// Return a success message
					$response['status'] = 'success';
					$response['msg']    = 'Request sent';

					$author_id     = get_post_field( 'post_author', $post_id ) ?? '';
					$post_owner    = get_user_by( 'id', $author_id );
					$post_owner_name = ucfirst( $post_owner->display_name ) ?? '';
					$post_owner_email = $post_owner->user_email ?? '';
					$requestee_profile_image = 'https://0.gravatar.com/avatar?s=96&d=mm&r=g';
					$post_title = get_the_title( $post_id );
					$requestee_name = strtok( $email, '@' ) ?? $email;
					$post_link = add_query_arg( array( 
						'post' => $post_id, 
						'action' => 'edit', 
						'accept-request' => "true",
					), admin_url( 'post.php' ) );
					$mail_template = new Guest_Email_Template();
					$mail_template->access_request_mail_html( $post_owner_name, $post_owner_email, $requestee_profile_image, $post_title, $email, ucfirst($requestee_name), $post_link );
				}

			} else {
				$response['status'] = 'failed';
				$response['msg']    = 'invalid request';
			}

		}
		wp_send_json_success($response);
		wp_die();
	}

}

new Copy_Link_Feature();