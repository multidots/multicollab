<form class="cf-cnt-box-body" method="post" id ="cf_email_notification">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<div class="cf-notification-settings">
		<div class="cf-check-wrap">
			<input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : ''; ?> value="1" class="regular-text" />
			<span class="cf-check"></span>
		</div>
		<label for="cf_admin_notif"><?php printf( '%s <b>%s</b>', esc_html__( 'Notify', 'content-collaboration-inline-commenting' ), esc_html__( 'Super Admin', 'content-collaboration-inline-commenting' ) ); ?> (<?php echo esc_html( get_option( 'admin_email' ) ); ?>) <?php esc_html_e( 'for all new comments. (Not Recommended)', 'content-collaboration-inline-commenting' ); ?>
			
		</label>
	</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	</div>
</form>
