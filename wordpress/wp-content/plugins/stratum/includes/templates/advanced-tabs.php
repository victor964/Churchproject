<?php

use Elementor\Frontend;
use Elementor\Icons_Manager;

extract( shortcode_atts( array(
	'tabs_items'    => array(),
	'item_icon' => '',
	'tabs_layout' => 'horizontal',
	'tabs_interactivity' => 'click',
	'equal_height' => '',
	'content_animation' => '',

), $settings ) );

$widget_class = 'stratum-advanced-tabs';

$is_active = false;
foreach ( $tabs_items as $index => $item ) {
	if ($item['active']){
		$is_active = true;
	}
}

$class = stratum_css_class([
	$widget_class,
	'tabs-layout-'.esc_attr($tabs_layout),
	($content_animation != 'none' ? $content_animation.'-animation' : ''),
]);

$accordion_options = [
	'tabs_interactivity' => $tabs_interactivity,
	'equal_height' => ($equal_height == 'yes' ? true : false),
];

$frontend = new Frontend;

ob_start();
	Icons_Manager::render_icon( $item_icon, [ 'aria-hidden' => 'true' ] );
$item_icon_html = ob_get_clean();

$once_active_nav = false;
$once_active_content = false;

?>
<div class="<?php echo esc_attr( $class ); ?>" data-tabs-options="<?php echo esc_attr( json_encode($accordion_options) ); ?>">
	<div class="<?php echo esc_attr( $widget_class . '__navigation' ); ?>">
		<?php
		foreach ( $tabs_items as $index => $item ) {
			$current_item = 'elementor-repeater-item-'.$item['_id'];

			$item_class = stratum_css_class([
				$widget_class . '__navigation-item',
				(($item['active'] == 'yes' && $once_active_nav == false) || ($index == 0 && $is_active == false) ? 'active-nav' : ''),
				$current_item
			]);

			if ($item['active'] == 'yes'){
				$once_active_nav = true;
			}

			ob_start();
				Icons_Manager::render_icon( $item['tab_icon'], [ 'aria-hidden' => 'true' ] );
			$item_icon_html = ob_get_clean();
			?>
			<div data-tab-id="<?php echo esc_attr($index); ?>" class="<?php echo esc_attr( $item_class ); ?>">
				<?php
				if ( !empty($item['tab_title']) ) {
					?>
					<div class="<?php echo esc_attr( $widget_class . '__title' ); ?>">
						<?php echo esc_html($item['tab_title']); ?>
					</div>
				<?php
				}
				if ( !empty($item_icon_html) ) {
					?>
					<div class="<?php echo esc_attr( $widget_class . '__icon' ); ?>">
						<span><?php echo $item_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
				<?php
				}
				?>
			</div>
		<?php
		}
		?>
	</div>
	<div class="<?php echo esc_attr( $widget_class . '__content' ); ?>">
		<?php
		foreach ( $tabs_items as $index => $item ) {
			$current_item = 'elementor-repeater-item-'.$item['_id'];

			$item_class = stratum_css_class([
				$widget_class . '__content-item',
				(($item['active'] == 'yes' && $once_active_content == false ) || ($index == 0 && $is_active == false) ? 'active-content' : ''),
				$current_item
			]);

			if ($item['active'] == 'yes'){
				$once_active_content = true;
			}
			?>
			<div data-tab-id="<?php echo esc_attr($index); ?>" class="<?php echo esc_attr( $item_class );?>">
				<div class="<?php echo esc_attr( $widget_class . '__content-wrapper' ); ?>">
					<div class="<?php echo esc_attr( $widget_class . '__content-overlay' ); ?>"></div>
					<div class="<?php echo esc_attr( $widget_class . '__text' ); ?>">
						<?php
						if ( $item['content_type'] == 'text' ) {
							if ( !empty($item['tab_text']) ) {
								echo wp_kses_post( $item['tab_text'] );
							}
						} elseif ( $item['content_type'] == 'template' ) {
							if ( !empty($item['tab_template']) ) {
								echo $frontend->get_builder_content($item['tab_template'], true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}
						?>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>
