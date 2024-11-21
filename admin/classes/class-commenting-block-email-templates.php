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
			<p style="color: #5f6368; font-size: 12px; line-height: 16px; letter-spacing: .3px; margin-bottom: 0;margin-top:15px;">' . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . '<a style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;" href="' . esc_url( 'https://www.multicollab.com' ) . '">' . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . '</a>' . __( ' to notify you of a collaboration activity on this post of ', 'content-collaboration-inline-commenting' ) . '<span><a href="' . esc_url( get_site_url() ) . '" style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;">' . esc_html( $http_host ) . '</a>.</span></p>
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

			$cf_edd = new CF_EDD();
			if ( $cf_edd->is__premium_only() ) {
				if( true === $cf_edd->is_plan( EDD_PLAN_PRO ) ){
					// Slack notification intigration.
					// Resolve Comment.
					$cf_slack_notification_resolve_comment = get_option( 'cf_slack_notification_resolve_comment' );

					$profile_url      = get_avatar_url( $current_user_email );
					$user             = get_user_by( 'email', $current_user_email );
					$user_role        = ucwords( $user->roles[0] );
					$slack_title_icon = esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/Accepted.png' );

					if ( isset( $cf_slack_notification_resolve_comment ) && '1' === $cf_slack_notification_resolve_comment ) {

						$comment_list                           = '';
						$author_list                            = array();
						$message_array                          = '';
						$attachment_array                       = array();
						$all_mentioned_email_from_content_array = array();
						if ((is_array($list_of_comments) && !empty($list_of_comments)) || (is_object($list_of_comments) && !empty((array)$list_of_comments))) {	
							foreach ( $list_of_comments as $list_of_comments_array ) {
								if ( 'publish' === $list_of_comments_array['status'] ) {
									$user_id     = $list_of_comments_array['userData'];
									$user        = get_user_by( 'id', $user_id );
									$user_role   = ucwords( $user->roles[0] );
									$username    = $user->display_name;
									$profile_url = get_avatar_url( $user_id, array( 'size' => 260 ) );

									preg_match_all( '/<[^>]*class="[^"]*\bjs-mentioned\b[^"]*"[^>]*>/i', $list_of_comments_array['thread'], $result );

									if ( ! empty( $result[0] ) ) {
										if ((is_array($result[0]) && !empty($result[0])) || (is_object($result[0]) && !empty((array)$result[0]))) {
											foreach ( $result[0] as $multiple_links ) {

												// Create a new DOMDocument.
												$dom = new DOMDocument();
												// Load the XML.
												$dom->loadXML(
													'<?xml version="1.0"?>
												<body>
													' . $multiple_links . '</a>
												</body>'
												);

												$element = $dom->getElementsByTagName( 'a' );
												// Get the attribute.
												$multiple_links = $element[0]->getAttribute( 'href' );

												$tag_link               = $multiple_links;
												$tag_link_withou_mailto = str_replace( 'mailto:', '', $tag_link );

												$list_of_comments_array['thread'] = str_replace( '<a contenteditable="false" href="' . $tag_link . '" data-email="' . $tag_link_withou_mailto . '" class="js-mentioned">', '', $list_of_comments_array['thread'] );
												$message_link_2                   = str_replace( '</a>', '', $list_of_comments_array['thread'] );

												array_push( $all_mentioned_email_from_content_array, $tag_link_withou_mailto );

											}
										}
									} else {
										$message_link_2 = $list_of_comments_array['thread'];
									}

									$message_link_2 = str_replace( '@', '', $message_link_2 );
									$message_link_2 = str_replace( '#', '', $message_link_2 );
									$message_link_2 = str_replace( '  ', ' ', $message_link_2 );

									$message_link_2 = str_replace( '<br>', "\n", $message_link_2 );
									$message_link_2 = wp_strip_all_tags( $message_link_2 );

									$tags           = array( 'a' );
									$message_link_2 = preg_replace( '#<(' . implode( '|', $tags ) . ')>.*?<\/$1>#s', '', $message_link_2 );

									$tagname = 'a';
									$pattern = "#<\s*?$tagname\b[^>]*>(.*?)>#s";
									preg_match_all( $pattern, $message_link_2, $result_2 );

									$a_link_array                 = array();
									$a_link_array['html']         = $result_2[0];
									$a_link_array['replace_link'] = $result_2[1];

									if ( ! empty( $a_link_array['html'] ) ) {
										if ((is_array($a_link_array['html']) && !empty($a_link_array['html'])) || (is_object($a_link_array['html']) && !empty((array)$a_link_array['html']))) {
											foreach ( $a_link_array['html'] as $key => $find ) {
													$message_link_2 = str_replace( $find, $a_link_array['replace_link'][ $key ], $message_link_2 );
											}
										}
									}

									$message_link_2  = str_replace( '</a', '', $message_link_2 );
									$suggestion_text = '*' . $message_link_2 . '*';
									$comment_list   .= " \n\n\n> " . $suggestion_text . "\n>";

									// Join attachement text.
									if ( ! empty( $list_of_comments_array['attachmentText'] ) ) {

										preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $list_of_comments_array['attachmentText'], $attachement_link );

										if ( ! empty( $attachement_link['href'] ) ) {
											$attachement_text = wp_strip_all_tags( $list_of_comments_array['attachmentText'] );
											array_push( $attachment_array, "\n<" . $attachement_link['href'][0] . '|' . $attachement_text . '>' );
										}
									} else {
										array_push( $attachment_array, 'not_attachment' );
									}

									$message_link_2 = str_replace( '<br', '<br>', $message_link_2 );
									$message_link_2 = str_replace( '>>', '>', $message_link_2 );
									$message_array .= $message_link_2 . '<new>';

									$array       = array(
										array(
											'type'     => 'context',
											'elements' => array(
												array(
													'type'     => 'image',
													'image_url' => $profile_url,
													'alt_text' => 'user-icon',
												),
												array(
													'type' => 'mrkdwn',
													'text' => '*' . $username . '* (' . $user_role . ')',
												),
											),
										),
									);
									$author_list = array_merge( $array, $author_list );
								}
							}
						}

						$message_array = str_replace( '<br>', "\n", $message_array );

						$notification             = array();
						$notification['username'] = $current_user_display_name;
						$username                 = $current_user_display_name;

						$notification['attachments'] = array(
							array(
								'color'  => '#4B1BCE',
								'blocks' => array(
									array(
										'type'     => 'context',
										'elements' => array(
											array(
												'type'      => 'image',
												'image_url' => $slack_title_icon,
												'alt_text'  => 'icon-check',
											),
											array(
												'type' => 'mrkdwn',
												'text' => '_ Resolved Thread Comments _',
											),
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => '<' . $post_edit_link . '|' . html_entity_decode( $p_title ) . '>',
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => " *Comments:* \n\n ``` " . $commented_on_text . "``` \n\n\n",
										),
									),
								),
							),
						);

						$author_list   = array_reverse( $author_list );
						$message_array = explode( '<new>', $message_array );

						if ((is_array($author_list) && !empty($author_list)) || (is_object($author_list) && !empty((array)$author_list))) {	
							foreach ( $author_list as $key => $author_list_array ) {
								array_push( $notification['attachments'][0]['blocks'], $author_list_array );

								$final_comment = str_replace( '<br>', "\n>", $message_array[ $key ] );
								$final_comment = wp_strip_all_tags( $final_comment );

								$message_content = array(
									'type' => 'section',
									'text' => array(
										'type' => 'mrkdwn',
										'text' => '>' . $final_comment,
									),
								);

								array_push( $notification['attachments'][0]['blocks'], $message_content );

								if ( 'not_attachment' !== $attachment_array[ $key ] ) {
									$array = array(
										'type'     => 'context',
										'elements' => array(
											array(
												'type'      => 'image',
												'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Attachment.png' ),
												'alt_text'  => 'user-icon',
											),
											array(
												'type' => 'mrkdwn',
												'text' => $attachment_array[ $key ],
											),
										),
									);
									array_push( $notification['attachments'][0]['blocks'], $array );
								}
							}
						}

						$resolved_array = array(
							'type'     => 'context',
							'elements' => array(
								array(
									'type'      => 'image',
									'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Resolved.png' ),
									'alt_text'  => 'icon-check',
								),
								array(
									'type' => 'mrkdwn',
									'text' => '_ Marked as resolved by: ' . $username . ' _',
								),
							),
						);
						array_push( $notification['attachments'][0]['blocks'], $resolved_array );

						// Add mentioned list paragraph to message.
						$all_mentioned_email_array = array_unique( $all_mentioned_email_from_content_array );
						if ( ! empty( $all_mentioned_email_array ) ) {
							$all_mentioned_user_names        = array();
							$all_mentioned_text              = '';
							$all_mentioned_email_array_count = count( $all_mentioned_email_array );
							$count                           = 1;
							if (is_array($all_mentioned_email_array) || is_object($all_mentioned_email_array)) {
								foreach ( $all_mentioned_email_array as $all_mentioned_email_array_value ) {
									$mentioned_user = get_user_by( 'email', $all_mentioned_email_array_value );
									array_push( $all_mentioned_user_names, $mentioned_user->display_name );
									if ( $count === $all_mentioned_email_array_count ) {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '> ';
									} else {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '>, ';
									}
									$count++;
								}
							}
							$all_mentioned_text .= __( ' mentioned you in a comment on this post.', 'content-collaboration-inline-commenting' );

							$mentioned_array = array(
								'type' => 'section',
								'text' => array(
									'type' => 'mrkdwn',
									'text' => $all_mentioned_text,
								),
							);

							array_unshift( $notification['attachments'][0]['blocks'], $mentioned_array );
						}

						// New method.
						array_push( $notification['attachments'][0]['blocks'], array( 'type' => 'divider' ) );
						$blocks = wp_json_encode( $notification['attachments'][0]['blocks'] );
						$this->cf_send_slack_notification( $blocks );

					}
				}
			}
			if($users_emails){
	            wp_mail($users_emails, $r_subject, $html, $headers); // phpcs:ignore
	        }
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
		$post_subject = ! empty( $p_title ) ? $this->cf_limit_characters( $p_title, 30 ) . ' | ' . $site_title : $site_title;

		return sprintf( __( '%1$s | %2$s', 'content-collaboration-inline-commenting' ), $pre_subject, $post_subject );
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
			if (is_array($matches[1]) || is_object($matches[1])) {
				foreach ( $matches[1] as $user_email ) {
					$refined_user_email[] = $user_email;
				}
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

		if ((is_array($list_of_comments) && !empty($list_of_comments)) || (is_object($list_of_comments) && !empty((array)$list_of_comments))) {
			foreach ( $list_of_comments as $timestamp => $comment ) {
				if ( isset( $comment['status'] ) && 'publish' === $comment['status'] ) {
					$find_mentions .= $comment['thread'];
					if ( in_array( $timestamp, $new_comments, true ) ) {
						$find_new_mentions .= $comment['thread'];
					}
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
			if (is_array($newly_mentioned_emails) || is_object($newly_mentioned_emails)) {
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
			$post_title_html  = '';
			$arrow_svg        = '<span style="vertical-align: middle;padding-right: 5px;"><img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/email-arrow.png' ) . '" alt="Arrow" width="22" height="13" /></span>';
			if ( ! empty( $args['post_title'] ) ) {
				$post_title_html .= $arrow_svg . "<h2 class='comment-page-title' style='margin:0;display:inline-block;'><a href='" . esc_url( $post_edit_link ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;'>" . esc_html( wp_trim_words( $p_title, 3, '...' ) ) . '</a></h2>';
			}

			$comment_icon_html = '';
			if ( ! empty( $assign_to ) ) {
				$email_top_content = '<span class="commenter-name" style="text-transform: capitalize;font-weight: 700;display: inline-block;margin-bottom:10px;"">' . esc_html( $current_user_display_name ) . '</span>' . __( ' added ', 'content-collaboration-inline-commenting' ) .'<span class="commenter-name" style="text-transform: capitalize;font-weight: 700;display: inline-block;margin-bottom:10px;"">'.esc_html( $assigned_user->display_name  ) . '</span>' . __( ' in a comment on this post.', 'content-collaboration-inline-commenting' );
			}else{
				$email_top_content = '<span class="commenter-name" style="text-transform: capitalize;font-weight: 700;display: inline-block;margin-bottom:10px;"">' . esc_html( $current_user_display_name ) . '</span>' . __( ' added comment on this post.', 'content-collaboration-inline-commenting' ); 
			}
			$html             .= "
            <div class='comment-box new-comment' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
				<div class='comment-box-header' style='margin-bottom:30px;'>
				<p style='font-size:18px;color:#000;line-height:normal;margin:0;;margin-bottom:20px'>{$email_top_content}</p>
					<div style='display: flex;align-items: center;justify-content:space-between;flex-wrap:wrap;gap:20px;'>
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
					line-height: 16px;letter-spacing: .3px;margin-bottom:0;margin-top:15px;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.multicollab.com' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
					" . __( 'to notify you of a collaboration activity on this post of', 'content-collaboration-inline-commenting' ) . " <span><a href='" . esc_url( get_site_url() ) . "' style='color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;'>" . esc_html( $http_host ) . "</a>.</span></p>
					<p style='color: #5f6368;font-size: 12px; line-height: 16px; letter-spacing: .3px;margin:0;'>" . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . "</p>
				</div>
            </div>
			";

			$mentioned_html .= "
			<div class='comment-box new-comment' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
				<div class='comment-box-header' style='margin-bottom:30px;'>
					<p style='font-size:18px;color:#000;line-height:normal;margin:0;margin-bottom:20px'><span class='commenter-name' style='text-transform: capitalize;font-weight: 700;display: inline-block;'>" . esc_html( $current_user_display_name ) . "</span>" . __( ' mentioned you in a comment on this post.', 'content-collaboration-inline-commenting' ) . "</p>
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
						<p style='color: #5f6368;font-size: 12px;line-height: 16px;letter-spacing: .3px;margin-bottom:0;margin-top:15px;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.multicollab.com' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
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
						$assigned_html = str_replace('mentioned you in a comment on this post.', 'assigned you in a comment on this post.', $mentioned_html);
						// Limit the page and site titles for Subject.
						$subject = $this->cf_email_prepare_subject( __( 'Assigned to You', 'content-collaboration-inline-commenting' ), $p_title, $site_title );

                        wp_mail($assigned_to_email, $subject, $assigned_html, $headers); // phpcs:ignore
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

			// Slack notification intigration.
			// Add Comment.
			$cf_edd = new CF_EDD();
			if ( $cf_edd->is__premium_only() ) {
				if( true === $cf_edd->is_plan( EDD_PLAN_PRO ) ){
					$cf_slack_notification_add_comment = get_option( 'cf_slack_notification_add_comment' );

					if ( isset( $cf_slack_notification_add_comment ) && '1' === $cf_slack_notification_add_comment ) {
						$comment_list                           = '';
						$author_list                            = array();
						$message_array                          = '';
						$attachment_array                       = array();
						$all_mentioned_email_from_content_array = array();
						if ( ! empty( $list_of_comments ) ) {
							if (is_array($list_of_comments) || is_object($list_of_comments)) {
								foreach ( $list_of_comments as $list_of_comments_array ) {

									$user_id     = $list_of_comments_array['userData'];
									$user        = get_user_by( 'id', $user_id );
									$user_role   = ucwords( $user->roles[0] );
									$username    = $user->display_name;
									$profile_url = get_avatar_url( $user_id, array( 'size' => 260 ) );

									if ( isset( $list_of_comments_array['status'] ) && 'publish' === $list_of_comments_array['status'] ) {

										// Convert mailto link as slack link.
										$link = $list_of_comments_array['thread'];
										preg_match_all( '/<[^>]*class="[^"]*\bjs-mentioned\b[^"]*"[^>]*>/i', $link, $result );

										if ( ! empty( $result[0] ) ) {
											if (is_array($result[0]) || is_object($result[0])) {
												foreach ( $result[0] as $multiple_links ) {

													// Create a new DOMDocument.
													$dom = new DOMDocument();
													// Load the XML.
													$dom->loadXML(
														'<?xml version="1.0"?>
													<body>
														' . $multiple_links . '</a>
													</body>'
													);

													$element = $dom->getElementsByTagName( 'a' );
													// Get the attribute.
													$multiple_links = $element[0]->getAttribute( 'href' );

													$tag_link               = $multiple_links;
													$tag_link_withou_mailto = str_replace( 'mailto:', '', $tag_link );

													$list_of_comments_array['thread'] = str_replace( '<a contenteditable="false" href="' . $tag_link . '" data-email="' . $tag_link_withou_mailto . '" class="js-mentioned">', '', $list_of_comments_array['thread'] );
													$message_link_2                   = str_replace( '</a>', '', $list_of_comments_array['thread'] );

													array_push( $all_mentioned_email_from_content_array, $tag_link_withou_mailto );

												}
											}
										} else {
											$message_link_2 = $list_of_comments_array['thread'];
										}

										$message_link_2 = str_replace( '@', '', $message_link_2 );
										$message_link_2 = str_replace( '#', '', $message_link_2 );
										$message_link_2 = str_replace( '  ', ' ', $message_link_2 );

										$tags           = array( 'a' );
										$message_link_2 = preg_replace( '#<(' . implode( '|', $tags ) . ')>.*?<\/$1>#s', '', $message_link_2 );

										$message_link_2 = str_replace( '<br>', "\n", $message_link_2 );
										$message_link_2 = wp_strip_all_tags( $message_link_2 );

										$tagname = 'a';
										$pattern = "#<\s*?$tagname\b[^>]*>(.*?)>#s";
										preg_match_all( $pattern, $message_link_2, $result_2 );

										$a_link_array                 = array();
										$a_link_array['html']         = $result_2[0];
										$a_link_array['replace_link'] = $result_2[1];

										if ( ! empty( $a_link_array['html'] ) ) {
											if (is_array($a_link_array['html']) || is_object($a_link_array['html'])) {
												foreach ( $a_link_array['html'] as $key => $find ) {
													$message_link_2 = str_replace( $find, $a_link_array['replace_link'][ $key ], $message_link_2 );
												}
											}
										}

										$message_link_2 = str_replace( '</a', '', $message_link_2 );

										$comment_list .= $username . ' (' . $user_role . ") \n" . $message_link_2 . "\n\n\n";
										$array         = array(
											array(
												'type'     => 'context',
												'elements' => array(
													array(
														'type'     => 'image',
														'image_url' => $profile_url,
														'alt_text' => 'user-icon',
													),
													array(
														'type' => 'mrkdwn',
														'text' => '*' . $username . '* (' . $user_role . ')',
													),
												),
											),
										);
										$author_list   = array_merge( $array, $author_list );

										$message_link_2 = str_replace( '<br', '<br>', $message_link_2 );
										$message_link_2 = str_replace( '>>', '>', $message_link_2 );

										$message_array .= $message_link_2;

										// Join attachement text.
										if ( ! empty( $list_of_comments_array['attachmentText'] ) ) {

											preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $list_of_comments_array['attachmentText'], $attachement_link );

											if ( ! empty( $attachement_link['href'] ) ) {
												$attachement_text = wp_strip_all_tags( $list_of_comments_array['attachmentText'] );
												array_push( $attachment_array, "\n<" . $attachement_link['href'][0] . '|' . $attachement_text . '>' );

											}
										} else {
											array_push( $attachment_array, 'not_attachment' );
										}
										$message_array .= '<new>';
									}
								}
							}
						}

						$comment_list = str_replace( '<br>', "\n", $comment_list );
						$comment_list = wp_strip_all_tags( $comment_list );

						$notification = array();

						$notification['attachments'] = array(
							array(
								'color'  => '#4B1BCE',
								'blocks' => array(
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => '*<' . $post_edit_link . '|' . html_entity_decode( $p_title ) . '>*',
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => " *Comments:* \n\n ``` " . $commented_on_text . "``` \n\n\n",
										),
									),
								),
							),
						);

						$author_list   = array_reverse( $author_list );
						$message_array = explode( '<new>', $message_array );

						if ((is_array($author_list) && !empty($author_list)) || (is_object($author_list) && !empty((array)$author_list))) {	
							foreach ( $author_list as $key => $author_list_array ) {
								array_push( $notification['attachments'][0]['blocks'], $author_list_array );

								$final_comment = str_replace( '<br>', "\n>", $message_array[ $key ] );
								$final_comment = wp_strip_all_tags( $final_comment );

								$message_content = array(
									'type' => 'section',
									'text' => array(
										'type' => 'mrkdwn',
										'text' => '>' . $final_comment,
									),
								);

								array_push( $notification['attachments'][0]['blocks'], $message_content );

								if ( 'not_attachment' !== $attachment_array[ $key ] ) {
									$array = array(
										'type'     => 'context',
										'elements' => array(
											array(
												'type'      => 'image',
												'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Attachment.png' ),
												'alt_text'  => 'user-icon',
											),
											array(
												'type' => 'mrkdwn',
												'text' => $attachment_array[ $key ],
											),
										),
									);
									array_push( $notification['attachments'][0]['blocks'], $array );
								}
							}
						}

						if ( ! empty( $assign_to ) ) {
							$inserted = array(
								'type'     => 'context',
								'elements' => array(
									array(
										'type'      => 'image',
										'image_url' => $slack_assign_to_image,
										'alt_text'  => 'icon-check',
									),
									array(
										'type' => 'mrkdwn',
										'text' => '_' . $slack_assign_to_text . $slack_assign_to_link . '_',
									),
								),
							);

							array_push( $notification['attachments'][0]['blocks'], $inserted );

						}

						$comment_link = array(
							'type' => 'section',
							'text' => array(
								'type' => 'mrkdwn',
								'text' => '*<' . $post_edit_link . '|Open>*',
							),
						);
						array_push( $notification['attachments'][0]['blocks'], $comment_link );

						// Add mentioned list paragraph to message.
						$all_mentioned_email_array = array_unique( $all_mentioned_email_from_content_array );
						if ( ! empty( $all_mentioned_email_array ) ) {
							$all_mentioned_user_names        = array();
							$all_mentioned_text              = '';
							$all_mentioned_email_array_count = count( $all_mentioned_email_array );
							$count                           = 1;
							if (is_array($all_mentioned_email_array) || is_object($all_mentioned_email_array)) {
								foreach ( $all_mentioned_email_array as $all_mentioned_email_array_value ) {
									$mentioned_user = get_user_by( 'email', $all_mentioned_email_array_value );
									array_push( $all_mentioned_user_names, $mentioned_user->display_name );
									if ( $count === $all_mentioned_email_array_count ) {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '> ';
									} else {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '>, ';
									}
									$count++;
								}
							}
							$all_mentioned_text .= __( ' mentioned you in a comment on this post.', 'content-collaboration-inline-commenting' );

							$mentioned_array = array(
								'type' => 'section',
								'text' => array(
									'type' => 'mrkdwn',
									'text' => $all_mentioned_text,
								),
							);

							array_unshift( $notification['attachments'][0]['blocks'], $mentioned_array );
						}

						// New method.
						array_push( $notification['attachments'][0]['blocks'], array( 'type' => 'divider' ) );
						$blocks = wp_json_encode( $notification['attachments'][0]['blocks'] );
						$this->cf_send_slack_notification( $blocks );
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

	/**
	 * Suggestion : Add New comment / Reply Comment Email Template.
	 *
	 * @param array $args Contains all keys related to send the email.
	 *
	 * @return void
	 */
	public function sg_email_suggestion_add_comment__premium_only( $args ) {

		$html                      = '';
		$p_title                   = html_entity_decode( $args['post_title'] );
		$site_title                = $args['site_title'];
		$post_edit_link            = $args['post_edit_link'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];

		$mentioned_html    = '';
		$commented_on_text = '';
		$admin_notified    = false;

		$suggestion = $args['suggestion'];

		if ( ! empty( $suggestion[0] ) ) {
			$suggestionDetail        = (array) $suggestion[0];
			$commented_on_text       = $suggestionDetail['action'] . ': ' . $suggestionDetail['text'];
			$suggestion[0]->is_draft = false;
		}

		$find_mentions     = '';
		$find_new_mentions = '';
		$comments          = $suggestion;
		unset( $comments[0] );

		if ((is_array($comments) && !empty($comments)) || (is_object($comments) && !empty((array)$comments))) {
			foreach ( $comments as $key => $comment ) {
				$comment        = (array) $comment;
				$find_mentions .= $comment['text'];
				if ( isset( $comment['is_draft'] ) && $comment['is_draft'] ) {
					 $find_new_mentions           .= $comment['text'];
					 $suggestion[ $key ]->is_draft = false;
				}
			}
		}

		// Grab all the emails mentioned in the current board.
		$users_emails     = array();
		$mentioned_emails = $this->cf_find_mentioned_emails( $find_mentions );

		if ( null !== $users_emails ) {
			$mentioned_emails = array_merge( $mentioned_emails, $users_emails );
		}
		$email_list = array_unique( $mentioned_emails );

		// Grab only newly mentioned email of the board.
		$newly_mentioned_emails = $this->cf_find_mentioned_emails( $find_new_mentions );

		// Unset the newly mentioned emails from the list.
		$mentioned_user_name = array();
		if ((is_array($newly_mentioned_emails) && !empty($newly_mentioned_emails)) || (is_object($newly_mentioned_emails) && !empty((array)$newly_mentioned_emails))) {
			foreach ( $newly_mentioned_emails as $newly_mentioned ) {
				$key = array_search( $newly_mentioned, $email_list, true );
				if ( $key !== false ) {
					unset( $email_list[ $key ] );
				}

				$mentioned_user = get_user_by( 'email', $newly_mentioned );
				array_push( $mentioned_user_name, $mentioned_user->display_name );

			}
		}

		// Removed current user email from the list.
		if ( ! empty( $current_user_email ) ) {
			$key = array_search( $current_user_email, $email_list, true );
			if ( $key !== false ) {
				unset( $email_list[ $key ] );
			}
		}

		if ( ! empty( $suggestion ) ) {

			// Get comments loop.
			$this->list_of_comments = $comments;
			$comment_list_html      = $this->sg_email_get_suggestion_comments_loop__premium_only();
			$http_host              = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS );
			$site_title_html        = "<h2 class='comment-page-web' style='margin:0;display:inline-block;'><a href='" . esc_url( get_site_url() ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;word-wrap: break-word;'>" . esc_html( $http_host ) . '</a></h2>';
			$arrow_svg              = '<span style="vertical-align: middle;padding-right: 5px;"><img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/email-arrow.png' ) . '" alt="Arrow" width="22" height="13" /></span>';
			$post_title_html        = '';
			if ( ! empty( $args['post_title'] ) ) {
				$post_title_html .= $arrow_svg . "<h2 class='comment-page-title' style='margin:0;display:inline-block;'><a href='" . esc_url( $post_edit_link ) . "' target='_blank' style='font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;'>" . esc_html( wp_trim_words( $p_title, 3, '...' ) ) . '</a></h2>';
			}

			$comment_icon_html = '';
			$html             .= "
            <div class='comment-box new-comment3' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
                <div class='comment-box-header' style='margin-bottom:30px;'>
				<p style='font-size:18px;color:#000;line-height:normal;margin:0;;margin-bottom:20px'>
                <span class='commenter-name' style='text-transform: capitalize;font-weight: 700;display: inline-block;margin-bottom:10px;'>" . esc_html( $current_user_display_name ) . '</span>' . __( ' added a suggestion on this post.', 'content-collaboration-inline-commenting' ) . "
            	</p>
				<div style='display: flex;align-items: center;justify-content:space-between;flex-wrap: wrap;gap:20px;'>
					<div class='comment-box-header-right'>
						{$site_title_html}
						{$post_title_html}
					</div>
						<div class='view_reply' style='
							margin-right: 10px;
							display: inline-block;cursor: pointer;margin-left:auto;'>
							<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding: 9.5px 30px;
							background-color: #4B1BCE;border-radius: 8px;font-size: 16px;text-decoration: none;color: #fff;'>" . __( 'Open', 'content-collaboration-inline-commenting' ) . "</a></div>
						</div>
					</div>
                </div>
                <div class='comment-box-body' style='border:1px solid #E2E4E7;border-radius:20px;padding:30px;'>
                    <h2 class='head-with-icon' style='margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;'>
                        {$comment_icon_html}
                        " . __( 'Suggestions', 'content-collaboration-inline-commenting' ) . "
                    </h2>
                    <div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;'>" . $commented_on_text . "</div>
                     {$comment_list_html}
                </div>
				<div>
					<p style='color: #5f6368;font-size: 12px;
					line-height: 16px;letter-spacing: .3px;margin-bottom:0;margin-top:15px;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.multicollab.com' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
					" . __( 'to notify you of a collaboration activity on this post of', 'content-collaboration-inline-commenting' ) . " <span><a href='" . esc_url( get_site_url() ) . "' style='color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;'>" . esc_html( $http_host ) . "</a>.</span></p>
					<p style='color: #5f6368;font-size: 12px; line-height: 16px; letter-spacing: .3px;margin:0;'>" . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . "</p>
				</div>
            </div>
			";

			$mentioned_html .= "
            <div class='comment-box new-comment4' style='background:#fff;width:95%;font-family:Arial,serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;'>
                <div class='comment-box-header' style='margin-bottom:30px;'>
                    <p style='font-size:18px;color:#000;line-height:normal;margin:0;;margin-bottom:20px'><span class='commenter-name'  style='text-transform: capitalize;font-weight: 700;
                    display: inline-block;margin-bottom:10px;'>" . esc_html( $current_user_display_name ) . '</span>' . __( ' mentioned <span class="mentioed-name" style="text-transform: capitalize;font-weight: 700;display: inline-block;">'.implode( ', ', $mentioned_user_name ).'</span> in a suggestion on this post 3.', 'content-collaboration-inline-commenting' ) . "</p>
					<div style='display: flex;align-items: center;justify-content:space-between;flex-wrap: wrap;gap:20px;'>
						<div class='comment-box-header-right'>
						{$site_title_html}
						{$post_title_html}
						</div>
						<div class='view_reply' style='
							margin-right: 10px;
							display: inline-block;cursor: pointer;margin-left:auto;'>
							<div class='view_reply_btn'><a href='" . esc_url( $post_edit_link ) . "' style='padding: 9.5px 30px;
							background-color: #4B1BCE;border-radius: 8px;font-size: 16px;text-decoration: none;color: #fff;'>" . __( 'Open', 'content-collaboration-inline-commenting' ) . "</a></div>
						</div>
					</div>	
                </div>
                <div class='comment-box-body' style='border:1px solid #E2E4E7;border-radius:20px;padding:30px;'>
					<h2 class='head-with-icon' style='margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;'>
						{$comment_icon_html}
						" . __( 'Suggestions', 'content-collaboration-inline-commenting' ) . "
					</h2>
                    <div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;'>" . $commented_on_text . "</div>
                    {$comment_list_html}
                </div>
				<div>
					<p style='color: #5f6368;font-size: 12px;
					line-height: 16px;letter-spacing: .3px;margin-bottom:0;margin-top:15px;'>" . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . "<a style='color: #4B1BCE;font-size: 12px;line-height: 16px;letter-spacing: .3px;text-decoration: none;' href='" . esc_url( 'https://www.multicollab.com' ) . "'>" . __( 'Multicollab', 'content-collaboration-inline-commenting' ) . "</a>
					" . __( 'to notify you of a collaboration activity on this post of', 'content-collaboration-inline-commenting' ) . " <span><a href='" . esc_url( get_site_url() ) . "' style='color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;'>" . esc_html( $http_host ) . "</a>.</span></p>
					<p style='color: #5f6368;font-size: 12px; line-height: 16px; letter-spacing: .3px;margin:0;'>" . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . "</p>
				</div>
            </div>
			";

			$headers = 'Content-Type: text/html; charset=UTF-8';

			// Sent email to assign user once & rest of the mentioned users.
			if ( ! empty( $suggestion ) ) {

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
						if ( empty( $comments ) ) {
							$subject = $this->cf_email_prepare_subject( __( 'New Suggestion', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
						} else {
							$subject = $this->cf_email_prepare_subject( __( 'New Comment on Suggestion', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
						}
                        wp_mail($email_list, $subject, $html, $headers); // phpcs:ignore
						$admin_notified = true;
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
							if ( empty( $comments ) ) {
								$subject = $this->cf_email_prepare_subject( __( 'New Suggestion', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
							} else {
								$subject = $this->cf_email_prepare_subject( __( 'New Comment on Suggestion', 'content-collaboration-inline-commenting' ), $p_title, $site_title );
							}
                            wp_mail($admin_email, $subject, $html, $headers); // phpcs:ignore
						}
					}
				}
			}

			// Slack notification intigration.
			// Add Suggestion.
			$cf_edd = new CF_EDD();
			if ( $cf_edd->is__premium_only() ) {
				if( true === $cf_edd->is_plan( EDD_PLAN_PRO ) ){
					$cf_slack_notification_add_suggestion = get_option( 'cf_slack_notification_add_suggestion' );

					$profile_url = get_avatar_url( $current_user_email );
					$user        = get_user_by( 'email', $current_user_email );
					$user_role   = ucwords( $user->roles[0] );
					$username    = $current_user_display_name;

					if ( isset( $cf_slack_notification_add_suggestion ) && '1' === $cf_slack_notification_add_suggestion ) {

						if ( ! empty( $suggestion ) ) {
							$count                                  = 0;
							$comment_list                           = '';
							$author_list                            = array();
							$attachment_array                       = array();
							$all_mentioned_email_from_content_array = array();
							$message_array                          = '';
							if (is_array($suggestion) || is_object($suggestion)) {
								foreach ( $suggestion as $list_of_comments_array ) {
									$list_of_comments_array = (array) $list_of_comments_array;
									$user_id                = $list_of_comments_array['uid'];
									$user                   = get_user_by( 'id', $user_id );
									$user_role              = ucwords( $user->roles[0] );
									$username               = $user->display_name;
									$profile_url            = get_avatar_url( $user_id, array( 'size' => 260 ) );

									if ( 'reply' === $list_of_comments_array['action'] && 'publish' === $list_of_comments_array['status'] ) {

										// Convert mailto link as slack link.
										$link = $list_of_comments_array['text'];
										preg_match_all( '/<[^>]*class="[^"]*\bjs-mentioned\b[^"]*"[^>]*>/i', $link, $result );

										if ( ! empty( $result[0] ) ) {
											if (is_array($result[0]) || is_object($result[0])) {
												foreach ( $result[0] as $multiple_links ) {

													// Create a new DOMDocument.
													$dom = new DOMDocument();
													// Load the XML.
													$dom->loadXML(
														'<?xml version="1.0"?>
													<body>
														' . $multiple_links . '</a>
													</body>'
													);

													$element = $dom->getElementsByTagName( 'a' );
													// Get the attribute.
													$multiple_links = $element[0]->getAttribute( 'href' );

													$tag_link               = $multiple_links;
													$tag_link_withou_mailto = str_replace( 'mailto:', '', $tag_link );

													$tagged_user = get_user_by( 'email', $tag_link_withou_mailto );

													$list_of_comments_array['text'] = str_replace( '<a contenteditable="false" href="' . $tag_link . '" title="' . $tagged_user->data->display_name . '" data-email="' . $tag_link_withou_mailto . '" class="js-mentioned">', '', $list_of_comments_array['text'] );
													$message_link_2                 = str_replace( '</a>', '', $list_of_comments_array['text'] );

													array_push( $all_mentioned_email_from_content_array, $tag_link_withou_mailto );
												}
											}
										} else {
											$message_link_2 = $list_of_comments_array['text'];
										}

										$message_link_2 = str_replace( '@', '', $message_link_2 );
										$message_link_2 = str_replace( '#', '', $message_link_2 );
										$message_link_2 = str_replace( '  ', ' ', $message_link_2 );

										$message_link_2 = str_replace( '<br>', "\n", $message_link_2 );
										$message_link_2 = wp_strip_all_tags( $message_link_2 );

										$tags           = array( 'a' );
										$message_link_2 = preg_replace( '#<(' . implode( '|', $tags ) . ')>.*?<\/$1>#s', '', $message_link_2 );

										$tagname = 'a';
										$pattern = "#<\s*?$tagname\b[^>]*>(.*?)>#s";
										preg_match_all( $pattern, $message_link_2, $result_2 );

										$a_link_array                 = array();
										$a_link_array['html']         = $result_2[0];
										$a_link_array['replace_link'] = $result_2[1];

										
										if ((is_array($a_link_array['html']) && !empty($a_link_array['html'])) || (is_object($a_link_array['html']) && !empty((array)$a_link_array['html']))) {	
											foreach ( $a_link_array['html'] as $key => $find ) {
												$message_link_2 = str_replace( $find, $a_link_array['replace_link'][ $key ], $message_link_2 );
											}
										}

										$message_link_2 = str_replace( '</a', '', $message_link_2 );

										$comment_list .= $username . ' (' . $user_role . ") \n" . $message_link_2 . "\n\n\n";
										$array         = array(
											array(
												'type'     => 'context',
												'elements' => array(
													array(
														'type'     => 'image',
														'image_url' => $profile_url,
														'alt_text' => 'user-icon',
													),
													array(
														'type' => 'mrkdwn',
														'text' => '*' . $username . '* (' . $user_role . ')',
													),
												),
											),
										);
										$author_list   = array_merge( $array, $author_list );

										$message_link_2 = str_replace( '<br', '<br>', $message_link_2 );
										$message_link_2 = str_replace( '>>', '>', $message_link_2 );
										$message_array .= $message_link_2;

										// Join attachement text.
										if ( ! empty( $list_of_comments_array['attachmentText'] ) ) {

											preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $list_of_comments_array['attachmentText'], $attachement_link );

											if ( ! empty( $attachement_link['href'] ) ) {
												$attachement_text = wp_strip_all_tags( $list_of_comments_array['attachmentText'] );
												array_push( $attachment_array, "\n<" . $attachement_link['href'][0] . '|' . $attachement_text . '>' );
											}
										} else {
											array_push( $attachment_array, 'not_attachment' );
										}
										$message_array .= '<new>';

										$count++;
									}
								}
							}
						}

						$comment_list = str_replace( '<br>', "\n", $comment_list );
						$comment_list = wp_strip_all_tags( $comment_list );

						$notification = array();

						$notification['attachments'] = array(
							array(
								'color'  => '#4B1BCE',
								'blocks' => array(
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => '*<' . $post_edit_link . '|' . html_entity_decode( $p_title ) . '>*',
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => " *Suggestion:* \n\n ``` " . wp_strip_all_tags( $commented_on_text ) . "``` \n\n\n",
										),
									),
								),
							),
						);

						if ( 0 !== $count ) {

							$author_list   = array_reverse( $author_list );
							$message_array = explode( '<new>', $message_array );

							if ((is_array($author_list) && !empty($author_list)) || (is_object($author_list) && !empty((array)$author_list))) {	
								foreach ( $author_list as $key => $author_list_array ) {
									array_push( $notification['attachments'][0]['blocks'], $author_list_array );

									$final_comment   = str_replace( '<br>', "\n>", $message_array[ $key ] );
									$final_comment   = wp_strip_all_tags( $final_comment );
									$message_content = array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => '>' . $final_comment,
										),
									);

									array_push( $notification['attachments'][0]['blocks'], $message_content );

									if ( 'not_attachment' !== $attachment_array[ $key ] ) {
										$array = array(
											'type'     => 'context',
											'elements' => array(
												array(
													'type'     => 'image',
													'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Attachment.png' ),
													'alt_text' => 'user-icon',
												),
												array(
													'type' => 'mrkdwn',
													'text' => $attachment_array[ $key ],
												),
											),
										);
										array_push( $notification['attachments'][0]['blocks'], $array );
									}
								}
							}
						}

						$comment_link = array(
							'type' => 'section',
							'text' => array(
								'type' => 'mrkdwn',
								'text' => '*<' . $post_edit_link . '|Open>*',
							),
						);
						array_push( $notification['attachments'][0]['blocks'], $comment_link );

						// Add mentioned list paragraph to message.
						$all_mentioned_email_array = array_unique( $all_mentioned_email_from_content_array );
						if ( ! empty( $all_mentioned_email_array ) ) {
							$all_mentioned_user_names        = array();
							$all_mentioned_text              = '';
							$all_mentioned_email_array_count = count( $all_mentioned_email_array );
							$count                           = 1;
							if (is_array($all_mentioned_email_array) || is_object($all_mentioned_email_array)) {
								foreach ( $all_mentioned_email_array as $all_mentioned_email_array_value ) {
									$mentioned_user = get_user_by( 'email', $all_mentioned_email_array_value );
									array_push( $all_mentioned_user_names, $mentioned_user->display_name );
									if ( $count === $all_mentioned_email_array_count ) {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '> ';
									} else {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '>, ';
									}
									$count++;
								}
							}
							$all_mentioned_text .= __( ' mentioned you in a comment on this post.', 'content-collaboration-inline-commenting' );

							$mentioned_array = array(
								'type' => 'section',
								'text' => array(
									'type' => 'mrkdwn',
									'text' => $all_mentioned_text,
								),
							);

							array_unshift( $notification['attachments'][0]['blocks'], $mentioned_array );
						}

						// New method.
						array_push( $notification['attachments'][0]['blocks'], array( 'type' => 'divider' ) );
						$blocks = wp_json_encode( $notification['attachments'][0]['blocks'] );
						$this->cf_send_slack_notification( $blocks );

					}
				}
			}
		}

		return $suggestion;
	}


	/**
	 * Resolved Suggestion Email Template
	 *
	 * @param array $args Data to be used in the template.
	 *
	 * @return void
	 */
	public function sg_email_suggestion_resolved__premium_only( $args ) {

		$html                      = '';
		$p_title                   = html_entity_decode( $args['post_title'] );
		$site_title                = $args['site_title'];
		$post_edit_link            = $args['post_edit_link'];
		$current_user_email        = $args['current_user_email'];
		$current_user_display_name = $args['current_user_display_name'];
		$commented_on_text         = '';
		$suggestion                = $args['suggestion'];

		if ( ! empty( $suggestion[0] ) ) {
			$suggestionDetail        = (array) $suggestion[0];
			$commented_on_text       = $suggestionDetail['action'] . ': ' . $suggestionDetail['text'];
			$suggestion[0]->is_draft = false;
		}

		$comments = $suggestion;
		unset( $comments[0] );

		if ( ! empty( $suggestion ) ) {

			// Get comments loop.
			$this->list_of_comments = $comments;
			$comment_list_html      = $this->sg_email_get_suggestion_comments_loop__premium_only();
			$slack_title            = '';
			$http_host       = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS );
			$arrow_svg        = '<span style="vertical-align: middle;padding-right: 5px;padding-left:5px;"><img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/email-arrow.png' ) . '" alt="Arrow" width="22" height="13" /></span>';
			$html           .= '<div class="comment-box comment-resolved" style="background:#fff;width:95%;font-family: Roboto,sans-serif;padding-top:40px;padding-right:10px;padding-bottom:20px;padding-left:10px;">';
			$html           .= '<div class="comment-box-header" style="margin-bottom:30px;">';
			$html           .= '<p style="font-size:18px;color:#000;line-height:normal;margin:0; margin-bottom: 20px;"><a href="mailto:' . esc_attr( $current_user_email ) . '" class="" style="color: #000; text-decoration: none; text-transform: capitalize;font-weight: 700;
            display: inline-block;margin-bottom:10px;">' . esc_html( $current_user_display_name ) . '</a> ' . sprintf( __( 'has %sed the following suggestion.', 'content-collaboration-inline-commenting' ), __( $suggestionDetail['status']->action, 'content-collaboration-inline-commenting' ) ) . '</p>';
			$html .= '<div style="display: flex;align-items: center;justify-content:space-between;flex-wrap:wrap;gap:20px;margin-bottom:30px;">
			<div class="comment-box-header-right">';
			$html .= '<h2 class="comment-page-web" style="margin:0;display:inline-block;"><a href="' . esc_url( get_site_url() ) . '" target="_blank" style="font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;word-wrap: break-word;">Multicollab.com</a></h2>';

			if ( ! empty( $p_title ) ) {
				$html .= $arrow_svg . '<h2 class="comment-page-title" style="margin:0;display:inline-block;"><a href="' . esc_url( $post_edit_link ) . '" style="color:#4B1BCE;text-decoration:underline;font-size:20px;">' . esc_html( wp_trim_words( $p_title, 3, '...' ) ) . '</a></h2></div>';
			}
			$html .= '<div class="view_reply" style="
			margin-right: 10px;
			display: inline-block;cursor: pointer;margin-left:auto;">
			<div class="view_reply_btn"><a href="' . esc_url( $post_edit_link ) . '" style="padding: 9.5px 30px;
			background-color: #4B1BCE;border-radius: 8px;font-size: 16px;text-decoration: none;color: #fff;">' . __( 'Open', 'content-collaboration-inline-commenting' ) . '</a>
			</div>
		</div></div>';
			$html .= '<div class="comment-box-body" style="border:1px solid #E2E4E7;border-radius:20px;padding:30px;">';
			$html .= '<h3 class="head-with-icon" style="margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;">';
			$html .= '<span class="icon-resolved" style="padding-right:10px;vertical-align:middle;">';

			if ( $suggestionDetail['status']->action === 'accept' ) {
				$html            .= '<img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/sg-accept.png' ) . '" alt="Resolved" />';
				$html            .= '</span>' . __( 'Suggestion Accepted', 'content-collaboration-inline-commenting' );
				$slack_title      = __( 'Suggestion Accepted', 'content-collaboration-inline-commenting' );
				$slack_title_icon = esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/Accepted.png' );

			} elseif ( $suggestionDetail['status']->action === 'reject' ) {
				$html            .= '<img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/sg-reject.png' ) . '" alt="Resolved" />';
				$html            .= '</span>' . __( 'Suggestion Rejected', 'content-collaboration-inline-commenting' );
				$slack_title      = __( 'Suggestion Rejected', 'content-collaboration-inline-commenting' );
				$slack_title_icon = esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/Rejected.png' );

			}

			$html .= '</h3>';

			$html .= "<div class='commented_text' style='background-color:#F8F8F8;border:1px solid #E2E4E7;font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#191E23;'>" . $commented_on_text . "</div>
                     {$comment_list_html}";

			$html .= '<table class="cf-marked-resolved-by" style="padding-bottom:10px"><tr><td valign="middle">';
			$html .= '<span class="icon-resolved" style="padding-right:5px;line-height:1;vertical-align:middle;"><img src="' . esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/icon-check.png' ) . '" alt="resolved by" style="width: 16px;"/></span>';
			$html .= '<span><em style="color: #191e23;">' . __( 'Marked as resolved by', 'content-collaboration-inline-commenting' ) . '</em></span>';
			$html .= '<a href="mailto:' . esc_attr( $current_user_email ) . '" title="' . esc_attr( $current_user_display_name ) . '" target="_blank" style="color:#4B1BCE;text-decoration:none;padding-left:5px;"><em>' . esc_html( $current_user_display_name ) . '</em></a>';
			$html .= '</td></tr></table>';
			$html .= '  </div>';
			$html .= '<div>
			<p style="color: #5f6368; font-size: 12px; line-height: 16px; letter-spacing: .3px; margin-bottom: 0;margin-top:15px;">' . __( 'This email is sent by ', 'content-collaboration-inline-commenting' ) . '<a style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;" href="' . esc_url( 'https://www.multicollab.com' ) . '</a>' . __( ' to notify you of a collaboration activity on this post of ', 'content-collaboration-inline-commenting' ) . '<span><a href="' . esc_url( get_site_url() ) . '" style="color: #4B1BCE; font-size: 12px; line-height: 16px; letter-spacing: .3px; text-decoration: none;">' . esc_html( $http_host ) . '</a>.</span></p>
			<p style="color: #5f6368; font-size: 12px; line-height: 16px; letter-spacing: .3px; margin: 0;">' . __( 'You have received this email because you are either mentioned or a participant in the post.', 'content-collaboration-inline-commenting' ) . '</p>';
			$html .= '  </div>
            </div>
			';

			$headers = 'Content-Type: text/html; charset=UTF-8';

			$users_emails = ! empty( $this->users_emails ) ? array_unique( $this->users_emails ) : array();
			$key          = array_search( $current_user_email, $users_emails, true );
			if ( $key !== false ) {
				unset( $users_emails[ $key ] );
			}

			// Notify Site Admin if setting enabled.
			$users_emails = $this->cf_email_notify_siteadmin( $users_emails );

			// Limit the page and site titles for Subject.
			$r_subject = $this->cf_email_prepare_subject( __( 'Suggestion Resolved', 'content-collaboration-inline-commenting' ), $p_title, $site_title );

            wp_mail($users_emails, $r_subject, $html, $headers); // phpcs:ignore

			// Slack notification intigration.
			// Accept/Reject Suggestion.
			$cf_edd = new CF_EDD();
			if ( $cf_edd->is__premium_only() ) {
				if( true === $cf_edd->is_plan( EDD_PLAN_PRO ) ){
					$cf_slack_notification_accept_reject_suggestion = get_option( 'cf_slack_notification_accept_reject_suggestion' );

					$profile_url = get_avatar_url( $current_user_email );
					$user        = get_user_by( 'email', $current_user_email );
					$user_role   = ucwords( $user->roles[0] );

					if ( isset( $cf_slack_notification_accept_reject_suggestion ) && '1' === $cf_slack_notification_accept_reject_suggestion ) {

						$comment_list                           = '';
						$reply_list                             = '';
						$author_list                            = array();
						$attachment_array                       = array();
						$all_mentioned_email_from_content_array = array();

						if ((is_array($suggestion) && !empty($suggestion)) || (is_object($suggestion) && !empty((array)$suggestion))) {	
							foreach ( $suggestion as $suggestion_array ) {
								// Convert mailto link as slack link.
								$link = $suggestion_array->text;
								preg_match_all( '/<[^>]*class="[^"]*\bjs-mentioned\b[^"]*"[^>]*>/i', $link, $result );

								if ((is_array($result[0]) && !empty($result[0])) || (is_object($result[0]) && !empty((array)$result[0]))) {	

									foreach ( $result[0] as $multiple_links ) {

										// Create a new DOMDocument.
										$dom = new DOMDocument();
										// Load the XML.
										$dom->loadXML(
											'<?xml version="1.0"?>
										<body>
											' . $multiple_links . '</a>
										</body>'
										);

										$element = $dom->getElementsByTagName( 'a' );
										// Get the attribute.
										$multiple_links = $element[0]->getAttribute( 'href' );

										$tag_link               = $multiple_links;
										$tag_link_withou_mailto = str_replace( 'mailto:', '', $tag_link );
										$tagged_user            = get_user_by( 'email', $tag_link_withou_mailto );

										$suggestion_array->text = str_replace( '<a contenteditable="false" href="' . $tag_link . '" title="' . $tagged_user->data->display_name . '" data-email="' . $tag_link_withou_mailto . '" class="js-mentioned">', '', $suggestion_array->text );
										$suggestion_array->text = str_replace( '</a>', '', $suggestion_array->text );

										array_push( $all_mentioned_email_from_content_array, $tag_link_withou_mailto );

									}
								}

								$suggestion_array->text = str_replace( '@', '', $suggestion_array->text );
								$suggestion_array->text = str_replace( '#', '', $suggestion_array->text );
								$suggestion_array->text = str_replace( '  ', ' ', $suggestion_array->text );

								$suggestion_array->text = str_replace( '<br>', "\n", $suggestion_array->text );
								$suggestion_array->text = wp_strip_all_tags( $suggestion_array->text );

								$tags                   = array( 'a' );
								$suggestion_array->text = preg_replace( '#<(' . implode( '|', $tags ) . ')>.*?<\/$1>#s', '', $suggestion_array->text );

								$tagname = 'a';
								$pattern = "#<\s*?$tagname\b[^>]*>(.*?)>#s";
								preg_match_all( $pattern, $suggestion_array->text, $result_2 );

								$a_link_array                 = array();
								$a_link_array['html']         = $result_2[0];
								$a_link_array['replace_link'] = $result_2[1];

								if ((is_array($a_link_array['html']) && !empty($a_link_array['html'])) || (is_object($a_link_array['html']) && !empty((array)$a_link_array['html']))) {	
									foreach ( $a_link_array['html'] as $key => $find ) {
										$suggestion_array->text = str_replace( $find, $a_link_array['replace_link'][ $key ], $suggestion_array->text );
									}
								}

								$suggestion_array->text = str_replace( '</a', '', $suggestion_array->text );
								$suggestion_array->text = str_replace( '<br>', "\n>", $suggestion_array->text );

								$suggestion_array->text = str_replace( '<br', '<br>', $suggestion_array->text );
								$suggestion_array->text = str_replace( '>>', '>', $suggestion_array->text );

								if ( is_object( $suggestion_array->status ) && 'accept' === $suggestion_array->status->action ) {
									$suggestion_text = wp_strip_all_tags( $suggestion_array->action . ' : ' . $suggestion_array->text );
									$comment_list   .= " \n\n\n ``` " . $suggestion_text . ' ```';
								} elseif ( is_object( $suggestion_array->status ) && 'reject' === $suggestion_array->status->action ) {
									$suggestion_text = wp_strip_all_tags( $suggestion_array->action . ' : ' . $suggestion_array->text );
									$comment_list   .= " \n\n\n ```" . $suggestion_text . '```';
								}

								if ( 'reply' === $suggestion_array->action && 'publish' === $suggestion_array->status ) {

									$reply_list .= '>' . $suggestion_array->text;

									// Join attachement text.
									$list_of_comments_array = (array) $suggestion_array;
									if ( ! empty( $list_of_comments_array['attachmentText'] ) ) {

										preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $list_of_comments_array['attachmentText'], $attachement_link );

										if ( ! empty( $attachement_link['href'] ) ) {
											$attachement_text = wp_strip_all_tags( $list_of_comments_array['attachmentText'] );
											array_push( $attachment_array, "\n<" . $attachement_link['href'][0] . '|' . $attachement_text . '>' );
										}
									} else {
										array_push( $attachment_array, 'not_attachment' );
									}

									$reply_list .= '<new>';

									$user_id     = $suggestion_array->uid;
									$user        = get_user_by( 'id', $user_id );
									$user_role   = ucwords( $user->roles[0] );
									$username    = $user->display_name;
									$profile_url = get_avatar_url( $user_id, array( 'size' => 260 ) );

									$array       = array(
										array(
											'type'     => 'context',
											'elements' => array(
												array(
													'type'     => 'image',
													'image_url' => $profile_url,
													'alt_text' => 'user-icon',
												),
												array(
													'type' => 'mrkdwn',
													'text' => '*' . $username . '* (' . $user_role . ')',
												),
											),
										),
									);
									$author_list = array_merge( $array, $author_list );

								}
							}
						}

						$comment_list = str_replace( '<br>', "\n", $comment_list );
						$comment_list = wp_strip_all_tags( $comment_list );

						$notification             = array();
						$notification['username'] = $current_user_display_name;
						$username_currunt         = $current_user_display_name;

						$notification['attachments'] = array(
							array(
								'color'  => '#4B1BCE',
								'blocks' => array(
									array(
										'type'     => 'context',
										'elements' => array(
											array(
												'type'      => 'image',
												'image_url' => $slack_title_icon,
												'alt_text'  => 'icon-check',
											),
											array(
												'type' => 'mrkdwn',
												'text' => '_' . $slack_title . '_',
											),
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => '<' . $post_edit_link . '|' . html_entity_decode( $p_title ) . '>',
										),
									),
									array(
										'type' => 'section',
										'text' => array(
											'type' => 'mrkdwn',
											'text' => "*Suggestion:* \n" . $comment_list,
										),
									),
								),
							),
						);

						$author_list = array_reverse( $author_list );
						$reply_list  = explode( '<new>', $reply_list );
						if ((is_array($author_list) && !empty($author_list)) || (is_object($author_list) && !empty((array)$author_list))) {	
							foreach ( $author_list as $key => $author_list_array ) {
								array_push( $notification['attachments'][0]['blocks'], $author_list_array );

								$final_comment = str_replace( '<br>', "\n>", $reply_list[ $key ] );
								$final_comment = wp_strip_all_tags( $final_comment );

								$message_content = array(
									'type' => 'section',
									'text' => array(
										'type' => 'mrkdwn',
										'text' => $final_comment,
									),
								);

								array_push( $notification['attachments'][0]['blocks'], $message_content );

								if ( 'not_attachment' !== $attachment_array[ $key ] ) {
									$array = array(
										'type'     => 'context',
										'elements' => array(
											array(
												'type'      => 'image',
												'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Attachment.png' ),
												'alt_text'  => 'user-icon',
											),
											array(
												'type' => 'mrkdwn',
												'text' => $attachment_array[ $key ],
											),
										),
									);
									array_push( $notification['attachments'][0]['blocks'], $array );
								}
							}
						}

						$resolved_by = array(
							'type'     => 'context',
							'elements' => array(
								array(
									'type'      => 'image',
									'image_url' => esc_url_raw( COMMENTING_BLOCK_URL . 'admin/assets/images/Resolved.png' ),
									'alt_text'  => 'icon-check',
								),
								array(
									'type' => 'mrkdwn',
									'text' => '_ Marked as resolved by: ' . $username_currunt . ' _ ',
								),
							),
						);
						array_push( $notification['attachments'][0]['blocks'], $resolved_by );

						// Add mentioned list paragraph to message.
						$all_mentioned_email_array = array_unique( $all_mentioned_email_from_content_array );
						if ( ! empty( $all_mentioned_email_array ) ) {
							$all_mentioned_user_names        = array();
							$all_mentioned_text              = '';
							$all_mentioned_email_array_count = count( $all_mentioned_email_array );
							$count                           = 1;
							if (is_array($all_mentioned_email_array) || is_object($all_mentioned_email_array)) {
								foreach ( $all_mentioned_email_array as $all_mentioned_email_array_value ) {
									$mentioned_user = get_user_by( 'email', $all_mentioned_email_array_value );
									array_push( $all_mentioned_user_names, $mentioned_user->display_name );
									if ( $count === $all_mentioned_email_array_count ) {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '> ';
									} else {
										$all_mentioned_text .= '<mailto:' . $all_mentioned_email_array_value . '|' . $mentioned_user->display_name . '>, ';
									}
									$count++;
								}
							}
							$all_mentioned_text .= __( ' mentioned you in a comment on this post.', 'content-collaboration-inline-commenting' );

							$mentioned_array = array(
								'type' => 'section',
								'text' => array(
									'type' => 'mrkdwn',
									'text' => $all_mentioned_text,
								),
							);

							array_unshift( $notification['attachments'][0]['blocks'], $mentioned_array );
						}

						// New method.
						array_push( $notification['attachments'][0]['blocks'], array( 'type' => 'divider' ) );
						$blocks = wp_json_encode( $notification['attachments'][0]['blocks'] );
						$this->cf_send_slack_notification( $blocks );
					}
				}
			}
		}
		return $suggestion;
	}

	/**
	 * Comments loop for Suggestion Email Templates.
	 *
	 * @return string HTML for comments loop.
	 */
	function sg_email_get_suggestion_comments_loop__premium_only() {
		ob_start();
		if ( ! empty( $this->list_of_comments ) ) {
			?> 
		<table class="comment-list" style="width:95%;">
			<?php
			$this_list_of_comments = $this->list_of_comments;
			if ((is_array($this_list_of_comments) && !empty($this_list_of_comments)) || (is_object($this_list_of_comments) && !empty((array)$this_list_of_comments))) {
	            foreach ($this->list_of_comments as $sg_comment) { // phpcs:ignore
						$sg_comment = (array) $sg_comment;

						$user_info            = get_userdata( $sg_comment['uid'] );
						$user_role            = implode( ', ', $user_info->roles );
						$username             = $user_info->display_name;
	                    $this->users_emails[] = $user_info->user_email; // phpcs:ignore
						$profile_url          = get_avatar_url( $user_info->user_email );
					?>

						<tr>
							<td style="padding-bottom:20px">
								<table class="comment-box-wrap" style="width:95%;font-family:Roboto,serif;font-size:14px;color:#4C5056;">
									<tr valign="top">
										<td class="avatar" style="width:32px;padding-right:5px;">
											<img src="<?php echo esc_url_raw( $profile_url ); ?>" alt="avatar" style="max-width:95%;border-radius:50%;" />
										</td>
										<td class="comment-details">
											<table class="commenter-name-role" valign="middle" style="width:95%;font-family:Roboto,sans-serif;">
												<tr>
													<th align="left">
														<span class="commenter-name" style="font-size:16px;font-family:Roboto,sans-serif;padding-right:5px;color:#1D2327;font-weight:500;text-transform:capitalize;"><?php echo esc_html( $username ); ?></span>
														<span class="commenter-role" style="font-weight:400;color:#50575E;font-size:14px;font-family: Roboto,sans-serif;">(<?php echo esc_html( translate_user_role( ucwords( $user_role ) ) ); ?>)</span>
													</th>
												</tr>
												<tr>
													<td align="left" class="comment"><?php echo wp_kses( $sg_comment['text'], wp_kses_allowed_html( 'post' ) ); ?></td>
												</tr>
												<?php if ( ! empty( $sg_comment['attachmentText'] ) ) { ?>
													<tr>
														<td align="left" class="attachment" style="padding-top:10px;">
															<img src="<?php echo esc_url( trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/images/attach.png' ); ?>" alt="Attach" class="attachImg" width="14" height="12" style="vertical-align: middle;"/>
															<?php echo wp_kses( $sg_comment['attachmentText'], wp_kses_allowed_html( 'post' ) ); ?>
														</td>
													</tr>
												<?php } ?>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					<?php
				}
			}
			?>
		</table>

			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Send slack messages for all actions.
	 *
	 * @param array $blocks
	 * @return void
	 */
	function cf_send_slack_notification( $blocks ) {
		$access_token      = get_option( 'access_token' );
		$user_access_token = get_option( 'user_access_token' );
		$channel           = get_option( 'channel_id' );
		$headers           = array( 'Accept' => 'application/json' );

		// Add the application id and secret to authenticate the request.
		$options = array( 'auth' => array( CF_SLACK_CLIENT_ID, CF_SLACK_CLIENT_SECRET ) );

		// Add the one-time token to request parameters.
		$data = array(
			'token'   => $access_token,
			'channel' => $channel,
			'blocks'  => $blocks,
		);

		$api_root      = 'https://slack.com/api/';
		
		global $wp_version;
		if( (double) $wp_version < 6.2 ) {
			$response	= Requests::post( $api_root . 'chat.postMessage', $headers, $data, $options );
		} else {
			$response	= \WpOrg\Requests\Requests::post( $api_root . 'chat.postMessage', $headers, $data, $options );
		}

		$json_response = json_decode( $response->body ); // phpcs:ignore\

		if ( true !== $json_response->ok ) {
			// Add the one-time token to request parameters.
			$data = array(
				'token'   => $user_access_token,
				'channel' => $channel,
				'blocks'  => $blocks,
			);

			$notification            = array();
			$notification['channel'] = $channel;
			$notification['blocks']  = $blocks;

			// Make an API call.
			$api_call_data = apply_filters(
				'slack_before_api_call',
				array(
					'method'      => 'POST',
					'httpversion' => '1.0',
					'blocking'    => true,
					'body'        => array(
						'payload' => wp_json_encode( $notification ),
					),
				)
			);
			$webhook_url   = get_option( 'cf_slack_webhook' );
			$response      = wp_remote_request( $webhook_url, $api_call_data );

		}
	}
}
