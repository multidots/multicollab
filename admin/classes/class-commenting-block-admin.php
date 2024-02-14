<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
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

class Commenting_block_Admin extends Commenting_block_Functions {


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

	/**
	 * @var string Comment Activities.
	 */
	public $cf_activities;
	public $cf_activities_object;

	private static $allowed_attribute_tags = array( 'content', 'citation', 'caption', 'value', 'values', 'fileName', 'text', 'downloadButtonText' );
	private static $cf_permission_options  = array( 'cf_permission_add_comment', 'cf_permission_resolved_comment', 'cf_permission_add_suggestion', 'cf_permission_resolved_suggestion' );
	/**
	 * Initiate basename .
	 */
	static $basename = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		global $pagenow;
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

		// Remove the mdspan tage from the front content.
		add_filter( 'the_content', array( $this, 'cf_removeMdspan' ) );

		// Add untitled when page/post title blank
		add_filter( 'the_title', array( $this, 'cf_post_title' ) );

		// Add user role to WordPress users api
		add_action( 'rest_api_init', array( $this, 'create_api_user_meta_field_for_userrole' ) );

		// clear output buffer on init hook.
		add_action( 'init', array( $this, 'cf_app_output_buffer' ) );

		add_action( 'admin_footer', array( $this, 'cf_free_admin_edit_post_feedback_form' ) );

		add_action( 'wp_ajax_cf_free_plugin_wizard_submit', array( $this, 'cf_free_plugin_wizard_submit' ) );

		add_action( 'cf_free_plugin_usage_data', array( $this, 'cf_free_plugin_usage_data_callback_function' ) );

		add_filter( 'admin_body_class', array( $this, 'cf_admin_classes' ) );

		$allow_pages = array( 'edit.php', 'post-new.php', 'post.php' );
		if ( in_array( $pagenow, $allow_pages, true ) ) {
			add_action( 'admin_notices', array( $this, 'cf_display_promotional_banner_page_post' ) );
		}

		add_filter( 'cron_schedules', array( $this, 'cf_free_cron_job_recurrence' ) );

