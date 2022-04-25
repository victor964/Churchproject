<?php

defined( 'ABSPATH' ) || die();

/**
 * WunderWP API.
 *
 * WunderWP API handler class is responsible for communicating with WunderWP
 * remote servers retrieving templates data.
 *
 * @since 1.3.0
 */
class WunderWP_Api {

	/**
	 * WunderWP library option key.
	 */
	const LIBRARY_OPTION_KEY = 'wunderwp_remote_info_library';

	/**
	 * API info URL.
	 */
	public static $api_info_url = 'https://jupiterx.artbees.net/library/wp-json/jupiterx/v1/library?project=wunderwp';

	/**
	 * API get template content URL.
	 */
	private static $api_get_template_content_url = 'https://jupiterx.artbees.net/library/wp-json/jupiterx/v1/templates/%d';

	/**
	 * API get custom template.
	 */
	private static $api_get_custom_template = 'https://wunderwp.com/wp-json/wunderwp-cloud/v1/custom-templates';

	/**
	 * Get info data.
	 *
	 * This function notifies the user of upgrade notices, new templates and contributors.
	 *
	 * @since 1.3.0
	 * @access private
	 * @static
	 *
	 * @param bool $force_update Optional. Whether to force the data retrieval or
	 *                                     not. Default is false.
	 *
	 * @return array|false Info data, or false.
	 */
	private static function get_info_data( $force_update = false ) {
		$cache_key = WunderWP_Utils::get_remote_info_transient_key();

		$info_data = get_transient( $cache_key );

		if ( $force_update || false === $info_data ) {
			$timeout = ( $force_update ) ? 25 : 8;

			$response = wp_remote_get( self::$api_info_url, [
				'timeout' => $timeout,
			] );

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

				return false;
			}

			$info_data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $info_data ) || ! is_array( $info_data ) ) {
				set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

				return false;
			}

			if ( isset( $info_data['library'] ) ) {
				$pre_made_templates = $info_data['library']['templates'];

				unset( $info_data['library']['templates'] );

				$info_data['library']['templates']['pre-made'] = $pre_made_templates;
				$info_data['library']['templates']['custom'] = self::get_custom_templates();

				update_option( self::LIBRARY_OPTION_KEY, $info_data['library'], 'no' );
				unset( $info_data['library'] );
			}

			set_transient( $cache_key, $info_data, 12 * HOUR_IN_SECONDS );
		}

		return $info_data;
	}

	/**
	 * Get templates data.
	 *
	 * Retrieve the templates data from a remote server.
	 *
	 * @since 1.3.0
	 * @access public
	 * @static
	 *
	 * @param bool $force_update Optional. Whether to force the data update or
	 *                                     not. Default is false.
	 *
	 * @return array The templates data.
	 */
	public static function get_library_data( $force_update = false ) {
		self::get_info_data( $force_update );

		$library_data = get_option( self::LIBRARY_OPTION_KEY );

		if ( empty( $library_data ) ) {
			return [];
		}

		return $library_data;
	}

	/**
	 * Get template content.
	 *
	 * Retrieve the templates content received from a remote server.
	 *
	 * @since 1.3.0
	 * @access public
	 * @static
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array The template content.
	 */
	public static function get_template_content( $template_id ) {
		$url = sprintf( self::$api_get_template_content_url, $template_id );

		$body_args = [];

		$response = wp_remote_get( $url, [
			'timeout' => 40,
			'body' => $body_args,
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error( 'response_code_error', sprintf( 'The request returned with a status code of %s.', $response_code ) );
		}

		$template_content = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $template_content['error'] ) ) {
			return new \WP_Error( 'response_error', $template_content['error'] );
		}

		if ( empty( $template_content['data'] ) && empty( $template_content['content'] ) ) {
			return new \WP_Error( 'template_data_error', 'An invalid data was returned.' );
		}

		$template_content['content'] = json_decode( $template_content['content'], true );

		return $template_content;
	}

	/**
	 * Save custom template.
	 *
	 * @since 1.4.0
	 */
	public static function save_custom_template( $template_data ) {
		if ( ! WunderWP_Utils::is_connected() ) {
			wp_send_json_error( sprintf(
				'%1$s <a href="%2$s" target="_blank">%3$s</a>.',
				__( 'You\'re not connected with WunderWP. Please', 'wunderwp' ),
				WunderWP_Utils::get_settings_page_url(),
				__( 'connect with WunderWP', 'wunderwp' )
			) );
		}

		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post(
			self::$api_get_custom_template . '/save',
			[
				'body' => [
					'template' => $template_data,
					'user_email' => $connected_user['user_email'],
					'site_key' => $connected_user['site_key'],
				],
			]
		);

		$error = WunderWP_Utils::error( $response );

		if ( is_wp_error( $error ) ) {
			wp_send_json_error( $error->get_error_messages() );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Save custom template content.
	 *
	 * @since 1.4.0
	 */
	public static function save_custom_template_content( $template ) {
		if ( ! WunderWP_Utils::is_connected() ) {
			wp_send_json_error( sprintf(
				'%1$s <a href="%2$s" target="_blank">%3$s</a>.',
				__( 'You\'re not connected with WunderWP. Please', 'wunderwp' ),
				WunderWP_Utils::get_settings_page_url(),
				__( 'connect with WunderWP', 'wunderwp' )
			) );
		}

		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post(
			self::$api_get_custom_template . '/save-content',
			[
				'body' => [
					'template' => $template,
					'user_email' => $connected_user['user_email'],
					'site_key' => $connected_user['site_key'],
				],
			]
		);

		$error = WunderWP_Utils::error( $response );

		if ( is_wp_error( $error ) ) {
			wp_send_json_error( $error->get_error_messages() );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Save custom template asset.
	 *
	 * @since 1.4.0
	 */
	public static function save_custom_template_asset( $template ) {
		if ( ! WunderWP_Utils::is_connected() ) {
			wp_send_json_error( sprintf(
				'%1$s <a href="%2$s" target="_blank">%3$s</a>.',
				__( 'You\'re not connected with WunderWP. Please', 'wunderwp' ),
				WunderWP_Utils::get_settings_page_url(),
				__( 'connect with WunderWP', 'wunderwp' )
			) );
		}

		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post(
			self::$api_get_custom_template . '/save-asset',
			[
				'timeout' => 60,
				'body' => [
					'template' => $template,
					'user_email' => $connected_user['user_email'],
					'site_key' => $connected_user['site_key'],
				],
			]
		);

		$error = WunderWP_Utils::error( $response );

		if ( is_wp_error( $error ) ) {
			wp_send_json_error( $error->get_error_messages() );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Delete custom template.
	 *
	 * @since 1.4.0
	 */
	public static function delete_custom_template( $template_id ) {
		if ( ! WunderWP_Utils::is_connected() ) {
			wp_send_json_error( sprintf(
				'%1$s <a href="%2$s" target="_blank">%3$s</a>.',
				__( 'You\'re not connected with WunderWP. Please', 'wunderwp' ),
				WunderWP_Utils::get_settings_page_url(),
				__( 'connect with WunderWP', 'wunderwp' )
			) );
		}

		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post(
			self::$api_get_custom_template . '/delete',
			[
				'timeout' => 60,
				'body' => [
					'template_id' => $template_id,
					'user_email' => $connected_user['user_email'],
					'site_key' => $connected_user['site_key'],
				],
			]
		);

		$error = WunderWP_Utils::error( $response );

		if ( is_wp_error( $error ) ) {
			wp_send_json_error( $error->get_error_messages() );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Get custom templates.
	 *
	 * @since 1.4.0
	 */
	private static function get_custom_templates() {
		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post( self::$api_get_custom_template, [
			'timeout' => 60,
			'body' => [
				'user_email' => $connected_user['user_email'],
				'site_key' => $connected_user['site_key'],
			],
		] );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Get custom template content.
	 *
	 * @since 1.4.0
	 */
	public static function get_custom_template_content( $template_id ) {
		$connected_user = WunderWP_Utils::get_connected_user();

		$response = wp_remote_post(
			self::$api_get_custom_template,
			[
				'timeout' => 60,
				'body' => [
					'template_id' => $template_id,
					'user_email' => $connected_user['user_email'],
					'site_key' => $connected_user['site_key'],
				],
			]
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return $response;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$template = json_decode( $response_body[0]['template'], true );

		if ( ! empty( $template['page_settings'] ) ) {
			$template['page_settings'] = json_decode( $template['page_settings'], true );
		}

		return $template;
	}
}
