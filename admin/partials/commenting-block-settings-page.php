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
        <h1>Google Doc-Style Editorial Commenting for WordPress</h1>
    </div>
    <form id="cf-settings-form" method="post">
        <div class="cf-outer">
            <div class="cf-left">
                <div class="cf-tabs-main">
                    <ul class="cf-tabs">
                        <li class="cf-tab-active"><a href="javascript:void(0)" class="cf-tab-item" data-id="cf-dashboard">Dashboard</a></li>
                        <li><a href="javascript:void(0)" class="cf-tab-item" data-id="cf-settings">Settings</a></li>
                    </ul>
                    <div class="cf-tabs-content">
                        <div id="cf-dashboard" class="cf-tab-inner cf-tab-active">
                            <div class="cf-content-simple">
                                <?php if ( $activated ) : ?>
                                    <p class="cf-greetings">Thanks for installing Google Doc-Style Editorial Commenting Plugin.</p>
                                <?php endif; ?>
                                <p class="cf-greetings">Thanks for installing Google Doc-Style Editorial Commenting Plugin.</p>
                                <div class="cf-video">
                                    <iframe width="970" height="545.63" src="https://www.youtube-nocookie.com/embed/rDdgh_u8oVQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                                <div class="cf-video-links">
                                    <div class="cf-link-wrap">
                                        <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/?demo=start" class="cf-button button button-primary" target="_blank" id="cf-demo">Live Demo</a>
                                    </div>
                                    <div class="cf-link-wrap">
                                        <p><a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/" class="cf-link" target="_blank" id="cf-demo">Learn More</a> About Plugin</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="cf-settings" class="cf-tab-inner">
                            <div class="cf-content-box">
                                <div class="cf-cnt-box-header">
                                    <h3>Notification Setting</h3>
                                </div>
                                <div class="cf-cnt-box-body">
                                    <div id="cf-notice">
                                        <div class="cf-success notice notice-success" style="display: none">
                                            <p><?php _e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="cf-notification-settings">
                                        <div class="cf-check-wrap">
                                            <input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : '' ?> value="1" class="regular-text"/>
                                            <span class="cf-check"></span>
                                        </div>
                                        <label for="cf_admin_notif">All types of new comments send an email notification to an administrator. If you do not mention "administrator" in the comment still get an email notification</label>
                                    </div>
                                    <?php submit_button( "Save Changes" ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cf-right">
                <div id="cf-developer" class="cf-card">
                    <div class="cf-card-header">
                        <h3>Plugin Developer</h3>
                    </div>
                    <div class="cf-card-body">
                        <div class="cf-dev-img">
                            <a href="https://www.multidots.com/" target="_blank">
                                <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/images/mdinc-logo.svg' ); ?>"/>
                            </a>
                        </div>
                        <p>WordPress Development Experts & WordPress VIP Partner Agency</p>
                        <a href="https://www.multidots.com/" target="_blank" class="cf-button button button-primary">Visit Site</a>
                    </div>
                </div>
                <div id="cf-contact" class="cf-card">
                    <div class="cf-card-header">
                        <h3>Contact Support</h3>
                    </div>
                    <div class="cf-card-body">
                        <p>For premium support and help with customizing this plugin for your business needs.</p>
                        <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/#mdinc-contact-form-1" target="_blank" class="cf-button button button-primary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
