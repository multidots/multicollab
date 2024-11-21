<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The generic functions of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */
class Commenting_block_Functions {

	/**
	 * Counts opened comment and total comments in the post/page.
	 *
	 * @param int    $post_ID Post ID.
	 * @param string $content Content of the post/page.
	 * @param array  $metas Array of all meta.
	 *
	 * @return array Details of the open and total comments.
	 */
	public function cf_get_comment_counts( $post_ID, $content = '', $metas = array() ) {

		$metas       = empty( $metas ) ? get_post_meta( $post_ID ) : $metas;
		$content     = get_post( $post_ID )->post_content;
		$total_count = $open_counts = $resolved_counts = 0;

		$keyArr = array();
		if ((is_array($metas) && !empty($metas)) || (is_object($metas) && !empty((array)$metas))) {
			foreach ( $metas as $key => $val ) {
				if ( substr( $key, 0, 3 ) === '_el' ) {
					$key = str_replace( '_el', '', $key );
					if ( strpos( $val[0], 'resolved_timestamp' ) === false && strpos( $content, strval( $key ) ) !== false ) {
						$keyArr[] = maybe_serialize( $key );
						$open_counts ++;
					}
					if ( strpos( $val[0], 'resolved' ) !== false ) {
						$resolved_counts ++;
					}

					if ( strpos( $val[0], 'publish' ) !== false && ( strpos( $val[0], 'resolved' ) !== false || strpos( $content, strval( $key ) ) !== false ) ) {
						$total_count ++;
					}
				}
			}
		}

		// Confirm open counts with the meta value, if not
		// matched, update it. Just for double confirmation.
		$open_cf_count = isset( $metas['open_cf_count'] ) ? $metas['open_cf_count'][0] : 0;
		if ( (int) $open_cf_count !== $open_counts ) {
			update_post_meta( $post_ID, 'open_cf_count', $open_counts );
		}

		$comment_counts                     = array();
		$comment_counts['open_counts']      = $open_counts;
		$comment_counts['resolved_counts']  = $resolved_counts;
		$comment_counts['total_counts']     = $total_count;
		$comment_counts['open_comment_key'] = $keyArr;

		return $comment_counts;
	}

	/**
	 * Counts opened suggestions and total suggestions in the post/page.
	 *
	 * @param int    $post_ID Post ID.
	 * @param string $content Content of the post/page.
	 * @param array  $metas Array of all meta.
	 *
	 * @return array Details of the open and total suggestions.
	 */
	public function cf_get_suggestion_counts( $post_ID ) {
		$content          = get_post( $post_ID )->post_content;
		$suggestions_meta = get_post_meta( $post_ID, '_sb_suggestion_history', true );
		$suggestions_meta = json_decode( $suggestions_meta, true );
		$comment_counts   = array(
			'open_counts'     => 0,
			'accepted_counts' => 0,
			'rejected_counts' => 0,
			'total_counts'    => 0,
		);

		if ( is_array( $suggestions_meta ) && !empty($suggestions_meta) ) {
			$accepted_suggestions = 0;
			$rejected_suggestions = 0;
			$open_suggestions     = 0;
			foreach ( $suggestions_meta as $key => $sg ) {
				$suggestion = ! empty( $sg[0] );
				if ( isset( $suggestion['status'] ) && 'accept' === $suggestion['status']['action'] ) {
					$accepted_suggestions ++;
				} elseif ( isset( $suggestion['status'] ) && 'reject' === $suggestion['status']['action'] ) {
					$rejected_suggestions ++;
				}
				if ( ! isset( $suggestion['status'] ) && strpos( $content, strval( $key ) ) !== false ) {
					$open_suggestions ++;
				}
			}
			$total_suggestions = $open_suggestions + $accepted_suggestions + $rejected_suggestions;

			$comment_counts['open_counts']     = $open_suggestions;
			$comment_counts['accepted_counts'] = $accepted_suggestions;
			$comment_counts['rejected_counts'] = $rejected_suggestions;
			$comment_counts['total_counts']    = $total_suggestions;
		}

		return $comment_counts;
	}


