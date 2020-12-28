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
        <h1>Editorial Comments Settings</h1>
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
                            <?php if ( $activated ) : ?>
                                <h2 id="cf-greetings">Thank you for installing Google Doc-Style Editorial Commenting Plugin.</h2>
                            <?php endif; ?>
                            <div id="cf-video">
                                <iframe width="970" height="545.63" src="https://www.youtube-nocookie.com/embed/rDdgh_u8oVQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            <div id="cf-video-links">
                                <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/?demo=start" class="cf-link button button-primary" target="_blank" id="cf-demo">Live Demo</a>
                                <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/" class="cf-link button button-primary" target="_blank" id="cf-demo">Learn More About Plugin</a>
                            </div>
                        </div>
                        <div id="cf-settings" class="cf-tab-inner">
                            <h2>Notification Setting</h2>
                            <div id="cf-notice">
                                <div class="cf-success notice notice-success" style="display: none">
                                    <p><?php _e( 'Settings saved!', 'content-collaboration-inline-commenting' ); ?></p>
                                </div>
                            </div>
                            <div class="cf-notification-settings">
                                <input type="checkbox" name="cf_admin_notif" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : '' ?> value="1" class="regular-text"/>
                                <label for="cf_admin_notif">All types of new comments send an email notification to an administrator. If you do not mention "administrator" in the comment still get an email notification</label>
                            </div>
                            <?php submit_button( "Save Changes" ); ?>
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
                    </div>
                    <!-- <div class="cf-card-footer">
                        
                    </div> -->
                </div>
                <div id="cf-contact" class="cf-card">
                    <div class="cf-card-header">
                        <h3>Contact Support</h3>
                    </div>
                    <div class="cf-card-body">
                        <p>For premium support and help with customizing this plugin for your business needs.</p>
                        <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/#mdinc-contact-form-1" target="_blank" class="button button-primary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
