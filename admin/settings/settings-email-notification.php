<form class="cf-cnt-box-body" method="post" id ="cf_email_notification">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php 
esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' );
?></p>
		</div>
	</div>
	<?php 
$disabled = '';
$disabled_class = '';

if ( 1 !== (int) cf_fs()->is__premium_only() ) {
    $disabled = 'disabled';
    $disabled_class = 'cf_disabled_input';
}

?>
	<div class="cf-notification-settings">
		<div class="cf-check-wrap">
			<input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php 
echo  ( '1' === $cf_admin_notif ? 'checked' : '' ) ;
?> value="1" class="regular-text" <?php 
echo  esc_html( $disabled ) ;
?>/>
			<span class="cf-check"></span>
		</div>
		<label for="cf_admin_notif"><?php 
printf( '%s <b>%s</b>', esc_html__( 'Notify', 'content-collaboration-inline-commenting' ), esc_html__( 'Super Admin', 'content-collaboration-inline-commenting' ) );
?> (<?php 
echo  esc_html( get_option( 'admin_email' ) ) ;
?>) <?php 
esc_html_e( 'for all new comments. (Not Recommended)', 'content-collaboration-inline-commenting' );
?><span class="cf_premium_star"><?php 
printf( '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg> %s', esc_html__( 'Premium', 'content-collaboration-inline-commenting' ) );
?></span></label>
	</div>
	<div class="cf-submit-button-settings"><?php 
submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) );
?>
	<?php 
?>
			<a href="https://www.multicollab.com/pricing/?utm_source=plugin_setting_header_free-user_upgrade_to_premium&utm_medium=header_free-user_upgrade_to_premium_link&utm_campaign=plugin_setting_free-user_upgrade_to_premium_link&utm_id=plugin_setting_header_link.++" target="_blank" class="cf-board-premi-btn">Upgrade to Premium<svg id="Group_52548" data-name="Group 52548" xmlns="http://www.w3.org/2000/svg" width="27.263" height="24.368" viewBox="0 0 27.263 24.368"><path id="Path_199491" data-name="Path 199491" d="M333.833,428.628a1.091,1.091,0,0,1-1.092,1.092H316.758a1.092,1.092,0,1,1,0-2.183h15.984a1.091,1.091,0,0,1,1.091,1.092Z" transform="translate(-311.117 -405.352)" fill="#d0a823"></path><path id="Path_199492" data-name="Path 199492" d="M312.276,284.423h0a1.089,1.089,0,0,0-1.213-.056l-6.684,4.047-4.341-7.668a1.093,1.093,0,0,0-1.9,0l-4.341,7.668-6.684-4.047a1.091,1.091,0,0,0-1.623,1.2l3.366,13.365a1.091,1.091,0,0,0,1.058.825h18.349a1.09,1.09,0,0,0,1.058-.825l3.365-13.365A1.088,1.088,0,0,0,312.276,284.423Zm-4.864,13.151H290.764l-2.509-9.964,5.373,3.253a1.092,1.092,0,0,0,1.515-.4l3.944-6.969,3.945,6.968a1.092,1.092,0,0,0,1.515.4l5.373-3.253Z" transform="translate(-285.455 -280.192)" fill="#d0a823"></path></svg></a>
			<a href="https://www.multicollab.com/pricing/?trial=#utm_source=multicollab_home_page&utm_medium=homepage_freetrial_button1&utm_id=multicollab_home_page&utm_term=start_your_14free_trial_now" target="_blank" class="cf-board-free-btn">Try Premium for 14 Days</a>
		<?php 
?>
	</div>
</form>
