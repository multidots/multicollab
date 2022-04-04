<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * REST API endpoints functionality of the plugin.
 *
 * @link  #
 * @since 1.3.0
 *
 * @package content-collaboration-inline-commenting
 */
class Commenting_Block_Rest_Routes
{
    private  $namespace ;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->namespace = 'cf/v2';
        add_action( 'rest_api_init', [ $this, 'create_rest_routes' ] );
    }
    
    /**
     * Defines Rest Routes
     *
     * @return void
     */
    public function create_rest_routes()
    {
        register_rest_route( $this->namespace, '/activities', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_activities' ],
            'permission_callback' => [ $this, 'check_activity_permits' ],
        ] );
        register_rest_route( $this->namespace, '/getuserdata', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'getuserdata' ],
            'permission_callback' => [ $this, 'check_activity_permits' ],
        ] );
    }
    
    /**
     * Ensuring Rest Permission
     *
     * @return void
     */
    public function check_activity_permits()
    {
        return true;
    }
    
    /**
     * Callback to send the rest response.
     *
     * @param  array $data
     * @return array
     */
    public function get_activities( $data )
    {
        $current_post_id = intval( $data->get_param( 'postID' ) );
        //phpcs:ignore
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        $timestamp = current_time( 'timestamp' );
        $cf_options = get_option( 'cf_permissions' );
        // Modify Query
        global  $wpdb ;
        global  $current_user ;
        //phpcs:ignore
        $user_id = apply_filters( 'determine_current_user', false );
        wp_set_current_user( $user_id );
        $current_users = get_userdata( $user_id );
        $hide_suggestion = $cf_options[$current_users->roles[0]]['hide_suggestion'];
        $hide_comment = $cf_options[$current_users->roles[0]]['hide_comment'];
        $like = $wpdb->esc_like( '_el' ) . '%';
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id=%d AND meta_key LIKE %s", $current_post_id, $like ), ARRAY_A );
        // phpcs:ignore
        $threads = [];
        foreach ( $results as $row ) {
            $cmnts = [];
            // passing the last activity parameters in thread for displaying in activity center last activity.
            $lastpostid = $row['post_id'];
            $editedLastUser = get_post_meta( $lastpostid, 'last_user_edited', true );
            $lasteditedtime = get_the_modified_time( 'U', $lastpostid );
            $usr_login_name = get_user_by( 'login', $editedLastUser );
            $lasteditedUsersID = $usr_login_name->ID;
            $lasteditedUsersUrl = get_avatar_url( $lasteditedUsersID );
            $elID = str_replace( '_', '', $row['meta_key'] );
            $comments = maybe_unserialize( $row['meta_value'] );
            foreach ( $comments['comments'] as $timestamp => $comment ) {
                $user_info = get_userdata( $comment['userData'] );
                if ( 'draft' !== $comment['status'] && 'permanent_draft' !== $comment['status'] ) {
                    $cmnts[] = [
                        'id'              => $timestamp,
                        'status'          => $comment['status'],
                        'created_at'      => ( isset( $comment['created_at'] ) ? $comment['created_at'] : '' ),
                        'timestamp'       => gmdate( $time_format . ' ' . $date_format, intval( $timestamp ) ),
                        'editedTime'      => ( isset( $comment['editedTime'] ) ? $comment['editedTime'] : '' ),
                        'editedTimestamp' => ( isset( $comment['editedTimestamp'] ) ? $comment['editedTimestamp'] : '' ),
                        'userData'        => [
                        'id'        => ( isset( $user_info->ID ) ? intval( $user_info->ID ) : 0 ),
                        'username'  => ( isset( $user_info->display_name ) ? $user_info->display_name : '' ),
                        'avatarUrl' => get_avatar_url( ( isset( $user_info->user_email ) ? $user_info->user_email : '' ) ),
                        'userRole'  => ( isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '' ),
                    ],
                        'thread'          => ( isset( $comment['thread'] ) ? $comment['thread'] : '' ),
                    ];
                }
            }
            $resolved_by = [];
            if ( isset( $comments['resolved'] ) && 'true' === $comments['resolved'] ) {
                
                if ( isset( $comments['resolved_by'] ) ) {
                    $resolved_user = get_userdata( $comments['resolved_by'] );
                    $resolved_by = [
                        'username'  => $resolved_user->display_name,
                        'avatarUrl' => get_avatar_url( $resolved_user->user_email ),
                    ];
                }
            
            }
            $assigned_user = [];
            
            if ( isset( $comments['assigned_to'] ) ) {
                $assigned_user_info = get_userdata( $comments['assigned_to'] );
                $assigned_user = [
                    'username' => ( $user_id == $assigned_user_info->ID ? 'You' : $assigned_user_info->display_name ),
                    'email'    => $assigned_user_info->user_email,
                ];
            }
            
            
            if ( !empty($cmnts) ) {
                $threads[] = [
                    'elID'              => $elID,
                    'activities'        => $cmnts,
                    'selectedText'      => $comments['commentedOnText'],
                    'resolved'          => ( isset( $comments['resolved'] ) ? $comments['resolved'] : 'false' ),
                    'resolvedTimestamp' => ( isset( $comments['resolved_timestamp'] ) ? gmdate( $time_format . ' ' . $date_format, intval( $comments['resolved_timestamp'] ) ) : '' ),
                    'resolvedBy'        => $resolved_by,
                    'updatedAt'         => ( isset( $comments['updated_at'] ) ? $comments['updated_at'] : '' ),
                    'assignedTo'        => $assigned_user,
                    'blockType'         => ( isset( $comments['blockType'] ) ? $comments['blockType'] : '' ),
                    'lastUser'          => $editedLastUser,
                    'lastEditedTime'    => $lasteditedtime,
                    'lastUsersUrl'      => $lasteditedUsersUrl,
                    'type'              => 'el',
                ];
                $threads = $threads;
            }
        
        }
        if ( !metadata_exists( 'post', $current_post_id, '_autodraft_ids' ) ) {
            // create new meta if meta key doesn't exists
            add_post_meta( $current_post_id, '_autodraft_ids', '' );
        }
        array_multisort( array_column( $threads, 'updatedAt' ), SORT_DESC, $threads );
        $response = [
            'threads' => $threads,
        ];
        return rest_ensure_response( $response );
    }
    
    // custom rest end point to get userdata by user id
    public function getuserdata( $data )
    {
        $userid = intval( $data->get_param( 'userid' ) );
        //phpcs:ignore
        if ( !$userid || $userid === '' ) {
            return null;
        }
        $user_info = get_userdata( $userid );
        
        if ( !empty($user_info) ) {
            $userdata = array(
                'id'        => ( isset( $user_info->ID ) ? intval( $user_info->ID ) : 0 ),
                'username'  => ( isset( $user_info->display_name ) ? $user_info->display_name : '' ),
                'avatarUrl' => get_avatar_url( ( isset( $user_info->user_email ) ? $user_info->user_email : '' ) ),
                'userRole'  => ( isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '' ),
            );
            return rest_ensure_response( $userdata );
        }
        
        return null;
    }

}
new Commenting_Block_Rest_Routes();