<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */
class Commenting_block_Admin extends Commenting_block_Functions
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private  $version ;
    /**
     * Initiate Email Class Object.
     */
    private  $email_class ;
    /**
     * @var string Comment Activities.
     */
    public  $cf_activities ;
    public  $cf_activities_object ;
    private static  $allowed_attribute_tags = array(
        'content',
        'citation',
        'caption',
        'value',
        'values',
        'fileName',
        'text',
        'downloadButtonText'
    ) ;
    private static  $cf_permission_options = array(
        'cf_permission_add_comment',
        'cf_permission_resolved_comment',
        'cf_permission_add_suggestion',
        'cf_permission_resolved_suggestion'
    ) ;
    /**
     * Initiate basename .
     */
    static  $basename = null ;
    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // Publish Comments on status change.
        add_action(
            'post_updated',
            array( $this, 'cf_post_status_changes' ),
            10,
            3
        );
        // Update caps for authors and contributors.
        add_filter( 'admin_init', array( $this, 'cf_custom_caps' ) );
        // Allow caps for Multisite environment.
        add_filter(
            'map_meta_cap',
            array( $this, 'cf_add_unfiltered_html_capability_to_users' ),
            1,
            3
        );
        // Action to add setting page.
        add_action( 'admin_menu', array( $this, 'cf_add_setting_page' ) );
        // Adding new column to the posts list.
        add_filter( 'manage_posts_columns', array( $this, 'cf_columns_head' ) );
        add_filter( 'manage_pages_columns', array( $this, 'cf_columns_head' ) );
        // Adding content in a column of posts list.
        add_action(
            'manage_posts_custom_column',
            array( $this, 'cf_columns_content' ),
            10,
            2
        );
        add_action(
            'manage_pages_custom_column',
            array( $this, 'cf_columns_content' ),
            10,
            2
        );
        // Make custom comments columns sortable.
        add_filter( 'manage_edit-post_sortable_columns', array( $this, 'cf_sortable_comments_column' ) );
        add_filter( 'manage_edit-page_sortable_columns', array( $this, 'cf_sortable_comments_column' ) );
        // Set query to sort.
        add_action( 'pre_get_posts', array( $this, 'cf_sort_custom_column_query' ) );
        // Remove the mdspan tage from the front content.
        add_filter( 'the_content', array( $this, 'cf_removeMdspan' ) );
        // Add untitled when page/post title blank
        add_filter( 'the_title', array( $this, 'cf_post_title' ) );
        // Add user role to WordPress users api
        add_action( 'rest_api_init', array( $this, 'create_api_user_meta_field_for_userrole' ) );
        // clear output buffer on init hook.
        add_action( 'init', array( $this, 'cf_app_output_buffer' ) );
    }
    
    /**
     * Clear output buffer on init function.
     *
     * @return void
     */
    public function cf_app_output_buffer()
    {
        ob_start();
    }
    
    /**
     * Add Untitled when post title is blank.
     *
     * @param array $title title of post.
     *
     * @return mixed Updated title.
     */
    function cf_post_title( $title )
    {
        return ( '' === $title ? esc_html_x( 'Untitled', 'Added to posts and pages that are missing titles', 'content-collaboration-inline-commenting' ) : $title );
    }
    
    /**
     * Remove custom tag "mdspan" from the content.
     *
     * @param array $content content of post.
     *
     * @return mixed Updated content.
     */
    public function cf_removeMdspan( $content )
    {
        
        if ( is_singular() && is_main_query() ) {
            $regex = '#<mdspan(.*?)>#';
            $replacement = '';
            $content = preg_replace( $regex, $replacement, $content );
        }
        
        return $content;
    }
    
    /**
     * Remove custom tag "mdspan" from the content.
     *
     * @param array $content content of post.
     *
     * @return mixed Updated content.
     */
    public function cf_remove_suggestions_tag( $content )
    {
        
        if ( is_singular() && is_main_query() ) {
            // mdadded remove text
            $regx1 = '#<ins\\s+class="mdadded" (.*?)>(.*?)</ins>#s';
            $replace = '';
            $remove_added_content = preg_replace( $regx1, $replace, $content );
            $regex = '#<(ins|del)\\s+class="(mdadded|mdremoved|mdmodified)" (.*?)>(\\<.*?\\>|.*?)#';
            $replacement = '';
            $content = preg_replace( $regex, $replacement, $remove_added_content );
        }
        
        return $content;
    }
    
    /**
     * Make custom comments columns sortable.
     *
     * @param array $columns List of columns.
     *
     * @return mixed Updated list of columns.
     */
    public function cf_sortable_comments_column( $columns )
    {
        $columns['cb_comments_status'] = 'sort_by_cf_comments';
        return $columns;
    }
    
    /**
     * Set query to sort.
     *
     * @param object $query Query object.
     */
    public function cf_sort_custom_column_query( $query )
    {
        $orderby = $query->get( 'orderby' );
        
        if ( 'sort_by_cf_comments' === $orderby && $query->is_main_query() ) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                'key'     => 'open_cf_count',
                'compare' => 'NOT EXISTS',
            ),
                array(
                'key' => 'open_cf_count',
            ),
            );
            $query->set( 'meta_query', $meta_query );
            $query->set( 'orderby', 'meta_value' );
            return;
        }
    
    }
    
    /**
     * Update columns of the posts list.
     *
     * @param array $defaults List of default columns.
     *
     * @return array mixed Updated list of default columns.
     */
    public function cf_columns_head( $defaults )
    {
        $all_post_type = get_post_types_by_support( array( 'editor' ) );
        $post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
        $type = get_post_type();
        $cf_hide_editorial_column = get_option( 'cf_hide_editorial_column' );
        if ( empty($cf_hide_editorial_column) ) {
            if ( in_array( trim( $post_type ), $all_post_type, true ) || in_array( trim( $type ), $all_post_type, true ) ) {
                if ( (isset( $post_type ) || isset( $type )) && ($post_type !== 'product' || $type !== 'product') ) {
                    $defaults['cb_comments_status'] = '<img id="cf-column-img" src="' . esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/commenting-logo.svg' ) . '" width=17/>' . __( 'Editorial Comments', 'content-collaboration-inline-commenting' );
                }
            }
        }
        return $defaults;
    }
    
    /**
     * Add content in a new column of the posts list.
     *
     * @param string $column_name Column name.
     * @param int    $post_ID Post ID.
     */
    public function cf_columns_content( $column_name, $post_ID )
    {
        
        if ( $column_name === 'cb_comments_status' ) {
            $comment_counts = $this->cf_get_comment_counts( $post_ID );
            $open_counts = intval( $comment_counts['open_counts'] );
            $total_counts = intval( $comment_counts['total_counts'] );
            $autodraft_total = intval( $total_counts ) - ($open_counts + intval( $comment_counts['resolved_counts'] ));
            $open_counts = $total_counts - ($autodraft_total + intval( $comment_counts['resolved_counts'] ));
            
            if ( 0 !== $total_counts ) {
                echo  '<a href="' . esc_url( get_edit_post_link( $post_ID ) ) . '">' . esc_html( $open_counts . '/' . $total_counts ) . '</a>' ;
            } else {
                echo  '-' ;
            }
        
        }
    
    }
    
    /**
     * Add Setting Page.
     */
    public function cf_add_setting_page()
    {
        $settings_title = 'Multicollab';
        // Adding a new admin page for MYS
        add_menu_page(
            __( esc_html( $settings_title ), 'content-collaboration-inline-commenting' ),
            __( esc_html( $settings_title ), 'content-collaboration-inline-commenting' ),
            'manage_options',
            'editorial-comments',
            array( $this, 'cf_settings_callback' ),
            COMMENTING_BLOCK_URL . '/admin/assets/images/menu-icon.svg'
        );
    }
    
    /**
     * Plugin setting page callback function.
     */
    public function cf_settings_callback()
    {
        // Add setting page file.
        require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-settings-page.php';
        // Removed phpcs:ignore by Rishi Shah.
    }
    
    /**
     * Allowed Administrator, editor, author and contributor user to enter unfiltered html.
     *
     * @param array  $caps All caps.
     * @param string $cap Cap in a loop.
     * @param int    $user_id User ID.
     *
     * @return array Caps.
     */
    public function cf_add_unfiltered_html_capability_to_users( $caps, $cap, $user_id )
    {
        if ( 'unfiltered_html' === $cap && (user_can( $user_id, 'administrator' ) || user_can( $user_id, 'editor' ) || user_can( $user_id, 'author' ) || user_can( $user_id, 'contributor' )) ) {
            $caps = array( 'unfiltered_html' );
        }
        return $caps;
    }
    
    /**
     * Add capabilities to user roles to make 'mdspan' tag unfiltered.
     *
     * @return bool True always.
     */
    public function cf_custom_caps()
    {
        $roles = array( 'author', 'contributor' );
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            if ( $role ) {
                // Add custom capabilities.
                $role->add_cap( 'unfiltered_html' );
            }
        }
        return true;
    }
    
    /**
     * Get User Details using AJAX.
     */
    public function cf_get_user()
    {
        $curr_user = wp_get_current_user();
        $userID = $curr_user->ID;
        $userName = $curr_user->display_name;
        $userURL = get_avatar_url( $userID );
        $userRole = get_userdata( $userID )->roles[0];
        echo  wp_json_encode( array(
            'id'   => $userID,
            'name' => $userName,
            'role' => $userRole,
            'url'  => $userURL,
        ) ) ;
        wp_die();
    }
    
    /**
     * @param int           $post_ID Post ID.
     * @param object/string $post Post Content.
     * @param string        $update Status of the update.
     */
    public function cf_post_status_changes( $post_ID, $post )
    {
        $metas = get_post_meta( $post_ID );
        $p_content = ( is_object( $post ) ? $post->post_content : $post );
        $p_link = get_edit_post_link( $post_ID );
        $p_title = get_the_title( $post_ID );
        // Removed phpcs:ignore by Rishi Shah.
        $site_title = get_bloginfo( 'name' );
        // Removed phpcs:ignore by Rishi Shah.
        $html = '';
        // Get current user details.
        $curr_user = wp_get_current_user();
        $user_id = $curr_user->ID;
        $current_user_email = $curr_user->user_email;
        $current_user_display_name = $curr_user->display_name;
        // Publish drafts from the '_current_drafts' stack.
        $current_drafts = ( isset( $metas['_current_drafts'][0] ) ? $metas['_current_drafts'][0] : array() );
        $current_drafts = maybe_unserialize( $current_drafts );
        $current_timestamp = current_time( 'timestamp' );
        // Publish Deleted Comments. (i.e. finally delete them.)
        
        if ( isset( $current_drafts['deleted'] ) && 0 !== count( $current_drafts['deleted'] ) ) {
            $deleted_drafts = $current_drafts['deleted'];
            foreach ( $deleted_drafts as $el => $timestamps ) {
                $prev_state = $metas[$el][0];
                $prev_state = maybe_unserialize( $prev_state );
                foreach ( $timestamps as $key => $t ) {
                    $local_time = current_datetime();
                    $deleted_timestamp = $local_time->getTimestamp() + $local_time->getOffset() + $key;
                    // Update the timestamp of deleted comment.
                    $previous_comment = $prev_state['comments'][$t];
                    
                    if ( !empty($previous_comment) ) {
                        $prev_state['comments'][$deleted_timestamp] = $previous_comment;
                        $prev_state['comments'][$deleted_timestamp]['status'] = 'deleted';
                        $prev_state['comments'][$deleted_timestamp]['created_at'] = $t;
                    }
                
                }
                $prev_state['updated_at'] = $current_timestamp;
                // add th meta
                update_post_meta( $post_ID, $el, $prev_state );
                update_post_meta( $post_ID, 'th' . $el, $deleted_timestamp );
                $metas[$el][0] = maybe_serialize( $prev_state );
            }
            // add mc_updated
        }
        
        // Publish New Comments.
        
        if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
            $new_drafts = $current_drafts['comments'];
            foreach ( $new_drafts as $el => $drafts ) {
                /*
                 * Make publish only if its tag available in the content.
                 * Doing this to handle the CTRL-Z action.
                 * Sometimes CTRL-Z does not removes the tag completely
                 * but only removes its attributes, so we cant find 'datatext' attribute,
                 * So skipping those mdspan tags which has no 'datatext' attribute.
                 * This is also skipping the recent resolved drafts.
                 */
                $elid = str_replace( '_', '', $el );
                
                if ( strpos( $p_content, $elid ) !== false ) {
                    $prev_state = $metas[$el][0];
                    $prev_state = maybe_unserialize( $prev_state );
                    $new_comments = array();
                    foreach ( $drafts as $d ) {
                        $prev_state['comments'][$d]['status'] = 'publish';
                        $new_comments[] = $d;
                    }
                    $prev_state['updated_at'] = $current_timestamp;
                    update_post_meta( $post_ID, $el, $prev_state );
                    $metas[$el][0] = maybe_serialize( $prev_state );
                }
            
            }
        }
        
        // Publish Edited Comments.
        
        if ( isset( $current_drafts['edited'] ) && 0 !== count( $current_drafts['edited'] ) ) {
            $edited_drafts = $current_drafts['edited'];
            foreach ( $edited_drafts as $el => $timestamps ) {
                $prev_state = $metas[$el][0];
                $prev_state = maybe_unserialize( $prev_state );
                foreach ( $timestamps as $t ) {
                    $edited_draft = $prev_state['comments'][$t]['draft_edits']['thread'];
                    $edited_attachment = $prev_state['comments'][$t]['draft_edits']['attachmentText'];
                    if ( !empty($edited_draft) ) {
                        $prev_state['comments'][$t]['thread'] = $edited_draft;
                    }
                    
                    if ( !empty($edited_attachment) ) {
                        $prev_state['comments'][$t]['attachmentText'] = $edited_attachment;
                    } else {
                        $prev_state['comments'][$t]['attachmentText'] = '';
                    }
                    
                    // Change status to publish.
                    $prev_state['comments'][$t]['status'] = 'publish';
                    // Remove comment from edited_draft.
                    unset( $prev_state['comments'][$t]['draft_edits']['thread'] );
                    unset( $prev_state['comments'][$t]['draft_edits']['attachmentText'] );
                }
                $prev_state['updated_at'] = $current_timestamp;
                update_post_meta( $post_ID, $el, $prev_state );
                update_post_meta( $post_ID, 'th' . $el, $current_timestamp );
                $metas[$el][0] = maybe_serialize( $prev_state );
            }
        }
        
        // Mark Resolved Threads.
        
        if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
            $resolved_drafts = $current_drafts['resolved'];
            $html .= '<div class="comment-box comment-resolved" style="background:#fff;width:70%;    font-family: Roboto,sans-serif;padding-top:40px;">';
            $html .= '<div class="comment-box-header" style="margin-bottom:30px;border:1px solid #eee;border-radius:20px;padding:30px;">';
            $html .= '<p style="margin:0;padding-bottom:20px;font-size:18px;"><a href="mailto:' . esc_attr( $current_user_email ) . '" class="" style="  padding: 8px 25px;font-size: 18px;background-color: #4B1BCE; border-radius: 8px;color: #fff;text-decoration: none; text-transform: capitalize; margin-right:6px;">' . esc_html( $current_user_display_name ) . '</a> ' . __( 'has resolved the following thread.', 'content-collaboration-inline-commenting' ) . '</p>';
            if ( !empty($p_title) ) {
                $html .= '<h2 class="comment-page-title" style="font-size:20px;margin:0;"><a href="' . esc_url( $p_link ) . '" style="color:#4B1BCE;text-decoration:underline;font-size:20px;">' . esc_html( $p_title ) . '</a></h2></div>';
            }
            $html .= '<div class="comment-box-body" style="border:1px solid #eee;border-radius:20px;padding:30px;">';
            $html .= '<h3 class="head-with-icon" style="margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;">';
            $html .= '<span class="icon-resolved" style="padding-right:10px;vertical-align:middle;">';
            $html .= '<img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/icon-check-fill.png' ) . '" alt="Resolved" />';
            $html .= '</span>' . __( ' Resolved Thread Comments', 'content-collaboration-inline-commenting' );
            $html .= '</h3>';
            foreach ( $resolved_drafts as $el ) {
                $prev_state = $metas[$el][0];
                $prev_state = maybe_unserialize( $prev_state );
                $prev_state['resolved'] = 'true';
                $prev_state['resolved_timestamp'] = $current_timestamp;
                $prev_state['resolved_by'] = $user_id;
                
                if ( isset( $current_drafts['comments'] ) && array_key_exists( $el, $current_drafts['comments'] ) ) {
                    // If any published comment is there.
                    $can_delete = false;
                    
                    if ( count( $prev_state['comments'] ) > 0 ) {
                        foreach ( $prev_state['comments'] as $prev_state_cmnt ) {
                            if ( 'draft' === isset( $prev_state_cmnt['status'] ) ) {
                                $can_delete = true;
                            }
                            break;
                        }
                        
                        if ( true === $can_delete ) {
                            delete_post_meta( $post_ID, $el );
                        } else {
                            $unpublished_comments = $current_drafts['comments'][$el];
                            if ( !empty($unpublished_comments) ) {
                                foreach ( $unpublished_comments as $unpublished_comment ) {
                                    $prev_state['comments'][$unpublished_comment]['status'] = 'publish';
                                }
                            }
                            $prev_state['updated_at'] = $current_timestamp;
                            update_post_meta( $post_ID, $el, $prev_state );
                        }
                    
                    }
                
                } else {
                    $prev_state['updated_at'] = $current_timestamp;
                    update_post_meta( $post_ID, $el, $prev_state );
                }
                
                update_post_meta( $post_ID, 'th' . $el, $current_timestamp );
            }
        }
        
        if ( isset( $current_drafts ) && !empty($current_drafts) ) {
            // create and update the mc_uodated meta
            update_post_meta( $post_ID, 'mc_updated', $current_timestamp );
        }
        // Flush Current Drafts Stack.
        update_post_meta( $post_ID, '_current_drafts', '' );
        // Update open comments count.
        $comment_counts = $this->cf_get_comment_counts( $post_ID, $p_content, $metas );
        update_post_meta( $post_ID, 'open_cf_count', $comment_counts['open_counts'] );
        // Create and Update the last user for summary tab in activity center.
        update_post_meta( $post_ID, 'last_user_edited', $current_user_display_name );
        // Deleteing comments if users delete comments at the same moment.
        if ( !empty($current_drafts['deleted']) ) {
            foreach ( $current_drafts['deleted'] as $key => $value ) {
                $comment = get_post_meta( $post_ID, $key, true );
                foreach ( $value as $delete_key ) {
                    unset( $comment['comments'][$delete_key] );
                }
                update_post_meta( $post_ID, $key, $comment );
            }
        }
    }
    
    /**
     * @param string $string The string to be limited.
     * @param int    $limit The total number of characters allowed.
     *
     * @return string The limited string with '...' appended.
     */
    public function cf_limit_characters( $string, $limit = 100 )
    {
        return ( strlen( $string ) > $limit ? substr( $string, 0, $limit ) . '...' : $string );
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function cf_enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Commenting_block_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Commenting_block_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();
        if ( 'site-editor' !== $screen->base ) {
            wp_enqueue_style(
                $this->plugin_name,
                trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/css/commenting-block-admin.css',
                array(),
                wp_rand(),
                'all'
            );
        }
        wp_enqueue_style(
            'cf-select2',
            trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/css/select2.min.css',
            array(),
            wp_rand(),
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function cf_enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Commenting_block_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Commenting_block_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();
        
        if ( $screen->is_block_editor || 'toplevel_page_editorial-comments' === $screen->base || 'site-editor' !== $screen->base ) {
            wp_enqueue_script(
                $this->plugin_name,
                trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/commenting-block-admin.js',
                array(
                'jquery',
                'wp-components',
                'wp-editor',
                'wp-data',
                'cf-mark',
                'cf-dom-purify',
                'react',
                'react-dom'
            ),
                wp_rand(),
                false
            );
            wp_enqueue_script(
                'cf-mark',
                trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/mark.min.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_script(
                'cf-dom-purify',
                trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/purify.min.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_script( 'font-awesome-free', 'https://kit.fontawesome.com/cfc6818450.js' );
            wp_enqueue_script(
                'cf-testimonial-slider',
                trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/jquery.bxslider.min.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name,
                trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/commenting-block-admin.js',
                array(
                'jquery',
                'wp-components',
                'wp-editor',
                'wp-data',
                'wp-i18n',
                'cf-mark',
                'cf-dom-purify',
                'react',
                'react-dom'
            ),
                wp_rand(),
                false
            );
            wp_enqueue_script(
                'freemius-checkout-js',
                'https://checkout.freemius.com/checkout.min.js',
                array( 'jquery' ),
                '',
                true
            );
            wp_enqueue_script(
                'content-collaboration-inline-commenting',
                trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/block.build.min.js',
                array(
                'jquery',
                'cf-mark',
                'cf-dom-purify',
                'cf-testimonial-slider',
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-editor',
                'wp-components',
                'wp-annotations',
                'jquery-ui-datepicker',
                'wp-api-fetch',
                'wp-plugins',
                'wp-edit-post',
                'wp-data'
            ),
                wp_rand(),
                true
            );
            
            if ( 'wp_template' === isset( $_REQUEST['postType'] ) ) {
                //phpcs:ignore
                wp_deregister_script( 'content-collaboration-inline-commenting' );
                wp_deregister_script( $this->plugin_name );
            }
            
            $comment_id = filter_input( INPUT_GET, 'comment_id', FILTER_SANITIZE_STRING );
            wp_localize_script( $this->plugin_name, 'adminLocalizer', array(
                'nonce'                  => wp_create_nonce( COMMENTING_NONCE ),
                'comment_id'             => ( isset( $comment_id ) ? $comment_id : null ),
                'allowed_attribute_tags' => apply_filters( 'commenting_block_allowed_attr_tags', static::$allowed_attribute_tags ),
                'cf_permission_options'  => apply_filters( 'commenting_block_permission_options', static::$cf_permission_options ),
            ) );
            // set edit time timezone
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            $edited_timestamp = current_time( 'timestamp' );
            $editedDateTime = gmdate( $time_format . ' ' . $date_format, $edited_timestamp );
            wp_localize_script( $this->plugin_name, 'editedTimestamp', array(
                'cmtEditedTime' => $edited_timestamp,
            ) );
            wp_localize_script( $this->plugin_name, 'editedTimezone', array(
                'editedTime' => $editedDateTime,
            ) );
            wp_localize_script( $this->plugin_name, 'wp_time_setting', array(
                'dateFormat'     => $date_format,
                'timeFormat'     => $time_format,
                'timezoneOffset' => get_option( 'gmt_offset' ),
            ) );
            $current_user = wp_get_current_user();
            $currunt_user_roles = array_values( $current_user->roles );
            $current_user_role = array_shift( $currunt_user_roles );
            wp_localize_script( $this->plugin_name, 'currentUserData', array(
                'id'       => $current_user->ID,
                'username' => $current_user->data->display_name,
                'role'     => $current_user_role,
                'avtarUrl' => get_avatar_url( $current_user->ID ),
            ) );
            $can_upload_file = $current_user->has_cap( 'upload_files' );
            wp_localize_script( $this->plugin_name, 'can_upload_file', array(
                'can_upload' => $can_upload_file,
            ) );
            $cf_options = get_option( 'cf_permissions' );
            $cf_add_comment_permission = isset( $cf_options[$current_user->roles[0]]['add_comment'] ) ?? '';
            $cf_resolved_comment_permission = isset( $cf_options[$current_user->roles[0]]['resolved_comment'] ) ?? '';
            $cf_hide_comment_permission = isset( $cf_options[$current_user->roles[0]]['hide_comment'] ) ?? '';
            $cf_add_suggestion_permission = isset( $cf_options[$current_user->roles[0]]['add_suggestion'] ) ?? '';
            $cf_resolved_suggestion_permission = isset( $cf_options[$current_user->roles[0]]['resolved_suggestion'] ) ?? '';
            $cf_hide_suggestion_permission = isset( $cf_options[$current_user->roles[0]]['hide_suggestion'] ) ?? '';
            wp_localize_script( $this->plugin_name, 'cf_permissions', array(
                'add_comment'         => $cf_add_comment_permission,
                'resolved_comment'    => $cf_resolved_comment_permission,
                'hide_comment'        => $cf_hide_comment_permission,
                'add_suggestion'      => $cf_add_suggestion_permission,
                'resolved_suggestion' => $cf_resolved_suggestion_permission,
                'hide_suggestion'     => $cf_hide_suggestion_permission,
            ) );
            $cf_fs_data = array(
                'current_plan'         => cf_fs()->get_plan_name(),
                'can_use_premium_code' => cf_fs()->can_use_premium_code(),
                'is_plan_plus'         => cf_fs()->is_plan( 'plus', true ),
                'is_plan_pro'          => cf_fs()->is_plan( 'pro', true ),
                'is_plan_vip'          => cf_fs()->is_plan( 'vip', true ),
            );
            wp_localize_script( $this->plugin_name, 'multicollab_fs', $cf_fs_data );
            wp_localize_script( $this->plugin_name, 'showinfoboard', array(
                'showinfoboard' => get_option( 'cf_show_infoboard' ),
            ) );
            $cf_give_alert_message = array(
                'cf_give_alert_message' => get_option( 'cf_give_alert_message' ),
            );
            wp_localize_script( $this->plugin_name, 'multicollab_cf_alert', $cf_give_alert_message );
            // Suggestion Mode/@author Rishi Shah/@since EDD - 3.0.1
            $cf_suggestion_mode = array(
                'cf_suggestion_mode_option_name'     => get_option( 'cf_suggestion_mode_option_name' ),
                'cf_specific_post_categories_values' => get_option( 'cf_specific_post_categories_values' ),
                'cf_specific_post_types_values'      => get_option( 'cf_specific_post_types_values' ),
            );
            wp_localize_script( $this->plugin_name, 'multicollab_suggestion_mode', $cf_suggestion_mode );
            // Floating Icons/@author Rishi Shah/@since EDD - 3.0.1
            $cf_hide_floating_icons = array(
                'cf_hide_floating_icons' => get_option( 'cf_hide_floating_icons' ),
            );
            wp_localize_script( $this->plugin_name, 'multicollab_floating_icons', $cf_hide_floating_icons );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-droppable' );
            wp_enqueue_script(
                'cf-activity-centre',
                trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/activityCentre.build.min.js',
                array(
                'content-collaboration-inline-commenting',
                'wp-plugins',
                'wp-editor',
                'wp-edit-post',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-data'
            ),
                wp_rand(),
                true
            );
            $activity = 'cf-activity-centre';
            wp_localize_script( $activity, 'activityLocalizer', array(
                'nonce'         => wp_create_nonce( 'wp_rest' ),
                'apiUrl'        => home_url( '/wp-json' ),
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'currentUserID' => get_current_user_id(),
            ) );
        }
        
        wp_enqueue_script(
            'cf-select2-js',
            trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/select2.min.js',
            array( 'jquery' ),
            wp_rand(),
            true
        );
    }
    
    /**
     * Convert string to linkable email.
     *
     * @param string $str Contains the strings that comes from the textarea.
     *
     * @return string
     */
    public function convert_str_to_email( $str )
    {
        $mail_pattern = '/([A-z0-9\\._-]+\\@[A-z0-9_-]+\\.)([A-z0-9\\_\\-\\.]{1,}[A-z])/';
        return preg_replace( $mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str );
    }
    
    /**
     * Add Comment function.
     */
    public function cf_add_comment()
    {
        $commentList = filter_input( INPUT_POST, "commentList", FILTER_DEFAULT );
        // phpcs:ignore
        $commentList = html_entity_decode( $commentList );
        $commentList = json_decode( $commentList, true );
        $list_of_comments = $commentList;
        // Get the assigned User ID.
        $assign_to = filter_input( INPUT_POST, 'assignTo', FILTER_SANITIZE_NUMBER_INT );
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $arr = array();
        $commentList = end( $commentList );
        $metaId = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_STRING );
        $blockType = filter_input( INPUT_POST, 'blockType', FILTER_SANITIZE_STRING );
        $login_user = wp_get_current_user();
        // If 'commented on' text is blank, stop process.
        
        if ( empty($commentList['commentedOnText']) ) {
            echo  wp_json_encode( array(
                'error' => 'Please select a block, text or media to comment on.',
            ) ) ;
            wp_die();
        }
        
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        $timestamp = current_time( 'timestamp' );
        $dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
        $commentListOld = get_post_meta( $current_post_id, $metaId, true );
        $superCareerData = maybe_unserialize( $commentListOld );
        $assign_label = 'Assigned to';
        $assigned_user_info = get_userdata( $commentList['assigned'] );
        
        if ( !empty($superCareerData) ) {
            $assignedExist = array_column( $superCareerData['comments'], 'assigned' );
            $has_empty_values = !array_filter( $assignedExist );
            $assign_label = ( $has_empty_values == 1 ? 'Assigned to' : 'Reassigned to' );
            // phpcs:ignore
        }
        
        $assigned_text = ( isset( $assigned_user_info->display_name ) ? ( $login_user->data->ID == $assigned_user_info->ID ? $assign_label . ' You' : $assign_label . ' ' . $assigned_user_info->display_name ) : '' );
        // phpcs:ignore
        $arr['userData'] = get_current_user_id();
        // Secure content.
        $arr['thread'] = $this->cf_secure_content( $commentList['thread'] );
        $arr['assigned'] = $assigned_text;
        if ( !empty($commentList['attachmentText']) ) {
            $arr['attachmentText'] = $commentList['attachmentText'];
        }
        // Update Current Drafts.
        $current_drafts = get_post_meta( $current_post_id, '_current_drafts', true );
        $current_drafts = maybe_unserialize( $current_drafts );
        $current_drafts = ( empty($current_drafts) ? array() : $current_drafts );
        
        if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
            $current_drafts['comments'][$metaId][] = $timestamp;
        } else {
            $current_drafts['comments'][$metaId][] = $timestamp;
        }
        
        update_post_meta( $current_post_id, '_current_drafts', $current_drafts );
        
        if ( isset( $superCareerData['comments'] ) && 0 !== count( $superCareerData['comments'] ) ) {
            $superCareerData['comments'][$timestamp] = $arr;
            $superCareerData['updated_at'] = $timestamp;
            
            if ( $assign_to > 0 ) {
                $superCareerData['assigned_to'] = $assign_to;
                $superCareerData['sent_assigned_email'] = false;
            }
            
            update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
        } else {
            $superCareerData = array();
            $superCareerData['comments'][$timestamp] = $arr;
            $superCareerData['commentedOnText'] = $commentList['commentedOnText'];
            $superCareerData['updated_at'] = $timestamp;
            $superCareerData['blockType'] = $blockType;
            
            if ( $assign_to > 0 ) {
                $superCareerData['assigned_to'] = $assign_to;
                $superCareerData['sent_assigned_email'] = false;
            }
            
            update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
        }
        
        update_post_meta( $current_post_id, $metaId, $superCareerData );
        update_post_meta( $current_post_id, 'mc_updated', $timestamp );
        $last_index = count( $list_of_comments ) - 1;
        $list_of_comments[$last_index]['timestamp'] = $timestamp;
        // Get assigned user data.
        $assigned_to = null;
        
        if ( !empty($superCareerData['assigned_to']) ) {
            $login_user = wp_get_current_user();
            $user_data = get_user_by( 'ID', $superCareerData['assigned_to'] );
            $displayName = ( $login_user->data->ID == $user_data->ID ? 'You' : $user_data->display_name );
            // phpcs:ignore
            $assigned_to = array(
                'ID'           => $user_data->ID,
                'display_name' => $displayName,
                'user_email'   => $user_data->user_email,
                'avatar'       => get_avatar_url( $user_data->ID, array(
                'size' => 32,
            ) ),
            );
        }
        
        echo  wp_json_encode( array(
            'dtTime'       => $dtTime,
            'timestamp'    => $timestamp,
            'assignedTo'   => $assigned_to,
            'assignedText' => $assigned_text,
        ) ) ;
        wp_die();
    }
    
    /**
     * Make Content Secure.
     *
     * @param string $content
     * @return string
     */
    public function cf_secure_content( $content )
    {
        $allowed_tags = array(
            'a'   => array(
            'contenteditable' => array(),
            'href'            => array(),
            'target'          => array(),
            'style'           => array(),
            'class'           => array( 'js-mentioned' ),
            'data-email'      => array(),
        ),
            'div' => array(
            'id'    => array(),
            'class' => array(),
            'style' => array(),
        ),
            'br'  => array(),
        );
        $pattern = '/<[script|\\/script]*>/i';
        $content = preg_replace( $pattern, '', $content );
        $content = wp_kses( $content, $allowed_tags );
        return $content;
    }
    
    /**
     * Update Comment function.
     */
    public function cf_update_comment()
    {
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $metaId = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_STRING );
        $edited_comment = filter_input( INPUT_POST, "editedComment", FILTER_DEFAULT );
        // phpcs:ignore
        $edited_comment = htmlspecialchars_decode( $edited_comment );
        $edited_comment = html_entity_decode( $edited_comment );
        $edited_comment = json_decode( $edited_comment, true );
        // Make content secured.
        $edited_comment['thread'] = $this->cf_secure_content( $edited_comment['thread'] );
        $old_timestamp = $edited_comment['timestamp'];
        $commentListOld = get_post_meta( $current_post_id, $metaId, true );
        $commentListOld = maybe_unserialize( $commentListOld );
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        $edited_timestamp = current_time( 'timestamp' );
        $edited_comment['editedTimestamp'] = $edited_timestamp;
        $edited_comment['editedTime'] = gmdate( $time_format . ' ' . $date_format, $edited_timestamp );
        $edited_draft = array();
        $edited_draft['thread'] = $edited_comment['thread'];
        $edited_draft['attachmentText'] = ( isset( $edited_comment['attachmentText'] ) ? $edited_comment['attachmentText'] : '' );
        $commentListOld['comments'][$old_timestamp]['draft_edits'] = $edited_draft;
        $commentListOld['comments'][$old_timestamp]['editedTime'] = $edited_comment['editedTime'];
        $commentListOld['comments'][$old_timestamp]['editedTimestamp'] = $edited_comment['editedTimestamp'];
        update_post_meta( $current_post_id, $metaId, $commentListOld );
        // Update Current Drafts.
        $current_drafts = get_post_meta( $current_post_id, '_current_drafts', true );
        $current_drafts = maybe_unserialize( $current_drafts );
        $current_drafts = ( empty($current_drafts) ? array() : $current_drafts );
        $current_drafts['edited'][$metaId][] = $old_timestamp;
        // New code by pooja ///////////
        
        if ( metadata_exists( 'post', $current_post_id, 'th' . $metaId ) ) {
            // update meta if meta key exists
            update_post_meta( $current_post_id, 'th' . $metaId, $edited_comment['editedTimestamp'] );
        } else {
            // create new meta if meta key doesn't exists
            add_post_meta( $current_post_id, 'th' . $metaId, $edited_comment['editedTimestamp'] );
        }
        
        update_post_meta( $current_post_id, '_current_drafts', $current_drafts );
        update_post_meta( $current_post_id, 'mc_updated', $edited_timestamp );
        wp_die();
    }
    
    /**
     * Delete Comment function.
     */
    public function cf_delete_comment()
    {
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $metaId = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_STRING );
        $timestamp = filter_input( INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT );
        $metas = get_post_meta( $current_post_id );
        // Update Current Drafts.
        $current_drafts = get_post_meta( $current_post_id, '_current_drafts', true );
        $current_drafts = maybe_unserialize( $current_drafts );
        $current_drafts = ( empty($current_drafts) ? array() : $current_drafts );
        $current_drafts['deleted'][$metaId][] = $timestamp;
        // Checking if user deleted the recently added comment.
        if ( isset( $current_drafts['deleted'] ) && 0 !== $current_drafts['deleted'] ) {
            if ( isset( $current_drafts['comments'] ) && 0 !== $current_drafts['comments'] ) {
                foreach ( $current_drafts['deleted'] as $el => $timestamps ) {
                    
                    if ( array_key_exists( $el, $current_drafts['comments'] ) ) {
                        $prev_state = $metas[$el][0];
                        $prev_state = maybe_unserialize( $prev_state );
                        // Deleteing comments if users delete comments at the same moment.
                        foreach ( $timestamps as $t ) {
                            $t = intval( $t );
                            $get_key = array_search( $t, $current_drafts['comments'][$el], true );
                            
                            if ( $get_key !== false ) {
                                unset( $current_drafts['comments'][$el][$get_key] );
                                unset( $current_drafts['deleted'][$el][$get_key] );
                                unset( $prev_state['comments'][$t] );
                            }
                        
                        }
                        $metas[$el][0] = maybe_serialize( $prev_state );
                        update_post_meta( $current_post_id, $el, $prev_state );
                    }
                
                }
            }
        }
        update_post_meta( $current_post_id, '_current_drafts', $current_drafts );
        update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
        wp_die();
    }
    
    /**
     * Delete Attchment
     */
    public function cf_delete_attachment()
    {
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $metaId = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_STRING );
        $attachment_text = filter_input( INPUT_POST, 'attachmentText', FILTER_SANITIZE_STRING );
        $metas = get_post_meta( $current_post_id );
        $current_del_data = maybe_unserialize( $metas[$metaId][0] );
        
        if ( isset( $current_del_data['comments'] ) && 0 !== $current_del_data['comments'] ) {
            foreach ( $current_del_data['comments'] as $el => $data ) {
                
                if ( $attachment_text == $current_del_data['comments'][$el]['attachmentText'] ) {
                    unset( $current_del_data['comments'][$el]['attachmentText'] );
                    update_post_meta( $current_post_id, $metaId, $current_del_data );
                }
            
            }
            wp_die();
        }
    
    }
    
    /**
     * Save settings of the plugin.
     */
    public function cf_save_settings()
    {
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        
        if ( isset( $form_data['cf_show_infoboard'] ) ) {
            update_option( 'cf_show_infoboard', $form_data['cf_show_infoboard'] );
        } else {
            delete_option( 'cf_show_infoboard' );
        }
        
        
        if ( isset( $form_data['cf_hide_editorial_column'] ) ) {
            update_option( 'cf_hide_editorial_column', $form_data['cf_hide_editorial_column'] );
        } else {
            delete_option( 'cf_hide_editorial_column' );
        }
        
        
        if ( isset( $form_data['cf_hide_floating_icons'] ) ) {
            update_option( 'cf_hide_floating_icons', $form_data['cf_hide_floating_icons'] );
        } else {
            delete_option( 'cf_hide_floating_icons' );
        }
        
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Save Publishing settings of the plugin.
     */
    public function cf_save_suggestions()
    {
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        
        if ( isset( $form_data['cf_give_alert_message'] ) ) {
            update_option( 'cf_give_alert_message', $form_data['cf_give_alert_message'] );
        } else {
            delete_option( 'cf_give_alert_message' );
        }
        
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Save email notification settings of the plugin.
     */
    public function cf_save_email_notification()
    {
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        
        if ( isset( $form_data['cf_admin_notif'] ) ) {
            update_option( 'cf_admin_notif', $form_data['cf_admin_notif'] );
        } else {
            delete_option( 'cf_admin_notif' );
        }
        
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Save Suggestion mode settings of the plugin.
     */
    public function cf_save_suggestions_mode()
    {
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        
        if ( 'cf_suggestion_specific_post_types' === $form_data['cf_suggestion_mode_option_name'] && empty($form_data['cf_specific_post_types_values']) ) {
            echo  'empty_custom_post_type' ;
            wp_die();
        }
        
        
        if ( 'cf_suggestion_specific_post_categories' === $form_data['cf_suggestion_mode_option_name'] && empty($form_data['cf_specific_post_categories_values']) ) {
            echo  'empty_custom_post_type' ;
            wp_die();
        }
        
        
        if ( isset( $form_data['cf_suggestion_mode_option_name'] ) ) {
            update_option( 'cf_suggestion_mode_option_name', $form_data['cf_suggestion_mode_option_name'] );
        } else {
            delete_option( 'cf_suggestion_mode_option_name' );
        }
        
        
        if ( isset( $form_data['cf_specific_post_categories_values'] ) ) {
            update_option( 'cf_specific_post_categories_values', $form_data['cf_specific_post_categories_values'] );
        } else {
            delete_option( 'cf_specific_post_categories_values' );
        }
        
        
        if ( isset( $form_data['cf_specific_post_types_values'] ) ) {
            update_option( 'cf_specific_post_types_values', $form_data['cf_specific_post_types_values'] );
        } else {
            delete_option( 'cf_specific_post_types_values' );
        }
        
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Save slack intigration settings.
     */
    public function cf_save_slack_intigration()
    {
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        $channel_old = get_option( 'channel_id' );
        
        if ( $channel_old !== $form_data['channels'] ) {
            $new_channel = $form_data['channels'];
            // new method.
            $access_token = get_option( 'access_token' );
            $channel_id = get_option( 'channel_id' );
            $bot_user_id = get_option( 'bot_user_id' );
            $user_access_token = get_option( 'user_access_token' );
            $headers = array(
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $user_access_token,
            );
            $data = array(
                'scope'   => 'channels:write,groups:write,mpim:write,im:write',
                'channel' => $new_channel,
                'users'   => $bot_user_id,
            );
            $api_root = 'https://slack.com/api/';
            $response = Requests::post( $api_root . 'conversations.invite', $headers, $data );
            $channels_response = json_decode( $response->body );
            update_option( 'channel_id', $new_channel );
        } else {
            $new_channel = $form_data['channel'];
        }
        
        
        if ( isset( $form_data['cf_slack_webhook'] ) ) {
            update_option( 'cf_slack_webhook', $form_data['cf_slack_webhook'] );
        } else {
            delete_option( 'cf_slack_webhook' );
        }
        
        
        if ( isset( $new_channel ) ) {
            update_option( 'channel_id', $new_channel );
        } else {
            delete_option( 'channel_id' );
        }
        
        
        if ( isset( $form_data['cf_slack_notification_add_comment'] ) ) {
            update_option( 'cf_slack_notification_add_comment', 1 );
        } else {
            delete_option( 'cf_slack_notification_add_comment' );
        }
        
        
        if ( isset( $form_data['cf_slack_notification_add_suggestion'] ) ) {
            update_option( 'cf_slack_notification_add_suggestion', 1 );
        } else {
            delete_option( 'cf_slack_notification_add_suggestion' );
        }
        
        
        if ( isset( $form_data['cf_slack_notification_resolve_comment'] ) ) {
            update_option( 'cf_slack_notification_resolve_comment', 1 );
        } else {
            delete_option( 'cf_slack_notification_resolve_comment' );
        }
        
        
        if ( isset( $form_data['cf_slack_notification_accept_reject_suggestion'] ) ) {
            update_option( 'cf_slack_notification_accept_reject_suggestion', 1 );
        } else {
            delete_option( 'cf_slack_notification_accept_reject_suggestion' );
        }
        
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Revoke slack intigration.
     */
    public function cf_slack_intigration_revoke()
    {
        $access_token = get_option( 'access_token' );
        $headers = array(
            'Accept' => 'application/json',
        );
        // Add the application id and secret to authenticate the request.
        $options = array(
            'auth' => array( CF_SLACK_CLIENT_ID, CF_SLACK_CLIENT_SECRET ),
        );
        // Add the one-time token to request parameters.
        $data = array(
            'token'         => $access_token,
            'client_id'     => CF_SLACK_CLIENT_ID,
            'client_secret' => CF_SLACK_CLIENT_SECRET,
        );
        $api_root = 'https://slack.com/api/';
        $response = Requests::post(
            $api_root . 'apps.uninstall',
            $headers,
            $data,
            $options
        );
        $json_response = json_decode( $response->body );
        
        if ( true === $json_response->ok ) {
            delete_option( 'cf_slack_webhook' );
            delete_option( 'channel' );
            delete_option( 'default_channel' );
            delete_option( 'access_token' );
            delete_option( 'cf_slack_channels' );
            echo  'ok' ;
        } else {
            delete_option( 'cf_slack_webhook' );
            delete_option( 'access_token' );
            echo  'ok' ;
        }
        
        wp_die();
    }
    
    /**
     * Save permissions of the plugin.
     */
    public function cf_save_permissions()
    {
        global  $wpdb ;
        $form_data = array();
        parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_STRING ), $form_data );
        foreach ( $form_data as $key => $val ) {
            
            if ( '1' === isset( $form_data[$key]['hide_comment'] ) ) {
                unset( $form_data[$key]['add_comment'] );
                unset( $form_data[$key]['resolved_comment'] );
            }
            
            
            if ( '1' === isset( $form_data[$key]['hide_suggestion'] ) ) {
                unset( $form_data[$key]['add_suggestion'] );
                unset( $form_data[$key]['resolved_suggestion'] );
            }
        
        }
        $prev_data = $wpdb->get_results( $wpdb->prepare( "SELECT option_name,option_value FROM {$wpdb->options} WHERE option_name = '%s' ORDER BY option_id DESC" ), 'cf_permissions' );
        // db call ok; no-cache ok
        delete_option( 'cf_permissions', $prev_data );
        update_option( 'cf_permissions', $form_data );
        echo  'saved' ;
        wp_die();
    }
    
    /**
     * Save important details in a localstorage.
     */
    public function cf_store_in_localstorage()
    {
        // Returning show_avatar option to display avatars (or not to).
        $show_avatars = get_option( 'show_avatars' );
        $show_avatars = ( '1' === $show_avatars ? $show_avatars : 0 );
        // Store plugin URL in localstorage so that its easy
        // to get sub site URL in JS files in Multisite environment.
        echo  wp_json_encode( array(
            'showAvatars'         => $show_avatars,
            'commentingPluginUrl' => COMMENTING_BLOCK_URL,
        ) ) ;
        wp_die();
    }
    
    /**
     * Resolve Thread function.
     */
    public function cf_resolve_thread()
    {
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $metaId = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_STRING );
        $timestamp = current_time( 'timestamp' );
        // Update Current Drafts.
        $current_drafts = get_post_meta( $current_post_id, '_current_drafts', true );
        $current_drafts = maybe_unserialize( $current_drafts );
        $current_drafts = ( empty($current_drafts) ? array() : $current_drafts );
        
        if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
            $current_drafts['resolved'][] = $metaId;
        } else {
            $current_drafts['resolved'][] = $metaId;
        }
        
        update_post_meta( $current_post_id, '_current_drafts', $current_drafts );
        update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
        wp_die();
    }
    
    /**
     * Rest API for Gutenberg Commenting Feature.
     */
    public function cf_rest_api()
    {
        register_rest_route( 'cf', 'cf-get-comments-api', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'cf_get_comments' ),
            'permission_callback' => '__return_true',
        ) );
    }
    
    /**
     * Update Autodraft meta on load.
     */
    public function cf_update_meta()
    {
        $current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $autoDraft_ids = ( isset( $_POST['data'] ) ? $_POST['data'] : '' );
        //phpcs:ignore
        update_post_meta( $current_post_id, '_autodraft_ids', $autoDraft_ids );
        wp_die();
    }
    
    /* SGEDIT */
    /**
     * Function is used to fetch stored comments.
     *
     * @return mixed|\WP_REST_Response
     */
    public function cf_get_comments()
    {
        $current_post_id = filter_input( INPUT_GET, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
        $userDetails = array();
        $elID = filter_input( INPUT_GET, 'elID', FILTER_SANITIZE_STRING );
        $commentList = get_post_meta( $current_post_id, $elID, true );
        $superCareerData = maybe_unserialize( $commentList );
        $comments = ( isset( $superCareerData['comments'] ) ? $superCareerData['comments'] : array() );
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        $login_user = wp_get_current_user();
        foreach ( $comments as $t => $val ) {
            
            if ( isset( $val['editedTime'] ) ) {
                $val['editedTime'] = $val['editedTime'];
            } else {
                $val['editedTime'] = '';
            }
            
            $user_info = get_userdata( $val['userData'] );
            $username = ( isset( $user_info->display_name ) ? $user_info->display_name : '' );
            $user_role = ( isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '' );
            $profile_url = get_avatar_url( ( isset( $user_info->user_email ) ? $user_info->user_email : '' ) );
            $thread = $val['thread'];
            $cstatus = ( isset( $val['status'] ) ? $val['status'] : '' );
            $cstatus = ( isset( $val['status'] ) ? $val['status'] : '' );
            $edited_draft = ( isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '' );
            $updatedTime = $val['editedTime'];
            $assigned_text = $val['assigned'];
            $editedTimestamp = ( isset( $val['editedTimestamp'] ) ? $val['editedTimestamp'] : '' );
            $attachment_text = ( isset( $val['attachmentText'] ) ? $val['attachmentText'] : '' );
            $date = gmdate( $time_format . ' ' . $date_format, $t );
            if ( 'deleted' !== $cstatus ) {
                array_push( $userDetails, array(
                    'userName'        => $username,
                    'userRole'        => $user_role,
                    'profileURL'      => $profile_url,
                    'dtTime'          => $date,
                    'thread'          => $thread,
                    'userData'        => $val['userData'],
                    'status'          => $cstatus,
                    'timestamp'       => $t,
                    'editedDraft'     => $edited_draft,
                    'updatedTime'     => $updatedTime,
                    'editedTimestamp' => $editedTimestamp,
                    'assignedText'    => $assigned_text,
                    'attachmentText'  => $attachment_text,
                ) );
            }
        }
        // Get assigned user data
        $assigned_to = null;
        
        if ( isset( $superCareerData['assigned_to'] ) && $superCareerData['assigned_to'] > 0 ) {
            $login_user = wp_get_current_user();
            $user_data = get_user_by( 'ID', $superCareerData['assigned_to'] );
            $displayName = ( $login_user->data->ID == $user_data->ID ? 'You' : $user_data->display_name );
            // phpcs:ignore
            $assigned_to = array(
                'ID'           => $user_data->ID,
                'display_name' => $displayName,
                'user_email'   => $user_data->user_email,
                'avatar'       => get_avatar_url( $user_data->ID, array(
                'size' => 32,
            ) ),
            );
        }
        
        $data = array();
        $data['userDetails'] = $userDetails;
        $data['resolved'] = ( isset( $superCareerData['resolved'] ) && 'true' === $superCareerData['resolved'] ? 'true' : 'false' );
        $data['commentedOnText'] = ( isset( $superCareerData['commentedOnText'] ) ? $superCareerData['commentedOnText'] : '' );
        $data['assignedTo'] = $assigned_to;
        return rest_ensure_response( $data );
    }
    
    /**
     * Fetch User Email List.
     */
    public function cf_get_user_email_list()
    {
        // Check for nonce verification.
        check_ajax_referer( COMMENTING_NONCE, 'nonce' );
        // Get the current post id if not present then return.
        $post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
        if ( $post_id <= 0 ) {
            return;
        }
        // Get user roles who can edit pages/posts./@author Rishi Shah/@since 2.0.4.5
        $roles = array();
        foreach ( wp_roles()->roles as $role_name => $role_obj ) {
            if ( !empty($role_obj['capabilities']['edit_pages']) || !empty($role_obj['capabilities']['edit_posts']) || !empty($role_obj['capabilities']['edit_others_pages']) || !empty($role_obj['capabilities']['edit_others_posts']) ) {
                $roles[] = $role_name;
            }
        }
        // WP User Query.
        $users = new WP_User_Query( array(
            'number'   => 9999,
            'role__in' => $roles,
        ) );
        // Fetch out all user's email.
        $email_list = array();
        $system_users = $users->get_results();
        $options = get_option( 'cf_permissions' );
        foreach ( $system_users as $user ) {
            $needToSortArray = $this->cf_get_reorder_user_role( $user->roles );
            $user->roles = $needToSortArray;
            if ( $user->has_cap( 'edit_posts' ) || $user->has_cap( 'edit_pages' ) ) {
                $email_list[] = array(
                    'ID'                => $user->ID,
                    'role'              => implode( ', ', $user->roles ),
                    'display_name'      => $user->display_name,
                    'full_name'         => $user->display_name,
                    'first_name'        => $user->first_name,
                    'user_email'        => $user->user_email,
                    'avatar'            => get_avatar_url( $user->ID, array(
                    'size' => '24',
                ) ),
                    'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
                    'edit_others_posts' => ( isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '' ),
                );
            }
        }
        $response = $email_list;
        echo  wp_json_encode( $response ) ;
        wp_die();
    }
    
    /**
     * Fetch Matched User Email List.
     */
    public function cf_get_matched_user_email_list()
    {
        // Check for nonce verification.
        check_ajax_referer( COMMENTING_NONCE, 'nonce' );
        // Get the current post id if not present then return.
        $post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
        if ( $post_id <= 0 ) {
            return;
        }
        $niddle = filter_input( INPUT_POST, 'niddle', FILTER_SANITIZE_STRING );
        $niddle = substr( $niddle, 1 );
        
        if ( !empty($niddle) && '@' !== $niddle ) {
            // Get user roles who can edit pages/posts.
            $roles = array();
            foreach ( wp_roles()->roles as $role_name => $role_obj ) {
                if ( !empty($role_obj['capabilities']['edit_pages']) || !empty($role_obj['capabilities']['edit_posts']) || !empty($role_obj['capabilities']['edit_others_pages']) || !empty($role_obj['capabilities']['edit_others_posts']) ) {
                    $roles[] = $role_name;
                }
            }
            $users = new WP_User_Query( array(
                'number'         => 9999,
                'search'         => $niddle . '*',
                'search_columns' => array( 'display_name' ),
                'role__in'       => $roles,
            ) );
            // Fetch out matched user's email.
            $email_list = array();
            $system_users = $users->get_results();
            $options = get_option( 'cf_permissions' );
            foreach ( $system_users as $user ) {
                $needToSortArray = $this->cf_get_reorder_user_role( $user->roles );
                $user->roles = $needToSortArray;
                if ( $user->has_cap( 'edit_posts' ) || $user->has_cap( 'edit_pages' ) ) {
                    $email_list[] = array(
                        'ID'                => $user->ID,
                        'role'              => implode( ', ', $user->roles ),
                        'display_name'      => $user->display_name,
                        'full_name'         => $user->display_name,
                        'first_name'        => $user->first_name,
                        'user_email'        => $user->user_email,
                        'avatar'            => get_avatar_url( $user->ID, array(
                        'size' => '24',
                    ) ),
                        'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
                        'edit_others_posts' => ( isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '' ),
                    );
                }
            }
            $response = $email_list;
        } elseif ( '@' === $niddle ) {
            $this->cf_get_user_email_list();
        } else {
            $response = '';
        }
        
        echo  wp_json_encode( $response ) ;
        wp_die();
    }
    
    /**
     * Get the list of assignable users.
     *
     * @return void
     */
    public function cf_get_assignable_user_list()
    {
        // Check for nonce verification.
        check_ajax_referer( COMMENTING_NONCE, 'nonce' );
        if ( !isset( $_POST['content'] ) || empty($_POST['content']) ) {
            return;
        }
        // Getting the content from the editor to filter out the users.
        $content = wp_kses( $_POST['content'], wp_kses_allowed_html( 'post' ) );
        $pattern = '/[a-z0-9_\\-\\+\\.]+@[a-z0-9\\-]+\\.([a-z]{2,4})(?:\\.[a-z]{2})?/i';
        preg_match_all( $pattern, $content, $matches );
        $user_emails = array_unique( $matches[0] );
        // Remove duplicate entries if any.
        $results = array();
        if ( count( $user_emails ) > 0 ) {
            foreach ( $user_emails as $user_email ) {
                $user_data = get_user_by( 'email', $user_email );
                $needToSortArray = $this->cf_get_reorder_user_role( $user_data->roles );
                $user_data->roles = $needToSortArray;
                $results[] = array(
                    'ID'           => $user_data->ID,
                    'display_name' => $user_data->display_name,
                    'user_email'   => $user_data->user_email,
                    'role'         => implode( ', ', $user_data->roles ),
                    'avatar'       => get_avatar_url( $user_data->ID ),
                );
            }
        }
        echo  wp_json_encode( $results ) ;
        wp_die();
    }
    
    /**
     * Add user role to users WordPress api
     *
     * @return void
     */
    function create_api_user_meta_field_for_userrole()
    {
        register_rest_field( 'user', 'userRole', array(
            'get_callback' => array( $this, 'get_userRole_for_api' ),
            'schema'       => null,
        ) );
    }
    
    /**
     * Get user role by user ID
     *
     * @param object
     * @return string
     */
    function get_userRole_for_api( $object )
    {
        // get the id of the post object array
        $user_id = $object['id'];
        $user_meta = get_userdata( $user_id );
        $user_roles = $user_meta->roles;
        // return the post meta
        return $user_roles[0];
    }

}