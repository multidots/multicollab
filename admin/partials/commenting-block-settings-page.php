<?php

/**
 * Gutenberg Commenting Feature Settings Page.
 */
// Get settings.
$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_STRING );
$webhook_url = filter_input( INPUT_GET, 'webhook_url', FILTER_SANITIZE_STRING );
$channel = filter_input( INPUT_GET, 'channel', FILTER_SANITIZE_STRING );
$access_token = filter_input( INPUT_GET, 'access_token', FILTER_SANITIZE_STRING );
$tab_number = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
$activated = filter_input( INPUT_GET, 'activated', FILTER_SANITIZE_STRING );
$channel_id = filter_input( INPUT_GET, 'channel_id', FILTER_SANITIZE_STRING );
$user_access_token = filter_input( INPUT_GET, 'user_access_token', FILTER_SANITIZE_STRING );
$bot_user_id = filter_input( INPUT_GET, 'bot_user_id', FILTER_SANITIZE_STRING );
$cf_admin_notif = get_option( 'cf_admin_notif' );
$cf_show_infoboard = get_option( 'cf_show_infoboard' );
$cf_hide_editorial_column = get_option( 'cf_hide_editorial_column' );
$cf_slack_default_channel = get_option( 'channel' );
$cf_slack_channels = get_option( 'cf_slack_channels' );
$cf_slack_notification_add_comment = get_option( 'cf_slack_notification_add_comment' );
$cf_slack_notification_add_suggestion = get_option( 'cf_slack_notification_add_suggestion' );
$cf_slack_notification_resolve_comment = get_option( 'cf_slack_notification_resolve_comment' );
$cf_give_alert_message = get_option( 'cf_give_alert_message' );
$cf_suggestion_mode_option_name = get_option( 'cf_suggestion_mode_option_name' );
$cf_specific_post_types_values = get_option( 'cf_specific_post_types_values' );
$cf_specific_post_categories_values = get_option( 'cf_specific_post_categories_values' );
$cf_hide_floating_icons = get_option( 'cf_hide_floating_icons' );
$cf_slack_notification_accept_reject_suggestion = get_option( 'cf_slack_notification_accept_reject_suggestion' );
$cf_page_url = menu_page_url( 'editorial-comments', false );
$cf_web_activity_url = add_query_arg( 'view', 'web-activity', $cf_page_url );
$cf_post_activity_url = add_query_arg( 'view', 'post-activity', $cf_page_url );
$view = ( null === $view ? 'web-activity' : $view );
$cf_permissions = get_option( 'cf_permissions' );

if ( !empty($webhook_url) && !empty($channel) && cf_fs()->is_plan( 'vip', true ) ) {
    update_option( 'cf_slack_webhook', $webhook_url, true );
    update_option( 'channel', $channel, true );
    update_option( 'default_channel', $channel, true );
    update_option( 'access_token', $access_token, true );
    update_option( 'channel_id', $channel_id, true );
    update_option( 'bot_user_id', $bot_user_id, true );
    update_option( 'user_access_token', $user_access_token, true );
    // New method.
    $access_token = get_option( 'access_token' );
    $channel_id = get_option( 'channel_id' );
    $bot_user_id = get_option( 'bot_user_id' );
    $user_access_token = get_option( 'user_access_token' );
    $headers = array(
        'Accept'        => 'application/json',
        'Authorization' => 'Bearer ' . $user_access_token,
    );
    $data = array(
        'channel' => $channel_id,
        'users'   => $bot_user_id,
    );
    $api_root = 'https://slack.com/api/';
    $response = Requests::post( $api_root . 'conversations.invite', $headers, $data );
    $channels_response = json_decode( $response->body );
    wp_safe_redirect( site_url() . '/wp-admin/admin.php?page=editorial-comments&tab=integrations' );
    exit;
}


if ( cf_fs()->is_plan( 'plus', true ) ) {
    delete_option( 'cf_suggestion_mode_option_name' );
    delete_option( 'cf_specific_post_types_values' );
    delete_option( 'cf_specific_post_categories_values' );
}

$cf_slack_webhook = get_option( 'cf_slack_webhook' );
// Save slack channels.

