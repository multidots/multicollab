<form class="cf-settings-panel__repeater-body" method="post" id ="cf_multiedit_mode">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
		<div class="cf-error notices notice-error" style="display: none">
			<p><?php esc_html_e( 'Please select any option to save!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	
	<div class="cf-suggestion-option ">
		<div class="cf-settings-row multiedit-websocket-settings">
			<!-- <div class="cf-websocket"> -->
				<div class="cf-settings-th">
					<label for="cf_websocket_default">
						<h4><?php printf( '%s', esc_html__( "Use Multicollab's complimentary WebSocket service.", 'content-collaboration-inline-commenting' ) ); ?></h4>
					</label>
					<p class="cf-setting-desc"><?php esc_html_e( 'WebSocket Service is required to use a real-time editing feature. You can use our free WebSocket service to enable real-time editing.', 'content-collaboration-inline-commenting' ); ?></p> 
				</div>
				<div class="cf-settings-td">
					<label class="cf-settings-td-toggle">
						<input type="checkbox" name="cf_websocket_options" class="cf-checkbox cf_websocket_options" id="cf_websocket_default" value="cf_websocket_default" class="regular-text"/>
						<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
					</label>
				</div>
			<!-- </div>  -->
		</div>
		<div class="cf-settings-row multiedit-websocket-settings">
			<div class="cf-settings-th">
				<label for="cf_websocket_custom">
					<h4><?php printf( '%s', esc_html__( 'Your WebSocket URL:', 'content-collaboration-inline-commenting' ) ); ?>
					</h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'WebSocket Service is required to use a real-time editing feature. If you want to use your own WebSocket service, then follow the setup guidelines and update your WebSocket URL here.', 'content-collaboration-inline-commenting' ); ?></p> 
		
				<div class="cf_websocket_custom_url_section">
					<input id="cf_multiedit_websocket" type="url" name="cf_multiedit_websocket" placeholder="<?php esc_attr_e( 'wss://your-WebSocket-URL/ws/', 'content-collaboration-inline-commenting' ); ?>" value="" required='required'/>
					<?php
						printf(
							'-<a href="https://docs.multicollab.com/settings/real-time-editing" target="_blank"> %s <img class="cf-external-link-icon" src="%s" alt="external-link"></a>',
							esc_html__( 'Guide to Setup WebSocket', 'content-collaboration-inline-commenting' ),
							esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/arrow_blue.svg' )
						);
					?>

				</div>
					</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
				<input type="checkbox" name="cf_websocket_options" class="cf-checkbox cf_websocket_options" id="cf_websocket_custom" value="cf_websocket_custom">					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>	
		</div>
		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_realtime_for_same_block">
					<h4><?php printf( '%s', esc_html__( 'Restrict Multiple Users to Edit same Block.', 'content-collaboration-inline-commenting' ) ); ?></h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( "If selected this option, Multiple users can't edit same block in realtime editing. By default, editing in same block is allowed for multiple users.", 'content-collaboration-inline-commenting' ); ?></p> 
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_realtime_for_same_block" class="cf-checkbox" id="cf_realtime_for_same_block" value="true" class="regular-text"/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>	
		</div>
		<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?></div>
	</div>
</form>