	/**
	 * Display Comment Activity in History Popup.
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function cf_comments_history( $post_id = 0 ) {
		$limit           = filter_input( INPUT_POST, 'limit', FILTER_SANITIZE_NUMBER_INT );
		$limit           = isset( $limit ) ? $limit : 10;
		$current_post_id = isset( $post_id ) ? $post_id : filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );

		$ajax_call = isset( $post_id ) ? 0 : 1;

		$all_meta         = get_post_meta( $current_post_id );
		$userData         = array();
		$prepareDataTable = array();

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$dtTime      = '';

		$total_comments = 0;

		// Add suggestions for dashboard.
		if ( ! $ajax_call ) {
			$suggestions_meta = get_post_meta( $post_id, '_sb_suggestion_history', true );
			$suggestions_meta = json_decode( $suggestions_meta, true );

			if ( is_array( $suggestions_meta ) && !empty($suggestions_meta) ) {
				foreach ( $suggestions_meta as $index => $suggestion ) {
					
					$suggestion[0]['text'] = $this->translate_strings_format( isset( $suggestion[0]['text'] ) ? $suggestion[0]['text'] : '' );

					$text = isset( $suggestion[0]['oldvalue'] ) ? $suggestion[0]['oldvalue'] : '';

					$title = isset( $suggestion[0]['action'] ) ? wp_kses( '<strong>' . __( $suggestion[0]['action'], 'content-collaboration-inline-commenting' ) . '</strong> : ' . __( $suggestion[0]['text'], 'content-collaboration-inline-commenting' ), wp_kses_allowed_html( 'post' ) ) : '';
					$mode  = ( isset( $suggestion[0]['action'] ) && strtolower( $suggestion[0]['action'] ) === 'delete' ) ? 'delete' : 'add';

					// If resolved.
					if ( isset( $suggestion[0]['status'] ) && isset( $suggestion[0]['status']['timestamp'] ) ) {
						$timestamp      = $suggestion[0]['status']['timestamp'];
						$resolve_action = $suggestion[0]['status']['action'];
						$resolve_action = 'accept' === $resolve_action ? '<strong>' . __( 'Suggestion Accepted', 'content-collaboration-inline-commenting' ) . ' </strong>' : '<strong>' . __( 'Suggestion Rejected', 'content-collaboration-inline-commenting' ) . ' </strong>';

						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['status'] = $suggestion[0]['status']['action'];

						$user_id = $suggestion[0]['status']['user'];
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

						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['mode']              = $mode;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['title']             = $title;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['username']          = $username;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['profileURL']        = $profile_url;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['userrole']          = $userrole;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['thread']            = $resolve_action;
						$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_top' ]['commented_on_text'] = $text;
					}
					if ((is_array($suggestion) && !empty($suggestion)) || (is_object($suggestion) && !empty((array)$suggestion))) {
						foreach ( $suggestion as $sg ) {
							// If edited, add as a new item.
							if ( isset( $sg['editedTime'] ) && 'deleted' !== $sg['status'] ) {

								if ( 10 !== strlen( $sg['editedTime'] ) ) {
									// Update $timestamp with edited time, to update side time value.
									$d                = DateTime::createFromFormat(
										'g:i a F j, Y',
										$sg['editedTime'],
										wp_timezone()
									);
									$edited_timestamp = $d ? $d->getTimestamp() : '';
								} else {
									$edited_timestamp = $sg['editedTime'];
								}
								if ( ! empty( $edited_timestamp ) ) {
									$timestamp = $edited_timestamp;
									if ( ! empty( $timestamp ) ) {
										$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
									}

									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['status'] = 'edited';

									$user_id = $sg['uid'];
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

									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['mode']              = $mode;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['title']             = $title;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['username']          = $username;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['profileURL']        = $profile_url;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['userrole']          = $userrole;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['thread']            = $sg['text'];
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['commented_on_text'] = $text;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['dtTime']            = $dtTime;
									$prepareDataTable[ $timestamp ][ 'suggestion_' . $index . '_edited' ]['attachmentText']    = isset( $sg['attachmentText'] ) ? $sg['attachmentText'] : '';
								}
							}

							$timestamp = isset( $sg['timestamp'] ) ? $sg['timestamp'] : '';
							if ( ! empty( $timestamp ) ) {
								$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
							}
							if ( isset( $sg['status'] ) && 'deleted' === $sg['status'] ) {
								$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['status'] = 'deleted comment of';
							} else {
								$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['status'] = isset( $sg['action'] ) ? $sg['action'] : '';
							}

							$user_id = isset( $sg['uid'] ) ? $sg['uid'] : '';
							if ( ! array_key_exists( $user_id, $userData ) ) {
								$user_info = get_userdata( $user_id );

								$userData[ $user_id ]['username']   = $username     = isset( $user_info->display_name ) ? $user_info->display_name : '';
								$userData[ $user_id ]['profileURL'] = $profile_url  = isset( $user_info->user_email ) ? get_avatar_url( $user_info->user_email ) : '';
								$userData[ $user_id ]['userrole']   = $userrole       = isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '';
							} else {
								$username    = $userData[ $user_id ]['username'];
								$profile_url = $userData[ $user_id ]['profileURL'];
								$userrole    = $userData[ $user_id ]['userrole'];
							}

							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['mode']              = $mode;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['title']             = $title;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['username']          = $username;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['profileURL']        = $profile_url;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['userrole']          = $userrole;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['thread']            = ( isset( $sg['mode'] ) && strtolower( $sg['mode'] ) === 'reply' ) ? $sg['text'] : '';
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['commented_on_text'] = $text;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['dtTime']            = $dtTime;
							$prepareDataTable[ $timestamp ][ 'suggestion_' . $index ]['attachmentText']    = isset( $sg['attachmentText'] ) ? $sg['attachmentText'] : '';
						}
					}
				}
			}
		}
		if ((is_array($all_meta) && !empty($all_meta)) || (is_object($all_meta) && !empty((array)$all_meta))) {
			foreach ( $all_meta as $dataid => $v ) {

				if ( strpos( $dataid, '_el' ) === 0 ) {
					$dataid            = str_replace( '_', '', $dataid );
					$v                 = maybe_unserialize( $v[0] );
					$comments          = ! empty( $v['comments'] ) ? $v['comments'] : array();
					$commented_on_text = isset( $v['commentedOnText'] ) ? $v['commentedOnText'] : '';
					$resolved          = isset( $v['resolved'] ) ? $v['resolved'] : 'false';
					$blockType         = isset( $v['blockType'] ) ? '-' . $v['blockType'] : '';

					if ( 'true' === $resolved ) {
						$udata = isset( $v['resolved_by'] ) ? $v['resolved_by'] : 0;
						if ( ! array_key_exists( $udata, $userData ) ) {
							$user_info = get_userdata( $udata );

							$userData[ $udata ]['username']   = $username = $user_info->display_name;
							$userData[ $udata ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
							$userData[ $udata ]['userrole']   = $userrole = isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '';
						} else {
							$username    = $userData[ $udata ]['username'];
							$profile_url = $userData[ $udata ]['profileURL'];
							$userrole    = $userData[ $udata ]['userrole'];
						}

						$timestamp = isset( $v['resolved_timestamp'] ) ? (int) $v['resolved_timestamp'] : '';
						if ( ! empty( $timestamp ) ) {
							 $dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
						}

						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dataid']            = $dataid;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['commented_on_text'] = $commented_on_text;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['username']          = $username;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['profileURL']        = $profile_url;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['userrole']          = $userrole;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dtTime']            = $dtTime;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['blockType']         = $blockType;
						$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['status']            = __( 'resolved thread', 'content-collaboration-inline-commenting' );

					}

					$comment_count = 0;
					if ((is_array($comments) && !empty($comments)) || (is_object($comments) && !empty((array)$comments))) {
						foreach ( $comments as $timestamp => $c ) {
							$cstatus         = 0 === $comment_count ? __( 'commented', 'content-collaboration-inline-commenting' ) : __( 'replied', 'content-collaboration-inline-commenting' );
							$cstatus        .= __( ' on', 'content-collaboration-inline-commenting' );
							$comment_status  = isset( $c['status'] ) ? $c['status'] : '';
							$cattachmentText = isset( $c['attachmentText'] ) ? $c['attachmentText'] : '';
							$cstatus         = 'deleted' === $comment_status ? __( 'deleted comment of', 'content-collaboration-inline-commenting' ) : $cstatus;
							if ( 'publish' === $comment_status && ! empty( $c['editedTime'] ) ) {
								$cstatus = __( 'edited', 'content-collaboration-inline-commenting' );
							}
							// Stop displaying history of comments in draft mode.
							if ( 'draft' === $comment_status || 'permanent_draft' === $comment_status ) {
								continue;
							}

							$udata = ! empty( $c['userData'] ) ? $c['userData'] : '';

							if ( ! array_key_exists( $udata, $userData ) ) {
								$user_info = get_userdata( $udata );

								$userData[ $udata ]['username']   = $username    = isset( $user_info->display_name ) ? $user_info->display_name : '';
								$userData[ $udata ]['profileURL'] = $profile_url = isset( $user_info->user_email ) ? get_avatar_url( $user_info->user_email ) : '';
								$userData[ $udata ]['userrole']   = $userrole       = isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '';
							} else {
								$username    = $userData[ $udata ]['username'];
								$profile_url = $userData[ $udata ]['profileURL'];
								$userrole    = $userData[ $udata ]['userrole'];
							}

							$thread = ! empty( $c['thread'] ) ? $c['thread'] : '';

							$edited_timestamp = isset( $c['editedTimestamp'] ) ? (int) $c['editedTimestamp'] : '';

							if ( ! empty( $edited_timestamp ) ) {
								$dtTime = gmdate( $time_format . ' ' . $date_format, $edited_timestamp );
							} else {
								$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
							}

							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dataid']            = $dataid;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['commented_on_text'] = $commented_on_text;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['username']          = $username;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['profileURL']        = $profile_url;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['userrole']          = $userrole;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['thread']            = $thread;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dtTime']            = $dtTime;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['status']            = $cstatus;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['attachmentText']    = $cattachmentText;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['blockType']         = $blockType;
							$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['resolved']          = $resolved;
							$comment_count ++;
							$total_comments ++;
						}
					}
				}
			}
		}

		krsort( $prepareDataTable, SORT_NUMERIC );
		if ( $ajax_call ) {

			$html = '<div id="history-popup-insider">';
			if ( 0 !== $total_comments ) {

				$count = 0;
				if ((is_array($prepareDataTable) && !empty($prepareDataTable)) || (is_object($prepareDataTable) && !empty((array)$prepareDataTable))) {
					foreach ( $prepareDataTable as $timestamp => $comments ) {
						if ((is_array($comments) && !empty($comments)) || (is_object($comments) && !empty((array)$comments))) {
							foreach ( $comments as $c ) {
								// Limit the number of characters of 'Commented On' Text.
								$limit             = 50;
								$commented_on_text = $c['commented_on_text'];
								if ( $limit < strlen( $commented_on_text ) ) {
									$commented_on_text = substr( $commented_on_text, 0, $limit ) . '...';
								}
								$c['thread'] = isset( $c['thread'] ) ? $c['thread'] : '';
								$count ++;

								$html .= "<div class='user-data-row'>";
								$html .= "<div class='user-data-box'>";
								$html .= "<div class='user-avatar'><img src='" . esc_url( $c['profileURL'] ) . "' alt='" . esc_attr( $c['username'] ) . "'/></div>";
								$html .= "<div class='user-title'>
											<span class='user-name'>" . esc_html( $c['username'] ) . ' ' . esc_html( $c['status'] ) . '</span> ';

								if ( 'deleted comment of' === $c['status'] || __( 'resolved thread', 'content-collaboration-inline-commenting' ) === $c['status'] || 'true' === $c['resolved'] ) {
									$html .= esc_html( $commented_on_text );
								} else {
									$html .= "<a href='javascript:void(0)' data-id='" . esc_attr( $c['dataid'] ) . "' class='user-commented-on'>" . esc_html( $commented_on_text ) . '</a>';
								}

								$html .= "<div class='user-comment'> " . wp_kses( $c['thread'], wp_kses_allowed_html( 'post' ) ) . '</div>
										</div>';

								$html .= "<div class='user-time'>" . esc_html( $c['dtTime'] ) . '</div>';
								$html .= '</div>';
								$html .= '</div>';

								if ( $count >= $limit ) {
									break;
								}
							}
						}
					}
				}
			} else {
				$html .= __( 'No comments found.', 'content-collaboration-inline-commenting' );
			}
			$html .= '</div>';

			$allowed_tags = array(
				'a'    => array(
					'id'      => array(),
					'href'    => array(),
					'target'  => array(),
					'style'   => array(),
					'class'   => array(),
					'data-id' => array(),
				),
				'div'  => array(
					'id'    => array(),
					'class' => array(),
					'style' => array(),
				),
				'img'  => array(
					'src'   => array(),
					'title' => array(),
					'alt'   => array(),
				),
				'span' => array(
					'class' => array(),
					'style' => array(),
				),
			);

			echo wp_kses( $html, $allowed_tags );
			wp_die();

		} else {
			return $prepareDataTable;
		}
	}

	/**
	 * Display Translated String
	 *
	 * @param int $str
	 *
	 * @return string
	 */
	public function translate_strings_format( $str ) {
		$translatedStringFormats = $str;
		if ( strpos( $translatedStringFormats, 'Space (' ) !== false ) {
			$splitString             = explode( ' ', $translatedStringFormats );
			$translatedStringFormats = sprintf( '%s %s %s', __( $splitString[0], 'content-collaboration-inline-commenting' ), isset( $splitString[1] ) ? $splitString[1] : '', __( isset( $splitString[2] ) ? $splitString[2] : '', 'content-collaboration-inline-commenting' ) );
		} elseif ( strpos( $translatedStringFormats, 'Remove Link with URL' ) !== false ) {
			$splitString             = explode( 'Remove Link with URL ', $translatedStringFormats );
			$translatedStringFormats = sprintf( '%s %s', __( 'Remove Link with URL', 'content-collaboration-inline-commenting' ), isset( $splitString[1] ) ? $splitString[1] : '' );
		} elseif ( strpos( $translatedStringFormats, 'with URL' ) !== false ) {
			$splitString             = explode( 'with URL ', $translatedStringFormats );
			$translatedStringFormats = sprintf( '%s %s', __( 'with URL', 'content-collaboration-inline-commenting' ), isset( $splitString[1] ) ? $splitString[1] : '' );
		} elseif ( strpos( $translatedStringFormats, 'Replace' ) !== false ) {
			$splitString             = explode( ' ', $translatedStringFormats );
			$translatedStringFormats = sprintf( '%s %s %s %s', __( $splitString[0], 'content-collaboration-inline-commenting' ), isset( $splitString[1] ) ? $splitString[1] : '', __( isset( $splitString[2] ) ? $splitString[2] : '', 'content-collaboration-inline-commenting' ), isset( $splitString[3] ) ? $splitString[3] : '' );
		} elseif ( strpos( $translatedStringFormats, 'Line Break (' ) !== false ) {
			$splitString             = explode( ' ', $translatedStringFormats );
			$concatBreakedString     = $splitString[0] . ' ' . $splitString[1];
			$translatedStringFormats = sprintf( '%s %s %s', __( $concatBreakedString, 'content-collaboration-inline-commenting' ), isset( $splitString[2] ) ? $splitString[2] : '', __( isset( $splitString[3] ) ? $splitString[3] : '', 'content-collaboration-inline-commenting' ) );
		} elseif ( strpos( $translatedStringFormats, 'Block Alignment' ) !== false ) {
			$splitString             = explode( ' ', wp_strip_all_tags( $translatedStringFormats ) );
			$concatBreakedString     = $splitString[0] . ' ' . $splitString[1];
			$translatedStringFormats = sprintf( '%s <em>%s</em> %s <em>%s</em>', __( $concatBreakedString, 'content-collaboration-inline-commenting' ), __( isset( $splitString[2] ) ? $splitString[2] : '', 'content-collaboration-inline-commenting' ), __( isset( $splitString[3] ) ? $splitString[3] : '', 'content-collaboration-inline-commenting' ), __( isset( $splitString[4] ) ? $splitString[4] : '', 'content-collaboration-inline-commenting' ) );
		} elseif ( strpos( $translatedStringFormats, 'Change Heading' ) !== false ) {
			$splitString             = explode( ' ', wp_strip_all_tags( $translatedStringFormats ) );
			$concatBreakedString     = $splitString[0] . ' ' . $splitString[1];
			$translatedStringFormats = sprintf( '%s <em> %s %s </em> %s <em> %s %s </em>', __( $concatBreakedString, 'content-collaboration-inline-commenting' ), __( isset( $splitString[3] ) ? $splitString[3] : '', 'content-collaboration-inline-commenting' ), isset( $splitString[4] ) ? $splitString[4] : '', __( isset( $splitString[5] ) ? $splitString[5] : '', 'content-collaboration-inline-commenting' ), __( isset( $splitString[7] ) ? $splitString[7] : '', 'content-collaboration-inline-commenting' ), isset( $splitString[3] ) ? $splitString[3] : '' );
		}
		$str = $translatedStringFormats;
		return $str;
	}

