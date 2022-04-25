<?php
/**
 * Custom Presets Control.
 *
 * @package WunderWP
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WunderWP custom presets control.
 *
 * @since 1.2.0
 */
class WunderWP_Controls_Custom_Presets extends \Elementor\Base_Data_Control {

	/**
	 * Get presets control type.
	 *
	 * Retrieve the control type, in this case `wunderwp_custom_presets`.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'wunderwp_custom_presets';
	}

	/**
	 * Render custom presets control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function content_template() {
		?>
		<div class="elementor-control-field wunderwp-control-custom-presets">
			<div class="wunderwp-element-custom-presets-wrapper">
				<?php if ( WunderWP_Utils::is_connected() ) : ?>
					<div class="wunderwp-element-custom-presets-input-wrapper">
						<div class="elementor-control-field">
							<div class="elementor-control-input-wrapper">
								<input type="text" class="wunderwp-element-custom-presets-input"/>
							</div>
						</div>
						<div class="elementor-button-wrapper">
							<button class="elementor-button elementor-button-default wunderwp-element-custom-presets-add-btn" type="button">
								<i class="fa fa-plus-circle"></i> <?php esc_html_e( 'New Preset', 'wunderwp' ); ?>
							</button>
						</div>
					</div>
					<div class="wunderwp-element-custom-presets loading">
						<div class="wunderwp-element-custom-presets-loading">
							Loading presets
							<span style="display:inline-flex" class="elementor-control-spinner"><span class="fa fa-spinner fa-spin"></span>&nbsp;</span>
						</div>
						<# if (window.wunderWpCustomPresets && window.wunderWpCustomPresets[data.element]) { #>
							<# _.each( window.wunderWpCustomPresets[data.element], function( preset, index ) { #>
								<div class="wunderwp-element-custom-presets-item" data-preset-index="{{{index}}}" data-preset-id="{{{preset.id}}}">
									<div class="wunderwp-element-custom-presets-item-title">
										{{preset.title}}
									</div>
									<div class="wunderwp-element-custom-presets-item-delete tooltip-target" data-tooltip="Delete Preset">
										<i class="fa fa-trash-o" ></i>
									</div>
									<div class="wunderwp-element-custom-presets-item-apply tooltip-target" data-tooltip="Apply Preset">
										<i class="fa fa-play-circle-o"></i>
									</div>
								</div>
							<# } ); #>
						<# } #>
					</div>
				<?php else : ?>
					<div class="elementor-panel-alert elementor-panel-alert-warning">
					<?php
						printf(
							__( 'Please connect with WunderWP to save your presets. <br><br> <a target="_blank" href="%s" class="elementor-button elementor-button-default">Connect with WunderWP <i class="fas fa-external-link-alt"></i></a>', 'jupiterx' ),
							WunderWP_Utils::get_authorize_url()
						);
					?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Control default value.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_default_value() {
		return [
			'presets' => [],
		];
	}
}
