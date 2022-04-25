<?php
/**
 * Template library module.
 *
 * @package WunderWP
 * @since 1.3.0
 */

use Elementor\Api;
use Elementor\TemplateLibrary\Manager as ElementorTemplateLibrary;
use Elementor\TemplateLibrary\Source_Base;
use Elementor\Core\Common\Modules\Ajax\Module as Ajax;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\TemplateLibrary\Classes\Import_Images;
use Elementor\Plugin as ElementorPlugin;
use Elementor\User;

defined( 'ABSPATH' ) || die();

/**
 * Elementor template library manager.
 *
 * Elementor template library manager handler class is responsible for
 * initializing the template library.
 *
 * @since 1.3.0
 */
class WunderWP_Template_Library {

	/**
	 * Registered template sources.
	 *
	 * Holds a list of all the supported sources with their instances.
	 */
	protected $_registered_sources = [];

	/**
	 * Imported template images.
	 *
	 * Holds an instance of `Import_Images` class.
	 */
	private $_import_images = null;

	/**
	 * Template library manager constructor.
	 *
	 * Initializing the template library manager by registering default template
	 * sources and initializing ajax calls.
	 *
	 * @since 1.3.0
	 * @access public
	 */
	public function __construct() {
		$this->register_default_sources();

		$this->add_actions();
	}

	/**
	 * @since 1.4.0
	 */
	public function add_actions() {
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		add_action( 'elementor/editor/footer', [ $this, 'add_editor_templates' ], 9 );
		// add_filter( 'post_row_actions', [ $this, 'filter_row_actions' ], 11, 2 ); // For now disabled.
		add_filter( 'elementor/template-library/get_template', [ $this, 'filter_elementor_local_templates' ] );
	}

	/**
	 * Get `Import_Images` instance.
	 *
	 * Retrieve the instance of the `Import_Images` class.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return Import_Images Imported images instance.
	 */
	public function get_import_images_instance() {
		if ( null === $this->_import_images ) {
			$this->_import_images = new Import_Images();
		}

		return $this->_import_images;
	}

	/**
	 * Register template source.
	 *
	 * Used to register new template sources displayed in the template library.
	 *
	 * @since 1.4.0
	 */
	public function register_source( $source_class, $args = [] ) {
		if ( ! class_exists( $source_class ) ) {
			return new \WP_Error( 'source_class_name_not_exists' );
		}

		$source_instance = new $source_class( $args );

		if ( ! $source_instance instanceof Source_Base ) {
			return new \WP_Error( 'wrong_instance_source' );
		}

		$source_id = $source_instance->get_id();

		if ( isset( $this->_registered_sources[ $source_id ] ) ) {
			return new \WP_Error( 'source_exists' );
		}

		$this->_registered_sources[ $source_id ] = $source_instance;


		return true;
	}

	/**
	 * Get registered template sources.
	 *
	 * Retrieve registered template sources.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return Source_Base[] Registered template sources.
	 */
	public function get_registered_sources() {
		return $this->_registered_sources;
	}

	/**
	 * Get template source.
	 *
	 * Retrieve single template sources for a given template ID.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param string $id The source ID.
	 *
	 * @return false|Source_Base Template sources if one exist, False otherwise.
	 */
	public function get_source( $id ) {
		$sources = $this->get_registered_sources();

		if ( ! isset( $sources[ $id ] ) ) {
			return false;
		}

		return $sources[ $id ];
	}

	/**
	 * Get templates.
	 *
	 * Retrieve all the templates from all the registered sources.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Templates array.
	 */
	public function get_templates() {
		$templates = [];

		foreach ( $this->get_registered_sources() as $source ) {
			$templates = array_merge( $templates, $source->get_items() );
		}

		return $templates;
	}

	/**
	 * Get library data.
	 *
	 * Retrieve the library data.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array $args Library arguments.
	 *
	 * @return array Library data.
	 */
	public function get_library_data( array $args ) {
		$library_data = WunderWP_Api::get_library_data( ! empty( $args['sync'] ) );

		// Ensure all document are registered.
		ElementorPlugin::$instance->documents->get_document_types();

		return [
			'templates' => $this->get_templates(),
			'config' => $library_data['types_data'],
		];
	}

