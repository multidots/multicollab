
<div class="container" id="wizard-main">
	<div class="row">
		<div class="col-md-12">
			<div class="wizard">
				<form role="form">
					<div class="tab-content">
						<div class="tab-pane active" role="tabpanel" id="step1">
							<div class="bs-calltoaction-wrap">
								<div class="bs-calltoaction bs-calltoaction-primary">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<h3 class="cta-title"><?php echo esc_html__( 'Welcome! 🤗', 'content-collaboration-inline-commenting' ); ?></h3>
											<img class="bs-calltoaction-logo" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/boarding-multicollablogo.svg' ); ?>"/>
											<div class="oauth_token_section">
												<p><?php echo esc_html__( 'Start collaborating in WordPress!', 'content-collaboration-inline-commenting' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<ul class="list-inline">
									<li><button type="button" id="wizard_validate_token" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'content-collaboration-inline-commenting' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button></li>
								</ul>
							</div>
						</div>
						<div class="tab-pane" role="tabpanel" id="step2" style="display: none;">
							<div class="bs-calltoaction-wrap">
								<div class="bs-calltoaction bs-calltoaction-info">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<h2 class="cta-title"><?php echo esc_html__( 'Never miss an important update 🔔', 'content-collaboration-inline-commenting' ); ?></h2>
											<div class="cta-desc">
												<p>Opt-in to get email notifications for security & feature updates and to share some basic WordPress environment info. This will help us make the plugin more compatible with your site and better at doing what you need it to. No personal data is tracked or stored.</p>
											</div>
											<div>
												<label class="bs-checkbox"><input class="count_me_in_free" type="checkbox" name="" value="">Yes, count me in! </label>
											</div>
										</div>
									</div>
								</div>
								<ul class="list-inline">
									<li><button type="button" id="wizard_language_select" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'content-collaboration-inline-commenting' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button></li>
								</ul>
							</div>
						</div>
						<div class="tab-pane" role="tabpanel" id="step3" style="display: none;">
							<div class="bs-calltoaction-wrap">
								<div class="bs-calltoaction bs-calltoaction-success">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<h2 class="cta-title"><?php echo esc_html__( 'Quick Tour 🗺️', 'content-collaboration-inline-commenting' ); ?></h2>
											<div class="select_event_categories_tags">
												<?php
													$rest_api_url        = CF_STORE_URL . 'wp-json/cf-onboarding-quick-tour-videos/v2/cf-onboarding-quick-tour-videos?' . wp_rand();
													$free_video_api_call = wp_remote_get( $rest_api_url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
													$free_video_api_data = $free_video_api_call['body'];
													$free_video_api_data = json_decode( $free_video_api_data, true );

												if ( ! empty( $free_video_api_data['free_plugin_video'] ) ) {
													$free_video_data = $free_video_api_data['free_plugin_video'];
													if ( 'media' === $free_video_data['video_option'] ) {
														?>
															<video controls>
																<source src="<?php echo esc_url( $free_video_data['video_url'] ); ?>" type="video/mp4">
															</video>
															<?php
													} else {
														?>
																<iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $free_video_data['video_url'] ); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
															<?php
													}
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<ul class="list-inline">
									<li><button type="button" id="wizard_select_cat_tag" class="btn btn-primary btn-info-full next-step"><?php echo esc_html__( 'Continue', 'content-collaboration-inline-commenting' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button></li>
								</ul>
							</div>
						</div>
						<div class="tab-pane" role="tabpanel" id="step4" style="display: none;">
							<div class="bs-calltoaction-wrap">
								<div class="bs-calltoaction bs-calltoaction-info">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<h2 class="cta-title"><?php echo esc_html__( 'You are all set, almost! 😀', 'content-collaboration-inline-commenting' ); ?></h2>
											<div class="cta-desc">
												<div class="number_of_events last_step_description">
													<p>Get latest updates and tips for collaborative publishing</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<input type="email" placeholder="Enter your email address" class="bs-callaction-input last_step_email_subscription">
								<span class="bs-callaction-list">Be our friend on</span>
								<ul class="bs-social-list">
									<li><a target="_blank" href="https://www.facebook.com/multicollab"><span class="dashicons dashicons-facebook-alt"></span></a></li>
									<li><a target="_blank" href="https://twitter.com/multicollab"><span class="dashicons dashicons-twitter"></span></a></li>
									<li><a target="_blank" href="https://www.youtube.com/@multicollab"><span class="dashicons dashicons-youtube"></span></a></li>
									<li><a target="_blank" href="https://www.linkedin.com/company/multicollab"><span class="dashicons dashicons-linkedin"></span></a></li>
								</ul>
								<?php
													$vis_ip      = get_visitor_ip_address();
													$remote_attr = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_SPECIAL_CHARS );
								if ( 'localhost' === gethostbyaddr( $remote_attr ) ) {
									$vis_ip = '150.129.206.240';
								}
													$get_country_name = wp_remote_get( 'http://ip-api.com/json/' . $vis_ip ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
													$country_name     = '';
								if ( ! empty( $get_country_name ) ) {
									$get_country_name_data = $get_country_name['body'];
									$get_country_name_data = json_decode( $get_country_name_data, true );
								}
													$country_name = '';
								if ( 'success' === $get_country_name_data['status'] ) {
									$country_name = $get_country_name_data['country'];
								}
								?>
								<ul class="list-inline">
									<input type="hidden" class="cf_country_name" value="<?php esc_attr_e( $country_name, 'content-collaboration-inline-commenting' ); ?>">
									<input type="hidden" class="cf_browser_name_version" value="">
									<li><button type="button" id="wizard_sync_button" class="btn btn-primary next-step"><?php echo esc_html__( 'Done', 'content-collaboration-inline-commenting' ); ?></button></li>
								</ul>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		</div>
   </div>
</div>
