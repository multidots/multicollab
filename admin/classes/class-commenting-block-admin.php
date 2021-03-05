<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    content-collaboration-inline-commenting
 */
class Commenting_block_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initiate Email Class Object.
	 */
	private $email_class;

	private static $allowed_attribute_tags = ['content', 'citation', 'caption', 'value', 'values', 'fileName', 'text', 'downloadButtonText'];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Publish Comments on status change.
		add_action( 'post_updated', array( $this, 'cf_post_status_changes' ), 10, 3 );

		// Update caps for authors and contributors.
		add_filter( 'admin_init', array( $this, 'cf_custom_caps' ) );

		// Allow caps for Multisite environment.
		add_filter( 'map_meta_cap', array( $this, 'cf_add_unfiltered_html_capability_to_users' ), 1, 3 );

		// Action to add setting page.
		add_action( 'admin_menu', array( $this, 'cf_add_setting_page' ) );

		// Adding new column to the posts list.
		add_filter( 'manage_posts_columns', array( $this, 'cf_columns_head' ) );
		add_filter( 'manage_pages_columns', array( $this, 'cf_columns_head' ) );

		// Adding content in a column of posts list.
		add_action( 'manage_posts_custom_column', array( $this, 'cf_columns_content' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'cf_columns_content' ), 10, 2 );

		// Make custom comments columns sortable.
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'cf_sortable_comments_column' ) );
		add_filter( 'manage_edit-page_sortable_columns', array( $this, 'cf_sortable_comments_column' ) );

		// Set query to sort.
		add_action( 'pre_get_posts', array( $this, 'cf_sort_custom_column_query' ) );
	}

	/**
	 * Make custom comments columns sortable.
	 *
	 * @param array $columns List of columns.
	 *
	 * @return mixed Updated list of columns.
	 */
	public function cf_sortable_comments_column( $columns ) {
		$columns['cb_comments_status'] = 'sort_by_cf_comments';

		return $columns;
	}

	/**
	 * Set query to sort.
	 *
	 * @param object $query Query object.
	 */
	public function cf_sort_custom_column_query( $query ) {
		$orderby = $query->get( 'orderby' );

		if ( 'sort_by_cf_comments' === $orderby && $query->is_main_query() ) {

			$meta_query = array(
				'relation' => 'OR',
				array(
					'key'     => 'open_cf_count',
					'compare' => 'NOT EXISTS', // see note above
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
	public function cf_columns_head( $defaults ) {
		$defaults['cb_comments_status'] = '<img id="cf-column-img" src="' . COMMENTING_BLOCK_URL . '/admin/images/commenting-logo.svg" width=17/>' . ' Editorial Comments';

		return $defaults;
	}

	/**
	 * Add content in a new column of the posts list.
	 *
	 * @param string $column_name Column name.
	 * @param int $post_ID Post ID.
	 */
	public function cf_columns_content( $column_name, $post_ID ) {

		if ( $column_name === 'cb_comments_status' ) {

			$comment_counts = $this->cf_get_comment_counts( $post_ID );
			if ( 0 !== $comment_counts['total_counts'] ) {
				echo '<a href="' . esc_url( get_edit_post_link( $post_ID ) ) . '">' . esc_html( $comment_counts['open_counts'] . '/' . $comment_counts['total_counts'] ) . '</a>';
			} else {
				echo '-';
			}
		}
	}

	/**
	 * Counts opened comment and total comments in the post/page.
	 *
	 * @param int $post_ID Post ID.
	 * @param string $content Content of the post/page.
	 * @param array $metas Array of all meta.
	 *
	 * @return array Details of the open and total comments.
	 */
	public function cf_get_comment_counts( $post_ID, $content = '', $metas = array() ) {

		$metas       = 0 === count( $metas ) ? get_post_meta( $post_ID ) : $metas;
		$content     = empty( $content ) ? get_the_content( $post_ID ) : $content;
		$total_count = $open_counts = 0;

		foreach ( $metas as $key => $val ) {
			if ( substr( $key, 0, 3 ) === '_el' ) {
				$key = str_replace( '_el', '', $key );
				if ( strpos( $val[0], 'resolved' ) === false && strpos( $content, $key ) !== false ) {
					$open_counts ++;
				}
				$total_count ++;
			}
		}

		// Confirm open counts with the meta value, if not
		// matched, update it. Just for double confirmation.
		$open_cf_count = isset($metas['open_cf_count']) ? $metas['open_cf_count'][0] : 0;
		if ( (int) $open_cf_count !== $open_counts ) {
			update_post_meta( $post_ID, 'open_cf_count', $open_counts );
		}

		$comment_counts                 = array();
		$comment_counts['open_counts']  = $open_counts;
		$comment_counts['total_counts'] = $total_count;

		return $comment_counts;
	}

	/**
	 * Add Setting Page.
	 *
	 */
	public function cf_add_setting_page() {

		$settings_title = 'Multicollab';

		//Adding a new admin page for MYS
		add_menu_page(
			__( esc_html( $settings_title ), 'content-collaboration-inline-commenting' ),
			__( esc_html( $settings_title ), 'content-collaboration-inline-commenting' ),
			'manage_options',
			'editorial-comments',
			array( $this, 'cf_settings_callback' ),
			COMMENTING_BLOCK_URL . '/admin/images/menu-icon.svg'
		);
	}

	/**
	 * Plugin setting page callback function.
	 *
	 */
	public function cf_settings_callback() {
		require_once( COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-settings-page.php' ); // phpcs:ignore
	}

	/**
	 * Allowed Administrator, editor, author and contributor user to enter unfiltered html.
	 *
	 * @param array $caps All caps.
	 * @param string $cap Cap in a loop.
	 * @param int $user_id User ID.
	 *
	 * @return array Caps.
	 */
	public function cf_add_unfiltered_html_capability_to_users( $caps, $cap, $user_id ) {
		if ( 'unfiltered_html' === $cap && ( user_can( $user_id, 'administrator' ) || user_can( $user_id, 'editor' ) || user_can( $user_id, 'author' ) || user_can( $user_id, 'contributor' ) ) ) {
			$caps = array( 'unfiltered_html' );
		}

		return $caps;
	}

	/**
	 * Add capabilities to user roles to make 'mdspan' tag unfiltered.
	 *
	 * @return bool True always.
	 */
	public function cf_custom_caps() {

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
	public function cf_get_user() {

		$curr_user = wp_get_current_user();
		$userID    = $curr_user->ID;
		$userName  = $curr_user->display_name;
		$userURL   = get_avatar_url( $userID );
		$userRole  = get_userdata( $userID )->roles[0];

		echo wp_json_encode( array( 'id' => $userID, 'name' => $userName, 'role' => $userRole, 'url' => $userURL ) );
		wp_die();

	}

	/**
	 * @param int $post_ID Post ID.
	 * @param object/string $post Post Content.
	 * @param string $update Status of the update.
	 */
	public function cf_post_status_changes( $post_ID, $post ) {
		$metas      = get_post_meta( $post_ID );
		$p_content  = is_object( $post ) ? $post->post_content: $post;
		$p_link     = get_edit_post_link( $post_ID );
		$p_title    = get_the_title( $post_ID );
		$site_title = get_bloginfo( 'name' );
		$html       = '';

		// Get current user details.
		$curr_user                 = wp_get_current_user();
		$user_id                   = $curr_user->ID;
		$current_user_email        = $curr_user->user_email;
		$current_user_display_name = $curr_user->display_name;

		// Publish drafts from the 'current_drafts' stack.
		$current_drafts    = $metas['current_drafts'][0];
		$current_drafts    = maybe_unserialize( $current_drafts );
		$current_timestamp = current_time( 'timestamp' );
		// Initiate Email Class Object.
		$this->cf_initiate_email_class();

		// Checking if user deleted the recently added comment.
		if( isset( $current_drafts['deleted'] ) && 0 !== $current_drafts['deleted'] ) {
			if( isset( $current_drafts['comments'] ) && 0 !== $current_drafts['comments'] ) {
				foreach( $current_drafts['deleted'] as $el => $timestamps ) {
					if( array_key_exists( $el, $current_drafts['comments'] ) ) {
						$prev_state = $metas[$el][0];
						$prev_state = maybe_unserialize( $prev_state );

						foreach( $timestamps as $t ) {
							$t = intval( $t );
							$get_key = array_search( $t, $current_drafts['comments'][$el], true );
							if( $get_key !== false ) {
								unset( $current_drafts['comments'][$el][$get_key] );
							}

							unset( $prev_state['comments'][$t] );
						}
						$metas[$el][0] = maybe_serialize( $prev_state );
					}
				}
			}
		}

		// Publish Deleted Comments. (i.e. finally delete them.)
		if ( isset( $current_drafts['deleted'] ) && 0 !== count( $current_drafts['deleted'] ) ) {
			$deleted_drafts = $current_drafts['deleted'];

			foreach ( $deleted_drafts as $el => $timestamps ) {
				$prev_state = $metas[ $el ][0];
				$prev_state = maybe_unserialize( $prev_state );

				foreach ( $timestamps as $key=>$t ) {
					$local_time = current_datetime();
					$deleted_timestamp = $local_time->getTimestamp() + $local_time->getOffset() + $key;
					// Update the timestamp of deleted comment.
					$previous_comment = $prev_state['comments'][ $t ];
					if( ! empty( $previous_comment ) ) {
						$prev_state['comments'][ $deleted_timestamp ]           = $previous_comment;
						$prev_state['comments'][ $deleted_timestamp ]['status'] = 'deleted';
					}
				}
				update_post_meta( $post_ID, $el, $prev_state );
				$metas[ $el ][0] = maybe_serialize( $prev_state );
			}
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
					$prev_state   = $metas[ $el ][0];
					$prev_state   = maybe_unserialize( $prev_state );
					$new_comments = array();
					foreach ( $drafts as $d ) {
						$prev_state['comments'][ $d ]['status'] = 'publish';
						$new_comments[]                         = $d;
					}
					update_post_meta( $post_ID, $el, $prev_state );
					$metas[ $el ][0] = maybe_serialize( $prev_state );
				}
			}
		}

		// Publish Edited Comments.
		if ( isset( $current_drafts['edited'] ) && 0 !== count( $current_drafts['edited'] ) ) {
			$edited_drafts = $current_drafts['edited'];

			foreach ( $edited_drafts as $el => $timestamps ) {
				$prev_state = $metas[ $el ][0];
				$prev_state = maybe_unserialize( $prev_state );

				foreach ( $timestamps as $t ) {

					$edited_draft = $prev_state['comments'][ $t ]['draft_edits']['thread'];
					if ( ! empty( $edited_draft ) ) {
						$prev_state['comments'][ $t ]['thread'] = $edited_draft;
					}

					// Change status to publish.
					$prev_state['comments'][ $t ]['status'] = 'publish';

					// Remove comment from edited_draft.
					unset( $prev_state['comments'][ $t ]['draft_edits']['thread'] );

				}
				update_post_meta( $post_ID, $el, $prev_state );
				$metas[ $el ][0] = maybe_serialize( $prev_state );
			}
		}

		// Mark Resolved Threads.
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$resolved_drafts = $current_drafts['resolved'];

			$html .= '<div class="comment-box comment-resolved" style="background:#fff;width:70%;font-family:Arial,serif;padding-top:40px;">';
			$html .= '<div class="comment-box-header" style="margin-bottom:30px;border:1px solid #eee;border-radius:20px;padding:30px;">';
			$html .= '<p style="margin:0;padding-bottom:20px;"><a href="mailto:' . esc_attr( $current_user_email ) . '" class="" style="color:#4B1BCE;text-decoration:none;">' . esc_html( $current_user_display_name ) . '</a> ' . __( 'has resolved the following thread.', 'content-collaboration-inline-commenting' ) . '</p>';
			if ( ! empty( $p_title ) ) {
				$html .= '<h2 class="comment-page-title" style="font-size:20px;margin:0;"><a href="' . esc_url( $p_link ) . '" style="color:#4B1BCE;text-decoration:underline;font-size:20px;">' . esc_html( $p_title ) . '</a></h2></div>';
			}
			$html .= '<div class="comment-box-body" style="border:1px solid #eee;border-radius:20px;padding:30px;">';
			$html .= '<h3 class="head-with-icon" style="margin:0;padding-bottom:20px;font-family:Roboto,Arial,sans-serif;font-weight:600;">';
			$html .= '<span class="icon-resolved" style="padding-right:10px;vertical-align:middle;">';
			$html .= '<img src="'.esc_url( COMMENTING_BLOCK_URL . 'admin/images/icon-check-fill.png' ).'" alt="Resolved" />';
			$html .= '</span>' . __( ' Resolved Thread Comments', 'content-collaboration-inline-commenting' );
			$html .= '</h3>';

			foreach ( $resolved_drafts as $el ) {
				$prev_state                       = $metas[ $el ][0];
				$prev_state                       = maybe_unserialize( $prev_state );
				$prev_state['resolved']           = 'true';
				$prev_state['resolved_timestamp'] = $current_timestamp;
				$prev_state['resolved_by']        = $user_id;

				if( array_key_exists( $el, $current_drafts['comments'] ) ) {
					// If any published comment is there.
					$can_delete = false;
					if( count( $prev_state['comments'] ) > 0 ) {
						foreach( $prev_state['comments'] as $prev_state_cmnt ) {
							if( 'draft' === $prev_state_cmnt['status'] ) {
								$can_delete = true;
							}
							break;
						}
						if( true === $can_delete ) {
							delete_post_meta( $post_ID, $el );
						} else {
							$unpublished_comments = $current_drafts['comments'][$el];
							if( ! empty( $unpublished_comments ) ) {
								foreach( $unpublished_comments as $unpublished_comment ) {
									$prev_state['comments'][$unpublished_comment]['status'] = 'publish';
								}
							}
							update_post_meta( $post_ID, $el, $prev_state );
						}
					}
				} else {
					update_post_meta( $post_ID, $el, $prev_state );
				}

				// Send Email.
				$comments          = get_post_meta( $post_ID, $el, true );
				$commented_on_text = $comments['commentedOnText'];
				$list_of_comments  = isset( $comments['comments'] ) ? $comments['comments'] : '';

				// Notify users about the resolved thread.
				$this->email_class->cf_email_resolved_thread( array(
					'html'                      => $html,
					'post_title'                => $p_title,
					'site_title'                => $site_title,
					'current_user_email'        => $current_user_email,
					'current_user_display_name' => $current_user_display_name,
					'commented_on_text'         => $commented_on_text,
					'list_of_comments'          => $list_of_comments
				) );
			}
		}

		// Flush Current Drafts Stack.
		update_post_meta( $post_ID, 'current_drafts', '' );

		// New Comments from past should be moved to 'permanent_drafts'.
		$permanent_drafts = $metas['permanent_drafts'][0];
		$permanent_drafts = maybe_unserialize( $permanent_drafts );
		if ( isset( $permanent_drafts['comments'] ) && 0 !== count( $permanent_drafts['comments'] ) ) {
			$permanent_drafts = $permanent_drafts['comments'];
			foreach ( $permanent_drafts as $el => $drafts ) {
				$prev_state = $metas[ $el ][0];
				$prev_state = maybe_unserialize( $prev_state );
				foreach ( $drafts as $d ) {
					$prev_state['comments'][ $d ]['status'] = 'permanent_draft';
				}
				update_post_meta( $post_ID, $el, $prev_state );
				$metas[ $el ][0] = maybe_serialize( $prev_state );
			}
		}

		// Flush Permanent Drafts Stack.
		update_post_meta( $post_ID, 'permanent_drafts', '' );

		// Update open comments count.
		$comment_counts = $this->cf_get_comment_counts( $post_ID, $p_content, $metas );
		update_post_meta( $post_ID, 'open_cf_count', $comment_counts['open_counts'] );

		// Deleteing comments if users delete comments at the same moment.
		if( ! empty( $current_drafts['deleted'] ) ) {
			foreach( $current_drafts['deleted'] as $key=>$value ) {
				$comment = get_post_meta( $post_ID, $key, true );
				foreach( $value as $delete_key ) {
					unset( $comment['comments'][$delete_key] );
				}
				update_post_meta( $post_ID, $key, $comment );
			}

		}

		// Sending Emails to newly mentioned users.
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) && 0 === count( $current_drafts['resolved'] ) ) {
			$new_drafts = $current_drafts['comments'];
			foreach ( $new_drafts as $el => $drafts ) {
				$comments          = get_post_meta( $post_ID, $el, true );
				$commented_on_text = $comments['commentedOnText'];
				$assigned_to       = $comments['assigned_to'];
				$list_of_comments  = isset( $comments['comments'] ) ? $comments['comments'] : '';

				// Send email to the commented recipients.
				$this->email_class->cf_email_new_comments( array(
					'post_ID'                   => $post_ID,
					'elid'                      => $elid,
					'post_title'                => $p_title,
					'post_edit_link'            => $p_link,
					'site_title'                => $site_title,
					'commented_on_text'         => $commented_on_text,
					'list_of_comments'          => $list_of_comments,
					'current_user_email'        => $current_user_email,
					'current_user_display_name' => $current_user_display_name,
					'new_comments'              => $new_comments,
					'assign_to'                 => $assigned_to
				) );
			}
		}
	}

	/**
	 * Include the Email template class and initiate the object.
	 */
	private function cf_initiate_email_class() {
		require_once( COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-email-templates.php' ); // phpcs:ignore
		$this->email_class = new Commenting_Block_Email_Templates();
	}

	/**
	 * @param string $string The string to be limited.
	 * @param int $limit The total number of characters allowed.
	 *
	 * @return string The limited string with '...' appended.
	 */
	public function cf_limit_characters( $string, $limit = 100 ) {
		return strlen( $string ) > $limit ? substr( $string, 0, $limit ) . '...' : $string;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cf_enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, COMMENTING_BLOCK_URL . '/admin/css/commenting-block-admin.css', array(), '1.0.3', 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cf_enqueue_scripts() {

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
		if ( $screen->is_block_editor || 'toplevel_page_editorial-comments' === $screen->base ) {
			wp_enqueue_script( $this->plugin_name, COMMENTING_BLOCK_URL . '/admin/js/commenting-block-admin.js', array( 'jquery', 'wp-components', 'wp-editor', 'wp-data' ), $this->version, false );
			wp_enqueue_script( 'cf-mark', COMMENTING_BLOCK_URL . '/admin/js/mark.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'content-collaboration-inline-commenting', COMMENTING_BLOCK_URL . '/admin/js/blockJS/block.build.js', array(
				'jquery',
				'cf-mark',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-annotations',
				'wp-annotations',
				'jquery-ui-datepicker',
				'wp-api-fetch',
				'wp-plugins',
				'wp-edit-post',
			), '1.0.7', true );

			$comment_id     = filter_input( INPUT_GET, 'comment_id', FILTER_SANITIZE_STRING );
			$get_users_list = get_transient( 'gc_users_list' );
			wp_localize_script( $this->plugin_name, 'adminLocalizer', [
				'nonce'      => wp_create_nonce( COMMENTING_NONCE ),
				'comment_id' => isset( $comment_id ) ? $comment_id : null,
				'cached_users_list' => $get_users_list,
				'allowed_attribute_tags' => apply_filters( 'commenting_block_allowed_attr_tags', static::$allowed_attribute_tags)
			] );

			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
		}

	}

	/**
	 * Convert string to linkable email.
	 *
	 * @param string $str Contains the strings that comes from the textarea.
	 *
	 * @return string
	 */
	public function convert_str_to_email( $str ) {
		$mail_pattern = "/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/";

		return preg_replace( $mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str );
	}

	/**
	 * Add Comment function.
	 */
	public function cf_add_comment() {
		$commentList      = filter_input( INPUT_POST, "commentList", FILTER_DEFAULT ); // phpcs:ignore
		$commentList      = html_entity_decode( $commentList );
		$commentList      = json_decode( $commentList, true );
		$list_of_comments = $commentList;
		// Get the assigned User ID.
		$assign_to = filter_input( INPUT_POST, 'assignTo', FILTER_SANITIZE_NUMBER_INT );

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$arr             = array();

		$commentList = end( $commentList );
		$metaId      = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		// If 'commented on' text is blank, stop process.
		if ( empty( $commentList['commentedOnText'] ) ) {
			echo wp_json_encode( array( 'error' => 'Please select text to comment onasd.' ) );
			wp_die();
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$timestamp   = current_time( 'timestamp' );

		$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );

		$commentListOld  = get_post_meta( $current_post_id, $metaId, true );
		$superCareerData = maybe_unserialize( $commentListOld );

		$arr['status']   = 'draft';
		$arr['userData'] = get_current_user_id();

		// Secure content.
		$arr['thread'] = $this->cf_secure_content( $commentList['thread'] );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		} else {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		if ( isset( $superCareerData['comments'] ) && 0 !== count( $superCareerData['comments'] ) ) {
			$superCareerData['comments'][ $timestamp ] = $arr;
			if ( $assign_to > 0 ) {
				$superCareerData['assigned_to']         = $assign_to;
				$superCareerData['sent_assigned_email'] = false;
			}
		} else {
			$superCareerData                           = array();
			$superCareerData['comments'][ $timestamp ] = $arr;
			$superCareerData['commentedOnText']        = $commentList['commentedOnText'];
			if ( $assign_to > 0 ) {
				$superCareerData['assigned_to']         = $assign_to;
				$superCareerData['sent_assigned_email'] = false;
			}

			update_post_meta( $current_post_id, 'th' . $metaId, get_current_user_id() );
		}
		update_post_meta( $current_post_id, $metaId, $superCareerData );

		$last_index                                   = count( $list_of_comments ) - 1;
		$list_of_comments[ $last_index ]['timestamp'] = $timestamp;

		// Get assigned user data.
		$assigned_to = null;
		if( ! empty( $superCareerData['assigned_to'] ) ) {
			$user_data   = get_user_by( 'ID', $superCareerData['assigned_to'] );
			$assigned_to = [
				'ID'           => $user_data->ID,
				'display_name' => $user_data->display_name,
				'user_email'   => $user_data->user_email,
				'avatar'       => get_avatar_url( $user_data->ID, [ 'size' => 32 ] )
			];
		}

		echo wp_json_encode( array(
			'dtTime'     => $dtTime,
			'timestamp'  => $timestamp,
			'assignedTo' => $assigned_to
		) );

		wp_die();
	}

	/**
	 * Display Comment Activity in History Popup.
	 */
	public function cf_comments_history() {

		$limit           = filter_input( INPUT_POST, "limit", FILTER_SANITIZE_NUMBER_INT );
		$limit           = isset( $limit ) ? $limit : 10;
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );

		$all_meta         = get_post_meta( $current_post_id );
		$userData         = array();
		$prepareDataTable = array();

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$total_comments = 0;
		foreach ( $all_meta as $dataid => $v ) {
			if ( strpos( $dataid, '_el' ) === 0 ) {
				$dataid            = str_replace( '_', '', $dataid );
				$v                 = maybe_unserialize( $v[0] );
				$comments          = $v['comments'];
				$commented_on_text = $v['commentedOnText'];
				$resolved          = isset( $v['resolved'] ) ? $v['resolved'] : 'false';

				if ( 'true' === $resolved ) {

					$udata = isset( $v['resolved_by'] ) ? $v['resolved_by'] : 0;
					if ( ! array_key_exists( $udata, $userData ) ) {
						$user_info = get_userdata( $udata );

						$userData[ $udata ]['username']   = $username = $user_info->display_name;
						$userData[ $udata ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
					} else {
						$username    = $userData[ $udata ]['username'];
						$profile_url = $userData[ $udata ]['profileURL'];
					}

					$timestamp = isset( $v['resolved_timestamp'] ) ? (int) $v['resolved_timestamp'] : '';
					if ( ! empty( $timestamp ) ) {
						$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
					}

					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dataid']            = $dataid;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['commented_on_text'] = $commented_on_text;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['username']          = $username;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['profileURL']        = $profile_url;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['dtTime']            = $dtTime;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata ]['status']            = 'resolved thread';
				}

				$comment_count = 0;
				foreach ( $comments as $timestamp => $c ) {

					$cstatus        = 0 === $comment_count ? __( 'commented', 'content-collaboration-inline-commenting' ) : __( 'replied', 'content-collaboration-inline-commenting' );
					$cstatus        .= __( ' on', 'content-collaboration-inline-commenting' );
					$comment_status = isset( $c['status'] ) ? $c['status'] : '';
					$cstatus        = 'deleted' === $comment_status ? __( 'deleted comment of', 'content-collaboration-inline-commenting' ) : $cstatus;

					// Stop displaying history of comments in draft mode.
					if ( 'draft' === $comment_status || 'permanent_draft' === $comment_status ) {
						continue;
					}

					$udata = $c['userData'];

					if ( ! array_key_exists( $udata, $userData ) ) {
						$user_info = get_userdata( $udata );

						$userData[ $udata ]['username']   = $username = $user_info->display_name;
						$userData[ $udata ]['profileURL'] = $profile_url = get_avatar_url( $user_info->user_email );
					} else {
						$username    = $userData[ $udata ]['username'];
						$profile_url = $userData[ $udata ]['profileURL'];
					}

					$thread = $c['thread'];
					if ( ! empty( $timestamp ) ) {
						$dtTime = gmdate( $time_format . ' ' . $date_format, $timestamp );
					}

					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dataid']            = $dataid;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['commented_on_text'] = $commented_on_text;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['username']          = $username;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['profileURL']        = $profile_url;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['thread']            = $thread;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['dtTime']            = $dtTime;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['status']            = $cstatus;
					$prepareDataTable[ $timestamp ][ $dataid . '_' . $udata . '_' . $comment_count ]['resolved']          = $resolved;
					$comment_count ++;
					$total_comments ++;
				}
			}
		}

		$html = '<div id="history-popup-insider">';
		if ( 0 !== $total_comments ) {
			krsort( $prepareDataTable, SORT_NUMERIC );

			$count = 0;

			foreach ( $prepareDataTable as $timestamp => $comments ) {
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
					$html .= "<div class='user-avatar'><img src='" . esc_url( $c['profileURL'] ) . "' alt='".esc_attr( $c['username'] )."'/></div>";
					$html .= "<div class='user-title'>
									<span class='user-name'>" . esc_html( $c['username'] ) . " " . esc_html( $c['status'] ) . "</span> ";

					if ( 'deleted comment of' === $c['status'] || 'resolved thread' === $c['status'] || 'true' === $c['resolved'] ) {
						$html .= esc_html( $commented_on_text );
					} else {
						$html .= "<a href='javascript:void(0)' data-id='" . esc_attr( $c['dataid'] ) . "' class='user-commented-on'>" . esc_html( $commented_on_text ) . "</a>";
					}

					$html .= "<div class='user-comment'> " . wp_kses( $c['thread'], wp_kses_allowed_html( 'post' ) ) . "</div>
								</div>";
					$html .= "<div class='user-time'>" . esc_html( $c['dtTime'] ) . "</div>";
					$html .= "</div>";
					$html .= "</div>";

					if ( $count >= $limit ) {
						break;
					}
				}
			}
		} else {
			$html .= __( 'No comments found.', 'content-collaboration-inline-commenting' );
		}
		$html .= "</div>";

		$allowed_tags = array(
			'a'    => array( 'id' => array(), 'href' => array(), 'target' => array(), 'style' => array(), 'class' => array(), 'data-id' => array() ),
			'div'  => array( 'id' => array(), 'class' => array(), 'style' => array() ),
			'img'  => array( 'src' => array(), 'title' => array(), 'alt' => array() ),
			'span' => array( 'class' => array(), 'style' => array() ),
		);

		echo wp_kses( $html, $allowed_tags );
		wp_die();
	}

	/**
	 * Make Content Secure.
	 *
	 * @param string $content
	 * @return string
	 */
	public function cf_secure_content( $content ) {
		$allowed_tags = array(
			'a'    => array( 'contenteditable' => array(), 'href' => array(), 'target' => array(), 'style' => array(), 'class' => array('js-mentioned'), 'data-email' => array() ),
			'div'  => array( 'id' => array(), 'class' => array(), 'style' => array() ),
			'br'   => array(),
		);

		$pattern = '/<[script|\/script]*>/i';
		$content = preg_replace( $pattern, '', $content );
		$content = wp_kses( $content, $allowed_tags );
		return $content;
	}

	/**
	 * Update Comment function.
	 */
	public function cf_update_comment() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		$edited_comment = filter_input( INPUT_POST, "editedComment", FILTER_DEFAULT ); // phpcs:ignore
		$edited_comment = htmlspecialchars_decode( $edited_comment );
		$edited_comment = html_entity_decode( $edited_comment );
		$edited_comment = json_decode( $edited_comment, true );

		// Make content secured.
		$edited_comment['thread'] = $this->cf_secure_content( $edited_comment['thread'] );

		$old_timestamp = $edited_comment['timestamp'];

		$commentListOld = get_post_meta( $current_post_id, $metaId, true );
		$commentListOld = maybe_unserialize( $commentListOld );

		$edited_draft           = array();
		$edited_draft['thread'] = $edited_comment['thread'];

		$commentListOld['comments'][ $old_timestamp ]['draft_edits'] = $edited_draft;

		update_post_meta( $current_post_id, $metaId, $commentListOld );

		// Update Current Drafts.
		$current_drafts                        = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts                        = maybe_unserialize( $current_drafts );
		$current_drafts                        = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['edited'][ $metaId ][] = $old_timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Delete Comment function.
	 */
	public function cf_delete_comment() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );
		$timestamp       = filter_input( INPUT_POST, "timestamp", FILTER_SANITIZE_NUMBER_INT );

		// Update Current Drafts.
		$current_drafts                         = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts                         = maybe_unserialize( $current_drafts );
		$current_drafts                         = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['deleted'][ $metaId ][] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Save settings of the plugin.
	 */
	public function cf_save_settings() {
		$form_data = array();
		parse_str( filter_input( INPUT_POST, "formData", FILTER_SANITIZE_STRING ), $form_data );

		update_option( 'cf_admin_notif', $form_data['cf_admin_notif'] );

		echo 'saved';
		wp_die();
	}

	/**
	 * Save important details in a localstorage.
	 */
	public function cf_store_in_localstorage() {

		// Returning show_avatar option to display avatars (or not to).
		$show_avatars = get_option( 'show_avatars' );
		$show_avatars = "1" === $show_avatars ? $show_avatars : 0;

		// Store plugin URL in localstorage so that its easy
		// to get sub site URL in JS files in Multisite environment.

		echo wp_json_encode( array( 'showAvatars' => $show_avatars, 'commentingPluginUrl' => COMMENTING_BLOCK_URL ) );
		wp_die();
	}

	/**
	 * Reset Drafts meta.
	 */
	public function cf_reset_drafts_meta() {
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;

		$permanent_drafts = get_post_meta( $current_post_id, 'permanent_drafts', true );
		$permanent_drafts = maybe_unserialize( $permanent_drafts );
		$permanent_drafts = empty( $permanent_drafts ) ? array() : $permanent_drafts;

		$draft_modes = array( 'resolved', 'comments', 'edited', 'deleted' );

		foreach ( $draft_modes as $draft_mode ) {
			if ( isset( $current_drafts[ $draft_mode ] ) && 0 !== count( $current_drafts[ $draft_mode ] ) ) {
				if ( isset( $permanent_drafts[ $draft_mode ] ) && 0 !== count( $permanent_drafts[ $draft_mode ] ) ) {

					$permanent_drafts[ $draft_mode ] = array_merge_recursive( $permanent_drafts[ $draft_mode ], $current_drafts[ $draft_mode ] );

				} else {
					$permanent_drafts[ $draft_mode ] = $current_drafts[ $draft_mode ];
				}
				$changed = 1;
			}
		}

		if ( 1 === $changed ) {
			update_post_meta( $current_post_id, 'permanent_drafts', $permanent_drafts );
		}

		$timestamp                = current_time( 'timestamp' );
		$drafts_meta              = array();
		$drafts_meta['timestamp'] = $timestamp;

		update_post_meta( $current_post_id, 'current_drafts', $drafts_meta );
	}

	/**
	 * Merge Drafts meta.
	 */
	public function cf_merge_draft_stacks() {
		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );

		$changed = 0;

		// Move previous drafts to Permanent Draft Stack.
		$current_drafts   = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts   = maybe_unserialize( $current_drafts );
		$current_drafts   = empty( $current_drafts ) ? array() : $current_drafts;
		$permanent_drafts = get_post_meta( $current_post_id, 'permanent_drafts', true );
		$permanent_drafts = maybe_unserialize( $permanent_drafts );

		if ( ! empty( $permanent_drafts ) ) {
			$draft_modes = array( 'resolved', 'comments', 'edited', 'deleted' );

			foreach ( $draft_modes as $draft_mode ) {
				if ( isset( $permanent_drafts[ $draft_mode ] ) && 0 !== count( $permanent_drafts[ $draft_mode ] ) ) {
					if ( isset( $current_drafts[ $draft_mode ] ) && 0 !== count( $current_drafts[ $draft_mode ] ) ) {
						$current_drafts[ $draft_mode ] = array_merge_recursive( $current_drafts[ $draft_mode ], $permanent_drafts[ $draft_mode ] );
					} else {
						$current_drafts[ $draft_mode ] = $permanent_drafts[ $draft_mode ];
					}
					$changed = 1;
				}
			}
		}

		if ( 1 === $changed ) {
			update_post_meta( $current_post_id, 'current_drafts', $current_drafts );
		}

		// Flush Permanent Draft Stack.
		update_post_meta( $current_post_id, 'permanent_drafts', '' );

		echo wp_json_encode( $current_drafts );
		wp_die();

	}

	/**
	 * Resolve Thread function.
	 */
	public function cf_resolve_thread() {

		$current_post_id = filter_input( INPUT_POST, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, "metaId", FILTER_SANITIZE_STRING );

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, 'current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		if ( isset( $current_drafts['resolved'] ) && 0 !== count( $current_drafts['resolved'] ) ) {
			$current_drafts['resolved'][] = $metaId;
		} else {
			$current_drafts['resolved'][] = $metaId;
		}
		update_post_meta( $current_post_id, 'current_drafts', $current_drafts );

		wp_die();
	}

	/**
	 * Rest API for Gutenberg Commenting Feature.
	 *
	 */
	public function cf_rest_api() {
		register_rest_route( 'cf', 'cf-get-comments-api', array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'cf_get_comments' ),
				'permission_callback' => '__return_true'
			)
		);
	}

	/**
	 * Function is used to fetch stored comments.
	 *
	 * @return mixed|\WP_REST_Response
	 */
	public function cf_get_comments() {
		$current_post_id = filter_input( INPUT_GET, "currentPostID", FILTER_SANITIZE_NUMBER_INT );
		$userDetails     = array();
		$elID            = filter_input( INPUT_GET, "elID", FILTER_SANITIZE_STRING );

		$commentList = get_post_meta( $current_post_id, $elID, true );

		$superCareerData = maybe_unserialize( $commentList );
		$comments        = $superCareerData['comments'];

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		foreach ( $comments as $t => $val ) {
			$user_info    = get_userdata( $val['userData'] );
			$username     = $user_info->display_name;
			$user_role    = implode( ', ', $user_info->roles );
			$profile_url  = get_avatar_url( $user_info->user_email );
			$thread       = $val['thread'];
			$cstatus      = isset( $val['status'] ) ? $val['status'] : '';
			$cstatus      = isset( $val['status'] ) ? $val['status'] : '';
			$edited_draft = isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '';

			$date = gmdate( $time_format . ' ' . $date_format, $t );

			if ( 'deleted' !== $cstatus ) {
				array_push( $userDetails,
					[
						'userName'    => $username,
						'userRole'    => $user_role,
						'profileURL'  => $profile_url,
						'dtTime'      => $date,
						'thread'      => $thread,
						'userData'    => $val['userData'],
						'status'      => $cstatus,
						'timestamp'   => $t,
						'editedDraft' => $edited_draft,
					] );
			}
		}

		// Get assigned user data
		$assigned_to = null;
		if ( $superCareerData['assigned_to'] > 0 ) {
			$user_data   = get_user_by( 'ID', $superCareerData['assigned_to'] );
			$assigned_to = [
				'ID'           => $user_data->ID,
				'display_name' => $user_data->display_name,
				'user_email'   => $user_data->user_email,
				'avatar'       => get_avatar_url( $user_data->ID, [ 'size' => 32 ] )
			];
		}

		$data                    = array();
		$data['userDetails']     = $userDetails;
		$data['resolved']        = 'true' === $superCareerData['resolved'] ? 'true' : 'false';
		$data['commentedOnText'] = $superCareerData['commentedOnText'];
		$data['assignedTo']      = $assigned_to;

		return rest_ensure_response( $data );

	}

	/**
	 * Fetch User Email List.
	 */
	public function cf_get_user_email_list() {
		// Check for nonce verification.
		check_ajax_referer( COMMENTING_NONCE, 'nonce' );

		// Get the current post id if not present then return.
		$post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
		if ( $post_id <= 0 ) {
			return;
		}

		$cache_key = 'gc_users_list';
		$get_users_list = get_transient( $cache_key );
		if( false === $get_users_list ) {
			// WP User Query.
			$users = new WP_User_Query( [
				'number'       => 9999,
				'role__in' => [ 'Administrator', 'Editor', 'Contributor', 'Author' ],
				'exclude'      => array( get_current_user_id() ),
			] );

			// Fetch out all user's email.
			$email_list   = [];
			$system_users = $users->get_results();

			foreach ( $system_users as $user ) {
				if ( $user->has_cap( 'edit_post', $post_id ) ) {
					$email_list[] = [
						'ID'                => $user->ID,
						'role'              => implode( ', ', $user->roles ),
						'display_name'      => $user->display_name,
						'full_name'         => $user->display_name,
						'user_email'        => $user->user_email,
						'avatar'            => get_avatar_url( $user->ID, [ 'size' => '24' ] ),
						'profile'           => admin_url( "/user-edit.php?user_id  ={ $user->ID}" ),
						'edit_others_posts' => $user->allcaps['edit_others_posts'],
					];
				}
			}
			// Set transient
			set_transient( $cache_key, $email_list, 24 * HOUR_IN_SECONDS );
			// Sending Response.
			$response = $email_list;
		} else {
			// Sending Response.
			$response = $get_users_list;
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Fetch Matched User Email List.
	 */
	public function cf_get_matched_user_email_list() {
		// Check for nonce verification.
		check_ajax_referer( COMMENTING_NONCE, 'nonce' );

		// Get the current post id if not present then return.
		$post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_NUMBER_INT );
		if ( $post_id <= 0 ) {
			return;
		}
		$niddle = filter_input( INPUT_POST, 'niddle', FILTER_SANITIZE_STRING );
		$niddle = substr( $niddle, 1 );
		if ( ! empty( $niddle ) && '@' !== $niddle ) {
			$users = new WP_User_Query( [
				'number'         => 9999,
				'search'         => $niddle . '*',
				'search_columns' => [ 'display_name' ],
				'role__not_in'   => 'Subscriber',
				'exclude'        => array( get_current_user_id() ),
			] );

			// Fetch out matched user's email.
			$email_list   = [];
			$system_users = $users->get_results();
			foreach ( $system_users as $user ) {
				if ( $user->has_cap( 'edit_post', $post_id ) ) {
					$email_list[] = [
						'ID'                => $user->ID,
						'role'              => implode( ', ', $user->roles ),
						'display_name'      => $user->display_name,
						'full_name'         => $user->display_name,
						'user_email'        => $user->user_email,
						'avatar'            => get_avatar_url( $user->ID, [ 'size' => '24' ] ),
						'edit_others_posts' => $user->allcaps['edit_others_posts'],
					];
				}
			}
			$response = $email_list;
		} else if ( '@' === $niddle ) {
			$this->cf_get_user_email_list();
		} else {
			$response = '';
		}
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Get the list of assignable users.
	 *
	 * @return void
	 */
	public function cf_get_assignable_user_list() {
		// Check for nonce verification.
		check_ajax_referer( COMMENTING_NONCE, 'nonce' );

		if ( ! isset( $_POST['content'] ) || empty( $_POST['content'] ) ) {
			return;
		}

		// Getting the content from the editor to filter out the users.
		$content = wp_kses( $_POST['content'], wp_kses_allowed_html( 'post' ) );
		$pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
		preg_match_all( $pattern, $content, $matches );

		$user_emails = array_unique( $matches[0] ); // Remove duplicate entries if any.

		$results = [];
		if ( count( $user_emails ) > 0 ) {
			foreach ( $user_emails as $user_email ) {
				$user_data = get_user_by( 'email', $user_email );
				$results[] = [
					'ID'           => $user_data->ID,
					'display_name' => $user_data->display_name,
					'user_email'   => $user_data->user_email,
					'role'         => implode( ', ', $user_data->roles ),
					'avatar'       => get_avatar_url( $user_data->ID ),
				];
			}
		}

		echo wp_json_encode( $results );
		wp_die();
	}
}
