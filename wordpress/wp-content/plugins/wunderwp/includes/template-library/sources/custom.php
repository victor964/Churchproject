<?php

use Elementor\TemplateLibrary\Source_Base;
use Elementor\Plugin as ElementorPlugin;

defined( 'ABSPATH' ) || die();

/**
 * WunderWP template library custom source.
 *
 * WunderWP template library custom source handler class is responsible for
 * handling custom templates from wunderwp.com servers.
 *
 * @since 1.4.0
 */
class WunderWP_Template_Library_Source_Custom extends Source_Base {

	public function get_id() {
		return 'custom';
	}

	public function get_title() {
		return __( 'Custom', 'wunderwp' );
	}

	public function register_data() {}

	public function get_items( $args = [] ) {
		$library_data = WunderWP_Api::get_library_data();

		$templates = [];

		if ( ! empty( $library_data['templates']['custom'] ) ) {
			foreach ( $library_data['templates']['custom'] as $template_data ) {
				$templates[] = $this->prepare_template( $template_data );
			}
		}

		return $templates;
	}

	public function get_item( $template_id ) {
		$templates = $this->get_items();

		return $templates[ $template_id ];
	}

	public function save_item( $template_data ) {
		return WunderWP_Api::store_custom_template( $template_data );
	}

	public function update_item( $new_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot update template to a remote source' );
	}

	public function delete_template( $args ) {
		if ( empty( $args['template_id'] ) ) {
			$args['template_id'] = get_post_meta( $args['post_id'], '_wunderwp_template_id', true );
		}

		$response = WunderWP_Api::delete_custom_template( $args['template_id'] );

		if ( ! empty( $response['template_id'] ) ) {
			delete_post_meta( $args['post_id'], '_wunderwp_template_content' );
			delete_post_meta( $args['post_id'], '_wunderwp_template_id' );
			delete_post_meta( $args['post_id'], '_wunderwp_template_assets' );
		}

		return $response;
	}

	public function export_template( $template_id ) {
		$document = ElementorPlugin::$instance->documents->get( $template_id );
		$content = $document ? $document->get_elements_data() : [];
		$content = $this->process_export_import_content( $content, 'on_export' );

		return $content;
	}

	public function get_data( array $args, $context = 'display' ) {
		$data = WunderWP_Api::get_custom_template_content( $args['template_id'] );

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

	private function prepare_template( array $template_data ) {
		$has_page_settings = '0';
		$template_data['template'] = json_decode( $template_data['template'], true );

		if ( 'page' === $template_data['template']['type'] ) {
			$has_page_settings = '1';
		}

		return [
			'template_id' => $template_data['template_id'],
			'post_id' => ! empty( $template_data['template']['post_id'] ) ? $template_data['template']['post_id'] : false,
			'source' => $this->get_id(),
			'type' => $template_data['template']['type'],
			'title' => $template_data['template']['title'],
			'date' => $template_data['created_at'],
			'human_date' => date_i18n( get_option( 'date_format' ), strtotime( $template_data['created_at'] ) ),
			'author' => $template_data['author'],
			'hasPageSettings' => ( '1' === $has_page_settings ),
		];
	}
}