	/**
	 * Save template.
	 *
	 * Save new or update existing template on the database.
	 *
	 * @since 1.4.0
	 */
	// public function save_template( $args ) {
	// 	$validate_args = $this->ensure_args( [ 'post_id', 'source', 'content', 'type' ], $args );

	// 	if ( is_wp_error( $validate_args ) ) {
	// 		return $validate_args;
	// 	}

	// 	$args['source'] = 'custom';

	// 	$source = $this->get_source( $args['source'] );

	// 	if ( ! $source ) {
	// 		return new \WP_Error( 'template_error', 'Template source not found.' );
	// 	}

	// 	$args['elementor_version'] = ELEMENTOR_VERSION;

	// 	$args['content'] = $args['content'];

	// 	if ( 'page' === $args['type'] ) {
	// 		$page = SettingsManager::get_settings_managers( 'page' )->get_model( $args['post_id'] );

	// 		$args['page_settings'] = wp_json_encode( $page->get_data( 'settings' ) );
	// 	}

	// 	unset( $args['source'], $args['post_id'], $args['wunderwp'] );

	// 	$template_id = $source->save_item( $args );

	// 	return;

	// 	if ( is_wp_error( $template_id ) ) {
	// 		return $template_id;
	// 	}


	// 	return $source->get_item( $template_id );
	// }

	/**
	 * Save template to WunderWP.
	 *
	 * @since 1.4.0
	 */
	public function save_template( $args ) {
		$post_id = $args['post_id'];
		$source = $this->get_source( 'custom' );
		$content = $source->export_template( $post_id );

		$args['title'] = get_the_title( $post_id );
		$args['type'] = get_post_meta( $post_id, '_elementor_template_type', true );
		$args['elementor_version'] = ELEMENTOR_VERSION;
		$args['builder'] = 'elementor';

		// Get page settings.
		if ( 'page' === $args['type'] ) {
			$page = SettingsManager::get_settings_managers( 'page' )->get_model( $post_id );
			$args['page_settings'] = wp_json_encode( $page->get_data( 'settings' ) );
		}

		// Check if content has any assets.
		$args['assets'] = $this->extract_assets( $post_id, $content );

		$result = WunderWP_Api::save_custom_template( $args );

		if ( ! empty ( $result['template_id'] ) ) {
			update_post_meta( $post_id, '_wunderwp_template_content', $content );
			update_post_meta( $post_id, '_wunderwp_template_id', $result['template_id'] );
		}

		return json_decode( $result['template'] );
	}

	/**
	 * Save template content to WunderWP.
	 *
	 * @since 1.4.0
	 */
	public function save_template_content( $args ) {
		$post_id = $args['post_id'];
		$template_id = get_post_meta( $post_id, '_wunderwp_template_id', true );
		$template_content = get_post_meta( $post_id, '_wunderwp_template_content', true );

		$args = [
			'template_id' => $template_id,
			'content' => wp_json_encode( $template_content ),
		];

		$result = WunderWP_Api::save_custom_template_content( $args );

		if ( $result ) {
			delete_post_meta( $post_id, '_wunderwp_template_content' );
		}

		return true;
	}

