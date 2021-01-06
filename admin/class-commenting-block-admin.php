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
		$userRole  = get_userdata($userID)->roles[0];

		echo wp_json_encode( array( 'id' => $userID, 'name' => $userName, 'role' => $userRole, 'url' => $userURL ) );
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

					$current_comment = end( $comments );

					$count_resolved_comment = count( $current_drafts['resolved'] );
					$count_open_comment     = count( $comments ) - $count_resolved_comment;

					$users_emails = array();

					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$html    = '
					<style>
						.comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
						.comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
						.comment-box *{-webkit-box-sizing:border-box;box-sizing:border-box;}
						.comment-box a{color:#4B1BCE;}
						.comment-box .comment-box-header{margin-bottom:30px;border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
						.comment-box .comment-box-header p{margin:15px 0;}
						.comment-box .comment-box-header .comment-page-title{font-size:20px;}
						.comment-box .comment-box-header a{color:#4B1BCE;text-decoration:underline;display:inline-block;font-size:20px;}
						.comment-box .comment-header{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
						.comment-box .comment-header:last-child{margin-bottom:0;}
						.comment-box .avtar{width:40px;margin-right:10px;}
						.comment-box .avtar img{max-width:100%;border-radius:50%;}
						.comment-box .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
						.comment-box .comment-header .commenter-name-role{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
						.comment-box .comment-header .commenter-name{font-size:18px;font-family:Roboto,Arial,sans-serif;margin-right:7px;color:#141414;font-weight:600;}
						.comment-box .comment-header .commenter-name-role .comment-role{font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;}
						.comment-box .author-name{margin:0 0 5px;}
						.comment-box .comment,
						.comment-box .author-comment{font-family:Arial,serif;font-size:14px;color:#4C5056;}
						.comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
						.comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:15px;color:#4C5056;}
						.comment-box .comment-assigned-to{margin-bottom:20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
						.comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;margin-left:5px;}
						.comment-box .comment-assigned-to .icon-assign{margin-right:5px;}
						.comment-box ul{margin:0;padding:0;list-style:none;}
						.comment-box ul li{margin-bottom:20px;}
						.comment-box .latest-comment{margin:0 0 20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;font-family:Roboto,Arial,sans-serif;font-weight:600;}
						.comment-box .latest-comment .icon-comment{margin-right:10px;}
						.comment-box .comment-box-header h3{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
					</style>
					';
					$html    .= '<div class="comment-box"><div class="comment-box-header">';
					$html    .= '<h3>';
					$html    .= '<span class="icon-resolved">';
					$html 	 .= '<svg id="Group_19" data-name="Group 19" xmlns="http://www.w3.org/2000/svg" width="40" height="40.001" viewBox="0 0 40 40.001"><path id="Path_6" data-name="Path 6" d="M65.567,45.564a20,20,0,1,0,20,20A20,20,0,0,0,65.567,45.564ZM61.722,75.7l-7.583-7.731L57,65.164l4.753,4.847L73.609,58.151l2.828,2.828Z" transform="translate(-45.567 -45.564)" fill="#6ac359"/></svg>';
					$html 	 .= '</span><span class="commenter-name">' .esc_html( $current_user_display_name ) .'</span>' . __( ' has resolved the following thread.', 'content-collaboration-inline-commenting' );
					$html 	 .= '</h3><p class="open-comment">Open - '.$count_open_comment.' Comment(s)</p>';
					$html 	 .= '<p class="resolve-comment">Resolved - '.$count_resolved_comment.' Comment(s)</p>';
					$html    .= '<p>' .$this->convert_str_to_email( $current_comment['thread'] ).'</p>';
					$html    .= '<h2 class="comment-page-title"><a href="' . esc_url( $p_link ) . '">' . esc_html( $p_title ) . '</a></h2></div>';
					$html    .= '<div class="comment-box-body"><ul>';
					foreach ( $comments as $timestamp => $arr ) {
						if ( isset( $arr['status'] ) && 'permanent_draft' !== $arr['status'] ) {
							$user_info      = get_userdata( $arr['userData'] );
							$username       = $user_info->display_name;
							$users_emails[] = $user_info->user_email;
							$profile_url    = get_avatar_url( $user_info->user_email );
							$date           = gmdate( $time_format . ' ' . $date_format, $timestamp );
							$text_comment   = $this->convert_str_to_email( $arr['thread'] );
							$cstatus        = $arr['status'];
							$draft          = 'draft' === $cstatus ? '(draft)' : '';

							$html .= "<li>
										<div class='comment-header'>
											<div class='avtar'><img src='" . esc_url( $profile_url ) . "' alt='avatar' /></div>
											<div class='comment-details'>
												<div class='commenter-name-role'>
													<div class='commenter-name'>" . esc_html( $username ) . "</div>
													<div class='comment-role'>(Author)</div>
												</div>
												<div class='comment'>" . __( '<strong>Comment</strong>', 'content-collaboration-inline-commenting' ) . ": " . $text_comment . " " . esc_html( $draft ) . "
												</div>
											</div>
										 </div>
									   </li>";
						}
					}
					$html .= '</ul></div>'; // .comment-box-body end
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
			wp_enqueue_script( 'cf-mark', plugin_dir_url( __FILE__ ) . 'js/mark.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'content-collaboration-inline-commenting', plugin_dir_url( __FILE__ ) . 'js/blockJS/block.build.js', array(
				'jquery',
				'cf-mark',
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
			$comment_id        = filter_input( INPUT_GET, 'comment_id', FILTER_SANITIZE_STRING );
			wp_localize_script( 'content-collaboration-inline-commenting', 'suggestionBlock', array( 'userRole' => $current_user_role, 'dateFormat' => $date_format, 'timeFormat' => $time_format ) );
			wp_localize_script( $this->plugin_name, 'adminLocalizer', [
				'comment_id' => isset( $comment_id ) ? $comment_id : null
			] );

			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
		}

	}

	/**
	 * Convert string to linkable email.
	 *
	 * @param string $str Contains the strings that comes from the textarea.
	 * @return string
	 */
	public function convert_str_to_email( $str ) {
		$mail_pattern = "/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/";
		return preg_replace( $mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str );
	}

	/**
	 * Sent email to the commented recipients.
	 *
	 * @param array $args Contains all keys related to send the email.
	 * @return void
	 */
	public function cf_sent_email_to_commented_users( $args ) {
		$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
		preg_match_all( $pattern, $args['thread'], $matches );

		if( ! empty( $args['list_of_comments'] ) ) {
			$comment_list_html = '<ul>';
			foreach( $args['list_of_comments'] as $comment ) {
				$comment_list_html .= "
					<li>
						<a href='{$args['post_edit_link']}&comment_id={$comment['timestamp']}' target='_blank'>
							<div class='comment-header'>
								<div class='avtar'>
									<img src='{$comment['profileURL']}' alt='{$comment['userName']}'/>
								</div>
								<div class='comment-details'>
									<h3 class='author-name'>{$comment['userName']}</h3>
									<div class='author-comment'>{$comment['thread']}</div>
								</div>
							</div>
						</a>
					</li>
				";
			}
			$comment_list_html .= '</ul>';
		}

		$assigned_to_who = '';
		if( ! empty( $args['assign_to'] ) ) {
			$assinged_user   = get_user_by( 'email', $args['assign_to'] );
			$assigned_to_who = "
				<div class='comment-assigned-to'>
					<span class='icon-assign'>
						<svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 22 22'>
							<g id='Group_22' data-name='Group 22' transform='translate(1 1)'>
								<circle id='Ellipse_4' data-name='Ellipse 4' cx='10' cy='10' r='10' fill='none' stroke='#6ac359' stroke-width='2'/>
								<path id='Path_7' data-name='Path 7' d='M93.92,119.6l-3.593-3.664,1.353-1.327,2.252,2.3,5.621-5.621,1.34,1.34Z' transform='translate(-85.327 -105.288)' fill='#6ac359'/>
							</g>
						</svg>
					</span>
					Assigned to <a href='mailto:{$assinged_user->user_email}' title={$assinged_user->display_name} class='commenter-name'>@{$assinged_user->first_name}</a>
				</div>
			";
		}

		$template = "
			<style>
				.comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
				.comment-box *{-webkit-box-sizing:border-box;box-sizing:border-box;}
				.comment-box a{color:#4B1BCE;}
				.comment-box .comment-box-header{margin-bottom:30px;border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
				.comment-box .comment-box-header p{margin:15px 0;}
				.comment-box .comment-box-header .comment-page-title{font-size:20px;}
				.comment-box .comment-box-header a{color:#4B1BCE;text-decoration:underline;display:inline-block;font-size:20px;}
				.comment-box .comment-header{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-header:last-child{margin-bottom:0;}
				.comment-box .avtar{width:40px;margin-right:10px;}
				.comment-box .avtar img{max-width:100%;border-radius:50%;}
				.comment-box .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
				.comment-box .comment-header .commenter-name-role{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-header .commenter-name{font-size:18px;font-family:Roboto,Arial,sans-serif;margin-right:7px;color:#141414;font-weight:600;}
				.comment-box .comment-header .commenter-name-role .comment-role{font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;}
				.comment-box .author-name{margin:0 0 5px;}
				.comment-box .comment,
				.comment-box .author-comment{font-family:Arial,serif;font-size:14px;color:#4C5056;}
				.comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
				.comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:15px;color:#4C5056;}
				.comment-box .comment-assigned-to{margin-bottom:20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;margin-left:5px;}
				.comment-box .comment-assigned-to .icon-assign{margin-right:5px;}
				.comment-box ul{margin:0;padding:0;list-style:none;}
				.comment-box ul li{margin-bottom:20px;}
				.comment-box .latest-comment{margin:0 0 20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;font-family:Roboto,Arial,sans-serif;font-weight:600;}
				.comment-box .latest-comment .icon-comment{margin-right:10px;}
				.comment-box .comment-box-header h3{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
			</style>
			<div class='comment-box'>
				<div class='comment-box-header'>
					<p><span class='commenter-name'>{$args['commenter']}</span> - mentioned you in a comment in the following page.</p>
					<h2 class='comment-page-title'><a href='{$args['post_edit_link']}' target='_blank'>{$args['post_title']}</a></h2>
				</div>
				<div class='comment-box-body'>
					<h2 class='latest-comment'>
						<span class='icon-comment'>
							<svg xmlns='http://www.w3.org/2000/svg' width='36.226' height='43.02' viewBox='0 0 36.226 43.02'>
								<g id='Group_2' data-name='Group 2' transform='translate(-36.242 1.019)'>
									<path id='Path_1' data-name='Path 1' d='M64.607,30.769,52.29,40l0-5.88-1.37-.279a17.1,17.1,0,1,1,13.683-3.072Z' transform='translate(0 0)' fill='none' stroke='#4b1bce' stroke-width='2'/>
								</g>
							</svg>
						</span>
						{$args['thread']}
					</h2>
					<div class='commented_text'>{$args['commented_text']}</div>
					{$assigned_to_who}
					{$comment_list_html}
				</div>
			</div>
		";

		// Limit the page and site titles for Subject.
		$post_title = $this->cf_limit_characters( $args['post_title'], 30 );
		$site_name  = $this->cf_limit_characters( $args['site_name'], 20 );

		if( ! empty( $args['assign_to'] ) ) {
			$key = array_search( $args['assign_to'], $matches[0] );
			unset( $matches[0][$key] );
		}

		if ( ! empty( $matches[0] ) ) {
			$to      = $matches[0];
			$subject = "New Comment - {$post_title} - {$site_name}";
			$body    = $template;
			$headers = 'Content-Type: text/html; charset=UTF-8';
			wp_mail( $to, $subject, $body, $headers );
		}

		if( ! empty( $args['assign_to'] ) ) {
			$assign_to      = $args['assign_to'];
			$assign_subject = "Assgined to you";
			$assign_body    = $template;
			$headers        = 'Content-Type: text/html; charset=UTF-8';
			wp_mail( $assign_to, $assign_subject, $assign_body, $headers );
		}
	}

	/**
	 * Add Comment function.
	 */
	public function cf_add_comment() {

		$commentList      = filter_input( INPUT_POST, "commentList" );
		$commentList      = html_entity_decode( $commentList );
		$commentList      = json_decode( $commentList, true );
		$list_of_comments = $commentList;

		// Get the assigned User Email.
		$user_email = '';
		$assign_to  = intval( $_POST['assignTo'] ); // get the assign to value
		if( isset( $assign_to ) && $assign_to > 0 ) {
			$user_data  = get_user_by( 'ID', $assign_to );
			$user_email = $user_data->user_email;
		}

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
			if( $assign_to > 0 ) {
				$superCareerData['assigned_to']            = $assign_to;
			}
		} else {
			$superCareerData                           = array();
			$superCareerData['comments'][ $timestamp ] = $arr;
			$superCareerData['commentedOnText']        = $commentList['commentedOnText'];
			if( $assign_to > 0 ) {
				$superCareerData['assigned_to']            = $assign_to;
			}

			update_post_meta( $current_post_id, 'th' . $metaId, get_current_user_id() );
		}
		update_post_meta( $current_post_id, $metaId, $superCareerData );

		$last_index = count($list_of_comments) - 1;
		$list_of_comments[$last_index]['timestamp'] = $timestamp;

		// Get assigned user data.
		$user_data = get_user_by( 'ID', $superCareerData['assigned_to'] );
		$assigned_to = [
			'ID'           => $user_data->ID,
			'display_name' => $user_data->display_name,
			'user_email'   => $user_data->user_email,
			'avatar'       => get_avatar_url( $user_data->ID, [ 'size' => 32 ] )
		];

		echo wp_json_encode( array(
			'dtTime'     => $dtTime,
			'timestamp'  => $timestamp,
			'assignedTo' => $assigned_to
		) );

		// Sending email.
		$this->cf_sent_email_to_commented_users( [
			'site_name'        => get_bloginfo( 'name' ),
			'commenter'        => $commentList['userName'],
			'thread'           => $commentList['thread'],
			'post_title'       => get_the_title( $current_post_id ),
			'post_edit_link'   => get_edit_post_link( $current_post_id ),
			'open_count'       => '',
			'resolved_count'   => '',
			'commented_text'   => $commentList['commentedOnText'],
			'list_of_comments' => $list_of_comments,
			'assign_to'        => $user_email
		] );
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
					$allowed_tags = [
							'a' => [ 'id' => [], 'title' => [], 'href' => [], 'target'=> [], 'style' => [], 'class' => [], 'data-email' => [], 'contenteditable' => [],
						]
					];
					$c['thread'] = wp_kses( $c['thread'], $allowed_tags );
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

					$html .= "<div class='user-comment'> " . $c['thread'] . "</div>
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
				'permission_callback' => '__return_true'
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
			$user_role   = implode( ', ', $user_info->roles );
			$profile_url = get_avatar_url( $user_info->user_email );
			$thread      = $val['thread'];
			$cstatus     = isset( $val['status'] ) ? $val['status'] : '';

			$edited_draft = isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '';

			$date = gmdate( $time_format . ' ' . $date_format, $t );

			if ( 'deleted' !== $cstatus ) {
				array_push( $userDetails,
					[
						'userName'    => $username,
						'userRole'    => $user_role,
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

		// Get assigned user data
		if( $superCareerData['assigned_to'] > 0 ) {
			$user_data = get_user_by( 'ID', $superCareerData['assigned_to'] );
			$assigned_to = [
				'ID'           => $user_data->ID,
				'display_name' => $user_data->display_name,
				'user_email'   => $user_data->user_email,
				'avatar'       => get_avatar_url( $user_data->ID, [ 'size' => 32 ] )
			];
		} else {
			$assign_to = null;
		}

		$data                    = array();
		$data['userDetails']     = $userDetails;
		$data['resolved']        = 'true' === $superCareerData['resolved'] ? 'true': 'false';
		$data['commentedOnText'] = $superCareerData['commentedOnText'];
		$data['assignedTo']      = $assigned_to;

		return rest_ensure_response( $data );

	}

	/**
	 * Fetch User Email List.
	 */
	public function cf_get_user_email_list() {
		// Get the current post id if not present then return.
		$post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
		if( $post_id <= 0 ) {
			return;
		}

		// WP User Query.
		$users = new WP_User_Query([
			'number' => 10,
			'role__not_in' => 'Subscriber'
		]);

		// Fetch out all user's email.
		$email_list   = [];
		$system_users = $users->get_results();

		foreach ( $system_users as $user ) {
			if( $user->has_cap( 'edit_post', $post_id ) ) {
				$email_list[] = [
					'ID'                => $user->ID,
					'role'              => implode( ', ', $user->roles ),
					'display_name'      => $user->first_name,
					'full_name'         => "{$user->first_name} {$user->last_name}",
					'user_email'        => $user->user_email,
					'avatar'            => get_avatar_url( $user->ID, [ 'size' => '32' ] ),
					'profile'           => admin_url( "/user-edit.php?user_id  ={ $user->ID}" ),
					'edit_others_posts' => $user->allcaps['edit_others_posts'],
				];
			}
		}

		// Sending Response.
		$response = $email_list;
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Fetch Matched User Email List.
	 */
	public function cf_get_matched_user_email_list() {
		// Get the current post id if not present then return.
		$post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
		if( $post_id <= 0 ) {
			return;
		}
		$niddle = filter_input( INPUT_POST, 'niddle', FILTER_SANITIZE_STRING );
		$niddle = substr( $niddle, 1 );
		if ( ! empty( $niddle ) && '@' !== $niddle ) {
			$users = new WP_User_Query([
				'search'         => $niddle . '*',
				'search_columns' => ['display_name'],
				'role__not_in'   => 'Subscriber'
			]);

			// Fetch out matched user's email.
			$email_list   = [];
			$system_users = $users->get_results();
			foreach ( $system_users as $user ) {
				if( $user->has_cap( 'edit_post', $post_id ) ) {
					$email_list[] = [
						'ID'                => $user->ID,
						'role'              => implode( ', ', $user->roles ),
						'display_name'      => $user->first_name,
						'full_name'         => "{$user->first_name} {$user->last_name}",
						'user_email'        => $user->user_email,
						'avatar'            => get_avatar_url( $user->ID, [ 'size' => '32' ] ),
						'edit_others_posts' => $user->allcaps['edit_others_posts'],
					];
				}
			}
			$response = $email_list;
		} else if ( '@' === $niddle ) {
			$this->cf_get_user_email_list();
		} else {
			$response = '';
		}
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Get the list of assignable users.
	 *
	 * @return void
	 */
	public function cf_get_assignable_user_list() {

		if( ! isset( $_POST['content'] ) || empty( $_POST['content'] ) ) {
			return;
		}
		$content      = $_POST['content'];
		$allowed_tags = [
				'a' => [ 'id' => [], 'title' => [], 'href' => [], 'target'=> [], 'style' => [], 'class' => [], 'data-email' => [], 'contenteditable' => [],
			]
		];

		$content = wp_kses( $content, $allowed_tags );

		$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
		preg_match_all( $pattern, $content, $matches );

		$user_emails = array_unique( $matches[0] ); // Remove duplicate entries if any.

		$results = [];
		if( count( $user_emails ) > 0 ) {
			foreach( $user_emails as $user_email ) {
				$user_data = get_user_by( 'email', $user_email );
				$results[] = [
					'ID'           => $user_data->ID,
					'display_name' => $user_data->display_name,
					'user_email'   => $user_data->user_email,
					'avatar'       => get_avatar_url( $user_data->ID ),
				];
			}
		}

		echo wp_json_encode( $results );
		wp_die();
	}
}
