<?php
/**
 * Gutenberg Commenting Feature Settings Page.
 */

// Get settings.
$activated      = filter_input(INPUT_GET, "activated", FILTER_SANITIZE_STRING);
$cf_admin_notif = get_option('cf_admin_notif');
$cf_permissions= get_option('cf_permissions');
if(!empty($cf_permissions)){
    delete_option( 'cf_permissions' );
}
?>
<div class="cf-plugin-settings">
    <div class="cf-plugin-header">
        <div class="cf-plugin-logo">
            <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/commenting-logo.svg' ); ?>"/>
            <h1><?php esc_html_e( 'Multicollab', 'content-collaboration-inline-commenting' ); ?></h1>
        </div>
        <div class="cf-plugin-version">
            <span>Version 2.0.4</span>
        </div>
    </div>
    <form id="cf-settings-form" method="post">
        <div class="cf-outer">
            <div class="cf-left cf-pricing-dashboard">
                <div class="cf-tabs-main">
                    <ul class="cf-tabs">
                        <li class="cf-tab-active"><a href="javascript:void(0)" class="cf-tab-item" data-id="cf-dashboard"><?php esc_html_e('Dashboard', 'content-collaboration-inline-commenting') ?></a></li>
                        
                    </ul>
                    <div class="cf-tabs-content">
                        <div id="cf-dashboard" class="cf-tab-inner cf-tab-active">
                            <div class="cf-content-simple">
                                <?php if ($activated) : ?>
                                    <p class="cf-greetings"><?php esc_html_e('Thanks for installing Google Doc-Style Editorial Commenting Plugin.', 'content-collaboration-inline-commenting') ?></p>
                                <?php endif; ?>
                                <div class="cf-video">
                                    <div class="pricing-grid-section gutenbrg-commtng-template">
                                        <div class="pricing-grid-inner">
                                            <div class="pricing-block-columns">
                                                <div class="pricing-block-column pricing-grid-cols">
                                                    <h4 id="h-pro">Pro</h4>
                                                    <h5 id="h-699">$699</h5>
                                                    <h3 id="h-499-annually"><strong>$499</strong> /Annually</h3>
                                                    <h6 id="h-200-savings">$200 Savings*</h6>
                                                    <div class="pricing-block-buttons is-content-justification-center">
                                                        <div class="pricing-block-button has-custom-font-size pro-btn" style="font-size:24px">
                                                            <a class="pricing-block-button__link" href="#">Upgrade</a></div>
                                                            
                                                    </div>
                                                    <span><a href="https://checkout.freemius.com/mode/dialog/plugin/8961/plan/15024/?trial=paid">or start 14-day free trial</a></span>
                                                    <ul>
                                                        <li><?php esc_html_e( 'Suggestion Mode (Track Changes)', 'content-collaboration-inline-commenting' ); ?></li>
                                                        <li><?php esc_html_e( 'Advanced Dashboard', 'content-collaboration-inline-commenting' ); ?></li>
                                                        <li><img class="wp-image-1631" style="width: NaNpx;" src="https://www.multicollabs.com/wp-content/uploads/sites/5/2021/09/star.svg" alt=""><?php echo esc_html_e( 'Pro Support', 'content-collaboration-inline-commenting' ); ?></li>
                                                        <li><?php  esc_html_e( 'All features of Plus Plan', 'content-collaboration-inline-commenting' ); ?></li>
                                                    </ul>
                                                </div>
                                                <div class="pricing-block-column pricing-grid-cols pricing-mid-grid-cols">
                                                    <h4 id="h-plus">Plus</h4>
                                                    <h5 id="h-349">$349</h5>
                                                    <h3 id="h-199-annually"><strong>$199</strong> /Annually</h3>
                                                    <h6 id="h-150-savings">$150 Savings*</h6>
                                                    <div class="pricing-block-buttons is-content-justification-center" style="margin-top: 60px;">
                                                        <div class="pricing-block-button plus-btn">
                                                            <a class="pricing-block-button__link" href="#">Upgrade</a>
                                                        </div>
                                                        
                                                    </div>
                                                    <span><a href="https://checkout.freemius.com/mode/dialog/plugin/8961/plan/15023/?trial=paid">or start 14-day free trial</a></span>
                                                    <ul>
                                                        <li><?php  esc_html_e( 'Comment on any text and media', 'content-collaboration-inline-commenting' ); ?></li>
                                                        <li><?php  esc_html_e( 'Email Notification', 'content-collaboration-inline-commenting' ); ?></li>
                                                        <li><img class="wp-image-1631" style="width: NaNpx;" src="https://www.multicollabs.com/wp-content/uploads/sites/5/2021/09/star.svg" alt="">Plus Support</li>
                                                        <li><?php  esc_html_e( 'All features of Basic Plan', 'content-collaboration-inline-commenting' ); ?></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="cf-contact" class="cf-card">
                                        <div class="cf-card-header">
                                            <h3><?php esc_html_e('Technical Help ', 'content-collaboration-inline-commenting'); ?></h3>
                                        </div>
                                        <div class="cf-card-body">
                                            <p><?php esc_html_e('Having trouble? Check out our help documentation', 'content-collaboration-inline-commenting') ?></p>
                                            <a href="<?php echo esc_url('https://docs.multicollabs.com/'); ?>" target="_blank" class="cf-button button button-primary"><?php esc_html_e('Help Documentation', 'content-collaboration-inline-commenting'); ?></a>
                                        </div>
                                    </div>
                                    
                                </div>
                                <!-- No-risk-money-back -->
                                <div class="no-risk-money-back-section">
                                    <div class="no-risk-section-inner"> 
                                        <div class="no-risk-block-column">
                                            <figure class="wp-block-image size-large">
                                             <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/no-risk-badge.svg' ); ?>"/></figure>
                                        </div>
                                        <div class="no-risk-block-column">
                                            <h2 id="h-14-days-100-no-risk-money-back-guarantee"><?php esc_html_e('14 Days – 100% No-Risk Money Back Guarantee!', 'content-collaboration-inline-commenting') ?></h2>
                                            <p><?php esc_html_e('You are fully protected by our 100% Money Back Guarantee. If you don’t like our products over the next 14 days, then we will gladly refund your money. No questions asked!', 'content-collaboration-inline-commenting') ?></p>
                                        </div> 
                                    </div>
                                </div>
                                
                                <!-- Pricing-testimonial -->
                                <div class="pricing-testimonial-section">
                                    <div class="pricing-testimonial-inner">
                                        <div class="pricing-testimonial-columns">
                                            <div class="pricing-testimonial-column" >
                                                <h2 class="pricing-testimonial-title" id="h-what-our-users-are-saying">What our <br>Users are <br>saying </h2>
                                            </div> 
                                            <div class="pricing-testimonial-column" style="flex-basis:65%"> 
                                                <div class="pricing-testi-slider">
                                                    <div class="pricing-slider">
                                                        <ul>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                        </ul>
                                                        <h4><?php esc_html_e('A great addition for author/editor collaboration ', 'content-collaboration-inline-commenting'); ?></h4>
                                                        <p><?php esc_html_e('This an easy-to-use plugin—the best one I’m aware of—for those who have an editorial process. My wife is a blogger and I’m her editor. We’ve only begun taking advantage of it but I wanted to offer a positive shout out. ', 'content-collaboration-inline-commenting'); ?></p>
                                                        <div class="pricing-testi-meta">
                                                            <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/Mario.png' ); ?>"/> 
                                                            <div class="pricing-testi-content">
                                                                <h5><?php esc_html_e('Mario T. Lanza', 'content-collaboration-inline-commenting'); ?></h5>
                                                                <p><?php esc_html_e('Sr. Web Developer', 'content-collaboration-inline-commenting'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="pricing-slider">
                                                        <ul>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                        </ul>
                                                        <h4><?php esc_html_e('Awesome plugin', 'content-collaboration-inline-commenting'); ?></h4>
                                                        <p><?php esc_html_e('This is exactly what I was looking for. This plugin works flawlessly. Great job.', 'content-collaboration-inline-commenting'); ?></p>
                                                        <div class="pricing-testi-meta">
                                                            <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/ivan.jpeg' ); ?>"/> 
                                                            <div class="pricing-testi-content">
                                                                <h5><?php esc_html_e('Ivan Ružević', 'content-collaboration-inline-commenting'); ?></h5>
                                                                <p><?php esc_html_e('WordPress Team Lead', 'content-collaboration-inline-commenting'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="pricing-slider">
                                                        <ul>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                            <li><i class="fas fa-star"></i></li>
                                                        </ul>
                                                        <h4><?php esc_html_e('Awesome plug-in', 'content-collaboration-inline-commenting'); ?></h4>
                                                        <p><?php esc_html_e('The plug-in an awesome. It works fantastic. It is one of the great features of our platform.The plug-in an awesome. It works fantastic. It is one of the great features of our platform.', 'content-collaboration-inline-commenting'); ?></p>
                                                        <div class="pricing-testi-meta">
                                                            <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/Mondale.jpeg' ); ?>"/> 
                                                            <div class="pricing-testi-content">
                                                                <h5><?php esc_html_e('Charudatta Mondale', 'content-collaboration-inline-commenting'); ?></h5>
                                                                <p><?php esc_html_e('Co-Founder at QuickDraft LLP', 'content-collaboration-inline-commenting'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pricing-testimonial -->
                            </div>
                        </div>
                        <div id="cf-settings" class="cf-tab-inner">
                            <div class="cf-content-box">
                                <div class="cf-cnt-box-header">
                                    <h3><?php esc_html_e('Notification Setting', 'content-collaboration-inline-commenting'); ?></h3>
                                </div>
                                <div class="cf-cnt-box-body">
                                    <div id="cf-notice">
                                        <div class="cf-success notice notice-success" style="display: none">
                                            <p><?php esc_html_e('Settings saved!', 'content-collaboration-inline-commenting'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="cf-notification-settings">
                                        <div class="cf-check-wrap">
                                            <input type="checkbox" name="cf_admin_notif" class="cf-checkbox" id="cf_admin_notif" <?php echo '1' === $cf_admin_notif ? 'checked' : '' ?> value="1" class="regular-text"/>
                                            <span class="cf-check"></span>
                                        </div>
                                        <label for="cf_admin_notif"><?php esc_html_e('Notify site admin', 'content-collaboration-inline-commenting'); ?> (<?php echo esc_html(get_option('admin_email')) ?>) <?php esc_html_e('for all new comments even if not mentioned.', 'content-collaboration-inline-commenting'); ?></label>
                                    </div>
                                    <?php submit_button(__('Save Changes', 'content-collaboration-inline-commenting')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="cf-right">
            </div>
        </div>
    </form>
</div>
