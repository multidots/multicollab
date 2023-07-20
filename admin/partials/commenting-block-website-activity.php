<?php
/**
 * Website Activities.
 */
$limit                     = 10;
$new_pointer   = $new_pointer ?? $limit;        					//phpcs:ignore
$activity_view = $activity_view ?? 'list-view'; 					//phpcs:ignore
$its_loadmore              = isset( $action );  					//phpcs:ignore
$date_continue_in_loadmore = $date_continue_in_loadmore ?? false;   //phpcs:ignore
$post_continue_in_loadmore = $post_continue_in_loadmore ?? false;   //phpcs:ignore


// If no data found, an HTML (message) will be returned instead of an array.
if ( ! is_array( $this->cf_activities ) ) { //phpcs:ignore
	echo $this->cf_activities; 				//phpcs:ignore

	return;
}

?>
<!-- <div class="board-items-rows<?php echo $date_continue_in_loadmore ? ' date_continued' : ''; ?><?php echo $post_continue_in_loadmore ? ' post_continued' : ''; ?>"> -->
<?php
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );

$final_activity = $userData = []; //phpcs:ignore
$date_displayed = $displayed_dates ?? []; //phpcs:ignore

if ( 'detail-view' === $activity_view ) {
	?>
	<div class="board-detail-head">
		<a href="javascript::void(0)" id="activity-go-back"><?php esc_html_e( 'Go Back', 'content-collaboration-inline-commenting' ); ?></a>
	   
		<h2 id="main-detailed-view">
            <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); //phpcs:ignore ?>"> 
				<?php esc_html_e( get_the_title( $post_id ) ); //phpcs:ignore ?>
			</a>
		</h2>
	</div>
	<?php
}

$post_ids           = array();
$new_date_displayed = true;
$board_position     = $board_position ?? 'board-pos-right';  //phpcs:ignore  
$side_time_format   = 'g:i a F j, Y';
$count_iterations   = 0;
$previousPostId     = '';

