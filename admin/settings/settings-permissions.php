<?php

require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-permissions.php';
$permission_list = new Commenting_Block_Permissions();
$data            = $permission_list->cf_get_pp_roles();

$disabled       = 'disabled';
$disabled_class = 'cf_disabled_input';
delete_option( 'cf_permissions' );

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
		</tr>

	<?php } ?>
	</table> 
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
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