if ( empty($cf_slack_channels) && cf_fs()->is_plan( 'vip', true ) ) {
    $access_token = get_option( 'access_token' );
    $user_access_token = get_option( 'user_access_token' );
    
    if ( isset( $access_token ) ) {
        $headers = array(
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $user_access_token,
        );
        $data = array(
            'limit' => '100000000',
            'types' => 'public_channel',
        );
        $api_root = 'https://slack.com/api/';
        $response = Requests::post( $api_root . 'conversations.list', $headers, $data );
        $channels_response = json_decode( $response->body );
        if ( true === $channels_response->ok ) {
            $all_channles = $channels_response->channels;
        }
        $private_data = array(
            'limit' => '100000000',
            'types' => 'private_channel',
        );
        $api_root = 'https://slack.com/api/';
        $response = Requests::post( $api_root . 'conversations.list', $headers, $private_data );
        $private_channels_response = json_decode( $response->body );
        if ( true === $private_channels_response->ok ) {
            $private_channels = $private_channels_response->channels;
        }
        if ( !empty($private_channels) ) {
            $all_channles = array_merge( $all_channles, $private_channels );
        }
        $channel_id = get_option( 'channel_id' );
        $default_channel = get_option( 'channel' );
        $default_channel = str_replace( '@', '@ ', $default_channel );
        $default_channel_array = array(
            'id'   => $channel_id,
            'name' => $default_channel,
        );
        if ( '@' === substr( $default_channel, 0, 1 ) ) {
            array_push( $all_channles, (object) $default_channel_array );
        }
        
        if ( !empty($all_channles) ) {
            update_option( 'cf_slack_channels', $all_channles );
            $cf_slack_channels = $all_channles;
        }
    
    }

}

$channel_id = get_option( 'channel_id' );
$cf_slack_channels = get_option( 'cf_slack_channels' );
?>
<div class="cf-plugin-settings">
<div class="cf_settings_loader"></div>
			<?php 
$CF_PROMOTIONAL_BANNER_API_URL = CF_PROMOTIONAL_BANNER_API_URL . 'wp-json/promotional-banner/v2/promotional-banner?' . rand();
$promotional_banner_request = wp_remote_get( $CF_PROMOTIONAL_BANNER_API_URL );

if ( empty($promotional_banner_request->errors) ) {
    $promotional_banner_request_body = $promotional_banner_request['body'];
    $promotional_banner_request_body = json_decode( $promotional_banner_request_body, true );
    if ( !empty($promotional_banner_request_body) ) {
        foreach ( $promotional_banner_request_body as $promotional_banner_request_body_data ) {
            $promotional_banner_cookie = $promotional_banner_request_body_data['promotional_banner_cookie'];
            $promotional_banner_image = $promotional_banner_request_body_data['promotional_banner_image'];
            $promotional_banner_description = $promotional_banner_request_body_data['promotional_banner_description'];
            $promotional_banner_button_group = $promotional_banner_request_body_data['promotional_banner_button_group'];
            $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_STRING );
            $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
            
            if ( empty($banner_cookie) && 'yes' !== $banner_cookie ) {
                ?>
				<div class="cf-plugin-popup <?php 
                echo  ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' ) ;
                ?>">
							<?php 
                
                if ( !empty($promotional_banner_image) ) {
                    ?>
							<img src="<?php 
                    echo  esc_url( $promotional_banner_image ) ;
                    ?>"/>
								<?php 
                }
                
                ?>
						<div class="cf-plugin-popup-meta">
							<p>
							<?php 
                echo  str_replace( array( '<p>', '</p>' ), '', $promotional_banner_description ) ;
                if ( !empty($promotional_banner_button_group) ) {
                    foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                        ?>
										<a href="<?php 
                        echo  esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] ) ;
                        ?>" target="_blank"><?php 
                        echo  esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] ) ;
                        ?></a>
									<?php 
                    }
                }
                ?>
						</p>
						</div>
						<a href="#." data-popup-name="<?php 
                echo  ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' ) ;
                ?>" class="cf-pluginpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
		</div>
							<?php 
            }
        
        }
    }
    ?>
			<?php 
}

?>
			<div class="cf-plugin-header">
			<div class="cf-plugin-logo">
				<a href="https://www.multicollab.com/" target="_blank"><img src="<?php 
