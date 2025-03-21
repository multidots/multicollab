<?php 

$unlock_premium_popup_data = filter_input( INPUT_COOKIE, 'cf_unlock_premium_popup', FILTER_SANITIZE_URL );

if ( ! empty( $unlock_premium_popup_data ) ) {
	$unlock_premium_popup_data = json_decode( $unlock_premium_popup_data, true );
} else {
	$unlock_premium_popup  = CF_STORE_URL . 'wp-json/cf-unlock-premium-popup/v2/cf-unlock-premium-popup?' . wp_rand();
	if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
		$unlock_premium_popup_request = vip_safe_wp_remote_get( $unlock_premium_popup, 3, 1, 20 );
	} else {
		$unlock_premium_popup_request = wp_remote_get( $unlock_premium_popup, array( 'timeout' => 20 ) ); // phpcs:ignore
	}
	
	if ( ! is_wp_error( $unlock_premium_popup_request ) ) {
		$unlock_premium_popup_body = wp_remote_retrieve_body( $unlock_premium_popup_request );
		$unlock_premium_popup_data = json_decode( $unlock_premium_popup_body, true );
	
		if( isset( $unlock_premium_popup_data ) && ! empty( $unlock_premium_popup_data ) ) {
			setcookie( 'cf_unlock_premium_popup', wp_json_encode( $unlock_premium_popup_data ), time() + 3600 * 6, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
	}
}
?>

<div class="cf-plugin_modal cf-plugin_upgrademodal" role="dialog" id="cf-plugin_upgrademodal">
	<div class="cf-pro-modal-dialog-outer">
	<div class="cf-pro-modal-dialog-wrapper">
		<div class="cf-pro-modal-dialog" role="document">

			<div class="cf-pro-modal__header">
				<img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/upgrade-pro-bg-img.webp'); ?>" alt="Upgrade to Pro">
				<span class="dashicons dashicons-no-alt modal-close-btn"></span>
			</div>
			<div class="cf-pro-modal__header">
				<h3 class="cf-pro-modal-title"><?php echo esc_html__( 'Unlock Premium Collaboration with a ' . $unlock_premium_popup_data['discount'] . '% Discount!', 'content-collaboration-inline-commenting' ); ?></h3>
			</div>

			<div class="cf-pro-modal__body">
				<p><?php echo esc_html__( "Enhance your content workflow in WordPress with our Multicollab plugin's Premium Features!", "content-collaboration-inline-commenting" ); ?></p>
				<ul>
					<li><?php echo esc_html__( "Multicollab can increase 2x speed to your publishing workflow." , "content-collaboration-inline-commenting" ); ?></li>
					<li><?php echo esc_html__( "Collaborate directly in WordPress with advanced permissions and controls." , "content-collaboration-inline-commenting" ); ?></li>
					<li><?php echo esc_html__( "Track and manage comments across multiple users seamlessly." , "content-collaboration-inline-commenting" ); ?></li>
					<li><?php echo esc_html__( "Assign roles and tasks to streamline editorial processes." , "content-collaboration-inline-commenting" ); ?></li>
				</ul>
			</div>
			<div class="cf-pro-modal__footer">
				<a href="https://www.multicollab.com/checkout/?discount=<?php echo esc_attr( $unlock_premium_popup_data['discount_coupon_code'] ) ?>" target="_blank" class="cf-plugin_upgradenow__btn">
					<?php echo esc_html__( 'Upgrade Now', 'content-collaboration-inline-commenting' ); ?>
				</a>
			</div>
		</div>
	</div>
	</div>
</div>