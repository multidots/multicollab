<form class="cf-settings-panel__repeater-body" method="post" id="cf_suggestion_settings">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<div class="cf-suggestion-option  cf-suggestion-option ">
		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_give_alert_messageg">
					<h4><?php printf( '<b>%s</b> %s <b>%s</b>', esc_html__( 'Remind', 'content-collaboration-inline-commenting' ), esc_html__( 'authors of pending comments before publishing while still', 'content-collaboration-inline-commenting' ), esc_html__( 'allowing publish the post.', 'content-collaboration-inline-commenting' ) ); ?>
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf-suggestion-tooltip-box">
									<a href="#." class="cf-tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
									<div style="display:flex;align-items;flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/remind-option.jpg' ); ?>"> </div>
								</div>
						</span>
					</h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'If this option is selected, everyone will be reminded when they try to publish a post that has unresolved comments or pending suggestions. This is useful to alert authors to review the unresolved comments and changes before they Publish the post.', 'content-collaboration-inline-commenting' ); ?></p>
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_give_alert_message" class="cf-checkbox cf-suggestion-stop__publish-options" id="cf_give_alert_message" value="remind" class="regular-text" <?php echo 'remind' === $cf_give_alert_message ? 'checked' : ''; ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>	
		</div>

		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_stop_publishingf">
					<span class="cf-suggestion-option label">
						<h4><?php printf( '<b>%s</b> %s', esc_html__( 'Stop', 'content-collaboration-inline-commenting' ), esc_html__( 'authors from publishing posts with pending comments.', 'content-collaboration-inline-commenting' ) ); ?> </h4>
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf-suggestion-tooltip-box">
									<a href="#." class="cf-tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
										<div style="display:flex;align-items;flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/stop-option.jpg' ); ?>"></div>
								</div>
						</span>
					</span>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'If this option is selected, users will not be able to publish a post with unresolved comments or pending suggestions. This is useful to prevent publishing a post with unresolved comments and pending suggestions.', 'content-collaboration-inline-commenting' ); ?></p>
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_give_alert_message" class="cf-checkbox cf-suggestion-stop__publish-options" id="cf_stop_publishing" value="stop" class="regular-text" <?php echo 'stop' === $cf_give_alert_message ? 'checked' : ''; ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>		
		</div>
		<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
		</div>
	</div>
</form>
