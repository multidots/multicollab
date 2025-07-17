<?php
/**
 * Gutenberg Commenting Feature Settings Page.
 */

// Get settings.
$view                        = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_SPECIAL_CHARS );
$tab_number                  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
$activated                   = filter_input( INPUT_GET, 'activated', FILTER_SANITIZE_SPECIAL_CHARS );
$cf_admin_notif              = get_option( 'cf_admin_notif' );

$cf_show_multicollab_sidebar = get_option( 'cf_show_multicollab_sidebar' );
$cf_hide_editorial_column 						= get_option( 'cf_hide_editorial_column' ) !== false
    												? get_option( 'cf_hide_editorial_column' )
    												: ( update_option( 'cf_hide_editorial_column', '0' ) ? '0' : '0' );
$cf_give_alert_message                          = get_option( 'cf_give_alert_message' );
$cf_suggestion_mode_option_name                 = get_option( 'cf_suggestion_mode_option_name' );
$cf_specific_post_types_values                  = get_option( 'cf_specific_post_types_values' );
$cf_specific_post_categories_values             = get_option( 'cf_specific_post_categories_values' );
$cf_hide_floating_icons 						= get_option( 'cf_hide_floating_icons' ) !== false
    												? get_option( 'cf_hide_floating_icons' )
    												: ( update_option( 'cf_hide_floating_icons', '0' ) ? '0' : '0' );
