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
<div class="cf-reports-container">
    <h1 class="cf-title"><?php esc_html_e( 'Track Collaboration Across All Your Content', 'content-collaboration-inline-commenting' ); ?></h1>
    <p class="cf-desc">
        <?php esc_html_e( 'This report shows activity on each post: total comments, replies, resolutions, recent activity, and collaborators.', 'content-collaboration-inline-commenting' ); ?>
    </p>
    <div class="cf-info-box">
        <span class="cf-info-text"><strong><?php esc_html_e( 'Get started with these steps', 'content-collaboration-inline-commenting' ); ?>:</strong></span>
        <div class="cf-action-row">
        <a href="<?php echo esc_url( $cf_noresults_redirect_link ); ?>" class="cf-add-btn">
            <span class="cf-btn-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20.2" height="19.418" viewBox="0 0 20.2 19.418"><g transform="translate(0.1 0.1)"><path d="M179.06,187.388a.963.963,0,0,1,.963.963v11.525a.963.963,0,0,1-.963.963H166.786l-3.872,1.927V188.352a.963.963,0,0,1,.963-.963H179.06m0-1.445H163.878a2.411,2.411,0,0,0-2.409,2.409V205.1l2.089-1.039,3.568-1.776H179.06a2.411,2.411,0,0,0,2.409-2.409V188.352a2.411,2.411,0,0,0-2.409-2.409Z" transform="translate(-161.469 -185.943)" fill="#4b1bce"/><path d="M163.878,185.843H179.06a2.511,2.511,0,0,1,2.509,2.509v11.525a2.511,2.511,0,0,1-2.509,2.509H167.15l-5.781,2.876v-16.91A2.511,2.511,0,0,1,163.878,185.843Zm15.183,16.342a2.311,2.311,0,0,0,2.309-2.309V188.352a2.311,2.311,0,0,0-2.309-2.309H163.878a2.311,2.311,0,0,0-2.309,2.309v16.587l5.534-2.754Zm-15.183-14.9H179.06a1.065,1.065,0,0,1,1.063,1.063v11.525a1.065,1.065,0,0,1-1.063,1.063H166.81l-4,1.988V188.352A1.065,1.065,0,0,1,163.878,187.288ZM179.06,200.74a.864.864,0,0,0,.863-.863V188.352a.864.864,0,0,0-.863-.863H163.878a.864.864,0,0,0-.863.863v14.254l3.749-1.865Z" transform="translate(-161.469 -185.943)" fill="#4b1bce"/><g transform="translate(5.905 4.076)"><rect width="1.445" height="8.189" rx="0.723" transform="translate(3.372 0)" fill="#4b1bce"/><path d="M.723-.1a.824.824,0,0,1,.823.823V7.467a.823.823,0,1,1-1.645,0V.723A.824.824,0,0,1,.723-.1Zm0,8.189a.623.623,0,0,0,.623-.623V.723A.623.623,0,1,0,.1.723V7.467A.623.623,0,0,0,.723,8.089Z" transform="translate(3.372 0)" fill="#4b1bce"/><rect width="8.189" height="1.445" rx="0.723" transform="translate(0 3.372)" fill="#4b1bce"/><path d="M.723-.1H7.467a.823.823,0,1,1,0,1.645H.723A.823.823,0,1,1,.723-.1ZM7.467,1.345A.623.623,0,1,0,7.467.1H.723a.623.623,0,1,0,0,1.245Z" transform="translate(0 3.372)" fill="#4b1bce"/></g></g></svg>
            </span>
            <?php esc_html_e( 'Add First Inline Comment', 'content-collaboration-inline-commenting' ); ?>
        </a>
        </div>
        <span class="cf-info-text-report"><?php esc_html_e( 'Only takes 10 seconds to see your first report', 'content-collaboration-inline-commenting' ); ?>.</span>
    </div>
    <h3><?php esc_html_e( 'Preview of sample reports', 'content-collaboration-inline-commenting' ); ?></h3>
    <div class="cf-no-results__preview">
        <img src="<?php echo esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/reports-bg-blur.webp' ); ?>" alt="preview dashboard"/>
    </div>
</div>