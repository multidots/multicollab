<?php
/**
 * Comments loop for Email Templates.
 */
?>
<ul class="comment-list">
	<?php
	foreach ( $this->list_of_comments as $cf_comment ) {

		if ( isset( $cf_comment['status'] ) && 'permanent_draft' !== $cf_comment['status'] && 'draft' !== $cf_comment['status'] ) {
			$user_info      = get_userdata( $cf_comment['userData'] );
			$user_role      = implode( ', ', $user_info->roles );
			$username       = $user_info->display_name;
			$this->users_emails[] = $user_info->user_email;
			$profile_url    = get_avatar_url( $user_info->user_email );
			?>
            <li>
                <div class="comment-box-wrap">
                    <div class="avatar"><img src="<?php echo esc_url( $profile_url ) ?>" alt="avatar"/></div>
                    <div class="comment-details">
                        <div class="commenter-name-role">
                            <h3 class="commenter-name"><?php echo esc_html( $username ) ?></h3>
                            <span class="commenter-role"><?php echo esc_html( ucwords( $user_role ) ); ?></span>
                        </div>
                        <div class="comment"><?php echo wp_kses( $cf_comment['thread'], wp_kses_allowed_html( 'post' ) ); ?></div>
                    </div>
                </div>
            </li>
		<?php }
    }
	?>
</ul>
