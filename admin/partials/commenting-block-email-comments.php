<?php
/**
 * Comments loop for Email Templates.
 */
?>
<ul class="comment-list" style="margin:0 0 20px;padding:0;list-style:none;">
	<?php
	foreach ( $this->list_of_comments as $cf_comment ) {

		if ( isset( $cf_comment['status'] ) && 'publish' === $cf_comment['status'] ) {
			$user_info      = get_userdata( $cf_comment['userData'] );
			$user_role      = implode( ', ', $user_info->roles );
			$username       = $user_info->display_name;
			$this->users_emails[] = $user_info->user_email;
			$profile_url    = get_avatar_url( $user_info->user_email );
			?>
            <li style="margin-bottom:20px;">
                <div class="comment-box-wrap" style="display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;">
                    <div class="avatar" style="width:40px;margin-right:10px;"><img src="<?php echo esc_url( $profile_url ) ?>" alt="avatar" style="max-width:100%;border-radius:50%;" /></div>
                    <div class="comment-details" style="margin-right:0;width:60%;width:calc(100% - 55px);">
                        <div class="commenter-name-role" style="display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;">
                            <h3 class="commenter-name" style="font-size:18px;font-family:Roboto,Arial,sans-serif;margin:0 7px 0 0;color:#141414;font-weight:600;"><?php echo esc_html( $username ) ?></h3>
                            <span class="commenter-role" style="font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;margin-right:10px;"><?php echo esc_html( ucwords( $user_role ) ); ?></span>
                        </div>
                        <div class="comment" style="font-family:Arial,serif;font-size:14px;color:#4C5056;"><?php echo wp_kses( $cf_comment['thread'], wp_kses_allowed_html( 'post' ) ); ?></div>
                    </div>
                </div>
            </li>
		<?php }
    }
	?>
</ul>
