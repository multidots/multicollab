<?php
/**
 * User guest functionality.
 *
 * @package multicollab
 */

/**
 * Class for User and role.
 */
class Guest_user_functions {

    private $namespace;

	/**
	 * Construct method.
	 */
	function __construct() {

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
		$this->namespace = 'cf/v2';
		add_action( 'rest_api_init', array( $this, 'create_rest_routes' ) );
		add_action( 'admin_init', array($this, 'restrict_admin_with_redirect'), 1 );
	}

    /**
	 * Defines Rest Routes
	 *
	 * @return void
	 */
	public function create_rest_routes() {

        register_rest_route(
			$this->namespace,
			'/usersList',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'usersList' ),
				'permission_callback' => array( $this, 'check_activity_permits' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/userInvitation',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'create_user_invitation' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		register_rest_route(
			$this->namespace,
			'/UserWithAccess',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'UserWithAccess' ),
				'permission_callback' => array( $this, 'check_activity_permits' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/updateCapabilities',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'updateCapabilities' ),
				'permission_callback' => array( $this, 'check_activity_permits' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/checkUserCapabilities',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'checkUserCapabilities' ),
				'permission_callback' => array( $this, 'check_activity_permits' ),
			)
		);
    }

    /**
	 * Ensuring Rest Permission
	 *
	 * @return void
	 */
	public function check_activity_permits() {
		return true;
	}

    /**
	 * Callback to send the rest response.
	 *
	 * @param array $data
	 * @return array
	 */
	public function usersList( $data ) {

		$auth_token      = $data->get_header( 'X-WP-Nonce' ) ?? '';

		if ( ! wp_verify_nonce( $auth_token, 'wp_rest' ) ) {
			$response = new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access it.' ),
				array( 'status' => rest_authorization_required_code() )
			);

			return rest_ensure_response( $response );
		}
		$term = $data->get_param( 'term' ) ?? [];
		$selectedUsers = $data->get_param( 'selectedUsers' ) ?? [];

		$post_id      = intval($data->get_param('post_id')) ?? '';
		$author_id    = get_post_field( 'post_author', $post_id ) ?? '';
		$post_owner   = get_user_by( 'id', $author_id );
		$user_logins  = [];
		$user_logins[] = $post_owner->user_login ?? [];

		$users = new WP_User_Query( array(
			'number' => 9999,
		) );
		$guest_users = $users->get_results();
		foreach ( $guest_users as $user ) {
            $guest_role_post_ids = get_user_meta($user->ID, 'guest_user_post_ids', true );
            if ( ! empty($guest_role_post_ids) && is_array($guest_role_post_ids) && in_array($post_id, $guest_role_post_ids, true) ) {
			    $user_logins[] = $user->user_login ?? '';
            }
			if ( ! empty($selectedUsers) && in_array($user->user_email, $selectedUsers, true) ) {
				$user_logins[] = $user->user_login ?? '';
			}
		}

