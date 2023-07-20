<?php
/**
 * Email Design templates.
 *
 * @package multicollab
 */

/**
 * Class Email_Template
 */
class Guest_Email_Template {
	
	/**
	 * Construct method.
	 */
	public function __construct() {

	}

	/**
	 * Invitation mail sent when user come from landing page.
	 *
	 * @param  string $email Email address.
	 * @param  string $name User Name.
	 * @param  string $login_url Magic link URL.
	 * @param  string $inviter_name it consist name of the inviter.
	 * @param  string $inviter_email it consist email of the inviter.
	 * @param string $inviter_profile_image
	 * @param string $post_title
	 * @param string $post_image
	 * @param string $last_edited_by
	 * @param string $last_edited_on
	 * @param string $guest_cap_role
	 * @param string $post_link
	 * @return void
	 */
	public function invitation_mail_html( $inviter_name, $inviter_email, $inviter_profile_image, $post_title, $post_image, $email, $last_edited_by, $last_edited_on, $login_url, $user_message, $guest_cap_role, $post_link ) {
		if ( ! $guest_cap_role ) {
			$guest_cap_role = 'View';
		}
		if ( ! $post_image ) {
			$post_image = trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/images/invit-defult-postimg.png';
		}
		$access_role = $guest_cap_role ?? '';
		if ( 'Comment' === $access_role || 'comment' === $access_role ) {
			$access_role = $access_role.' '.'on';
		}
		if ( $user_message ) {
			$user_message = '<tr>
				<th style="text-align: left;padding: 0px 24px 15px;"><span style="font-weight: 400;font-size: 16px;">' . esc_html__( $user_message, 'content-collaboration-inline-commenting' ) . '</span></th>
			</tr>';
		} else {
			$user_message = '';
		}
		ob_start();
		?>

		<!DOCTYPE html>
		<html>
			<head>
				<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
				<title><?php _e( 'Email Template', 'content-collaboration-inline-commenting' ); ?></title>
			</head>

			<body>
				<table style="max-width: 656px;width:100%;border-spacing:0;font-family:'Roboto', sans-serif;margin:auto;border:1px solid #DADCE0;border-radius: 6px">
					<tbody>
						<tr>
							<td>
								<table>
									<tbody>
										<tr>
											<th>
												<h2 style="font-size:26px;font-weight:normal;line-height:normal;color:#3C4043;text-align:left;padding:25px 28px 29px;margin:0;">
													<?php echo esc_html__( $inviter_name.' has invited you.', 'content-collaboration-inline-commenting' ); ?>
												</h2>
											</th>
										</tr>
										<tr>
											<th style="padding:0 24px;">
												<span style="display:flex;align-items:center;margin-bottom:30px;">
													<img src="<?php echo esc_url( $inviter_profile_image ); ?>" style="width:40px;height:40px;border-radius: 50%;margin-right: 10px;" />
													<span style="font-size:15px;line-height:21px;font-weight:400;text-align:left;color:#202124;">
														<?php echo esc_html__( $inviter_name.' ('.$inviter_email.') has invited you to collaborate on the following post:', 'content-collaboration-inline-commenting' ); ?>
													</span>
												</span>
											</th>
										</tr>
										<?php echo wp_kses_post( __( $user_message, 'content-collaboration-inline-commenting' ) ); ?>
										<tr>
											<th style="padding: 0 24px;">
												<div style="border: 1px solid #DADCE0;width: 340px;border-radius: 8px;padding: 25px 20px 29px;">
													<span style="font-size:14px;line-height:16px;font-weight:400;color:#3C4043;display: flex;align-items: center;text-align:left;margin-bottom:11px;"><img width="15" height="15" src ="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/collaboration-doc.png' ); ?>"/><a style ="margin-left: 5px;text-decoration: none;font-size: 16px;font-size: 14px;line-height: 16px;color: #3C4043;font-family: \'Google\', sans-serif;" href="<?php echo esc_url( $post_link ); ?>" target="_blank"><?php echo esc_html__($post_title, 'content-collaboration-inline-commenting') ?></a></span>
													<div style="border:1px solid #DADCE0;border-radius:8px;padding:24px 50px 0px;margin-bottom: 20px;">
														<span style="font-family:\'Google\', sans-serif;color:#3C4043;font-size:11px;line-height:18px;font-weight:400;margin-bottom:10px;display:inline-block;"><?php echo esc_html__('Workflow - Guest Collaborator ('.$guest_cap_role.' Only)', 'content-collaboration-inline-commenting'); ?></span>
														<img src="<?php echo esc_url( $post_image ); ?>" style="width:130px;margin-bottom:-5px;" />
													</div>
													<span style="font-size:13px;line-height:18px;font-weight:400;color:#3C4043;display: flex;align-items: center;text-align:left;"><img width="15" height="15" src ="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/website.png' ); ?>"><a style ="margin-left: 5px;font-size: 16px;font-size: 14px;line-height: 16px;color: #3C4043;font-family: \'Google\', sans-serif;" href="<?php echo esc_url(get_home_url()); ?>"><?php echo esc_url(get_home_url()); ?></a></span>
												</div>
											</th>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td style="padding: 28px 24px 28px;">
								<a href="<?php echo esc_url( $login_url ); ?>" style="background-color:#4B1BCE;color:#fff;text-decoration:none;border-radius:17px;padding:9.5px 24.5px;font-size:13px;line-height:18px;font-weight:500;font-family:'Google',sans-serif;display:inline-block;"><?php _e('Open', 'content-collaboration-inline-commenting') ?></a>
							</td>
						</tr>
						<tr>
							<td style="padding: 0px 24px 19px;">
								<span style="font-size: 13px;font-weight: 400;color: #3C4043;line-height: 18px;display: inline-block;">
									<?php _e( 'If you don’t want to participate. After 48 hours your invitation will be canceled.', 'content-collaboration-inline-commenting' ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<td style="border-top:1px solid #DADCE0;padding:20px 24px;">
								<span style="font-size:13px;color:#3C4043;line-height:18px;font-weight:400;">
									<?php _e( 'Multicollab sent this email on behalf of ', 'content-collaboration-inline-commenting' ); ?><?php echo esc_html(get_bloginfo( 'name' )); ?><?php _e( '. If you have any questions or concerns, please ', 'content-collaboration-inline-commenting' ); ?><a href="https://www.multicollab.com/contact/" style="color:#4B1BCE;font-weight:700;"><?php _e( 'Contact Us!', 'content-collaboration-inline-commenting' ); ?></a>
								</span>
							</td>
						</tr>
					</tbody>
				</table>
			</body>
		</html>

		<?php

		$message = ob_get_clean();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . $inviter_name . ' <' . $inviter_email . '>',
			'From: ' . $inviter_name . ' <' . $inviter_email . '>',
		);

		$subject = 'Invite to collaborate with you on ' . html_entity_decode( $post_title );

		$client_mail = wp_mail( $email, wp_specialchars_decode( $subject ), $message, $headers ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail

	}

}
