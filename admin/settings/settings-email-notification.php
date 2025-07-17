<form class="cf-settings-panel__repeater-body" method="post" id ="cf_email_notification">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<div class="cf-settings-row">
		<div class="cf-settings-th">
			<label for="cf_admin_notif"><?php printf( '%s <b>%s</b>', esc_html__( 'Notify', 'content-collaboration-inline-commenting' ), esc_html__( 'Super Admin', 'content-collaboration-inline-commenting' ) ); ?> (<?php echo esc_html( get_option( 'admin_email' ) ); ?>) <?php esc_html_e( 'for all new comments. (Not Recommended)', 'content-collaboration-inline-commenting' ); ?>
			</label>
		</div>
		<div class="cf-settings-td">
			<label class="cf-settings-td-toggle">
				<input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : ''; ?> value="1" class="regular-text"/>
				<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
			</label>
		</div>
	</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	</div>
</form>
