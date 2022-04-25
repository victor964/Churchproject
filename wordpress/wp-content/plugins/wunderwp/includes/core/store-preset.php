<?php
/**
 * Store Presets.
 *
 * @package WunderWP
 * @since 1.2.0
 */

defined( 'ABSPATH' ) || die();

/**
 * WunderWP store preset library module.
 *
 * WunderWP store preset library module handler class is responsible for registering and fetching
 * WunderWP Store Presets.
 *
 * @since 1.2.0
 */
class WunderWP_Core_Store_Preset {

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.2.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wunderwp_element_presets', [ $this, 'get_element_presets' ] );
	}

	/**
	 * Fetch WunderWP element presets.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_element_presets() {
		check_ajax_referer( 'wunderwp_element_presets_nonce' );

		if ( empty( $_POST['element'] ) ) {
			wp_send_json_error( 'element field is missing' );
		}

		$element = sanitize_text_field( wp_unslash( $_POST['element'] ) );
		$url     = 'https://jupiterx.artbees.net/library/wp-json/jupiterx/v1/presets/' . $element;
		$presets = get_transient( 'wunderwp_preset_' . $element );

		if ( ! empty( $presets ) ) {
			return is_array( $presets ) ? wp_send_json_success( $presets ) : wp_send_json_success( [] );
		}

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 40,
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( 'Unable to fetch presets.' );
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			wp_send_json_error( 'Unable to fetch presets.' );
		}

		$presets = json_decode( wp_remote_retrieve_body( $response ), true );

		set_transient( 'wunderwp_preset_' . $element, $presets, DAY_IN_SECONDS );

		wp_send_json_success( $presets );
	}
}

new WunderWP_Core_Store_Preset();
