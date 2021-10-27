<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * Get comments Activities.
 *
 * @link       #
 * @since      1.3.0
 *
 * @package    content-collaboration-inline-commenting
 */
class Commenting_Block_Activities extends Commenting_block_Functions
{
    /**
     * Get the latest activities of comments.
     *
     * @return array Activity data.
     */
    public function cf_get_activities()
    {
        $view = filter_input( INPUT_GET, "view", FILTER_SANITIZE_STRING );
        // Page/Post Activity.
        
        if ( 'post-activity' === $view ) {
            return $this->cf_get_cpt_activity();
        } else {
            return $this->cf_get_site_activity();
        }
    
    }
    
    public function cf_get_cpt_activity()
    {
        global  $wpdb ;
        // Page number adjustments.
        $paged = filter_input( INPUT_GET, "paged", FILTER_SANITIZE_NUMBER_INT );
        $paged = ( $paged ? $paged : 1 );
        $items_per_page = 10;
        // Post type filter.
        $cpt_filter = filter_input( INPUT_GET, "cpt", FILTER_SANITIZE_STRING );
        // Months filter.
        $m_filter = filter_input( INPUT_GET, "m", FILTER_SANITIZE_STRING );
        
        if ( $m_filter ) {
            $year = substr( $m_filter, 0, 4 );
            $month = substr( $m_filter, 4, 2 );
        }
        
        $query_replace = 'SELECT p.*';
        $query = "{$query_replace} FROM {$wpdb->posts} as p\n\t\t\t\tLEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id";
        $cat_filter = filter_input( INPUT_GET, "cat", FILTER_SANITIZE_STRING );
        if ( $cat_filter ) {
            $query .= " LEFT JOIN {$wpdb->term_relationships} as tr ON pm.post_id = tr.object_id";
        }
        $query .= " WHERE pm.meta_key = 'mc_updated'";
        if ( $cat_filter ) {
            $query .= " AND tr.term_taxonomy_id = {$cat_filter}";
        }
        if ( $cpt_filter ) {
            $query .= " AND p.post_type = '{$cpt_filter}'";
        }
        if ( $m_filter ) {
            $query .= " AND MONTH(p.post_modified) = " . $month . " AND YEAR(p.post_modified) = " . $year;
        }
        $offset = $items_per_page * ($paged - 1);
        $limit_query = "LIMIT {$items_per_page} OFFSET {$offset}";
        // Get total counts.
        $query_all = $query . " ORDER BY meta_value DESC";
        $query_all = str_replace( $query_replace, 'SELECT count(p.ID)', $query_all );
        $found_posts = $wpdb->get_var(
            //phpcs:ignore
            $wpdb->prepare( $query_all )
        );
        //phpcs:ignore
        wp_reset_query();
        $order = filter_input( INPUT_GET, "order", FILTER_SANITIZE_STRING );
        $order = $order ?? 'DESC';
        $query .= " ORDER BY pm.meta_value {$order} {$limit_query}";
        $result = $wpdb->get_results(
            //phpcs:ignore
            $wpdb->prepare( $query )
        );
        //phpcs:ignore
        wp_reset_query();
        // The Loop
        $activities_data = [];
        if ( $result ) {
            foreach ( $result as $item ) {
                $current_post_id = $item->ID;
                $prepareDataTable = $this->cf_comments_history( $current_post_id );
                $prepare_data = [];
                // Title.
                $link = get_edit_post_link( $current_post_id );
                $title = get_the_title( $current_post_id );
                $post_content = get_post( $current_post_id )->post_content;
                $prepare_data['title'] = '<a href="' . $link . '">' . $title . '</a>';
                // Get comments counts.
                $comments_count_data = $this->cf_get_comment_counts( $current_post_id );
                // Get suggestions counts.
                $suggestions_count_data = $this->cf_get_suggestion_counts( $current_post_id );
                // Merge counts.
                $comments_count_data['open_counts'] = $comments_count_data['open_counts'] + $suggestions_count_data['open_counts'];
                $comments_count_data['total_counts'] = $comments_count_data['total_counts'] + $suggestions_count_data['total_counts'];
                $resolved_total = $comments_count_data['resolved_counts'] + $suggestions_count_data['accepted_counts'] + $suggestions_count_data['rejected_counts'];
                $autodraft_total = $comments_count_data['total_counts'] - ($comments_count_data['open_counts'] + $resolved_total);
                $comments_count = '<div class="open-comments">' . $comments_count_data['open_counts'] . ' <span>Open</span></div>';
                $comments_count .= '<div class="resolved-comments">' . $resolved_total . ' <span>Resolved</span></div>';
                $comments_count .= '<div class="total-comments">' . ($comments_count_data['total_counts'] - $autodraft_total) . ' <span>Total</span></div>';
                $prepare_data['comments_count'] = $comments_count;
                // Activities.
                $collaborators = [];
                $activity_text = '';
                $activity_limit = 3;
                $activity_count = 0;
                foreach ( $prepareDataTable as $comments ) {
                    foreach ( $comments as $item ) {
                        if ( isset( $item['dataid'] ) && strpos( $post_content, strval( $item['dataid'] ) ) === false ) {
                            continue;
                        }
                        $activity_count++;
                        if ( $activity_limit < $activity_count ) {
                            break;
                        }
                        $status = '';
                        switch ( $item['status'] ) {
                            case 'commented on':
                                $status = 'New Comment';
                                break;
                            case 'replied on':
                            case 'reply':
                                $status = 'Replied';
                                break;
                            case 'deleted comment of':
                                $status = 'Deleted';
                                break;
                            case 'resolved thread':
                                $status = 'Marked as Resolved';
                            case 'accept':
                            case 'reject':
                                $status = 'Resolved';
                                break;
                            case 'edited':
                                $status = 'Edited';
                                break;
                            default:
                                $status = $item['status'];
                        }
                        $max_str_length = 150;
                        if ( isset( $item['title'] ) ) {
                            $item['commented_on_text'] = ( strlen( $item['title'] ) < $max_str_length ? $item['title'] : substr( $item['title'], 0, $max_str_length ) . '...' );
                        }
                        // Last updated.
                        $thread = ( strlen( wp_strip_all_tags( $item['thread'] ) ) < $max_str_length ? $item['thread'] : substr( $item['thread'], 0, $max_str_length ) . '...' );
                        $collaborators[] = "<span class='tbl-user-avatar'><img src=" . esc_url( $item['profileURL'] ) . "  alt='" . esc_attr( $item['username'] ) . "' />" . esc_html( $item['username'] ) . "</span>";
                        $single_collaborator = "<div class='user-data-header'> <div class='user-avatar'><img src=" . esc_url( $item['profileURL'] ) . "  alt='" . esc_attr( $item['username'] ) . "' /></div><div class='user-display-name'><span class='user-name'>" . esc_html( $item['username'] ) . "<span class='tooltip'>" . esc_html( $item['userrole'] ) . "</span> </span><time class='user-commented-date'>" . $item['dtTime'] . "</time></div></div>";
                        $activity_text .= "<div class='single-activity'><span class='single-activity-status'>" . $status . " By</span>" . $single_collaborator;
                        $commented_text_class = ( empty($item['commented_on_text']) ? 'empty' : '' );
                        $commented_text_class .= ( isset( $item['mode'] ) ? ' ' . $item['mode'] : '' );
                        $commented_text_class .= ( isset( $item['blockType'] ) ? $item['blockType'] : '' );
                        $activity_text .= "<div class='tbl-user-activity-left'><blockquote class='tbl-user-commented-icon{$commented_text_class}'>" . wp_kses( $item['commented_on_text'], wp_kses_allowed_html( 'post' ) ) . "</blockquote>";
                        $activity_text .= "<span class='tbl-user-comment'> " . wp_kses( $thread, wp_kses_allowed_html( 'post' ) ) . "</span></div></div>";
                    }
                }
                $prepare_data['activities'] = $activity_text;
                // Collaborators.
                $collaborators = array_unique( $collaborators );
                $collaborators = implode( $collaborators );
                $prepare_data['collaborators'] = $collaborators;
                if ( !empty($activity_text) ) {
                    $activities_data[] = $prepare_data;
                }
            }
        }
        return [
            'activities_data' => $activities_data,
            'items_per_page'  => $items_per_page,
            'found_posts'     => $found_posts,
        ];
    }
    
