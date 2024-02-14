
<div class="container" id="wizard-main">
	<div class="row">
		<div class="col-md-12">
			<div class="wizard">
				<form role="form">
					<div class="tab-content">
						<div class="tab-pane active" role="tabpanel" id="step1">
							<div class="bs-calltoaction-wrap bs-calltoaction-spacewrap">
								<div class="cf-tab-steps">
									<ul class="cf-tab-steps-bulletlists">
										<li class="cf-tab-steps-list cf-tab-step-list-one"><span class="cf-tab-steps-list-number cf-tab-step-list-number-one">1</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-two"><span class="cf-tab-steps-list-number cf-tab-step-list-number-two">2</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-three"><span class="cf-tab-steps-list-number cf-tab-step-list-number-three">3</span></li>
									</ul>
								</div>
								<div class="bs-calltoaction bs-calltoaction-primary">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<img class="bs-calltoaction-logo" src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/boarding-multicollablogo.svg' ); ?>"/>
											<div class="oauth_token_section">
												<p><?php echo esc_html__( 'Thank you for choosing Multicollab.', 'content-collaboration-inline-commenting' ); ?></p>
											</div>
											<h3 class="cta-title"><?php echo esc_html__( 'Start Collaboration in WordPress', 'content-collaboration-inline-commenting' ); ?></h3>
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
								<div class="cf-tab-steps">
									<ul class="cf-tab-steps-bulletlists">
										<li class="cf-tab-steps-list cf-tab-step-list-one"><span class="cf-tab-steps-list-number cf-tab-step-list-number-one">1</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-two"><span class="cf-tab-steps-list-number cf-tab-step-list-number-two">2</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-three"><span class="cf-tab-steps-list-number cf-tab-step-list-number-three">3</span></li>
									</ul>
								</div>
								<div class="bs-calltoaction bs-calltoaction-info">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<h2 class="cta-title">❤️ <?php echo esc_html__( 'Help us build a better Multicollab!', 'content-collaboration-inline-commenting' ); ?></h2>
											<div class="cta-desc">
												<p>Enhance Multicollab with us! Share non-sensitive data for better features and fixes. No personal data is tracked or stored.</p>
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
						<div class="tab-pane active" role="tabpanel" id="step3" style="display: none;">
							<div class="bs-calltoaction-wrap">
								<div class="cf-tab-steps">
									<ul class="cf-tab-steps-bulletlists">
										<li class="cf-tab-steps-list cf-tab-step-list-one"><span class="cf-tab-steps-list-number cf-tab-step-list-number-one">1</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-two"><span class="cf-tab-steps-list-number cf-tab-step-list-number-two">2</span></li>
										<li class="cf-tab-steps-list cf-tab-step-list-three"><span class="cf-tab-steps-list-number cf-tab-step-list-number-three">3</span></li>
									</ul>
								</div>
								<div class="bs-calltoaction bs-calltoaction-primary">
									<div class="row">
										<div class="col-md-12 cta-contents">
											<div class="oauth_token_section">
											<p class="step3-cta-contents-para"><?php echo esc_html__( 'Getting Started with Multicollab.', 'content-collaboration-inline-commenting' ); ?></p>
											</div>
										</div>
										<ul class="list-inline">
											<li><button type="button" id="wizard_language_select" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'content-collaboration-inline-commenting' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button></li>
										</ul>
									</div>
								</div>

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
								<input type="hidden" class="cf_country_name" value="<?php esc_attr_e( $country_name, 'content-collaboration-inline-commenting' ); ?>">
								<input type="hidden" class="cf_browser_name_version" value="">
								
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		</div>
   </div>
</div>
