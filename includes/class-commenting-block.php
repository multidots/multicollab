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
	        require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-loader.php'; // phpcs:ignore

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'includes/class-commenting-block-i18n.php'; // phpcs:ignore

			/**
			 * The class responsible for generic functions.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-functions.php'; // phpcs:ignore

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-admin.php'; // phpcs:ignore

			/**
			 * This class is responsible for defining all custom rest route endpoints.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-rest-routes.php'; // phpcs:ignore

			/**
			 * This class is responsible for defining user role and capabilities.
			 * Kept outside of is_premium functionality because from here we are restricting user to access the dashboard from the free build.
			 */
			require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-user-and-role.php'; // phpcs:ignore

			/**
			 * This class is responsible for defining all custom rest route endpoints.
			 */
			require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-guest-user-functions.php'; // phpcs:ignore

			/**
			 * This class is responsible for defining all guest related copy links functionalities.
			 */
			require_once COMMENTING_BLOCK_DIR . 'admin/classes/copy-link-feature/class-guest-copy-link-feature.php'; // phpcs:ignore

			/**
			 * This class is responsible for defining all custom rest route endpoints.
			 */
			require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-guest-email-template.php'; // phpcs:ignore

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
	        require_once COMMENTING_BLOCK_DIR . 'public/class-commenting-block-public.php'; // phpcs:ignore

			/**
			 * Include the Email template class and initiate the object.
			*/

			$this->loader = new Commenting_block_Loader();

			/**
			 *  Include all the multiedit related class files.
			 */
			require_once(COMMENTING_BLOCK_DIR . 'admin/classes/realtime/class-realtime.php');
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
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'cf_enqueue_scripts' );
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
			$this->loader->add_action( 'wp_ajax_cf_save_multiedit_settings', $plugin_admin, 'cf_save_multiedit_settings' );
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
			$this->loader->add_action( 'wp_ajax_realtime_collaborators_update_ajax', $plugin_admin, 'realtime_collaborators_update_ajax_function' );

			// Replace content with filter HTML(without HTML tags) which we get from AJAX response. Github issue: #491. @author: Rishi Shah @since: 3.5
			$this->loader->add_action( 'wp_ajax_cf_suggestion_text_filter', $plugin_admin, 'cf_suggestion_text_filter' );

			$this->loader->add_action( 'init', $plugin_admin, 'sg_register_post_meta_field' );
			$this->loader->add_action( 'wp_ajax_sg_update_suggestion_history', $plugin_admin, 'sg_update_suggestion_history' );

			$this->loader->add_action( 'wp_ajax_realtime_collaborators_activity_update_ajax', $plugin_admin, 'realtime_collaborators_activity_update_ajax_function' );
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

			if (class_exists('Copy_Link_Feature')) { // For the copy link feature frontend.
				$copy_link_frontend = new Copy_Link_Feature();
				$this->loader->add_action( 'init', $copy_link_frontend, 'cf_add_request_access_endpoint' );
				$this->loader->add_action( 'wp_enqueue_scripts', $copy_link_frontend, 'cf_enqueue_frontend_scripts' );
				$this->loader->add_action( 'template_include', $copy_link_frontend, 'cf_load_request_access_template' );

				$this->loader->add_action( 'wp_ajax_nopriv_request_access_form_action', $copy_link_frontend, 'cf_request_access_form_action_handler' );
				$this->loader->add_action('wp_ajax_request_access_form_action', $copy_link_frontend, 'cf_request_access_form_action_handler');
			}
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
