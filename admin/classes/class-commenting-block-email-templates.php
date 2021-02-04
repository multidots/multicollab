<?php
/**
 * Provides the email templates.
 *
 * This file is used to create the related email templates.
 *
 * @link       #
 * @since      1.1.0
 *
 * @package    content-collaboration-inline-commenting.
 */

class Commenting_Block_Email_Templates {

	/**
	 * Comments Array.
	 */
	public $list_of_comments;

	/**
	 * User email addresses.
	 */
	public $users_emails;

	/**
	 * Limiting the characters of a string.
	 *
	 * @param string $string The string that is going to be limiting.
	 * @param integer $limit The limiting value.
	 *
	 * @return void
	 */
	public function cf_limit_characters( $string, $limit = 100 ) {
		return strlen( $string ) > $limit ? substr( $string, 0, $limit ) . '...' : $string;
	}

	/**
	 * Resolved Thread Email Template.
	 *
	 * @param array $args Data to be used in the template.
	 *
	 * @return void
	 */
	public function cf_email_resolved_thread( $args ) {
		$html                      = $args['html'];
		$p_title                   = $args['post_title'];
		$site_title                = $args['site_title'];
		$list_of_comments          = $args['list_of_comments'];
		$commented_on_text         = $args['commented_on_text'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];

		if ( ! empty( $list_of_comments ) && is_array( $list_of_comments ) ) {

			$headers      = array( 'Content-Type: text/html; charset=UTF-8' );
			$html         .= "<div class='commented_text'>" . $commented_on_text . "</div>";

			// Get comments loop.
			$this->list_of_comments = $list_of_comments;
			$html                   .= $this->cf_email_get_comments_loop();

			$html .= '<div class="cf-marked-resolved-by">';
			$html .= '<span class="icon-resolved"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22">
							  <g id="Group_22" data-name="Group 22" transform="translate(1 1)">
							    <circle id="Ellipse_4" data-name="Ellipse 4" cx="10" cy="10" r="10" fill="none" stroke="#6ac359" stroke-width="2"/>
							    <path id="Path_7" data-name="Path 7" d="M93.92,119.6l-3.593-3.664,1.353-1.327,2.252,2.3,5.621-5.621,1.34,1.34Z" transform="translate(-85.327 -105.288)" fill="#6ac359"/>
							  </g>
							</svg></span>';
			$html .= __( 'Marked as resolved by ', 'content-collaboration-inline-commenting' ) . '<a href="mailto:' . esc_attr( $current_user_email ) . '" title="' . esc_attr( $current_user_display_name ) . '" target="_blank"> ' . esc_html( $current_user_display_name ) . ' </a>' . '</div>';
			$html .= '</div>'; // .comment-box-body end
			$html .= '</div>'; // .comment-box end

			$users_emails = array_unique( $this->users_emails );
			$key = array_search( $current_user_email, $users_emails, true );
			if ( $key !== false ) {
				unset( $users_emails[ $key ] );
			}

			// Notify Site Admin if setting enabled.
			$users_emails = $this->cf_email_notify_siteadmin( $users_emails );

			// Limit the page and site titles for Subject.
			$r_subject = $this->cf_email_prepare_subject( 'Comment Resolved', $p_title, $site_title );

			wp_mail( $users_emails, $r_subject, $html, $headers ); // phpcs:ignore
		}
	}