echo  esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/multicollab_logo.svg' ) ;
?>"/>
				<span class="cf-plan-name">
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513">
					<g id="Group_52550" data-name="Group 52550" transform="translate(-285.455 -280.192)">
						<path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/>
						<path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/>
					</g>
					</svg>
					<?php 
echo  esc_html( 'FREE' ) ;
?>
				</span>
				<a class="cf-plan-version" href="<?php 
echo  esc_url( 'https://www.multicollab.com/blog/release-notes/?utm_source=plugin_setting_header_release-notes&utm_medium=header_release-notes_link&utm_campaign=plugin_setting_release-notes_link&utm_id=plugin_setting_header_link' ) ;
?>" target="_blank">v <?php 
echo  esc_html( COMMENTING_BLOCK_VERSION ) ;
?></a>
			</div>
			<div class="cf-plugin-version">
				<a href="https://docs.multicollab.com/?utm_source=plugin_setting_header_helpdoc_link&utm_medium=header_helpdoc_link&utm_campaign=plugin_setting_header_link&utm_id=plugin_setting_header_link" target="_blank"><?php 
esc_html_e( 'Help', 'content-collaboration-inline-commenting' );
?></a> | 
				<a href="https://www.multicollab.com/contact/?utm_source=plugin_setting_header_link_contact&utm_medium=header_link_contact&utm_campaign=plugin_setting_header_link_contact&utm_id=plugin_setting_header_link" target="_blank"><?php 
esc_html_e( 'Contact', 'content-collaboration-inline-commenting' );
?></a> | 
				<a href="https://feedback.multicollab.com/feedback/add?utm_source=plugin_setting_header_link_add_feedback&utm_medium=header_link_add_feedback&utm_campaign=plugin_setting_header_link&utm_id=plugin_setting_header_link" target="_blank"><?php 
esc_html_e( 'Feedback', 'content-collaboration-inline-commenting' );
?></a>
				<?php 

if ( !cf_fs()->is_plan( 'vip', true ) ) {
    ?>
				<a class="pricing-block-button__link" href="<?php 
    echo  esc_url( 'https://www.multicollab.com/pricing/?utm_source=plugin_setting_header_free-user_upgrade_to_premium&utm_medium=header_free-user_upgrade_to_premium_link&utm_campaign=plugin_setting_free-user_upgrade_to_premium_link&utm_id=plugin_setting_header_link.++' ) ;
    ?>" target="_blank"><?php 
    esc_html_e( 'Upgrade', 'content-collaboration-inline-commenting' );
    ?></a>
				<?php 
}

?>
			</div>
		</div>
	
		<div class="cf-outer">
			<div class="cf-left cf-pricing-dashboard">
				<div class="cf-tabs-main">
				<ul class="cf-tabs">
					<li class="
						<?php 
if ( ('dashboard' === $tab_number || empty($tab_number) || 'web-activity' === $view) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number ) {
    echo  esc_html( 'cf-tab-active' ) ;
}
?>
							"><a href="/wp-admin/admin.php?page=editorial-comments&view=web-activity" class="cf-tab-item" data-id="cf-dashboard"><?php 
esc_html_e( 'Dashboard', 'content-collaboration-inline-commenting' );
?></a></li>
						<li class="
									<?php 
if ( 'post-activity' === $view ) {
    echo  esc_html( 'cf-tab-active' ) ;
}
?>
						"><a href="/wp-admin/admin.php?page=editorial-comments&view=post-activity" class="cf-tab-item" data-id="cf-reports"><?php 
esc_html_e( 'Reports', 'content-collaboration-inline-commenting' );
?></a></li>
						<?php 

if ( current_user_can( 'administrator' ) ) {
    ?>
						<li class="
							<?php 
    if ( 'settings' === $view ) {
        echo  esc_html( 'cf-tab-active' ) ;
    }
    ?>
						"><a href="/wp-admin/admin.php?page=editorial-comments&view=settings" class="cf-tab-item" data-id="cf-settings"><?php 
    esc_html_e( 'Settings', 'content-collaboration-inline-commenting' );
    ?></a></li>
							<?php 
    ?>
						<?php 
}

