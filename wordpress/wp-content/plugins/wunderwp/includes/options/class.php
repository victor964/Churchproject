<?php
/**
 * Options module.
 *
 * @package WunderWP
 */

/**
 * Options class.
 */
class WunderWP_Options {

	/**
	 * Construct class.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			'WunderWP',
			'WunderWP',
			'manage_options',
			'wunderwp',
			[ $this, 'options_page' ]
		);
	}

	/**
	 * Options page.
	 */
	public function options_page() {
		do_action( 'wunderwp_options_page' );
	}

}

new WunderWP_Options();