		if ( isset( $term['term'] ) && $term['term'] ) {
			$search_string = esc_attr( trim( $term['term'] ) );
			$users = new WP_User_Query( array(
				'login__not_in' => $user_logins,
				'search'         => "*{$search_string}*",
				'search_columns' => array(
					'user_login',
					'user_nicename',
					'user_email',
					'user_url',
					'display_name',
				)
			) );
			$email_list   = array();
			$system_users = $users->get_results();
			if ( ! empty( $system_users ) ){
				foreach ( $system_users as $user ) {
					$commenting_block = new Commenting_block_Functions();
					$needToSortArray = $commenting_block->cf_get_reorder_user_role( $user->roles );
					$user->roles     = $needToSortArray;
					$role =  ucfirst(reset($user->roles)) ?? '';
					$email_list[] = array(
						'ID'                => $user->ID,
						'role'              => ucwords($role) ?? '',
						'display_name'      => ucwords($user->display_name),
						'full_name'         => ucwords($user->display_name),
						'first_name'        => ucwords($user->first_name),
						'user_email'        => $user->user_email,
						'avatar'            => get_avatar_url(
							$user->ID,
							array(
								'size' => '96',
							)
						),
						'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
						'edit_others_posts' => isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '',
					);
				}
				$response = $email_list;
			} else {
				$response = [];
			}
			return rest_ensure_response( $response );
			
		} else {
			$users = new WP_User_Query(
				array(
					'login__not_in' => $user_logins,
					'number'   => 9999
				)
			);
			$email_list   = array();
			$system_users = $users->get_results();
			foreach ( $system_users as $user ) {
				$commenting_block = new Commenting_block_Functions();
				$needToSortArray  = $commenting_block->cf_get_reorder_user_role( $user->roles );
				$user->roles      = $needToSortArray;
				$role             =  ucfirst(reset($user->roles)) ?? '';
				$email_list[] = array(
					'ID'                => $user->ID,
					'role'              => ucwords($role) ?? '',
					'display_name'      => ucwords($user->display_name),
					'full_name'         => ucwords($user->display_name),
					'first_name'        => ucwords($user->first_name),
					'user_email'        => $user->user_email,
					'avatar'            => get_avatar_url(
						$user->ID,
						array(
							'size' => '96',
						)
					),
					'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
					'edit_others_posts' => isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '',
				);
			}
			$response = $email_list;
			return rest_ensure_response( $response );
		}
        
	}

    /**
	 * User invitation mail.
	 *
	 * @param  mixed $data
	 * @return void
	 */
	public function create_user_invitation( $data ) {
		$auth_token    = $data->get_header( 'X-WP-Nonce' ) ?? '';

		if ( ! wp_verify_nonce( $auth_token, 'wp_rest' ) ) {
			$response = new WP_Error(
				'rest_forbidden',
				__( $auth_token ),
				array( 'status' => rest_authorization_required_code() )
			);

			return rest_ensure_response( $response );
		}

		$notices           = [];
		$all_users_names   = [];
		$user_id           = get_current_user_id();
		$inviter_user_id   = $user_id;

		$user_emails   = $data->get_param( 'userData' ) ?? [];
		$capabilities  = $data->get_param( 'capabilities' ) ?? '';
		$message       = $data->get_param( 'message' ) ?? '';
		$notifyCheck   = $data->get_param( 'notifyCheck' ) ?? false;
		$post_id       = intval($data->get_param('post_id')) ?? '';
		$guest_cap_role  = '';

		if('commenter' === $capabilities){
			$guest_cap_role  = 'Comment';
		} else if('viewer' === $capabilities){
			$guest_cap_role  = 'View';
		}

		if(!$notifyCheck){
			$message = '';
		}

		$inviter_info = get_userdata($inviter_user_id);
		if ( ! empty( $inviter_info ) ){
			$inviter_name  = ucfirst($inviter_info->display_name) ?? '';
			$inviter_email = $inviter_info->user_email ?? '';
			$inviter_profile_image = get_avatar_url( isset( $inviter_info->user_email ) ? $inviter_info->user_email : '' ) ?? '';
		}

		$post_image = get_the_post_thumbnail_url($post_id) ?? '';

		$last_edited_by = get_post_meta( $post_id, 'last_user_edited', true ) ?? $inviter_name;
		$last_edited_by = ucfirst($last_edited_by);

		$last_edited_timestamp = get_the_modified_date( 'U', $post_id ) ?? '';
		$current_time          = current_time('timestamp');
		$time_difference       = human_time_diff( $last_edited_timestamp, $current_time ) ?? '';
		$last_edited_on        = $time_difference.' ago'; 

		if ( ! empty( $user_emails ) ){
			foreach($user_emails as $user_email){
				if ( email_exists( $user_email ) ) {
					$existing_user = get_user_by( 'email', $user_email );
					
					$fname       = $existing_user->first_name ?? '';
					$user_id     =  $existing_user->ID ?? '';

					$post_access = (array)get_user_meta( $user_id, 'guest_user_post_ids', true) ?? [];

					array_push($post_access, $post_id);
					update_user_meta( $user_id, 'guest_user_post_ids', $post_access );

					$guest_role = get_user_meta($user_id, 'guest_user_post_ids_roles', true );
					if ( ! empty( $guest_role ) ) {
						if(!array_key_exists($post_id, $guest_role)){
							$guest_role[$post_id] = $capabilities;
							update_user_meta( $user_id, 'guest_user_post_ids_roles', $guest_role );
						}
					} else {
						$guest_user_post_ids_roles = [];
						$guest_user_post_ids_roles[$post_id] = $capabilities;
						update_user_meta( $user_id, 'guest_user_post_ids_roles', $guest_user_post_ids_roles );
					}

					$time     = time();
					$token    = wp_hash( $user_email . $time );

                    $guest_token = (array) get_user_meta( $user_id, 'multicollab_guest_token', true) ?? [];
					array_push($guest_token, $token);
					update_user_meta( $user_id, 'multicollab_guest_token', $guest_token );
					

					$multicollab_guest_token_timestamp = get_user_meta($user_id, 'multicollab_guest_token_timestamp', true );
					if(!empty($multicollab_guest_token_timestamp)){
						if(!array_key_exists($token, $multicollab_guest_token_timestamp)){
							$multicollab_guest_token_timestamp[$token][0] = time();
							$multicollab_guest_token_timestamp[$token][1] = '';
							update_user_meta( $user_id, 'multicollab_guest_token_timestamp', $multicollab_guest_token_timestamp );
						}
					} else {
						$multicollab_guest_token_timestamp = [];
						$multicollab_guest_token_timestamp[$token][0] = time();
						$multicollab_guest_token_timestamp[$token][1] = '';
						update_user_meta( $user_id, 'multicollab_guest_token_timestamp', $multicollab_guest_token_timestamp );
					}

					$login_url = site_url( 'wp-login.php', 'login' );
					
					$login_url = add_query_arg(
						[
							'user_id'  => $user_id,
							'username' => $existing_user->user_login ?? '',
							'post_id'  => $post_id,
							'token'    => $token,
						],
						$login_url
					);
					
					$token_with_postid = get_user_meta($user_id, 'multicollab_guest_token_with_post_id', true );
					if(!empty($token_with_postid)){
						if(!array_key_exists($post_id, $token_with_postid)){
							$token_with_postid[$post_id] = $token;
							update_user_meta( $user_id, 'multicollab_guest_token_with_post_id', $token_with_postid );
						}
					} else {
						$token_with_postid = [];
						$token_with_postid[$post_id] = $token;
						update_user_meta( $user_id, 'multicollab_guest_token_with_post_id', $token_with_postid );
					}

					$guest_login_url = get_user_meta($user_id, 'multicollab_guest_login_url', true );
					if(!empty($guest_login_url)){
						if(!array_key_exists($post_id, $guest_login_url)){
							$guest_login_url[$post_id] = $login_url;
							update_user_meta( $user_id, 'multicollab_guest_login_url', $guest_login_url );
						}
					} else {
						$guest_login_url = [];
						$guest_login_url[$post_id] = $login_url;
						update_user_meta( $user_id, 'multicollab_guest_login_url', $guest_login_url );
					}

					$mail_template = new Guest_Email_Template();
					$mail_template->invitation_mail_html( $inviter_name, $inviter_email, $inviter_profile_image, get_the_title($post_id), $post_image, $user_email, $last_edited_by, $last_edited_on, $login_url, $message, $guest_cap_role, get_post_permalink($post_id) );

				} else {
					$fname = strtok($user_email, '@') ?? $user_email;
					$user_login = str_replace( ' ', '', $fname . wp_generate_password( 3, false, false ) );
					$user_login = preg_replace( '/[^A-Za-z0-9\-]/', '', $user_login );
					$user_login = strtolower( $user_login );

					$time     = time();
					$password = wp_generate_password( 20, false );
					$token    = wp_hash( $password . $time );

					$new_user_id = wp_insert_user(
						[
							'user_login'   => $user_login,
							'user_pass'    => $password,
							'user_email'   => $user_email,
							'first_name'   => $fname,
							'display_name' => $fname,
							'role'         => 'guest',
						]
					);

					$post_access = [];
					array_push($post_access, $post_id);
					update_user_meta( $new_user_id, 'guest_user_post_ids', $post_access );

					$guest_user_post_ids_roles = [];
					$guest_user_post_ids_roles[$post_id] = $capabilities;
					update_user_meta( $new_user_id, 'guest_user_post_ids_roles', $guest_user_post_ids_roles );

                    $post_token = [];
                    array_push($post_token, $token);
					update_user_meta( $new_user_id, 'multicollab_guest_token', $post_token );

					$guest_user_post_ids_roles = [];
                    $guest_user_post_ids_roles[$post_id] = $token;
					update_user_meta( $new_user_id, 'multicollab_guest_token_with_post_id', $guest_user_post_ids_roles );

					$multicollab_guest_token_timestamp = [];
					$multicollab_guest_token_timestamp[$token][0] = time();
					$multicollab_guest_token_timestamp[$token][1] = '';
					update_user_meta( $new_user_id, 'multicollab_guest_token_timestamp', $multicollab_guest_token_timestamp );

					$login_url = site_url( 'wp-login.php', 'login' );

					$login_url = add_query_arg(
						[
							'user_id'  => $new_user_id,
							'username' => $user_login,
							'post_id'  => $post_id,
							'token'    => $token,
						],
						$login_url
					);

					$guest_login_url = [];
                    $guest_login_url[$post_id] = $login_url;
					update_user_meta( $new_user_id, 'multicollab_guest_login_url', $guest_login_url );

					map_meta_cap( 'edit_others_posts', $new_user_id, $post_id );
					map_meta_cap( 'edit_published_posts', $new_user_id, $post_id );
					map_meta_cap( 'edit_post', $new_user_id, $post_id );

					$mail_template = new Guest_Email_Template();
					$mail_template->invitation_mail_html( $inviter_name, $inviter_email, $inviter_profile_image, get_the_title($post_id), $post_image, $user_email, $last_edited_by, $last_edited_on, $login_url, $message, $guest_cap_role, get_post_permalink($post_id) );
				}
				$all_users_names[] = ucfirst($fname);
			}
			
			$all_users_names = join( ' and ', array_filter( array_merge( [ join( ', ', array_slice( $all_users_names, 0, -1 ) ) ], array_slice( $all_users_names, -1 ) ), 'strlen' ) );
			
			$notices = [
				'type' => 'success',
				'text' => $all_users_names
			];
		} else {
			$notices = [
				'type' => 'error',
			];
			$response['data'] = $notices;
			return rest_ensure_response( $response );
		}

		$response['data'] = $notices;
		return rest_ensure_response( $response );
	}

    /**
	 * Callback to send the rest response.
	 *
	 * @param array $data
	 * @return array
	 */
	public function UserWithAccess( $data ) {

		$auth_token      = $data->get_header( 'X-WP-Nonce' ) ?? '';

		if ( ! wp_verify_nonce( $auth_token, 'wp_rest' ) ) {
			$response = new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access it.' ),
				array( 'status' => rest_authorization_required_code() )
			);

			return rest_ensure_response( $response );
		}
		$post_id    = intval($data->get_param('post_id')) ?? '';
        $author_id  = get_post_field( 'post_author', $post_id ) ?? '';
		$post_owner = get_user_by( 'id', $author_id );
		$user_login = $post_owner->user_login ?? '';

		$commenting_block  = new Commenting_block_Functions();
		$needToSortArray   = $commenting_block->cf_get_reorder_user_role( $post_owner->roles );
		$post_owner->roles = $needToSortArray;
		$role              =  ucfirst(reset($post_owner->roles)) ?? '';
		$userdata          = array();
		$userdata['owner'] = [
			'email' => $post_owner->user_email ?? '',
			'name' =>$post_owner->display_name .' ('. $role .') ' ?? '',
			'avatarUrl' => get_avatar_url( isset( $post_owner->user_email ) ? $post_owner->user_email : '' ),
		];

		$users = new WP_User_Query( array(
			'login__not_in' => array($user_login ),
			'number'        => 9999,
		) );
		$guest_users = $users->get_results();

		if(!empty($guest_users)){
			foreach ( $guest_users as $user ) {
				$commenting_block = new Commenting_block_Functions();
				$needToSortArray  = $commenting_block->cf_get_reorder_user_role( $user->roles );
				$user->roles      = $needToSortArray;
				$role             =  ucfirst(reset($user->roles)) ?? '';

				$guest_role = get_user_meta($user->ID, 'guest_user_post_ids_roles', true );
				if(!empty($guest_role)){
					$capability = $guest_role[$post_id] ?? 'Viewer';
				}
				
				$guest_login_url = get_user_meta($user->ID, 'multicollab_guest_login_url', true ) ?? '';
				if( !empty($guest_login_url) && isset($guest_login_url[$post_id]) ){
					$login_url = $guest_login_url[$post_id];
				} else {
					$login_url = '';
				}
                $guest_role_post_ids = get_user_meta($user->ID, 'guest_user_post_ids', true );
                if ( ! empty( $guest_role_post_ids ) && is_array( $guest_role_post_ids ) && in_array( $post_id, $guest_role_post_ids, true ) ) {
                    $userdata['accessed_people'][] = [
                        'email' => $user->user_email ?? '',
                        'name' => $user->display_name .' ('. $role .') ' ?? '',
                        'avatarUrl' => get_avatar_url( isset( $user->user_email ) ? $user->user_email : '' ),
                        'capability' => $capability ?? '',
                        'user_id' => $user->ID ?? '',
						'guest_login_url' => $login_url ?? '',
                    ];
                }
				
			}
		}
		return rest_ensure_response( $userdata );
	}

    /**
	 * Callback to send the rest response.
	 *
	 * @param array $data
	 * @return array
	 */
	public function updateCapabilities( $data ) {

		$auth_token      = $data->get_header( 'X-WP-Nonce' ) ?? '';

		if ( ! wp_verify_nonce( $auth_token, 'wp_rest' ) ) {
			$response = new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access it.' ),
				array( 'status' => rest_authorization_required_code() )
			);

			return rest_ensure_response( $response );
		}


		$post_id      = intval($data->get_param('post_id')) ?? '';
		$users        = json_decode($data->get_param('users')) ?? [];

		$response   = [];
		if(!empty($users)){
			foreach($users as $user) {
				if(!empty($user)){
					$user_id      = $user->user_id ?? '';
					$user_email   = $user->email ?? '';
					$capabilities = $user->capabilities ?? '';
		
					$guest_role        = get_user_meta($user_id, 'guest_user_post_ids_roles', true );
					$post_access       = get_user_meta( $user_id, 'guest_user_post_ids', true);
					$guest_login_url   = get_user_meta( $user_id, 'multicollab_guest_login_url', true);
					$token_with_postid = get_user_meta( $user_id, 'multicollab_guest_token_with_post_id', true);

					if(!empty($guest_role)){
						if(array_key_exists($post_id, $guest_role)){
							if('remove_access' === $capabilities){
								unset($guest_role[$post_id]);
								update_user_meta( $user_id, 'guest_user_post_ids_roles', $guest_role );
			
								$key = array_search($post_id, $post_access);
								unset($post_access[$key]);
								update_user_meta( $user_id, 'guest_user_post_ids', $post_access );

								if(!empty($guest_login_url)){
									unset($guest_login_url[$post_id]);
									update_user_meta( $user_id, 'multicollab_guest_login_url', $guest_login_url );
								}
								if(!empty($token_with_postid)){
									unset($token_with_postid[$post_id]);
									update_user_meta( $user_id, 'multicollab_guest_token_with_post_id', $token_with_postid );
								}

								$response[] = array('status' => 'Removed Successfully', 'user_email' => $user_email);
							} else {
								$guest_role[$post_id] = $capabilities;
								update_user_meta( $user_id, 'guest_user_post_ids_roles', $guest_role );
								$response[] = array('status' => 'Updated Successfully', 'user_email' => $user_email);
							}
						}
					}
				}
			}
		}
		return rest_ensure_response( $response );
	}

    /**
	 * Callback to send the rest response.
	 *
	 * @param array $data
	 * @return array
	 */
	public function checkUserCapabilities( $data ) {

		$auth_token      = $data->get_header( 'X-WP-Nonce' ) ?? '';

		if ( ! wp_verify_nonce( $auth_token, 'wp_rest' ) ) {
			$response = new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access it.' ),
				array( 'status' => rest_authorization_required_code() )
			);

			return rest_ensure_response( $response );
		}


		$post_id  = intval($data->get_param('post_id')) ?? '';
		$user_id  = $data->get_param('user_id') ?? get_current_user_id();
		
		$user = [];
		if($user_id){
			$user = new WP_User( $user_id );
		}
		
		$response   = [];

		if ( ! empty( $user ) ) {
			
			$guest_role = get_user_meta($user_id, 'guest_user_post_ids_roles', true );
			if(in_array( 'administrator', (array) $user->roles )) {
				$response = array('status' => 'No require to check.', 'canIgnore' => 'yes', 'capability' => '');
				return rest_ensure_response( $response );
			}
			if(!empty($guest_role)){
				if( $post_id && array_key_exists($post_id, $guest_role) ){
					$response = array('status' => 'Check required.', 'canIgnore' => 'no', 'capability' => $guest_role[$post_id]);

				} else if ( in_array( 'guest', (array) $user->roles ) ) {
					$response = array('status' => 'Not found', 'canIgnore' => 'no', 'capability' => 'not_allowed');
				} else {
                    $response = array('status' => 'No require to check.', 'canIgnore' => 'yes', 'capability' => '');
                }
			} else {
				if ( in_array( 'guest', (array) $user->roles ) ) {
					$response = array('status' => 'Not found', 'canIgnore' => 'no', 'capability' => 'not_allowed');
				} else {
					$response = array('status' => 'No require to check.', 'canIgnore' => 'yes', 'capability' => '');
				}
			}
		}
		
		return rest_ensure_response( $response );
	}

	/**
	 * To disable the editor + sign button of gutenberg.
	 * 
	 * @param array $editor_settings - gutenberg settings.
	 * 
	 * @return array
	 */
	public function disable_block_editor_settings( $editor_settings ) {
		$editor_settings["allowedBlockTypes"] = false;
		return $editor_settings;
	}

	/**
	 * To restrict user from backend if user unauthorised.
	 */
	public function restrict_admin_with_redirect() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
        if( wp_doing_ajax() ){
            return;
        }
		global $post;
		$post_id = (isset($_GET['post'])) ? absint($_GET['post']) : get_the_ID();
		if(!$post_id){
			$post_id = '';
		}
		$user_id = get_current_user_id();

		$user = [];
		if($user_id){
			$user = new WP_User( $user_id );
		}

		if ( ! empty( $user ) ) {
			if(in_array( 'administrator', (array) $user->roles )) {
				return;
			}
			$guest_role = get_user_meta($user_id, 'guest_user_post_ids_roles', true ) ?? [];
			if(!empty($guest_role)){  // Check user's capability according to the user meta value.
				if( $post_id && array_key_exists($post_id, $guest_role)){  // if meta value found than user can access the post.
					add_filter( 'block_editor_settings_all', array($this, 'disable_block_editor_settings'), 10 ); // To disable gutenberg + button.
					if( 'viewer' === $guest_role[$post_id] ){
                       if( 'draft' !== get_post_status( $post_id ) ){
                        wp_die( esc_html__('You only can view draft post.', 'multicollab') );
                       }
					} else if( 'editor' === $guest_role[$post_id] ){ // For editor.

					} else { // For commentor.

					}
				} elseif ( in_array( 'guest', (array) $user->roles, true ) || ( isset( $guest_role[$post_id] ) && 'coeditor' === $guest_role[$post_id] ) ) {  // if user is guest then need to redirect
					wp_die( 'You are not allowed to access this page.' );
				}
			} else {
				if ( in_array( 'guest', (array) $user->roles ) ) {  // if meta value is not found but user is guest then need to redirect.
					wp_die( 'You are not allowed to access this post.' );
				}
			}
		}
	}
}

new Guest_user_functions();