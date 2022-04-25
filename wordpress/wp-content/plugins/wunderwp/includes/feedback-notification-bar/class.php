<?php
/**
 * This class handles rate notification
 *
 * @since 1.6.0
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manager class.
 *
 * @since 1.6.0
 *
 */
class WunderWP_Feedback_Notification_Bar {
	/**
	 * Current user.
	 *
	 * @var WP_User
	 */
	public $user;

	/**
	 * Meta key.
	 */
	const META_KEY = 'wunderwp_feedback_notification_bar';

	/**
	 * Class Constructor.
	 *
	 * @since 1.6.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'publish_page', [ $this, 'update_elementor_pages' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( 'wp_ajax_wunderwp_dismiss_feedback_notification_bar_notice', [ $this, 'dismiss_notice' ] );

		$this->user = wp_get_current_user();
	}

	/**
	 * Dismiss notice
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'wunderwp_feedback_notification_bar_nonce' );

		update_user_meta( $this->user->ID, self::META_KEY . '_dismissed', 1 );

		wp_send_json_success();
	}

	/**
	 * Register notice.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_notice() {
		if ( ! $this->show_notice() ) {
			return;
		}

		if( ! WunderWP_Utils::is_connected() ) {
			return;
		}

		$nonce = wp_create_nonce( 'wunderwp_feedback_notification_bar_nonce' );
		?>
			<div data-nonce="<?php echo esc_attr( $nonce ); ?>" class="wunderwp-feedback-notification-bar-notice notice notice-warning is-dismissible">
				<div class="wunderwp-feedback-notification-bar-notice-inner">
					<div class="wunderwp-feedback-notification-bar-notice-logo">
						<img src="<?php echo esc_url( wunderwp()->plugin_url() . 'assets/img/wunderwp_plugin_icon.png' ); ?>" alt="<?php esc_html_e( 'WunderWP', 'wunderwp' ); ?>" />
					</div>
					<div class="wunderwp-feedback-notification-bar-notice-content">
						<!-- STEP 1 -->
						<div class="wunderwp-feedback-notification-bar-notice-step" data-step="1">
							<p><?php esc_html_e( 'How do you like WunderWP?', 'wunderwp' ); ?></p>
							<div class="wunderwp-feedback-notification-bar-notice-step-actions">
								<button class="button button-primary" data-step="2"><?php esc_html_e( 'Liked it', 'wunderwp' ); ?></button>
								<button class="button-secondary" data-step="3"><?php esc_html_e( 'Disliked it', 'wunderwp' ); ?></button>
							</div>
						</div>
						<!-- STEP 2 -->
						<div class="wunderwp-feedback-notification-bar-notice-step hidden" data-step="2">
							<p><?php esc_html_e( 'Please help us by rating WunderWP', 'wunderwp' ); ?></p>
							<div class="wunderwp-feedback-notification-bar-notice-step-actions">
								<a href="<?php echo esc_url( 'https://wordpress.org/plugins/wunderwp/#reviews' ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Rate WunderWP', 'wunderwp' ); ?></a>
								<button class="button-secondary"><?php esc_html_e( 'Discard', 'wunderwp' ); ?> </button>
							</div>
						</div>
						<!-- STEP 3 -->
						<div class="wunderwp-feedback-notification-bar-notice-step hidden" data-step="3">
							<p><?php esc_html_e( 'Would you like to share the problem with us?', 'wunderwp' ); ?></p>
							<div class="wunderwp-feedback-notification-bar-notice-step-actions">
								<a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wunderwp/' ); ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Contact support', 'wunderwp' ); ?></a>
								<button class="button-secondary"><?php esc_html_e( 'Discard', 'wunderwp' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Show notice if all conditions are satisfied.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return boolean
	 */
	public function show_notice() {

		if ( ! in_array( 'administrator', (array) $this->user->roles, true ) ) {
			return false;
		}

		if ( strval( 1 ) === get_user_meta( $this->user->ID, self::META_KEY . '_dismissed', true ) ) {
			return false;
		}

		if ( ! $this->has_elementor_pages() ) {
			return false;
		}

		if ( ! $this->show_since_last_page_created( 10 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check user has atleast n number of elementor pages when Jupiter X was active.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return boolean
	 */
	public function has_elementor_pages() {
		$pages = get_user_meta( $this->user->ID, 'wunderwp_elementor_pages_created', true );
		$count = 0;

		if ( is_array( $pages ) ) {
			$count = count( $pages );
		}

		return $count >= 10;
	}

	/**
	 * Keep record of elementor pages created when WunderWP was active.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param integer $post_id Page Id.
	 * @return void
	 */
	public function update_elementor_pages( $post_id ) {


		$built_with_elementor = ! ! get_post_meta( $post_id, '_elementor_edit_mode', true );

		if ( ! $built_with_elementor ) {
			return;
		}

		$pages = get_user_meta( $this->user->ID, 'wunderwp_elementor_pages_created', true );

		if ( empty( $pages ) || ! is_array( $pages ) ) {
			$pages = [];
		}

		if ( ! in_array( $post_id, $pages, true ) ) {
			update_user_meta( $this->user->ID, 'wunderwp_elementor_last_page_created_at', time() );
		}

		$pages[] = $post_id;
		$pages   = array_unique( $pages );

		update_user_meta( $this->user->ID, 'wunderwp_elementor_pages_created', $pages );
	}

	/**
	 * Show after $days since last elementor page is created while Jupiter X was active.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param integer $days Number of days.
	 *
	 * @return boolean
	 */
	public function show_since_last_page_created( $days ) {
		$current              = time();
		$last_page_created_at = get_user_meta( $this->user->ID, 'wunderwp_elementor_last_page_created_at', true );

		if ( empty( $last_page_created_at ) ) {
			return false;
		}

		$diff = $current - $last_page_created_at;

		$day_in_seconds = apply_filters( 'wunderwp_feedback_notification_bar_dis', DAY_IN_SECONDS );

		return abs( round( $diff / $day_in_seconds ) ) >= $days;
	}
}

new WunderWP_Feedback_Notification_Bar();
