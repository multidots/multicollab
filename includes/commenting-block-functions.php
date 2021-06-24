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
function gc_delete_users_transient()
{
    delete_transient('gc_users_list');
}
add_action('user_register', 'gc_delete_users_transient', 10, 1);
add_action('deleted_user', 'gc_delete_users_transient', 10, 3);

/**
 * On edit page loaded this function will trigger
 * and restore the users in the transient.
 *
 * @return void
 */
function gc_after_edit_load()
{
    $post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_STRING);

    if (! empty($post_id)) {
        delete_transient('gc_users_list'); // Delete transient.
        $cache_key      = 'gc_users_list';
        $get_users_list = get_transient($cache_key);
        if (false === $get_users_list) {
            // WP User Query.
            $users = new WP_User_Query([
                'number'   => 9999,
                'role__in' => [ 'Administrator', 'Editor', 'Contributor', 'Author' ],
                'exclude'  => array( get_current_user_id() ),
            ]);

            // Fetch out all user's email.
            $email_list   = [];
            $system_users = $users->get_results();

            foreach ($system_users as $user) {
                if ($user->has_cap('edit_post', $post_id)) {
                    $email_list[] = [
                        'ID'                => $user->ID,
                        'role'              => implode(', ', $user->roles),
                        'display_name'      => $user->display_name,
                        'full_name'         => $user->display_name,
                        'user_email'        => $user->user_email,
                        'avatar'            => get_avatar_url($user->ID, [ 'size' => '24' ]),
                        'profile'           => admin_url("/user-edit.php?user_id={$user->ID}"),
                        'edit_others_posts' => $user->allcaps['edit_others_posts'],
                    ];
                }
            }
            // Set transient
            set_transient($cache_key, $email_list, 24 * HOUR_IN_SECONDS);
        }
    }
}
add_action('admin_init', 'gc_after_edit_load');

/**
 * Reaasigning Deleted User.
 *
 * @param int $id
 * @param int $reassign
 * @param object $user
 * @return void
 */
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

        update_post_meta($result->post_id, $result->meta_key, $values);
    }
}
add_action('delete_user', 'gc_reassigning_deleted_user', 10, 3);