if ( ! empty( $this->cf_activities ) ) {
	foreach ( $this->cf_activities as $meta_single ) { //phpcs:ignore

		if ( isset( $meta_single->post_id ) ) {

			$main_timestamp          = $meta_single->meta_value;
			$post_id                 = $meta_single->post_id; //phpcs:ignore  
			$title_displayed_already = in_array( $post_id, $post_ids, true );
			$post_ids[]              = $post_id;
			$post_title              = get_the_title( $post_id );
			$meta_key                = $meta_single->meta_key;

			// Skip first iteration to avoid variable value in first iteration, Then update continuation variables.

			if ( 0 !== $count_iterations ) {
				$post_continue_in_loadmore = $title_displayed_already ? true : false;
			}

			$board_type = '';
			// pooja
			$show_reply = 0;
			if ( strpos( $meta_key, 'el' ) !== false ) {
				// If its for comments.
				$board_type       = 'comments';
				$meta_key         = str_replace( 'th', '', $meta_key );
				$thread_edit_link = get_edit_post_link( $post_id ) . '&current_url=' . str_replace( '_', '', $meta_key );
				$meta             = get_post_meta( $meta_single->post_id, $meta_key, true );
			} else {
				// If its for suggestions.
				$board_type       = 'suggestions';
				$suggestion_key   = str_replace( 'th_', '', $meta_key );
				$meta_key         = '_sb_suggestion_history';
				$thread_edit_link = get_edit_post_link( $post_id ) . '&current_url=sg' . str_replace( '_', '', $suggestion_key );
				$suggestions_meta = get_post_meta( $meta_single->post_id, $meta_key, true );
				$suggestions_meta = json_decode( $suggestions_meta, true );
				$meta             = array();
				$meta['comments'] = ! empty( $suggestions_meta[ $suggestion_key ] ) ? $suggestions_meta[ $suggestion_key ] : '';

				// Handle resolved suggestions.
				$top_suggestion = ! empty( $suggestions_meta[ $suggestion_key ][0] ) ? $suggestions_meta[ $suggestion_key ][0] : '';
				if ( isset( $top_suggestion['status'] ) && isset( $top_suggestion['status']['timestamp'] ) ) {
					$meta['resolved_by']        = $top_suggestion['status']['user'];
					$meta['resolved_timestamp'] = $top_suggestion['status']['timestamp'];
					$meta['resolved_text']      = 'accept' === $top_suggestion['status']['action'] ? __( 'Suggestion Accepted', 'content-collaboration-inline-commenting' ) : __( 'Suggestion Rejected', 'content-collaboration-inline-commenting' );
				}
			}

			$date_displaying   = date_i18n( 'l, F j', $main_timestamp );
			$now               = time();
			$beginning_of_week = strtotime( 'last Monday', $now ); // Gives you the time at the BEGINNING of the week
			$yesterday         = strtotime( '-1 day', $now );

			// Display only the day, if its from current week.
			if ( $main_timestamp > $beginning_of_week ) {
				if ( gmdate( 'Y-m-d', $main_timestamp ) === gmdate( 'Y-m-d', $now ) ) {
					$date_displaying = __( 'Today', 'content-collaboration-inline-commenting' );
				} elseif ( gmdate( 'Y-m-d', $main_timestamp ) === gmdate( 'Y-m-d', $yesterday ) ) {
					$date_displaying = __( 'Yesterday', 'content-collaboration-inline-commenting' );
				}
			}

			// Skip first iteration to avoid variable value in first iteration, Then update continuation variables.
			if ( 0 !== count( $date_displayed ) ) {
				$date_continue_in_loadmore = in_array( $date_displaying, $date_displayed, true ) ? true : false;
			}

			// Check if date should be displayed.
			if ( ! in_array( $date_displaying, $date_displayed, true ) && ! $date_continue_in_loadmore ) {
				$date_displayed[] = $date_displaying;

				echo "<h4 class='board-items-day'>" . esc_html( $date_displaying ) . '</h4>';
				$new_date_displayed = true;

				// Force the post title to be displayed.
				$title_displayed_already = false;
			} else {
				$new_date_displayed = false;
			}

			// Update board position.
			if (
			( $its_loadmore && ( ! $date_continue_in_loadmore || ! $post_continue_in_loadmore ) )
			|| ( ! $its_loadmore && ( ! $title_displayed_already || $new_date_displayed ) )
			|| ( $previousPostId !== $post_id )
			) {
				$board_position = 'board-pos-left' === $board_position ? 'board-pos-right' : 'board-pos-left';
			}

			// fix to separate comment by post id
			if ( $previousPostId !== $post_id ) {
				$title_displayed_already   = false;
				$post_continue_in_loadmore = false;
			}

			$all_comments = isset( $meta['comments'] ) ? $meta['comments'] : '';
			if ( is_array( $all_comments ) ) {
				// Add mark as resolved item.
				if ( isset( $meta['resolved_by'] ) ) {
					$all_comments['resolved']['resolved_by']        = $meta['resolved_by'];
					$all_comments['resolved']['resolved_timestamp'] = $meta['resolved_timestamp'];
					$all_comments['resolved']['resolved_text']      = isset( $meta['resolved_text'] ) ? __( $meta['resolved_text'], 'content-collaboration-inline-commenting' ) : __( 'Marked as Resolved', 'content-collaboration-inline-commenting' );
				}


				if ( isset( $meta['assigned_to'] ) ) {
					$user_data   = get_user_by( 'ID', $meta['assigned_to'] );
					$login_user  = wp_get_current_user();
					$displayName = ($login_user->data->ID == $user_data->ID) ? 'You' : $user_data->display_name; // phpcs:ignore

					$assigned_to = array(
						'ID'           => $user_data->ID,
						'display_name' => $displayName,
						'user_email'   => $user_data->user_email,
						'avatar'       => get_avatar_url( $user_data->ID, array( 'size' => 32 ) ),
					);
				}

				?>
			<div class="board-cnt-main 
				<?php
				esc_attr_e( $board_position );
				echo $date_continue_in_loadmore ? ' date_continued' : '';
				?>
				 <?php echo $post_continue_in_loadmore ? 'post_continued' : ''; ?>">
				<div class="board-cnt-inner">
					<div class="board-cnt-wrap">
						<?php if ( ! $title_displayed_already ) { ?>
							<h4 class="board-items-page"><a href="javascript:void(0)" class="show_activity_details" data-id="<?php esc_attr_e( $post_id ); ?>"><?php esc_html_e( $post_title ); ?></a></h4>
						<?php } ?>
						<div class="user-data-row">
							<?php
							// Display thread comments.
							$comments_counts = 0;
							$side_time       = $side_time_stamp = '';

							$index                 = 0;
							$showallcommentbtnflag = 0;
							foreach ( $all_comments as $timestamp => $comment ) {  //phpcs:ignore
								$comments_counts ++;

								if ( 1 === $comments_counts ) {
									$side_time_stamp = $timestamp;
								}

								$timestamp = 'comments' === $board_type || 'resolved' === $timestamp ? $timestamp : $comment['timestamp'];

								$box_class = 'user-data-box';
								if ( 'resolved' === $timestamp ) {
									$box_class          .= ' cf-mark-as-resolved';
									$timestamp           = $comment['resolved_timestamp'];
									$comment['userData'] = $comment['resolved_by'];  //phpcs:ignore
								} elseif ( 1 !== $comments_counts ) {
									$box_class .= ' user-reply';
								}

								$user_id = isset( $comment['userData'] ) ? $comment['userData'] : $comment['uid'];

								if ( ! array_key_exists( $user_id, $userData ) ) {
									$user_info = get_userdata( $user_id );

									$userData[ $user_id ]['username']   = $username = $user_info->display_name;
									$userData[ $user_id ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
									$userData[ $user_id ]['userrole']   = $userrole = isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '';

								} else {
									$username    = $userData[ $user_id ]['username'];
									$profile_url = $userData[ $user_id ]['profileURL'];
									$userrole    = $userData[ $user_id ]['userrole'];

								}

								if ( isset( $comment['thread'] ) && 'comments' === $board_type ) {
									$thread = ( isset( $comment['status'] ) && 'deleted' === $comment['status'] ) ? '<del>' . $comment['thread'] . '</del>' : $comment['thread'];
								} elseif ( isset( $comment['action'] ) && 'reply' === $comment['action'] ) {
									$thread = 'deleted' === $comment['status'] ? '<del>' . $comment['text'] . '</del>' : $comment['text'];
								} else {
									$thread = '';
								}

								$date_time = date_i18n( $time_format . ' ' . $date_format, $timestamp );
								?>
								<div class="<?php echo esc_attr( $box_class ); ?>" style="<?php echo ( $index > 6 && ! isset( $comment['resolved_by'] ) ) ? 'display:none' : ''; ?>">
									<div class="user-data">
										<div class="user-data-header">
											<div class="user-avatar">
												<img src="<?php echo esc_url( $profile_url ); ?>" alt="<?php echo esc_attr( $username ); ?>">
											</div>
											<div class="user-display-name">
												<span class="user-name"><?php echo esc_html( $username ); ?><span class="tooltip"><?php echo esc_html( $userrole ); ?></span></span>
												<time class="user-commented-date"><?php echo esc_html( $date_time ); ?></time>
											</div>
										</div>
										<?php if ( isset( $comment['resolved_timestamp'] ) ) { ?>
											<div class="user-comment <?php echo 'comments' === $board_type ? 'mark-resolved-icon' : ''; ?>">
												<strong><?php esc_html_e( $comment['resolved_text'], 'content-collaboration-inline-commenting' ); ?></strong>
											</div>
										<?php } else { ?>
											<div class="user-data-wrapper">
												<?php if ( 1 === $comments_counts && 'comments' === $board_type ) { ?>
													<div class="user-commented-on">
														<blockquote class="user-commented-icon<?php echo ( isset( $meta['blockType'] ) ) ? esc_attr( '-' . $meta['blockType'] ) : ''; ?>">
															<a  class="user-commented-on show-all <?php echo ( '' !== $meta['commentedOnText'] && strlen( wp_strip_all_tags( $meta['commentedOnText'] ) ) > 45 ) ? 'js-hide' : ''; ?>" href="javascript:void(0)" 
															>
																<?php
																	echo wp_kses( $meta['commentedOnText'], wp_kses_allowed_html( 'post' ) );
																?>
															</a>
															<a  class="user-commented-on show-less <?php echo ( '' !== $meta['commentedOnText'] && strlen( wp_strip_all_tags( $meta['commentedOnText'] ) ) <= 45 ) ? 'js-hide' : ''; ?>" href="javascript:void(0)" >
																<?php
																	echo (strlen(wp_strip_all_tags($meta['commentedOnText'])) > 45 ) ? esc_html(substr($meta['commentedOnText'],0,45).'...') : esc_html($meta['commentedOnText']); //phpcs:ignore  
																?>
															</a>
															<?php
															if ( '' !== $meta['commentedOnText'] && strlen( wp_strip_all_tags( $meta['commentedOnText'] ) ) > 45 ) {
																?>
																	<a  
																	href="javascript:void(0)" 
																	class="cf-show-more"   
																>
																<?php echo esc_html__( 'Show all', 'content-collaboration-inline-commenting' ); ?>
																</a>
																	<?php
															}
															?>
														</blockquote>
													</div>
													<?php
												} elseif ( 'suggestions' === $board_type && isset( $comment['action'] ) && 'reply' !== $comment['action'] ) {

														$commentingFunction = new Commenting_block_Functions();
														$comment['text']    = $commentingFunction->translate_strings_format( $comment['text'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
														echo "test"; exit;
													?>
													<div class="user-commented-on <?php echo ( isset( $comment['action'] ) && 'delete' === strtolower( $comment['action'] ) ) ? 'delete' : 'add'; ?>">
														<blockquote class="user-commented-icon">
															<?php

															echo '<strong>' . esc_html( __( $comment['action'], 'content-collaboration-inline-commenting' ) ) . '</strong> : ';
															?>
															<a class="user-commented-on show-all <?php echo ( '' !== $comment['text'] && strlen( wp_strip_all_tags( $comment['text'] ) ) > 45 ) ? 'js-hide' : ''; ?>" href="javascript:void(0)" >
																<?php echo esc_html( wp_strip_all_tags( __( $comment['text'], 'content-collaboration-inline-commenting' ) ) ); ?>
															</a>
															<a class="user-commented-on show-less <?php echo ( '' !== $comment['text'] && strlen( wp_strip_all_tags( $comment['text'] ) ) <= 45 ) ? 'js-hide' : ''; ?>" href="javascript:void(0)" > 
																<?php echo (strlen(wp_strip_all_tags($comment['text'])) > 45 ) ? substr(wp_strip_all_tags($comment['text']),0,45).'...' : wp_strip_all_tags($comment['text']) ;  //phpcs:ignore ?> 
															</a>
															<?php if ( '' !== $comment['text'] && strlen( wp_strip_all_tags( $comment['text'] ) ) > 45 ) { ?>
																<a href="javascript:void(0)" class="cf-show-more">
																<?php echo esc_html__( 'Show all', 'content-collaboration-inline-commenting' ); ?>
																</a>
															<?php } ?>
														</blockquote>
													</div>
												<?php } ?>
												<div class="user-comment">
													<span><?php echo wp_kses( $thread, wp_kses_allowed_html( 'post' ) ); ?></span>
												</div>
												<?php if ( 1 === $comments_counts && 'comments' === $board_type && isset( $meta['assigned_to'] ) ) { ?>
												<div class="user-assigned-to"><span class="icon"></span><span class="assign-avatar-data"><?php esc_html_e( 'Assigned to', 'content-collaboration-inline-commenting' ); ?><a href="mailto:<?php echo esc_attr( $assigned_to['user_email'] ); ?>" title="<?php echo esc_attr( $assigned_to['display_name'] ); ?>"> <?php echo esc_html_e( $assigned_to['display_name'], 'content-collaboration-inline-commenting' ); ?></a></span></div>
												<?php } ?>
												<?php
												if ( isset( $comment['editedTime'] ) && 'deleted' !== $comment['status'] ) {
													if ( isset( $comment['editedTimestamp'] ) ) {
														$timestamp = $comment['editedTimestamp'];
													} elseif ( 10 !== strlen( $comment['editedTime'] ) ) {
														// Update $timestamp with edited time, to update side time value.
														$d         = DateTime::createFromFormat(
															$side_time_format,
															$comment['editedTime'],
															wp_timezone()
														);
														$timestamp = $d ? $d->getTimestamp() : $timestamp;
													} else {
														$timestamp = $comment['editedTime'];
													}
													$edited_time = date_i18n( $side_time_format, $timestamp );
													?>
													<time class="user-commented-date user-inner-box-time">(<?php esc_html_e( 'edited', 'content-collaboration-inline-commenting' ); ?> <?php esc_html_e( $edited_time ); ?>)</time>
												<?php } ?>
											</div>
											<?php
										}
										?>
									</div>
								</div> 
								<?php

								if ( $timestamp > $side_time_stamp ) {
									$side_time_stamp = $timestamp;
								}

								if ( ! $showallcommentbtnflag && $index > 6 && count( $all_comments ) > 7 ) {
									$showallcommentbtnflag = true;
									$count                 = isset( $all_comments['resolved'] ) ? count( $all_comments ) - 2 : count( $all_comments ) - 1;
									?>
									<div class="show-all-comments"><?php echo esc_html__( 'Show all', 'content-collaboration-inline-commenting' ); ?> <?php echo esc_html( $count ); ?> <?php echo esc_html__( 'replies', 'content-collaboration-inline-commenting' ); ?></div>
									<?php
								}

								$index ++;
							}

							$side_time = date_i18n( $time_format, $side_time_stamp );

							
							if( 'comments' == $board_type){ //phpcs:ignore

								$show_reply = 1;
							}


							if ( ! isset($meta['resolved_by']) && 1 == $show_reply ) { //phpcs:ignore
								?>
								<div class="user-action">
									<a href="<?php echo esc_url( $thread_edit_link ); ?>" class="user-cmnt-reply"><?php esc_html_e( 'Reply', 'content-collaboration-inline-commenting' ); ?> <span class="tooltip"> <?php esc_html_e( 'Reply to this comment', 'content-collaboration-inline-commenting' ); ?> </span></a>
								</div>
							<?php } ?>
							<span class="side-time"><?php esc_html_e( $side_time ); ?></span>
						</div>
					</div>
				</div>
			</div>
				<?php
			}
			$count_iterations ++;
			$previousPostId = $post_id;
		}
	}
}

?>
<?php
if ( 'list-view' === $activity_view && ! empty( $main_timestamp ) && count( $this->cf_activities ) === $limit ) {
	$latest_displaying_date = gmdate( 'l, F j', $main_timestamp );
	$term_id                = '';
	if( isset($_GET['cat']) ){  //phpcs:ignore  
		$cat_id  = term_exists( intval($_GET['cat']), 'category' ) ; //phpcs:ignore  
		$term_id = ( $cat_id !== 0 ) ? $cat_id['term_id'] : null;
	}

	$cpt  = isset($_GET['cpt']);//phpcs:ignore
	?>
	 
	<a href="javascript:void(0)" class="load-more-activity" data-pointer="<?php echo esc_attr( $new_pointer ); ?>" data-date="<?php echo esc_attr( $latest_displaying_date ); ?>" data-post="<?php echo esc_attr( $post_id ); ?>"  data-category ="<?php echo esc_attr( $term_id ); ?>" data-cpt= "<?php echo esc_attr( $cpt ); ?>" data-board-position="<?php echo esc_attr( $board_position ); ?>" style="opacity: 1">Load More</a>
<?php } ?>