	/**
	 * Reorder Userrole According to Default WordPress Roles
	 *
	 * @param array $needToSortArray
	 *
	 * @return array
	 */
	public function cf_get_reorder_user_role( $needToSortArray ) {
		global $wp_roles;
		if ( isset( $wp_roles ) ) {
			$cf_wp_roles     = new WP_Roles();
			$available_roles = $cf_wp_roles->get_names();
		}
		$available_roles = $wp_roles->get_names();
		$order           = array_keys( $available_roles );
		// Add code to reolve VIP error. @author Rishi Shah @since 3.6.1
		if( is_array( $needToSortArray ) && ! empty( $needToSortArray ) ) {
			usort(
				$needToSortArray,
				function( $key1, $key2 ) use ( $order, $needToSortArray ) {
					if(isset($needToSortArray[ $key1 ]) && isset($needToSortArray[ $key2 ])){
						return ( array_search( $needToSortArray[ $key1 ], $order, true ) <=> array_search( $needToSortArray[ $key2 ], $order, true ) );
					}
				}
			);
			return $needToSortArray;
		} else {
			return $needToSortArray;
		}
	}

	/**
	 * Return all posts categories.
	 *
	 * @return void
	 */
	public function cf_get_posts_categories() {

		$cf_categories = get_categories(
			array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			)
		);

