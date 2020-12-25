<?php
/**
 * Gutenberg Commenting Feature Settings Page.
 */

// Get settings.
$cf_admin_notif = get_option( 'cf_admin_notif' );
$activated      = filter_input( INPUT_GET, "activated", FILTER_SANITIZE_STRING );
?>
<div class="cf-plugin-settings">
    <h2>Editorial Comments Settings</h2>
    <form id="cf-settings-form" method="post">
        <div class="cf-outer">
            <div class="cf-left">
                <div class="cf-tabs">
                    <span data-id="cf-dashboard">Dashboard</span>
                    <span data-id="cf-settings">Settings</span>
                </div>
                <div class="cf-tabs-content">
                    <div id="cf-dashboard" class="cf-tab-inner">
						<?php if ( $activated ) : ?>
                            <h2 id="cf-greetings">Thank you for installing Google Doc-Style Editorial Commenting Plugin.</h2>
						<?php endif; ?>
                        <div id="cf-video">

                        </div>
                        <div id="cf-links">
                            <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/?demo=start" class="cf-link" target="_blank" id="cf-demo">Live Demo</a>
                            <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/" class="cf-link" target="_blank" id="cf-demo">Learn More About Plugin</a>
                        </div>
                    </div>
                    <div id="cf-settings" class="cf-tab-inner">
                        <h2>Notification Setting</h2>
                        <div id="cf-notice">
                            <div class="cf-notice notice notice-success">
                                <p><?php _e( 'Settings Saved!', 'content-collaboration-inline-commenting' ); ?></p>
                            </div>
                        </div>
                        <table class="cf-settings-table">
                            <tr>
                                <th>
                                    <input type="checkbox" name="cf_admin_notif" <?php selected( '1', $cf_admin_notif ); ?> value="<?php echo esc_attr( $cf_admin_notif ); ?>" class="regular-text"/>
                                </th>
                                <td>
                                    <label for="cf_admin_notif">All types of new comments send an email notification to an administrator. If you do not mention "administrator" in the comment still get an email notification</label>
                                </td>
                            </tr>
                        </table>
						<?php submit_button( "Save Changes" ); ?>
                    </div>
                </div>
            </div>
            <div class="cf-right">
                <div id="cf-developer" class="cf-card">
                    <a href="https://www.multidots.com/" target="_blank">
                        <h3>Plugin Developer</h3>
                        <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/images/mdinc-logo.svg' ); ?>"/>
                        <span id="cf-dev-line">WordPress Development Experts & WordPress VIP Partner Agency</span>
                    </a>
                </div>
                <div id="cf-contact" class="cf-card">
                    <h3>Contact Support</h3>
                    <span id="cf-contact-line">For premium support and help with customizing this plugin for your business needs.</span>
                    <a href="https://www.multidots.com/google-doc-style-editorial-commenting-for-wordpress/#mdinc-contact-form-1" target="_blank">Contact Us</a>
                </div>
            </div>
        </div>
    </form>
</div>
