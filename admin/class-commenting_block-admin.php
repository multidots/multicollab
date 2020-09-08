<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    commenting_block
 * @subpackage commenting_block/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    commenting_block
 * @subpackage commenting_block/admin
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

		// Publish Comments on status change.
		add_action( 'post_updated', array( $this, 'cf_post_status_changes' ), 10, 3 );
	}

	/**
	 * Get User Details using AJAX.
	 */
	public function cf_get_user() {

		$curr_user = wp_get_current_user();
		$userID       = $curr_user->ID;
		$userName     = $curr_user->display_name;
		$userURL      = get_avatar_url( $userID );

		echo wp_json_encode( array( 'id' => $userID, 'name' => $userName, 'url' => $userURL ) );
		wp_die();

	}

	/**
	 * @param int $post_ID Post ID.
	 * @param object/string $post Post Content.
	 * @param string $update Status of the update.
	 */
	public function cf_post_status_changes( $post_ID, $post, $update ) {

		$p_content = is_object( $post ) ? $post->post_content : $post;

		// Publish drafts from the 'current_drafts' stack.
		$current_drafts = get_post_meta( $post_ID, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$current_timestamp = current_time( 'timestamp' );

		// Mark Resolved Threads.
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$resolved_drafts = $current_drafts['resolved'];

			foreach ( $resolved_drafts as $el ) {
				$prev_state                       = get_post_meta( $post_ID, $el, true );
				$prev_state                       = maybe_unserialize( $prev_state );
				$prev_state['resolved']           = 'true';
				$prev_state['resolved_timestamp'] = $current_timestamp;
				$prev_state['resolved_by']        = get_current_user_id();
				update_post_meta( $post_ID, $el, $prev_state );

				$curr_user = wp_get_current_user();
				$current_user_email = $curr_user->user_email;

				// Send Email.
				$comments = get_post_meta( $post_ID, "$el", true );
				$comments = maybe_unserialize( $comments );
				$comments = isset( $comments['comments'] ) ? $comments['comments'] : '';

				if ( ! empty( $comments ) && is_array( $comments ) ) {

					$users_emails = array();

					$headers = array( 'Content-Type: text/html; charset=UTF-8' );

					$html = __( 'Hi Admin', 'commenting_block' );
					$html .= ',<br><br>';
					$html = __( 'The following comment has been resolved', 'commenting_block' );
					$html .= ':<br><br>';

					foreach ( $comments as $timestamp => $arr ) {

						if( isset( $arr['status'] ) && 'permanent_draft' !== $arr['status'] ) {
							$user_info   = get_userdata( $arr['userData'] );
							$username    = $user_info->display_name;
							$users_emails[] = $user_info->user_email;
							$profile_url = get_avatar_url( $user_info->user_email );
							$date        = gmdate( $time_format . ' ' . $date_format, $timestamp );
							$text_comment     = $arr['thread'];
							$cstatus = $arr['status'];
							$draft = 'draft' === $cstatus ? '(draft)' : '';

							$html .= "<div className='comment-header'>
									<div className='avtar'><img src='" . esc_url( $profile_url ) ."' alt='avatar' /></div>
									<div className='commenter-name-time'>
									<div className='commenter-name'>". esc_html( $username ) ."</div>
									<div className='comment-time'>". esc_html( $date ) ."</div>
									<div className='comment'>Comment: ". esc_html( $text_comment. " ". $draft ) . "</div>
									</div>
								</div>";

							$html .= ' <br> ';
						}
					}

					$html .= '<br>' . __( 'Thank you!', 'commenting_block' );

					$users_emails = array_unique( $users_emails );
					if ( ( $key = array_search( $current_user_email, $users_emails, true ) ) !== false ) {
						unset( $users_emails[ $key ] );
					}

					wp_mail( $users_emails, __( 'Comment Resolved', 'commenting_block' ), $html, $headers );
				}
			}
		}

		// Publish New Comments.
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$new_drafts = $current_drafts['comments'];
			foreach ( $new_drafts as $el => $drafts ) {

				/*
				 * Make publish only if its tag available in the content.
				 * Doing this to handle the CTRL-Z action.
				 * Sometimes CTRL-Z does not removes the tag completely
				 * but only removes its attributes, so we cant find 'datatext' attribute,
				 * So skipping those mdspan tags which has no 'datatext' attribute.
				 */
				$elid = str_replace( '_', '', $el );
				if ( strpos( $p_content, $elid ) !== false ) {
					$prev_state = get_post_meta( $post_ID, $el, true );
					$prev_state = maybe_unserialize( $prev_state );
					foreach ( $drafts as $d ) {
						$prev_state['comments'][ $d ]['status'] = 'publish';
					}
					update_post_meta( $post_ID, $el, $prev_state );
				}
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
					// Update the timestamp of deleted comment.
					$previous_comment = $prev_state['comments'][ $t ];
					unset( $prev_state['comments'][ $t ] );
					$prev_state['comments'][ $current_timestamp ]           = $previous_comment;
					$prev_state['comments'][ $current_timestamp ]['status'] = 'deleted';
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

		$screen = get_current_screen();
		if ( $screen->is_block_editor ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/commenting_block-admin.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'commenting-block', plugin_dir_url( __FILE__ ) . 'js/blockJS/block.build.js', array(
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
			), '1.0.4', true );

			global $wp_roles;
			$curr_user      = wp_get_current_user();
			$current_user_role = $wp_roles->roles[ $curr_user->roles[0] ]['name'];
			$date_format       = get_option( 'date_format' );
			$time_format       = get_option( 'time_format' );
			wp_localize_script( 'commenting-block', 'suggestionBlock', array( 'userRole' => $current_user_role, 'dateFormat' => $date_format, 'timeFormat' => $time_format ) );

			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
		}

	}

	/**
	 * Add Comment function.
	 */
	public function cf_add_comment() {

		$commentList = filter_input( INPUT_POST, "commentList", FILTER_SANITIZE_STRING );
		$commentList = html_entity_decode( $commentList );
		$commentList = json_decode( $commentList, true );

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$arr             = array();

		$commentList = end( $commentList );
		$metaId      = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		// If 'commented on' text is blank, stop process.
		if ( empty( $commentList['commentedOnText'] ) ) {
			echo wp_json_encode( array( 'error' => 'Please select text to comment on.' ) );
			wp_die();
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$timestamp   = current_time( 'timestamp' );

		$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );

		$commentListOld  = get_post_meta( $current_post_id, $metaId, true );
		$superCareerData = maybe_unserialize( $commentListOld );

		$arr['status']   = 'draft';
		$arr['userData'] = get_current_user_id();
		$arr['thread']   = $commentList['thread'];

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		} else {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		if ( isset( $superCareerData['comments'] ) && 0 !== count( $superCareerData['comments'] ) ) {
			$superCareerData['comments'][ $timestamp ] = $arr;
		} else {
			$superCareerData                           = array();
			$superCareerData['comments'][ $timestamp ] = $arr;
			$superCareerData['commentedOnText']        = $commentList['commentedOnText'];

			update_post_meta( $current_post_id, 'th' . $metaId, get_current_user_id() );
		}
		update_post_meta( $current_post_id, $metaId, $superCareerData );

		echo wp_json_encode( array( 'dtTime' => $dtTime, 'timestamp' => $timestamp ) );
		wp_die();
	}

	/**
	 * Display Comment Activity in History Popup.
	 */
	public function cf_comments_history() {

		$limit 			 = filter_input( INPUT_POST, "limit", FILTER_SANITIZE_NUMBER_INT );
		$limit           = isset( $limit ) ? $limit : 10;
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );

		$all_meta         = get_post_meta( $current_post_id );
		$userData         = array();
		$prepareDataTable = array();

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$total_comments = 0;
		foreach ( $all_meta as $dataid => $v ) {
			if ( strpos( $dataid, '_el' ) === 0 ) {
				$dataid            = str_replace( '_', '', $dataid );
				$v                 = maybe_unserialize( $v[0] );
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
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['status']            = 'resolved thread';

				} else {

					$comment_count = 0;
					foreach ( $comments as $timestamp => $c ) {

						$cstatus         = 0 === $comment_count ? __( 'commented', 'commenting_block' ) : __( 'replied', 'commenting_block' );
						$cstatus		 .= __( ' on', 'commenting_block' );
						$comment_status = isset( $c['status'] ) ? $c['status'] : '';
						$cstatus         = 'deleted' === $comment_status ? __( 'deleted comment of', 'commenting_block' ) : $cstatus;

						// Stop displaying history of comments in draft mode.
						if ( 'draft' === $comment_status ) {
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
						$comment_count ++;
						$total_comments ++;
					}
				}
			}
		}

		$html = '<div id="history-popup-insder">';
		if ( 0 !== $total_comments ) {
			krsort( $prepareDataTable, SORT_NUMERIC );

			$count = 0;

			foreach ( $prepareDataTable as $timestamp => $comments ) {
				foreach ( $comments as $c ) {

					// Limit the number of characters of 'Commented On' Text.
					$limit             = 50;
					$commented_on_text = $c['commented_on_text'];
					if ( $limit < strlen( $commented_on_text ) ) {
						$commented_on_text = substr( $commented_on_text, 0, $limit ) . '...';
					}
					$c['thread'] = isset( $c['thread'] ) ? $c['thread'] : '';
					$count ++;

					$html .= "<div class='user-data-row'>";
					$html .= "<div class='user-data-box'>";
					$html .= "<div class='user-avtar'><img src='" . esc_url( $c['profileURL'] ) . "'/></div>";
					$html .= "<div class='user-title'>
									<span class='user-name'>" . esc_html( $c['username'] . " " . $c['status'] ) . "</span>
									\"<a href='javascript:void(0)' data-id='" . esc_attr( $c['dataid'] ) . "' class='user-comented-on'>" . esc_html( $commented_on_text ) . "</a>\"
									<div class='user-comment'> " . esc_html( $c['thread'] ) . "</div>
								</div>";
					$html .= "<div class='user-time'>" . esc_html( $c['dtTime'] ) . "</div>";
					$html .= "</div>";
					$html .= "</div>";

					if ( $count >= $limit ) {
						break;
					}
				}
			}
		} else {
			$html .= __( 'No comments found.', 'commenting_block' );
		}
		$html .= "</div>";

		$allowed_tags = array(
			'a'    => array( 'id' => array(), 'href' => array(), 'target' => array(), 'style' => array(), 'class' => array(), 'data-id' => array() ),
			'div'  => array( 'id' => array(), 'class' => array(), 'style' => array() ),
			'img'  => array( 'src' => array(), 'title' => array(), 'alt' => array() ),
			'span' => array( 'class' => array(), 'style' => array() ),
		);

		echo wp_kses( $html, $allowed_tags );
		wp_die();
	}

	/**
	 * Update Comment function.
	 */
	public function cf_update_comment() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		$edited_comment = filter_input( INPUT_POST, "editedComment", FILTER_SANITIZE_STRING );
		$edited_comment = html_entity_decode( $edited_comment );
		$edited_comment = json_decode( $edited_comment, true );

		$old_timestamp = $edited_comment['timestamp'];

		$commentListOld = get_post_meta( $current_post_id, $metaId, true );
		$commentListOld = maybe_unserialize( $commentListOld );

		$edited_draft           = array();
		$edited_draft['thread'] = $edited_comment['thread'];

		$commentListOld['comments'][ $old_timestamp ]['draft_edits'] = $edited_draft;

		update_post_meta( $current_post_id, $metaId, $commentListOld );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['edited'][ $metaId ][] = $old_timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Delete Comment function.
	 */
	public function cf_delete_comment() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );
		$timestamp       = filter_input( INPUT_POST, "timestamp", FILTER_SANITIZE_NUMBER_INT );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['deleted'][ $metaId ][] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Reset Drafts meta.
	 */
	public function cf_reset_drafts_meta() {
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );;

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;

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

	/**
	 * Merge Drafts meta.
	 */
	public function cf_merge_draft_stacks() {
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts   = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts   = maybe_unserialize( $current_drafts );
		$current_drafts   = empty( $current_drafts ) ? array() : $current_drafts;
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

		echo wp_json_encode( $current_drafts );
		wp_die();

	}

	/**
	 * Resolve Thread function.
	 */
	public function cf_resolve_thread() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$current_drafts['resolved'][] = $metaId;
		} else {
			$current_drafts['resolved'][] = $metaId;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Rest API for Gutenberg Commenting Feature.
	 *
	 */
	public function cf_rest_api() {
		register_rest_route( 'cf', 'cf-get-comments-api', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'cf_get_comments' ),
			)
		);
	}

	/**
	 * Function is used to fetch stored comments.
	 *
	 * @return mixed|\WP_REST_Response
	 */
	public function cf_get_comments() {
		$current_post_id = filter_input( INPUT_GET, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$userDetails     = array();
		$elID            = filter_input( INPUT_GET, "elID", FILTER_SANITIZE_STRING );

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
			$cstatus      = isset( $val['status'] ) ? $val['status'] : '';

			$edited_draft = isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '';

			$date = gmdate( $time_format . ' ' . $date_format, $t );

			if ( 'deleted' !== $cstatus ) {
				array_push( $userDetails,
					[
						'userName'    => $username,
						'profileURL'  => $profile_url,
						'dtTime'      => $date,
						'thread'      => $thread,
						'userData'    => $val['userData'],
						'status'      => $cstatus,
						'timestamp'   => $t,
						'editedDraft' => $edited_draft,
					] );
			}
		}

		$data                    = array();
		$data['userDetails']     = $userDetails;
		$data['resolved']        = 'true' === $superCareerData['resolved'] ? 'true' : 'false';
		$data['commentedOnText'] = $superCareerData['commentedOnText'];

		return rest_ensure_response( $data );

	}

}
