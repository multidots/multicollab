<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * REST API endpoints functionality of the plugin.
 *
 * @link       #
 * @since      1.3.0
 *
 * @package    content-collaboration-inline-commenting
 */

class Commenting_Block_Rest_Routes extends Commenting_block_Functions {

	private $namespace;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->namespace = 'cf/v2';
		add_action( 'rest_api_init', array( $this, 'create_rest_routes' ) );
	}

	/**
	 * Defines Rest Routes
	 *
	 * @return void
	 */
	public function create_rest_routes() {
		register_rest_route(
			$this->namespace,
			'/activities',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_activities' ),
				'permission_callback' => array( $this, 'check_activity_permits' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/getuserdata',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'getuserdata' ),
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
	public function get_activities( $data ) {

        $current_post_id =intval($data->get_param('postID'));  //phpcs:ignore
		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$timestamp       = current_time( 'timestamp' );
		$cf_options      = get_option( 'cf_permissions' );

		// Modify Query
		global $wpdb;
        global $current_user; //phpcs:ignore
		$user_id = apply_filters( 'determine_current_user', false );
		wp_set_current_user( $user_id );
		$current_users   = get_userdata( $user_id );
		$hide_suggestion = isset( $cf_options[ $current_users->roles[0] ]['hide_suggestion'] ) ? $cf_options[ $current_users->roles[0] ]['hide_suggestion'] : '';
		$hide_comment    = isset( $cf_options[ $current_users->roles[0] ]['hide_comment'] ) ? $cf_options[ $current_users->roles[0] ]['hide_comment'] : '';

        $like            = $wpdb->esc_like( '_el' ) . '%';
		$c_reg_exp       = $wpdb->esc_like( '_el[0-9]' );
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id=%d AND meta_key LIKE %s AND meta_key REGEXP %s", $current_post_id, $like, $c_reg_exp), ARRAY_A); // phpcs:ignore

		$threads = array();

		foreach ( $results as $row ) {
			$cmnts = array();

			// passing the last activity parameters in thread for displaying in activity center last activity.
			$lastpostid     = $row['post_id'];
			$editedLastUser = get_post_meta( $lastpostid, 'last_user_edited', true );

			// Get post default author name.
			$author_id     = get_post_field( 'post_author', $lastpostid );
			$defaultAuthor = get_the_author_meta( 'nickname', $author_id );

			$lasteditedtime = get_the_modified_time( 'U', $lastpostid );
			$usr_login_name = get_user_by( 'login', $editedLastUser );
			if ( ( get_user_by( 'login', $editedLastUser ) ) !== false ) {
				$lasteditedUsersID = isset( $usr_login_name ) ? (int) $usr_login_name->ID : 0;
			} else {
				$lasteditedUsersID = null;
				$checkLastEditedUser = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE display_name = %s", $editedLastUser)); //phpcs:ignore
				if(!empty($checkLastEditedUser)){
					$lasteditedUsersID = $checkLastEditedUser->ID;
					$usr_login_name = get_user_by( 'ID', $lasteditedUsersID );
				}
			}
			$lasteditedUsersUrl = get_avatar_url( $lasteditedUsersID );
			$elID               = str_replace( '_', '', $row['meta_key'] );
			$comments           = maybe_unserialize( $row['meta_value'] );

			foreach ( $comments['comments'] as $timestamp => $comment ) {

				$user_info      = get_userdata( isset( $comment['userData'] ) ? $comment['userData'] : '' );

				// Add break if $user_info->roles is giving null value.
				if( empty( $user_info->roles ) ) {
					break;
				}

				$comment_status = isset( $comment['status'] ) ? $comment['status'] : '';
				if ( 'draft' !== $comment_status && 'permanent_draft' !== $comment_status ) {
					$cmnts[] = array(
						'id'              => $timestamp,
						'status'          => isset( $comment_status ) ? $comment_status : '',
						'created_at'      => isset( $comment['created_at'] ) ? $comment['created_at'] : '',
						'timestamp'       => gmdate( $time_format . ' ' . $date_format, intval( $timestamp ) ),
						'editedTime'      => isset( $comment['editedTime'] ) ? $comment['editedTime'] : '',
						'editedTimestamp' => isset( $comment['editedTimestamp'] ) ? $comment['editedTimestamp'] : '',
						'userData'        => array(
							'id'        => isset( $user_info->ID ) ? intval( $user_info->ID ) : 0,
							'username'  => isset( $user_info->display_name ) ? $user_info->display_name : '',
							'avatarUrl' => get_avatar_url( isset( $user_info->user_email ) ? $user_info->user_email : '' ),
							'userRole'  => isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '',
						),
						'thread'          => isset( $comment['thread'] ) ? $comment['thread'] : '',
						'attachmentText'  => isset( $comment['attachmentText'] ) ? $comment['attachmentText'] : '',
					);
				}
			}

			$resolved_by = array();
			if ( isset( $comments['resolved'] ) && 'true' === $comments['resolved'] ) {
				if ( isset( $comments['resolved_by'] ) ) {
					$resolved_user = get_userdata( $comments['resolved_by'] );
					$resolved_by   = array(
						'username'  => $resolved_user->display_name,
						'avatarUrl' => get_avatar_url( $resolved_user->user_email ),
					);
				}
			}

			$assigned_user = array();
			if ( isset( $comments['assigned_to'] ) ) {
				$assigned_user_info = get_userdata( $comments['assigned_to'] );
				$assigned_user      = array(
                    'username' => ($user_id == $assigned_user_info->ID)? 'You' : $assigned_user_info->display_name, //phpcs:ignore
					'email'    => $assigned_user_info->user_email,
				);
			}

			// Get post default author name.
			$author_id         = get_post_field( 'post_author', $current_post_id );
			$defaultAuthor     = get_the_author_meta( 'display_name', $author_id );
			$defaultAuthorLink = get_avatar_url( get_the_author_meta( 'user_email', $author_id ) );
			$Defaultusers      = get_userdata( $author_id );

			if ( ! empty( $cmnts ) ) {
				$threads[] = array(
					'elID'              => $elID,
					'activities'        => $cmnts,
					'selectedText'      => $comments['commentedOnText'],
					'resolved'          => isset( $comments['resolved'] ) ? $comments['resolved'] : 'false',
					'resolvedTimestamp' => isset( $comments['resolved_timestamp'] ) ? $comments['resolved_timestamp'] : '',
					'resolvedBy'        => $resolved_by,
					'updatedAt'         => isset( $comments['updated_at'] ) ? $comments['updated_at'] : '',
					'assignedTo'        => $assigned_user,
					'blockType'         => isset( $comments['blockType'] ) ? $comments['blockType'] : '',
					'lastUser'          => $editedLastUser,
					'defaultAuthor'     => $defaultAuthor,
					'defaultAuthorLink' => $defaultAuthorLink,
					'defaultUserRole'   => $Defaultusers->roles[0],
					'lastEditedTime'    => $lasteditedtime,
					'lastUsersUrl'      => $lasteditedUsersUrl,
					'type'              => 'el',
				);
				
				if ( '1' !== $hide_comment ) {
					$threads = $threads;
				} else {
					$threads = array();
				}
			}
		}

		$real_time_mode = get_post_meta( $current_post_id, '_is_real_time_mode', true );
		if ( '1' === $real_time_mode ) { 
			$collaborators = $this->get_collaborator_activities( $current_post_id );
			if ( empty( $threads ) && ! empty( $collaborators ) ) {
				$threads = $collaborators;
			} elseif ( ! empty( $collaborators ) && ! empty( $threads ) ) {
				$threads = array_merge( $collaborators, $threads );
			}
		}

		if ( ! metadata_exists( 'post', $current_post_id, '_autodraft_ids' ) ) {
			// create new meta if meta key doesn't exists
			add_post_meta( $current_post_id, '_autodraft_ids', '' );
		}
		array_multisort( array_column( $threads, 'updatedAt' ), SORT_DESC, $threads );

		$response = array(
			'threads' => $threads,
		);

		return rest_ensure_response( $response );
	}

	 /**
	  * Return collaborator activities array
	  *
	  * @param integer $data
	  * @return array
	  */
	public function get_collaborator_activities( $postId ) {
		$current_post_id = intval( $postId );
		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$timestamp       = current_time( 'timestamp' );

		$collaboratorHistory = get_post_meta( $current_post_id, '_realtime_collaborators_activity', true );
		if ( $collaboratorHistory === '' ) {
			return;
		}
		
		
		$collaboratorHistory = json_decode( $collaboratorHistory );
		$collaboratorHistory = (array)$collaboratorHistory;
		$collaboratorHistory = array_filter($collaboratorHistory, function($value) {
			return property_exists( $value, 'timestamp' );
		});		

		$collaboratorHistory = array_filter($collaboratorHistory, function($collab) {
			return $collab->type === 'Joined';
		});

		$collaboratorHistory = array_values($collaboratorHistory);
		$threads = array();
		if ( ! empty( $collaboratorHistory ) ) {
			foreach ( $collaboratorHistory as $Id => $collaborator ) {
				$cmnts = array();

				$suggeestionDetail = $collaborator;
				$suggeestionDetail = (array) $suggeestionDetail;
				$elID  = 'collaborator-' . $suggeestionDetail['userId'];
				$user_info = get_userdata( $suggeestionDetail['userId'] );
				$timestamp = $suggeestionDetail['timestamp'];
				$cmnts[]   = array(
					'id'        => $timestamp,
					'status'    => 'publish',
					'timestamp' => isset( $suggeestionDetail['timestamp'] ) ? $suggeestionDetail['timestamp'] : '',
					'userData'  => array(
						'id'        => isset( $user_info->ID ) ? intval( $user_info->ID ) : 0,
						'username'  => isset( $user_info->display_name ) ? $user_info->display_name : '',
						'avatarUrl' => get_avatar_url( isset( $user_info->user_email ) ? $user_info->user_email : '' ),
						'userRole'  => isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '',
					),
					'thread'    => '',
				);

				if ( ! empty( $cmnts ) ) {
					$threads[] = array(
						'elID'              => $elID,
						'activities'        => $cmnts,
						'updatedAt'					=> $timestamp,
						'resolved'       		=> 'true',
						'selectedText'			=> 'Joined as collaborator.',
						'assignedTo'        => '',
					);
				}
			}
		}

		return $threads;
	}

	// custom rest end point to get userdata by user id
	public function getuserdata( $data ) {
        $userid = intval($data->get_param('userid'));  //phpcs:ignore

		if ( ! $userid || $userid === '' ) {
			return null;
		}

		$user_info = get_userdata( $userid );
		if ( ! empty( $user_info ) ) {

			$userdata = array(
				'id'        => isset( $user_info->ID ) ? intval( $user_info->ID ) : 0,
				'username'  => isset( $user_info->display_name ) ? $user_info->display_name : '',
				'avatarUrl' => get_avatar_url( isset( $user_info->user_email ) ? $user_info->user_email : '' ),
				'userRole'  => isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '',
			);

			return rest_ensure_response( $userdata );
		}

		return null;
	}
}
new Commenting_Block_Rest_Routes();
