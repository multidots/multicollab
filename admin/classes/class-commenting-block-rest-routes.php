<?php
// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * REST API endpoints functionality of the plugin.
 *
 * @link       #
 * @since      1.3.0
 *
 * @package    content-collaboration-inline-commenting
 */

class Commenting_Block_Rest_Routes
{
    private $namespace;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->namespace = 'cf/v2';
        add_action('rest_api_init', [ $this, 'create_rest_routes' ]);
    }

    /**
     * Defines Rest Routes
     *
     * @return void
     */
    public function create_rest_routes()
    {
        register_rest_route($this->namespace, '/activities', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_activities' ],
            'permission_callback' => [ $this, 'check_activity_permits' ],
        ]);
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
     * @param array $data
     * @return array
     */
    public function get_activities($data)
    {
        $current_post_id = intval($data->get_param('postID'));
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $timestamp = current_time('timestamp');

        // Modify Query
        global $wpdb;
        $like   = $wpdb->esc_like('_el') . '%';

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id=%d AND meta_key LIKE %s", $current_post_id, $like), ARRAY_A); // phpcs:ignore

        $threads = [];
        foreach ($results as $row) {
            $cmnts = [];
            $elID = str_replace('_', '', $row['meta_key']);
            $comments = maybe_unserialize($row['meta_value']);
            foreach ($comments['comments'] as $timestamp => $comment) {
                $user_info = get_userdata($comment['userData']);
                if ('draft' !== $comment['status'] && 'permanent_draft' !== $comment['status']) {
                    if (isset($comment['editedTime'])) {
                        $comment['editedTime']=	$comment['editedTime'];
                    } else {
                        $comment['editedTime']='';
                    }

                    $cmnts[] = [
                        'id'         => $timestamp,
                        'status'     => $comment['status'],
                        'timestamp'  => gmdate($time_format . ' ' . $date_format, intval($timestamp)),
                        'editedTime' =>  $comment['editedTime'],
                        'userData'   => [
                            'id'        => isset($user_info->ID) ? intval($user_info->ID) : 0,
                            'username'  => isset($user_info->display_name) ? $user_info->display_name : '',
                            'avatarUrl' => get_avatar_url(isset($user_info->user_email) ? $user_info->user_email : ''),
                            'userRole'  => isset($user_info->roles) ? implode(', ', $user_info->roles) : '',
                        ],
                        'thread'     =>  isset($comment['thread']) ? $comment['thread'] : ''
                    ];
                }
            }

            $resolved_by = [];
            if (isset($comments['resolved']) && 'true' === $comments['resolved']) {
                if (isset($comments['resolved_by'])) {
                    $resolved_user = get_userdata($comments['resolved_by']);
                    $resolved_by = [
                        'username' => $resolved_user->display_name,
                        'avatarUrl' => get_avatar_url($resolved_user->user_email),
                    ];
                }
            }

            $assigned_user = [];
            if (isset($comments['assigned_to'])) {
                $assigned_user_info = get_userdata($comments['assigned_to']);
                $assigned_user = [
                    'username' => $assigned_user_info->display_name,
                    'email' => $assigned_user_info->user_email,
                ];
            }
            if (! empty($cmnts)) {
                $threads[] = [
                    'elID'              => $elID,
                    'activities'        => $cmnts,
                    'selectedText'      => $comments['commentedOnText'],
                    'resolved'          => isset($comments['resolved']) ? $comments['resolved'] : 'false',
                    'resolvedTimestamp' => isset($comments['resolved_timestamp']) ? gmdate($time_format . ' ' . $date_format, intval($comments['resolved_timestamp'])): '',
                    'resolvedBy'        => $resolved_by,
                    'updatedAt'			=> isset($comments['updated_at']) ? $comments['updated_at'] : '',
                    'assignedTo'		=> $assigned_user,
                    
                ];
            }
        }
        
        array_multisort(array_column($threads, 'updatedAt'), SORT_DESC, $threads);
       
        $response = [
            'threads' => $threads,
        ];
      
        return rest_ensure_response($response);
    }
}
new Commenting_Block_Rest_Routes();
