<?php

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}
/**
 * This file contians all global functions for the plugin
 *
 * @link              #
 * @since             1.0.5
 * @package           content-collaboration-inline-commenting
 *
 */

/**
 * When new user registered or deleted this will trigger.
 *
 * @return void
 */
if (!function_exists('gc_delete_users_transient')) {
    function gc_delete_users_transient()
    {
        delete_transient('gc_users_list');
    }
}
add_action('user_register', 'gc_delete_users_transient', 10, 1);
add_action('deleted_user', 'gc_delete_users_transient', 10, 3);

/**
 * Reaasigning Deleted User.
 *
 * @param int $id
 * @param int $reassign
 * @param object $user
 * @return void
 */
if (!function_exists('gc_reassigning_deleted_user')) {
    function gc_reassigning_deleted_user($id, $reassign)
    {
        global $wpdb;
        $wild    = '%';
        $find    = '_el';
        $like    = $wpdb->esc_like($find) . $wild;
        $results = $wpdb->get_results($wpdb->prepare( // phpcs:ignore
            "SELECT post_id, meta_value, meta_key
            FROM {$wpdb->prefix}postmeta
            WHERE meta_key
            LIKE %s",
            $like
        ));
        $user_to_reassign = $reassign ? $reassign : get_current_user_id();

        foreach ($results as $result) {
            $values = maybe_unserialize($result->meta_value);

            if (empty($values['comments'])) {
                continue;
            }

            foreach ($values['comments'] as $key=>$value) {
                if (isset($value['userData']) && $id === $value['userData']) {
                    $values['comments'][$key]['userData'] = $user_to_reassign;
                }
            }

            if (isset($values['assigned_to']) && $id === intval($values['assigned_to'])) {
                $values['assigned_to'] = $user_to_reassign;
            }

            if (isset($values['resolved_by']) && $id === intval($values['resolved_by'])) {
                $values['resolved_by'] = $user_to_reassign;
            }
         

            update_post_meta($result->post_id, $result->meta_key, $values);
        }
    }
}
add_action('delete_user', 'gc_reassigning_deleted_user', 10, 3);

/**
 * Get user's IP address.
 *
 * @return void
 */
if (!function_exists('get_visitor_ip_address')) {
    function get_visitor_ip_address() {

        $HTTP_CLIENT_IP       = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_SPECIAL_CHARS );
        $HTTP_X_FORWARDED_FOR = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_SPECIAL_CHARS );
        $REMOTE_ADDR          = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_SPECIAL_CHARS );

        if ( ! empty( $HTTP_CLIENT_IP ) ) {
            return $HTTP_CLIENT_IP;
        } elseif ( ! empty( $HTTP_X_FORWARDED_FOR ) ) {
            return $HTTP_X_FORWARDED_FOR;
        } else {
            return $REMOTE_ADDR;
        }

    }
}

/**
 * Get promotional banner.
 * 
 * @param string $location
 * 
 * @return string HTML
 */
