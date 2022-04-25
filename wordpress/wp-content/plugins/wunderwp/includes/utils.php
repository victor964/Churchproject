<?php
/**
 * Adds utils.
 *
 * @package WunderWP
 * @since 1.2.0
 */

/**
 * WunderWP utils class.
 *
 * WunderWP utils handler class is responsible for different utility methods
 * used by WunderWP.
 *
 * @since 1.2.0
 */
class WunderWP_Utils {

	/**
	 * Get custom presets table name.
	 *
	 * @since 1.2.0
	 *
	 * @access public
	 * @static
	 *
	 * @return string
	 */
	public static function presets_elements() {
		$transient_key = 'wunderwp_presets_elements';

		$preset_elements = get_transient( $transient_key );

		if ( false !== $preset_elements ) {
			return $preset_elements;
		}

		$preset_elements = [];

		$url = 'https://jupiterx.artbees.net/library/wp-json/jupiterx/v1/presets-elements';

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		if ( is_wp_error( $response ) ) {
			set_transient( $transient_key, $preset_elements, DAY_IN_SECONDS );

			return $preset_elements;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			set_transient( $transient_key, $preset_elements, DAY_IN_SECONDS );

			return $preset_elements;
		}

		$body = wp_remote_retrieve_body( $response );

		$preset_elements = json_decode( $body, true );

		set_transient( $transient_key, $preset_elements, DAY_IN_SECONDS );

		return $preset_elements;
	}

	/**
	 * Check if connected.
	 *
	 * @since 1.2.0
	 *
	 * @access public
	 * @static
	 *
	 * @return boolean
	 */
	public static function is_connected() {
		if ( empty( get_option( 'wunderwp_connect' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get authorize URL.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 * @static
	 *
	 * @return string
	 */
	public static function get_authorize_url() {
		$args = [
			'page'   => 'wunderwp',
			'action' => 'authorize',
		];

		if ( self::is_connected() ) {
			return '';
		}

		return wp_nonce_url( add_query_arg( $args, admin_url( 'options-general.php' ) ), -1, 'nonce' );
	}

	/**
	 * Get connected user.
	 *
	 * @since 1.4.0
	 */
	public static function get_connected_user() {
		return get_option( 'wunderwp_connect' );
	}

	/**
	 * Get settings page.
	 *
	 * @since 1.4.0
	 */
	public static function get_settings_page_url() {
		return admin_url( 'options-general.php?page=wunderwp' );
	}

	/**
	 * Get remote info data transient.
	 *
	 * @since 1.4.0
	 */
	public static function get_remote_info_transient_key() {
		return 'wunderwp_remote_info_api_data_' . wunderwp()->version();
	}

	/**
	 * Generate proper WP_Error. All response codes except 200 are considered as error.
	 *
	 * @since 1.4.0
	 */
	public static function error( $response ) {
		if ( $response instanceof WP_Error ) {
			return $response;
		}

		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$error = new WP_Error();

		$error->add( $body['code'], $body['message'], $body['data'] );

		if ( ! empty( $body['additional_errors'] ) ) {
			foreach ( $body['additional_errors'] as $additional_errors ) {
				$error->add(
					$additional_errors['code'],
					$additional_errors['message'],
					$additional_errors['data']
				);
			}
		}

		return $error;
	}
}
