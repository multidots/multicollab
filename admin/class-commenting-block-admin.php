<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    content-collaboration-inline-commenting
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

		// Update caps for authors and contributors.
		add_filter( 'admin_init', array( $this, 'cf_custom_caps' ) );

		// Allow caps for Multisite environment.
		add_filter( 'map_meta_cap', array( $this, 'cf_add_unfiltered_html_capability_to_users' ), 1, 3 );
	}

	/**
	 * Allowed Administrator, editor, author and contributor user to enter unfiltered html.
	 *
	 * @param array $caps All caps.
	 * @param string $cap Cap in a loop.
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	function cf_add_unfiltered_html_capability_to_users( $caps, $cap, $user_id ) {
		if ( 'unfiltered_html' === $cap && ( user_can( $user_id, 'administrator' ) || user_can( $user_id, 'editor' ) || user_can( $user_id, 'author' ) || user_can( $user_id, 'contributor' ) ) ) {
			$caps = array( 'unfiltered_html' );
		}

		return $caps;
	}

	/**
	 * Add capabilities to user roles to make 'mdspan' tag unfiltered.
	 */
	public function cf_custom_caps() {

		$roles = array( 'author', 'contributor' );

		foreach ( $roles as $role ) {

			$role = get_role( $role );

			if ( $role ) {
				// Add custom capabilities.
				$role->add_cap( 'unfiltered_html' );
			}
		}
	}

	/**
	 * Get User Details using AJAX.
	 */
	public function cf_get_user() {

		$curr_user = wp_get_current_user();
		$userID    = $curr_user->ID;
		$userName  = $curr_user->display_name;
		$userURL   = get_avatar_url( $userID );

		echo wp_json_encode( array( 'id' => $userID, 'name' => $userName, 'url' => $userURL ) );
		wp_die();

	}

	/**
	 * @param int $post_ID Post ID.
	 * @param object/string $post Post Content.
	 * @param string $update Status of the update.
	 */
	public function cf_post_status_changes( $post_ID, $post, $update ) {

		$p_content  = is_object( $post ) ? $post->post_content : $post;
		$p_link     = get_edit_post_link( $post_ID );
		$p_title    = get_the_title( $post_ID );
		$site_title = get_bloginfo( 'name' );

		// Publish drafts from the 'current_drafts' stack.
		$current_drafts = get_post_meta( $post_ID, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$current_timestamp = current_time( 'timestamp' );

		// Mark Resolved Threads.
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$resolved_drafts = $current_drafts['resolved'];

			// Get current user details.
			$curr_user                 = wp_get_current_user();
			$current_user_email        = $curr_user->user_email;
			$current_user_display_name = $curr_user->display_name;

			foreach ( $resolved_drafts as $el ) {
				$prev_state                       = get_post_meta( $post_ID, $el, true );
				$prev_state                       = maybe_unserialize( $prev_state );
				$prev_state['resolved']           = 'true';
				$prev_state['resolved_timestamp'] = $current_timestamp;
				$prev_state['resolved_by']        = get_current_user_id();
				update_post_meta( $post_ID, $el, $prev_state );

				// Send Email.
				$comments = get_post_meta( $post_ID, "$el", true );
				$comments = maybe_unserialize( $comments );
				$comments = isset( $comments['comments'] ) ? $comments['comments'] : '';

				if ( ! empty( $comments ) && is_array( $comments ) ) {

					$users_emails = array();

					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$html    = '
					<style>
					.comment-box{-webkit-box-shadow:0px 6px 20px 0px rgba(27,29,35,0.1);box-shadow:0px 6px 20px 0px rgba(27,29,35,0.1);border:1px solid #E2E4E7;border-radius:5px;background:#fff;padding:20px;-webkit-box-sizing:border-box;box-sizing:border-box;max-width:600px;width:70%;font-family:Arial,serif;margin:40px 0 0;}
					.comment-box .comment-box-header{padding-bottom:15px;-webkit-box-sizing:border-box;box-sizing:border-box;border-bottom:1px solid #ccc;margin-bottom:15px;}
					.comment-box .comment-box-header p{margin:15px 0;}
					.comment-box .comment-box-header a{color:#0073aa;text-decoration:none;display:inline-block;padding:5px 7px 4px;border:1px solid #ccc;border-radius:5px;}
					.comment-box .comment-box-header a:hover{text-decoration:underline;color:#006799;}
					.comment-header{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
					.comment-header:last-child{margin-bottom:0;}
					.comment-header .avtar{width:40px;margin-right:10px;}
					.comment-header .avtar img{max-width:100%;border-radius:50%;}
					.comment-header .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
					.comment-header .commenter-name-time{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
					.comment-header .commenter-name-time .commenter-name{font-size:16px;font-weight:600;font-family:Arial,serif;margin-right:10px;}
					.comment-header .commenter-name-time .comment-time{font-size:12px;font-weight:400;font-family:Arial,serif;color:#808080;}
					.comment-header .comment{font-family:Arial,serif;font-size:14px;}
					</style>
					';
					$html    .= '<div class="comment-box"><div class="comment-box-header">';
					$html    .= '<p>';
					$html    .= esc_html( $current_user_display_name ) . __( ' has resolved the following thread.', 'content-collaboration-inline-commenting' );
					$html    .= '</p><a href="' . esc_url( $p_link ) . '" class="comment-page-title">' . esc_html( $p_title ) . '</a></div>';
					$html    .= '<div class="comment-box-body">';
					foreach ( $comments as $timestamp => $arr ) {

						if ( isset( $arr['status'] ) && 'permanent_draft' !== $arr['status'] ) {
							$user_info      = get_userdata( $arr['userData'] );
							$username       = $user_info->display_name;
							$users_emails[] = $user_info->user_email;
							$profile_url    = get_avatar_url( $user_info->user_email );
							$date           = gmdate( $time_format . ' ' . $date_format, $timestamp );
							$text_comment   = $arr['thread'];
							$cstatus        = $arr['status'];
							$draft          = 'draft' === $cstatus ? '(draft)' : '';

							$html .= "<div class='comment-header'>
										<div class='avtar'><img src='" . esc_url( $profile_url ) . "' alt='avatar' /></div>
										<div class='comment-details'>
											<div class='commenter-name-time'>
												<div class='commenter-name'>" . esc_html( $username ) . "</div>
												<div class='comment-time'>" . esc_html( $date ) . "</div>
											</div>
											<div class='comment'>" . __( '<strong>Comment</strong>', 'content-collaboration-inline-commenting' ) . ": " . esc_html( $text_comment ) . " " . esc_html( $draft ) . "
											</div>
										</div>
									 </div>";
						}
					}
					$html .= '</div>'; // .comment-box-body end
					$html .= '</div>'; // .comment-box end

					$users_emails = array_unique( $users_emails );
					if ( ( $key = array_search( $current_user_email, $users_emails, true ) ) !== false ) {
						unset( $users_emails[ $key ] );
					}

					// Limit the page and site titles for Subject.
					$p_title    = $this->cf_limit_characters( $p_title, 30 );
					$site_title = $this->cf_limit_characters( $site_title, 20 );

					wp_mail( $users_emails, __( "Comment Resolved — $p_title — $site_title", 'content-collaboration-inline-commenting' ), $html, $headers );
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
	 * @param string $string The string to be limited.
	 * @param int $limit The total number of characters allowed.
	 *
	 * @return string The limited string with '...' appended.
	 */
	public function cf_limit_characters( $string, $limit = 100 ) {
		return strlen( $string ) > $limit ? substr( $string, 0, $limit ) . '...' : $string;
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/commenting-block-admin.css', array(), '1.0.3', 'all' );

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
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/commenting-block-admin.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'content-collaboration-inline-commenting', plugin_dir_url( __FILE__ ) . 'js/blockJS/block.build.js', array(
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
			), '1.0.7', true );

			global $wp_roles;
			$curr_user         = wp_get_current_user();
			$current_user_role = $wp_roles->roles[ $curr_user->roles[0] ]['name'];
			$date_format       = get_option( 'date_format' );
			$time_format       = get_option( 'time_format' );
			wp_localize_script( 'content-collaboration-inline-commenting', 'suggestionBlock', array( 'userRole' => $current_user_role, 'dateFormat' => $date_format, 'timeFormat' => $time_format ) );

			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
		}

	}

	/**
	 * Sent email to the commented recipients
	 *
	 * @param string $message
	 * @return void
	 */
	public function cf_sent_email_to_commented_users( $message ) {
		$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
		preg_match_all( $pattern, $message, $matches );

		if ( ! empty( $matches[0] ) ) {
			$to        = $matches[0];
			$subject   = 'You have been mentioned';
			$body      = $message;
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			wp_mail( $to, $subject, $body );
		}

	}

	/**
	 * Convert string to linkable email
	 *
	 * @param string $str
	 * @return string
	 */
	public function convert_str_to_email( $str ) {
		$mail_pattern = "/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/";
		$formatted = preg_replace( $mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str );

		return $formatted;
	}

	/**
	 * Add Comment function.
	 */
	public function cf_add_comment() {

		$commentList = filter_input( INPUT_POST, "commentList" );
		$commentList = html_entity_decode( $commentList );
		$commentList = json_decode( $commentList, true );

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$arr             = array();

		$commentList = end( $commentList );
		$metaId      = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		// Adding link to email addresses
		$commentList['thread'] = $this->convert_str_to_email( $commentList['thread'] );

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

		// Sending email
		$this->cf_sent_email_to_commented_users( $commentList['thread'] );
		wp_die();
	}

	/**
	 * Display Comment Activity in History Popup.
	 */
	public function cf_comments_history() {

		$limit           = filter_input( INPUT_POST, "limit", FILTER_SANITIZE_NUMBER_INT );
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

						$cstatus        = 0 === $comment_count ? __( 'commented', 'content-collaboration-inline-commenting' ) : __( 'replied', 'content-collaboration-inline-commenting' );
						$cstatus        .= __( ' on', 'content-collaboration-inline-commenting' );
						$comment_status = isset( $c['status'] ) ? $c['status'] : '';
						$cstatus        = 'deleted' === $comment_status ? __( 'deleted comment of', 'content-collaboration-inline-commenting' ) : $cstatus;

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

		$html = '<div id="history-popup-insider">';
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
									<span class='user-name'>" . esc_html( $c['username'] ) . " " . esc_html( $c['status'] ) . "</span> ";

					if ( 'deleted comment of' === $c['status'] || 'resolved thread' === $c['status'] ) {
						$html .= esc_html( $commented_on_text );
					} else {
						$html .= "<a href='javascript:void(0)' data-id='" . esc_attr( $c['dataid'] ) . "' class='user-comented-on'>" . esc_html( $commented_on_text ) . "</a>";
					}

					$html .= "<div class='user-comment'> " . esc_html( $c['thread'] ) . "</div>
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
			$html .= __( 'No comments found.', 'content-collaboration-inline-commenting' );
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

		$edited_comment = filter_input( INPUT_POST, "editedComment" );
		$edited_comment = html_entity_decode( $edited_comment );
		$edited_comment = json_decode( $edited_comment, true );

		$edited_comment['thread'] = $this->convert_str_to_email( $edited_comment['thread'] );

		$old_timestamp = $edited_comment['timestamp'];

		$commentListOld = get_post_meta( $current_post_id, $metaId, true );
		$commentListOld = maybe_unserialize( $commentListOld );

		$edited_draft           = array();
		$edited_draft['thread'] = $edited_comment['thread'];

		$commentListOld['comments'][ $old_timestamp ]['draft_edits'] = $edited_draft;

		update_post_meta( $current_post_id, $metaId, $commentListOld );

		// Update Current Drafts.
		$current_drafts                        = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts                        = maybe_unserialize( $current_drafts );
		$current_drafts                        = empty( $current_drafts ) ? array() : $current_drafts;
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
		$current_drafts                         = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts                         = maybe_unserialize( $current_drafts );
		$current_drafts                         = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['deleted'][ $metaId ][] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Save important details in a localstorage.
	 */
	public function cf_store_in_localstorage() {

		// Returning show_avatar option to display avatars (or not to).
		$show_avatars = get_option( 'show_avatars' );
		$show_avatars = "1" === $show_avatars ? $show_avatars : 0;

		// Store plugin URL in localstorage so that its easy
		// to get sub site URL in JS files in Multisite environment.

		echo wp_json_encode( array( 'showAvatars' => $show_avatars, 'commentingPluginUrl' => COMMENTING_BLOCK_URL ) );
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
			$cstatus     = isset( $val['status'] ) ? $val['status'] : '';

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

	/**
	 * Fetch User Email List
	 */
	public function cf_get_user_email_list() {

		// Fetch out all user's email
		$email_list   = [];
		$system_users = get_users();
		foreach ( $system_users as $user ) {
			$email_list[] = [
				'user_email' => $user->user_email
			];
		}

		// Sending Response
		$response = $email_list;
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Fetch Matched User Email List
	 */
	public function cf_get_matched_user_email_list() {
		global $wpdb;
		$niddle = isset( $_POST['niddle'] ) ? sanitize_text_field( $_POST['niddle'] ) : '';
		$niddle = substr( $niddle, 1 );
		if ( ! empty( $niddle ) && '@' !== $niddle ) {
			$emails   = $wpdb->get_results(
				"SELECT user_email FROM {$wpdb->prefix}users WHERE user_email LIKE '%{$niddle}%'"
			);
			$response = $emails;
		} else if ( '@' === $niddle ) {
			$this->cf_get_user_email_list();
		} else {
			$response = '';
		}
		echo wp_json_encode( $response );
		wp_die();
	}


}
