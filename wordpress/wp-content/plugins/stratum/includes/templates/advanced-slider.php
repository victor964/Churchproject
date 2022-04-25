<?php

use Elementor\Utils;

extract( shortcode_atts( array(
	'animation_effect'   => '',
	'text_animation_effect'   => '',
	'sub_title_typography_html_tag'   => 'h3',
	'title_typography_html_tag'   => 'h3',
	'description_typography_html_tag'   => 'h3',
	'slides'    => array(),
	'image_size' => '',

	//Swiper
	'columns_count'				=> '1',
	'slides_in_columns'			=> '1',
	'navigation'				=> 'both',
	'pagination_style'			=> 'bullets',
	//--Swiper
), $settings ) );

$widget_class = 'stratum-advanced-slider';

$class = stratum_css_class([
	$widget_class,
	($animation_effect !='none' ? "stratum-effect-".esc_attr( $animation_effect ) : ''),
	(($text_animation_effect !='none' && (intval($columns_count) == 1 && intval($slides_in_columns) == 1 ) ) ? "has-text-animation-".esc_attr( $text_animation_effect ) : '')
]);

//Generate options for swiper
$slider_options = stratum_generate_swiper_options($settings);

?>
<div class="<?php echo esc_attr( $class ); ?>" data-slider-options="<?php echo esc_attr( json_encode($slider_options) ); ?>">
	<div class="swiper-container">
		<div class="swiper-wrapper">
			<?php
			foreach ( $slides as $index => $item ) {
				$id = $item[ 'image' ][ 'id' ];

				if ( $id ) {
					$url = wp_get_attachment_image_url($id, $image_size );
				} else {
					$url = Utils::get_placeholder_image_src();
				}
				$current_item = 'elementor-repeater-item-'.$item['_id'];
				?>
				<div class="swiper-slide <?php echo esc_attr($current_item); ?>">
					<div class="<?php echo esc_attr( $widget_class . '__image' ); ?>" style="background-image: url('<?php echo esc_url($url); ?>'); "></div>
					<div class="<?php echo esc_attr( $widget_class . '__slide-content' ); ?>">
						<div class="<?php echo esc_attr( $widget_class . '__slide-wrapper' ); ?>">
							<div class="<?php echo esc_attr( $widget_class . '__slide-container' ); ?>">
							<?php
							if ( !empty($item['sub_title']) ) {
								?>
								<div class="<?php echo esc_attr( $widget_class . '__sub-title' ); ?>">
									<?php echo esc_html($item['sub_title']); ?>
								</div>
								<?php
							}

							if ( !empty($item['title']) ) {
								?>
								<<?php echo esc_html($title_typography_html_tag); ?> class="<?php echo esc_attr( $widget_class . '__title' );?>">
									<?php echo esc_html($item['title']); ?>
								</<?php echo esc_html($title_typography_html_tag); ?>>
							<?php
							}

							if ( !empty($item['description']) ) {
								?>
								<div class="<?php echo esc_attr( $widget_class . '__description' ); ?>">
									<?php echo esc_html($item['description']); ?>
								</div>
							<?php
							}

							if ( !empty($item['button_text']) ) {
								?>
								<div class="<?php echo esc_attr( $widget_class . '__button' ); ?>">
									<a href="<?php echo esc_url( $item['button_link']['url'] ) ?>" <?php if ( $item['button_link']['is_external'] ) { ?>target="_blank" <?php } ?> ><?php echo esc_html( $item['button_text'] ); ?></a>
								</div>
							<?php
							}
							?>
							</div>
						</div>
					</div>
					<div class="<?php echo esc_attr( $widget_class . '__overlay' ); ?>"></div>
				</div>
			<?php
			}
			?>
		</div>
		<?php
		if ( $navigation == 'both' || $navigation == 'pagination' ) {
			if ( $pagination_style == 'scrollbar' ) {
				?>
				<div class="swiper-scrollbar"></div>
			<?php
			} else {
				?>
				<div class="swiper-pagination"></div>
			<?php
			}
		}
		?>
	</div>

	<?php
	if ( $navigation == 'both' || $navigation == 'arrows' ) {
		?>
		<div class="stratum-swiper-button-prev"></div>
		<div class="stratum-swiper-button-next"></div>
	<?php
	}
	?>
</div>
