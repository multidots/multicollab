<?php
/**
 * Gutenberg Commenting Feature Settings Page.
 */

// Get settings.
$view                        = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_SPECIAL_CHARS );
$tab_number                  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
$activated                   = filter_input( INPUT_GET, 'activated', FILTER_SANITIZE_SPECIAL_CHARS );
$cf_admin_notif              = get_option( 'cf_admin_notif' );
$cf_show_infoboard           = get_option( 'cf_show_infoboard' );
$cf_show_multicollab_sidebar = get_option( 'cf_show_multicollab_sidebar' );
$cf_hide_editorial_column    = get_option( 'cf_hide_editorial_column' );

$cf_give_alert_message                          = get_option( 'cf_give_alert_message' );
$cf_suggestion_mode_option_name                 = get_option( 'cf_suggestion_mode_option_name' );
$cf_specific_post_types_values                  = get_option( 'cf_specific_post_types_values' );
$cf_specific_post_categories_values             = get_option( 'cf_specific_post_categories_values' );
$cf_hide_floating_icons                         = get_option( 'cf_hide_floating_icons' );
$cf_slack_notification_accept_reject_suggestion = get_option( 'cf_slack_notification_accept_reject_suggestion' );
$cf_page_url                                    = menu_page_url( 'editorial-comments', false );
$cf_web_activity_url                            = add_query_arg( 'view', 'web-activity', $cf_page_url );
$cf_post_activity_url                           = add_query_arg( 'view', 'post-activity', $cf_page_url );
$view                   = ( null === $view ) ? 'web-activity' : $view;
$cf_permissions         = get_option( 'cf_permissions' );
$cf_multiedit_websocket = get_option( 'cf_multiedit_websocket' );
$cf_websocket_options   = get_option( 'cf_websocket_options' );
?>
<div class="cf-plugin-settings">
<div class="cf_settings_loader"></div>
			<?php
			// Display Promotional Banner.
			$promotional_banner = cf_dpb_promotional_banner( 'setting' );
			if ( ! empty( $promotional_banner ) ) {
				echo $promotional_banner; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
			<div class="cf-plugin-header">
			<div class="cf-plugin-logo">
				<a href="https://www.multicollab.com/" target="_blank"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multicollab_logo.svg' ); ?>"/>
				<span class="cf-plan-name">
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513">
					<g id="Group_52550" data-name="Group 52550" transform="translate(-285.455 -280.192)">
						<path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/>
						<path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/>
					</g>
					</svg>
					<?php
						echo esc_html( 'FREE' );
					?>
				</span>
				<a class="cf-plan-version" href="<?php echo esc_url( 'https://www.multicollab.com/change-log' ); ?>" target="_blank">v <?php echo esc_html( COMMENTING_BLOCK_VERSION ); ?></a>
			</div>
			<div class="cf-plugin-version">
				<a href="<?php echo esc_url( 'https://www.multicollab.com/contact/?utm_source=plugin_setting_header_link_contact&utm_medium=header_link_contact&utm_campaign=plugin_setting_header_link_contact&utm_id=plugin_setting_header_link' ); ?>" target="_blank"><?php esc_html_e( 'Contact', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a> | 
				<a href="<?php echo esc_url( CF_STORE_URL ) . 'my-account/'; ?>" target="_blank"><?php esc_html_e( 'My Account', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a>
				<a class="pricing-block-button__link" href="<?php echo esc_url( 'https://www.multicollab.com/upgrade-to-premium/' ); ?>" target="_blank"><?php esc_html_e( 'Upgrade', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow-white.svg' ); ?>" alt="external-link"></a>
			</div>
		</div>
	
		<div class="cf-outer">
			<div class="cf-left cf-pricing-dashboard">
				<div class="cf-tabs-main">
				<ul class="cf-tabs">
					<li class="
						<?php
						if ( ( 'dashboard' === $tab_number || empty( $tab_number ) || 'web-activity' === $view ) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number && 'license' !== $view ) {
							echo esc_html( 'cf-tab-active' ); }
						?>
							"><a href="<?php echo esc_url( home_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=web-activity" class="cf-tab-item" data-id="cf-dashboard"><?php esc_html_e( 'Dashboard', 'content-collaboration-inline-commenting' ); ?></a></li>
						<li class="
									<?php
									if ( 'post-activity' === $view ) {
										echo esc_html( 'cf-tab-active' ); }
									?>
						"><a href="<?php echo esc_url( home_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=post-activity" class="cf-tab-item" data-id="cf-reports"><?php esc_html_e( 'Reports', 'content-collaboration-inline-commenting' ); ?></a></li>
		
						<?php if ( current_user_can( 'administrator' ) ) : ?>
						<li class="
							<?php
							if ( 'settings' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						"><a href="<?php echo esc_url( home_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=settings" class="cf-tab-item" data-id="cf-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ); ?></a></li>
						<li class="
							<?php
							if ( ! empty( $tab_number ) || 'intigrations' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						"><a href="<?php echo esc_url( home_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=intigrations" class="cf-tab-item" data-id="cf-roles-slack-integration"><?php esc_html_e( 'Integrations', 'content-collaboration-inline-commenting' ); ?></a></li>
						<?php endif; ?>

					</ul>
					<div class="cf-tabs-content">
						<div id="cf-dashboard" class="cf-tab-inner 
							<?php
							if ( ( 'dashboard' === $tab_number || empty( $tab_number ) || 'web-activity' === $view ) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number && 'license' !== $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						">
							<?php
								$this->cf_get_activities();
								require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-dashboard.php'; // Removed phpcs:ignore by Rishi Shah.

							?>
						</div>
						<?php if ( current_user_can( 'administrator' ) ) : ?>
						<div id="cf-reports" class="cf-tab-inner
							<?php
							if ( 'post-activity' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						">
							<?php

							$this->cf_get_activities();
							require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-report.php'; // Removed phpcs:ignore by Rishi Shah.
							?>
						</div>
						<?php endif; ?>
						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div id="cf-settings" class="cf-tab-inner
							<?php
							if ( 'settings' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
							">
							<div class="cf-content-box">
								<div class="cf-cnt-box-header">
									<h3><?php esc_html_e( 'General', 'content-collaboration-inline-commenting' ); ?></h3>
								</div>
								<?php
									// Get general settings form HTML.
									require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-general.php';
								?>
								<div class="cf-cnt-box-header cf-cnt-pro-migration-header">
									<h3><?php esc_html_e( 'Migration Setting', 'content-collaboration-inline-commenting' ); ?></h3>
								</div>
								<div class="cf-cnt-box-body cf-cnt-pro-migration">
									<div id="migration-progress-bar" style="display: none"><span>% completed</span></div>
									<div id="migration-progress-info"></div>
									<p class="submit"><a href="javascrpit:void(0)" id="pro-migration-button" class="button button-primary">Migrate</a></p>
								</div>
							</div>
							<?php // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1 ?>
							<div class="cf-content-box">
								<div class="cf-cnt-box-header">
									<h3><?php printf( '%s - <a href="https://docs.multicollab.com/settings/email-notifications" target="_blank"> %s  <img class="cf-external-link-icon" src="' . esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ) . '" alt="external-link"></a>', esc_html__( 'Email Notification', 'content-collaboration-inline-commenting' ), esc_html__( 'Guide to Setup Email Notifications', 'content-collaboration-inline-commenting' ) ); ?></h3>
								</div>
								<?php
									// Get general settings form HTML.
									require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-email-notification.php';
								?>
							</div>
							<?php
							$disabled_class = 'cf_disabled_input';
							?>
							<div class="cf-suggestion-box <?php echo esc_html( $disabled_class ); ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3>
											<?php printf( '%s', esc_html__( 'Publishing', 'content-collaboration-inline-commenting' ) ); ?>
											
											<a href="https://www.multicollab.com/upgrade-to-premium/" target="_blank" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . ' <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
											
										</h3>
									</div>
									<?php
										// Get permission form HTML.
										require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-publishing.php';
									?>
								</div>
							</div>
							<?php // Suggestion Mode/@author Rishi Shah/@since EDD - 3.0.1 ?>
							<?php $disabled_class = 'cf_disabled_input'; ?>
							<div class="cf-suggestion-box <?php echo esc_html( $disabled_class ); ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3>
											<?php printf( '%s', esc_html__( 'Suggestion Mode', 'content-collaboration-inline-commenting' ) ); ?>
										
											<a href="https://www.multicollab.com/upgrade-to-premium/" target="_blank" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
											
									</h3>
									</div>
									<?php
										// Get permission form HTML.
										require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-suggestion-mode.php';
									?>
								</div>
							</div>

							<div class="cf-suggestion-box" id="real-time-settings">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3>
											<?php printf( '%s', esc_html__( 'Real-time Editing ', 'content-collaboration-inline-commenting' ) ); ?>
											(<i><?php printf( esc_html__( 'Beta', 'content-collaboration-inline-commenting' ) ); ?></i>)
										</h3>
									</div>
									<?php
										// Get permission form HTML.
										require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-realtime-mode.php';
									?>
								</div>
							</div>

							<?php // Manage Permissions/@author Rishi Shah/@since EDD - 3.0.1. ?>
							<?php $disabled_class = 'cf_disabled_input'; ?>
							<div class="cf-suggestion-box <?php echo esc_html( $disabled_class ); ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3>
											<?php printf( '%s', esc_html__( 'Manage Permissions', 'content-collaboration-inline-commenting' ) ); ?>
											
												<a href="https://www.multicollab.com/upgrade-to-premium/" target="_blank" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
											
										</h3>
									</div>
									<?php
										// Get permission form HTML.
										require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-permissions.php';
									?>
								</div>
							</div>
							<?php $disabled_class = 'cf_disabled_input'; ?>
							<div class="cf-content-language-box <?php echo esc_html( $disabled_class ); ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3>
											<?php printf( '%s', esc_html__( 'Multilingual Options', 'content-collaboration-inline-commenting' ) ); ?>
											
											<a href="https://www.multicollab.com/upgrade-to-premium/" target="_blank" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
												
										</h3>
									</div>
									<?php
										// Get permission form HTML.
										require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-multilingual-options.php';
									?>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div id="cf-roles-slack-integration" class="cf-tab-inner 
							<?php
							if ( ! empty( $tab_number ) || 'intigrations' === $view ) {
								echo esc_html( 'cf-tab-active' );
							}
							?>
							">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php esc_html_e( 'Slack', 'content-collaboration-inline-commenting' ); ?>
										
										<a href="https://www.multicollab.com/upgrade-to-premium/" target="_blank" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
											
										</h3>
										<img style="width:100%;" alt="Slack-integrations" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/slackIntegration.png' ); ?>"/>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="cf-right">
			</div>
		</div>
  
</div>