    public function cf_get_site_activity()
    {
        global  $wpdb ;
        $cpt = filter_input( INPUT_GET, "cpt", FILTER_SANITIZE_STRING );
        $cat = filter_input( INPUT_GET, "cat", FILTER_SANITIZE_STRING );
        $m = filter_input( INPUT_GET, "m", FILTER_SANITIZE_STRING );
        $month = substr( $m, 5, 1 );
        $year = substr( $m, 0, 4 );
        $action = filter_input( INPUT_POST, "action", FILTER_SANITIZE_STRING );
        $offset = filter_input( INPUT_POST, "pointer", FILTER_VALIDATE_INT );
        $date = filter_input( INPUT_POST, "date", FILTER_SANITIZE_STRING );
        $post_id = filter_input( INPUT_POST, "postID", FILTER_VALIDATE_INT );
        $category_id = filter_input( INPUT_POST, "categoryID", FILTER_VALIDATE_INT );
        //will be used in the included file.
        $board_position = filter_input( INPUT_POST, "boardPosition", FILTER_SANITIZE_STRING );
        //phpcs:ignore
        //will be used in the included file.
        $displayed_dates = filter_input( INPUT_POST, "displayedDates", FILTER_SANITIZE_STRING );
        //phpcs:ignore
        $displayed_dates = explode( '|', $displayed_dates );
        $offset = $offset ?? 0;
        $limit = 10;
        //will be used in the included file.
        $new_pointer = $offset + $limit;
        //phpcs:ignore
        $cat = ( empty($cat) ? $category_id : $cat );
        $cpt = ( empty($cpt) ? sanitize_text_field( ( isset( $_POST['cpt'] ) ? $_POST['cpt'] : '' ) ) : $cpt );
        //phpcs:ignore
        $autodrat_id_str = $this->cf_find_autodraft_id();
        $query = "SELECT pm.*  FROM {$wpdb->postmeta} as pm ";
        $query .= " LEFT JOIN {$wpdb->posts} as p ON pm.post_id = p.ID ";
        if ( $cat ) {
            $query .= " LEFT JOIN {$wpdb->term_relationships} as tr ON pm.post_id = tr.object_id";
        }
        $query .= " WHERE pm.meta_key LIKE 'th_el%' AND LENGTH(pm.meta_value) = 10 AND  pm.meta_key NOT IN ({$autodrat_id_str}))";
        if ( $cat ) {
            $query .= " AND tr.\t = {$cat}";
        }
        if ( $cpt ) {
            $query .= " AND p.post_type = '{$cpt}'";
        }
        if ( $m ) {
            $query .= " AND MONTH(p.post_modified) = {$month} AND YEAR(p.post_modified) = {$year}";
        }
        $query .= " ORDER BY pm.meta_value DESC LIMIT {$limit} OFFSET {$offset}";
        $all_metas = $wpdb->get_results( $wpdb->prepare( $query ) );
        //db call ok; no-cache ok
        wp_reset_query();
        // If no data found.
        if ( 0 === count( $all_metas ) ) {
            
            if ( 0 === $offset ) {
                // If no data found on load.
                return "<p>No activities found.</p>";
            } else {
                // If no data found on load.
                echo  "<p>No more activities.</p>" ;
                wp_die();
            }
        
        }
        // If load more call.
        
        if ( $action ) {
            // Check if same date is continued.
            $main_timestamp = $all_metas[0]->meta_value;
            $date_displaying = gmdate( 'l, F j', $main_timestamp );
            $date_continue_in_loadmore = $date_displaying === $date;
            // phpcs:ignore
            // Check if same post continued.
            $main_post_id = (int) $all_metas[0]->post_id;
            //will be used in the included file.
            $post_continue_in_loadmore = $main_post_id === $post_id;
            //phpcs:ignore
            $this->cf_activities = $all_metas;
            // Website Activities.
            require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-website-activity.php';
            wp_die();
        } else {
            // On load call.
            return $all_metas;
        }
    
    }
    