if (!function_exists('cf_dpb_promotional_banner')) {
    function cf_dpb_promotional_banner( $where = null ){

        global $pagenow;

        $allow_pages = array('edit.php', 'post-new.php', 'post.php');
        $listing_page = array('edit.php');
        $single_new_edit_page = array('post-new.php', 'post.php');
        $dpb_banner_main_class = '';

        if( in_array( $pagenow, $listing_page ) ){
            $dpb_banner_main_class = 'cf-listing-page';
            $location = 'listing_post_page';
        }elseif( in_array( $pagenow, $single_new_edit_page ) ){
            $dpb_banner_main_class = 'cf-single-page block-editor';
            $location = 'single_post_page';
        }elseif( null !== $where && 'setting' === $where){
            $location = 'setting';
        }else{
            $location = '';
        } 

        if( in_array( $pagenow, $allow_pages ) || null !== $location ){

            $cf_edd = new CF_EDD();
            $promotional_banner_request_body = get_transient( 'cf_promotional_banner_request_data' );

            // Check if promotional banner datat exists in caching or not.
            if( isset( $promotional_banner_request_body ) && !empty( $promotional_banner_request_body ) ) {
                cf_display_promotional_banner_code( $promotional_banner_request_body, $pagenow, $location, $single_new_edit_page, $cf_edd, $dpb_banner_main_class );
            } else {
                $CF_PROMOTIONAL_BANNER_API_URL = CF_PROMOTIONAL_BANNER_API_URL . 'wp-json/dpb-promotional-banner/v2/dpb-promotional-banner?' . wp_rand();
                if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
                    $promotional_banner_request = vip_safe_wp_remote_get( $CF_PROMOTIONAL_BANNER_API_URL, 3, 1, 20 );
                } else {
                    $promotional_banner_request = wp_remote_get( $CF_PROMOTIONAL_BANNER_API_URL );   // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
                }
                
                ob_start();
                if ( empty( $promotional_banner_request->errors ) ) {
                    $promotional_banner_request_body = $promotional_banner_request['body'];
                    $promotional_banner_request_body = json_decode( $promotional_banner_request_body, true );
                    if ( ! empty( $promotional_banner_request_body ) ) {
                        cf_display_promotional_banner_code( $promotional_banner_request_body, $pagenow, $location, $single_new_edit_page, $cf_edd, $dpb_banner_main_class );

                        // Set promotional banner data in transient for 6 hours to restrict API call everytime.
                        set_transient( 'cf_promotional_banner_request_data', $promotional_banner_request_body, 21600 );
                    }
                }
            }

            

            return ob_get_clean();

        }else{
            return '';
        }

        
    }
}


/**
 * Function to display promotional banner HTML code.
 */
