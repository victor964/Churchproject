<?php

use Elementor\Utils;

extract( shortcode_atts( array(
	//Swiper
	'navigation'					=> 'both',
	'pagination_style'				=> 'bullets',
	'heading_typography_html_tag'	=> 'h3',
	'subtitle_typography_html_tag'	=> 'span',
	//--Swiper
), $settings ) );

$class = 'stratum-testimonial-carousel';
$slider_options = stratum_generate_swiper_options( $settings );

?>
<div class="<?php echo esc_attr( $class ); ?>" data-slider-options="<?php echo esc_attr( json_encode( $slider_options ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
    <div class="swiper-container stratum-main-swiper">
        <div class="swiper-wrapper">
			<?php
            foreach ( $settings[ 'slides' ] as $index => $item ) {

                $current_item = 'elementor-repeater-item-' . $item[ '_id' ];?>
                <div class="swiper-slide <?php echo esc_attr( $current_item ); ?>">
                	<div class="<?php echo esc_attr( $class . "__wrapper" ); ?>">
						<div class="<?php echo esc_attr( $class . "__container" ); ?>">
							<div class="<?php echo esc_attr( $class . "__container-inner" ) ;?>">
								<div class="<?php echo esc_attr( $class . "__footer" ); ?>">
									<?php
									list( , $id ) = array_values( $item[ 'image' ]  );

									if ( ! empty( $item[ 'image' ][ 'url' ] ) ) {
										$url_placeholder = Utils::get_placeholder_image_src();
										$srcset 		 = wp_get_attachment_image_srcset( $id, 'full' );
										$url    		 = wp_get_attachment_image_url   ( $id, 'full' );
										$src_url 		 = empty( $url ) ? $url_placeholder : $url;
										?>
										<img src="<?php echo esc_url( $src_url ); ?>" class="<?php
											echo esc_attr( $class . '__image' ) . " wp-image-" . esc_attr( $id ) ?>" srcset="<?php
											echo $srcset; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
									<?php } ?>
								</div>
								<div class="<?php echo esc_attr( $class . "__content" ); ?>">
										<div class="<?php echo esc_attr( $class . "__cite" );?> ">
										<<?php echo esc_html( $heading_typography_html_tag ); ?> class="<?php echo esc_attr( $class . "__heading" ); ?>">
											<?php echo esc_html( $item[ 'heading' ] ); ?>
										</<?php echo esc_html( $heading_typography_html_tag ); ?>>
										<<?php echo esc_html( $subtitle_typography_html_tag ); ?> class="<?php echo esc_attr( $class . "__subtitle" ); ?>">
											<?php echo esc_html( $item[ 'subtitle' ] ); ?>
										</<?php echo esc_html( $subtitle_typography_html_tag ); ?>>
										</div>
									<div class="<?php echo esc_attr( $class . "__text" ); ?>">
										<?php echo esc_html( $item[ 'content' ] ); ?>
									</div>
								</div>
							</div>
						</div>
                	</div>
                </div>
            <?php } ?>
		</div><?php
		//swiper-wrapper

		if ( $navigation == 'both' || $navigation == 'pagination' ) {
			if ( $pagination_style == 'scrollbar' ) { ?>
				<div class="swiper-scrollbar"></div>
			<?php } else { ?>
				<div class="swiper-pagination"></div>
			<?php }
		}?>
    </div><?php
	//swiper-container

	if ( $navigation == 'both' || $navigation == 'arrows' ) { ?>
		<div class="stratum-swiper-button-prev"></div>
		<div class="stratum-swiper-button-next"></div>
	<?php } ?>
</div>