    public function cf_find_autodraft_id()
    {
        global  $wpdb ;
        $autodraft_ids = array();
        $select = "SELECT pm.meta_value as ids  FROM {$wpdb->postmeta} as pm";
        $select .= " LEFT JOIN {$wpdb->posts} as p ON pm.post_id = p.ID";
        $select .= " WHERE pm.meta_key = '_autodraft_ids'";
        $autodrafts_id = $wpdb->get_results( $wpdb->prepare( $select ) );
        //db call ok; no-cache ok
        foreach ( $autodrafts_id as $id ) {
            $ids = maybe_unserialize( $id->ids );
            if ( isset( $ids ) && !empty($ids) ) {
                foreach ( $ids as $id ) {
                    array_push( $autodraft_ids, $id );
                }
            }
        }
        $autodrat_id_str = "'" . implode( "', '", $autodraft_ids ) . "'";
        return $autodrat_id_str;
    }
    
    public function cf_get_detailed_activity()
    {
        global  $wpdb ;
        // will be used in the included file.
        $activity_view = 'detail-view';
        //phpcs:ignore
        $post_id = filter_input( INPUT_POST, "postID", FILTER_VALIDATE_INT );
        // will (also) be used in the included file.
        $all_metas = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE 'th_%' AND post_id = %d ORDER BY meta_value DESC", $post_id ) );
        //db call ok; no-cache ok
        wp_reset_query();
        $this->cf_activities = $all_metas;
        // Website Activities.
        require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-website-activity.php';
        wp_die();
    }
    
