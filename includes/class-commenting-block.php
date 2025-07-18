<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    content-collaboration-inline-commenting
 *
 * @author     multidots
 */
if ( ! class_exists( 'Commenting_block' ) ) :
	class Commenting_block {


		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Commenting_block_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			if ( defined( 'COMMENTING_BLOCK_VERSION' ) ) {
				$this->version = COMMENTING_BLOCK_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'COMMENTING_BLOCK';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Commenting_block_Loader. Orchestrates the hooks of the plugin.
		 * - Commenting_block_i18n. Defines internationalization functionality.
		 * - Commenting_block_Admin. Defines all hooks for the admin area.
		 * - Commenting_block_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-i18n.php';

			/**
			 * The class responsible for generic functions.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-functions.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-admin.php';

			/**
			 * This class is responsible for defining all custom rest route endpoints.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-rest-routes.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'public/class-commenting-block-public.php';

			/**
			 * Include the Email template class and initiate the object.
			*/

			$this->loader = new Commenting_block_Loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the commenting_block_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {
			$plugin_i18n = new Commenting_block_i18n();

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {
			$plugin_admin = new Commenting_block_Admin( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'cf_enqueue_styles' );
			$this->loader->add_action( 'enqueue_block_assets', $plugin_admin, 'cf_enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'cf_enqueue_scripts' );
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'cf_user_tour_enqueue_pointer');
			$this->loader->add_action( 'wp_ajax_cf_comments_history', $plugin_admin, 'cf_comments_history' );
			$this->loader->add_action( 'wp_ajax_cf_update_click', $plugin_admin, 'cf_update_click' );
			$this->loader->add_action( 'wp_ajax_cf_get_user', $plugin_admin, 'cf_get_user' );
			$this->loader->add_action( 'wp_ajax_cf_add_comment', $plugin_admin, 'cf_add_comment' );
			$this->loader->add_action( 'wp_ajax_cf_update_comment', $plugin_admin, 'cf_update_comment' );
			$this->loader->add_action( 'wp_ajax_cf_delete_comment', $plugin_admin, 'cf_delete_comment' );
			$this->loader->add_action( 'wp_ajax_cf_delete_attachment', $plugin_admin, 'cf_delete_attachment' );
			$this->loader->add_action( 'wp_ajax_cf_resolve_thread', $plugin_admin, 'cf_resolve_thread' );
			$this->loader->add_action( 'wp_ajax_cf_store_in_localstorage', $plugin_admin, 'cf_store_in_localstorage' );
			$this->loader->add_action( 'wp_ajax_cf_save_settings', $plugin_admin, 'cf_save_settings' );
			$this->loader->add_action( 'wp_ajax_cf_save_slack_intigration', $plugin_admin, 'cf_save_slack_intigration' );
			$this->loader->add_action( 'wp_ajax_cf_slack_intigration_revoke', $plugin_admin, 'cf_slack_intigration_revoke' );
			$this->loader->add_action( 'wp_ajax_cf_save_permissions', $plugin_admin, 'cf_save_permissions' );
			$this->loader->add_action( 'wp_ajax_cf_save_suggestions', $plugin_admin, 'cf_save_suggestions' );
			$this->loader->add_action( 'wp_ajax_cf_save_email_notification', $plugin_admin, 'cf_save_email_notification' );
			$this->loader->add_action( 'wp_ajax_cf_save_suggestions_mode', $plugin_admin, 'cf_save_suggestions_mode' );
			$this->loader->add_action( 'wp_ajax_cf_get_user_email_list', $plugin_admin, 'cf_get_user_email_list' );
			$this->loader->add_action( 'wp_ajax_cf_get_matched_user_email_list', $plugin_admin, 'cf_get_matched_user_email_list' );
			$this->loader->add_action( 'wp_ajax_cf_get_activities', $plugin_admin, 'cf_get_activities' );
			$this->loader->add_action( 'wp_ajax_cf_get_activity_details', $plugin_admin, 'cf_get_activity_details' );
			$this->loader->add_action( 'wp_ajax_cf_migrate_to_pro', $plugin_admin, 'cf_migrate_to_pro' );
			$this->loader->add_action( 'wp_ajax_cf_get_assignable_user_list', $plugin_admin, 'cf_get_assignable_user_list' );
			$this->loader->add_action( 'rest_api_init', $plugin_admin, 'cf_rest_api' );
			$this->loader->add_action( 'wp_ajax_cf_update_meta', $plugin_admin, 'cf_update_meta' );
			$this->loader->add_action( 'wp_ajax_cf_license_activation', $plugin_admin, 'cf_license_activation' );
			$this->loader->add_action( 'wp_ajax_cf_deactive_plugin_free', $plugin_admin, 'cf_deactive_plugin_free' );

			// Replace content with filter HTML(without HTML tags) which we get from AJAX response. Github issue: #491. @author: Rishi Shah @since: 3.5
			$this->loader->add_action( 'wp_ajax_cf_suggestion_text_filter', $plugin_admin, 'cf_suggestion_text_filter' );

			$this->loader->add_action( 'init', $plugin_admin, 'sg_register_post_meta_field' );
			$this->loader->add_action( 'wp_ajax_sg_update_suggestion_history', $plugin_admin, 'sg_update_suggestion_history' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public = new Commenting_block_Public( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'cf_enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'cf_enqueue_scripts' );
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Commenting_block_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Plugin Setup (On Activation)
		 *
		 * @package MYS Modules
		 * @since 1.0.0
		 */
		public static function cf_redirect_on_activate( $plugin = false ) {
			if ( COMMENTING_BLOCK_BASE === $plugin ) {

				// Create getting started page from API on plugin activation.
				$getting_started_api_url =  CF_STORE_URL . 'wp-json/cf-getting-started-draft-page/v2/cf-getting-started-draft-page?' . wp_rand();
				if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
					$response = vip_safe_wp_remote_get( $getting_started_api_url, 3, 1, 20 );
				} else {
					$response = wp_remote_get( $getting_started_api_url ); // phpcs:ignore
				}

				if ( ! empty( $response['body'] ) ) {
					$response_data = $response['body'];
					$response_data_array = json_decode( $response_data, true );
					$args = array(
						'title' => $response_data_array['post_title'],
						'post_type'         => 'post',
						'post_status'       => 'draft',
					);
					$posts_array = get_posts( $args );
					if( ! empty( $posts_array ) ){
						$existing_getting_started_post = array(
							'ID' =>  $posts_array[0]->ID,
							'post_title'    => $response_data_array['post_title'],
							'post_status'   => $posts_array[0]->post_status,
						);
						wp_update_post( $existing_getting_started_post );
					} else {
						$wordpress_post = array(
							'post_title' => $response_data_array['post_title'],
							'post_content' => $response_data_array['post_content'],
							'post_status' => 'draft',
							'post_author' => 1,
							'post_type' => 'post'
						);
						wp_insert_post( $wordpress_post );
					}

				}

				// Delete setting options.
				delete_option( 'cf_hide_floating_icons' );
				delete_option( 'cf_admin_notif' );
				delete_option( 'cf_give_alert_message' );
				delete_option( 'cf_suggestion_mode_option_name' );
				
				wp_redirect(
					add_query_arg(
						array(
							'page' => 'multicollab_setup_wizard',
						),
						admin_url( 'admin.php' )
					)
				);
				exit();
				
			}
		}
		public static function cf_deactivate_notice() {      ?>
			<p>
						<?php
						printf(
							/* translators: %1$s: the plugin name */
							esc_html__( ' Gutenberg block harm when deactivate : %1$s.', 'content-collaboration-inline-commenting' ),
							sprintf(
								'<strong>%1$s</strong>',
								esc_html__( 'Multicollab', 'content-collaboration-inline-commenting' )
							)
						);
						?>

					</p>
					<?php
					exit();
		}
	}
endif;
