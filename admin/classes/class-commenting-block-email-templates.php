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
			$html         .= "<div class='commented_text' style='background-color:#F8F8F8;border:1px solid #eee;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#4C5056;'>" . $commented_on_text . "</div>";

			// Get comments loop.
			$this->list_of_comments = $list_of_comments;
			$html                   .= $this->cf_email_get_comments_loop();

			$html .= '<table class="cf-marked-resolved-by" style="padding-bottom:10px"><tr><td valign="middle">';
			$html .= '<span class="icon-resolved" style="padding-right:5px;line-height:1;vertical-align:middle;"><img src="'.esc_url_raw( COMMENTING_BLOCK_URL . 'admin/images/icon-check.png' ).'" alt="resolved by" /></span>';
			$html .= '<span>' . __( 'Marked as resolved by', 'content-collaboration-inline-commenting' ) . '</span>';
			$html .= '<a href="mailto:' . esc_attr( $current_user_email ) . '" title="' . esc_attr( $current_user_display_name ) . '" target="_blank" style="color:#4B1BCE;text-decoration:none;padding-left:5px;">' . esc_html( $current_user_display_name ) . '</a>';
			$html .= '</td></tr></table>';
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
		$admin_notified = false;

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
                <div class='comment-assigned-to' style='padding-bottom:20px;'>
					<span class='icon-assign' style='padding-right:5px;line-height:1;vertical-align:middle;'>
						<img src='".esc_url_raw( COMMENTING_BLOCK_URL . 'admin/images/icon-assign.png' )."' alt='Assigned to' />
					</span>
                    ".__( 'Assigned to', 'content-collaboration-inline-commenting' )."<a href='mailto:" . sanitize_email( $assigned_user->user_email ) . "' title='" . esc_attr( $assigned_user->display_name ) . "' class='commenter-name' style='color:#4B1BCE;text-decoration:none;padding-left:5px;vertical-align:middle;'>@" . esc_html( $assigned_user->display_name ) . "</a>
                </div>
            	";
			}

			$post_title_html = '';
			if ( ! empty( $args['post_title'] ) ) {
				$post_title_html .= "<h2 class='comment-page-title' style='font-size:20px;margin:0;'><a href='" . esc_url( $post_edit_link ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;'>" . esc_html( $p_title ) . "</a></h2>";
			}

			$comment_icon_html = "<img src='".esc_url_raw( COMMENTING_BLOCK_URL . 'admin/images/icon-comment.png' )."' alt='Icon Comment' style='vertical-align:middle;padding-right:10px;' />";

			$html .= "
            <div class='comment-box new-comment' style='background:#fff;width:90%;max-width:1024px;font-family:Arial,serif;padding-top:40px;'>
                <div class='comment-box-header' style='margin-bottom:30px;border:1px solid #eee;border-radius:20px;padding:30px;'>
                    {$post_title_html}
                </div>
                <div class='comment-box-body' style='border:1px solid #eee;border-radius:20px;padding:30px;'>
                    <h2 class='head-with-icon' style='margin:0;padding-bottom:20px;font-family:Roboto,Arial,sans-serif;font-weight:600;'>
                        {$comment_icon_html}
                        ".__( 'Comments', 'content-collaboration-inline-commenting' )."
                    </h2>
                    <div class='commented_text' style='background-color:#F8F8F8;border:1px solid #eee;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#4C5056;'>" . esc_html( $commented_on_text ) . "</div>
                    {$assigned_to_who_html}
                    {$comment_list_html}
                    <div class='view_reply' style='padding:10px 0;'>
                        <div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding:15px 25px;font-size:20px;background-color:#4B1BCE;border-radius:8px;color:#fff;text-decoration:none;'>".__( 'Click here to view this comment', 'content-collaboration-inline-commenting' )."</a></div>
                    </div>
                </div>
            </div>
			";

			$mentioned_html .= "
            <div class='comment-box new-comment' style='background:#fff;width:90%;max-width:1024px;font-family:Arial,serif;padding-top:40px;'>
                <div class='comment-box-header' style='margin-bottom:30px;border:1px solid #eee;border-radius:20px;padding:30px;'>
                    <p style='padding-bottom:20px;margin:0;'><span class='commenter-name'>" . esc_html( $current_user_display_name ) . "</span> - mentioned you in a comment in the following page.</p>
					{$post_title_html}
                </div>
                <div class='comment-box-body' style='border:1px solid #eee;border-radius:20px;padding:30px;'>
					<h2 class='head-with-icon' style='margin:0;padding-bottom:20px;font-family:Roboto,Arial,sans-serif;font-weight:600;'>
						{$comment_icon_html}
						".__( 'Comments', 'content-collaboration-inline-commenting' )."
					</h2>
                    <div class='commented_text' style='background-color:#F8F8F8;border:1px solid #eee;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#4C5056;'>" . esc_html( $commented_on_text ) . "</div>
                    {$assigned_to_who_html}
                    {$comment_list_html}
                    <div class='view_reply' style='padding:10px 0;'>
					<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding:15px 25px;font-size:20px;background-color:#4B1BCE;border-radius:8px;color:#fff;text-decoration:none;'>".__( 'Click here to view this comment', 'content-collaboration-inline-commenting' )."</a></div>
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
						$assigned_to_email = $assigned_user->user_email;

						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'Assigned to you', $p_title, $site_title );

						wp_mail( $assigned_to_email, $subject, $mentioned_html, $headers ); // phpcs:ignore
					}
					// Updating after sending the email.
					$el_obj['sent_assigned_email'] = true;
					update_post_meta( $post_id, "_{$elid}", $el_obj );

					// Remove assigned email from the list.
					$key = array_search( $assigned_user->user_email, $email_list, true );
					if( $key !== false ) {
						unset( $email_list[$key] );
					}

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

					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );
					// Sent email to all users.
					if ( ! empty( $email_list ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
						wp_mail( $email_list, $subject, $html, $headers ); // phpcs:ignore
						$admin_notified = true;
					}
				} else if( $el_obj['assigned_to'] > 0 && $el_obj['sent_assigned_email'] === true ) {
					// Remove assigned email from the list.
					$assigned_user = get_user_by( 'ID', $el_obj['assigned_to'] );
					$email_list[]  = $assigned_user->user_email;

					$email_list = array_diff( $email_list, $newly_mentioned_emails );

					// Removed current user email from the list.
					if( ! empty( $current_user_email ) ) {
						$key = array_search( $current_user_email, $email_list, true );
						if( $key !== false ) {
							unset( $email_list[$key] );
						}
					}

					// Sent email to newly mentioned users.
					if( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( 'You have been mentioned', $p_title, $site_title );
						wp_mail( $newly_mentioned_emails, $subject, $mentioned_html, $headers ); // phpcs:ignore

						// Notify Site Admin if setting enabled.
						$email_list = $this->cf_email_notify_siteadmin( $email_list );

						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
							wp_mail( $email_list, $subject, $html, $headers ); // phpcs:ignore
							$admin_notified = true;
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
							$admin_notified = true;
						}
					}
				}

				// Send Email to admin if no user is mentioned.
				if( false === $admin_notified ) {
					if( empty( $newly_mentioned_emails ) ) {
						// Notify Site Admin if setting enabled.
						$cf_admin_notif = get_option( 'cf_admin_notif' );
						if ( '1' === $cf_admin_notif ) {
							$admin_email = get_option( 'admin_email' );
						}

						if( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( 'New Comment', $p_title, $site_title );
							wp_mail( $admin_email, $subject, $html, $headers ); // phpcs:ignore
						}
					}
				}
			}
		}
	}
}
