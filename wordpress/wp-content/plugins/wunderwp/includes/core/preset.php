<?php
/**
 * Register Preset Control.
 *
 * @package WunderWP
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die();

/**
 * WunderWP preset library module.
 *
 * WunderWP preset library module handler class is responsible for registering and fetching
 * WunderWP Presets.
 *
 * @since 1.0.0
 */
class WunderWP_Core_Preset {

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'register_preset_control' ], 10, 3 );
	}

	/**
	 * Register preset control for active elements.
	 *
	 * @param mixed  $element Element instance.
	 * @param string $section_id Section Id.
	 * @param array  $args Element arguments.
	 *
	 * @return void
	 */
	public function register_preset_control( $element, $section_id, $args ) {
		if ( 'widget' !== $element->get_type() ) {
			return;
		}

		$elements = WunderWP_Utils::presets_elements();

		if ( ! in_array( $element->get_name(), $elements, true ) ) {
			return;
		}

		if ( 'section_wunderwp_presets' === $section_id ) {
			return;
		}

		if ( null !== $element->get_current_tab() ) {
			return;
		}

		if ( 'content' !== $args['tab'] ) {
			if ( ! $this->is_preset_control_registered( $element ) ) {
				$this->remove_controls( $element );

				$this->register_controls( $element );
			}

			return;
		}

		$this->remove_controls( $element );

		$this->register_controls( $element );
	}

	/**
	 * Register preset section controls.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @param mixed $element Element instance.
	 * @return void
	 */
	protected function register_controls( $element ) {
		$element->start_controls_section(
			'section_wunderwp_presets',
			[
				'label' => 'Presets',
				'tab' => 'content',
			]
		);

		$element->add_control(
			'wunderwp_presets_sync',
			[
				'type' => 'raw_html',
				'raw' => '<span data-nonce="' . wp_create_nonce( 'wunderwp_sync_library_nonce' ) . '" data-element="' . $element->get_name() . '">' . __( 'Sync', 'wunderwp' ) . ' <i class="eicon-sync" title=" ' . __( 'Sync Presets', 'wunderwp' ) . '"></i></span>',
				'content_classes' => 'wunderwp-presets-sync',
			]
		);

		$element->start_controls_tabs( 'tabs_presets' );

		$element->start_controls_tab(
			'tab_store_presets',
			[
				'label' => __( 'Pre-made', 'wunderwp' ),
			]
		);

		$element->add_control(
			'wunderwp_presets',
			[
				'type' => 'wunderwp_presets',
				'element' => $element->get_name(),
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'tab_custom_presets',
			[
				'label' => __( 'Custom', 'wunderwp' ),
			]
		);

		$element->add_control(
			'wunderwp_custom_presets',
			[
				'type' => 'wunderwp_custom_presets',
				'element' => $element->get_name(),
			]
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->add_control(
			'wunderwp_presets_warning',
			[
				'type' => 'raw_html',
				'raw' => '<b>' . __( 'Note: ', 'wunderwp' ) . '</b>' . __( 'After applying preset if you don\'t see the expected result then it\'s possibily due to the settings that are database dependent.', 'wunderwp' ) . '<br><br>' . __( 'e.g. If you apply preset in ', 'wunderwp' ) . '<b>' . __( 'Elementor > Image Gallery ', 'wunderwp' ) . '</b>' . __( 'element you have to manually remove & add the images or in case of ', 'wunderwp' ) . '<b>' . __( 'Raven > Categories ', 'wunderwp' ) . '</b>' . __( 'element you have to change source & specific categories settings.', 'wunderwp' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Check preset control is registered.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $element Element instance.
	 * @return boolean
	 */
	protected function is_preset_control_registered( $element ) {
		return ! empty( $element->get_controls( 'section_wunderwp_presets' ) );
	}

	/**
	 * Remove control from widget if already added.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @param mixed  $element Element instance.
	 * @param string $control Control name.
	 *
	 * @return void
	 */
	protected function remove_control( $element, $control ) {
		if ( ! empty( $element->get_controls( $control ) ) ) {
			$element->remove_control( $control );
		}
	}

	/**
	 * Remove existing controls.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @param mixed $element Element instance.
	 *
	 * @return void
	 */
	protected function remove_controls( $element ) {
		$this->remove_control( $element, 'wunderwp_presets_sync' );

		$this->remove_control( $element, 'section_wunderwp_presets' );

		$this->remove_control( $element, 'tabs_presets' );

		$this->remove_control( $element, 'tab_store_presets' );
		$this->remove_control( $element, 'wunderwp_presets' );

		$this->remove_control( $element, 'tab_custom_presets' );
		$this->remove_control( $element, 'wunderwp_custom_presets' );

		$this->remove_control( $element, 'wunderwp_presets_warning' );
	}
}

new WunderWP_Core_Preset();
