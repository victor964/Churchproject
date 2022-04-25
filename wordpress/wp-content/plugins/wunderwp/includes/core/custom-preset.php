<?php
/**
 * Register Preset Control.
 *
 * @package WunderWP
 * @since 1.2.0
 */

defined( 'ABSPATH' ) || die();

/**
 * WunderWP custom preset library module.
 *
 * WunderWP custom preset library module handler class is responsible for registering and fetching
 * WunderWP Custom Presets.
 *
 * @since 1.2.0
 */
class WunderWP_Core_Custom_Preset {

	/**
	 * Base URL.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	protected $base_url = 'https://wunderwp.com/wp-json/wunderwp-cloud/v1';

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.2.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wunderwp_element_custom_presets', [ $this, 'get_element_presets' ] );
		add_action( 'wp_ajax_wunderwp_store_custom_preset', [ $this, 'store_preset' ] );
		add_action( 'wp_ajax_wunderwp_delete_custom_preset', [ $this, 'delete_preset' ] );
	}

	/**
	 * Save custom preset to WunderWP_Cloud.
	 *
	 * @access public
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function store_preset() {
		check_ajax_referer( 'wunderwp_store_custom_preset_nonce' );

		if ( empty( $_POST['data'] ) ) {
			wp_send_json_error();
		}

		$preset = wp_unslash( $_POST['data'] );
		$url    = $this->base_url . '/custom-presets';

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 40,
				'body' => [
					'preset'     => $preset,
					'user_email' => $this->get_user_email(),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'Unable to store preset', 'wunderwp' ) );
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			wp_send_json_error( __( 'Unable to store preset', 'wunderwp' ) );
		}

		$preset = json_decode( wp_remote_retrieve_body( $response ), true );

		delete_transient( 'wunderwp_custom_preset_' . $preset['element_type'] );

		wp_send_json_success( $preset );
	}

	/**
	 * Get element custom presets.
	 *
	 * @access public
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_element_presets() {
		check_ajax_referer( 'wunderwp_element_custom_presets_nonce' );

		if ( ! WunderWP_Utils::is_connected() ) {
			wp_send_json_error( __( 'Site is not connected.', 'wunderwp' ) );
		}

		if ( empty( $_POST['element'] ) ) {
			wp_send_json_error( __( 'element field is missing', 'wunderwp' ) );
		}

		$element = sanitize_text_field( wp_unslash( $_POST['element'] ) );
		$url     = $this->base_url . '/custom-presets/' . $element;
		$presets = get_transient( 'wunderwp_custom_preset_' . $element );

		if ( false !== $presets ) {
			return is_array( $presets ) ? wp_send_json_success( $presets ) : wp_send_json_success( [] );
		}

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 40,
				'body' => [
					'user_email' => $this->get_user_email(),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'Unable to fetch presets.', 'wunderwp' ) );
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			wp_send_json_error( __( 'Unable to fetch presets.', 'wunderwp' ) );
		}

		$presets = json_decode( wp_remote_retrieve_body( $response ), true );

		$elements = [];

		set_transient( 'wunderwp_custom_preset_' . $element, $presets, DAY_IN_SECONDS );

		wp_send_json_success( $presets );
	}

	/**
	 * Delete preset.
	 *
	 * @access public
	 * @since 1.2.0
	 *
	 * @return mixed
	 */
	public function delete_preset() {
		check_ajax_referer( 'wunderwp_delete_custom_preset_nonce' );

		if ( empty( $_POST['id'] ) ) {
			wp_send_json_error( __( 'id field is missing', 'wunderwp' ) );
		}

		if ( empty( $_POST['element_type'] ) ) {
			wp_send_json_error( __( 'element_type field is missing', 'wunderwp' ) );
		}

		$id           = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		$element_type = sanitize_text_field( wp_unslash( $_POST['element_type'] ) );
		$url          = $this->base_url . '/custom-presets/' . $id;

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 40,
				'body' => [
					'user_email' => $this->get_user_email(),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'Unable to delete preset.', 'wunderwp' ) );
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			wp_send_json_error( __( 'Unable to delete preset.', 'wunderwp' ) );
		}

		delete_transient( 'wunderwp_custom_preset_' . $element_type );

		wp_send_json_success();
	}

	/**
	 * Get user email.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @return mixed
	 */
	private function get_user_email() {
		$connect = get_option( 'wunderwp_connect' );

		if ( ! empty( $connect ) ) {
			return $connect['user_email'];
		}

		return false;
	}
}

new WunderWP_Core_Custom_Preset();
