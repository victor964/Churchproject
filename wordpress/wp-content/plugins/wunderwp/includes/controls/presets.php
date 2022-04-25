<?php
/**
 * Presets Control.
 *
 * @package WunderWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WunderWP presets control.
 *
 * @since 1.0.0
 */
class WunderWP_Controls_Presets extends \Elementor\Base_Data_Control {

	/**
	 * Get presets control type.
	 *
	 * Retrieve the control type, in this case `wunderwp_presets`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'wunderwp_presets';
	}

	/**
	 * Render presets control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		?>
		<div class="elementor-control-field wunderwp-control-presets">
			<div class="wunderwp-element-presets-wrapper">
				<div class="wunderwp-element-presets">
					<# if (!window.wunderWpPresets || !window.wunderWpPresets[data.element] || window.wunderWpPresets[data.element].length === 0) { #>
						<div class="wunderwp-element-presets-404">
							No presets found
						</div>
					<# } #>

					<div class="wunderwp-element-presets-loading">
						Loading presets
						<span style="display:inline-flex" class="elementor-control-spinner"><span class="fa fa-spinner fa-spin"></span>&nbsp;</span>
					</div>

					<# if (window.wunderWpPresets && window.wunderWpPresets[data.element]) { #>
						<# _.each( window.wunderWpPresets[data.element], function( preset ) { #>
							<div class="wunderwp-element-presets-item" data-preset-id='{{{preset.id}}}'>
								<i class="fa fa-check"></i>
								<# if (preset.thumbnail) { #>
									<img src="{{{preset.thumbnail}}}" alt="{{{preset.title}}}">
								<# } else { #>
									<span class="wunderwp-element-presets-item-title">{{{preset.title}}}</span>
								<# } #>
							</div>
						<# } ); #>
					<# } #>
				</div>
			</div>
		</div>
		<?php
	}
}
