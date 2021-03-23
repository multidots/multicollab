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
		$userData         = array();
		$prepareDataTable = array();

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
			ORDER BY meta_id DESC
			LIMIT %d OFFSET %d
			",
			$current_post_id, $like, $limit, $offset
		), ARRAY_A );

		$total_comments = 0;
		foreach ( $results as $v ) {
			$dataid            = str_replace( '_', '', $v['meta_key'] );
			$v                 = maybe_unserialize( $v['meta_value'] );
			$comments          = $v['comments'];
			$commented_on_text = $v['commentedOnText'];
			$resolved          = isset( $v['resolved'] ) ? $v['resolved'] : 'false';

			if ( 'true' === $resolved ) {

				$udata = isset( $v['resolved_by'] ) ? $v['resolved_by'] : 0;
				if ( ! array_key_exists( $udata, $userData ) ) {
					$user_info = get_userdata( $udata );

					$userData[ $udata ]['username']   = $username = $user_info->display_name;
					$userData[ $udata ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
				} else {
					$username    = $userData[ $udata ]['username'];
					$profile_url = $userData[ $udata ]['profileURL'];
				}

				$timestamp = isset( $v['resolved_timestamp'] ) ? (int) $v['resolved_timestamp'] : '';
				if ( ! empty( $timestamp ) ) {
					$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
				}

				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dataid']            = $dataid;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['commented_on_text'] = $commented_on_text;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['username']          = $username;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['profileURL']        = $profile_url;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dtTime']            = $dtTime;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['status']            = __( 'resolved thread', 'content-collaboration-inline-commenting' );
			}

			$comment_count = 0;
			foreach ( $comments as $timestamp => $c ) {

				$cstatus        = 0 === $comment_count ? __( 'commented', 'content-collaboration-inline-commenting' ) : __( 'replied', 'content-collaboration-inline-commenting' );
				$cstatus        .= __( ' on', 'content-collaboration-inline-commenting' );
				$comment_status = isset( $c['status'] ) ? $c['status'] : '';
				$cstatus        = 'deleted' === $comment_status ? __( 'deleted comment of', 'content-collaboration-inline-commenting' ) : $cstatus;

				// Stop displaying history of comments in draft mode.
				if ( 'draft' === $comment_status || 'permanent_draft' === $comment_status ) {
					continue;
				}

				$udata = $c['userData'];

				if ( ! array_key_exists( $udata, $userData ) ) {
					$user_info = get_userdata( $udata );

					$userData[ $udata ]['username']   = $username = $user_info->display_name;
					$userData[ $udata ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
				} else {
					$username    = $userData[ $udata ]['username'];
					$profile_url = $userData[ $udata ]['profileURL'];
				}

				$thread = $c['thread'];
				if ( ! empty( $timestamp ) ) {
					$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
				}

				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dataid']            = $dataid;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['commented_on_text'] = $commented_on_text;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['username']          = $username;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['profileURL']        = $profile_url;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['thread']            = $thread;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dtTime']            = $dtTime;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['status']            = $cstatus;
				$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['resolved']          = $resolved;
				$comment_count ++;
				$total_comments ++;
			}
		}

        krsort( $prepareDataTable, SORT_NUMERIC );
		$response = [
			'comments' => $prepareDataTable,
			'total' => $total
		];
		return rest_ensure_response( $response );
    }

}
new Commenting_Block_Rest_Routes();