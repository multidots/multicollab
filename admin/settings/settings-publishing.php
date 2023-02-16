<form class="cf-cnt-box-body" method="post" id="cf_suggestion_settings">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
		<div class="cf_suggestion_option cf_publishing_option">
			<div class="cf-notification-settings">
				<div class="cf-check-wrap">
					<input type="checkbox" name="cf_give_alert_message" class="cf-checkbox cf_suggestion_stop_publish_options" id="cf_give_alert_message" value="remind" class="regular-text" <?php echo 'remind' === $cf_give_alert_message ? 'checked' : ''; ?>/>
					<span class="cf-check"></span>
				</div>
				<label for="cf_give_alert_message">
					<span class="cf_suggestion_optionlabel"><?php printf( '<b>%s</b> - %s <b>%s</b>', esc_html__( 'Remind', 'content-collaboration-inline-commenting' ), esc_html__( 'When an author tries to Publish a post with pending comments or suggestions, alert and remind them about it, but they can', 'content-collaboration-inline-commenting' ), esc_html__( 'publish the post.', 'content-collaboration-inline-commenting' ) ); ?>
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf_suggestion-tooltip-box">
									<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
									<div style="display:flex;align-items;flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/remind-option.jpg' ); ?>"> <span><?php esc_html_e( 'If this option is selected, everyone will be reminded when they try to publish a post that has unresolved comments or pending suggestions. This is useful to alert authors to review the unresolved comments and changes before they Publish the post.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
								</div>
						</span>
					</span>
				</label>	
			</div>

			<div class="cf-notification-settings">
				<div class="cf-check-wrap">
					<input type="checkbox" name="cf_give_alert_message" class="cf-checkbox cf_suggestion_stop_publish_options" id="cf_stop_publishing" value="stop" class="regular-text" <?php echo 'stop' === $cf_give_alert_message ? 'checked' : ''; ?>/>
					<span class="cf-check"></span>
				</div>
				<label for="cf_stop_publishing">
					<span class="cf_suggestion_optionlabel">
						<?php printf( '<b>%s</b> - %s <b>%s</b>', esc_html__( 'Stop', 'content-collaboration-inline-commenting' ), esc_html__( 'When an author tries to publish a post with pending comments or suggestions, remind them about it and', 'content-collaboration-inline-commenting' ), esc_html__( 'stop them from publishing it.', 'content-collaboration-inline-commenting' ) ); ?> 
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf_suggestion-tooltip-box">
									<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
									<div style="display:flex;align-items;flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/stop-option.jpg' ); ?>"> <span><?php esc_html_e( 'If this option is selected, users will not be able to publish a post with unresolved comments or pending suggestions. This is useful to prevent publishing a post with unresolved comments and pending suggestions.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
								</div>
						</span>
					</span>
				</label>	
			</div>

		</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	<?php
	if ( ! $cf_edd->is__premium_only() ) {
		?>
			<a href="https://www.multicollab.com/pricing/?utm_source=plugin_setting_header_free-user_upgrade_to_premium&utm_medium=header_free-user_upgrade_to_premium_link&utm_campaign=plugin_setting_free-user_upgrade_to_premium_link&utm_id=plugin_setting_header_link.++" target="_blank" class="cf-board-premi-btn">Upgrade to Premium<svg id="Group_52548" data-name="Group 52548" xmlns="http://www.w3.org/2000/svg" width="27.263" height="24.368" viewBox="0 0 27.263 24.368"><path id="Path_199491" data-name="Path 199491" d="M333.833,428.628a1.091,1.091,0,0,1-1.092,1.092H316.758a1.092,1.092,0,1,1,0-2.183h15.984a1.091,1.091,0,0,1,1.091,1.092Z" transform="translate(-311.117 -405.352)" fill="#d0a823"></path><path id="Path_199492" data-name="Path 199492" d="M312.276,284.423h0a1.089,1.089,0,0,0-1.213-.056l-6.684,4.047-4.341-7.668a1.093,1.093,0,0,0-1.9,0l-4.341,7.668-6.684-4.047a1.091,1.091,0,0,0-1.623,1.2l3.366,13.365a1.091,1.091,0,0,0,1.058.825h18.349a1.09,1.09,0,0,0,1.058-.825l3.365-13.365A1.088,1.088,0,0,0,312.276,284.423Zm-4.864,13.151H290.764l-2.509-9.964,5.373,3.253a1.092,1.092,0,0,0,1.515-.4l3.944-6.969,3.945,6.968a1.092,1.092,0,0,0,1.515.4l5.373-3.253Z" transform="translate(-285.455 -280.192)" fill="#d0a823"></path></svg></a>
			<a href="https://www.multicollab.com/pricing/?is_trial_period=yes" target="_blank" class="cf-board-free-btn">Try Premium for 14 Days</a>
		<?php
	}
	?>
	</div>
</form>
