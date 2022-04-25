<?php
/**
 * Connect module.
 *
 * @package WunderWP
 * @since 1.2.0
 */

/**
 * Connect class.
 *
 * @since 1.2.0
 */
class WunderWP_Connect {

	/**
	 * WunderWP cloud url.
	 *
	 * @since 1.2.0
	 */
	private $cloud_url;

	/**
	 * Admin url.
	 *
	 * @since 1.2.0
	 */
	private $admin_url;

	/**
	 * Site url.
	 *
	 * @since 1.2.0
	 */
	private $site_url;

	/**
	 * Construct class.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		$this->cloud_url = 'https://wunderwp.com';
		$this->admin_url = admin_url( 'options-general.php?page=wunderwp' );
		$this->site_url  = home_url();

		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'wunderwp_options_page', [ $this, 'get_html' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice_connect' ] );

		add_action( 'wp_ajax_wunderwp_dismiss_connect_notice', [ $this, 'dismiss_connect_notice' ] );
	}

	/**
	 * Init.
	 *
	 * @since 1.2.0
	 */
	public function init() {
		$this->check_cloud_connection();

		$page   = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$nonce  = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

		if ( empty( $page ) || empty( $action ) || 'wunderwp' !== $page ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce ) ) {
			return;
		}

		call_user_func( [ $this, $action ] );
	}

	/**
	 * Get option.
	 *
	 * @since 1.2.0
	 */
	private function get( $name ) {
		$option = get_option( 'wunderwp_connect' );

		if ( empty( $option[ $name ] ) ) {
			return false;
		}

		return $option[ $name ];
	}

	/**
	 * Authorize.
	 *
	 * @since 1.2.0
	 */
	private function authorize() {
		$url = add_query_arg( [
			'site_url'     => urlencode( $this->site_url ),
			'redirect_url' => urlencode( $this->admin_url ),
			'nonce'        => wp_create_nonce(),
		], $this->cloud_url . '/authorize' );

		wp_redirect( $url );
		exit;
	}

	/**
	 * Connect after successful authorization.
	 *
	 * @since 1.2.0
	 */
	private function connect() {
		$user_email = filter_input( INPUT_GET, 'user_email', FILTER_SANITIZE_STRING );
		$site_key   = filter_input( INPUT_GET, 'site_key', FILTER_SANITIZE_STRING );

		update_option( 'wunderwp_connect', [
			'status'     => 'active',
			'user_email' => $user_email,
			'site_key'   => $site_key,
		] );

		delete_transient( WunderWP_Utils::get_remote_info_transient_key() );

		wp_redirect( $this->admin_url );
		exit;
	}

	/**
	 * Disconnect a authorized website.
	 *
	 * @since 1.2.0
	 */
	private function disconnect() {
		$result = wp_remote_post( $this->cloud_url . '/wp-json/authorize/v1/disconnect', [
			'body' => [
				'site_url' => urlencode( $this->site_url ),
				'site_key' => $this->get( 'site_key' ),
			]
		] );

		if ( is_wp_error( $result ) ) {
			return;
		}

		$this->delete_transients();

		wp_redirect( $this->admin_url );
		exit;
	}

	/**
	 * Check if connected.
	 *
	 * @since 1.2.0
	 */
	public function is_connected() {
		$option = get_option( 'wunderwp_connect' );

		if ( isset( $option['status'] ) && 'active' === $option['status'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Check remote connection is intact or revoked.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function check_cloud_connection() {
		if ( ! $this->is_connected() ) {
			return;
		}

		$transient_key = '_wunderwp_cloud_connected';
		$transient     = get_transient( $transient_key );

		if ( false !== $transient ) {
			return;
		}

		set_transient( $transient_key, time(), 6 * HOUR_IN_SECONDS );

		$url = $this->cloud_url . '/wp-json/authorize/v1/connected';

		$response = wp_remote_post(
			$url,
			[
				'body' => [
					'site_url' => $this->site_url,
					'site_key' => $this->get( 'site_key' ),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 400 !== $response_code ) {
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['code'] ) && 'disconnected' === $body['code'] ) {
			$this->delete_transients();
			delete_transient( $transient_key ) ;
		}
	}

	/**
	 * Get action url.
	 *
	 * @since 1.2.0
	 */
	private function get_action_url() {
		$args = [
			'page'   => 'wunderwp',
			'action' => 'disconnect',
		];

		if ( ! $this->is_connected() ) {
			$args['action'] = 'authorize';
		}

		return wp_nonce_url( add_query_arg( $args, admin_url( 'options-general.php' ) ), -1, 'nonce' );
	}

	/**
	 * Get status.
	 *
	 * @since 1.2.0
	 */
	private function get_status() {
		return 'Active';
	}

	/**
	 * Get html.
	 *
	 * @since 1.2.0
	 */
	public function get_html() {
		$settings = [
			'title' => [
				'text' => sprintf( esc_html__( 'Get started with %s', 'wunderwp' ), 'WunderWP' ),
			],
			'description' => [
				'text' => sprintf( esc_html__( 'Once you connect to %s cloud, you will be able to save and load custom preset styles throughout your websites. We will securely connect your website.', 'wunderwp' ), 'WunderWP' ),
			],
			'button' => [
				'href'  => $this->get_action_url(),
				'class' => 'button button-primary',
				'text'  => sprintf( esc_html__( 'Connect with %s', 'wunderwp' ), 'WunderWP' ),
			],
		];

		if ( $this->is_connected() ) {
			$settings['title']['text']       = sprintf( esc_html__( 'Status: %s', 'wunderwp' ), $this->get_status() );
			$settings['description']['text'] = sprintf( __( 'You are connected as <strong>%s</strong>. If you need to switch the connected account, try to disconnect then reconnect.', 'wunderwp' ), $this->get( 'user_email' ) );
			$settings['button']['text']      = esc_html__( 'Disconnect', 'wunderwp' );
			$settings['button']['class']     = 'button';
		}
		?>
			<div class="wrap">
				<h2></h2>
				<div class="card" style="max-width: 800px; margin-top: 0;">
					<h2 class="title">
						<?php echo $settings['title']['text']; ?>
						<?php if ( $this->is_connected() ) : ?>
							<a class="button subtitle preview" target="_blank" href="<?php echo $this->cloud_url . '/my-account'; ?>" style="margin-top: -6px;">My Account</a>
						<?php endif; ?>
					</h2>
					<p><?php echo $settings['description']['text']; ?></p>
					<p>
						<a class="<?php echo $settings['button']['class']; ?>" href="<?php echo $settings['button']['href']; ?>"><?php echo $settings['button']['text']; ?></a>
					</p>
				</div>
			</div>
		<?php
	}

	/**
	 * Show connect notice if not connected.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_notice_connect() {
		if ( $this->is_connected() ) {
			return;
		}

		if ( false !== get_option( '_wunderwp_dismiss_connect_notice' ) ) {
			return;
		}

		if ( ! class_exists( 'WunderWP_Utils' ) ) {
			return;
		}

		$message = sprintf(
			'<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">'
			. __( 'In order to save your Elementor styles and templates into WunderWP, you need to connect your site to WunderWP cloud. It\'s easy and secure!', 'wunderwp' )
			. '</span>'
		);

		$message .= sprintf(
			'<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">' .
			'<a class="button-primary" href="%1$s">%2$s</a></span>',
			WunderWP_Utils::get_authorize_url(),
			__( 'Connect with WunderWP', 'wunderwp' )
		);

		$nonce = wp_create_nonce( 'wunderwp_dismiss_connect_notice' );

		printf( '<div data-nonce="%s" class="wunderwp-connect-notice notice notice-warning is-dismissible"><p>%s</p></div>', $nonce, $message );
	}

	/**
	 * Set option if notice is dismissed.
	 *
	 * @since 1.5.1
	 * @access public
	 *
	 * @return void
	 */
	public function dismiss_connect_notice() {
		check_ajax_referer( 'wunderwp_dismiss_connect_notice' );

		update_option( '_wunderwp_dismiss_connect_notice', 1 );
	}

	/**
	 * Delete transients.
	 *
	 * @since 1.4.0
	 */
	private function delete_transients() {
		// Delete elements transients.
		$elements = WunderWP_Utils::presets_elements();

		if ( ! empty( $elements ) ) {
			foreach ( $elements as $element ) {
				delete_transient( 'wunderwp_custom_preset_' . $element );
			}
		}

		// Delete remote info transient.
		delete_transient( WunderWP_Utils::get_remote_info_transient_key() );

		// Delete connection data.
		delete_option( 'wunderwp_connect' );
	}
}

new WunderWP_Connect();

