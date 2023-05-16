<?php

require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-permissions.php';
$permission_list = new Commenting_Block_Permissions();
$data            = $permission_list->cf_get_pp_roles();

$disabled_class = '';
if ( 1 !== (int) $cf_edd->is__premium_only() ) {
	$disabled       = 'disabled';
	$disabled_class = 'cf_disabled_input';
	delete_option( 'cf_permissions' );
}
?>
<form class="cf-cnt-box-body" id="cf_permissions"  method="post"> 
		<div id="cf-permissions-notice">
			<div class="cf-success notices notice-success" style="display: none">
				<p><?php esc_html_e( 'Permissions saved!', 'content-collaboration-inline-commenting' ); ?></p>
			</div>
		</div>
	<table class="wp-list-table widefat fixed striped">
		<tr>
			<th><b><?php esc_html_e( 'Roles', 'content-collaboration-inline-commenting' ); ?></b></th>
			<th><b><?php esc_html_e( 'Add Comment', 'content-collaboration-inline-commenting' ); ?></b></th>
			<th><b><?php esc_html_e( 'Resolve Comment', 'content-collaboration-inline-commenting' ); ?></b></th>
			<th><b><?php esc_html_e( 'Disable Comments', 'content-collaboration-inline-commenting' ); ?></b></th>
			<?php
			if ( $cf_edd->is__premium_only() ) {
				if ( true === $cf_edd->is_plan( 'pro', true ) || true === $cf_edd->is_plan( 'vip', true ) ) {
					?>
			<th><b><?php esc_html_e( 'Add Suggestion', 'content-collaboration-inline-commenting' ); ?></b></th>
			<th><b><?php esc_html_e( 'Accept/Reject Suggestion', 'content-collaboration-inline-commenting' ); ?></b></th>
			<th><b><?php esc_html_e( 'Disable Suggestion', 'content-collaboration-inline-commenting' ); ?></b></th>
					<?php
				}
			}
			?>
				
		</tr>
		<?php

		foreach ( $data as $key => $value ) {

				$disabled = (1 == isset($value['role']['capabilities']['edit_posts']) || 1 == isset($value['role']['capabilities']['edit_pages']) )? '':'disabled'; //phpcs:ignore
				$options             = get_option( 'cf_permissions' );
				$disabled_comment = ("1" == isset($options[$key]["hide_comment"])) ? 'disabled' : '';//phpcs:ignore
				$disabled_suggestion = ('1'== isset($options[$key]["hide_suggestion"])) ? 'disabled' : '';//phpcs:ignore             
			?>
			<tr><td><?php echo esc_html( translate_user_role( $value['role']['name'] ) ); ?> </td>
			
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[add_comment]' id ='cf_add_comment' <?php echo '1' ==  isset($options[$key]['add_comment']) ? 'checked' : '' ?>  value='1'  <?php echo esc_attr($disabled.$disabled_comment);?>/></td> <?php  //phpcs:ignore ?>
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[resolved_comment]'  id ='cf_resolved_comment' <?php echo '1' == isset($options[$key]['resolved_comment']) ? 'checked' : '' ?>  value='1' <?php echo esc_attr($disabled.$disabled_comment);?>/></td> <?php  //phpcs:ignore ?>
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[hide_comment]'  id ='cf_hide_comment' <?php echo '1' ==isset( $options[$key]['hide_comment']) ? 'checked' : '' ?>  value='1' <?php echo esc_attr($disabled); ?>/></td> <?php  //phpcs:ignore ?>
			<?php
			if ( $cf_edd->is__premium_only() ) {
				if ( $cf_edd->is_plan( 'pro', true ) || $cf_edd->is_plan( 'vip', true ) ) {
					?>
	
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[add_suggestion]'  id ='cf_add_suggestion' <?php echo '1' == isset($options[$key]['add_suggestion']) ? 'checked' : '' ?> value='1' <?php echo esc_attr($disabled.$disabled_suggestion);?>/></td><?php  //phpcs:ignore ?>
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[resolved_suggestion]'  id ='cf_resolved_suggestion' <?php echo '1' == isset($options[$key]['resolved_suggestion']) ? 'checked' : '' ?> value='1' <?php echo esc_attr($disabled.$disabled_suggestion);?>/></td><?php  //phpcs:ignore ?>
			<td><input type='checkbox' name='<?php echo esc_attr($key)?>[hide_suggestion]'  id ='cf_hide_suggestion' <?php echo '1' == isset($options[$key]['hide_suggestion'] )? 'checked' : '' ?>  value='1' <?php echo esc_attr($disabled);?>/></td><?php  //phpcs:ignore ?>
					<?php
				}
			}
			?>
				
		</tr>

	<?php } ?>
	</table> 
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	<?php
	if ( ! $cf_edd->is__premium_only() ) {
		?>
			<a href="https://www.multicollab.com/pricing/?utm_source=plugin_setting_header_free-user_upgrade_to_premium&utm_medium=header_free-user_upgrade_to_premium_link&utm_campaign=plugin_setting_free-user_upgrade_to_premium_link&utm_id=plugin_setting_header_link.++" target="_blank" class="cf-board-premi-btn">Upgrade to Premium<svg id="Group_52548" data-name="Group 52548" xmlns="http://www.w3.org/2000/svg" width="27.263" height="24.368" viewBox="0 0 27.263 24.368"><path id="Path_199491" data-name="Path 199491" d="M333.833,428.628a1.091,1.091,0,0,1-1.092,1.092H316.758a1.092,1.092,0,1,1,0-2.183h15.984a1.091,1.091,0,0,1,1.091,1.092Z" transform="translate(-311.117 -405.352)" fill="#d0a823"></path><path id="Path_199492" data-name="Path 199492" d="M312.276,284.423h0a1.089,1.089,0,0,0-1.213-.056l-6.684,4.047-4.341-7.668a1.093,1.093,0,0,0-1.9,0l-4.341,7.668-6.684-4.047a1.091,1.091,0,0,0-1.623,1.2l3.366,13.365a1.091,1.091,0,0,0,1.058.825h18.349a1.09,1.09,0,0,0,1.058-.825l3.365-13.365A1.088,1.088,0,0,0,312.276,284.423Zm-4.864,13.151H290.764l-2.509-9.964,5.373,3.253a1.092,1.092,0,0,0,1.515-.4l3.944-6.969,3.945,6.968a1.092,1.092,0,0,0,1.515.4l5.373-3.253Z" transform="translate(-285.455 -280.192)" fill="#d0a823"></path></svg></a>
		<?php
	}
	?>
	</div>
</form>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function ($) {
		$(document).on('change', '#cf_hide_comment', function () {
			var isChecked = $(this).prop('checked');
			if(true === isChecked){
				$(this).closest('tr').find('#cf_add_comment').prop("checked", false).attr('disabled', true);
				$(this).closest('tr').find('#cf_resolved_comment').prop("checked", false).attr('disabled', true);   
			}else{
				$(this).closest('tr').find('#cf_add_comment').attr('disabled', false);
				$(this).closest('tr').find('#cf_resolved_comment').attr('disabled', false);   
			}
		})
		$(document).on('change', '#cf_hide_suggestion', function () {
			var isChecked = $(this).prop('checked');
			if(true === isChecked){
				$(this).closest('tr').find('#cf_add_suggestion').prop("checked", false).attr('disabled', true);
				$(this).closest('tr').find('#cf_resolved_suggestion').prop("checked", false).attr('disabled', true);   
			}else{
				$(this).closest('tr').find('#cf_add_suggestion').attr('disabled', false);
				$(this).closest('tr').find('#cf_resolved_suggestion').attr('disabled', false);   
			}
		})
	})
</script>