		return $cf_categories;

	}
	/**
	 * Return all custom post types.
	 *
	 * @return object
	 */
	public function cf_get_posts_types() {

		$args = array(
			'public' => true,
		);

		$output   = 'objects';
		$operator = 'and';

		$post_types = get_post_types( $args, $output, $operator );

		return $post_types;

	}

	/**
	 * Return all general setting value.
	 *
	 * @return array
	 */
	public function cf_get_general_settings() {

		$cf_edd = new CF_EDD();
		if ( $cf_edd->is__premium_only() ) {
			$multicollab_plan = esc_html( strtoupper( $cf_edd->get_plan_name() ) );
		} else {
			$multicollab_plan = esc_html( 'FREE', 'content-collaboration-inline-commenting' );
		}

		$cf_gen_hide_multicollab_comment = get_option( 'cf_hide_editorial_column' );
		$cf_gen_hide_floating_icons      = get_option( 'cf_hide_floating_icons' );
		$cf_gen_hide_save_draft_comment  = get_option( 'cf_show_infoboard' );
		$cf_admin_notification = get_option( 'cf_admin_notif' );

		$cf_suggestion_option  = get_option( 'cf_give_alert_message' );

		if ( 'VIP' === $multicollab_plan || 'PRO' === $multicollab_plan ) {
			$cf_suggestion_mode_option_name = get_option( 'cf_suggestion_mode_option_name' );
			if ( 'cf_suggestion_specific_post_categories' === $cf_suggestion_mode_option_name ) {
				$cf_specific_post_categories_values = get_option( 'cf_specific_post_categories_values' );
			} else {
				$cf_specific_post_categories_values = '';
			}

			if ( 'cf_suggestion_specific_post_types' === $cf_suggestion_mode_option_name ) {
				$cf_specific_post_types_values = get_option( 'cf_specific_post_types_values' );
			} else {
				$cf_specific_post_types_values = '';
			}

			$cf_slack_channels = get_option( 'cf_slack_channels' );
			$channel_id        = get_option( 'channel_id' );

			$cf_slack_default_channel = '';
			if ((is_array($cf_slack_channels) && !empty($cf_slack_channels)) || (is_object($cf_slack_channels) && !empty((array)$cf_slack_channels))) {
				foreach ( $cf_slack_channels as $channel ) {
					if ( $channel->id === $channel_id ) {
						$cf_slack_default_channel = $channel->name;
					}
				}
			}

			$cf_slack_notification_add_comment              = get_option( 'cf_slack_notification_add_comment' );
			$cf_slack_notification_add_suggestion           = get_option( 'cf_slack_notification_add_suggestion' );
			$cf_slack_notification_resolve_comment          = get_option( 'cf_slack_notification_resolve_comment' );
			$cf_slack_notification_accept_reject_suggestion = get_option( 'cf_slack_notification_accept_reject_suggestion' );

			$cf_lang = get_bloginfo( 'language' );

		} else {
			$cf_suggestion_mode_option_name                 = '';
			$cf_specific_post_types_values                  = '';
			$cf_specific_post_categories_values             = '';
			$cf_slack_default_channel                       = '';
			$cf_slack_notification_add_comment              = '';
			$cf_slack_notification_add_suggestion           = '';
			$cf_slack_notification_resolve_comment          = '';
			$cf_slack_notification_accept_reject_suggestion = '';
			$cf_lang                                        = '';
		}

		$cf_permissions_options = get_option( 'cf_permissions' );

		$cf_plugin_general_setting = array(
			'cf_gen_hide_multicollab_comment'       => $cf_gen_hide_multicollab_comment,
			'cf_gen_hide_save_draft_comment'        => $cf_gen_hide_save_draft_comment,
			'cf_gen_hide_floating_icons'            => $cf_gen_hide_floating_icons,
			'cf_admin_notification'                 => $cf_admin_notification,
			'cf-suggestion-option '                  => $cf-suggestion-option ,
			'cf_suggestion_mode_option_name'        => $cf_suggestion_mode_option_name,
			'cf_specific_post_types_values'         => $cf_specific_post_types_values,
			'cf_specific_post_categories_values'    => $cf_specific_post_categories_values,
			'cf_slack_default_channel'              => $cf_slack_default_channel,
			'cf_slack_notification_add_comment'     => $cf_slack_notification_add_comment,
			'cf_slack_notification_add_suggestion'  => $cf_slack_notification_add_suggestion,
			'cf_slack_notification_resolve_comment' => $cf_slack_notification_resolve_comment,
			'cf_slack_notification_accept_reject_suggestion' => $cf_slack_notification_accept_reject_suggestion,
			'cf_multilingual_language'              => $cf_lang,
			'cf_permissions_options'                => $cf_permissions_options,
		);

		return $cf_plugin_general_setting;

	}

	/**
	 * Return all open comment key when post updated
	 */
	public function cf_open_comment_key() {

		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$commentsKey     = get_post_meta( $current_post_id, 'open_cf_comment_key', true );

		echo wp_json_encode(
			array(
				'comments_key' => $commentsKey,
			)
		);

		wp_die();

	}

}
