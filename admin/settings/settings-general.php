<?php $disabled_class = 'cf_disabled_input'; ?>
<form class="cf-settings-panel__repeater-body" method="post" id ="cf_settings">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<div class="cf-settings-row">
		<div class="cf-settings-th">
			<div class="cf-settings-th__wrap">
				<div class="<?php echo esc_html( $disabled_class ); ?>" style="float:left;">
					<label for="cf_multicollab_specific_post_types>">
						<span class="cf-multicollab-optionlabel">
							<h4><?php esc_html_e( 'Enable', 'content-collaboration-inline-commenting'); ?> <b><?php esc_html_e( 'Multicollab for Specific Post Types', 'content-collaboration-inline-commenting' ); ?></b></h4>
						</span>
					</label>
				</div>	
				<a href="#" class="cf_premium_star"><?php esc_html_e( 'Upgrade to Premium'); ?><svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"></path><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"></path></g></svg></a>
			</div>
			<p class="cf-setting-desc <?php echo esc_html( $disabled_class ); ?>"><?php esc_html_e( 'Multicollab is enabled for all post types by default. You can customize this setting by selecting which post types you want to enable for using the dropdown.', 'content-collaboration-inline-commenting'); ?></p> 
		</div>
		<div class="cf-settings-td <?php echo esc_html( $disabled_class ); ?>">
			<label class="cf-settings-td-toggle">
				<input type="checkbox" name="cf_multicollab_specific_post_types" class="cf-checkbox cf_multicollab_mode_options" id="cf_multicollab_specific_post_types" value="cf_multicollab_specific_post_types">
				<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
			</label>
		</div>
	</div>
	<div class="cf-settings-row">
		<div class="cf-settings-th" >
			<label for="cf_hide_editorial_column">
				<span class="cf-multicollab-optionlabel">
					<?php printf( '%s <b>%s</b> %s', esc_html__( 'Show', 'content-collaboration-inline-commenting' ), esc_html__( 'Comments Column', 'content-collaboration-inline-commenting' ), esc_html__( 'in Post List', 'content-collaboration-inline-commenting' ) ); ?>
					<span class="md-plugin-tooltip">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
							<div class="cf-suggestion-tooltip-box">
								<a href="#." class="cf-tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
								<div style="display:flex;align-items:flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/multicollab.png' ); ?>"> </div> 
							</div>
					</span>
				</span>
			</label>
			<p class="cf-setting-desc"><?php esc_html_e( 'Display the Multicollab comments column in the post/page list to quickly view comment activity at a glance.', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
		<div class="cf-settings-td">
			<label class="cf-settings-td-toggle">
				<input type="checkbox" name="cf_hide_editorial_column" class="cf-checkbox" id="cf_hide_editorial_column" <?php checked( $cf_hide_editorial_column, '0' ); ?> value="0" class="regular-text"/>
				<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
			</label>
		</div>
	</div>
	<div class="cf-settings-row">
		<div class="cf-settings-th">
			<label for="cf_hide_floating_icons">
				<span class="cf-suggestion-option label">
				<?php printf( '%s <b>%s</b> %s', esc_html__( 'Show', 'content-collaboration-inline-commenting' ), esc_html__( 'Floating Comment', 'content-collaboration-inline-commenting' ), esc_html__( '& Suggest Buttons', 'content-collaboration-inline-commenting' ) ); ?>
					<span class="md-plugin-tooltip">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
							<div class="cf-suggestion-tooltip-box">
								<a href="#." class="cf-tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
								<div style="display:flex;align-items:flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/floating-option.jpeg' ); ?>"> </div> 
							</div>
					</span>
				</span> 
			</label> 
			<p class="cf-setting-desc"><?php esc_html_e( 'Displays floating buttons in the editor for adding comments and suggestions directly on the content. These buttons appear when you select text in the editor.', 'content-collaboration-inline-commenting' ); ?></p class="cf-settings-decs"> 
		</div>
		<div class="cf-settings-td">
			<label class="cf-settings-td-toggle">
				<input type="checkbox" name="cf_hide_floating_icons" class="cf-checkbox" id="cf_hide_floating_icons" <?php checked( $cf_hide_floating_icons, '0' ); ?> value="0" class="regular-text"/>
				<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
			</label>
		</div>
	</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?></div>
</form>
