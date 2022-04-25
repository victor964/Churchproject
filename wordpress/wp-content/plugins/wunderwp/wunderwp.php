<?php
/**
 * Plugin Name: WunderWP
 * Plugin URI: https://wunderwp.com
 * Description: Apply preset styling to Elementor widgets and beautify your content in seconds!
 * Version: 1.6.0
 * Author: Artbees
 * Author URI: https://artbees.net
 * Text Domain: wunderwp
 * License: GPL2
 *
 * @package WunderWP
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'WunderWP' ) ) {

	/**
	 * WunderWP class.
	 *
	 * @since 1.0.0
	 */
	class WunderWP {

		/**
		 * WunderWP instance.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var WunderWP
		 */
		private static $instance;

		/**
		 * The plugin version number.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $version;

		/**
		 * The plugin basename.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_basename;

		/**
		 * The plugin name.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_name;

		/**
		 * The plugin directory.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_dir;

		/**
		 * The plugin URL.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_url;

		/**
		 * The plugin assets URL.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @var string
		 */
		public static $plugin_assets_url;

		/**
		 * Returns WunderWP instance.
		 *
		 * @since 1.0.0
		 *
		 * @return WunderWP
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'plugins_loaded', [ $this, 'init' ] );
		}

		/**
		 * Defines constants used by the plugin.
		 *
		 * @since 1.0.0
		 */
		protected function define_constants() {
			$plugin_data = get_file_data( __FILE__, [ 'Plugin Name', 'Version' ], 'wunderwp' );

			self::$plugin_basename   = plugin_basename( __FILE__ );
			self::$plugin_name       = array_shift( $plugin_data );
			self::$version           = array_shift( $plugin_data );
			self::$plugin_dir        = trailingslashit( plugin_dir_path( __FILE__ ) );
			self::$plugin_url        = trailingslashit( plugin_dir_url( __FILE__ ) );
			self::$plugin_assets_url = trailingslashit( self::$plugin_url . 'assets' );
		}

		/**
		 * Adds required action hooks.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function add_actions() {
			add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ], 15 );
			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_enqueue_styles' ], 0 );
			add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'editor_enqueue_scripts' ], 0 );
			add_action( 'elementor/preview/enqueue_styles', [ $this, 'preview_enqueue_styles' ] );

			add_action( 'wp_ajax_wunderwp_sync_libraries', [ $this, 'sync_libraries' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts' ] );

			if ( is_admin() ) {
				add_action( 'elementor/admin/after_create_settings/' . \Elementor\Settings::PAGE_ID, [ $this, 'register_admin_fields' ], 20 );
			}
		}

		/**
		 * Initializes the plugin.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			if ( ! class_exists( '\Elementor\Plugin' ) ) {
				return;
			}

			$this->define_constants();
			$this->add_actions();

			// Load files.
			$this->load_files(
				[
					'utils',
					'api',
					'/core/preset',
					'/core/store-preset',
					'/core/custom-preset',
					'/connect/class',
					'/feedback-notification-bar/class',
					'/options/class',
					'/template-library/class',
				]
			);

			/**
			 * Fires after all files have been loaded.
			 *
			 * @since 1.0.0
			 *
			 * @param WunderWP
			 */
			do_action( 'wunderwp_init', $this );
		}

		/**
		 * Returns the version number of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function version() {
			return self::$version;
		}

		/**
		 * Returns the plugin basename.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_basename() {
			return self::$plugin_basename;
		}

		/**
		 * Returns the plugin name.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_name() {
			return self::$plugin_name;
		}

		/**
		 * Returns the plugin directory.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_dir() {
			return self::$plugin_dir;
		}

		/**
		 * Returns the plugin URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_url() {
			return self::$plugin_url;
		}

		/**
		 * Loads all PHP files in a given directory.
		 *
		 * @since 1.0.0
		 *
		 * @param string $directory_name The directory name to load the files.
		 */
		public function load_directory( $directory_name ) {
			$path       = trailingslashit( $this->plugin_dir() . 'includes/' . $directory_name );
			$file_names = glob( $path . '*.php' );
			foreach ( $file_names as $filename ) {
				if ( file_exists( $filename ) ) {
					require_once $filename;
				}
			}
		}

		/**
		 * Loads specified PHP files from the plugin includes directory.
		 *
		 * @since 1.0.0
		 *
		 * @param array $file_names The names of the files to be loaded in the includes directory.
		 */
		public function load_files( $file_names = [] ) {
			foreach ( $file_names as $file_name ) {
				$path = $this->plugin_dir() . 'includes/' . $file_name . '.php';

				if ( file_exists( $path ) ) {
					require_once $path;
				}
			}
		}

		/**
		 * Register controls with Elementor by wunderwp prefix.
		 * wunderwp-loop-animation, wunderwp-parallax-scroll, ...
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param object $controls_manager The controls manager.
		 */
		public function register_controls( $controls_manager ) {
			/**
			 * List of all controls and group controls.
			 * Credit: goo.gl/hkvhZJ - preg_grep solution
			 */
			$controls       = preg_grep( '/^((?!index.php).)*$/', glob( self::$plugin_dir . 'includes/controls/*.php' ) );
			$controls_path  = self::$plugin_dir . 'includes/controls/';

			// Register controls.
			foreach ( $controls as $control ) {
				require_once $control;

				// Prepare control name.
				$control_name = basename( $control, '.php' );
				$control_name = str_replace( '-', '_', $control_name );

				// Prepare class name.
				$class_name = str_replace( '_', ' ', $control_name );
				$class_name = ucwords( $class_name );
				$class_name = str_replace( ' ', '_', $class_name );
				$class_name = 'WunderWP_Controls_' . $class_name;

				// Register now.
				$controls_manager->register_control( 'wunderwp_' . $control_name, new $class_name() );
			}
		}

		/**
		 * Enqueue styles.
		 *
		 * Enqueue all the editor styles.
		 *
		 * Fires after Elementor editor styles are enqueued.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function editor_enqueue_styles() {
			$suffix = \Elementor\Utils::is_script_debug() ? '' : '.min';

			wp_enqueue_style(
				'wunderwp-editor',
				self::$plugin_assets_url . 'css/editor' . $suffix . '.css',
				[],
				self::$version
			);
		}

		/**
		 * Enqueue preview styles.
		 *
		 * Enqueue all the preview styles.
		 *
		 * @since 1.3.0
		 * @access public
		 */
		public function preview_enqueue_styles() {
			$suffix = \Elementor\Utils::is_script_debug() ? '' : '.min';

			wp_enqueue_style(
				'wunderwp-preview',
				self::$plugin_assets_url . 'css/preview' . $suffix . '.css',
				[],
				self::$version
			);
		}

		/**
		 * Enqueue scripts.
		 *
		 * Enqueue all the editor scripts.
		 *
		 * Fires after Elementor editor scripts are enqueued.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function editor_enqueue_scripts() {
			$suffix = \Elementor\Utils::is_script_debug() ? '' : '.min';

			wp_enqueue_script(
				'wunderwp-editor',
				self::$plugin_assets_url . 'js/editor' . $suffix . '.js',
				[ 'jquery' ],
				self::$version,
				true
			);

			wp_localize_script(
				'wunderwp-editor',
				'wunderwp_editor',
				[
					'is_connected'                   => WunderWP_Utils::is_connected(),
					'element_presets_nonce'          => wp_create_nonce( 'wunderwp_element_presets_nonce' ),
					'element_custom_presets_nonce'   => wp_create_nonce( 'wunderwp_element_custom_presets_nonce' ),
					'store_custom_preset_nonce'      => wp_create_nonce( 'wunderwp_store_custom_preset_nonce' ),
					'delete_custom_preset_nonce'     => wp_create_nonce( 'wunderwp_delete_custom_preset_nonce' ),

					'delete'                         => __( 'Delete', 'wunderwp' ),
					'cancel'                         => __( 'Cancel', 'wunderwp' ),
					'deleting'                       => __( 'Deleting', 'wunderwp' ),

					'dialog_delete_preset_msg'       => __( 'Do you want to delete this preset?', 'wunderwp' ),
					'dialog_delete_preset_error_msg' => __( 'Unable to delete preset, please try again.',  'wunderwp' ),
					'library_unconnected_title'      => __( 'Haven\'t Connected Yet?', 'wunderwp' ),
					'library_unconnected_message'    => __( 'Please connect to WunderWP to save your templates.', 'wunderwp' ),
					'library_unconnected_button'     => sprintf( '<br><a class="wunderwp-connect-button" href="%s" target="_blank">%s</a>', admin_url( 'options-general.php?page=wunderwp' ), __( 'Connect with WunderWP', 'wunderwp' ) ),
				]
			);
		}

		/**
		 * Register WunderWP admin scripts.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return void
		 */
		public function register_admin_scripts() {
			$suffix = \Elementor\Utils::is_script_debug() ? '' : '.min';

			wp_enqueue_script(
				'wunderwp-admin',
				self::$plugin_assets_url . 'js/admin' . $suffix . '.js',
				[ 'jquery' ],
				self::$version,
				true
			);

			wp_enqueue_style(
				'wunderwp-admin-style',
				self::$plugin_assets_url . 'css/admin' . $suffix . '.css'
			);

			// Disabled for now.
			// wp_enqueue_script(
			// 	'wunderwp-save-template',
			// 	self::$plugin_assets_url . 'js/save-template' . $suffix . '.js',
			// 	[ 'jquery', 'elementor-common' ],
			// 	self::$version,
			// 	true
			// );

			wp_localize_script( 'wunderwp-admin', 'wunderwp_sync_library_nonce', wp_create_nonce( 'wunderwp_sync_library_nonce' ) );
		}

		/**
		 * Add WunderWP tab in Elementor Settings page.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param object $settings Settings.
		 */
		public function register_admin_fields( $settings ) {
			$settings->add_tab(
				'wunderwp', [
					'label' => __( 'WunderWP', 'wunderwp' ),
				]
			);

			$settings->add_section(
				'wunderwp',
				'wunderwp_sync_data',
				[
					'callback' => function() {
						echo '<hr><h2>' . esc_html__( 'Sync', 'wunderwp' ) . '</h2>';
					},
					'fields' => [
						'reset_presets_data' => [
							'label' => __( 'Presets Library', 'wunderwp' ),
							'field_args' => [
								'type' => 'raw_html',
								'html' => sprintf( '<button data-nonce="%s" class="button elementor-button-spinner" id="wunderwp-preset-library-sync-button">%s</button>', wp_create_nonce( 'wunderwp_reset_preset_library' ), __( 'Sync All Presets', 'wunderwp' ) ),
								'desc' => __( 'WunderWP Preset Library gets updated automatically only once a day. You can also manually update it by clicking the Sync All Presets button.', 'wunderwp' ),
							],
						],
					],
				]
			);
		}

		/**
		 * Sync libraries.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return void
		 */
		public function sync_libraries() {
			check_ajax_referer( 'wunderwp_sync_library_nonce' );

			// Load files.
			$this->load_files(
				[
					'utils',
				]
			);

			if ( empty( $_POST['library'] ) ) {
				wp_send_json_error( __( 'library field is missing', 'wunderwp' ) );
			}

			$library = sanitize_text_field( wp_unslash( $_POST['library'] ) );

			if ( 'presets' === $library ) {
				$elements = WunderWP_Utils::presets_elements();

				if ( false === $elements ) {
					wp_send_json_success();
				}

				foreach ( $elements as $element ) {
					delete_transient( 'wunderwp_preset_' . $element );
					delete_transient( 'wunderwp_custom_preset_' . $element );
				}

				delete_transient( 'wunderwp_presets_elements' );

				wp_send_json_success();
			}

			wp_send_json_error( __( 'Invalid library value received.', 'wunderwp' ) );
		}
	}
}

/**
 * Returns the WunderWP application instance.
 *
 * @since 1.0.0
 *
 * @return WunderWP
 */
function wunderwp() {
	return WunderWP::get_instance();
}

/**
 * Initializes the WunderWP application.
 *
 * @since 1.0.0
 */
wunderwp();
