<?php
$args = array(
    'title'       => 'Getting Started with Multicollab',
    'post_type'   => 'post',
    'post_status' => 'draft',
);
$posts_array = get_posts( $args );
$cf_noresults_redirect_link = '';
if ( ! empty( $posts_array ) && isset( $posts_array[0]->ID ) ) {
    $cf_noresults_redirect_link = get_edit_post_link( $posts_array[0]->ID );
}
?>
<div class="cf-no-results">
    <div class="cf-no-results__welcome">
        <div class="cf-no-results__emoji">🙌</div>
        <h1><?php esc_html_e( 'Welcome to Multicollab!', 'content-collaboration-inline-commenting' ); ?></h1>
        <p class="cf-no-results__subtitle"><?php esc_html_e( "You haven't collaborated yet.", 'content-collaboration-inline-commenting' ); ?></p>
        <p><?php esc_html_e( 'Start by adding your first inline comment', 'content-collaboration-inline-commenting' ); ?>.</p>
        <a href="<?php echo esc_url( $cf_noresults_redirect_link ); ?>" class="cf-no-results__collab-btn"><?php esc_html_e( 'Start Collaborating ', 'content-collaboration-inline-commenting' ); ?>→</a>
        <p class="cf-no-results__inline-comment"><?php esc_html_e( 'Add Inline Comment to Any Page/Post', 'content-collaboration-inline-commenting' ); ?>.</p>
    </div>
    <div class="cf-no-results__more-section">
        <h2>👇 <?php esc_html_e( 'Next Steps You Can Take', 'content-collaboration-inline-commenting' ); ?></h2>
        <div class="cf-no-results__options-grid">
            <div class="cf-no-results__option">
                <span class="cf-no-results__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20.2" height="19.418" viewBox="0 0 20.2 19.418"><g transform="translate(0.1 0.1)"><path d="M179.06,187.388a.963.963,0,0,1,.963.963v11.525a.963.963,0,0,1-.963.963H166.786l-3.872,1.927V188.352a.963.963,0,0,1,.963-.963H179.06m0-1.445H163.878a2.411,2.411,0,0,0-2.409,2.409V205.1l2.089-1.039,3.568-1.776H179.06a2.411,2.411,0,0,0,2.409-2.409V188.352a2.411,2.411,0,0,0-2.409-2.409Z" transform="translate(-161.469 -185.943)" fill="#4b1bce"/><path d="M163.878,185.843H179.06a2.511,2.511,0,0,1,2.509,2.509v11.525a2.511,2.511,0,0,1-2.509,2.509H167.15l-5.781,2.876v-16.91A2.511,2.511,0,0,1,163.878,185.843Zm15.183,16.342a2.311,2.311,0,0,0,2.309-2.309V188.352a2.311,2.311,0,0,0-2.309-2.309H163.878a2.311,2.311,0,0,0-2.309,2.309v16.587l5.534-2.754Zm-15.183-14.9H179.06a1.065,1.065,0,0,1,1.063,1.063v11.525a1.065,1.065,0,0,1-1.063,1.063H166.81l-4,1.988V188.352A1.065,1.065,0,0,1,163.878,187.288ZM179.06,200.74a.864.864,0,0,0,.863-.863V188.352a.864.864,0,0,0-.863-.863H163.878a.864.864,0,0,0-.863.863v14.254l3.749-1.865Z" transform="translate(-161.469 -185.943)" fill="#4b1bce"/><g transform="translate(5.905 4.076)"><rect width="1.445" height="8.189" rx="0.723" transform="translate(3.372 0)" fill="#4b1bce"/><path d="M.723-.1a.824.824,0,0,1,.823.823V7.467a.823.823,0,1,1-1.645,0V.723A.824.824,0,0,1,.723-.1Zm0,8.189a.623.623,0,0,0,.623-.623V.723A.623.623,0,1,0,.1.723V7.467A.623.623,0,0,0,.723,8.089Z" transform="translate(3.372 0)" fill="#4b1bce"/><rect width="8.189" height="1.445" rx="0.723" transform="translate(0 3.372)" fill="#4b1bce"/><path d="M.723-.1H7.467a.823.823,0,1,1,0,1.645H.723A.823.823,0,1,1,.723-.1ZM7.467,1.345A.623.623,0,1,0,7.467.1H.723a.623.623,0,1,0,0,1.245Z" transform="translate(0 3.372)" fill="#4b1bce"/></g></g></svg>
                </span>
                <?php esc_html_e( 'Add comment to any post', 'content-collaboration-inline-commenting' ); ?>
            </div>
            <div class="cf-no-results__option">
                <span class="cf-no-results__icon cf-no-results__check">
                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 31" width="20" height="20"><g id="Group 1272"><g id="Group 1269"><path id="Path 10442" class="s0" fill="#4b1bce" d="m4.3 30.6c-2.4 0-4.3-1.9-4.3-4.3v-19.5c0-2.4 1.9-4.3 4.3-4.3h10.7v2.3h-10.7c-1.1 0-2 0.9-2 2v19.5c0 1.1 0.9 2 2 2h19.6c1 0 1.9-0.9 1.9-2v-10.6h2.3v10.6c0 2.4-1.9 4.3-4.2 4.3z"></path><g id="Group 1268"><path id="Path 10443" fill-rule="evenodd" class="s0" fill="#4b1bce" d="m6.9 23.7v-6l17.1-17 6 6-17.1 17zm2.3-5v2.8h2.8l11-11-2.8-2.8zm12.6-12.7l2.8 2.9 2.2-2.3-2.8-2.8z"></path></g></g><path id="Path 10445" class="s0" fill="#4b1bce" d="m17 21.7h5v2h-7z"></path></g></svg>
                </span>
                <?php esc_html_e( 'Try suggestion mode', 'content-collaboration-inline-commenting' ); ?>
            </div>
            <div class="cf-no-results__option">
                <span class="cf-no-results__icon cf-no-results__person">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20.5" viewBox="0 0 18.433 20.5"><g transform="translate(-182.9 -724.405)"><g transform="translate(124.479 719.847)"><g transform="translate(60.634 6.359)"><path d="M66.771,16.39a4.366,4.366,0,0,1,0-8.731,4.3,4.3,0,0,1,.552.038l1.577.2L67.818,9.062a3.274,3.274,0,0,0,1.111,5.246l1.459.624-1.357.823A4.349,4.349,0,0,1,66.771,16.39ZM64.979,9.964a2.731,2.731,0,0,0,.989,4.674l.794.245-.5-.667a4.917,4.917,0,0,1-.843-3.988l.18-.81Z" transform="translate(-62.406 -7.659)" fill="#4b1bce"/><path d="M66.771,16.49a4.466,4.466,0,0,1,0-8.931,4.389,4.389,0,0,1,.565.039l1.77.226L67.891,9.13a3.174,3.174,0,0,0,1.077,5.086l1.638.7-1.523.924A4.449,4.449,0,0,1,66.771,16.49Zm0-8.731a4.266,4.266,0,1,0,2.207,7.911l1.191-.722-1.28-.548a3.374,3.374,0,0,1-1.145-5.406l.95-1.021L67.311,7.8A4.2,4.2,0,0,0,66.771,7.759Zm.253,7.309-1.086-.335a2.831,2.831,0,0,1-1.025-4.845l.854-.748-.246,1.108a4.817,4.817,0,0,0,.826,3.907ZM65.439,9.694l-.395.346a2.631,2.631,0,0,0,.952,4.5l.5.155-.313-.422a5.017,5.017,0,0,1-.86-4.069Z" transform="translate(-62.406 -7.659)" fill="#fff"/></g><g transform="translate(58.521 15.187)"><path d="M63.884,32.064a8.688,8.688,0,0,1-4.546-2.008,2.326,2.326,0,0,1-.817-1.77,4.409,4.409,0,0,1,4.414-4.395h4.13a4.47,4.47,0,0,1,.921.1l4.883,1.026-.061.589H66.062a3.328,3.328,0,0,0-3.335,3.313.928.928,0,0,0,.326.707,8.349,8.349,0,0,0,1.334.921L66.628,31.8l-.195.589ZM61.46,25.944l0,0a2.747,2.747,0,0,0-1.3,2.34.692.692,0,0,0,.244.528c.085.073.173.145.262.213l.427.329.011-.54a4.916,4.916,0,0,1,.738-2.5s.244-.475.4-.736A2.859,2.859,0,0,0,61.46,25.944Z" transform="translate(-58.521 -23.891)" fill="#4b1bce"/><path d="M66.5,32.5l-2.632-.337a8.788,8.788,0,0,1-4.6-2.031,2.426,2.426,0,0,1-.852-1.847,4.509,4.509,0,0,1,4.514-4.495h4.13a4.57,4.57,0,0,1,.942.1l4.971,1.044L72.9,25.7H66.062a3.228,3.228,0,0,0-3.235,3.213.828.828,0,0,0,.291.631,8.248,8.248,0,0,0,1.318.91l2.313,1.3Zm-3.568-8.51a4.309,4.309,0,0,0-4.314,4.295A2.226,2.226,0,0,0,59.4,29.98,8.589,8.589,0,0,0,63.9,31.965l2.468.316.143-.432-2.169-1.219a8.449,8.449,0,0,1-1.35-.932,1.028,1.028,0,0,1-.361-.783A3.428,3.428,0,0,1,66.062,25.5h6.655l.042-.409-4.794-1.007a4.37,4.37,0,0,0-.9-.094Zm-1.753,5.565-.584-.45c-.084-.065-.172-.136-.266-.216a.793.793,0,0,1-.279-.6A2.848,2.848,0,0,1,61.4,25.861a2.934,2.934,0,0,1,.808-.376l.242-.065-.13.214c-.155.254-.4.725-.4.73l0,.007a4.818,4.818,0,0,0-.722,2.447Zm.836-3.794a2.9,2.9,0,0,0-.5.264l-.005,0a2.65,2.65,0,0,0-1.259,2.256.593.593,0,0,0,.209.451c.092.078.176.147.258.21l.27.208L61,28.814a5.018,5.018,0,0,1,.75-2.545C61.765,26.234,61.892,25.989,62.018,25.762Z" transform="translate(-58.521 -23.891)" fill="#fff"/></g><g transform="translate(63.806 4.808)"><path d="M73.42,15.175A5.183,5.183,0,1,1,78.6,9.991,5.189,5.189,0,0,1,73.42,15.175Zm0-8.191a3.008,3.008,0,1,0,3.008,3.008A3.011,3.011,0,0,0,73.42,6.984Z" transform="translate(-68.237 -4.808)" fill="#4b1bce"/><path d="M73.42,15.425a5.433,5.433,0,1,1,5.434-5.433A5.439,5.439,0,0,1,73.42,15.425Zm0-10.367a4.933,4.933,0,1,0,4.934,4.933A4.939,4.939,0,0,0,73.42,5.058Zm0,8.191a3.258,3.258,0,1,1,3.258-3.258A3.261,3.261,0,0,1,73.42,13.249Zm0-6.016a2.758,2.758,0,1,0,2.758,2.758A2.761,2.761,0,0,0,73.42,7.234Z" transform="translate(-68.237 -4.808)" fill="#fff"/></g><g transform="translate(61.368 14.994)"><path d="M71.373,33.35a10.217,10.217,0,0,1-6.624-2.442,2.831,2.831,0,0,1-.994-2.154,5.234,5.234,0,0,1,5.238-5.217h4.766a5.23,5.23,0,0,1,5.232,5.217A2.824,2.824,0,0,1,78,30.908,10.207,10.207,0,0,1,71.373,33.35Zm-2.38-7.638a3.056,3.056,0,0,0-3.063,3.042.653.653,0,0,0,.231.5,8.027,8.027,0,0,0,10.423,0,.656.656,0,0,0,.231-.5,3.053,3.053,0,0,0-3.056-3.042Z" transform="translate(-63.755 -23.536)" fill="#4b1bce"/><path d="M71.373,33.6a10.469,10.469,0,0,1-6.786-2.5A3.08,3.08,0,0,1,63.5,28.753a5.484,5.484,0,0,1,5.488-5.467h4.766a5.481,5.481,0,0,1,5.482,5.467A3.073,3.073,0,0,1,78.158,31.1,10.456,10.456,0,0,1,71.373,33.6Zm-2.38-9.814A4.983,4.983,0,0,0,64,28.753a2.581,2.581,0,0,0,.906,1.964,9.955,9.955,0,0,0,12.923,0,2.574,2.574,0,0,0,.907-1.965,4.98,4.98,0,0,0-4.982-4.967Zm2.38,7.638A8.287,8.287,0,0,1,66,29.442a.9.9,0,0,1-.318-.689,3.306,3.306,0,0,1,3.313-3.292h4.766a3.3,3.3,0,0,1,3.306,3.292.906.906,0,0,1-.318.689A8.28,8.28,0,0,1,71.373,31.424Zm-2.38-5.462a2.805,2.805,0,0,0-2.813,2.792.4.4,0,0,0,.142.308,7.777,7.777,0,0,0,10.1,0,.406.406,0,0,0,.143-.309,2.8,2.8,0,0,0-2.806-2.792Z" transform="translate(-63.755 -23.536)" fill="#fff"/></g></g></g></svg>
                </span>
                <?php esc_html_e( 'Invite a teammate', 'content-collaboration-inline-commenting' ); ?>
            </div>
            <div class="cf-no-results__option">
                <span class="cf-no-results__icon cf-no-results__checklist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 28.7"><g id="Group_1" data-name="Group 1" transform="translate(0 0)"><path id="Path_10444" data-name="Path 10444" d="M30,3.6V20.9a3.585,3.585,0,0,1-3.6,3.6H8.5L3.1,27.2,0,28.7V3.6A3.585,3.585,0,0,1,3.6,0H26.4A3.585,3.585,0,0,1,30,3.6ZM3.6,2.2A1.367,1.367,0,0,0,2.2,3.6V25.2L8,22.3H26.4a1.367,1.367,0,0,0,1.4-1.4V3.6a1.367,1.367,0,0,0-1.4-1.4Z" fill="#4b1bce" fill-rule="evenodd"></path><path id="check-svgrepo-com" d="M4,10.5l3.2,3.2L14.4,6.5" transform="translate(5.5 1.793)" fill="none" stroke="#4b1bce" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></g></svg>
                </span>
                <?php esc_html_e( 'Use the editorial checklist', 'content-collaboration-inline-commenting' ); ?>
            </div>
        </div>
        <div class="cf-no-results__demo-guides">
            <a href="#." class="cf-no-results__demo-btn">
                <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#4b1bce">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier"> 
                        <title>multimedia / 10 - multimedia, play icon, circle, button</title> 
                        <g id="Free-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(-821.000000, -378.000000)" id="Group"> <g transform="translate(819.000000, 376.000000)" id="Shape"> <circle stroke="#4b1bce" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="9"> </circle> <path d="M9.99806947,8.42746827 L15.4902873,11.5658784 C15.730046,11.7028834 15.8133443,12.0083107 15.6763394,12.2480695 C15.6320447,12.3255851 15.5678029,12.3898269 15.4902873,12.4341216 L9.99806947,15.5725317 C9.75831075,15.7095367 9.45288341,15.6262384 9.31587843,15.3864796 C9.27270766,15.3109308 9.25,15.2254236 9.25,15.1384102 L9.25,8.86158984 C9.25,8.58544746 9.47385763,8.36158984 9.75,8.36158984 C9.83701347,8.36158984 9.92252062,8.3842975 9.99806947,8.42746827 Z" fill="#4b1bce"> </path> </g> </g> </g> </g></svg><?php esc_html_e( 'Watch quick demo (13 sec)', 'content-collaboration-inline-commenting' ); ?></a>
            <span class="cf-no-results__divider">|</span>
            <a class="cf-no-results__guide-btn" href="https://docs.multicollab.com/article/20-requirements?utm_source=plugin+&utm_medium=setting+page&utm_campaign=help+doc+from+plugin+setting+section" target="_blank"><svg fill="#4b1bce" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13px" height="13px" viewBox="0 0 459.319 459.319" xml:space="preserve">
                <g>
                    <path d="M94.924,366.674h312.874c0.958,0,1.886-0.136,2.778-0.349c0.071,0,0.13,0.012,0.201,0.012
                        c6.679,0,12.105-5.42,12.105-12.104V12.105C422.883,5.423,417.456,0,410.777,0h-2.955H114.284H94.941
                        c-32.22,0-58.428,26.214-58.428,58.425c0,0.432,0.085,0.842,0.127,1.259c-0.042,29.755-0.411,303.166-0.042,339.109
                        c-0.023,0.703-0.109,1.389-0.109,2.099c0,30.973,24.252,56.329,54.757,58.245c0.612,0.094,1.212,0.183,1.847,0.183h317.683
                        c6.679,0,12.105-5.42,12.105-12.105v-45.565c0-6.68-5.427-12.105-12.105-12.105s-12.105,5.426-12.105,12.105v33.461H94.924
                        c-18.395,0-33.411-14.605-34.149-32.817c0.018-0.325,0.077-0.632,0.071-0.963c-0.012-0.532-0.03-1.359-0.042-2.459
                        C61.862,380.948,76.739,366.674,94.924,366.674z M103.178,58.425c0-6.682,5.423-12.105,12.105-12.105s12.105,5.423,12.105,12.105
                        V304.31c0,6.679-5.423,12.105-12.105,12.105s-12.105-5.427-12.105-12.105V58.425z"></path>
                </g>
                </svg><?php esc_html_e( 'Get Started Guide ', 'content-collaboration-inline-commenting' ); ?>→</a>
        </div>
    </div>
    <h3><?php esc_html_e( 'Preview of sample timeline', 'content-collaboration-inline-commenting' ); ?></h3>
    <div class="cf-no-results__preview">
        <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/free-dashboard-bg.webp' ); ?>" alt="preview dashboard"/>
    </div>
</div>


<div class="cf-plugin_modal cf-plugin_upgrademodal cf-dashboard_videomodal" role="dialog" id="cf-dashboard_videomodal">
	<div class="cf-pro-modal-dialog-outer">
		<div class="cf-pro-modal-dialog-wrapper">
			<div class="cf-pro-modal-dialog" role="document">

				<!-- Close Button -->
				<button type="button" class="modal-close-btn" aria-label="Close">&times;</button>

				<div class="cf-pro-modal__body">
					<video width="auto" height="auto" controls poster="https://www.multicollab.com/wp-content/uploads/2024/06/how_multicollab_works_.webp">
						<source src="https://www.multicollab.com/wp-content/uploads/2024/03/Multicollab-Demo_-How-To-Increase-Your-Content-Team-Output-in-WordPress-Using-Collaborative-Editing.mp4" type="video/mp4">
					</video>
				</div>
				
			</div>
		</div>
	</div>
</div>