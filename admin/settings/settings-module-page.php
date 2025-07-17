<?php
	
$disabled       = 'disabled';
$disabled_class = 'cf_disabled_input';
	
?>
<div class="cf-module-premium">
	<a href="#" class="cf_premium_star"><?php echo esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ); ?>
		<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"></path><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"></path></g></svg>
	</a>
</div>
<div class="cf-card-container <?php echo esc_html( $disabled_class ); ?>">
	<div class="cf-card-modules">
		<div class="cf-card-modules-content">
			<div class="cf-card-modules-content__wrap">
				<div class="cf-card-icon" style="background-color: rgb(75 27 206 / 7%);">
					<svg xmlns="http://www.w3.org/2000/svg" width="30" height="28.7" viewBox="0 0 30 28.7"><g id="Group_1" data-name="Group 1" transform="translate(0 0)"><path id="Path_10444" data-name="Path 10444" d="M30,3.6V20.9a3.585,3.585,0,0,1-3.6,3.6H8.5L3.1,27.2,0,28.7V3.6A3.585,3.585,0,0,1,3.6,0H26.4A3.585,3.585,0,0,1,30,3.6ZM3.6,2.2A1.367,1.367,0,0,0,2.2,3.6V25.2L8,22.3H26.4a1.367,1.367,0,0,0,1.4-1.4V3.6a1.367,1.367,0,0,0-1.4-1.4Z" fill="#007cba" fill-rule="evenodd"/><path id="check-svgrepo-com" d="M4,10.5l3.2,3.2L14.4,6.5" transform="translate(5.5 1.793)" fill="none" stroke="#007cba" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></g></svg>
				</div>
				<h3 class="cf-card-title"><?php esc_html_e( 'Editorial Checklist', 'content-collaboration-inline-commenting' ) ?></h3>
			</div>
			<div class="cf-card-desc"><?php esc_html_e( "Ensure everything is in place before hitting “Publish” with the help of checklists. This feature helps you:", 'content-collaboration-inline-commenting' ) ?></div>
			<ul class="cf-card-desc">
				<li><?php esc_html_e( "Guide your team through all critical publishing steps", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Support editorial workflows with structured processes", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Maintain consistent publishing standards across all post types, including custom post types.", 'content-collaboration-inline-commenting' ) ?></li>
			</ul>		
		</div>
		<div class="cf-card-footer">
			<div class="cf-card-footer__wrap">
				<a  href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=settings#cf-checklist-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ) ?></a>
				<a href="https://docs.multicollab.com/article/104-editorial-checklist?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" class="cf-theme-link" target="_blank"><?php esc_html_e( "Learn More", 'content-collaboration-inline-commenting' ) ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a>
			</div>
			<label class="cf-switch">
				<input type="checkbox" class="cf-toggle" <?php if( '1' !== $cf_disable_checklist ) { echo 'checked'; } ?>>
				<span class="cf-slider"></span>
			</label>
		</div>
	</div>

	<div class="cf-card-modules">
		<div class="cf-card-modules-content">
			<div class="cf-card-modules-content__wrap">
				<div class="cf-card-icon" style="background-color: rgb(75 27 206 / 7%);">
					<svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 31" width="31" height="31"><g id="Group 1272"><g id="Group 1269"><path id="Path 10442" class="s0" fill="#1fa15d" d="m4.3 30.6c-2.4 0-4.3-1.9-4.3-4.3v-19.5c0-2.4 1.9-4.3 4.3-4.3h10.7v2.3h-10.7c-1.1 0-2 0.9-2 2v19.5c0 1.1 0.9 2 2 2h19.6c1 0 1.9-0.9 1.9-2v-10.6h2.3v10.6c0 2.4-1.9 4.3-4.2 4.3z"/><g id="Group 1268"><path id="Path 10443" fill-rule="evenodd" class="s0" fill="#1fa15d" d="m6.9 23.7v-6l17.1-17 6 6-17.1 17zm2.3-5v2.8h2.8l11-11-2.8-2.8zm12.6-12.7l2.8 2.9 2.2-2.3-2.8-2.8z"/></g></g><path id="Path 10445" class="s0" fill="#1fa15d" d="m17 21.7h5v2h-7z"/></g></svg>
				</div>
				<h3 class="cf-card-title"><?php esc_html_e( 'Suggestion Mode', 'content-collaboration-inline-commenting' ) ?></h3>
			</div>
			<div class="cf-card-desc"><?php esc_html_e( "Suggest Edits Without Changing the Original Content", 'content-collaboration-inline-commenting' ) ?></div>
			<ul class="cf-card-desc">
				<li><?php esc_html_e( "Team members can propose edits without altering the original text", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Suggestions can be reviewed, accepted, or declined—just like in Google Docs", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Makes feedback easy to give and manage, including custom post types.", 'content-collaboration-inline-commenting' ) ?></li>
			</ul>
		</div>
		<div class="cf-card-footer">
			<div class="cf-card-footer__wrap">
				<a  href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=settings#cf-suggestion-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ) ?></a>
				<a href="https://docs.multicollab.com/" class="cf-theme-link" target="_blank"><?php esc_html_e( "Learn More", 'content-collaboration-inline-commenting' ) ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a>
			</div>
			<label class="cf-switch">
				<input type="checkbox" class="cf-toggle" <?php if( '1' !== $cf_disable_suggestion ) { echo 'checked'; } ?>>
				<span class="cf-slider"></span>
			</label>
		</div>
	</div>

	<div class="cf-card-modules">
		<div class="cf-card-modules-content">
			<div class="cf-card-modules-content__wrap">
				<div class="cf-card-icon" style="background-color: rgb(75 27 206 / 7%);">
					<svg xmlns="http://www.w3.org/2000/svg" width="31.782" height="33.95" viewBox="0 0 31.782 33.95">
					<g id="Group_1" data-name="Group 1" transform="translate(-5 -1)">
						<g id="Group_1272" data-name="Group 1272" transform="translate(5 4.35)">
						<g id="Group_1269" data-name="Group 1269">
							<path id="Path_10442" data-name="Path 10442" d="M4.3,30.6A4.268,4.268,0,0,1,0,26.3V6.8A4.268,4.268,0,0,1,4.3,2.5H15V4.8H4.3a2.006,2.006,0,0,0-2,2V26.3a2.006,2.006,0,0,0,2,2H23.9a1.988,1.988,0,0,0,1.9-2V15.7h2.3V26.3a4.247,4.247,0,0,1-4.2,4.3Z" fill="#ff6f6f"></path>
						</g>
						<g id="Real-time-Co-editing-green" transform="translate(10.7 -7.028)">
							<path id="Path_1" data-name="Path 1" d="M4.3,15.915V19.2h8.136a5.069,5.069,0,1,0,.992-7.952,5.749,5.749,0,0,0-1.212-.68,3.783,3.783,0,1,0-4.316,0,5.767,5.767,0,0,0-3.6,5.345Zm15.685-.294a3.967,3.967,0,1,1-3.967-3.967,3.967,3.967,0,0,1,3.967,3.967ZM7.385,7.467a2.681,2.681,0,1,1,2.681,2.681A2.681,2.681,0,0,1,7.385,7.467Zm2.681,3.783a4.665,4.665,0,0,1,2.443.716,5.051,5.051,0,0,0-.9,6.134H5.4V15.915a4.665,4.665,0,0,1,4.665-4.665Z" fill="#ff6f6f"></path>
							<path id="Path_2" data-name="Path 2" d="M66.794,56.5H64.9V54H63.8v3.6h3.012Z" transform="translate(-48.572 -41.078)" fill="#ff6f6f"></path>
						</g>
						</g>
						<g id="Group_1272-2" data-name="Group 1272" transform="translate(62 12.3)">
						<g id="Group_1269-2" data-name="Group 1269">
							<g id="Group_1268" data-name="Group 1268">
							<path id="Path_10443" data-name="Path 10443" d="M6.9,12.533V9.446L15.857.7,19,3.787l-8.957,8.746ZM8.1,9.961V11.4H9.571l5.762-5.659L13.867,4.3Zm6.6-6.534,1.467,1.492,1.152-1.183L15.857,2.295Z" transform="translate(-58 4.372)" fill="#ff6f6f" fill-rule="evenodd"></path>
							</g>
						</g>
						<path id="Path_10445" data-name="Path 10445" d="M17,21.7h5v2H15Z" transform="translate(-61 -7)" fill="#ff6f6f"></path>
						</g>
					</g>
					</svg>
				</div>
				<h3 class="cf-card-title"><?php esc_html_e( 'Real-time Editing (Beta)', 'content-collaboration-inline-commenting' ) ?></h3>
			</div>
			<div class="cf-card-desc"><?php esc_html_e( "Collaborate in Real-Time", 'content-collaboration-inline-commenting' ) ?></div>
			<ul class="cf-card-desc">
				<li><?php esc_html_e( "Multiple team members can write and edit the same post or page simultaneously", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Real-time updates ensure everyone sees changes instantly", 'content-collaboration-inline-commenting' ) ?></li>
				<li><?php esc_html_e( "Prevents version conflicts and duplicate work, including custom post types.", 'content-collaboration-inline-commenting' ) ?></li>
			</ul>		
		</div>
		<div class="cf-card-footer">
			<div class="cf-card-footer__wrap">
				<a  href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=settings#real-time-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ) ?></a>
				<a href="https://docs.multicollab.com/" class="cf-theme-link" target="_blank"><?php esc_html_e( "Learn More", 'content-collaboration-inline-commenting' ) ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a>
			</div>	
			<label class="cf-switch">
				<input type="checkbox" class="cf-toggle" <?php if( '1' !== $cf_disable_real_time_editing ) { echo 'checked'; } ?>>
				<span class="cf-slider"></span>
			</label>
		</div>
	</div>

</div>