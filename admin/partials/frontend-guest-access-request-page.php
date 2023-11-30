<?php 

$request_post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
$token =  filter_input( INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS ); 

if(!$request_post_id || !$token){ // Check if token or post id are not present in the link.
    wp_safe_redirect(home_url('/404-page/'));
    exit();
}

?>
<div class="cf_request_access_content">
    <h1 class="cf_request_access_heading"><?php esc_html_e( 'You need access', 'content-collaboration-inline-commenting' ); ?></h1>
    <p class="cf_request_access_desc"><?php esc_html_e( 'Request access to see the post.', 'content-collaboration-inline-commenting' ); ?></p>
    <form class="cf_request_access_form" action="<?php echo esc_url( admin_url( 'admin-post.php')); ?>" method="post" id="cf_requestAccessForm">
        <?php wp_nonce_field('request_access_form_action', 'request_access_nonce'); ?>
        <input type="hidden" name="action" value="request_access_form_action">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($request_post_id); ?>">
        <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
        <label for="email"><?php esc_html_e( 'Email Id', 'content-collaboration-inline-commenting' ); ?></label>
        <input type="email" name="email" id="cf_request_access_email" placeholder="Enter Email Id" required>
        <span class="cf_access_validation" style="display:none"><?php esc_html_e( 'Invalid Email Id.', 'content-collaboration-inline-commenting' ); ?></span>
        <span class="cf_access_authorization_status"></span>
        <input type="submit" class="cf_request_access_submit" value="Request Access">
    </form>
</div>