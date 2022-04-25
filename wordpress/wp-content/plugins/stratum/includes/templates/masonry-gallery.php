<?php

extract( shortcode_atts( array(
	'gallery_images' => array(),
	'gallery_columns' => array(),
	'gutter' => array(),
	'animate_on_scroll'   => false,
	'animation_effects'   => '',
	'image_size' => '',
), $settings ) );

$class = 'stratum-masonry-gallery';

$gallery_id = uniqid( 'gallery-' );

$options = [
    'columns' => $gallery_columns['size'],
    'gutter'  => $gutter['size'],
    'animate' => ($animate_on_scroll == 'yes' ? true : false)
];

?>
<div class="<?php echo esc_attr( $class ) . ($animate_on_scroll == 'yes' ? ' animate_on_scroll' : ''); ?> masonry-grid <?php
	echo esc_attr($animation_effects); ?>" data-options="<?php echo esc_attr(json_encode($options)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

	<div class="grid-sizer masonry-col-<?php echo esc_attr($gallery_columns['size']); ?>"></div>
	<?php
    foreach ( $gallery_images as $index => $image ) {
		$data_img = $this->_get_image_attributes( $image[ 'id' ] );

		$url 	 = wp_get_attachment_image_url( $image[ 'id' ], $image_size );
		$srcset  = wp_get_attachment_image_srcset( $image[ 'id' ], $image_size );
		$caption = wp_get_attachment_caption( $image[ 'id' ] );
		?>
		<div class="<?php echo esc_attr( $class . '__item' ); ?> masonry-item">
			<?php
			if (is_admin()){ ?>
				<a href="#" class="<?php echo esc_attr( $class . '__link' ); ?>">
			<?php } else { ?>
				<a data-elementor-open-lightbox="default" data-elementor-lightbox-slideshow="<?php echo esc_attr($gallery_id); ?>" href="<?php
					echo esc_url($image['url']); ?>" class="<?php echo esc_attr( $class . '__link' ); ?>">
			<?php } ?>
                <div class="<?php echo esc_attr( $class . '__image' ); ?>">
                	<figure>
                   		<img class="wp-image-<?php echo esc_attr($image[ 'id' ]); ?>" alt="<?php
							echo esc_attr( $data_img[ 'alt' ] ); ?>" src="<?php echo esc_url($url); ?>" srcset="<?php
							echo $srcset; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php

                   		if ( ! empty( $caption ) ) { ?>
                   			<figcaption class="<?php echo esc_attr( $class . '__caption' ); ?>">
                   				<?php echo wp_kses_post( $data_img[ 'caption' ] ); ?>
                   			</figcaption>
                   		<?php } ?>
                	</figure>
                </div>
                <div class="<?php echo esc_attr( $class . '__overlay' ); ?>"></div>
            </a>
        </div>
	<?php } ?>
</div>