	/**
	 * Save template assets to WunderWP.
	 *
	 * @since 1.4.0
	 */
	public function save_template_asset( $args ) {
		$post_id = $args['post_id'];
		$template_id = get_post_meta( $post_id, '_wunderwp_template_id', true );
		$template_content = get_post_meta( $post_id, '_wunderwp_template_content', true );
		$template_assets = get_post_meta( $post_id, '_wunderwp_template_assets', true );
		$asset = array_shift( $template_assets );
		$asset_name = pathinfo( $asset, PATHINFO_BASENAME );
		$asset_content = file_get_contents( $asset );

		if ( ! $asset_content ) {
			return new \WP_Error( 'assets_content_error', sprintf( '"%s" is not readable.', $asset_name ) );
		}

		$args = [
			'template_id' => $template_id,
			'asset_name' => $asset_name,
			'asset_content' => $asset_content,
			'asset_content_type' => wp_check_filetype( $asset )['type'],
		];

		$s3_asset = WunderWP_Api::save_custom_template_asset( $args );

		// Replace 'http://test.com/image.jpg' | 'http:\/\/test.com\/image.jpg'
		$template_content = str_replace(
			[ $asset, json_encode( $asset ) ],
			'"wunderwp_asset(' . $s3_asset . ')"',
			json_encode( $template_content )
		);

		update_post_meta( $post_id, '_wunderwp_template_content', json_decode( $template_content, true ) );

		if ( count( $template_assets ) ) {
			update_post_meta( $post_id, '_wunderwp_template_assets', $template_assets );
		} else {
			delete_post_meta( $post_id, '_wunderwp_template_assets' );
		}

		return [
			'uploaded' => $args['asset_name'],
			'remaining' => count( $template_assets )
		];
	}

	/**
	 * Extract assets and save them as post meta.
	 *
	 * @since 1.4.0
	 */
	private function extract_assets( $post_id, array $content ) {
		// Supported file types based on https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types.
		$supported_types = 'apng|bmp|gif|ico|cur|jpg|jpeg|jfif|pjpeg|pjp|png|svg|tif|tiff|webp';

		// Find all the assets.
		preg_match_all( '/https?[^"]*(?:' . $supported_types . ')/', wp_json_encode( $content ), $assets );

		// Remove duplicated assets.
		$assets = array_unique( $assets[0] );

		// Count the assets to make sure there's at least one.
		$assets_count = count( $assets );

		// Save as post meta for later uploading.
		if ( $assets_count ) {
			update_post_meta( $post_id, '_wunderwp_template_assets', $assets );
		}

		return $assets_count;
	}

	/**
	 * Delete template from WunderWP.
	 *
	 * @since 1.4.0
	 */
	public function delete_template( array $args ) {
		$validate_args = $this->ensure_args( [ 'source' ], $args );

		if ( is_wp_error( $validate_args ) ) {
			return $validate_args;
		}

		$source = $this->get_source( $args['source'] );

		if ( ! $source ) {
			return new \WP_Error( 'template_error', 'Template source not found.' );
		}

		return $source->delete_template( $args );
	}

	/**
	 * Get template data.
	 *
	 * Retrieve the template data.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array $args Template arguments.
	 *
	 * @return \WP_Error|bool|array ??
	 */
	public function get_template_data( array $args ) {
		$validate_args = $this->ensure_args( [ 'source', 'template_id' ], $args );

		if ( is_wp_error( $validate_args ) ) {
			return $validate_args;
		}

		if ( isset( $args['edit_mode'] ) ) {
			ElementorPlugin::instance()->editor->set_edit_mode( $args['edit_mode'] );
		}

		$source = $this->get_source( $args['source'] );

		if ( ! $source ) {
			return new \WP_Error( 'template_error', 'Template source not found.' );
		}

		do_action( 'wunderwp/template-library/before_get_source_data', $args, $source );

		$data = $source->get_data( $args );

		do_action( 'wunderwp/template-library/after_get_source_data', $args, $source );

		return $data;
	}

	/**
	 * Register default template sources.
	 *
	 * Register the 'local' and 'remote' template sources that Elementor use by
	 * default.
	 *
	 * @since 1.3.0
	 * @access private
	 */
	private function register_default_sources() {
		$sources = [
			'Pre_Made',
			'Custom',
		];

		wunderwp()->load_directory( 'template-library/sources' );

		foreach ( $sources as $source ) {
			$this->register_source( 'WunderWP_Template_Library_Source_' . $source );
		}
	}

