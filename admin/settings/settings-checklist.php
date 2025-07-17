<form class="cf-settings-panel__repeater-body" method="post" id="cf_checklist_settings">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>

	<div class="cf-checklist-option  cf-checklist-option cf-suggestion-option">

	<?php
			$disabled       = '';
			$disabled_class = '';
			$disabled       = 'disabled';
			$disabled_class = 'cf_disabled_input';
			$post_types_list = new Commenting_block_Functions();
			$post_types      = $post_types_list->cf_get_posts_types();
			$post_types = (array) $post_types;
			$excluded = [ 'elementor_library', 'e-landing-page', 'e-floating-buttons' ]; // Add more if needed
		
			$post_types = array_filter( $post_types, function ( $key ) use ( $excluded ) {
				return ! in_array( $key, $excluded, true );
			}, ARRAY_FILTER_USE_KEY );
            
			$cf_specific_post_types_checklist_values = delete_option( 'cf_specific_post_types_checklist_values' );
			$cf_disable_checklist_publish_button = delete_option( 'cf_disable_checklist_publish_button' );
			$cf_checklist_option_name = delete_option( 'cf_checklist_option_name' );

		if ( ! empty( $post_types ) ) {
			?>
			<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_checklist_specific_post_types">
					<h4><?php printf( '%s', esc_html__( 'Enable Editorial Checklist for Pages/Posts', 'content-collaboration-inline-commenting' ) ) ?></h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'Apply the checklist to selected content types (Posts, Pages, or custom post types) to help ensure all publishing standards are met before going live.', 'content-collaboration-inline-commenting' ); ?></p> 
				<div class="cf_checklist_specific_post_type_section"
				<?php
				if ( 'cf_checklist_specific_post_types' !== $cf_checklist_option_name ) {
					echo 'style="display:none;"'; }
				?>
				>
					<div class="cf_post_types_lists">
						<select class="cf-checklist-specific-post-type-multiple" name="cf_specific_post_types_values[]" multiple="multiple">
							<option value=""><?php esc_html_e( 'Please select a post type', 'content-collaboration-inline-commenting' ); ?></option>
							<?php
							if ((is_array($post_types) && !empty($post_types)) || (is_object($post_types) && !empty((array)$post_types))) {
								foreach ( $post_types as $key => $post_types_values ) {
									if ( 'attachment' !== $key ) {
										?>
									<option value="<?php echo esc_attr( $key ); ?>"
																		<?php
																		if ( ! empty( $cf_specific_post_types_checklist_values ) && in_array( $key, $cf_specific_post_types_checklist_values, true ) ) {
																			echo esc_html( 'selected' ); }
																		?>
											><?php echo esc_html( $post_types_values->labels->name ); ?></option>
											<?php
									}
								}
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_checklist_option_name" class="cf-checkbox cf_checklist_post_type_options" id="cf_checklist_specific_post_types" value="cf_checklist_specific_post_types" class="regular-text" <?php echo 'cf_checklist_specific_post_types' === $cf_checklist_option_name ? 'checked' : ''; ?> <?php echo esc_html( $disabled ); ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>
		</div>
		<?php } ?>
		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_disable_checklist_publish_button">
					<h4><?php printf( '%s', esc_html__( 'Prevent Publishing with Incomplete Checklist', 'content-collaboration-inline-commenting' ) ); ?></h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'Blocks publishing until all required items are completed. A warning will appear if any are missing.', 'content-collaboration-inline-commenting' ); ?></p> 
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_disable_checklist_publish_button" class="cf-checkbox cf_disable_checklist_publish_button" id="cf_disable_checklist_publish_button" value="1" class="regular-text" <?php echo '1' == $cf_disable_checklist_publish_button ? 'checked' : ''; ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>	
		</div>

	</div>
	<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?>
	</div>
</form>