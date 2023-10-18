<form class="cf-cnt-box-body" method="post" id ="cf_settings">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<div class="cf_suggestion_option cf_publishing_option cf_general_option">
		<div class="cf-notification-settings">
			<div class="cf-check-wrap">
				<input type="checkbox" name="cf_hide_editorial_column" class="cf-checkbox" id="cf_hide_editorial_column" <?php echo '1' === $cf_hide_editorial_column ? 'checked' : ''; ?> value="1" class="regular-text"/>
				<span class="cf-check"></span>
			</div>
			<label for="cf_hide_editorial_column">
				<span class="cf_suggestion_optionlabel">
				<?php printf( '%s <b>%s</b> %s', esc_html__( 'Hide', 'content-collaboration-inline-commenting' ), esc_html__( 'Comments Column', 'content-collaboration-inline-commenting' ), esc_html__( 'on the Post Lists.', 'content-collaboration-inline-commenting' ) ); ?>
					<span class="md-plugin-tooltip">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
							<div class="cf_suggestion-tooltip-box">
								<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
								<div style="display:flex;align-items:flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/multicollab.png' ); ?>"> <span><?php esc_html_e( 'This column will disappear from the Post List if this is checked.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
							</div>
					</span>
				</span> 
			</label> 
		</div>
		<div class="cf-notification-settings">
			<div class="cf-check-wrap">
				<input type="checkbox" name="cf_show_infoboard" class="cf-checkbox" id="cf_show_infoboard" <?php echo '1' === $cf_show_infoboard ? 'checked' : ''; ?> value="1" class="regular-text"/>
				<span class="cf-check"></span>
			</div>
			<label for="cf_show_infoboard">
				<span class="cf_suggestion_optionlabel">
				<?php printf( '%s <b>%s</b> %s', esc_html__( 'Hide', 'content-collaboration-inline-commenting' ), esc_html__( 'Save Draft Comment', 'content-collaboration-inline-commenting' ), esc_html__( 'reminder.', 'content-collaboration-inline-commenting' ) ); ?>
					<span class="md-plugin-tooltip">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
							<div class="cf_suggestion-tooltip-box">
								<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
								<div style="display:flex;align-items:flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/save-draft.png' ); ?>"> <span><?php esc_html_e( 'This reminder will disappear from the Block Editor if this is checked.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
							</div>
					</span>
				</span>  
			</label>
		</div>
		<?php // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1. ?>
		
		<div class="cf-notification-settings">
			<div class="cf-check-wrap">
				<input type="checkbox" name="cf_hide_floating_icons" class="cf-checkbox" id="cf_hide_floating_icons" <?php echo '1' === $cf_hide_floating_icons ? 'checked' : ''; ?> value="1" class="regular-text"/>
				<span class="cf-check"></span>
			</div>
			<label for="cf_hide_floating_icons">
				<span class="cf_suggestion_optionlabel">
				<?php printf( '%s <b>%s</b> %s', esc_html__( 'Hide', 'content-collaboration-inline-commenting' ), esc_html__( 'Floating Comment', 'content-collaboration-inline-commenting' ), esc_html__( 'toolbar.', 'content-collaboration-inline-commenting' ) ); ?>
					<span class="md-plugin-tooltip">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>
							<div class="cf_suggestion-tooltip-box">
								<a href="#." class="cf_tooltip-close"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path id="Icon_material-close" data-name="Icon material-close" d="M27.5,9.514,25.486,7.5,17.5,15.486,9.514,7.5,7.5,9.514,15.486,17.5,7.5,25.486,9.514,27.5,17.5,19.514,25.486,27.5,27.5,25.486,19.514,17.5Z" transform="translate(-7.5 -7.5)" fill="#000"></path></svg></a>
								<div style="display:flex;align-items:flex-start;"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/floating-option.jpeg' ); ?>"> <span><?php esc_html_e( 'This floating toolbar to add quick comments and suggestions will disappear from the Block Editor if this is checked.', 'content-collaboration-inline-commenting' ); ?></span> </div> 
							</div>
					</span>
				</span> 
			</label> 
		</div>
	</div>	
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?></div>
</form>