		// User authentication function while redirect guest.
		add_filter( 'authenticate', array( $this, 'cf_guest_auto_login' ), 10, 3 );
		add_filter( 'plugin_action_links_' . COMMENTING_BLOCK_BASE, array( $this, 'cf_custom_plugin_action_links' ), 10, 4 );
		add_filter( 'block_type_metadata', array( $this, 'filter_block_type_metadata' ), 10, 1  );
	}

	/**
	 * Adds a custom cron schedule for every month.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 * @return array Filtered array of non-default cron schedules.
	 */
	function cf_free_cron_job_recurrence( $schedules ) {
		$schedules['cf_free_monthly'] = array(
			'display'  => __( 'Once monthly', 'content-collaboration-inline-commenting' ),
			'interval' => 2635200,
		);
		return $schedules;
	}

	/**
	 * Display promotional banner on  post/page.
	 *
	 * @author Nirav Soni
	 */
	public function cf_display_promotional_banner_page_post() {

			$promotional_banner = cf_dpb_promotional_banner();

		if ( ! empty( $promotional_banner ) ) {
			echo $promotional_banner; // phpcs:ignore WordPress.Security.EscapeOutput
		}

	}

	/**
	 * Added extra links to default meta row.
	 *
	 * @author: Himanshu shekhar
	 * @version 4.1
	 *
	 * @param array  $links To add custom link.
	 * @param string $plugin_file The path to the main plugin file.
	 */
	public function cf_custom_plugin_action_links( $links, $plugin_file ) {
		// Add your custom links.
		$custom_links = array(
			'<a href="https://www.multicollab.com/upgrade-to-premium/" style="color:#4abe17" target="_blank">Upgrade to Pro</a>',
			'<a href="https://docs.multicollab.com/" target="_blank">Documentation</a>',
		);

		// Merge the custom links with the existing action links.
		$links = array_merge( $custom_links, $links );
		return $links;
	}

	/**
	 * Update realtime collobrators ajax function.
	 *
	 * @return void
	 */
	public function realtime_collaborators_update_ajax_function() {


		$current_post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_SPECIAL_CHARS );
		$activeUsers = filter_input( INPUT_POST, 'activeUsers', FILTER_UNSAFE_RAW ); //phpcs:ignore
		$active_users =  json_decode( stripslashes( $activeUsers ), true );

		$realtime_collobrator = get_post_meta( $current_post_id, '_realtime_collaborators', true );
		$realtime_collobrator =  (array) json_decode( $realtime_collobrator, true );

		if( isset( $realtime_collobrator ) && !empty( $realtime_collobrator ) ) {
			foreach( $active_users as $active_users_data ) {
				array_push( $realtime_collobrator, $active_users_data );
			}
		} else {
			$realtime_collobrator = $active_users;
		}



		$realtime_collobrators_unique_array = [];
		foreach($realtime_collobrator as $element) {
			$hash = $element['userId'];
			$realtime_collobrators_unique_array[$hash] = $element;
			$user_data   = get_user_by( 'ID', $element['userId'] );

			$realtime_user_roles = array_values( $user_data->roles );
			$realtime_user_role  = array_shift( $realtime_user_roles );

			$realtime_collobrators_unique_array[$hash]['role'] = $realtime_user_role;
			$realtime_collobrators_unique_array[$hash]['email'] = $user_data->user_email;
			$realtime_collobrators_unique_array[$hash]['userAvatar'] = get_avatar_url( $element['userId'] );

		}

		update_post_meta( $current_post_id, '_realtime_collaborators', wp_json_encode( $realtime_collobrators_unique_array ) );
		echo wp_json_encode( $realtime_collobrators_unique_array );
		wp_die();


	}
	
	/**
	 * Update realtime collobrators activity ajax function.
	 *
	 * @return void
	 */
	public function realtime_collaborators_activity_update_ajax_function() {
		$current_post_id = filter_input( INPUT_POST, 'postID', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$activeUsers = filter_input( INPUT_POST, 'currentUser', FILTER_UNSAFE_RAW ); //phpcs:ignore
		$active_users =  json_decode( stripslashes( $activeUsers ), true );
		
		$realtime_collobrator = get_post_meta( $current_post_id, '_realtime_collaborators_activity', true );
		$realtime_collobrator =  (array) json_decode( $realtime_collobrator, true );
		
		if( isset( $realtime_collobrator ) && !empty( $realtime_collobrator ) ) {
			$userIdExists = false;
			$joined_count = 0;
			$removed_count = 0;
			foreach($realtime_collobrator as $collaborator) {
				if($collaborator['userId'] === $active_users[0]['userId']){
					if ( 'Joined' === $collaborator['type'] ) {
						$joined_count++;
					}
					if ( 'Removed' === $collaborator['type'] ) {
						$removed_count++;
					}
					$userIdExists = true;
				}
			}
			
			// If userId is unique, OR IF user joined back after being removed merge it into realtime_collobrator array
				if (!$userIdExists || ($joined_count === $removed_count)) {
					$realtime_collobrator[] = $active_users[0];
					$meta_key_sufix = $active_users[0]['userId'].'_'.$active_users[0]['timestamp'];
					if( 'Joined' === $active_users[0]['type'] ){
						update_post_meta( $current_post_id, 'th_rc_joined_'.$meta_key_sufix, $active_users[0]['timestamp'] );
					}
					
				}
		} else {
			$realtime_collobrator = $active_users;
			$meta_key_sufix = $active_users[0]['userId'].'_'.$active_users[0]['timestamp'];
			if( 'Joined' === $active_users[0]['type'] ){
				update_post_meta( $current_post_id, 'th_rc_joined_'.$meta_key_sufix, $active_users[0]['timestamp'] );
			}
		}
		
		update_post_meta( $current_post_id, '_realtime_collaborators_activity', wp_json_encode( $realtime_collobrator ) );
		echo wp_json_encode( $realtime_collobrator );
		wp_die();
	}

	/**
	 * Add body class for wizard screen.
	 *
	 * @return void
	 */
	public function cf_admin_classes( $classes ) {

		$page_type = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( 'multicollab_setup_wizard' === $page_type ) {
			$classes .= ' cf_fullscreen';
		}

		$cf_edd = new CF_EDD();
		if ( $cf_edd->is__premium_only() ) {
			$plan_name = esc_html( $cf_edd->get_plan_name() );
		} else {
			$plan_name = esc_html( 'FREE' );
		}

		$classes .= ' multicollab_plan_' . strtolower( $plan_name );

		// Added parent class to body for adding css.
		$classes .= ' multicollab_body_class ';

		return $classes;

	}

	/**
	 * Send Wizard Opt-in details to sendinblue.
	 *
	 * @return void
	 */
	public function cf_free_plugin_wizard_submit() {

		global $wp_version;

		$current_user    = wp_get_current_user();
		$subscribe_email = filter_input( INPUT_GET, 'subscribe_email', FILTER_SANITIZE_SPECIAL_CHARS );
		$subscribe_email = ! empty( $subscribe_email ) ? $subscribe_email : '';

		$opt_in      = filter_input( INPUT_GET, 'opt_in', FILTER_SANITIZE_SPECIAL_CHARS );
		$broser_name = filter_input( INPUT_GET, 'broser_name', FILTER_SANITIZE_SPECIAL_CHARS );
		$country     = filter_input( INPUT_GET, 'country', FILTER_SANITIZE_SPECIAL_CHARS );
		$user_email  = $current_user->user_email;

		// Get Gutenberg Version.
		$plugins = get_option( 'active_plugins' );
		if ( in_array( 'gutenberg/gutenberg.php', $plugins, true ) ) {
			$get_plugin_data   = get_plugin_data( WP_PLUGIN_DIR . '/gutenberg/gutenberg.php' );
			$gutenberg_version = esc_html( $get_plugin_data['Version'] );
		} else {
			$gutenberg_version = esc_html( 'Default', 'content-collaboration-inline-commenting' );
		}

		update_option( 'cf_opt_in', $opt_in );

		// Get multicollab plan details.

		$multicollab_plan = esc_html( 'FREE', 'content-collaboration-inline-commenting' );

		// Get Webserver Name and Version.
		$server_software        = filter_input( INPUT_SERVER, 'SERVER_SOFTWARE', FILTER_SANITIZE_SPECIAL_CHARS );
		$server_software        = isset( $server_software ) ? $server_software : '';
		$webservername          = explode( '/', $server_software )[0];
		$webserver_name_version = esc_html( $webservername ) . ' ' . esc_html( explode( '/', $server_software )[1] );

		// Get users count.
		$user_count = count_users();

		// get WP version.
		$my_theme = wp_get_theme();

		$plugin_usage_data = array(
			'Admin Email'                => $user_email,
			'Website URL'                => esc_url( get_home_url() ),
			'WordPress Version'          => $wp_version,
			'Gutenberg Version'          => $gutenberg_version,
			'PHP Version'                => phpversion(),
			'Multicollab Plugin status'  => 'active',
			'Multicollab Version'        => COMMENTING_BLOCK_VERSION,
			'Multicollab Plan'           => $multicollab_plan,
			'Date & Time'                => gmdate( 'F j, Y g:i a' ),
			'Language'                   => get_bloginfo( 'language' ),
			'Theme'                      => $my_theme->get( 'Name' ),
			'Browser Name and Version'   => $broser_name,
			'Webserver Name and Version' => $webserver_name_version,
			'Operating System'           => sprintf( '%s %s %s', php_uname( 's' ), php_uname( 'r' ), php_uname( 'm' ) ),
			'Country'                    => $country,
		);

		$data_insert_array = array(
			'user_name'         => $current_user->display_name,
			'user_email'        => $user_email,
			'news_letter_email' => $subscribe_email,
			'opt_in'            => $opt_in,
			'plan_name'         => $multicollab_plan,
			'PLUGIN_USAGE_DATA' => wp_json_encode( $plugin_usage_data ),
			'website_url'       => esc_url( get_home_url() ),
		);

		$feedback_api_url = CF_STORE_URL . '/wp-json/edd-add-free-user-contact/v2/edd-add-free-user-contact';
		$query_url        = $feedback_api_url . '?' . http_build_query( $data_insert_array );
		$response         = wp_remote_get( $query_url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get

		if ( ! wp_next_scheduled( 'cf_free_plugin_usage_data' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'cf_free_monthly', 'cf_free_plugin_usage_data' );
		}

		if ( ! wp_next_scheduled( 'cf_daily_license_checker' ) ) {
			wp_schedule_event( time(), 'daily', 'cf_daily_license_checker' );
		}

		$args = array(
			'title' => 'Getting Started with Multicollab',
			'post_type'         => 'post',
			'post_status'       => 'draft',
		);
		$posts_array = get_posts( $args );
		if( empty( $posts_array ) ){
			$wizard_redirect_link = home_url() . '/wp-admin/admin.php?page=editorial-comments&view=web-activity';
		} else {
			$wizard_redirect_link = $url = get_edit_post_link( $posts_array[0]->ID );;
		}

		echo esc_html( $wizard_redirect_link );
		wp_die();
	}

	/**
	 * Send Wizard Opt-in details to sendinblue every monthly.
	 *
	 * @return void
	 */
	public function cf_free_plugin_usage_data_callback_function() {

		global $wp_version;

		// Get Gutenberg Version.
		$plugins = get_option( 'active_plugins' );
		if ( in_array( 'gutenberg/gutenberg.php', $plugins, true ) ) {
			$get_plugin_data   = get_plugin_data( WP_PLUGIN_DIR . '/gutenberg/gutenberg.php' );
			$gutenberg_version = esc_html( $get_plugin_data['Version'] );
		} else {
			$gutenberg_version = esc_html( 'Default', 'content-collaboration-inline-commenting' );
		}

		// Get multicollab plan details.

		$multicollab_plan = esc_html( 'FREE', 'content-collaboration-inline-commenting' );

		// get WP version.
		$my_theme = wp_get_theme();

		$user_count           = count_users();
		$cf_custom_posts_args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$cf_custom_posts_output   = 'names';
		$cf_custom_posts_operator = 'and';

		$cf_custom_post_types  = get_post_types( $cf_custom_posts_args, $cf_custom_posts_output, $cf_custom_posts_operator );
		$cf_default_post_types = array(
			'post' => 'post',
			'page' => 'page',
		);
		$cf_all_post_types     = array_merge( $cf_default_post_types, $cf_custom_post_types );

		$all_posts_id = get_posts(
			array(
				'post_type'   => $cf_all_post_types,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		$posts_counts    = count( $all_posts_id );
		$comments_counts = 0;

		if ( $all_posts_id ) {

			foreach ( $all_posts_id as $id ) {

				$comments_count_data = $this->cf_get_comment_counts( $id );
				$comments_counts    += $comments_count_data['total_counts'];

			}
		}
		// average post count
		$avg_post_count = ( $comments_counts / $posts_counts );

		// number of users - exclude subscriber user
		$total_user = $user_count['total_users'];
		if ( isset( $user_count['avail_roles']['subscriber'] ) ) {
				$subscribers = $user_count['avail_roles']['subscriber'];
		} else {
			$subscribers = 0;
		}
		$no_of_users = ( $total_user - $subscribers );

		// commennt of current month
		$comments_of_month = gmdate( 'F' );

		// commennt of current year
		$comments_of_year = gmdate( 'Y' );

		$general_setting     = $this->cf_get_general_settings();
		$plugin_advance_data = array(
			'general_setting'   => $general_setting,
			'comment_count'     => $comments_counts,
			'post_count'        => $posts_counts,
			'avg_post_count'    => $avg_post_count,
			'no_of_users'       => $no_of_users,
			'comments_of_month' => $comments_of_month,
			'comments_of_year'  => $comments_of_year,
		);

		$cf_opt_in = get_option( 'cf_opt_in' );

		$plugin_basic_data = array(
			'wordpress_version'   => $wp_version,
			'gutenberg_version'   => $gutenberg_version,
			'php_version'         => phpversion(),
			'multicollab_version' => COMMENTING_BLOCK_VERSION,
			'multicollab_plan'    => $multicollab_plan,
			'language'            => get_bloginfo( 'language' ),
			'theme'               => $my_theme->get( 'Name' ),
		);

		$data_insert_array = array(
			'website_url'         => esc_url( get_home_url() ),
			'plugin_basic_data'   => wp_json_encode( $plugin_basic_data ),
			'plugin_advance_data' => wp_json_encode( $plugin_advance_data ),
			'opt_in'              => $cf_opt_in,
		);

		$feedback_api_url = CF_STORE_URL . '/wp-json/edd-add-free-user-contact/v2/edd-add-free-user-contact?' . wp_rand();
		$query_url        = $feedback_api_url . '&' . http_build_query( $data_insert_array );

		$response = wp_remote_get( $query_url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get

		wp_die();
	}

	/**
	 * Daily cron for check license key status.
	 *
	 * @return void
	 */
	public function cf_daily_license_checker_callback_function() {

		// Set webhook URL.
		$cf_websocket_options = get_option( 'cf_websocket_options' );
		$cf_edd = new CF_EDD();
		if( 'cf_websocket_default' === $cf_websocket_options ) {

			$cf_websocket_url  = CF_STORE_URL . 'wp-json/cf-websocket-url/v2/cf-websocket-url?' . wp_rand();
			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$cf_websocket_url_request = vip_safe_wp_remote_get( $cf_websocket_url, 3, 1, 20 ); //phpcs:ignore
			} else {
				$cf_websocket_url_request = wp_remote_get( $cf_websocket_url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
			}
			$cf_websocket_url_request_body = $cf_websocket_url_request['body'];
			$cf_websocket_url_request_data = json_decode( $cf_websocket_url_request_body, true );
			
			if ( $cf_edd->is__premium_only() ) {
				update_option( 'cf_multiedit_websocket', isset($cf_websocket_url_request_data['pro']['wsurl']) ? $cf_websocket_url_request_data['pro']['wsurl'] : '' );
			} else {
				update_option( 'cf_multiedit_websocket', isset($cf_websocket_url_request_data['free']['wsurl']) ? $cf_websocket_url_request_data['free']['wsurl'] : '' );
			}
			
		}
	}

	/**
	 * Add function to add HTML to admin footer.
	 *
	 * @return void
	 */
	public function cf_free_admin_edit_post_feedback_form() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-admin-deactive-free.php'; // phpcs:ignore
		}
	}


	/**
	 * Clear output buffer on init function.
	 *
	 * @return void
	 */
	public function cf_app_output_buffer() {
		ob_start();
	}

	/**
	 * Add Untitled when post title is blank.
	 *
	 * @param array $title title of post.
	 *
	 * @return mixed Updated title.
	 */
	function cf_post_title( $title ) {
		return '' === $title ? esc_html_x( 'Untitled', 'Added to posts and pages that are missing titles', 'content-collaboration-inline-commenting' ) : $title;
	}

	/**
	 * Remove custom tag "mdspan" from the content.
	 *
	 * @param array $content content of post.
	 *
	 * @return mixed Updated content.
	 */
	public function cf_removeMdspan( $content ) {
		if ( ( is_singular() ) && ( is_main_query() ) ) {
			$regex       = '#<mdspan(.*?)>#';
			$replacement = '';
			$content     = preg_replace( $regex, $replacement, $content );
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
		$all_post_type            = get_post_types_by_support( array( 'editor' ) );
		$post_type                = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_SPECIAL_CHARS );
		$type                     = get_post_type();
		$cf_hide_editorial_column = get_option( 'cf_hide_editorial_column' );
		if ( empty( $cf_hide_editorial_column ) ) {
			if ( ( in_array( trim( $post_type ), $all_post_type, true ) ) || ( in_array( trim( $type ), $all_post_type, true ) ) ) {
				if ( ( isset( $post_type ) || isset( $type ) ) && ( $post_type !== 'product' || $type !== 'product' ) ) {
					$defaults['cb_comments_status'] = '<img id="cf-column-img" src="' . esc_url( COMMENTING_BLOCK_URL . '/admin/assets/images/commenting-logo.svg' ) . '" width=17/>' . __( 'Multicollab', 'content-collaboration-inline-commenting' );
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
	public function cf_columns_content( $column_name, $post_ID ) {
		if ( 'cb_comments_status' === $column_name ) {
			$comment_counts    = $this->cf_get_comment_counts( $post_ID );
			$suggestion_counts = $this->cf_get_suggestion_counts( $post_ID );
			$open_counts       = intval( $comment_counts['open_counts'] ) + intval( $suggestion_counts['open_counts'] );
			$total_counts      = intval( $comment_counts['total_counts'] ) + intval( $suggestion_counts['total_counts'] );
			$resolved_total    = $comment_counts['resolved_counts'] + $suggestion_counts['accepted_counts'] + $suggestion_counts['rejected_counts'];
			$autodraft_total   = $total_counts - ( $open_counts + $resolved_total );
			$open_counts       = $total_counts - ( $autodraft_total + $resolved_total );

			if ( 0 !== $total_counts ) {
				echo '<a href="' . esc_url( get_edit_post_link( $post_ID ) ) . '">' . esc_html( $open_counts . '/' . $total_counts ) . '</a>';
			} else {
				echo '-';
			}
		}
	}

	/**
	 * Add Setting Page.
	 */
	public function cf_add_setting_page() {
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

		add_submenu_page(
			'',
			__( 'Free Wizard', 'textdomain' ),
			'Multicolab Wizard',
			'manage_options',
			'multicollab_setup_wizard',
			array( $this, 'multicollab_setup_wizard_function' )
		);
	}

	/**
	 * Callback function for free user setup wizard.
	 *
	 * @return void
	 */
	public function multicollab_setup_wizard_function() {
		include COMMENTING_BLOCK_DIR . 'admin/partials/free-user-plugin-wizard.php';
	}

	/**
	 * Plugin setting page callback function.
	 */
	public function cf_settings_callback() {

		// Add setting page file.
		require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-settings-page.php'; // Removed phpcs:ignore by Rishi Shah.
	}

	/**
	 * Get the latest comment activities.
	 */
	public function cf_get_activities() {
		require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-activities.php'; // Removed phpcs:ignore by Rishi Shah.

		$this->cf_activities_object = new Commenting_Block_Activities();
		$this->cf_activities        = $this->cf_activities_object->cf_get_activities();
	}

	/**
	 * Get activities code.
	 */
	public function cf_get_activity_details() {
		require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-activities.php'; // Removed phpcs:ignore by Rishi Shah.

		$this->cf_activities_object = new Commenting_Block_Activities();
		$this->cf_activities        = $this->cf_activities_object->cf_get_detailed_activity();
	}

	/**
	 * Get activities code.
	 */
	public function cf_migrate_to_pro() {
		require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-activities.php'; // Removed phpcs:ignore by Rishi Shah.

		$this->cf_activities_object = new Commenting_Block_Activities();
		$this->cf_activities        = $this->cf_activities_object->cf_migrate_to_pro_now();
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

		echo wp_json_encode(
			array(
				'id'   => $userID,
				'name' => $userName,
				'role' => $userRole,
				'url'  => $userURL,
			)
		);
		wp_die();
	}

	/**
	 * @param int           $post_ID Post ID.
	 * @param object/string $post Post Content.
	 * @param string        $update Status of the update.
	 */
	public function cf_post_status_changes( $post_ID, $post ) {
		$metas      = get_post_meta( $post_ID );
		$p_content  = is_object( $post ) ? $post->post_content : $post;
		$p_link     = get_edit_post_link( $post_ID );
		$p_title    = get_the_title( $post_ID ); // Removed phpcs:ignore by Rishi Shah.
		$site_title = get_bloginfo( 'name' ); // Removed phpcs:ignore by Rishi Shah.
		$html       = '';

		// Get current user details.
		$curr_user                 = wp_get_current_user();
		$user_id                   = $curr_user->ID;
		$current_user_email        = $curr_user->user_email;
		$current_user_display_name = $curr_user->display_name;

		// Publish drafts from the '_current_drafts' stack.
		$current_drafts    = isset( $metas['_current_drafts'][0] ) ? $metas['_current_drafts'][0] : array();
		$current_drafts    = maybe_unserialize( $current_drafts );
		$current_timestamp = current_time( 'timestamp' );

		// Initiate Email Class Object.
		$this->cf_initiate_email_class();

		// Publish Deleted Comments. (i.e. finally delete them.)
		if ( isset( $current_drafts['deleted'] ) && 0 !== count( $current_drafts['deleted'] ) ) {
			$deleted_drafts = $current_drafts['deleted'];
			foreach ( $deleted_drafts as $el => $timestamps ) {
				$prev_state = $metas[ $el ][0];
				$prev_state = maybe_unserialize( $prev_state );

				foreach ( $timestamps as $key => $t ) {

					$local_time        = current_datetime();
					$deleted_timestamp = $local_time->getTimestamp() + $local_time->getOffset() + $key;
					// Update the timestamp of deleted comment.
					$previous_comment = $prev_state['comments'][ $t ];
					if ( ! empty( $previous_comment ) ) {
						$prev_state['comments'][ $deleted_timestamp ]               = $previous_comment;
						$prev_state['comments'][ $deleted_timestamp ]['status']     = 'deleted';
						$prev_state['comments'][ $deleted_timestamp ]['created_at'] = $t;
					}
				}
				$prev_state['updated_at'] = $current_timestamp;

				// add th meta
				update_post_meta( $post_ID, $el, $prev_state );
				update_post_meta( $post_ID, 'th' . $el, $deleted_timestamp );
				$metas[ $el ][0] = maybe_serialize( $prev_state );
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
					$prev_state   = $metas[ $el ][0];
					$prev_state   = maybe_unserialize( $prev_state );
					$new_comments = array();
					foreach ( $drafts as $d ) {
						$prev_state['comments'][ $d ]['status'] = 'publish';
						$new_comments[]                         = $d;
					}

					$prev_state['updated_at'] = $current_timestamp;
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
					$edited_draft      = $prev_state['comments'][ $t ]['draft_edits']['thread'];
					$edited_attachment = $prev_state['comments'][ $t ]['draft_edits']['attachmentText'];
					if ( ! empty( $edited_draft ) ) {
						$prev_state['comments'][ $t ]['thread'] = $edited_draft;
					}
					if ( ! empty( $edited_attachment ) ) {
						$prev_state['comments'][ $t ]['attachmentText'] = $edited_attachment;

					} else {
						$prev_state['comments'][ $t ]['attachmentText'] = '';
					}

					// Change status to publish.
					$prev_state['comments'][ $t ]['status'] = 'publish';

					// Remove comment from edited_draft.
					unset( $prev_state['comments'][ $t ]['draft_edits']['thread'] );
					unset( $prev_state['comments'][ $t ]['draft_edits']['attachmentText'] );
				}
				$prev_state['updated_at'] = $current_timestamp;
				update_post_meta( $post_ID, $el, $prev_state );
				update_post_meta( $post_ID, 'th' . $el, $current_timestamp );
				$metas[ $el ][0] = maybe_serialize( $prev_state );

			}
		}

		if ( isset( $current_drafts ) && ! empty( $current_drafts ) ) {
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
		if ( ! empty( $current_drafts['deleted'] ) ) {
			foreach ( $current_drafts['deleted'] as $key => $value ) {
				$comment = get_post_meta( $post_ID, $key, true );
				foreach ( $value as $delete_key ) {
					unset( $comment['comments'][ $delete_key ] );
				}
				update_post_meta( $post_ID, $key, $comment );
			}
		}

		// Sending Emails to newly mentioned users.
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$new_drafts = $current_drafts['comments'];

			foreach ( $new_drafts as $el => $drafts ) {
				$comments          = get_post_meta( $post_ID, $el, true );
				$commented_on_text = $comments['commentedOnText'];
				$assigned_to       = isset( $comments['assigned_to'] ) ? $comments['assigned_to'] : '';
				$list_of_comments  = isset( $comments['comments'] ) ? $comments['comments'] : '';
				$blockType         = isset( $comments['blockType'] ) ? $comments['blockType'] : '';
				$link              = $p_link . '&current_url=' . $elid;

				$prev_state   = $metas[ $el ][0];
				$prev_state   = maybe_unserialize( $prev_state );
				$new_comments = array();
				foreach ( $drafts as $d ) {
					$prev_state['comments'][ $d ]['status'] = 'publish';
					$new_comments[]                         = $d;
				}

				// Send email to the commented recipients.
				$this->email_class->cf_email_new_comments(
					array(
						'post_ID'                   => $post_ID,
						'elid'                      => $elid,
						'post_title'                => $p_title,
						'post_edit_link'            => $link,
						'site_title'                => $site_title,
						'commented_on_text'         => $commented_on_text,
						'list_of_comments'          => $list_of_comments,
						'current_user_email'        => $current_user_email,
						'current_user_display_name' => $current_user_display_name,
						'new_comments'              => $new_comments,
						'assign_to'                 => $assigned_to,
						'block_type'                => $blockType,
					)
				);
			}
		}
	}

	/**
	 * Include the Email template class and initiate the object.
	 */
	private function cf_initiate_email_class() {
		require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-email-templates.php'; // Removed phpcs:ignore by Rishi Shah.
		$this->email_class = new Commenting_Block_Email_Templates();
	}

	/**
	 * @param string $string The string to be limited.
	 * @param int    $limit The total number of characters allowed.
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
		$screen = get_current_screen();
		if ( 'site-editor' !== $screen->base ) {

			wp_enqueue_style( $this->plugin_name, trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/styles/editorStyle.build.min.css', array(), wp_rand(), 'all' );
		}
		wp_enqueue_style( 'cf-select2', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/css/select2.min.css', array(), wp_rand(), 'all' );
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

		// Enque de-active pro plugin JS.
		if ( 'plugins' === $screen->base && current_user_can( 'activate_plugins' ) ) {
			wp_enqueue_script( 'wp-deactivation-message', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/comenting-block-deactive-free.js', array( 'jquery' ), wp_rand(), false );
			wp_localize_script(
				'wp-deactivation-message',
				'multicollab_plugin_path',
				array(
					'plugin_path' => COMMENTING_BLOCK_BASE,
					'nonce'       => wp_create_nonce( 'multicollab_plugin_path' ),
				)
			);
		}

		if ( ( $screen->is_block_editor && 'site-editor' !== $screen->base && 'widgets' !== $screen->base ) || ( 'toplevel_page_editorial-comments' === $screen->base || 'admin_page_multicollab_setup_wizard' === $screen->base ) ) {
			wp_enqueue_script( $this->plugin_name, trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/commenting-block-admin.js', array( 'jquery', 'wp-components', 'wp-editor', 'wp-data', 'cf-mark', 'cf-dom-purify', 'react', 'react-dom' ), wp_rand(), false );
			wp_enqueue_script( 'es5-js', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/commenting-broser-details.js', array( 'jquery' ), wp_rand(), false );
			wp_enqueue_script( 'cf-mark', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/mark.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'cf-dom-purify', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/libs/purify.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'font-awesome-free', 'https://kit.fontawesome.com/cfc6818450.js' );
			wp_enqueue_script( $this->plugin_name, trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/commenting-block-admin.js', array( 'jquery', 'wp-components', 'wp-editor', 'wp-data', 'wp-i18n', 'cf-mark', 'cf-dom-purify', 'react', 'react-dom' ), wp_rand(), false );

			// Add wizard JS.
			wp_enqueue_script( 'commenting-block-wizard-js', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/commenting-block-wizard.js', array( 'jquery' ), wp_rand(), false );

			wp_enqueue_script(
				'content-collaboration-inline-commenting',
				trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/block.build.min.js',
				array(
					'jquery',
					'cf-mark',
					'cf-dom-purify',
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
					'wp-data',
				),
				wp_rand(),
				true
			);

			if ( 'wp_template' === isset( $_REQUEST['postType'] ) ) { //phpcs:ignore
				wp_deregister_script( 'content-collaboration-inline-commenting' );
				wp_deregister_script( $this->plugin_name );
			}
			$comment_id = filter_input( INPUT_GET, 'comment_id', FILTER_SANITIZE_SPECIAL_CHARS );
			wp_localize_script(
				$this->plugin_name,
				'adminLocalizer',
				array(
					'nonce'                  => wp_create_nonce( COMMENTING_NONCE ),
					'comment_id'             => isset( $comment_id ) ? $comment_id : null,
					'allowed_attribute_tags' => apply_filters( 'commenting_block_allowed_attr_tags', static::$allowed_attribute_tags ),
					'cf_permission_options'  => apply_filters( 'commenting_block_permission_options', static::$cf_permission_options ),
				)
			);
			// set edit time timezone
			$date_format      = get_option( 'date_format' );
			$time_format      = get_option( 'time_format' );
			$edited_timestamp = current_time( 'timestamp' );
			$editedDateTime   = gmdate( $time_format . ' ' . $date_format, $edited_timestamp );
			wp_localize_script( $this->plugin_name, 'editedTimestamp', array( 'cmtEditedTime' => $edited_timestamp ) );
			wp_localize_script( $this->plugin_name, 'editedTimezone', array( 'editedTime' => $editedDateTime ) );
			wp_localize_script(
				$this->plugin_name,
				'wp_time_setting',
				array(
					'dateFormat'     => $date_format,
					'timeFormat'     => $time_format,
					'timezoneOffset' => get_option( 'gmt_offset' ),
				)
			);
			$current_user       = wp_get_current_user();
			$currunt_user_roles = array_values( $current_user->roles );
			$current_user_role  = array_shift( $currunt_user_roles );
			wp_localize_script(
				$this->plugin_name,
				'currentUserData',
				array(
					'id'       => $current_user->ID,
					'username' => $current_user->data->display_name,
					'role'     => $current_user_role,
					'avtarUrl' => get_avatar_url( $current_user->ID ),
				)
			);
			$can_upload_file = ( $current_user->has_cap( 'upload_files' ) );
			wp_localize_script( $this->plugin_name, 'can_upload_file', array( 'can_upload' => $can_upload_file ) );
			$cf_options                        = get_option( 'cf_permissions' );
			$cf_add_comment_permission         = isset( $cf_options[ $current_user->roles[0] ]['add_comment'] ) ?? '';
			$cf_resolved_comment_permission    = isset( $cf_options[ $current_user->roles[0] ]['resolved_comment'] ) ?? '';
			$cf_hide_comment_permission        = isset( $cf_options[ $current_user->roles[0] ]['hide_comment'] ) ?? '';
			$cf_add_suggestion_permission      = isset( $cf_options[ $current_user->roles[0] ]['add_suggestion'] ) ?? '';
			$cf_resolved_suggestion_permission = isset( $cf_options[ $current_user->roles[0] ]['resolved_suggestion'] ) ?? '';
			$cf_hide_suggestion_permission     = isset( $cf_options[ $current_user->roles[0] ]['hide_suggestion'] ) ?? '';
			wp_localize_script(
				$this->plugin_name,
				'cf_permissions',
				array(
					'add_comment'         => $cf_add_comment_permission,
					'resolved_comment'    => $cf_resolved_comment_permission,
					'hide_comment'        => $cf_hide_comment_permission,
					'add_suggestion'      => $cf_add_suggestion_permission,
					'resolved_suggestion' => $cf_resolved_suggestion_permission,
					'hide_suggestion'     => $cf_hide_suggestion_permission,
				)
			);

			$cf_edd     = new CF_EDD();
			$cf_fs_data = array(
				'current_plan' => $cf_edd->get_plan_name(),
			);

			wp_localize_script( $this->plugin_name, 'multicollab_fs', $cf_fs_data );
			wp_localize_script( $this->plugin_name, 'showinfoboard', array( 'showinfoboard' => get_option( 'cf_show_infoboard' ) ) );
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

					$cf_show_multicollab_sidebar = array(
						'cf_show_multicollab_sidebar' => get_option( 'cf_show_multicollab_sidebar' ),
					);
					wp_localize_script( $this->plugin_name, 'multicollab_sidebar', $cf_show_multicollab_sidebar );

					global $wp_version;
					$cf_wp_version = array(
						'wp_version' => $wp_version,
					);
					wp_localize_script( $this->plugin_name, 'multicollab_wp_version', $cf_wp_version );

					wp_enqueue_script( 'jquery-ui-draggable' );
					wp_enqueue_script( 'jquery-ui-droppable' );

					$cf_multiedit_websocket_url = '';
					if( !empty( get_option( 'cf_websocket_options' ) ) ) {
						$cf_multiedit_websocket_url = get_option( 'cf_multiedit_websocket' );
					}

					$cf_multiedit_websocket = array(
						'cf_multiedit_websocket' => $cf_multiedit_websocket_url,
					);

					// load multiedit scripts and styles
					wp_enqueue_script( 'multiedit-script', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/realTimeScripts.build.min.js', array(), wp_rand(), true );
					wp_enqueue_style( 'multiedit-style', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/styles/realTimeEditor.build.min.css', array(), wp_rand(), false );

					wp_localize_script( 'multiedit-script', 'cf_multiedit', $cf_multiedit_websocket );

					wp_enqueue_script( 'cf-block-script', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/dist/activityCentre.build.min.js', array(), wp_rand(), true );
					$cf_protocol_remove = array("http://","https://");
					wp_localize_script(
						'cf-block-script',
						'activityLocalizer',
						array(
							'nonce'                  => wp_create_nonce( 'wp_rest' ),
							'apiUrl'                 => rtrim( get_rest_url(), '/\\'), // No nedded to pass wp-json inside this function as it is already giving same path.
							'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
							'currentUserID'          => get_current_user_id(),
							'cf_multiedit_websocket' => $cf_multiedit_websocket,
							'cf_site_url' => str_replace( $cf_protocol_remove,"",home_url() ),
						)
					);

					// Add unique_key to websocket URL.
					$cf_multiedit_websocket_unique_key = get_option( 'cf_multiedit_websocket_unique_key' );
					if( empty( $cf_multiedit_websocket_unique_key ) ) {
						$cf_multiedit_websocket_unique_key = strtolower( wp_generate_password( 8, false ) );
						update_option( 'cf_multiedit_websocket_unique_key', $cf_multiedit_websocket_unique_key, true );
					}
					$cf_multiedit_websocket_uniqe_array = array(
						'cf_multiedit_websocket_unique_key' => $cf_multiedit_websocket_unique_key,
					);
					wp_localize_script( 'multiedit-script', 'cf_multiedit_websocket_unique_key', $cf_multiedit_websocket_uniqe_array );

		}

		wp_enqueue_script( 'cf-select2-js', trailingslashit( COMMENTING_BLOCK_URL ) . 'admin/assets/js/select2.min.js', array( 'jquery' ), wp_rand(), true );

		wp_enqueue_script( $this->plugin_name . '-general', trailingslashit( COMMENTING_BLOCK_URL ) . '/admin/assets/js/commenting-block-admin-general.js', array(), wp_rand(), false );

	}

	/**
	 * Convert string to linkable email.
	 *
	 * @param string $str Contains the strings that comes from the textarea.
	 *
	 * @return string
	 */
	public function convert_str_to_email( $str ) {
		$mail_pattern = '/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/';

		return preg_replace( $mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str );
	}

	/**
	 * Add Comment function.
	 */
	public function cf_add_comment() {
        $commentList      = filter_input(INPUT_POST, "commentList", FILTER_DEFAULT); // phpcs:ignore
		$commentList      = json_decode( $commentList, true );
		$list_of_comments = $commentList;
		// Get the assigned User ID.
		$assign_to       = filter_input( INPUT_POST, 'assignTo', FILTER_SANITIZE_NUMBER_INT );
		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$arr             = array();

		if (!empty($commentList)){
			$commentList = end( $commentList );
		}
		$metaId      = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_SPECIAL_CHARS );
		$blockType   = filter_input( INPUT_POST, 'blockType', FILTER_SANITIZE_SPECIAL_CHARS );
		$login_user  = wp_get_current_user();
		// If 'commented on' text is blank, stop process.
		if ( empty( $commentList['commentedOnText'] ) ) {
			echo wp_json_encode( array( 'error' => 'Please select a block, text or media to comment on.' ) );
			wp_die();
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$timestamp   = current_time( 'timestamp' );
		$dtTime      = gmdate( $time_format . ' ' . $date_format, $timestamp );

		$commentListOld     = get_post_meta( $current_post_id, $metaId, true );
		$superCareerData    = maybe_unserialize( $commentListOld );
		$assign_label       = 'Assigned to';
		$assigned_user_info = get_userdata( $commentList['assigned'] );
		if ( ! empty( $superCareerData ) ) {
			$assignedExist    = array_column( $superCareerData['comments'], 'assigned' );
			$has_empty_values = ! array_filter( $assignedExist );
            $assign_label = ($has_empty_values == 1) ? 'Assigned to' : 'Reassigned to';  // phpcs:ignore

		}

        $assigned_text = isset($assigned_user_info->display_name) ? ( ($login_user->data->ID == $assigned_user_info->ID) ? $assign_label .' You' : $assign_label. ' ' .$assigned_user_info->display_name ) : ''; // phpcs:ignore
		$arr['userData'] = get_current_user_id();
		// Secure content.
		$arr['thread']   = $this->cf_secure_content( $commentList['thread'] );
		$arr['assigned'] = $assigned_text;

		if ( ! empty( $commentList['attachmentText'] ) ) {
			$arr['attachmentText'] = $commentList['attachmentText'];
		}

		// Update Current Drafts.
		$current_drafts = get_post_meta( $current_post_id, '_current_drafts', true );
		$current_drafts = maybe_unserialize( $current_drafts );
		$current_drafts = empty( $current_drafts ) ? array() : $current_drafts;
		if ( isset( $current_drafts['comments'] ) && 0 !== count( $current_drafts['comments'] ) ) {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		} else {
			$current_drafts['comments'][ $metaId ][] = $timestamp;
		}
		update_post_meta( $current_post_id, '_current_drafts', $current_drafts );

		if ( isset( $superCareerData['comments'] ) && 0 !== count( $superCareerData['comments'] ) ) {

			$superCareerData['comments'][ $timestamp ] = isset( $arr ) ? $arr : '';
			$superCareerData['updated_at']             = $timestamp;
			if ( $assign_to > 0 ) {
				$superCareerData['assigned_to']         = $assign_to;
				$superCareerData['sent_assigned_email'] = false;
			}
			update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
		} else {
			$superCareerData                           = array();
			$superCareerData['comments'][ $timestamp ] = $arr;
			$superCareerData['commentedOnText']        = $commentList['commentedOnText'];
			$superCareerData['updated_at']             = $timestamp;
			$superCareerData['blockType']              = $blockType;
			if ( $assign_to > 0 ) {
				$superCareerData['assigned_to']         = $assign_to;
				$superCareerData['sent_assigned_email'] = false;
			}

			update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );
		}

		update_post_meta( $current_post_id, $metaId, $superCareerData );
		update_post_meta( $current_post_id, 'mc_updated', $timestamp );

		$last_index                                   = count( $list_of_comments ) - 1;
		$list_of_comments[ $last_index ]['timestamp'] = $timestamp;
		// Get assigned user data.
		$assigned_to = null;
		if ( ! empty( $superCareerData['assigned_to'] ) ) {
			$login_user  = wp_get_current_user();
			$user_data   = get_user_by( 'ID', $superCareerData['assigned_to'] );
            $displayName = ($login_user->data->ID == $user_data->ID) ? 'You' : $user_data->display_name; // phpcs:ignore
			$assigned_to = array(
				'ID'           => $user_data->ID,
				'display_name' => $displayName,
				'user_email'   => $user_data->user_email,
				'avatar'       => get_avatar_url( $user_data->ID, array( 'size' => 32 ) ),
			);
		}

		echo wp_json_encode(
			array(
				'dtTime'       => $dtTime,
				'timestamp'    => $timestamp,
				'assignedTo'   => $assigned_to,
				'assignedText' => $assigned_text,
				'arr'          => $arr,
			)
		);

		wp_die();
	}

	/**
	 * Add ajax function to filter HTML tags from add/edit suggestion.
	 * Replace content with filter HTML(without HTML tags) which we get from AJAX response. Github issue: #491.
	 *
	 * @author: Rishi Shah
	 * @since: 3.5
	 *
	 * @return void
	 */
	public function cf_suggestion_text_filter() {

		$newText   	=  isset( $_POST['newText'] ) ? wp_kses_post( $_POST['newText'] ) : ''; // phpcs:ignore.
		$newText = html_entity_decode( $newText );
		$newText = $this->cf_secure_content( $newText );

		echo wp_json_encode(
			array(
				'arr' => $newText,
			)
		);

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
			'a'   => array(
				'contenteditable' => array(),
				'href'            => array(),
				'target'          => array(),
				'style'           => array(),
				'class'           => array( 'js-mentioned' ),
				'data-email'      => array(),
				'data-display-name' => array(),
				'data-user-id'	  	=> array(),
			),
			'div' => array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			),
			'br'  => array(),
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
		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_SPECIAL_CHARS );

        $edited_comment = filter_input(INPUT_POST, "editedComment", FILTER_DEFAULT); // phpcs:ignore
		$edited_comment = htmlspecialchars_decode( $edited_comment );
		$edited_comment = html_entity_decode( $edited_comment );
		$edited_comment = json_decode( $edited_comment, true );
		// Make content secured.
		$edited_comment['thread']          = $this->cf_secure_content( $edited_comment['thread'] );
		$old_timestamp                     = $edited_comment['timestamp'];
		$commentListOld                    = get_post_meta( $current_post_id, $metaId, true );
		$commentListOld                    = maybe_unserialize( $commentListOld );
		$date_format                       = get_option( 'date_format' );
		$time_format                       = get_option( 'time_format' );
		$edited_timestamp                  = current_time( 'timestamp' );
		$edited_comment['editedTimestamp'] = $edited_timestamp;
		$edited_comment['editedTime']      = gmdate( $time_format . ' ' . $date_format, $edited_timestamp );
		$edited_draft                      = array();
		$edited_draft['thread']            = $edited_comment['thread'];
		$edited_draft['attachmentText']    = isset( $edited_comment['attachmentText'] ) ? $edited_comment['attachmentText'] : '';
		$commentListOld['comments'][ $old_timestamp ]['draft_edits']     = $edited_draft;
		$commentListOld['comments'][ $old_timestamp ]['editedTime']      = $edited_comment['editedTime'];
		$commentListOld['comments'][ $old_timestamp ]['editedTimestamp'] = $edited_comment['editedTimestamp'];
		update_post_meta( $current_post_id, $metaId, $commentListOld );

		// Update Current Drafts.
		$current_drafts                        = get_post_meta( $current_post_id, '_current_drafts', true );
		$current_drafts                        = maybe_unserialize( $current_drafts );
		$current_drafts                        = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['edited'][ $metaId ][] = $old_timestamp;

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

		echo wp_json_encode(
			array(
				'arr' => $edited_comment,
			)
		);
		wp_die();

	}
	/**
	 * Delete Comment function.
	 */
	public function cf_delete_comment() {
		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_SPECIAL_CHARS );
		$timestamp       = filter_input( INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT );
		$metas           = get_post_meta( $current_post_id );

		// Update Current Drafts.
		$current_drafts                         = get_post_meta( $current_post_id, '_current_drafts', true );
		$current_drafts                         = maybe_unserialize( $current_drafts );
		$current_drafts                         = empty( $current_drafts ) ? array() : $current_drafts;
		$current_drafts['deleted'][ $metaId ][] = $timestamp;

		// Checking if user deleted the recently added comment.
		if ( isset( $current_drafts['deleted'] ) && 0 !== $current_drafts['deleted'] ) {
			if ( isset( $current_drafts['comments'] ) && 0 !== $current_drafts['comments'] ) {
				foreach ( $current_drafts['deleted'] as $el => $timestamps ) {
					if ( array_key_exists( $el, $current_drafts['comments'] ) ) {
						$prev_state = $metas[ $el ][0];
						$prev_state = maybe_unserialize( $prev_state );
						// Deleteing comments if users delete comments at the same moment.
						foreach ( $timestamps as $t ) {
							$t       = intval( $t );
							$get_key = array_search( $t, $current_drafts['comments'][ $el ], true );

							if ( $get_key !== false ) {
								unset( $current_drafts['comments'][ $el ][ $get_key ] );
								unset( $current_drafts['deleted'][ $el ][ $get_key ] );
								unset( $prev_state['comments'][ $t ] );
							}
						}
						$metas[ $el ][0] = maybe_serialize( $prev_state );
						update_post_meta( $current_post_id, $el, $prev_state );
					}
				}
			}
		}
		update_post_meta( $current_post_id, '_current_drafts', $current_drafts );
		update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );

		// multiedit change - delete the comment from database
		$comments = get_post_meta( $current_post_id, $metaId, true );
		$comments = maybe_unserialize( $comments );
		$comments = empty( $comments ) ? array() : $comments;

		if ( ! empty( $comments ) ) {
			$current_timestamp                                = current_time( 'timestamp' );
			$comments['comments'][ $timestamp ]['status']     = 'deleted';
			$comments['comments'][ $timestamp ]['created_at'] = $current_timestamp;
			$comments['comments'][ $current_timestamp ]       = $comments['comments'][ $timestamp ];
			unset( $comments['comments'][ $timestamp ] );
			$comments['updated_at'] = $current_timestamp;
			update_post_meta( $current_post_id, $metaId, $comments );
		}
		wp_die();
	}

	/**
	 * Save settings of the plugin.
	 */
	public function cf_save_settings() {
		$form_data = array();
		parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_URL ), $form_data );

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

		if ( isset( $form_data['cf_show_multicollab_sidebar'] ) ) {
			update_option( 'cf_show_multicollab_sidebar', $form_data['cf_show_multicollab_sidebar'] );
		} else {
			update_option( 'cf_show_multicollab_sidebar', 0 );
		}

		echo 'saved';
		wp_die();
	}

	/**
	 * Save Publishing settings of the plugin.
	 */
	public function cf_save_suggestions() {
		$form_data = array();
		parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_URL ), $form_data );

		if ( isset( $form_data['cf_give_alert_message'] ) ) {
			update_option( 'cf_give_alert_message', $form_data['cf_give_alert_message'] );
		} else {
			delete_option( 'cf_give_alert_message' );
		}

		echo 'saved';
		wp_die();
	}

	/**
	 * Save Real-Time Co-editing settings of the plugin.
	 */
	public function cf_save_multiedit_settings() {
		$cf_edd = new CF_EDD();

		$form_data = array();
		parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_URL ), $form_data );

		update_option( 'cf_websocket_options', $form_data['cf_websocket_options'], true );

		if( 'cf_websocket_default' === $form_data['cf_websocket_options'] ) {

			$cf_websocket_url  = CF_STORE_URL . 'wp-json/cf-websocket-url/v2/cf-websocket-url?' . wp_rand();
			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$cf_websocket_url_request = vip_safe_wp_remote_get( $cf_websocket_url, 3, 1, 20 ); //phpcs:ignore
			} else {
				$cf_websocket_url_request = wp_remote_get( $cf_websocket_url, array( 'timeout' => 20 ) ); // phpcs:ignore
			}
			
			$cf_websocket_url_request_data = json_decode( $cf_websocket_url_request['body'], true );

			if ( $cf_edd->is__premium_only() ) {
				update_option( 'cf_multiedit_websocket', $cf_websocket_url_request_data['pro']['wsurl'] );
			} else {
				update_option( 'cf_multiedit_websocket', $cf_websocket_url_request_data['free']['wsurl'] );
			}

		} else if( 'cf_websocket_custom' === $form_data['cf_websocket_options'] ) {

			if ( isset( $form_data['cf_multiedit_websocket'] ) ) {
				update_option( 'cf_multiedit_websocket', $form_data['cf_multiedit_websocket'] );
			} else {
				delete_option( 'cf_multiedit_websocket' );
			}

		}

		if ( isset( $form_data['cf_websocket_options'] ) ) {
			update_option( 'cf_websocket_options', $form_data['cf_websocket_options'] );
		} else {
			delete_option( 'cf_websocket_options' );
		}

		echo 'saved';
		wp_die();
	}

	/**
	 * Save email notification settings of the plugin.
	 */
	public function cf_save_email_notification() {
		$form_data = array();
		parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_URL ), $form_data );

		if ( isset( $form_data['cf_admin_notif'] ) ) {
			update_option( 'cf_admin_notif', $form_data['cf_admin_notif'] );
		} else {
			delete_option( 'cf_admin_notif' );
		}

		echo 'saved';
		wp_die();
	}

	/**
	 * Save Suggestion mode settings of the plugin.
	 */
	public function cf_save_suggestions_mode() {
		$form_data = array();
		parse_str( filter_input( INPUT_POST, 'formData', FILTER_SANITIZE_URL ), $form_data );

		if ( 'cf_suggestion_specific_post_types' === $form_data['cf_suggestion_mode_option_name'] && empty( $form_data['cf_specific_post_types_values'] ) ) {
			echo 'empty_custom_post_type';
			wp_die();
		}

		if ( 'cf_suggestion_specific_post_categories' === $form_data['cf_suggestion_mode_option_name'] && empty( $form_data['cf_specific_post_categories_values'] ) ) {
			echo 'empty_custom_post_type';
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

		echo 'saved';
		wp_die();
	}



	/**
	 * Save important details in a localstorage.
	 */
	public function cf_store_in_localstorage() {

		// Returning show_avatar option to display avatars (or not to).
		$show_avatars = get_option( 'show_avatars' );
		$show_avatars = '1' === $show_avatars ? $show_avatars : 0;

		// Store plugin URL in localstorage so that its easy
		// to get sub site URL in JS files in Multisite environment.

		echo wp_json_encode(
			array(
				'showAvatars'         => $show_avatars,
				'commentingPluginUrl' => COMMENTING_BLOCK_URL,
			)
		);
		wp_die();
	}


	/**
	 * Resolve Thread function.
	 */
	public function cf_resolve_thread() {

		// Get current user details.
		$curr_user                 = wp_get_current_user();
		$user_id                   = $curr_user->ID;
		$current_user_email        = $curr_user->user_email;
		$current_user_display_name = $curr_user->display_name;

		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$metaId          = filter_input( INPUT_POST, 'metaId', FILTER_SANITIZE_SPECIAL_CHARS );
		$timestamp       = current_time( 'timestamp' );

		$prev_state                       = get_post_meta( $current_post_id, $metaId, true );
		$prev_state                       = maybe_unserialize( $prev_state );
		$prev_state['resolved']           = 'true';
		$prev_state['resolved_timestamp'] = $timestamp;
		$prev_state['resolved_by']        = $user_id;
		$prev_state['updated_at'] 		  = $timestamp;

		// Initiate Email Class Object.
		$this->cf_initiate_email_class();
		
		update_post_meta( $current_post_id, $metaId, $prev_state );
		update_post_meta( $current_post_id, 'th' . $metaId, $timestamp );

		// Send Email.
		$p_link     = get_edit_post_link( $current_post_id );
		$p_title    = get_the_title( $current_post_id ); // Removed phpcs:ignore by Rishi Shah.
		$site_title = get_bloginfo( 'name' ); // Removed phpcs:ignore by Rishi Shah.
		$http_host  = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS );
		$arrow_svg  = '<span style="vertical-align: middle;padding-right: 5px;padding-left:5px;"><img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/email-arrow.png' ) . '" alt="Arrow" width="22" height="13" /></span>';
		$html       = '';
		$html      .= '<div class="comment-box comment-resolved" style="background:#fff;width:95%;font-family: Roboto,sans-serif;padding-top:40px;padding-right: 10px;padding-bottom: 20px;padding-left: 10px;">';
		$html      .= '<div class="comment-box-header" style="margin-bottom:30px;">';
		$html      .= '<p style="margin:0;padding-bottom:20px;font-size:18px;"><a href="mailto:' . esc_attr( $current_user_email ) . '" class="" style="color: #000;text-decoration: none; text-transform: capitalize;font-weight: 700;">' . esc_html( $current_user_display_name ) . '</a> ' . __( 'has resolved the following thread.', 'content-collaboration-inline-commenting' ) . '</p>';
		$html      .= '<div class="comment-box-header-right">';
		$html      .= '<h2 class="comment-page-web" style="margin:0;display:inline-block;"><a href="' . esc_url( get_site_url() ) . '" target="_blank" style="font-size:20px;color:#4B1BCE;text-decoration:underline;color:#4B1BCE;word-wrap: break-word;">' . esc_html( $http_host ) . '</a></h2>';
		if ( ! empty( $p_title ) ) {
			$html .= $arrow_svg . '<h2 class="comment-page-title" style="margin:0;display:inline-block;"><a href="' . esc_url( $p_link ) . '" style="color:#4B1BCE;text-decoration:underline;font-size:20px;">' . esc_html( wp_trim_words( $p_title, 3, '...' ) ) . '</a></h2></div>';
		}
		$html .= '</div>';
		$html .= '<div class="comment-box-body" style="border:1px solid #eee;border-radius:20px;padding:30px;">';
		$html .= '<h3 class="head-with-icon" style="margin:0;padding-bottom:30px;font-family:Roboto,sans-serif;font-weight:500;font-size:26px;color:#000;">';
		$html .= '<span class="icon-resolved" style="padding-right:10px;vertical-align:middle;">';
		$html .= '<img src="' . esc_url( COMMENTING_BLOCK_URL . 'admin/assets/images/icon-check-fill.png' ) . '" alt="Resolved" />';
		$html .= '</span>' . __( ' Resolved Thread Comments', 'content-collaboration-inline-commenting' );
		$html .= '</h3>';

		$elid              = str_replace( '_', '', $el );
		$comments          = get_post_meta( $current_post_id, $metaId, true );
		$commented_on_text = $comments['commentedOnText'];
		$list_of_comments  = isset( $comments['comments'] ) ? $comments['comments'] : '';
		$blockType         = isset( $comments['blockType'] ) ? $comments['blockType'] : '';
		$link              = $p_link . '&current_url=' . $metaId;

		// Notify users about the resolved thread.
		$this->email_class->cf_email_resolved_thread(
			array(
				'html'                      => $html,
				'post_title'                => $p_title,
				'site_title'                => $site_title,
				'current_user_email'        => $current_user_email,
				'current_user_display_name' => $current_user_display_name,
				'commented_on_text'         => $commented_on_text,
				'list_of_comments'          => $list_of_comments,
				'block_type'                => $blockType,
				'post_edit_link'            => $link,
			)
		);
		wp_die();
	}

	/**
	 * Rest API for Gutenberg Commenting Feature.
	 */
	public function cf_rest_api() {
		register_rest_route(
			'cf',
			'cf-get-comments-api',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'cf_get_comments' ),
				'permission_callback' => '__return_true',
			)
		);
	}
	/**
	 * Update Autodraft meta on load.
	 */
	public function cf_update_meta() {
		$current_post_id = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$autoDraft_ids          = filter_input(INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		update_post_meta( $current_post_id, '_autodraft_ids', $autoDraft_ids );
		wp_die();
	}
	public function register_post_meta_autodraft_id() {
		register_post_meta(
			'',
			'_autodraft_ids',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'array',
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
				'auth_callback' => function() {
					return true; },
			)
		);
	}

	/* SGEDIT */
	/**
	 * Register post meta field for suggestion history and suggestion mode enable.
	 */

	public function sg_register_post_meta_field() {
		register_post_meta(
			'',
			'_sb_is_suggestion_mode',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'auth_callback' => function() {
					return true; },
			)
		);
		register_post_meta(
			'',
			'_sb_show_suggestion_boards',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'auth_callback' => function() {
					return true; },
			)
		);
		register_post_meta(
			'',
			'_sb_suggestion_history',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return true; },
			)
		);
		register_post_meta(
			'',
			'_sb_update_block_changes',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return true; },
			)
		);
		register_post_meta(
			'',
			'_is_real_time_mode',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'default'       => false,
				'type'          => 'boolean',
				'auth_callback' => function() {
					return true; },
			)
		);

		register_post_meta(
			'',
			'_realtime_collaborators',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return true; },
			)
		);

	}

	/**
	 * ajax call to update suggestion history post meta
	 */
	public function sg_update_suggestion_history() {

		/* Check for nonce verification.*/
		check_ajax_referer( COMMENTING_NONCE, 'nonce' );

		if ( isset( $_POST['suggestionHistory'] ) ) {
			$current_post_id   = filter_input( INPUT_POST, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
            $suggestionHistory  = $_POST["suggestionHistory"]; // phpcs:ignore
			// Update suggestion history.
			if ( metadata_exists( 'post', $current_post_id, '_sb_suggestion_history' ) ) {
				update_post_meta( $current_post_id, '_sb_suggestion_history', $suggestionHistory );
			} else {
				add_post_meta( $current_post_id, '_sb_suggestion_history', $suggestionHistory );
			}
            echo get_post_meta( $current_post_id, '_sb_suggestion_history',true);// phpcs:ignore
			wp_die();
		}
	}

	/* SGEDIT */

	/**
	 * Function is used to fetch stored comments.
	 *
	 * @return mixed|\WP_REST_Response
	 */
	public function cf_get_comments() {
		$current_post_id = filter_input( INPUT_GET, 'currentPostID', FILTER_SANITIZE_NUMBER_INT );
		$userDetails     = array();
		$elID            = filter_input( INPUT_GET, 'elID', FILTER_SANITIZE_SPECIAL_CHARS );

		$commentList     = get_post_meta( $current_post_id, $elID, true );
		$superCareerData = maybe_unserialize( $commentList );
		$comments        = isset( $superCareerData['comments'] ) ? $superCareerData['comments'] : array();
		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$login_user      = wp_get_current_user();

		foreach ( $comments as $t => $val ) {
			if ( isset( $val['editedTime'] ) ) {
				$val['editedTime'] = $val['editedTime'];
			} else {
				$val['editedTime'] = '';
			}
			$user_info       = get_userdata( $val['userData'] );
			$username        = isset( $user_info->display_name ) ? $user_info->display_name : '';
			$user_role       = isset( $user_info->roles ) ? implode( ', ', $user_info->roles ) : '';
			$profile_url     = get_avatar_url( isset( $user_info->user_email ) ? $user_info->user_email : '' );
			$thread          = $val['thread'];
			$cstatus         = isset( $val['status'] ) ? $val['status'] : '';
			$cstatus         = isset( $val['status'] ) ? $val['status'] : '';
			$edited_draft    = isset( $val['draft_edits']['thread'] ) ? $val['draft_edits']['thread'] : '';
			$updatedTime     = $val['editedTime'];
			$assigned_text   = $val['assigned'];
			$editedTimestamp = isset( $val['editedTimestamp'] ) ? $val['editedTimestamp'] : '';
			$attachment_text = isset( $val['attachmentText'] ) ? $val['attachmentText'] : '';
			$date            = gmdate( $time_format . ' ' . $date_format, $t );

			if ( 'deleted' !== $cstatus ) {
				array_push(
					$userDetails,
					array(
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
					)
				);
			}
		}

		// Get assigned user data
		$assigned_to = null;
		if ( isset( $superCareerData['assigned_to'] ) && $superCareerData['assigned_to'] > 0 ) {
			$login_user  = wp_get_current_user();
			$user_data   = get_user_by( 'ID', $superCareerData['assigned_to'] );
            $displayName = ($login_user->data->ID == $user_data->ID) ? 'You' : $user_data->display_name; // phpcs:ignore
			$assigned_to = array(
				'ID'           => $user_data->ID,
				'display_name' => $displayName,
				'user_email'   => $user_data->user_email,
				'avatar'       => get_avatar_url( $user_data->ID, array( 'size' => 32 ) ),
			);
		}

		$data                    = array();
		$data['userDetails']     = $userDetails;
		$data['resolved']        = ( isset( $superCareerData['resolved'] ) && 'true' === $superCareerData['resolved'] ) ? 'true' : 'false';
		$data['commentedOnText'] = isset( $superCareerData['commentedOnText'] ) ? $superCareerData['commentedOnText'] : '';
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

		// Get user roles who can edit pages/posts./@author Rishi Shah/@since 2.0.4.5
		$roles = array();
		foreach ( wp_roles()->roles as $role_name => $role_obj ) {
			if ( ! empty( $role_obj['capabilities']['edit_pages'] ) || ! empty( $role_obj['capabilities']['edit_posts'] ) || ! empty( $role_obj['capabilities']['edit_others_pages'] ) || ! empty( $role_obj['capabilities']['edit_others_posts'] ) ) {
				$roles[] = $role_name;
			}
		}

		// WP User Query.
		$users = new WP_User_Query(
			array(
				'number'   => 9999,
				'role__in' => $roles,
			)
		);

		// Fetch out all user's email.
		$email_list = array();

		/**
		 * Set transient to imporve @ get users names.
		 *
		 * @author: Rishi Shah
		 * @version 3.4
		 */
		$system_users = get_transient( 'cf_system_users' );
		if ( false === $system_users ) {
			$system_users = $users->get_results();
			set_transient( 'cf_system_users', $system_users, 30 * MINUTE_IN_SECONDS );
		}
		foreach ( $system_users as $user ) {
			$needToSortArray = $this->cf_get_reorder_user_role( $user->roles );
			$user->roles     = $needToSortArray;
			if ( $user->has_cap( 'edit_posts' ) || $user->has_cap( 'edit_pages' ) ) {
				$email_list[] = array(
					'ID'                => $user->ID,
					'role'              => implode( ', ', $user->roles ),
					'display_name'      => $user->display_name,
					'full_name'         => $user->display_name,
					'first_name'        => $user->first_name,
					'user_email'        => $user->user_email,
					'avatar'            => get_avatar_url(
						$user->ID,
						array(
							'size' => '24',
						)
					),
					'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
					'edit_others_posts' => isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '',
				);
			}
		}

		$response = $email_list;
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
		$niddle = filter_input( INPUT_POST, 'niddle', FILTER_SANITIZE_SPECIAL_CHARS );
		$niddle = substr( $niddle, 1 );
		if ( ! empty( $niddle ) && '@' !== $niddle ) {

			// Get user roles who can edit pages/posts.
			$roles = array();
			foreach ( wp_roles()->roles as $role_name => $role_obj ) {
				if ( ! empty( $role_obj['capabilities']['edit_pages'] ) || ! empty( $role_obj['capabilities']['edit_posts'] ) || ! empty( $role_obj['capabilities']['edit_others_pages'] ) || ! empty( $role_obj['capabilities']['edit_others_posts'] ) ) {
					$roles[] = $role_name;
				}
			}

			$users = new WP_User_Query(
				array(
					'number'         => 9999,
					'search'         => $niddle . '*',
					'search_columns' => array( 'display_name' ),
					'role__in'       => $roles,
				)
			);

			// Fetch out matched user's email.
			$email_list   = array();
			$system_users = $users->get_results();
			foreach ( $system_users as $user ) {
				$needToSortArray = $this->cf_get_reorder_user_role( $user->roles );
				$user->roles     = $needToSortArray;
				if ( $user->has_cap( 'edit_posts' ) || $user->has_cap( 'edit_pages' ) ) {
					$email_list[] = array(
						'ID'                => $user->ID,
						'role'              => implode( ', ', $user->roles ),
						'display_name'      => $user->display_name,
						'full_name'         => $user->display_name,
						'first_name'        => $user->first_name,
						'user_email'        => $user->user_email,
						'avatar'            => get_avatar_url(
							$user->ID,
							array(
								'size' => '24',
							)
						),
						'profile'           => admin_url( "/user-edit.php?user_id  ={ {$user->ID}}" ),
						'edit_others_posts' => isset( $user->allcaps['edit_others_posts'] ) ? $user->allcaps['edit_others_posts'] : '',
					);
				}
			}
			$response = $email_list;
		} elseif ( '@' === $niddle ) {
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

		$results = array();
		if ( count( $user_emails ) > 0 ) {
			foreach ( $user_emails as $user_email ) {
				$user_data        = get_user_by( 'email', $user_email );
				$needToSortArray  = $this->cf_get_reorder_user_role( $user_data->roles );
				$user_data->roles = $needToSortArray;
				$results[]        = array(
					'ID'           => $user_data->ID,
					'display_name' => $user_data->display_name,
					'user_email'   => $user_data->user_email,
					'role'         => implode( ', ', $user_data->roles ),
					'avatar'       => get_avatar_url( $user_data->ID ),
				);
			}
		}

		echo wp_json_encode( $results );
		wp_die();
	}


	/**
	 * Add user role to users WordPress api
	 *
	 * @return void
	 */
	function create_api_user_meta_field_for_userrole() {

		register_rest_field(
			'user',
			'userRole',
			array(
				'get_callback' => array( $this, 'get_userRole_for_api' ),
				'schema'       => null,
			)
		);

		register_rest_field(
			'user',
			'user_email',
			array(
				'get_callback'    => function ( $user ) {
						return get_userdata( $user['id'] )->user_email;
				},
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}


	/**
	 * Get user role by user ID
	 *
	 * @param object
	 * @return string
	 */
	function get_userRole_for_api( $object ) {
		// get the id of the post object array
		$user_id = $object['id'];

		$user_meta = get_userdata( $user_id );

		$user_roles = $user_meta->roles;

		// return the post meta
		return $user_roles[0];
	}

	/**
	 * AJAX function for submit free user feedback form.
	 *
	 * @return void
	 */
	public function cf_deactive_plugin_free() {

		$current_user        = wp_get_current_user();
		$subscription_option = filter_input( INPUT_POST, 'subscription_option', FILTER_SANITIZE_SPECIAL_CHARS );
		$subscription_option = str_replace( '&#39;', "'", $subscription_option );

		$fs_feedback_message = filter_input( INPUT_POST, 'fs_feedback_message', FILTER_SANITIZE_SPECIAL_CHARS );
		$current_date        = gmdate( 'Y-m-d' );

		$data_insert_array = array(
			'user_name'           => $current_user->display_name,
			'user_email'          => $current_user->user_email,
			'feedback_type'       => $subscription_option,
			'feedback_message'    => $fs_feedback_message,
			'feedback_date'       => $current_date,
			'free_plugin_version' => COMMENTING_BLOCK_VERSION
		);

		$feedback_api_url = CF_STORE_URL . '/wp-json/cf-free-user-feedback/v2/cf-free-user-feedback';
		$query_url        = $feedback_api_url . '?' . http_build_query( $data_insert_array );
		$response         = wp_remote_get( $query_url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get

		if ( 'success' === trim( $response['body'] ) ) {
			deactivate_plugins( COMMENTING_BLOCK_BASE );
			echo 'success';
		}

		wp_die();
	}

	/**
	 * User authentication function while redirect guest.
	 *
	 * @param object|array $user - contains user details.
	 * @param string       $username_  - contains user name
	 * @param string       $password   - user's password.
	 * @return mixed
	 */
	public function cf_guest_auto_login( $user, $username_, $password ) {

		$user_id  = filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT );
		$username = filter_input( INPUT_GET, 'username', FILTER_SANITIZE_SPECIAL_CHARS );
		$post_id  = filter_input( INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$token    = filter_input( INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS );

		$guest_token = get_user_meta( (int) $user_id, 'multicollab_guest_token', true );
		$guest_token = ( ! is_array( $guest_token ) ) ? (array) $guest_token : $guest_token;

		if ( ! empty( $user_id ) && ! empty( $username ) && ( ! in_array( $token, $guest_token, true ) ) ) {
			$error_redirect = site_url( 'landing-page', 'login' );
			$error_string   = 'Token miss match';
			$error_redirect = add_query_arg(
				array(
					'message' => $error_string,
				),
				$error_redirect
			);

			wp_safe_redirect( $error_redirect, 302, 'Multicollab-token-message' );
			exit;
		}

		$multicollab_guest_token_timestamp = get_user_meta( $user_id, 'multicollab_guest_token_timestamp', true );

		if ( ! empty( $multicollab_guest_token_timestamp ) ) {
			if ( isset( $multicollab_guest_token_timestamp[ $token ] ) && ! empty( $multicollab_guest_token_timestamp[ $token ] ) ) {
				if ( $multicollab_guest_token_timestamp[ $token ][0] <= strtotime( '-48 hours' ) && '' === $multicollab_guest_token_timestamp[ $token ][1] ) { // Check if timestamp is more than 48 hours and user not visited.

					// LINK is expired.
					wp_die( esc_html__( 'Link is Expired.', 'content-collaboration-inline-commenting' ) );

				} elseif ( $multicollab_guest_token_timestamp[ $token ][0] >= strtotime( '-48 hours' ) && '' === $multicollab_guest_token_timestamp[ $token ][1] ) { // If timestamp is less than 48 hours and user not visited.

					// Update value of visited link.
					$multicollab_guest_token_timestamp[ $token ][1] = 'Visited';
					update_user_meta( $user_id, 'multicollab_guest_token_timestamp', $multicollab_guest_token_timestamp );
				} else {
				}
			}
		}

		if ( $user_id && $username ) {
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id );
			$user_data = new WP_User( $user_id );
			do_action( 'wp_login', $username, $user_data );

			// redirect to login URL.
			$editor_url = site_url( 'wp-admin/post.php', 'post-editor' );

			$editor_url = add_query_arg(
				array(
					'post'   => $post_id,
					'action' => 'edit',
				),
				$editor_url
			);

			wp_safe_redirect( $editor_url, 302, 'Multicollab-editor-redirect' );
			exit;
		}
		if ( ! empty( $user_data ) ) {
			return $user_data;
		} else {
			return $user;
		}
	}

	/**
	 * Add custom attribute to core block.
	 *
	 * @param array $metadata Array of meta data.
	 * @return array
	 */
	public function filter_block_type_metadata( $metadata ) {
		$allowedBlocks_widget = array('core/rss','core/tag-cloud','core/latest-comments','core/archives','core/calendar','woocommerce/product-best-sellers','woocommerce/product-new','woocommerce/product-categories','woocommerce/product-top-rated',
		'woocommerce/product-category','woocommerce/handpicked-products','woocommerce/product-tag','woocommerce/products-by-attribute','woocommerce/filled-cart-block');

		if ( ( str_starts_with( $metadata['name'], 'core/' ) || str_starts_with( $metadata['name'], 'woocommerce/' ) )
            && 
			( 
				in_array($metadata['name'], $allowedBlocks_widget,true) 
				|| str_contains($metadata['name'], 'woocommerce/cart-') 
				|| str_contains($metadata['name'], 'woocommerce/checkout-') 
			)
		) {
                $metadata['attributes']['datatext'] = [
                    'type'    => 'string',
                    'default' => true,
                ];
        }

        return $metadata;
	}

}



