<?php

use Elementor\TemplateLibrary\Source_Base;
use Elementor\Plugin as ElementorPlugin;

defined( 'ABSPATH' ) || die();

/**
 * WunderWP template library pre-made source.
 *
 * WunderWP template library pre-made source handler class is responsible for
 * handling pre-made templates from wunderwp.com servers.
 *
 * @since 1.3.0
 */
class WunderWP_Template_Library_Source_Pre_Made extends Source_Base {

	/**
	 * Get remote template ID.
	 *
	 * Retrieve the remote template ID.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return string The remote template ID.
	 */
	public function get_id() {
		return 'pre-made';
	}

	/**
	 * Get remote template title.
	 *
	 * Retrieve the remote template title.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return string The remote template title.
	 */
	public function get_title() {
		return __( 'Pre-made', 'wunderwp' );
	}

	/**
	 * Register remote template data.
	 *
	 * Used to register custom template data like a post type, a taxonomy or any
	 * other data.
	 *
	 * @since 1.3.0
	 * @access public
	 */
	public function register_data() {}

	/**
	 * Get remote templates.
	 *
	 * Retrieve remote templates from Elementor.com servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array $args Optional. Nou used in remote source.
	 *
	 * @return array Remote templates.
	 */
	public function get_items( $args = [] ) {
		$library_data = WunderWP_Api::get_library_data();

		$templates = [];

		if ( ! empty( $library_data['templates']['pre-made'] ) ) {
			foreach ( $library_data['templates']['pre-made'] as $template_data ) {
				$templates[] = $this->prepare_template( $template_data );
			}
		}

		return $templates;
	}

	/**
	 * Get remote template.
	 *
	 * Retrieve a single remote template from Elementor.com servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array Remote template.
	 */
	public function get_item( $template_id ) {
		$templates = $this->get_items();

		return $templates[ $template_id ];
	}

	/**
	 * Save remote template.
	 *
	 * Remote template from Elementor.com servers cannot be saved on the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array $template_data Remote template data.
	 *
	 * @return \WP_Error
	 */
	public function save_item( $template_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot save template to a remote source' );
	}

	/**
	 * Update remote template.
	 *
	 * Remote template from Elementor.com servers cannot be updated on the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array $new_data New template data.
	 *
	 * @return \WP_Error
	 */
	public function update_item( $new_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot update template to a remote source' );
	}

	/**
	 * Delete remote template.
	 *
	 * Remote template from Elementor.com servers cannot be deleted from the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Error
	 */
	public function delete_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot delete template from a remote source' );
	}

	/**
	 * Export remote template.
	 *
	 * Remote template from Elementor.com servers cannot be exported from the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Error
	 */
	public function export_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot export template from a remote source' );
	}

	/**
	 * Get remote template data.
	 *
	 * Retrieve the data of a single remote template from Elementor.com servers.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @param array  $args    Custom template arguments.
	 * @param string $context Optional. The context. Default is `display`.
	 *
	 * @return array Remote Template data.
	 */
	public function get_data( array $args, $context = 'display' ) {
		$data = WunderWP_Api::get_template_content( $args['template_id'] );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$data['content'] = $this->replace_elements_ids( $data['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		$post_id = $args['editor_post_id'];
		$document = ElementorPlugin::$instance->documents->get( $post_id );
		if ( $document ) {
			$data['content'] = $document->get_elements_raw_data( $data['content'], true );
		}

		return $data;
	}

	/**
	 * @since 1.3.0
	 * @access private
	 */
	private function prepare_template( array $template_data ) {
		$favorite_templates = $this->get_user_meta( 'favorites' );

		return [
			'template_id' => $template_data['id'],
			'source' => $this->get_id(),
			'type' => '',
			'subtype' => $template_data['subtype'],
			'title' => $template_data['title'],
			'thumbnail' => $template_data['thumbnail'],
			'date' => $template_data['tmpl_created'],
			'author' => $template_data['author'],
			'tags' => json_decode( $template_data['tags'] ),
			'plugins' => $this->prepare_template_plugins( $template_data['plugins'] ),
			'inactive_plugins' => $this->prepare_template_inactive_plugins( $template_data['plugins'] ),
			'isPro' => ( '1' === $template_data['is_pro'] ),
			'popularityIndex' => (int) $template_data['popularity_index'],
			'trendIndex' => (int) $template_data['trend_index'],
			'hasPageSettings' => ( '1' === $template_data['has_page_settings'] ),
			'url' => $template_data['url'],
			'favorite' => ! empty( $favorite_templates[ $template_data['id'] ] ),
		];
	}

	/**
	 * The required plugins which are inactive/not installed.
	 *
	 * @since 1.3.1
	 */
	private function prepare_template_inactive_plugins( $plugins ) {
		$inactive_plugins = array_diff( $plugins, get_option( 'active_plugins' ) );

		return array_keys( $inactive_plugins );
	}

	/**
	 * The plugins used for this template.
	 *
	 * @since 1.4.0
	 */
	private function prepare_template_plugins( $plugins ) {
		$plugins = array_keys( $plugins );

		foreach( $plugins as $key => $plugin ) {
			$plugins[ $key ] = strtolower( str_replace( ' ', '-', $plugin ) );
		}

		return $plugins;
	}
}