?>
					</ul>
					<div class="cf-tabs-content">
						<div id="cf-dashboard" class="cf-tab-inner 
							<?php 
if ( ('dashboard' === $tab_number || empty($tab_number) || 'web-activity' === $view) && 'post-activity' !== $view && 'intigrations' !== $view && 'settings' !== $view && 'integrations' !== $tab_number ) {
    echo  esc_html( 'cf-tab-active' ) ;
}
?>
						">
							<?php 
require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-dashboard.php';
// Removed phpcs:ignore by Rishi Shah.
?>
						</div>
						<?php 

if ( current_user_can( 'administrator' ) ) {
    ?>
						<div id="cf-reports" class="cf-tab-inner
							<?php 
    if ( 'post-activity' === $view ) {
        echo  esc_html( 'cf-tab-active' ) ;
    }
    ?>
						">
							<?php 
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-report.php';
    // Removed phpcs:ignore by Rishi Shah.
    ?>
						</div>
						<?php 
}

?>
						<?php 

if ( current_user_can( 'administrator' ) ) {
    ?>
							<div id="cf-settings" class="cf-tab-inner
							<?php 
    if ( 'settings' === $view ) {
        echo  esc_html( 'cf-tab-active' ) ;
    }
    ?>
							">
							<div class="cf-content-box">
								<div class="cf-cnt-box-header">
									<h3><?php 
    esc_html_e( 'General', 'content-collaboration-inline-commenting' );
    ?></h3>
								</div>
								<?php 
    // Get general settings form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-general.php';
    ?>
								<div class="cf-cnt-box-header cf-cnt-pro-migration-header">
									<h3><?php 
    esc_html_e( 'Migration Setting', 'content-collaboration-inline-commenting' );
    ?></h3>
								</div>
								<div class="cf-cnt-box-body cf-cnt-pro-migration">
									<div id="migration-progress-bar" style="display: none"><span>% completed</span></div>
									<div id="migration-progress-info"></div>
									<p class="submit"><a href="javascrpit:void(0)" id="pro-migration-button" class="button button-primary">Migrate</a></p>
								</div>
							</div>
							<?php 
    $disabled_class = '';
    $disabled = 'disabled';
    $disabled_class = 'cf_disabled_input';
    ?>
							<?php 
    // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1
    ?>
							<div class="cf-content-box <?php 
    echo  esc_html( $disabled_class ) ;
    ?>">
								<div class="cf-cnt-box-header">
									<h3><?php 
    printf( '%s - <a href="https://docs.multicollab.com/activate-email-notifications" target="_blank"> %s </a>', esc_html__( 'Email Notification', 'content-collaboration-inline-commenting' ), esc_html__( 'Guide to Setup Email Notifications', 'content-collaboration-inline-commenting' ) );
    ?></h3>
								</div>
								<?php 
    // Get general settings form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-email-notification.php';
    ?>
							</div>
							<?php 
    $disabled_class = '';
    $disabled = 'disabled';
    $disabled_class = 'cf_disabled_input';
    ?>
							<div class="cf-suggestion-box <?php 
    echo  esc_html( $disabled_class ) ;
    ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php 
    printf( '%s <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52538" data-name="Group 52538" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg> %s', esc_html__( 'Publishing', 'content-collaboration-inline-commenting' ), esc_html__( 'Premium', 'content-collaboration-inline-commenting' ) );
    ?></h3>
									</div>
									<?php 
    // Get permission form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-publishing.php';
    ?>
								</div>
							</div>
							<?php 
    // Suggestion Mode/@author Rishi Shah/@since EDD - 3.0.1
    ?>
							<?php 
    $disabled = '';
    $disabled_class = '';
    
    if ( !cf_fs()->is__premium_only() || true === cf_fs()->is_plan( 'plus', true ) ) {
        $disabled = 'disabled';
        $disabled_class = 'cf_disabled_input';
    }
    
    ?>
							<div class="cf-suggestion-box <?php 
    echo  esc_html( $disabled_class ) ;
    ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php 
    printf(
        '%s <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52538" data-name="Group 52538" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg> %s: %s',
        esc_html__( 'Suggestion Mode', 'content-collaboration-inline-commenting' ),
        esc_html__( 'Premium', 'content-collaboration-inline-commenting' ),
        esc_html__( 'PRO', 'content-collaboration-inline-commenting' )
    );
    ?></h3>
									</div>
									<?php 
    // Get permission form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-suggestion-mode.php';
    ?>
								</div>
							</div>

							<?php 
    // Manage Permissions/@author Rishi Shah/@since EDD - 3.0.1
    ?>
							<?php 
    $disabled_class = '';
    $disabled = 'disabled';
    $disabled_class = 'cf_disabled_input';
    ?>
							<div class="cf-suggestion-box <?php 
    echo  esc_html( $disabled_class ) ;
    ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php 
    printf( '%s <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52538" data-name="Group 52538" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg> %s', esc_html__( 'Manage Permissions', 'content-collaboration-inline-commenting' ), esc_html__( 'Premium', 'content-collaboration-inline-commenting' ) );
    ?></h3>
									</div>
									<?php 
    // Get permission form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-permissions.php';
    ?>
								</div>
							</div>
							<?php 
    $disabled = '';
    $disabled_class = '';
    
    if ( !cf_fs()->is__premium_only() || true === cf_fs()->is_plan( 'pro', true ) || true === cf_fs()->is_plan( 'plus', true ) ) {
        $disabled = 'disabled';
        $disabled_class = 'cf_disabled_input';
    }
    
    ?>
							<div class="cf-content-language-box <?php 
    echo  esc_html( $disabled_class ) ;
    ?>">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php 
    printf(
        '%s <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12.513" viewBox="0 0 14 12.513"><g id="Group_52538" data-name="Group 52538" transform="translate(-285.455 -280.192)"><path id="Path_199491" data-name="Path 199491" d="M324.995,428.1a.56.56,0,0,1-.561.561h-8.208a.561.561,0,1,1,0-1.121h8.208a.561.561,0,0,1,.56.561Z" transform="translate(-27.875 -135.952)" fill="#d0a823"/><path id="Path_199492" data-name="Path 199492" d="M299.228,282.364h0a.559.559,0,0,0-.623-.029l-3.432,2.078-2.229-3.938a.561.561,0,0,0-.976,0l-2.229,3.938-3.432-2.078a.56.56,0,0,0-.833.616l1.728,6.863a.56.56,0,0,0,.543.424h9.423a.56.56,0,0,0,.543-.424l1.728-6.863A.559.559,0,0,0,299.228,282.364Zm-2.5,6.753h-8.549L286.893,284l2.759,1.67a.561.561,0,0,0,.778-.2l2.025-3.579,2.026,3.578a.561.561,0,0,0,.778.2l2.759-1.67Z" transform="translate(0 0)" fill="#d0a823"/></g></svg> %s: %s (%s)',
        esc_html__( 'Multilingual Options', 'content-collaboration-inline-commenting' ),
        esc_html__( 'Premium', 'content-collaboration-inline-commenting' ),
        esc_html__( 'VIP', 'content-collaboration-inline-commenting' ),
        esc_html__( 'Coming Soon', 'content-collaboration-inline-commenting' )
    );
    ?></h3>
									</div>
									<?php 
    // Get permission form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/settings/settings-multilingual-options.php';
    ?>
								</div>
							</div>
						</div>
						<?php 
}

?>

						<?php 

if ( current_user_can( 'administrator' ) ) {
    ?>
							<div id="cf-roles-slack-integration" class="cf-tab-inner 
							<?php 
    if ( !empty($tab_number) || 'intigrations' === $view ) {
        echo  esc_html( 'cf-tab-active' ) ;
    }
    ?>
							">
								<div class="cf-content-box">
									<div class="cf-cnt-box-header">
										<h3><?php 
    esc_html_e( 'Slack', 'content-collaboration-inline-commenting' );
    ?></h3>
									</div>
									<?php 
    // Get permission form HTML.
    require_once COMMENTING_BLOCK_DIR . 'admin/intigrations/intigrations-slack.php';
    ?>
								</div>
							</div>
						<?php 
}

?>

					</div>
				</div>
			</div>
			
			<div class="cf-right">
			</div>
		</div>
  
</div>
