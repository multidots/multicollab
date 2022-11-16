<form class="cf-cnt-box-body cf-slack-integration-form" id="cf_slack_intigration"  method="post">
	<div class="cf-cnt-box-body">
		<div id="cf-slack-notice">
			<div class="cf-success notices notice-success" style="display: none">
				<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
			</div>
		</div>
		<div class="cf-slack-settings" style="align-items: flex-start;">
			<div class="cf_webhook_url_input">
				<input type="hidden" class="field_type_text regular-text cf_slack_webhook"  name="cf_slack_webhook" value="<?php echo esc_attr( $cf_slack_webhook ); ?>">
				<input type="hidden" class="field_type_text regular-text cf_slack_channel"  name="channel" value="<?php echo esc_attr( $channel_id ); ?>">
				<input type="hidden" class="hidden_site_url" value="<?php echo esc_attr( site_url() ); ?>">
			</div>
		</div>
		<div class="cf-slack-integration-box">
			<div class="cf-slack-image">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.8 122.8"><path d="M25.8 77.6c0 7.1-5.8 12.9-12.9 12.9S0 84.7 0 77.6s5.8-12.9 12.9-12.9h12.9v12.9zm6.5 0c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9v32.3c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9V77.6z" fill="#e01e5a"></path><path d="M45.2 25.8c-7.1 0-12.9-5.8-12.9-12.9S38.1 0 45.2 0s12.9 5.8 12.9 12.9v12.9H45.2zm0 6.5c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9H12.9C5.8 58.1 0 52.3 0 45.2s5.8-12.9 12.9-12.9h32.3z" fill="#36c5f0"></path><path d="M97 45.2c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9-5.8 12.9-12.9 12.9H97V45.2zm-6.5 0c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9V12.9C64.7 5.8 70.5 0 77.6 0s12.9 5.8 12.9 12.9v32.3z" fill="#2eb67d"></path><path d="M77.6 97c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9-12.9-5.8-12.9-12.9V97h12.9zm0-6.5c-7.1 0-12.9-5.8-12.9-12.9s5.8-12.9 12.9-12.9h32.3c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9H77.6z" fill="#ecb22e"></path></svg>
			</div>
			<div class="cf-slack-integration-messages">
				<span><b><?php esc_html_e( 'Slack Channel Notifications', 'content-collaboration-inline-commenting' ); ?></b></span>
				<span><?php esc_html_e( 'You will get real-time updates for mentions, replies, and other comment activities of Multicollab in your Slack channel. It allows you to fit Multicollab in a place you know your team is checking all day.', 'content-collaboration-inline-commenting' ); ?></span>
			</div>
			<div class="cf-slack-integration-button">
				<?php
				if ( isset( $cf_slack_webhook ) && ! empty( $cf_slack_webhook ) ) {
					?>
						<input type="button" class="cf-slack-integration-connect" id="cf-slack-integration-disconnect" value="<?php echo esc_attr_e( 'Disconnect', 'content-collaboration-inline-commenting' ); ?>">
						<?php
				} else {
					?>
					<a href="https://slack.com/oauth/v2/authorize?client_id=<?php echo esc_attr( CF_SLACK_CLIENT_ID ); ?>&scope=incoming-webhook,chat:write,commands&user_scope=groups:write,channels:read,groups:read,channels:write&state=<?php echo esc_attr( site_url() ); ?>" class="cf-slack-integration-connect"><?php esc_html_e( 'Connect', 'content-collaboration-inline-commenting' ); ?></a>	
						<?php
				}
				?>
			</div>
		</div>
		<div class="cf-slack-integration-box" style="justify-content:flex-end;">
			<a class="slack-accordion-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ); ?></a>
		</div>
		<div class="cf-slack-inner-integration-box">
			<h4><?php esc_html_e( 'Slack Settings', 'content-collaboration-inline-commenting' ); ?></h4>
			<?php if ( ! empty( $cf_slack_webhook ) ) { ?>
				<div class="cf_slack_channel_setting">
					<h5><?php esc_html_e( 'Channel Name:', 'content-collaboration-inline-commenting' ); ?></h5>
					<select name="channels" id="cf_slack_channels">
						<option value=""><?php esc_html_e( 'Please select a channel', 'content-collaboration-inline-commenting' ); ?></option>
						<?php
						if ( ! empty( $cf_slack_channels ) ) {
							$lock_image = COMMENTING_BLOCK_URL . 'admin/assets/images/lock-icon.svg';
							$channel_id = get_option( 'channel_id' );
							foreach ( $cf_slack_channels as $channel ) {
								if ( $channel->id === $channel_id ) {
									$slected = 'selected';
								} else {
									$slected = '';
								}
								if ( true === $channel->is_private ) {
									echo '<option ' . esc_html( $slected ) . ' value="' . esc_attr( $channel->id ) . '" data-image="' . esc_attr( $lock_image ) . '" class="cf_slack_private_channel">' . esc_html( $channel->name ) . '</option>';
								} elseif ( false === $channel->is_private ) {
									echo '<option ' . esc_html( $slected ) . ' value="' . esc_attr( $channel->id ) . '"># ' . esc_html( $channel->name ) . '</option>';
								} else {
									echo '<option ' . esc_html( $slected ) . ' value="' . esc_attr( $channel->id ) . '">' . esc_html( $channel->name ) . '</option>';
								}
								$slected = '';
							}
						}
						?>
					</select>
				</div>
			<?php } ?>
			<div class="cf-slack-notification-wrap">
				<h5><?php esc_html_e( 'Notifications', 'content-collaboration-inline-commenting' ); ?></h5>
				<table class="wp-list-table widefat fixed striped">
					<tbody>
						<tr>
							<th><b><?php esc_html_e( 'Activities', 'content-collaboration-inline-commenting' ); ?></b></th>
							<th><b><?php esc_html_e( 'Notifications', 'content-collaboration-inline-commenting' ); ?></b></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Add Comment', 'content-collaboration-inline-commenting' ); ?></td>							   
							<td><input type="checkbox" name="cf_slack_notification_add_comment" id="cf_slack_notification" value="cf_slack_notification_add_comment" 
							<?php
							if ( '1' === $cf_slack_notification_add_comment ) {
								echo 'checked'; }
							?>
							></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Add Suggestion', 'content-collaboration-inline-commenting' ); ?></td>										   
							<td><input type="checkbox" name="cf_slack_notification_add_suggestion" id="cf_slack_notification" value="cf_slack_notification_add_suggestion" 
							<?php
							if ( '1' === $cf_slack_notification_add_suggestion ) {
								echo 'checked'; }
							?>
							></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Resolve Comment', 'content-collaboration-inline-commenting' ); ?></td>										   
							<td><input type="checkbox" name="cf_slack_notification_resolve_comment" id="cf_slack_notification" value="cf_slack_notification_resolve_comment" 
							<?php
							if ( '1' === $cf_slack_notification_resolve_comment ) {
								echo 'checked'; }
							?>
							></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Accept/Reject Suggestion', 'content-collaboration-inline-commenting' ); ?></td>										   
							<td><input type="checkbox" name="cf_slack_notification_accept_reject_suggestion" id="cf_slack_notification" value="cf_slack_notification_accept_reject_suggestion" 
							<?php
							if ( '1' === $cf_slack_notification_accept_reject_suggestion ) {
								echo 'checked'; }
							?>
							></td>
						</tr>
					</tbody>
				</table>
			</div>	
			<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?></div>
		</div>	
	</div>
</form>
