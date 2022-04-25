<?php

use Elementor\Frontend;
use Elementor\Icons_Manager;

extract( shortcode_atts( array(
	'accordion_items'    => array(),
	'expand_icon' => '',
	'expand_icon_active' => '',
	'accordion_type' => 'accordion',
	'accordion_collapsible' => '',
	'accordion_interactivity' => 'click',
	'equal_height' => '',

), $settings ) );

$widget_class = 'stratum-advanced-accordion';

$class = stratum_css_class([
	$widget_class,
]);

$accordion_options = [
	'accordion_type' => $accordion_type,
	'accordion_collapsible' => ($accordion_collapsible == 'yes'),
	'accordion_interactivity' => $accordion_interactivity,
	'equal_height' => (($equal_height == 'yes' && $accordion_type == 'accordion') ? true : false),
];

$out = "";

$frontend = new Frontend;

ob_start();
	Icons_Manager::render_icon( $expand_icon, [ 'aria-hidden' => 'true' ] );
$expand_icon_html = ob_get_clean();

ob_start();
	Icons_Manager::render_icon( $expand_icon_active, [ 'aria-hidden' => 'true' ] );
$expand_icon_active_html = ob_get_clean();

$once_active = false;
?>
<div class="<?php echo esc_attr( $class ); ?>" data-accordion-options="<?php echo esc_attr( json_encode($accordion_options) ); ?>">
	<div class="<?php echo esc_attr( $widget_class . '__wrapper' ); ?>">
		<?php
		foreach ( $accordion_items as $index => $item ) {
			$current_item = 'elementor-repeater-item-'.$item['_id'];

			$item_class = stratum_css_class([
				$widget_class . '__item',
				(($item['active'] == 'yes' && $once_active == false) ? 'active-accordion' : ''),
				$current_item
			]);

			if ($accordion_type == 'accordion' && $item['active'] == 'yes'){
				$once_active = true;
			}

			ob_start();
				Icons_Manager::render_icon( $item['title_icon'], [ 'aria-hidden' => 'true' ] );
			$title_icon_html = ob_get_clean();

			ob_start();
				Icons_Manager::render_icon( $item['title_icon_active'], [ 'aria-hidden' => 'true' ] );
			$title_icon_active_html = ob_get_clean();

			?>
			<div class="<?php echo esc_attr( $item_class ); ?>">
				<div class="<?php echo esc_attr( $widget_class . '__item-header' ); ?>">
					<div class="<?php echo esc_attr( $widget_class . '__title' ); ?>">
						<?php
						if ( ! empty( $title_icon_html ) || ! empty( $title_icon_active_html ) ) {
							?>
							<span class="<?php echo esc_attr( $widget_class . '__title-icon' ); ?>">
								<?php
								if ( ! empty( $title_icon_html ) ) {
									?>
									<span class="normal"><?php echo $title_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></span>
								<?php
								}

								if ( ! empty( $title_icon_active_html ) ) {
									?>
									<span class="active"><?php echo $title_icon_active_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></span>
								<?php
								}
								?>
							</span>
							<?php
						}

						echo esc_html($item['title']);
						?>
					</div>
					<div class="<?php echo esc_attr( $widget_class . '__expand-icon' ); ?>">
						<?php
						if ( ! empty( $expand_icon_html ) ) {
							?>
							<span class="normal"><?php echo $expand_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php
						}

						if ( ! empty( $expand_icon_active_html ) ) {
							?>
							<span class="active"><?php echo $expand_icon_active_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php
						}
						?>
					</div>
				</div>

				<div class="<?php echo esc_attr( $widget_class . '__item-content' ); ?>">
					<div class="<?php echo esc_attr( $widget_class . '__item-wrapper' ); ?>">
						<div class="<?php echo esc_attr( $widget_class . '__item-content-overlay' ); ?>"></div>
						<div class="<?php echo esc_attr( $widget_class . '__text' ); ?>">
							<?php
							if ( $item['content_type'] == 'text' ) {
								if ( !empty($item['text']) ) {
									echo wp_kses_post( $item['text'] );
								}
							} elseif ( $item['content_type'] == 'template' ) {
								if ( !empty($item['accordion_template']) ) {
									echo $frontend->get_builder_content($item['accordion_template'], true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>