$cf_slack_notification_accept_reject_suggestion = get_option( 'cf_slack_notification_accept_reject_suggestion' );
$cf_page_url                                    = menu_page_url( 'editorial-comments', false );
$cf_web_activity_url                            = add_query_arg( 'view', 'web-activity', $cf_page_url );
$cf_post_activity_url                           = add_query_arg( 'view', 'post-activity', $cf_page_url );
$view                                           = ( null === $view ) ? 'web-activity' : $view;
$cf_permissions                                 = get_option( 'cf_permissions' );
$cf_disable_checklist                           = get_option( 'cf_disable_checklist' );
$cf_disable_suggestion                          = get_option( 'cf_disable_suggestion' );
$cf_disable_real_time_editing                   = get_option( 'cf_disable_real_time_editing' );
$cf_checklist_option_name                       = get_option( 'cf_checklist_option_name' );
$cf_disable_checklist_publish_button            = get_option( 'cf_disable_checklist_publish_button' );
$cf_specific_post_types_checklist_values        = get_option( 'cf_specific_post_types_checklist_values' );
?>
<div class="cf-dashboard-layout">
<div class="cf-settings-loader"></div>
			<?php
			// Display Promotional Banner.
			$promotional_banner = cf_dpb_promotional_banner( 'setting' );
			if ( ! empty( $promotional_banner ) ) {
				echo $promotional_banner; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
			<div class="cf-dashboard-layout__header">
			<div class="cf-dashboard-layout__header-logo">
				<a href="https://www.multicollab.com/" target="_blank"><img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multicollab_logo.svg' ); ?>"/></a>
				<a href="<?php echo esc_url( 'https://www.multicollab.com/pricing/#h-compare-features-side-by-side' ); ?>" target="_blank" class="cf-dashboard-layout__header-plan-name">
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513">
					<g id="Group_52550" data-name="Group 52550" transform="translate(-285.455 -280.192)">
						<path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/>
						<path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/>
					</g>
					</svg>
					<?php
						echo esc_html( 'FREE' );
					?>
				</a>
				<a class="cf-dashboard-layout__header-plan-version" href="<?php echo esc_url( 'https://www.multicollab.com/change-log' ); ?>" target="_blank">v <?php echo esc_html( COMMENTING_BLOCK_VERSION ); ?></a>
			</div>
			<div class="cf-plugin-version">
				<a href="<?php echo esc_url( 'https://www.multicollab.com/contact/?utm_source=plugin_setting_header_link_contact&utm_medium=header_link_contact&utm_campaign=plugin_setting_header_link_contact&utm_id=plugin_setting_header_link' ); ?>" target="_blank"><?php esc_html_e( 'Contact', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a> | 
				<a href="<?php echo esc_url( CF_STORE_URL ) . 'my-account/'; ?>" target="_blank"><?php esc_html_e( 'My Account', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a>
				<a class="pricing-block-button__link" href="<?php echo esc_url( 'https://www.multicollab.com/upgrade-to-premium?utm_source=plugin+&utm_medium=+upgrade&utm_campaign=upgrade_from_setting_free' ); ?>" target="_blank"><?php esc_html_e( 'Upgrade', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow-white.svg' ); ?>" alt="external-link"></a>
			</div>
		</div>
	
		<div class="cf-dashboard-layout__outer">
			<div class="cf-dashboard-layout__inner">
				<div class="cf-dashboard-layout__tabs-wrap">
				<ul class="cf-dashboard-layout__tabs-list">
					<li class="
						<?php
						if ( ( 'dashboard' === $tab_number || empty( $tab_number ) || 'web-activity' === $view || 'modules' === $view ) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number && 'license' !== $view && 'modules' !== $view && 'need-help' !== $view) {
							echo esc_html( 'cf-tab-active' ); }
						?>
							"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=web-activity" class="cf-tab-item" data-id="cf-dashboard"><?php esc_html_e( 'Dashboard', 'content-collaboration-inline-commenting' ); ?></a></li>
						<li class="
									<?php
									if ( 'post-activity' === $view ) {
										echo esc_html( 'cf-tab-active' ); }
									?>
						"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=post-activity" class="cf-tab-item" data-id="cf-reports"><?php esc_html_e( 'Reports', 'content-collaboration-inline-commenting' ); ?></a></li>
						<?php if ( current_user_can( 'administrator' ) ) : ?>
						<li class="
							<?php
							if ( 'settings' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=settings" class="cf-tab-item" data-id="cf-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ); ?></a></li>
							
						<li class="
								<?php
								if ( ! empty( $tab_number ) || 'intigrations' === $view ) {
									echo esc_html( 'cf-tab-active' ); }
								?>
							"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=intigrations" class="cf-tab-item" data-id="cf-roles-slack-integration"><?php esc_html_e( 'Integrations', 'content-collaboration-inline-commenting' ); ?></a></li>
		
							<li class="
								<?php
								if ( ! empty( $tab_number ) || 'modules' === $view ) {
									echo esc_html( 'cf-tab-active' ); }
								?>
							"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=modules" class="cf-tab-item" data-id="cf-modules"><?php esc_html_e( 'Modules', 'content-collaboration-inline-commenting' ); ?></a><span class="cf-plugin-launch-badge"><?php esc_html_e( 'New', 'content-collaboration-inline-commenting' ); ?></span></li>
							<li class="cf-dashboard-need-help-tab 
								<?php
								if ( ! empty( $tab_number ) || 'need-help' === $view ) {
									echo esc_html( 'cf-tab-active' ); }
								?>
							"><a href="<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=editorial-comments&view=need-help" class="cf-tab-item" data-id="cf-need-help"><?php esc_html_e( 'Get Started', 'content-collaboration-inline-commenting' ); ?></a></li>

						<?php endif; ?>
					</ul>
					<div class="cf-dashboard-layout__tabs-content">
						<div id="cf-dashboard" class="cf-dashboard-layout__tabs-wrap-inner 
							<?php
							if ( ( 'dashboard' === $tab_number || empty( $tab_number ) || 'web-activity' === $view || 'modules' === $view ) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number && 'license' !== $view && 'modules' !== $view && 'need-help' !== $view) {
								echo esc_html( 'cf-tab-active' ); 
							}
							?>
						">
							<?php
								$this->cf_get_activities();
								require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-dashboard.php'; // Removed phpcs:ignore by Rishi Shah.

							?>
							<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
						</div>
						<?php if ( current_user_can( 'administrator' ) || current_user_can( 'manage_options' ) ) : ?>
						<div id="cf-reports" class="cf-dashboard-layout__tabs-wrap-inner
							<?php
							if ( 'post-activity' === $view ) {
								echo esc_html( 'cf-tab-active' ); }
							?>
						">
							<?php

							$this->cf_get_activities();
							require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-report.php'; // Removed phpcs:ignore by Rishi Shah.
							?>
							<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
						</div>
						<?php endif; ?>
						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div id="cf-settings" class="cf-dashboard-layout__tabs-wrap-inner
							<?php
							if ( 'settings' === $view ) {
								echo esc_html( 'cf-tab-active' ); 
							 
							}
							?>
							">
								<div class="cf-settings-wrapper">
									<div class="cf-settings-wrapper__sidebar">
										<ul>
											<li class="cf-tab-btn cf-tab-active" data-tab="cf-general">
												<svg width="18px" height="18px" viewBox="-2.73 -2.73 96.46 96.46" enable-background="new 0 0 91 91" id="Layer_1" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#5B6064" stroke="#5B6064" stroke-width="6"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M90.668,35.091c0.17-0.643,0.08-1.326-0.25-1.902L78.813,13.115c-0.695-1.195-2.225-1.607-3.422-0.914 l-9.734,5.619c-1.791-1.283-3.707-2.391-5.727-3.307V3.269c0-1.383-1.121-2.504-2.506-2.504H34.23 c-1.385,0-2.504,1.121-2.504,2.504v11.246c-1.891,0.861-3.699,1.893-5.406,3.086l-9.678-5.736 c-0.572-0.338-1.252-0.434-1.896-0.271c-0.643,0.166-1.197,0.578-1.535,1.15L1.387,32.69c-0.338,0.57-0.436,1.254-0.271,1.898 c0.166,0.643,0.578,1.195,1.15,1.533l9.682,5.734c-0.154,1.305-0.229,2.523-0.229,3.695c0,1.039,0.061,2.117,0.189,3.309 L2.16,54.491c-0.576,0.334-0.996,0.881-1.166,1.521c-0.174,0.641-0.082,1.326,0.25,1.9l11.6,20.076 c0.691,1.197,2.225,1.609,3.422,0.914l9.736-5.621c1.793,1.285,3.711,2.391,5.725,3.307v11.244c0,1.385,1.119,2.504,2.504,2.504 h23.193c1.385,0,2.506-1.119,2.506-2.504V76.589c1.889-0.859,3.697-1.895,5.402-3.088l9.684,5.736 c1.188,0.705,2.727,0.311,3.432-0.879L90.27,58.413c0.336-0.572,0.434-1.256,0.271-1.898c-0.166-0.643-0.578-1.195-1.152-1.533 l-9.676-5.734c0.156-1.314,0.23-2.518,0.23-3.695c0-1.051-0.063-2.143-0.189-3.313l9.746-5.627 C90.074,36.282,90.496,35.733,90.668,35.091z M59.242,45.55c0,7.396-6.018,13.414-13.416,13.414 c-7.396,0-13.412-6.018-13.412-13.414c0-7.393,6.016-13.41,13.412-13.41C53.225,32.14,59.242,38.157,59.242,45.55z" fill="#ffffff"></path> </g> </g></svg>
												<?php esc_html_e( 'General', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-email">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18.6 23.169" fill="#5B6064" stroke-width="0"><g transform="translate(-182.701 -870.7)"><g transform="translate(183.001 871)"><path d="M54.445,14.569a3.653,3.653,0,0,1-3.622-3.259H46.445a1,1,0,0,1-.977-.772.973.973,0,0,1,.505-1.077,3.233,3.233,0,0,0,.9-1.256,15.959,15.959,0,0,0,1.044-6.287,6.586,6.586,0,0,1,3.656-5.873V-5.22A2.781,2.781,0,0,1,54.345-8h.2a2.781,2.781,0,0,1,2.776,2.78v1.265a6.591,6.591,0,0,1,3.655,5.844c0,.006,0,.017,0,.029a15.958,15.958,0,0,0,1.044,6.287,3.234,3.234,0,0,0,.9,1.256.973.973,0,0,1,.505,1.077.994.994,0,0,1-.977.772H58.068a3.653,3.653,0,0,1-3.622,3.259ZM52.814,11.31a1.676,1.676,0,0,0,3.262,0Zm7.577-1.968-.048-.093a16.518,16.518,0,0,1-1.332-7.3s0-.016,0-.027a4.566,4.566,0,1,0-9.132,0,16.555,16.555,0,0,1-1.332,7.331l-.048.093ZM54.445-4.625a6.572,6.572,0,0,1,.911.064V-5.22a.812.812,0,0,0-.81-.812h-.2a.812.812,0,0,0-.81.812v.659A6.579,6.579,0,0,1,54.445-4.625Z" transform="translate(-45.445 8)"/><path d="M54.445,14.869a3.954,3.954,0,0,1-3.882-3.259H46.445a1.3,1.3,0,0,1-1.271-1.01,1.275,1.275,0,0,1,.639-1.394A3.118,3.118,0,0,0,46.6,8.077a15.678,15.678,0,0,0,1.016-6.159,6.9,6.9,0,0,1,3.656-6.057V-5.22A3.081,3.081,0,0,1,54.345-8.3h.2a3.081,3.081,0,0,1,3.076,3.08v1.081a6.9,6.9,0,0,1,3.655,6.022c0,.009,0,.022,0,.034a15.677,15.677,0,0,0,1.016,6.159,3.119,3.119,0,0,0,.784,1.129,1.275,1.275,0,0,1,.639,1.394,1.3,1.3,0,0,1-1.271,1.01H58.328a3.953,3.953,0,0,1-3.882,3.259ZM54.345-7.7a2.481,2.481,0,0,0-2.476,2.48v1.451l-.167.083a6.293,6.293,0,0,0-3.488,5.6A16.242,16.242,0,0,1,47.14,8.333a3.508,3.508,0,0,1-.993,1.372l-.019.014-.021.01a.674.674,0,0,0-.346.746.693.693,0,0,0,.684.534h4.648l.029.268a3.343,3.343,0,0,0,6.648,0l.029-.268h4.648a.693.693,0,0,0,.684-.534.674.674,0,0,0-.346-.746l-.021-.01-.019-.014a3.509,3.509,0,0,1-.993-1.372,16.241,16.241,0,0,1-1.073-6.416c0-.006,0-.013,0-.016V1.89a6.3,6.3,0,0,0-3.488-5.577l-.167-.083V-5.22A2.481,2.481,0,0,0,54.546-7.7Zm.1,20.6a1.971,1.971,0,0,1-1.923-1.521l-.088-.369h4.022l-.088.369A1.971,1.971,0,0,1,54.445,12.9Zm-1.193-1.29a1.377,1.377,0,0,0,2.387,0Zm7.636-1.968H48l.231-.439.046-.089a16.273,16.273,0,0,0,1.3-7.2,4.866,4.866,0,1,1,9.732,0v.027a16.236,16.236,0,0,0,1.3,7.167l.046.089Zm-11.909-.6H59.912a17.558,17.558,0,0,1-1.2-7.09c0-.009,0-.02,0-.032a4.266,4.266,0,1,0-8.532,0A17.6,17.6,0,0,1,48.979,9.042ZM55.656-4.216l-.342-.048a6.288,6.288,0,0,0-1.738,0l-.342.048v-1a1.112,1.112,0,0,1,1.11-1.112h.2a1.112,1.112,0,0,1,1.11,1.112Zm-1.211-.709c.2,0,.408.009.611.027V-5.22a.511.511,0,0,0-.51-.512h-.2a.511.511,0,0,0-.51.512V-4.9C54.038-4.916,54.242-4.925,54.445-4.925Z" transform="translate(-45.445 8)" fill="#fff"/></g></g></svg>
												<?php esc_html_e( 'Email Notification', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-checklist-settings">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 30 28.7" stroke-width="0" fill="#5B6064"><g id="Group_1" data-name="Group 1" transform="translate(0 0)"><path id="Path_10444" data-name="Path 10444" d="M30,3.6V20.9a3.585,3.585,0,0,1-3.6,3.6H8.5L3.1,27.2,0,28.7V3.6A3.585,3.585,0,0,1,3.6,0H26.4A3.585,3.585,0,0,1,30,3.6ZM3.6,2.2A1.367,1.367,0,0,0,2.2,3.6V25.2L8,22.3H26.4a1.367,1.367,0,0,0,1.4-1.4V3.6a1.367,1.367,0,0,0-1.4-1.4Z" fill-rule="evenodd"></path><path id="check-svgrepo-com" d="M4,10.5l3.2,3.2L14.4,6.5" transform="translate(5.5 1.793)" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="#5B6064"></path></g></svg>
												<?php esc_html_e( 'Editorial Checklist', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-publishing">
												<svg width="18px" height="18px" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#5B6064"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>publish-document</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="publishing-icon" fill="#5B6064" transform="translate(42.666667, 33.830111)"> <path d="M170.666667,51.5032227 L256,136.836556 L256,392.836556 L-2.13162821e-14,392.836556 L-2.13162821e-14,51.5032227 L170.666667,51.5032227 Z M152.993555,94.1698893 L42.6666667,94.1698893 L42.6666667,350.169889 L213.333333,350.169889 L213.333333,154.509668 L152.993555,94.1698893 Z M341.333333,7.10542736e-15 L431.084945,89.7516113 L400.915055,119.921501 L362.666,81.683 L362.666667,222.169889 C362.666667,267.870058 326.742006,305.179572 281.592327,307.398789 L277.333333,307.503223 L277.333333,264.836556 C299.826385,264.836556 318.254189,247.431163 319.882971,225.354153 L320,222.169889 L319.999,81.684 L281.751611,119.921501 L251.581722,89.7516113 L341.333333,7.10542736e-15 Z" id="Combined-Shape"> </path> </g> </g> </g></svg>											
												<?php esc_html_e( 'Publishing', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-suggestion-settings">
												<svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24" fill="#5B6064">
												<path d="M4.85687 21.9777C3.28156 21.9777 2 20.6962 2 19.1209V6.07166C2 4.49644 3.28156 3.21484 4.85687 3.21484H11.9868V4.76231H4.85687C4.13483 4.76231 3.54742 5.3497 3.54742 6.07166V19.1209C3.54742 19.8429 4.13483 20.4303 4.85687 20.4303H17.906C18.628 20.4303 19.2154 19.8429 19.2154 19.1209V12.0066H20.7629V19.1209C20.7629 20.6962 19.4813 21.9777 17.906 21.9777H4.85687Z" stroke-width="0"></path>
												<path d="M6.6123 17.3858V13.3866L18.0018 2L21.9998 5.99776L10.6117 17.3858H6.6123ZM8.11249 14.0079V15.8865H9.99146L17.3387 8.5397L15.4596 6.66048L8.11249 14.0079ZM16.5208 5.59952L18.3981 7.47879L19.8795 5.99751L18.0019 4.1183L16.5208 5.59952Z" stroke-width="0"></path>
												<path d="M13.3333 16.0254H16.6667V17.3854H12L13.3333 16.0254Z" stroke-width="0"></path>
												</svg>
												<?php esc_html_e( 'Suggestion Mode', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-real-time-settings">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16.782 17" fill="#5B6064"><g transform="translate(-4.3 -3.68)"><path stroke-width="0" class="a" d="M4.3,15.915V19.2h8.136a5.069,5.069,0,1,0,.992-7.952,5.749,5.749,0,0,0-1.212-.68,3.783,3.783,0,1,0-4.316,0,5.767,5.767,0,0,0-3.6,5.345Zm15.685-.294a3.967,3.967,0,1,1-3.967-3.967,3.967,3.967,0,0,1,3.967,3.967ZM7.385,7.467a2.681,2.681,0,1,1,2.681,2.681A2.681,2.681,0,0,1,7.385,7.467Zm2.681,3.783a4.665,4.665,0,0,1,2.443.716,5.051,5.051,0,0,0-.9,6.134H5.4V15.915a4.665,4.665,0,0,1,4.665-4.665Z" transform="translate(0)"/><path stroke-width="0" class="a" d="M66.794,56.5H64.9V54H63.8v3.6h3.012Z" transform="translate(-48.572 -41.078)"/></g></svg>
												<?php esc_html_e( 'Real-time Editing (Beta)', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-permissions">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20.2 25.143" fill="#5B6064"><g transform="translate(-1.585 -0.694)"><path stroke-width="0" d="M11.624.794A6.359,6.359,0,0,0,5.26,7.146v3.4H4.329a2.651,2.651,0,0,0-2.644,2.636V23.1a2.651,2.651,0,0,0,2.644,2.636H19.047A2.646,2.646,0,0,0,21.685,23.1V13.184a2.646,2.646,0,0,0-2.638-2.636H17.991v-3.4A6.362,6.362,0,0,0,11.624.794Zm0,1.467a4.866,4.866,0,0,1,4.9,4.885v3.4h-9.8v-3.4a4.862,4.862,0,0,1,4.9-4.885ZM4.329,12.015H19.047a1.15,1.15,0,0,1,1.171,1.169V23.1a1.15,1.15,0,0,1-1.171,1.169H4.329A1.155,1.155,0,0,1,3.152,23.1V13.184a1.155,1.155,0,0,1,1.176-1.169Zm7.359,2.6a2.552,2.552,0,0,0-.736,5V20.94a.734.734,0,0,0,1.467,0V19.613a2.553,2.553,0,0,0-.731-5Zm0,1.467a1.086,1.086,0,1,1-1.095,1.089A1.078,1.078,0,0,1,11.688,16.081Z" transform="translate(0 0)" fill-rule="evenodd"/><path stroke-width="0" d="M11.624.694a6.467,6.467,0,0,1,6.468,6.452v3.3h.956a2.74,2.74,0,0,1,2.738,2.736V23.1a2.74,2.74,0,0,1-2.738,2.736H4.329A2.743,2.743,0,0,1,1.585,23.1V13.184a2.743,2.743,0,0,1,2.744-2.736H5.16v-3.3A6.465,6.465,0,0,1,11.624.694Zm7.424,24.943A2.54,2.54,0,0,0,21.585,23.1V13.184a2.54,2.54,0,0,0-2.538-2.536H17.891v-3.5a6.266,6.266,0,0,0-12.531,0v3.5H4.329a2.543,2.543,0,0,0-2.544,2.536V23.1a2.543,2.543,0,0,0,2.544,2.536ZM11.624,2.161a4.966,4.966,0,0,1,5,4.985v3.5h-10v-3.5a4.962,4.962,0,0,1,5-4.985Zm4.8,8.286v-3.3a4.8,4.8,0,1,0-9.6,0v3.3Zm-12.1,1.467H19.047a1.256,1.256,0,0,1,1.271,1.269V23.1a1.256,1.256,0,0,1-1.271,1.269H4.329A1.259,1.259,0,0,1,3.052,23.1V13.184A1.259,1.259,0,0,1,4.329,11.915ZM19.047,24.17A1.046,1.046,0,0,0,20.118,23.1V13.184a1.046,1.046,0,0,0-1.071-1.069H4.329a1.049,1.049,0,0,0-1.076,1.069V23.1A1.049,1.049,0,0,0,4.329,24.17Zm-7.359-9.656a2.652,2.652,0,0,1,.831,5.173V20.94a.834.834,0,0,1-1.667,0V19.685a2.651,2.651,0,0,1,.836-5.171Zm0,7.057a.636.636,0,0,0,.634-.631v-1.4l.071-.021a2.462,2.462,0,1,0-1.41,0l.071.021v1.4A.635.635,0,0,0,11.685,21.571Zm0-5.59a1.186,1.186,0,1,1-1.195,1.189A1.176,1.176,0,0,1,11.688,15.981Zm0,2.172a.986.986,0,1,0-.995-.983A.976.976,0,0,0,11.688,18.153Z" transform="translate(0 0)"/></g></svg>
												<?php esc_html_e( 'Manage Permissions', 'content-collaboration-inline-commenting' ); ?>
											</li>
											<li class="cf-tab-btn" data-tab="cf-multilingual">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20.001" fill="#5B6064"><g transform="translate(-569.999 -1092)"><path stroke-width="0" d="M501.8,123.924a10,10,0,1,0-7.109,17.07l.01,0h.027a10,10,0,0,0,7.072-17.072Zm-2.713,4.561-.016-.115h3.758a8.435,8.435,0,0,1,0,5.253h-3.76l.016-.115a17.85,17.85,0,0,0,.173-2.511A18.216,18.216,0,0,0,499.088,128.485Zm3.1-1.6h-3.405l-.02-.077a13.525,13.525,0,0,0-1.116-2.987,10.23,10.23,0,0,0-.61-1.021A8.549,8.549,0,0,1,502.188,126.888Zm-7.4,12.573-.056.035-.054-.035a5.935,5.935,0,0,1-1.666-2.231,12.02,12.02,0,0,1-.761-2l-.037-.129h5.036l-.036.129a12.026,12.026,0,0,1-.762,2A5.926,5.926,0,0,1,494.785,139.461Zm-2.895-5.838-.013-.086a16.293,16.293,0,0,1-.2-2.54,16.585,16.585,0,0,1,.193-2.541l.013-.085h5.683l.013.085a16.828,16.828,0,0,1,0,5.082l-.014.086Zm2.787-11.1.053-.032.054.032a5.337,5.337,0,0,1,1.679,2.242,12.087,12.087,0,0,1,.754,1.995l.036.128h-5.044l.036-.128a12.276,12.276,0,0,1,.754-1.995A5.344,5.344,0,0,1,494.677,122.522Zm-2.255.281a10.222,10.222,0,0,0-.608,1.02,13.593,13.593,0,0,0-1.117,2.988l-.019.077h-3.405A8.549,8.549,0,0,1,492.421,122.8ZM486.211,131a8.492,8.492,0,0,1,.418-2.626h3.758l-.015.115A18.216,18.216,0,0,0,490.2,131a18.009,18.009,0,0,0,.172,2.511l.016.115h-3.76A8.5,8.5,0,0,1,486.211,131Zm1.06,4.107h3.409l.019.077a13.522,13.522,0,0,0,1.137,3.011,10.433,10.433,0,0,0,.6,1A8.549,8.549,0,0,1,487.271,135.1Zm9.75,4.09a10.532,10.532,0,0,0,.6-1,13.6,13.6,0,0,0,1.137-3.011l.019-.077h3.408A8.55,8.55,0,0,1,497.02,139.193Z" transform="translate(85.27 971.005)"/></g></svg>
												<?php esc_html_e( 'Multilingual Options', 'content-collaboration-inline-commenting' ); ?>
											</li>
										</ul>
									</div>
									<div class="cf-settings-wrapper__content">
										<!-- All your PHP blocks go here -->
										<?php
					
												$disabled       = 'disabled';
												$disabled_class = 'cf_disabled_input';
										
										?>
										<div id="cf-general" class="cf-tab-content cf-tab-active">
											<!-- General settings -->
											<div class="cf-settings-wrap__repeater">
												<div class="cf-settings-wrap__repeater-header">
													<h3><?php esc_html_e( 'General', 'content-collaboration-inline-commenting' ); ?></h3>
													<p><?php esc_html_e( 'Manage your general settings from here.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/28-general-settings?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
												</div>
												<?php
													// Get general settings form HTML.
													require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-general.php';
												?>
												<div class="cf-settings-wrap__repeater-header cf-cnt-pro-migration-header">
													<h3><?php esc_html_e( 'Migration Setting', 'content-collaboration-inline-commenting' ); ?></h3>
												</div>
												<div class="cf-settings-wrap__repeater-body cf-cnt-pro-migration">
													<div id="migration-progress-bar" style="display: none"><span>% completed</span></div>
													<div id="migration-progress-info"></div>
													<p class="submit"><a href="javascrpit:void(0)" id="pro-migration-button" class="button button-primary">Migrate</a></p>
												</div>
											</div>
										</div>
										<?php // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1 ?>
										<div id="cf-email" class="cf-tab-content">
											<!-- Email notification -->
											<?php // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1 ?>
											<div class="cf-settings-wrap__repeater">
												<div class="cf-settings-wrap__repeater-header">
													<h3>
														<?php
														printf(
															'%s',
															esc_html__( 'Email Notification', 'content-collaboration-inline-commenting' )
														);
														?>
													</h3>											<p>
													<?php
													printf(
														'%s - <a href="https://docs.multicollab.com/article/11-email-notifications?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"> %s <img class="cf-external-link-icon" src="%s" alt="external-link"></a>',
														esc_html__( 'Set your email notification preferences.', 'content-collaboration-inline-commenting' ),
														esc_html__( 'Guide to Setup Email Notifications', 'content-collaboration-inline-commenting' ),
														esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/arrow_blue.svg' ),
													);
													?>
												</p>
												</div>
												<?php
													// Get general settings form HTML.
													require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-email-notification.php';
													?>
											</div>
										</div>
										<?php
										$disabled_class = 'cf_disabled_input';
										$disabled       = 'disabled';
										?>
										<div id="cf-checklist-settings" class="cf-tab-content">
											<!-- checklist  -->
											<div class="cf-suggestion-box">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
													<h3>
														<?php printf( '%s', esc_html__( 'Editorial Checklist', 'content-collaboration-inline-commenting' ) ); ?>
														<?php
															?>
															<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
													</h3>
														<p><?php esc_html_e( 'Ensure key publishing tasks are completed by enabling editorial checklists.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/104-editorial-checklist?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-checklist.php';
															?>
													</div>
												</div>
											</div>
										</div>
										<div id="cf-publishing" class="cf-tab-content">
											<div class="cf-suggestion-box">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
														<h3>
															<?php printf( '%s', esc_html__( 'Publishing', 'content-collaboration-inline-commenting' ) ); ?>
													
															<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
														</h3>
														<p><?php esc_html_e( 'Alert authors about any unresolved comments or suggestions before they publish a post.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/21-publishing-settings?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-publishing.php';
															?>
													</div>
												</div>
											</div>
										</div>
										<?php // Suggestion Mode/@author Rishi Shah/@since EDD - 3.0.1 ?>
										<?php $disabled_class = 'cf_disabled_input'; ?>
										<div id="cf-suggestion-settings" class="cf-tab-content">
					
											<div class="cf-suggestion-box" id="cf-suggestion-settings">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
													<h3>
															<?php printf( '%s', esc_html__( 'Suggestion Mode', 'content-collaboration-inline-commenting' ) ); ?>
														
															<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
															
													</h3>
													<p><?php esc_html_e( 'Suggests edit without changing the original content.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/23-suggestion-mode?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-suggestion-mode.php';
															?>
													</div>
												</div>
											</div>
										</div>
										<div id="cf-real-time-settings" class="cf-tab-content">
											<div class="cf-suggestion-box" id="real-time-settings">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
														<h3>
															<?php printf( '%s', esc_html__( 'Real-time Editing ', 'content-collaboration-inline-commenting' ) ); ?>
															(<i><?php printf( esc_html__( 'Beta', 'content-collaboration-inline-commenting' ) ); ?></i>)
															<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
														</h3>
														<p><?php esc_html_e( 'Multiple team members can write and edit the same post or page simultaneously.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/24-real-time-editing?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-realtime-mode.php';
														?>
													</div>
												</div>
											</div>
										</div>
										<?php // Manage Permissions/@author Rishi Shah/@since EDD - 3.0.1. ?>
										<?php $disabled_class = 'cf_disabled_input'; ?>
										<div id="cf-permissions" class="cf-tab-content">
											<!-- Manage permissions -->
											<?php // Manage Permissions/@author Rishi Shah/@since EDD - 3.0.1 ?>
											<div class="cf-suggestion-box">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
														<h3>
															<?php printf( '%s', esc_html__( 'Manage Permissions', 'content-collaboration-inline-commenting' ) ); ?>
															
																<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
															
														</h3>
														<p><?php esc_html_e( 'Decide which role should have permission to manage comments and suggestion in multicollab.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/25-custom-permissions?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-permissions.php';
														?>
													</div>
												</div>
											</div>
										</div>
										<?php $disabled_class = 'cf_disabled_input'; ?>
										<div id="cf-multilingual" class="cf-tab-content">
											<div class="cf-content-language-box">
												<div class="cf-settings-wrap__repeater">
													<div class="cf-settings-wrap__repeater-header">
														<h3>
															<?php printf( '%s', esc_html__( 'Multilingual Options', 'content-collaboration-inline-commenting' ) ); ?>
															
															<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
																
														</h3>
														<p><?php esc_html_e( 'This features helps in enhavcing the accessibility of the Multicollab plugin for users who do not follow the English language.', 'content-collaboration-inline-commenting' ); ?><a href="https://docs.multicollab.com/article/26-multilingual?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></p>
													</div>
													<div class="<?php echo esc_html( $disabled_class ); ?>">
														<?php
															// Get permission form HTML.
															require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-multilingual-options.php';
														?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
							</div>
						<?php endif; ?>

						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div id="cf-roles-slack-integration" class="cf-dashboard-layout__tabs-wrap-inner 
							<?php
							if ( ! empty( $tab_number ) || 'intigrations' === $view ) {
								echo esc_html( 'cf-tab-active' );
							}
							?>
							">
								<div class="cf-settings-panel__repeater">
									<div class="cf-settings-panel__repeater-header">
										<h3><?php esc_html_e( 'Slack', 'content-collaboration-inline-commenting' ); ?>
										
										<a href="#" class="cf_premium_star"><?php printf( esc_html__( 'Upgrade to Premium', 'content-collaboration-inline-commenting' ) . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52542" data-name="Group 52542" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg>' ); ?></a>
											
										</h3>
										<img style="width:100%;" alt="Slack-integrations" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/slackIntegration.png' ); ?>"/>
									</div>
								</div>
								<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
							</div>
						<?php endif; ?>

						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div id="cf-modules" class="cf-dashboard-layout__tabs-wrap-inner 
								<?php
								if ( ! empty( $tab_number ) || 'modules' === $view ) {
									echo esc_html( 'cf-tab-active' ); }
								?>
								">
								
								<div class="cf-settings-panel__checklist">
									<?php
										if( isset( $mode ) && 'checklist-settings' === $mode ) {
											require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-checklist-page.php';
										} else {
											require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-module-page.php';
										}
										
									?>
								</div>
								<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
								
							</div>
						<?php endif; ?>
						<div id="cf-need-help" class="cf-dashboard-layout__tabs-wrap-inner 
							<?php
							if ( ! empty( $tab_number ) || 'need-help' === $view ) {
								echo esc_html( 'cf-tab-active' ); 
							}
							?>
						">
							<?php
								// Load "Need Help" content here.
								require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-need-help.php';
							?>
							<div class="cf-copyright-text"><?php esc_html_e( 'Multicollab is powered by ', 'content-collaboration-inline-commenting' ); ?>
								<img class="cf-footer-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multidots-logo.svg' ); ?>" alt="Multidots" style="height: 16px;position: relative;top: 0px;">
								<?php esc_html_e( ' — Enterprise WordPress Agency.', 'content-collaboration-inline-commenting' ); ?>
								<a href="https://www.multidots.com?utm_source=plugin+&utm_medium=+Explore+custom+solutions.&utm_campaign=plugin_footer_free" target="_blank"><?php esc_html_e( 'Explore custom solutions', 'content-collaboration-inline-commenting' ); ?><img class="cf-external-link-icon" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/arrow_blue.svg' ); ?>" alt="external-link"></a></div>							
						</div>
					</div>
				</div>
			</div>

			<div class="cf-right">
			</div>
		</div>
  
</div>
