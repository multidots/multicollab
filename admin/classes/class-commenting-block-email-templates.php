<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
	 * @param string  $string The string that is going to be limiting.
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
		$p_title                   = html_entity_decode( $args['post_title'] );
		$site_title                = $args['site_title'];
		$list_of_comments          = $args['list_of_comments'];
		$commented_on_text         = $args['commented_on_text'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];
		$blockType                 = $args['block_type'];
		$post_edit_link            = $args['post_edit_link'];

		$url = $this->cf_get_block_type( $blockType );
		if ( '' !== $url ) {
			$block_type_html = "<img src='" . $url . "' alt='Block Type' style='vertical-align:middle;padding-right:10px;width:15px;' />";
		}

		if ( ! empty( $list_of_comments ) && is_array( $list_of_comments ) ) {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			$html   .= "<div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;word-wrap: break-word;'>" . isset( $block_type_html ) . $commented_on_text . '</div>';

			// Get comments loop.
			$this->list_of_comments = $list_of_comments;
			$html                  .= $this->cf_email_get_comments_loop();
			$http_host        = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS );
			$html .= '<table class="cf-marked-resolved-by" style="padding-bottom:10px"><tr><td valign="middle">';
			$html .= '<span class="icon-resolved" style="padding-right:5px;line-height:1;vertical-align:middle;"><img src="' . esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/icon-check.png' ) . '" alt="resolved by"  style="width: 16px;"/></span>';
			$html .= '<span><em style="color: #191e23;">' . __( 'Marked as resolved by', 'content-collaboration-inline-commenting' ) . '</em></span>';
			$html .= '<a href="mailto:' . esc_attr( $current_user_email ) . '" title="' . esc_attr( $current_user_display_name ) . '" target="_blank" style="color:#4B1BCE;text-decoration:none;padding-left:5px;"><em>' . esc_html( $current_user_display_name ) . '</em></a>';
			$html .= '</td></tr></table>';
			$html .= '  </div>';
			$html .= '<div>
			<p style="color: #5f6368; font-size: 12px; line-height: 16px; letter-spacing: .3px; margin-bottom: 0;">' . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . '<a style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;" href="' . esc_url( 'https://www.notion.so/mdnotes/Getting-Started-5103a8f3be084fc0880039161a49e23a' ) . '">' . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . '</a>' . __( ' to notify you of a collaboration activity on this post of ', 'content-collaboration-inline-commenting' ) . '<span><a href="' . esc_url( get_site_url() ) . '" style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;">' . esc_html( $http_host ) . '</a>.</span></p>
			<p style="color: #5f6368; font-size: 12px; line-height: 16px; letter-spacing: .3px; margin: 0;">' . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . '</p>';
			$html .= '</div>'; // .comment-box-body end
			$html .= '</div>'; // .comment-box end

			$users_emails = ! empty( $this->users_emails ) ? array_unique( $this->users_emails ) : array();
			$key          = array_search( $current_user_email, $users_emails, true );
			if ( $key !== false ) {
				unset( $users_emails[ $key ] );
			}

			// Notify Site Admin if setting enabled.
			$users_emails = $this->cf_email_notify_siteadmin( $users_emails );

			// Limit the page and site titles for Subject.
			$r_subject = $this->cf_email_prepare_subject( __( 'Comment Resolved', 'content-collaboration-inline-commenting' ), $p_title, $site_title );

            wp_mail($users_emails, $r_subject, $html, $headers); // phpcs:ignore
		}
	}

	/**
	 * Comments loop for Email Templates.
	 *
	 * @return string HTML for comments loop.
	 */
	private function cf_email_get_comments_loop() {
		ob_start();

        require(COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-email-comments.php'); // phpcs:ignore

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

		return sprintf( __( '%1$s — %2$s', 'content-collaboration-inline-commenting' ), $pre_subject, $post_subject );
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

	/**
	 * Filter out the actuall email address to sent the email.
	 *
	 * @param string $str
	 * @return array
	 */
	public function cf_find_mentioned_emails( $str ) {
		$pattern = '/data-email=\"([a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?)\"/i';
		preg_match_all( $pattern, $str, $matches );
		$refined_user_email = array();
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $user_email ) {
				$refined_user_email[] = $user_email;
			}
		}

		return array_unique( $refined_user_email );
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
		$mentioned_html            = isset( $args['html'] ) ? $args['html'] : '';
		$elid                      = $args['elid'];
		$post_id                   = $args['post_ID'];
		$p_title                   = html_entity_decode( $args['post_title'] );
		$assign_to                 = $args['assign_to'];
		$site_title                = $args['site_title'];
		$new_comments              = $args['new_comments'];
		$post_edit_link            = $args['post_edit_link'];
		$list_of_comments          = $args['list_of_comments'];
		$commented_on_text         = $args['commented_on_text'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];
		$blockType                 = $args['block_type'];

		$url = $this->cf_get_block_type( $blockType );
		if ( '' !== $url ) {
			$block_type_html = "<img src='" . $url . "' alt='Block Type' style='vertical-align:middle;padding-right:10px;width:15px;' />";
		}

		$admin_notified = false;

		$find_mentions     = '';
		$find_new_mentions = '';

		foreach ( $list_of_comments as $timestamp => $comment ) {
			if ( isset( $comment['status'] ) && 'publish' === $comment['status'] ) {
				$find_mentions .= $comment['thread'];
				if ( in_array( $timestamp, $new_comments, true ) ) {
					$find_new_mentions .= $comment['thread'];
				}
			}
		}

		// Grab all the emails mentioned in the current board.
		$mentioned_emails = $this->cf_find_mentioned_emails( $find_mentions );
		$users_emails     = ! empty( $this->users_emails ) ? array_unique( $this->users_emails ) : '';

		if ( ! empty( $users_emails ) ) {
			$mentioned_emails = array_merge( $mentioned_emails, $users_emails );
		}

		$email_list = ! empty( $mentioned_emails ) ? array_unique( $mentioned_emails ) : array();

		// Grab only newly mentioned email of the board.
		$newly_mentioned_emails = $this->cf_find_mentioned_emails( $find_new_mentions );
		$mentioned_user_name = array();
		// Unset the newly mentioned emails from the list.
		if ( ! empty( $newly_mentioned_emails ) ) {
			foreach ( $newly_mentioned_emails as $newly_mentioned ) {
				if ( ! empty( $email_list ) ) {
					$key = array_search( $newly_mentioned, $email_list, true );
					if ( $key !== false ) {
						unset( $email_list[ $key ] );
					}
				}
				$mentioned_user = get_user_by( 'email', $newly_mentioned );
				array_push( $mentioned_user_name, $mentioned_user->display_name );
			}
		}

		// Removed current user email from the list.
		if ( ! empty( $current_user_email ) ) {
			if ( ! empty( $email_list ) ) {
				$key = array_search( $current_user_email, $email_list, true );
				if ( $key !== false ) {
					unset( $email_list[ $key ] );
				}
			}
		}

		if ( ! empty( $list_of_comments ) ) {
			// Get comments loop.
			$this->list_of_comments = $list_of_comments;
			$comment_list_html      = $this->cf_email_get_comments_loop();
			$assigned_to_who_html   = '';
			if ( ! empty( $assign_to ) ) {
				$assigned_user        = get_user_by( 'ID', $assign_to );
				$assigned_to_who_html = "
                <div class='comment-assigned-to' style='padding-bottom:20px;padding-top: 10px;'>
					<span class='icon-assign' style='padding-right:5px;line-height:1;vertical-align:middle;'>
						<img src='" . esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/assigned-user.png' ) . "' alt='Assigned to' />
					</span>
                    " . __( 'Assigned to', 'content-collaboration-inline-commenting' ) . "<a href='mailto:" . sanitize_email( $assigned_user->user_email ) . "' title='" . esc_attr( $assigned_user->display_name ) . "' class='commenter-name' style='color:#4B1BCE;text-decoration:none;padding-left:5px;'>@" . esc_html( $assigned_user->display_name ) . '</a>
                </div>
            	';

				$slack_assign_to_image = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Assigned-2.png' );
				$slack_assign_to_text  = 'Assigned to ';
				$slack_assign_to_link  = '<mailto:' . sanitize_email( $assigned_user->user_email ) . '|' . esc_html( $assigned_user->display_name ) . '>';
			}
			$http_host        = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS );
			$site_title_html  = '';
			$site_title_html .= "<h2 class='comment-page-web' style='margin:0;display:inline-block;'><a href='" . esc_url( get_site_url() ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;word-wrap: break-word;'>" . esc_html( $http_host ) . '</a></h2>';
			$arrow_svg = "<span style='vertical-align: middle;padding-right: 5px;'><img src='" . esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/email-arrow.png' ) . "' alt='Arrow' width='22' height='13' /></span>";
			$post_title_html = '';

			if ( ! empty( $args['post_title'] ) ) {
				$post_title_html .= $arrow_svg . "<h2 class='comment-page-title' style='margin:0;display:inline-block;'><a href='" . esc_url( $post_edit_link ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;'>" . esc_html( wp_trim_words( $p_title, 3, '...' ) ) . '</a></h2>';
			}

			$comment_icon_html = '';
			$html             .= "
            <div class='comment-box new-comment' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
                <div class='comment-box-header' style='margin-bottom:30px;'>
				<p style='font-size:18px;color:#000;line-height:normal;margin:0;;margin-bottom:20px'>
                <span class='commenter-name' style='text-transform: capitalize;font-weight: 700;display: inline-block;margin-bottom:10px;'>" . esc_html( $current_user_display_name ) . '</span>' . __( ' added you in a comment on this post.', 'content-collaboration-inline-commenting' ) . "
            	</p>
					<div style='display: flex;align-items: center;justify-content:space-between;flex-wrap: wrap;margin-bottom:20px;gap:20px;'>
						<div class='comment-box-header-right'>
							{$site_title_html}
							{$post_title_html}
						</div>
						<div class='view_reply' style='
							margin-right: 10px;
							display: inline-block;cursor: pointer;margin-left:auto;'>
							<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding: 9.5px 30px;
							background-color: #4B1BCE;border-radius: 8px;font-size: 16px;text-decoration: none;color: #fff;'>" . __( 'Open', 'content-collaboration-inline-commenting' ) . "</a>
						</div>
					</div>
                </div>
                <div class='comment-box-body' style='border:1px solid #E2E4E7;border-radius:20px;padding:30px;'>
                    <h2 class='head-with-icon' style='margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;'>
                        {$comment_icon_html}
                        " . __( 'Comments', 'content-collaboration-inline-commenting' ) . "
                    </h2>
                    <div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;'>" . isset( $block_type_html ) . esc_html( $commented_on_text ) . "</div>
                    {$assigned_to_who_html}
                    {$comment_list_html}
                </div>
				<div>
					<p style='color: #5f6368;font-size: 12px;
					line-height: 16px;letter-spacing: .3px;margin-bottom:0;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.notion.so/mdnotes/Getting-Started-5103a8f3be084fc0880039161a49e23a' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
					" . __( 'to notify you of a collaboration activity on this post of', 'content-collaboration-inline-commenting' ) . " <span><a href='" . esc_url( get_site_url() ) . "' style='color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;'>" . esc_html( $http_host ) . "</a>.</span></p>
					<p style='color: #5f6368;font-size: 12px; line-height: 16px; letter-spacing: .3px;margin:0;'>" . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . "</p>
				</div>

            </div>
			";

			$mentioned_html .= "
			<div class='comment-box new-comment' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
				<div class='comment-box-header' style='margin-bottom:30px;'>
					<p style='font-size:18px;color:#000;line-height:normal;margin:0;margin-bottom:20px'><span class='commenter-name' style='text-transform: capitalize;font-weight: 700;display: inline-block;'>" . esc_html( $current_user_display_name ) . '</span>';

			if ( ! empty( $assigned_user ) ) {
				// If $assigned_user is not empty, display assigned text.
				$mentioned_html .= __( ' assigned you in a comment on this post.', 'content-collaboration-inline-commenting' );
			} elseif ( ! empty( $newly_mentioned_emails ) ) {
				// If $newly_mentioned_emails is not empty, display mentioned text.
				$mentioned_html .= __( ' mentioned <span class="mentioed-name" style="text-transform: capitalize;font-weight: 700;display: inline-block;">'. implode( ', ', $mentioned_user_name ) .'</span> in a comment on this post.', 'content-collaboration-inline-commenting' );
			}

			$mentioned_html .= "
						</p>
						<div style='display: flex;align-items: center;justify-content:space-between;flex-wrap: wrap;gap:20px;'>
							<div class='comment-box-header-right'>
								{$site_title_html}
								{$post_title_html}
							</div>
							<div class='view_reply' style='margin-right: 10px;display: inline-block;cursor: pointer;margin-left:auto;'>
								<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding: 9.5px 30px;background-color: #4B1BCE;border-radius: 8px;font-size: 16px;text-decoration: none;color: #fff;'>" . __( 'Open', 'content-collaboration-inline-commenting' ) . "</a></div>
							</div>
						</div>
					</div>
					<div class='comment-box-body' style='border:1px solid #E2E4E7;border-radius:20px;padding:30px;'>
						<h2 class='head-with-icon' style='margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;'>
							{$comment_icon_html}
							" . __( 'Comments', 'content-collaboration-inline-commenting' ) . "
						</h2>
						<div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;'>" . isset( $block_type_html ) . esc_html( $commented_on_text ) . "</div>
						{$assigned_to_who_html}
						{$comment_list_html}
					</div>
					<div>
						<p style='color: #5f6368;font-size: 12px;line-height: 16px;letter-spacing: .3px;margin-bottom:0;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.notion.so/mdnotes/Getting-Started-5103a8f3be084fc0880039161a49e23a' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
							" . __( 'to notify you of a collaboration activity on this post of', 'content-collaboration-inline-commenting' ) . " <span><a href='" . esc_url( get_site_url() ) . "' style='color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;'>" . esc_html( $http_host ) . "</a>.</span></p>
						<p style='color: #5f6368;font-size: 12px;line-height: 16px;letter-spacing: .3px;margin:0;'>" . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . "</p>
					</div>
				</div>
			</div>";

			$headers = 'Content-Type: text/html; charset=UTF-8';

			// Sent email to assign user once & rest of the mentioned users.
			$el_obj = get_post_meta( $post_id, "_{$elid}", true );
			if ( ! empty( $el_obj ) ) {
				if ( isset( $el_obj['assigned_to'] ) > 0 && $el_obj['sent_assigned_email'] === false ) {
					$assigned_user = get_user_by( 'ID', $el_obj['assigned_to'] );
					if ( ! empty( $assigned_user ) ) {
						$assigned_to_email = $assigned_user->user_email;

						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'Assigned to You', 'content-collaboration-inline-commenting' ), $p_title, $site_title );

                        wp_mail($assigned_to_email, $subject, $mentioned_html, $headers); // phpcs:ignore
					}
					// Updating after sending the email.
					$el_obj['sent_assigned_email'] = true;
					update_post_meta( $post_id, "_{$elid}", $el_obj );

					// Remove assigned email from the list.
					$key = array_search( $assigned_user->user_email, $email_list, true );
					if ( $key !== false ) {
						unset( $email_list[ $key ] );
					}

					$key = array_search( $assigned_user->user_email, $newly_mentioned_emails, true );
					if ( $key !== false ) {
						unset( $newly_mentioned_emails[ $key ] );
					}

					// Sent email to newly mentioned users.
					if ( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'You have been mentioned', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                       wp_mail($newly_mentioned_emails, $subject, $mentioned_html, $headers); // phpcs:ignore
					}

					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );
					// Sent email to all users.
					if ( ! empty( $email_list ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'New Comment', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                        wp_mail($email_list, $subject, $html, $headers); // phpcs:ignore
						$admin_notified = true;
					}
				} elseif ( isset( $el_obj['assigned_to'] ) > 0 && $el_obj['sent_assigned_email'] === true ) {
					// Remove assigned email from the list.
					$assigned_user = get_user_by( 'ID', $el_obj['assigned_to'] );
					$email_list[]  = $assigned_user->user_email;

					$email_list = array_diff( $email_list, $newly_mentioned_emails );

					// Removed current user email from the list.
					if ( ! empty( $current_user_email ) ) {
						$key = array_search( $current_user_email, $email_list, true );
						if ( $key !== false ) {
							unset( $email_list[ $key ] );
						}
					}

					// Sent email to newly mentioned users.
					if ( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'You have been mentioned', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                        wp_mail($newly_mentioned_emails, $subject, $mentioned_html, $headers); // phpcs:ignore

						// Notify Site Admin if setting enabled.
						$email_list = $this->cf_email_notify_siteadmin( $email_list );

						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( __( 'New Comment', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                            wp_mail($email_list, $subject, $html, $headers); // phpcs:ignore
							$admin_notified = true;
						}
					}
				} else {
					// Notify Site Admin if setting enabled.
					$email_list = $this->cf_email_notify_siteadmin( $email_list );

					// Sent email to newly mentioned users.
					if ( ! empty( $newly_mentioned_emails ) ) {
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'You have been mentioned', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                        wp_mail($newly_mentioned_emails, $subject, $mentioned_html, $headers); // phpcs:ignore

						// Sent email to all users.
						if ( ! empty( $email_list ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( __( 'New Comment', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                            wp_mail($email_list, $subject, $html, $headers); // phpcs:ignore
							$admin_notified = true;
						}
					}
				}

				// Send Email to admin if no user is mentioned.
				if ( false === $admin_notified ) {
					if ( empty( $newly_mentioned_emails ) ) {
						// Notify Site Admin if setting enabled.
						$cf_admin_notif = get_option( 'cf_admin_notif' );
						if ( '1' === $cf_admin_notif ) {
							$admin_email = get_option( 'admin_email' );
						}

						if ( ! empty( $admin_email ) ) {
							// Limit the page and site titles for Subject.
							$subject = $this->cf_email_prepare_subject( __( 'New Comment', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
                            wp_mail($admin_email, $subject, $html, $headers); // phpcs:ignore
						}
					}
				}
			}
		}
	}
	/**
	 *  Get icon according to blocktype.
	 *
	 * @param string $blockType contains type of block.
	 *
	 * return $url
	 */
	public function cf_get_block_type( $blockType ) {
		$url = '';
		switch ( $blockType ) {

			case 'core/image':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/image.png' );
				break;
			case 'core/video':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Video.png' );
				break;
			case 'core/audio':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/media.png' );
				break;

			case 'core/cover':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/cover.png' );
				break;

			case 'core/gallery':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/gallery.png' );
				break;

			case 'core/media-text':
				$url = esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/media-text.png' );
				break;

			default:
				$url = '';
		}

		return $url;
	}
}
