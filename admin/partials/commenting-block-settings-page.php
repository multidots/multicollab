<?php
/**
 * Gutenberg Commenting Feature Settings Page.
 */

// Get settings.
$activated      = filter_input( INPUT_GET, "activated", FILTER_SANITIZE_STRING );
$cf_admin_notif = get_option( 'cf_admin_notif' );
?>
<div class="cf-plugin-settings">
    <div class="cf-plugin-header">
        <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/images/commenting-logo.svg' ); ?>"/>
        <h1><?php esc_html_e( 'Multicollab - Google Doc-Style Editorial Commenting for WordPress', 'content-collaboration-inline-commenting' ); ?></h1>
    </div>
    <form id="cf-settings-form" method="post">
        <div class="cf-outer">
            <div class="cf-left">
                <div class="cf-tabs-main">
                    <ul class="cf-tabs">
                        <li class="cf-tab-active"><a href="javascript:void(0)" class="cf-tab-item" data-id="cf-dashboard"><?php esc_html_e( 'Dashboard', 'content-collaboration-inline-commenting' ) ?></a></li>
                        <li><a href="javascript:void(0)" class="cf-tab-item" data-id="cf-settings"><?php esc_html_e( 'Settings', 'content-collaboration-inline-commenting' ); ?></a></li>
                    </ul>
                    <div class="cf-tabs-content">
                        <div id="cf-dashboard" class="cf-tab-inner cf-tab-active">
                            <div class="cf-content-simple">
                                <?php if ( $activated ) : ?>
                                    <p class="cf-greetings"><?php esc_html_e( 'Thanks for installing Google Doc-Style Editorial Commenting Plugin.', 'content-collaboration-inline-commenting' ) ?></p>
                                <?php endif; ?>
                                <div class="cf-video">
                                    <iframe width="970" height="530" src="<?php echo esc_url( 'https://www.youtube-nocookie.com/embed/rDdgh_u8oVQ' ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                                <div class="cf-video-links">
                                    <div class="cf-link-wrap">
                                        <a href="<?php echo esc_url( 'https://www.multidots.com/multicollab/?demo=start' ); ?>" class="cf-button button button-primary" target="_blank" id="cf-demo"><?php esc_html_e( 'Live Demo', 'content-collaboration-inline-commenting' ); ?></a>
                                    </div>
                                    <div class="cf-link-wrap">
                                        <p><a href="<?php echo esc_url( 'https://www.multidots.com/multicollab/' ); ?>" class="cf-link" target="_blank" id="cf-demo"><?php esc_html_e( 'Learn More', 'content-collaboration-inline-commenting' ); ?></a> <?php esc_html_e( 'About Plugin', 'content-collaboration-inline-commenting' ); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="cf-settings" class="cf-tab-inner">
                            <div class="cf-content-box">
                                <div class="cf-cnt-box-header">
                                    <h3><?php esc_html_e( 'Notification Setting', 'content-collaboration-inline-commenting' ); ?></h3>
                                </div>
                                <div class="cf-cnt-box-body">
                                    <div id="cf-notice">
                                        <div class="cf-success notice notice-success" style="display: none">
                                            <p><?php esc_html_e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="cf-notification-settings">
                                        <div class="cf-check-wrap">
                                            <input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : '' ?> value="1" class="regular-text"/>
                                            <span class="cf-check"></span>
                                        </div>
                                        <label for="cf_admin_notif"><?php esc_html_e( 'Notify site admin', 'content-collaboration-inline-commenting' ); ?> (<?php echo esc_html( get_option( 'admin_email') ) ?>) <?php esc_html_e( 'for all new comments even if not mentioned.', 'content-collaboration-inline-commenting' ); ?></label>
                                    </div>
                                    <?php submit_button( __( 'Save Changes', 'content-collaboration-inline-commenting' )); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cf-right">
                <div id="cf-developer" class="cf-card">
                    <div class="cf-card-header">
                        <h3><?php esc_html_e( 'Plugin Developer', 'content-collaboration-inline-commenting' ); ?></h3>
                    </div>
                    <div class="cf-card-body">
                        <div class="cf-dev-img">
                            <a href="<?php echo esc_url( 'https://www.multidots.com/' ); ?>" target="_blank">
                                <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/images/mdinc-logo.svg' ); ?>"/>
                            </a>
                        </div>
                        <p><?php esc_html_e( 'WordPress Development Experts & WordPress VIP Partner Agency', 'content-collaboration-inline-commenting' ); ?></p>
                        <a href="<?php echo esc_url( 'https://www.multidots.com/' ); ?>" target="_blank" class="cf-button button button-primary"><?php esc_html_e( 'Visit Site', 'content-collaboration-inline-commenting' ); ?></a>
                    </div>
                </div>
                <div id="cf-contact" class="cf-card">
                    <div class="cf-card-header">
                        <h3><?php esc_html_e( 'Contact Support', 'content-collaboration-inline-commenting' ); ?></h3>
                    </div>
                    <div class="cf-card-body">
                        <p><?php esc_html_e( 'For premium support and help with customizing this plugin for your business needs.', 'content-collaboration-inline-commenting' ) ?></p>
                        <a href="<?php echo esc_url( 'https://www.multidots.com/multicollab/#commenting-contact-form' ); ?>" target="_blank" class="cf-button button button-primary"><?php esc_html_e( 'Contact Us', 'content-collaboration-inline-commenting' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