if (!function_exists('cf_display_promotional_banner_code')) {
    function cf_display_promotional_banner_code( $promotional_banner_request_body, $pagenow, $location, $single_new_edit_page, $cf_edd, $dpb_banner_main_class ){
        
        foreach ( $promotional_banner_request_body as $promotional_banner_request_body_data ) {

            $dpb_banner_display_on = explode(",", $promotional_banner_request_body_data['dpb_banner_display_on']);

            if( in_array( $pagenow, $single_new_edit_page ) ){
                $dpb_banner_display_position = isset($promotional_banner_request_body_data['dpb_banner_display_position']) ? $promotional_banner_request_body_data['dpb_banner_display_position'] : '';
            }else{
                $dpb_banner_display_position = '';
            }

            if ( in_array($location, $dpb_banner_display_on)){ 

                $promotional_banner_cookie          = $promotional_banner_request_body_data['promotional_banner_cookie'];
                $promotional_banner_image           = $promotional_banner_request_body_data['promotional_banner_image'];
                $promotional_banner_description     = $promotional_banner_request_body_data['promotional_banner_description'];
                $promotional_banner_button_group    = $promotional_banner_request_body_data['promotional_banner_button_group'];
                $dpb_schedule_campaign_type         = $promotional_banner_request_body_data['dpb_schedule_campaign_type'];
                $promotional_banner_target_audience = $promotional_banner_request_body_data['promotional_banner_target_audience'];

                if ( ! empty( $promotional_banner_target_audience ) ) {

                    $currunt_plan_name = '';
                    if ( $cf_edd->is__premium_only() ) {
                        $currunt_plan_name = esc_html( $cf_edd->get_plan_name() );
                    } else {
                        $currunt_plan_name = esc_html( 'FREE' );
                    }

                    $display_banner_flag = false;
                    if ( 'all_customers' === $promotional_banner_target_audience['value'] ) {
                        $display_banner_flag = true;
                    } elseif ( 'pro_plan_customer' === $promotional_banner_target_audience['value'] && 'pro' === $currunt_plan_name ) {
                        $display_banner_flag = true;
                    } elseif ( 'plus_plan_customer' === $promotional_banner_target_audience['value'] && 'plus' === $currunt_plan_name ) {
                        $display_banner_flag = true;
                    } elseif ( 'vip_plan_customer' === $promotional_banner_target_audience['value'] && 'vip' === $currunt_plan_name ) {
                        $display_banner_flag = true;
                    } elseif ( 'basic_plan_customer' === $promotional_banner_target_audience['value'] && 'FREE' === $currunt_plan_name ) {
                        $display_banner_flag = true;
                    } elseif ( 'premium_customer' === $promotional_banner_target_audience['value'] && ( 'plus' === $currunt_plan_name || 'pro' === $currunt_plan_name || 'vip' === $currunt_plan_name ) ) {
                        $display_banner_flag = true;
                    }
                }

                if ( true === $display_banner_flag ) {
                    if ( 'default' === $dpb_schedule_campaign_type ) {
                        $banner_cookie_show         = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        $flag                       = false;
                        if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                            setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes', time() + ( 86400 * 7 ) ); //phpcs:ignore
                            setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' ); //phpcs:ignore
                            $flag = true;
                        }

                        $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        if ( ! empty( $banner_cookie_show ) || true === $flag ) {

                            $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                            $banner_cookie = isset( $banner_cookie ) ? $banner_cookie : '';
                            if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                ?>
                        <div class="cf-plugin-popup <?php echo isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner'; ?> <?php echo esc_attr($dpb_banner_display_position); ?> <?php echo esc_attr($dpb_banner_main_class); ?>">
                                <?php
                                if ( ! empty( $promotional_banner_image ) ) {
                                    ?>
                                        <img src="<?php echo esc_url( $promotional_banner_image ); ?>"/>
                                    <?php
                                }
                                ?>
                                <div class="cf-plugin-popup-meta">
                                    <p>
                                        <?php
                                        echo wp_kses_post( str_replace( array( '<p>', '</p>' ), '', $promotional_banner_description ) );
                                        if ( ! empty( $promotional_banner_button_group ) ) {
                                            foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                                ?>
                                                <a href="<?php echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] ); ?>" target="_blank"><?php echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] ); ?></a>
                                                <?php
                                            }
                                        }
                                        ?>
                                </p>
                                </div>
                                <a href="#." data-popup-name="<?php echo isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner'; ?>" class="cf-pluginpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>   
                            </div>
                                <?php
                            }
                        }
                    } else {

                        $banner_cookie_show         = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        $flag                       = false;
                        if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                            setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes'); //phpcs:ignore
                            setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' ); //phpcs:ignore
                            $flag = true;
                        }

                        $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                        if ( ! empty( $banner_cookie_show ) || true === $flag ) {

                            $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_SPECIAL_CHARS );
                            $banner_cookie = isset( $banner_cookie ) ? $banner_cookie : '';
                            if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                ?>
                            <div class="cf-plugin-popup <?php echo isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner'; ?> <?php echo esc_attr($dpb_banner_display_position); ?> <?php echo esc_attr($dpb_banner_main_class); ?>">
                                <?php
                                if ( ! empty( $promotional_banner_image ) ) {
                                    ?>
                                        <img src="<?php echo esc_url( $promotional_banner_image ); ?>"/>
                                    <?php
                                }
                                ?>
                                <div class="cf-plugin-popup-meta">
                                    <p>
                                        <?php
                                        echo wp_kses_post( str_replace( array( '<p>', '</p>' ), '', $promotional_banner_description ) );
                                        if ( ! empty( $promotional_banner_button_group ) ) {
                                            foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                                ?>
                                                <a href="<?php echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] ); ?>" target="_blank"><?php echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] ); ?></a>
                                                <?php
                                            }
                                        }
                                        ?>
                                </p>
                                </div>
                                <a href="#." data-popup-name="<?php echo isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner'; ?>" class="cf-pluginpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                            </div>
                                <?php

                            }
                        }
                    }
                }
            }

        }
    }
}
