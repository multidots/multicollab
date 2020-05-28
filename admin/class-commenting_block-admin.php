<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Commenting_block
 * @subpackage Commenting_block/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Commenting_block
 * @subpackage Commenting_block/admin
 * @author     multidots <janki.moradiya@multidots.com>
 */
class Commenting_block_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'post_updated', array( $this, 'mdgcf_post_status_changes' ), 10, 3 );
	}

	public function mdgcf_post_status_changes( $post_ID, $post, $update ) {

		// Publish drafts from the 'current_drafts' stack.
		$current_drafts = get_post_meta( $post_ID, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		// Mark Resolved Threads.
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$resolved_drafts = $current_drafts['resolved'];

			foreach ( $resolved_drafts as $el ) {
				$prev_state             = get_post_meta( $post_ID, $el, true );
				$prev_state             = maybe_unserialize( $prev_state );
				$prev_state['resolved'] = 'true';
				update_post_meta( $post_ID, $el, $prev_state );

				// Send Email.
				$comments = get_post_meta( $post_ID, "$el" );
				$comments = maybe_unserialize( $comments );
				$comments = $comments['comments'];

				$headers = array( 'Content-Type: text/html; charset=UTF-8' );

				$html = 'Hi Admin,<br><br>The following comment has been resolved:<br><br>';

				foreach ( $comments as $timestamp => $c ) {

					foreach ( $c as $arr ) {

						$user_info   = get_userdata( $arr['userData'] );
						$username    = $user_info->display_name;
						$profile_url = get_avatar_url( $user_info->user_email );
						$dtTime      = date( $time_format . ' ' . $date_format, $timestamp );
						$comment     = $arr['thread'];

						$html .= "<div className='comment-header'>
						          <div className='avtar'><img src='$profile_url' alt='avatar' /></div>
						          <div className='commenter-name-time'>
						            <div className='commenter-name'>$username</div>
						            <div className='comment-time'>$date</div>
						            <div className='comment'>Comment: $comment</div>
						          </div>
					          </div>";

						$html .= ' <br> ';
					}
					$html .= ' <br> ';
				}

				$html .= "<br>Thank you!";

				wp_mail( 'admin@gmail.com', 'NABPilot: Comment Resolved', $html, $headers );
			}
		}

		// Publish New Comments.
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$new_drafts = $current_drafts['comments'];
			foreach ( $new_drafts as $el => $drafts ) {
				$prev_state = get_post_meta( $post_ID, $el, true );
				$prev_state = maybe_unserialize( $prev_state );
				foreach ( $drafts as $d ) {
					$prev_state['comments'][ $d ]['status'] = 'publish';
				}
				update_post_meta( $post_ID, $el, $prev_state );
			}
		}

		// Publish Edited Comments.
		if ( isset( $current_drafts['edited'] ) && 0 !== count( $current_drafts['edited'] ) ) {
			$edited_drafts = $current_drafts['edited'];

			foreach ( $edited_drafts as $el => $timestamps ) {
				$prev_state = get_post_meta( $post_ID, $el, true );
				$prev_state = maybe_unserialize( $prev_state );

				foreach ( $timestamps as $t ) {

					$edited_draft = $prev_state['comments'][ $t ]['draft_edits']['thread'];
					if ( ! empty( $edited_draft ) ) {
						$prev_state['comments'][ $t ]['thread'] = $edited_draft;
					}

					// Change status to publish.
					$prev_state['comments'][ $t ]['status'] = 'publish';

					// Remove comment from edited_draft.
					unset( $prev_state['comments'][ $t ]['draft_edits']['thread'] );

				}
				update_post_meta( $post_ID, $el, $prev_state );
			}
		}

		// Publish Deleted Comments. (i.e. finally delete them.)
		if ( isset( $current_drafts['deleted'] ) && 0 !== count( $current_drafts['deleted'] ) ) {
			$deleted_drafts = $current_drafts['deleted'];

			foreach ( $deleted_drafts as $el => $timestamps ) {
				$prev_state = get_post_meta( $post_ID, $el, true );
				$prev_state = maybe_unserialize( $prev_state );

				foreach ( $timestamps as $t ) {
					unset( $prev_state['comments'][ $t ] );
				}
				update_post_meta( $post_ID, $el, $prev_state );
			}
		}

		// Flush Current Drafts Stack.
		update_post_meta( $post_ID, 'current_drafts', '' );

		// New Comments from past should be moved to'permanent_drafts'.
		$permanent_drafts = get_post_meta( $post_ID, 'permanent_drafts', true );
		$permanent_drafts = maybe_unserialize( $permanent_drafts );
		if ( isset( $permanent_drafts['comments'] ) && 0 !== count( $permanent_drafts['comments'] ) ) {
			$permanent_drafts = $permanent_drafts['comments'];
			foreach ( $permanent_drafts as $el => $drafts ) {
				$prev_state = get_post_meta( $post_ID, $el, true );
				$prev_state = maybe_unserialize( $prev_state );
				foreach ( $drafts as $d ) {
					$prev_state['comments'][ $d ]['status'] = 'permanent_draft';
				}
				update_post_meta( $post_ID, $el, $prev_state );
			}
		}

		// Flush Permanent Drafts Stack.
		update_post_meta( $post_ID, 'permanent_drafts', '' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Commenting_block_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Commenting_block_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/commenting_block-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Commenting_block_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Commenting_block_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/commenting_block-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'suggestion-block', plugin_dir_url( __FILE__ ) . 'js/blockJS/block.build.js', array(
			'jquery',
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-annotations',
			'wp-annotations',
			'jquery-ui-datepicker',
			'wp-api-fetch',
			'wp-plugins',
			'wp-edit-post',
		), $this->version, true );

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

	}

	public function my_action() {

		$commentList     = str_replace( "\\", "", $_POST['commentList'] );
		$commentList     = json_decode( $commentList, true );
		$current_post_id = $_POST['currentPostID'];
		$arr             = array();

		$commentList = end( $commentList );
		$metaId      = $_POST['metaId'];

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$timestamp   = current_time( 'timestamp' );

		$dtTime = date( $time_format . ' ' . $date_format, $timestamp );

		$commentListOld  = get_post_meta( $current_post_id, $metaId, true );
		$superCareerData = maybe_unserialize( $commentListOld );

		$arr['status']   = 'draft';//$post_status;
		$arr['userData'] = get_current_user_id();
		//$arr['dtTime']   = $timestamp;
		$arr['thread'] = $commentList['thread'];

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		} else {
			$current_drafts['comments'] = array();

			$current_drafts['comments'][ $metaId ][] = $timestamp;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		if ( isset( $superCareerData['comments'] ) && 0 !== count( $superCareerData['comments'] ) ) {
			$superCareerData['comments'][ $timestamp ] = $arr;
			//array_push( $superCareerData['comments'], $arr );
		} else {
			$superCareerData                           = array();
			$superCareerData['comments'][ $timestamp ] = $arr;
			$superCareerData['commentedOnText']        = $commentList['commentedOnText'];
			$superCareerData['value']                  = $commentList['value'];
			$superCareerData['onChange']               = $commentList['onChange'];

			update_post_meta( $current_post_id, 'th' . $metaId, get_current_user_id() );
		}
		update_post_meta( $current_post_id, $metaId, $superCareerData );

		echo json_encode( array( 'dtTime' => $dtTime, 'timestamp' => $timestamp ) );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function mdgcf_comments_history() {
		global $wpdb;
		$limit           = $_POST['limit'];
		$current_post_id = $_POST['currentPostID'];

		$all_meta         = get_post_meta( $current_post_id );
		$userData         = array();
		$prepareDataTable = array();

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		foreach ( $all_meta as $dataid => $v ) {
			if ( strpos( $dataid, '_el' ) === 0 ) {
				$v        = maybe_unserialize( $v[0] );
				$comments = $v['comments'];

				$commented_on_text = $v['commentedOnText'];

				$comment_count = 0;
				foreach ( $comments as $timestamp => $c ) {
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
					$dtTime = date( $time_format . ' ' . $date_format, $timestamp );
					//$dtTime    = $c['dtTime'];
					//$timestamp = strtotime( $dtTime );
					$dataid = str_replace( '_', '', $dataid );

					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dataid']            = $dataid;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['commented_on_text'] = $commented_on_text;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['username']          = $username;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['profileURL']        = $profile_url;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['thread']            = $thread;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dtTime']            = $dtTime;
					$comment_count ++;
				}
			}
		}

		krsort( $prepareDataTable, SORT_NUMERIC );

		$count = 0;
		$data  = array();
		$html  = '<div id="history-popup-insder">';

		foreach ( $prepareDataTable as $timestamp => $comments ) {

			foreach ( $comments as $c ) {
				$count ++;

				$html .= "<div class='user-data-row'>";
				$html .= "<div class='user-data-box'>";
				$html .= "<div class='user-avtar'><img src='" . $c['profileURL'] . "'/></div>";
				$html .= "<div class='user-title'>
									<span class='user-name'>" . $c['username'] . " commented on </span>
									\"<a href='javascript:void(0)' data-id='" . $c['dataid'] . "' class='user-comented-on'>" . $c['commented_on_text'] . "</a>\"
									<div class='user-comment'> " . $c['thread'] . "</div>
								</div>";
				$html .= "<div class='user-time'>" . $c['dtTime'] . "</div>";
				$html .= "</div>";
				$html .= "</div>";

				if ( $count >= $limit ) {
					break;
				}
			}
		}

		/*foreach ( $all_meta as $dataid => $v ) {
			if ( strpos( $dataid, '_el' ) === 0 ) {
				$html .= '<tr>';

				$v        = maybe_unserialize( $v[0] );
				$comments = $v['comments'];

				$commented_on_text = $v['commentedOnText'];
				$html              .= "<td rowspan='" . count( $comments ) . "'>$commented_on_text</td>";

				$comment_count = 0;
				foreach ( $comments as $c ) {
					if ( 0 !== $comment_count ) {
						$html .= '<tr>';
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
					$html .= "<td>" . $username . "</td>";
					$html .= "<td><img src='" . $profile_url . "'/></td>";
					$html .= "<td>" . $c['thread'] . "</td>";
					$html .= "<td>Added</td>";
					$html .= "<td>" . $c['dtTime'] . "</td>";

					$html .= '</tr>';
					$comment_count ++;
				}

				$data[ $dataid ] = $comments;

				$html .= '</tr>';

				$count ++;
				if ( $count >= $limit ) {
					break;
				}
			}
		}*/
		//$html .= '</table>';

		/*echo '<pre>';
		print_r( $html );
		die( '<br><----here' );*/

		echo $html;
		wp_die();
	}

	public function my_action_edit() {

		$current_post_id = $_POST['currentPostID'];
		$metaId          = $_POST['metaId'];
		$edited_comment  = str_replace( "\\", "", $_POST['editedComment'] );
		$edited_comment  = json_decode( $edited_comment, true );
		$old_timestamp   = $edited_comment['timestamp'];

		$commentListOld = get_post_meta( $current_post_id, $metaId, true );
		$commentListOld = maybe_unserialize( $commentListOld );

		$edited_draft           = array();
		$edited_draft['thread'] = $edited_comment['thread'];

		$commentListOld['comments'][ $old_timestamp ]['draft_edits'] = $edited_draft;

		update_post_meta( $current_post_id, $metaId, $commentListOld );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$current_drafts['edited'][ $metaId ][] = $old_timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function my_action_delete() {

		$current_post_id = $_POST['currentPostID'];
		$metaId          = $_POST['metaId'];
		$timestamp       = $_POST['timestamp'];

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$current_drafts['deleted'][ $metaId ][] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function my_action_fetch() {
		global $wpdb; // this is how you get access to the database
		$current_post_id = $_POST['currentPostID'];
		// delete_post_meta($current_post_id,'mytest');

		$commentList = get_post_meta( $current_post_id, 'mytest', true );
		echo json_encode( maybe_unserialize( $commentList ) );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function sb_getOwner_callback() {
		$current_post_id = $_POST['currentPostID'];
		$arr             = array();
		//end($commentList);

		$metaId = $_POST['metaId'];
	}

	public function reset_drafts_meta() {
		$current_post_id = $_POST['currentPostID'];

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$permanent_drafts = get_post_meta( $current_post_id, 'permanent_drafts', true );
		$permanent_drafts = maybe_unserialize( $permanent_drafts );
		$permanent_drafts = empty( $permanent_drafts ) ? array() : $permanent_drafts;

		$draft_modes = array( 'resolved', 'comments', 'edited', 'deleted' );

		foreach ( $draft_modes as $draft_mode ) {
			if ( isset( $current_drafts[ $draft_mode ] ) && 0 !== count( $current_drafts[ $draft_mode ] ) ) {
				if ( isset( $permanent_drafts[ $draft_mode ] ) && 0 !== count( $permanent_drafts[ $draft_mode ] ) ) {

					$permanent_drafts[ $draft_mode ] = array_merge_recursive( $permanent_drafts[ $draft_mode ], $current_drafts[ $draft_mode ] );

				} else {
					$permanent_drafts[ $draft_mode ] = $current_drafts[ $draft_mode ];
				}
				$changed = 1;
			}
		}

		if ( 1 === $changed ) {
			update_post_meta( $current_post_id, 'permanent_drafts', $permanent_drafts );
		}

		$timestamp                = current_time( 'timestamp' );
		$drafts_meta              = array();
		$drafts_meta['timestamp'] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $drafts_meta );
	}

	public function merge_draft_stacks() {
		$current_post_id = $_POST['currentPostID'];

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$permanent_drafts = get_post_meta( $current_post_id, 'permanent_drafts', true );
		$permanent_drafts = maybe_unserialize( $permanent_drafts );

		if ( ! empty( $permanent_drafts ) ) {
			$draft_modes = array( 'resolved', 'comments', 'edited', 'deleted' );

			foreach ( $draft_modes as $draft_mode ) {
				if ( isset( $permanent_drafts[ $draft_mode ] ) && 0 !== count( $permanent_drafts[ $draft_mode ] ) ) {
					if ( isset( $current_drafts[ $draft_mode ] ) && 0 !== count( $current_drafts[ $draft_mode ] ) ) {
						$current_drafts[ $draft_mode ] = array_merge_recursive( $current_drafts[ $draft_mode ], $permanent_drafts[ $draft_mode ] );
					} else {
						$current_drafts[ $draft_mode ] = $permanent_drafts[ $draft_mode ];
					}
					$changed = 1;
				}
			}
		}

		if ( 1 === $changed ) {
			update_post_meta( $current_post_id, 'current_drafts', $current_drafts );
		}

		// Flush Permanent Draft Stack.
		update_post_meta( $current_post_id, 'permanent_drafts', '' );

		echo json_encode( $current_drafts );
		wp_die();

	}

	public function sb_resolve_thread_callback() {

		$current_post_id = $_POST['currentPostID'];
		$metaId          = $_POST['metaId'];

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$current_drafts['resolved'][] = $metaId;
		} else {
			$current_drafts['resolved'] = array();

			$current_drafts['resolved'][] = $metaId;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Function is used to register endpoint for rest api call of super widget.
	 *
	 */
	public function career_data_by_select_route() {
		register_rest_route( 'career-data-by-select1', 'my-route1', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'career_data1' ),
			)
		);
	}

	/**
	 * Function is used to fetch super widget data from aws server.
	 *
	 * @return mixed|\WP_REST_Response
	 */
	public function career_data1() {
		$current_post_id = $_GET['currentPostID'];
		$userDetails     = array();
		$elID            = $_GET['elID'];

		$commentList = get_post_meta( $current_post_id, $elID, true );

		$superCareerData = maybe_unserialize( $commentList );
		$comments        = $superCareerData['comments'];

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		foreach ( $comments as $t => $val ) {
			$user_info   = get_userdata( $val['userData'] );
			$username    = $user_info->display_name;
			$profile_url = get_avatar_url( $user_info->user_email );
			$thread      = $val['thread'];

			$edited_draft = isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '';

			$date = date( $time_format . ' ' . $date_format, $t );

			array_push( $userDetails,
				[
					'userName'    => $username,
					'profileURL'  => $profile_url,
					'dtTime'      => $date,
					'thread'      => $thread,
					'userData'    => $val['userData'],
					'status'      => $val['status'],
					'timestamp'   => $t,
					'editedDraft' => $edited_draft,
				] );

		}

		$data['userDetails'] = $userDetails;
		$data['resolved']    = 'true' === $superCareerData['resolved'] ? 'true' : 'false';
		$data['onChange']    = $superCareerData['onChange'];
		$data['value']       = $superCareerData['value'];

		return rest_ensure_response( $data );

	}

	public function sb_whois_callback() {
		$current_post_id = $_GET['currentPostID'];
		$currentUserID   = $_GET['uid'];
		$elID            = $_GET['elID'];
	}

}
