<form class="cf-settings-panel__repeater-body" id="cf_license"  method="post">
	<p>
		<?php
		$license_key_description = esc_html__( 'Enter the license key from your purchase email or access your licenses in the', 'content-collaboration-inline-commenting' );
		$my_account_link = sprintf(
			'<a href="%s" target="_blank">%s <img class="cf-external-link-icon" src="%s" alt="external-link"></a>',
			esc_url( CF_STORE_URL ) . '/new/my-account/',
			esc_html__( 'My Account', 'content-collaboration-inline-commenting' ),
			esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/arrow_blue.svg' )
		);
		$multicollab_section_description = esc_html__( 'section on the Multicollab website.', 'content-collaboration-inline-commenting' );

		printf( '%s %s %s', esc_html( $license_key_description ), wp_kses_post( $my_account_link ), esc_html( $multicollab_section_description ) );
		?>
	</p>
	<div class="cf-settings-panel__repeater-body">
	<?php
	$cf_expire_date     = get_option( 'cf_expire_date' );
	$cf_license_status  = get_option( 'cf_license_status' );
	$cf_expire_date_new = gmdate( 'M d, Y', strtotime( $cf_expire_date ) );
	if ( ! empty( $cf_activated_license_details ) ) {
		$license_key_show = apply_filters( 'cf_setting_tab_license_key_value', $cf_activated_license_details->license_key );
		?>
				<div class="cf-license-settings">
					<span class="cf-license-activator-input"><input id="cf-license-activator" type="text" name="cf_license_key" placeholder="<?php esc_attr_e( 'Enter your license key', 'content-collaboration-inline-commenting' ); ?>" value="<?php echo esc_attr( $license_key_show ); ?>"><span class="dashicons dashicons-yes-alt"></span></span>
					<input id="cf-license-deactivate-submit" data-activate="Deactivate License" data-activating="Verifying License..." value="<?php esc_attr_e( 'Deactivate License', 'content-collaboration-inline-commenting' ); ?>" type="submit" class="button button-primary">
					<input id="cf_license_action" type="hidden" value="deactivate" name="license_action">
					<div class="cf-license-notices" style="">
					<?php
					if ( 'expired' === $cf_activated_license_details->license_status ) {
						?>
						<div class="cf-notice notice-expired">
								<p><?php printf( '%s %s %s', esc_html__( 'Your license key expires on', 'content-collaboration-inline-commenting' ), esc_html__( $cf_expire_date_new ), esc_html__( 'Please renew your license key.', 'content-collaboration-inline-commenting' ) ); ?></p>
							</div>
						<?php
					} else {
						?>
							<div class="cf-notice notice-success">
							<p><?php printf( '%s %s', esc_html__( 'Your license key expires on', 'content-collaboration-inline-commenting' ), esc_html__( $cf_expire_date_new ) ); ?></p>
							</div>
							<?php
					}
					?>
					</div>
					<div class="cf-license-sucess activate"><span></span></div>
				</div>
			<?php
	} else {
		?>
			<div class="cf-license-settings">
				<span class="cf-license-activator-input"><input id="cf-license-activator" type="text" name="cf_license_key" placeholder="<?php esc_attr_e( 'Enter your license key', 'content-collaboration-inline-commenting' ); ?>" value=""></span>
				<input id="cf-license-activator-submit" data-activate="Activate License" data-activating="Verifying License..." value="<?php esc_attr_e( 'Activate License', 'content-collaboration-inline-commenting' ); ?>" type="submit" class="button button-primary">
				<input id="cf_license_action" type="hidden" value="activate" name="license_action">
				<div class="cf-license-notices" style="display: none;"></div>
				<div class="cf-license-sucess" style="display: none;"></div>
			</div>
		<?php } ?>
		<?php
		if ( ! empty( $cf_activated_license_details ) ) {
			$cf_license_limit = '';
			if ( '0' === $cf_activated_license_details->license_limit ) {
				$cf_license_limit = '&infin;';
			} else {
				$cf_license_limit = $cf_activated_license_details->license_limit;
			};
			?>
			<div class="license_check_status">
				<h4><?php esc_html_e( 'License Information:', 'content-collaboration-inline-commenting' ); ?></h4>
				<table class="widefat striped fixed">
					<tr>
						<td><?php esc_html_e( 'Plan', 'content-collaboration-inline-commenting' ); ?></td>
						<td><?php esc_html_e( 'License Status', 'content-collaboration-inline-commenting' ); ?></td>
						<td><?php esc_html_e( 'Expires', 'content-collaboration-inline-commenting' ); ?></td>
						<td><?php esc_html_e( 'Activations', 'content-collaboration-inline-commenting' ); ?></td>
					</tr>
					<tr>
						<td><?php echo esc_html( ucwords( $cf_activated_license_details->item_name ) ); ?></td>
						<td><?php echo esc_html( ucwords( $cf_license_status ) ); ?></td>
						<td><?php echo esc_html( $cf_expire_date_new ); ?></td>
						<td><?php echo esc_html( $cf_activated_license_details->site_count ) . ' / ' . esc_html( $cf_license_limit ); ?></td>
					</tr>
				</table>
			</div>
		<?php } ?>
	</div>
</form>