    public function cf_migrate_to_pro_now()
    {
        global  $wpdb ;
        // th_'s value should be 10 digits / if not, need to be migrated.
        // count suggestions board from suggestions single meta (_sb_suggestion_history)
        // + count comments boards (_el*) === count th_ board having exact value of 10 digits (th_*)
        // if both values are not same, migration is required.
        $post_id = filter_input( INPUT_POST, "postID", FILTER_VALIDATE_INT );
        // will (also) be used in the included file.
        $suggestions_included = filter_input( INPUT_POST, "suggestionsIncluded", FILTER_VALIDATE_BOOLEAN );
        // will (also) be used in the included file.
        $pending = $data = [];
        $migrated_post = 0;
        // If post id is 0, means need to find post ids.
        
        if ( 0 === $post_id ) {
            if ( $suggestions_included ) {
                $where_suggestions = "OR pm.meta_key = '_sb_suggestion_history'";
            }
            $post_ids = $wpdb->get_col(
                //phpcs:ignore
                $wpdb->prepare( "SELECT DISTINCT p.ID FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as pm ON pm.post_id = p.ID WHERE pm.meta_key LIKE '_el%' {$where_suggestions}" )
            );
            //phpcs:ignore
            foreach ( $post_ids as $post_id ) {
                $total_suggestions = 0;
                
                if ( $suggestions_included ) {
                    $suggestions_meta = get_post_meta( $post_id, '_sb_suggestion_history', true );
                    $suggestions_meta = json_decode( $suggestions_meta );
                    $total_suggestions = count( (array) $suggestions_meta );
                }
                
                $total_comments = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_el%' AND post_id = %d", $post_id ) );
                //db call ok; no-cache ok
                $total_should_be = $total_suggestions + $total_comments;
                $data[$post_id]['total_comments'] = $total_comments;
                $data[$post_id]['total_suggestions'] = $total_suggestions;
                $data[$post_id]['total_should_be'] = $total_should_be;
                $th_exists = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta}\n\t\t\t\t\t\tWHERE meta_key LIKE 'th_%'\n\t\t\t\t\t\t  AND LENGTH ( meta_value ) = 10\n\t\t\t\t\t\t  AND post_id = %d", $post_id ) );
                //db call ok; no-cache ok
                $data[$post_id]['th_exists'] = $th_exists;
                
                if ( $th_exists >= $total_should_be ) {
                    $data[$post_id]['status'] = 'done';
                } else {
                    $data[$post_id]['status'] = 'pending';
                    $pending[] = $post_id;
                }
            
            }
            $pending = implode( ',', $pending );
            wp_reset_query();
        } else {
            // Got the id to migrate.
            
            if ( $suggestions_included ) {
                $suggestions_meta = get_post_meta( $post_id, '_sb_suggestion_history', true );
                $suggestions_meta = json_decode( $suggestions_meta );
                // Create/Update 'th_*' metas for suggestions.
                foreach ( $suggestions_meta as $uid => $item ) {
                    $timestamp = $item[0]->updated_at;
                    update_post_meta( $post_id, 'th_' . $uid, $timestamp );
                }
            }
            
            // Create/Update 'th_*' metas for comments.
            $all_comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE '_el%' AND post_id = %d", $post_id ) );
            //db call ok; no-cache ok
            foreach ( $all_comments as $item ) {
                $key = 'th' . $item->meta_key;
                $data = maybe_unserialize( $item->meta_value );
                update_post_meta( $post_id, $key, $data['updated_at'] );
            }
            $migrated_post = $post_id;
        }
        
        echo  wp_json_encode( array(
            'data'         => $data,
            'pending'      => $pending,
            'migratedPost' => $migrated_post,
        ) ) ;
        wp_die();
    }

}