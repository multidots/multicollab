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
        <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/commenting-logo.svg' ); ?>"/>
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
                        <h3><?php esc_html_e( 'Priority Support', 'content-collaboration-inline-commenting' ); ?></h3>
                    </div>
                    <div class="cf-card-body">
                        <div class="cf-dev-img">
                            <a href="<?php echo esc_url( 'https://www.multidots.com/' ); ?>" target="_blank">
                                <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/mdinc-logo.svg' ); ?>"/>
                            </a>
                        </div>
                        <h3 class="cf-card-title"><strong><?php esc_html_e( '$99', 'content-collaboration-inline-commenting' ); ?></strong><?php esc_html_e( ' / Annual', 'content-collaboration-inline-commenting' ); ?></h3>
                        <ul class="cf-card-check-list">
                            <li><?php esc_html_e( 'Response time 3 business days', 'content-collaboration-inline-commenting' ); ?></li>
                            <li><?php esc_html_e( 'Get immediate Patch for fixes', 'content-collaboration-inline-commenting' ); ?></li>
                            <li><?php esc_html_e( 'Email-based support', 'content-collaboration-inline-commenting' ); ?></li>
                        </ul>
                        <a href="<?php echo esc_url( 'https://www.multidots.com/multicollab/checkout/?add-to-cart=1208' ); ?>" target="_blank" class="cf-button button button-primary"><?php esc_html_e( 'Buy Now', 'content-collaboration-inline-commenting' ); ?></a>
                    </div>
                </div>
                <div id="cf-contact" class="cf-card">
                    <div class="cf-card-header">
                        <h3><?php esc_html_e( 'Standard Support', 'content-collaboration-inline-commenting' ); ?></h3>
                    </div>
                    <div class="cf-card-body">
                        <ul class="cf-card-check-list">
                            <li><?php esc_html_e( 'Response time within 1 to 2 Weeks.', 'content-collaboration-inline-commenting' ); ?></li>
                            <li><?php esc_html_e( 'Get fixes in the next release.', 'content-collaboration-inline-commenting' ); ?></li>
                        </ul>
                        <a href="<?php echo esc_url( 'https://www.multidots.com/multicollab/#commenting-contact-form' ); ?>" target="_blank" class="cf-button button button-primary"><?php esc_html_e( 'Free', 'content-collaboration-inline-commenting' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
