<?php
/**
 * Comments loop for Email Templates.
 */
?>
<table class="comment-list" style="width:100%;">
    <?php
    foreach ( $this->list_of_comments as $cf_comment ) { // phpcs:ignore

        if ( isset( $cf_comment['status'] ) && 'publish' === $cf_comment['status'] ) {
            $user_info      = get_userdata( $cf_comment['userData'] );
            $user_role      = implode( ', ', $user_info->roles );
            $username       = $user_info->display_name;
            $this->users_emails[] = $user_info->user_email;
            $profile_url    = get_avatar_url( $user_info->user_email );
            ?>
            <tr>
                <td style="padding-bottom:20px">
                    <table class="comment-box-wrap" style="width:100%;font-family:Arial,serif;font-size:14px;color:#4C5056;">
                        <tr valign="top">
                            <td class="avatar" style="width:40px;padding-right:10px;">
                                <img src="<?php echo esc_url_raw( $profile_url ) ?>" alt="avatar" style="max-width:100%;border-radius:50%;" />
                            </td>
                            <td class="comment-details">
                                <table class="commenter-name-role" valign="middle" style="width:100%;">
                                    <tr>
                                        <th align="left">
                                            <span class="commenter-name" style="font-size:18px;font-family:Roboto,Arial,sans-serif;padding-right:5px;color:#141414;font-weight:600;"><?php echo esc_html( $username ) ?></span>
                                            <span class="commenter-role" style="font-weight:400;"><?php echo esc_html( ucwords( $user_role ) ); ?></span>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td align="left" class="comment"><?php echo wp_kses( $cf_comment['thread'], wp_kses_allowed_html( 'post' ) ); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php }
    }
    ?>
</table>
