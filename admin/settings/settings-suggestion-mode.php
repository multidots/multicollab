<form class="cf-settings-panel__repeater-body" method="post" id ="cf_suggestion_mode">
	<div id="cf-notice">
		<div class="cf-success notices notice-success" style="display: none">
			<p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
		<div class="cf-error notices notice-error" style="display: none">
			<p><?php esc_html_e( 'Please select any option to save!', 'content-collaboration-inline-commenting' ); ?></p>
		</div>
	</div>
	<?php
	$disabled       = '';
	$disabled_class = '';
		$disabled       = 'disabled';
		$disabled_class = 'cf_disabled_input';

	?>
	<div class="cf-suggestion-option ">
		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_suggestion_all_posts_pages">											
					<h4><?php printf( '%s <b>%s</b> %s', esc_html__( 'Enable Suggestions for', 'content-collaboration-inline-commenting' ), esc_html__( 'all pages/posts', 'content-collaboration-inline-commenting' ), esc_html__( '(Not Recommended).', 'content-collaboration-inline-commenting' ) ); ?></h4>
				</label> 
				<p class="cf-setting-desc"><?php esc_html_e( "By default, Suggestions Mode is OFF on for each post in Multicollab. Selecting this option will automatically turn ON Suggestions on all future posts on your website. You can still manually turn OFF suggestions mode for individual posts. We don't recommend turning Suggestions Mode ON for all the new posts unless you have specific business needs.", 'content-collaboration-inline-commenting' ); ?></p>
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_suggestion_mode_option_name" class="cf-checkbox cf_suggestion_mode_options" id="cf_suggestion_all_posts_pages" value="cf_suggestion_all_posts_pages" class="regular-text" <?php echo 'cf_suggestion_all_posts_pages' === $cf_suggestion_mode_option_name ? 'checked' : ''; ?> <?php echo esc_html( $disabled ); ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>	
		</div>

		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_suggestion_specific_post_categoriesf">
					<h4><?php printf( '%s <b>%s</b>', esc_html__( 'Enable Suggestions for', 'content-collaboration-inline-commenting' ), esc_html__( 'selected categories.', 'content-collaboration-inline-commenting' ) ); ?> </h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'By default, Suggestions Mode is OFF on for each post in Multicollab. This option will automatically turn ON Suggestions for selected post categories for future posts. You can still manually turn OFF suggestions mode for individual posts.', 'content-collaboration-inline-commenting' ); ?></p>
			</div>
			<div class="cf-settings-td">
				<label class="cf-settings-td-toggle">
					<input type="checkbox" name="cf_suggestion_mode_option_name" class="cf-checkbox cf_suggestion_mode_options" id="cf_suggestion_specific_post_categories" value="cf_suggestion_specific_post_categories" class="regular-text" <?php echo 'cf_suggestion_specific_post_categories' === $cf_suggestion_mode_option_name ? 'checked' : ''; ?> <?php echo esc_html( $disabled ); ?>/>
					<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
				</label>
			</div>
		</div>
		<div class="cf-settings-row">
			<div class="cf-settings-th">
				<label for="cf_suggestion_specific_post_typesj">
					<h4><?php printf( '%s <b>%s</b>', esc_html__( 'Enable Suggestions for', 'content-collaboration-inline-commenting' ), esc_html__( 'custom post types.', 'content-collaboration-inline-commenting' ) ); ?></h4>
				</label>
				<p class="cf-setting-desc"><?php esc_html_e( 'By default, Suggestions Mode is OFF on for each post in Multicollab. This option will automatically turn ON Suggestions for selected Custom Post Types for future posts. You can still manually turn OFF suggestions mode for individual posts.', 'content-collaboration-inline-commenting' ); ?></p>
				</div>
				<div class="cf-settings-td">
					<label class="cf-settings-td-toggle">
						<input type="checkbox" name="cf_suggestion_mode_option_name" class="cf-checkbox cf_suggestion_mode_options" id="cf_suggestion_specific_post_types" value="cf_suggestion_specific_post_types" class="regular-text" <?php echo 'cf_suggestion_specific_post_types' === $cf_suggestion_mode_option_name ? 'checked' : ''; ?> <?php echo esc_html( $disabled ); ?>/>
						<span class="cf-settings-td-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span>
					</label>
				</div>
		</div>
		<div class="cf-submit-button-settings"><?php submit_button( __( 'Save', 'content-collaboration-inline-commenting' ) ); ?></div>
	</div>

</form>
