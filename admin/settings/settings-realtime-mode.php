<form class="cf-cnt-box-body" method="post" id ="cf_multiedit_mode">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
		<div class="cf-error notices notice-error" style="display: none">
			<p><?php esc_html_e( 'Please select any option to save!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	
	<div class="cf_suggestion_option">
		<div class="cf-notification-settings multiedit-websocket-settings">
			<!-- <div class="cf-websocket"> -->
				<div class="cf-check-wrap">
					<input type="checkbox" name="cf_websocket_options" class="cf-checkbox cf_websocket_options" id="cf_websocket_default" value="cf_websocket_default" class="regular-text" <?php echo 'cf_websocket_default' === $cf_websocket_options ? 'checked' : ''; ?>/>
					<span class="cf-check"></span>
				</div>
				<label for="cf_websocket_default">
					<span class="cf_suggestion_optionlabel"><?php printf( '%s', esc_html__( "Use Multicollab's complimentary WebSocket service.", 'content-collaboration-inline-commenting' ) ); ?>
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf_suggestion-tooltip-box">
									<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
									<div style="display:flex;align-items;flex-start;"><span><?php esc_html_e( 'WebSocket Service is required to use a real-time editing feature. You can use our free WebSocket service to enable real-time editing.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
								</div>
						</span>
					</span>
				</label>
			<!-- </div>  -->
		</div>
		<div class="cf-notification-settings multiedit-websocket-settings">
			<div class="cf-websocket">
				<div class="cf-check-wrap">
					<input type="checkbox" name="cf_websocket_options" class="cf-checkbox cf_websocket_options" id="cf_websocket_custom" value="cf_websocket_custom" class="regular-text" <?php echo 'cf_websocket_custom' === $cf_websocket_options ? 'checked' : ''; ?>/>
					<span class="cf-check"></span>
				</div>
				<label for="cf_websocket_custom">
					<span class="cf_suggestion_optionlabel"><?php esc_html_e( 'Your WebSocket URL:', 'content-collaboration-inline-commenting' ); ?>
						<span class="md-plugin-tooltip">
							<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
								<div class="cf_suggestion-tooltip-box">
									<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
									<div style="display:flex;align-items;flex-start;"><span><?php esc_html_e( 'WebSocket Service is required to use a real-time editing feature. If you want to use your own WebSocket service, then follow the setup guidelines and update your WebSocket URL here.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
								</div>
						</span>
					</span>
					<div class="cf_websocket_custom_url_section">
						<input id="cf_multiedit_websocket" type="url" name="cf_multiedit_websocket" placeholder="<?php esc_attr_e( 'wss://your-WebSocket-URL/ws/', 'content-collaboration-inline-commenting' ); ?>" value="<?php if ( 'cf_websocket_custom' === $cf_websocket_options ) { echo esc_attr( $cf_multiedit_websocket ); } ?>" 
						<?php
							if ( 'cf_websocket_custom' !== $cf_websocket_options ) {
								echo 'disabled'; 
							}
						?>
						required='required'
						/>
						<?php
							printf(
								'-<a href="https://docs.multicollab.com/settings/real-time-editing" target="_blank"> %s <img class="cf-external-link-icon" src="%s" alt="external-link"></a>',
								esc_html__( 'Guide to Setup WebSocket', 'content-collaboration-inline-commenting' ),
								esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' )
							);
						?>

					</div>
				</label>
				
				
			</div>
		</div>
		
	</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	</div>
</form>
