<?php
/**
 * REST API endpoints functionality of the plugin.
 *
 * @link       #
 * @since      1.3.0
 *
 * @package    content-collaboration-inline-commenting
 */

class Commenting_Block_Rest_Routes {

    private $namespace;

    /**
     * Constructor
     */
    public function __construct() {
        $this->namespace = 'cf/v2';
        add_action( 'rest_api_init', [ $this, 'create_rest_routes' ] );
    }

    /**
     * Defines Rest Routes
     *
     * @return void
     */
    public function create_rest_routes() {
        register_rest_route( $this->namespace, '/activities', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_activities' ],
            'permission_callback' => [ $this, 'check_activity_permits' ],
        ] );
    }

	public function check_activity_permits() {
		return true;
	}

    public function get_activities( $data ) {
       	$current_post_id = intval( $data->get_param( 'postID' ) );
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		// Modify Query
		global $wpdb;
		$like   = $wpdb->esc_like( '_el' ) . '%';
		$limit  = intval( $data->get_param( 'limit' ) );
		$offset = intval( $data->get_param( 'offset' ) );

		$total = $wpdb->get_var( $wpdb->prepare("
			SELECT COUNT(meta_id) as total
			FROM {$wpdb->prefix}postmeta
			WHERE post_id=%d AND meta_key LIKE %s
			ORDER BY meta_id DESC
			",
			$current_post_id, $like,
		) );

		$results = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM {$wpdb->prefix}postmeta
			WHERE post_id=%d AND meta_key LIKE %s
			",
			$current_post_id, $like
		), ARRAY_A );

		$threads = [];
		foreach( $results as $row ) {
			$cmnts = [];
			$elID = str_replace( '_', '', $row['meta_key'] );
			$comments = maybe_unserialize( $row['meta_value'] );
			foreach( $comments['comments'] as $timestamp => $comment ) {
				$user_info = get_userdata( $comment['userData'] );
				$cmnts[] = [
					'status' => $comment['status'],
					'timestamp' => gmdate( $time_format . ' ' . $date_format, intval( $timestamp ) ),
					'userData' => [
						'username' => $user_info->display_name,
						'avatarUrl' => get_avatar_url( $user_info->user_email ),
					],
					'thread' => $comment['thread']
				];
			}

			$resolved_by = [];
			if( 'true' === $comments['resolved'] ) {
				if( isset( $comments['resolved_by'] ) ) {
					$resolved_user = get_userdata( $comments['resolved_by'] );
					$resolved_by = [
						'username' => $resolved_user->display_name,
						'avatarUrl' => get_avatar_url( $resolved_user->user_email ),
					];
				}
			}

			$threads[] = [
				'elID'              => $elID,
				'activities'        => $cmnts,
				'selectedText'      => $comments['commentedOnText'],
				'resolved'          => isset( $comments['resolved'] ) ? $comments['resolved'] : 'false',
				'resolvedTimestamp' => isset( $comments['resolved_timestamp'] ) ? gmdate( $time_format . ' ' . $date_format, intval( $comments['resolved_timestamp'] ) ): '',
				'resolvedBy'        => $resolved_by,
				'updatedAt'			=> $comments['updated_at'],
			];
		}

		array_multisort( array_column( $threads, 'updatedAt' ), SORT_DESC, $threads );

		$response = [
			'threads' => $threads,
			'total'   => $total,
		];
		return rest_ensure_response( $response );
    }

}
new Commenting_Block_Rest_Routes();