	/**
	 * Comments loop for Email Templates.
	 *
	 * @return string HTML for comments loop.
	 */
	private function cf_email_get_comments_loop() {
		ob_start();
		require_once( COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-email-comments.php' ); // phpcs:ignore

		return ob_get_clean();
	}

	/**
	 * Prepare Subject line and limit the page and site titles for Subject.
	 *
	 * @param string $pre_subject The subject prefix.
	 * @param string $p_title The post title.
	 * @param string $site_title The site title.
	 *
	 * @return string The subject for email.
	 */
	private function cf_email_prepare_subject( $pre_subject, $p_title, $site_title ) {
		$site_title   = $this->cf_limit_characters( $site_title, 20 );
		$post_subject = ! empty( $p_title ) ? $this->cf_limit_characters( $p_title, 30 ) . ' — ' . $site_title : $site_title;

		return sprintf( __( '%s — %s', 'content-collaboration-inline-commenting' ), $pre_subject, $post_subject );
	}

	/**
	 * @param array $users_emails List of emails.
	 *
	 * @return array Updated list of emails.
	 */
	private function cf_email_notify_siteadmin( $users_emails ) {
		$cf_admin_notif = get_option( 'cf_admin_notif' );
		if ( '1' === $cf_admin_notif ) {
			$users_emails[] = get_option( 'admin_email' );
		}

		return $users_emails;
	}

	public function cf_find_mentioned_emails( $str ) {
		$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
		preg_match_all( $pattern, $str, $matches );

		return array_unique( $matches[0] );
	}

	/**
	 * Add Comment / Reply Comment Email Template.
	 *
	 * @param array $args Contains all keys related to send the email.
	 *
	 * @return void
	 */
	public function cf_email_new_comments( $args ) {
		$html                      = '';
		$mentioned_html            = $args['html'];
		$elid                      = $args['elid'];
		$post_id                   = $args['post_ID'];
		$p_title                   = $args['post_title'];
		$assign_to                 = $args['assign_to'];
		$site_title                = $args['site_title'];
		$new_comments              = $args['new_comments'];
		$post_edit_link            = $args['post_edit_link'];
		$list_of_comments          = $args['list_of_comments'];
		$commented_on_text         = $args['commented_on_text'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];

		$find_mentions     = '';
		$find_new_mentions =  '';
		foreach ( $list_of_comments as $timestamp => $comment ) {
			if( 'publish' === $comment['status'] ) {
				$find_mentions .= $comment['thread'];
				if( in_array( $timestamp, $new_comments, true ) ) {
					$find_new_mentions .= $comment['thread'];
				}
			}
		}

		// Grab all the emails mentioned in the current board.
		$users_emails      = array_unique( $this->users_emails );
		$mentioned_emails  = $this->cf_find_mentioned_emails( $find_mentions );

		if( null !== $users_emails ) {
			$mentioned_emails = array_merge( $mentioned_emails, $users_emails );
		}
		$email_list = array_unique( $mentioned_emails );



		// Grab only newly mentioned email of the board.
		$newly_mentioned_emails = $this->cf_find_mentioned_emails( $find_new_mentions );

		// Unset the newly mentioned emails from the list.
		foreach( $newly_mentioned_emails as $newly_mentioned ) {
			$key = array_search( $newly_mentioned, $email_list, true );
			if( $key !== false ) {
				unset( $email_list[$key] );
			}
		}

		// Removed current user email from the list.
		if( ! empty( $current_user_email ) ) {
			$key = array_search( $current_user_email, $email_list, true );
			if( $key !== false ) {
				unset( $email_list[$key] );
			}
		}

		if ( ! empty( $list_of_comments ) ) {
			// Get comments loop.
			$this->list_of_comments = $list_of_comments;
			$comment_list_html      = $this->cf_email_get_comments_loop();

			$assigned_to_who_html = '';
			if ( ! empty( $assign_to ) ) {
				$assigned_user   = get_user_by( 'ID', $assign_to );
				$assigned_to_who_html = "
                <div class='comment-assigned-to'>
					<span class='icon-assign'>
						<svg id='Group_31' data-name='Group 31' xmlns='http://www.w3.org/2000/svg' width='19.644' height='20' viewBox='0 0 19.644 20'>
							<g id='Group_28' data-name='Group 28' transform='translate(2.21)'>
							<path id='Path_11' data-name='Path 11' d='M149.786,160.469a10.107,10.107,0,0,1-7.123-2.907.885.885,0,0,1,0-1.279.885.885,0,0,1,1.275,0,8.254,8.254,0,0,0,5.78,2.439,7.905,7.905,0,0,0,5.776-2.436,8.236,8.236,0,0,0,0-11.632,8.253,8.253,0,0,0-5.779-2.438,8.032,8.032,0,0,0-5.779,2.438,1.047,1.047,0,0,1-1.255.018.771.771,0,0,1-.29-.564.949.949,0,0,1,.269-.73,9.992,9.992,0,0,1,7.126-2.909,10.107,10.107,0,0,1,7.124,2.907,9.761,9.761,0,0,1,2.912,7.128,10.1,10.1,0,0,1-2.907,7.124A9.619,9.619,0,0,1,149.786,160.469Z' transform='translate(-142.388 -140.469)' fill='#6ac359'/>
							</g>
							<g id='Group_29' data-name='Group 29' transform='translate(0 9.055)'>
							<path id='Path_12' data-name='Path 12' d='M141.088,151.342a.909.909,0,1,1,0-1.818h5.727a.909.909,0,1,1,0,1.818Z' transform='translate(-140.178 -149.524)' fill='#6ac359'/>
							</g>
							<g id='Group_30' data-name='Group 30' transform='translate(4.564 4.705)'>
							<path id='Path_13' data-name='Path 13' d='M148.645,155.834a.844.844,0,0,1-.638-.271.884.884,0,0,1,0-1.276l2.945-2.945h-5.3a.909.909,0,0,1,0-1.818h5.159l-2.8-2.8a.884.884,0,0,1,0-1.276.884.884,0,0,1,1.276,0l4.492,4.492a.8.8,0,0,1,.2.566.845.845,0,0,1-.271.639l-4.421,4.42A.841.841,0,0,1,148.645,155.834Z' transform='translate(-144.742 -145.174)' fill='#6ac359'/>
							</g>
						</svg>
					</span>
                    ".__( 'Assigned to', 'content-collaboration-inline-commenting' )." <a href='mailto:" . sanitize_email( $assigned_user->user_email ) . "' title='" . esc_attr( $assigned_user->display_name ) . "' class='commenter-name'>@" . esc_html( $assigned_user->display_name ) . "</a>
                </div>
            	";
			}

			$post_title_html = '';
			if ( ! empty( $args['post_title'] ) ) {
				$post_title_html .= "<h2 class='comment-page-title'><a href='" . esc_url( $post_edit_link ) . "' target='_blank'>" . esc_html( $p_title ) . "</a></h2>";
			}

			$comment_icon_html = "<span class='icon-comment'>
				<svg xmlns='http://www.w3.org/2000/svg' width='36.226' height='43.02' viewBox='0 0 36.226 43.02'>
					<g id='Group_2' data-name='Group 2' transform='translate(-36.242 1.019)'>
						<path id='Path_1' data-name='Path 1' d='M64.607,30.769,52.29,40l0-5.88-1.37-.279a17.1,17.1,0,1,1,13.683-3.072Z' transform='translate(0 0)' fill='none' stroke='#4b1bce' stroke-width='2'/>
					</g>
				</svg>
			</span>";

			$email_css .= "
			<style>
				.comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
				.comment-box *{-webkit-box-sizing:border-box;box-sizing:border-box;}
				.comment-box a{color:#4B1BCE;text-decoration:none;}
				.comment-box .comment-box-header{margin-bottom:30px;border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
				.comment-box .comment-box-header p{margin:0 0 20px;}
				.comment-box .comment-box-header .comment-page-title{font-size:20px;margin:0;}
				.comment-box .comment-box-header a{color:#4B1BCE;display:inline-block;}
				.comment-box .comment-page-title a{text-decoration:underline;font-size:20px;}
				.comment-box .comment-box-wrap{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-box-wrap:last-child{margin-bottom:0;}
				.comment-box .avatar{width:40px;margin-right:10px;}
				.comment-box .avatar img{max-width:100%;border-radius:50%;}
				.comment-box .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
				.comment-box .comment-box-wrap .commenter-name-role{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-box-wrap .commenter-name{font-size:18px;font-family:Roboto,Arial,sans-serif;margin:0 7px 0 0;color:#141414;font-weight:600;}
				.comment-box .comment-box-wrap .commenter-role{font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;margin-right:10px;}
				.comment-box .comment{font-family:Arial,serif;font-size:14px;color:#4C5056;}
				.comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
				.comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#4C5056;}
				.comment-box .comment-assigned-to{margin-bottom:20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;margin-left:5px;}
				.comment-box .comment-assigned-to .icon-assign{margin-right:5px;line-height:1;}
				.comment-box ul{margin:0 0 20px;padding:0;list-style:none;}
				.comment-box ul li{margin-bottom:20px;}
				.comment-box ul li:last-child{margin-bottom:10px;}
				.comment-box .head-with-icon{margin:0 0 20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;font-family:Roboto,Arial,sans-serif;font-weight:600;}
				.comment-box .head-with-icon .icon-comment{margin-right:10px;line-height:1;}
				.comment-box .head-with-icon .icon-resolved{margin-right:10px;}
				.comment-box .head-with-icon h3{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .cf-marked-resolved-by{margin:0 10px 20px 0;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
				.comment-box .cf-marked-resolved-by .icon-resolved{margin-right:5px;line-height:1;}
				.comment-box .cf-marked-resolved-by a{margin-left:5px;}
				.comment-box.new-comment .comment-list li:last-child .commenter-name-role:after{content:'New';padding:5px 10px;background-color:#4B1BCE;color:#fff;font-size:12px;}
				.comment-box .view_reply{margin:10px 0;}
				.comment-box .view_reply_btn{display:inline-block;padding:15px 25px;font-size:20px;background-color:#4B1BCE;border-radius:8px;color:#fff;}
				.comment-box .view_reply_btn a{text-decoration:underline;color:#fff;}
				@media (max-width:1400px){.comment-box{width:90%;}}
			</style>";

			$html .= "
			{$email_css}
            <div class='comment-box new-comment'>
                <div class='comment-box-header'>
                    {$post_title_html}
                </div>
                <div class='comment-box-body'>
                    <h2 class='head-with-icon'>
                        {$comment_icon_html}
                        ".__( 'Comments', 'content-collaboration-inline-commenting' )."
                    </h2>
                    <div class='commented_text'>" . esc_html( $commented_on_text ) . "</div>
                    {$assigned_to_who_html}
                    {$comment_list_html}
                    <div class='view_reply'>
                        <div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "'>".__( 'Click here to view this comment', 'content-collaboration-inline-commenting' )."</a></div>
                    </div>
                </div>
            </div>
			";

			$mentioned_html .= "
			{$email_css}
            <div class='comment-box new-comment'>
                <div class='comment-box-header'>
                    <p><span class='commenter-name'>" . esc_html( $current_user_display_name ) . "</span> - mentioned you in a comment in the following page.</p>
					{$post_title_html}
                </div>
                <div class='comment-box-body'>
					<h2 class='head-with-icon'>
						{$comment_icon_html}
						".__( 'Comments', 'content-collaboration-inline-commenting' )."
					</h2>
                    <div class='commented_text'>" . esc_html( $commented_on_text ) . "</div>
                    {$assigned_to_who_html}
                    {$comment_list_html}
                    <div class='view_reply'>
					<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "'>".__( 'Click here to view this comment', 'content-collaboration-inline-commenting' )."</a></div>
                    </div>
                </div>
            </div>
			";

			$headers = 'Content-Type: text/html; charset=UTF-8';

			// Sent email to assign user once & rest of the mentioned users.
			$el_obj = get_post_meta( $post_id, "_{$elid}", true );
			if( ! empty( $el_obj ) ) {
				if( $el_obj['assigned_to'] > 0 && $el_obj['sent_assigned_email'] === false ) {
					$assigned_user = get_user_by( 'ID', $el_obj['assigned_to'] );
					if ( ! empty( $assigned_user ) ) {
						$assign_to = $assigned_user->user_email;

						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'Assigned to you', $p_title, $site_title );

						wp_mail( $assign_to, $subject, $mentioned_html, $headers ); // phpcs:ignore
					}
					// Updating after sending the email.
					$el_obj['sent_assigned_email'] = true;
					update_post_meta( $post_id, "_{$elid}", $el_obj );

					// Remove assigned email from the list.
					$key = array_search( $assigned_user->user_email, $email_list, true );
					if( $key !== false ) {
						unset( $email_list[$key] );
					}

					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );

					$key = array_search( $assigned_user->user_email, $newly_mentioned_emails, true );
					if( $key !== false ) {
						unset( $newly_mentioned_emails[$key] );
					}

					// Sent email to newly mentioned users.
					if( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'You have been mentioned', $p_title, $site_title );
						wp_mail( $newly_mentioned_emails, $subject, $mentioned_html, $headers ); // phpcs:ignore

					}

					if( ! empty( $newly_mentioned_emails ) || ! empty( $assign_to ) ) {
						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
							wp_mail( $email_list, $subject, $html, $headers ); // phpcs:ignore
						}
					}
				} else if( $el_obj['assigned_to'] > 0 && $el_obj['sent_assigned_email'] === true ) {

					// Remove assigned email from the list.
					$assigned_user = get_user_by( 'ID', $el_obj['assigned_to'] );
					$email_list[]  = $assigned_user->user_email;

					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );

					$email_list = array_diff( $email_list, $newly_mentioned_emails );

					// Removed current user email from the list.
					if( ! empty( $current_user_email ) ) {
						$key = array_search( $current_user_email, $email_list );
						if( $key !== false ) {
							unset( $email_list[$key] );
						}
					}

					// Sent email to newly mentioned users.
					if( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'You have been mentioned', $p_title, $site_title );
						wp_mail( $newly_mentioned_emails, $subject, $mentioned_html, $headers ); // phpcs:ignore

						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
							wp_mail( $email_list, $subject, $html, $headers ); // phpcs:ignore
						}
					}
				} else {
					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );

					// Sent email to newly mentioned users.
					if( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'You have been mentioned', $p_title, $site_title );
						wp_mail( $newly_mentioned_emails, $subject, $mentioned_html, $headers ); // phpcs:ignore

						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
							wp_mail( $email_list, $subject, $html, $headers ); // phpcs:ignore
						}
					}
				}
			}
		}
	}
}