	/**
	 * Handle ajax request.
	 *
	 * Fire authenticated ajax actions for any given ajax request.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @param string $ajax_request Ajax request.
	 *
	 * @param array $data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	private function handle_ajax_request( $ajax_request, array $data ) {
		if ( ! empty( $data['editor_post_id'] ) ) {
			$editor_post_id = absint( $data['editor_post_id'] );

			if ( ! get_post( $editor_post_id ) ) {
				throw new \Exception( __( 'Post not found.', 'wunderwp' ) );
			}

			ElementorPlugin::$instance->db->switch_to_post( $editor_post_id );
		}

		$ajax_request = str_replace( 'wunderwp_', '', $ajax_request );

		$result = call_user_func( [ $this, $ajax_request ], $data );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		return $result;
	}

	/**
	 * Init ajax calls.
	 *
	 * Initialize template library ajax calls for allowed ajax requests.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param Ajax $ajax
	 */
	public function register_ajax_actions( Ajax $ajax ) {
		$actions = ! empty( $_REQUEST['actions'] ) ? $_REQUEST['actions'] : '';
		$requests = json_decode( stripslashes( $_REQUEST['actions'] ), true );
		$library_ajax_requests = [
			'wunderwp_get_library_data',
			'wunderwp_get_template_data',
			'wunderwp_save_template',
			'wunderwp_save_template_content',
			'wunderwp_save_template_asset',
			'wunderwp_delete_template',
		];

		foreach ( $library_ajax_requests as $ajax_request ) {
			$ajax->register_ajax_action( $ajax_request, function( $data ) use ( $ajax_request ) {
				return $this->handle_ajax_request( $ajax_request, $data );
			} );
		}
	}

	/**
	 * Add editor templates.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function add_editor_templates() {
		ElementorPlugin::$instance->common->add_template( wunderwp()->plugin_dir() . '/includes/template-library/templates.php' );
	}

	/**
	 * Check if a template is saved on WunderWP.
	 *
	 * @since 1.4.0
	 */
	public function is_saved_to_wunderwp( $post_id ) {
		return ! ! get_post_meta( $post_id, '_wunderwp_template_id', true );
	}

	/**
	 * Add save/delete actions to Saved templates page. For now disabled.
	 *
	 * @since 1.4.0
	 */
	public function filter_row_actions( $actions, $post ) {
		if ( 'elementor_library' !== get_post_type( $post ) ) {
			return $actions;
		}

		$action_id = 'save_to_wunderwp';
		$action_class = 'wunderwp-save-template';
		$action_label = esc_html__( 'Save to WunderWP', 'wunderwp' );

		if ( $this->is_saved_to_wunderwp( $post->ID ) ) {
			$action_id = 'delete_from_wunderwp';
			$action_class = 'wunderwp-delete-template';
			$action_label = esc_html__( 'Delete from WunderWP', 'wunderwp' );
		}

		$actions[ $action_id ] = sprintf(
			'<a href="#" class="' . $action_class . '" data-post-id="%1$s">%2$s</a>',
			trim( $post->ID ),
			$action_label
		);

		return $actions;
	}

	/**
	 * Filter Elementor local templates to add "saved_to_wunderwp" arg.
	 *
	 * @since 1.4.0
	 */
	public function filter_elementor_local_templates( $data ) {
		$data['saved_to_wunderwp'] = $this->is_saved_to_wunderwp( $data['template_id'] );

		return $data;
	}

	/**
	 * Ensure arguments exist.
	 *
	 * Checks whether the required arguments exist in the specified arguments.
	 *
	 * @since 1.3.0
	 * @access private
	 *
	 * @param array $required_args  Required arguments to check whether they
	 *                              exist.
	 * @param array $specified_args The list of all the specified arguments to
	 *                              check against.
	 *
	 * @return \WP_Error|true True on success, 'WP_Error' otherwise.
	 */
	private function ensure_args( array $required_args, array $specified_args ) {
		$not_specified_args = array_diff( $required_args, array_keys( array_filter( $specified_args ) ) );

		if ( $not_specified_args ) {
			return new \WP_Error( 'arguments_not_specified', sprintf( 'The required argument(s) "%s" not specified.', implode( ', ', $not_specified_args ) ) );
		}

		return true;
	}
}

new WunderWP_Template_Library();
