<?php

use Elementor\Utils;

extract( shortcode_atts( [
	'image'					=> '',
    'image_size' 			=> '',
    'hosted_url'            => '',
	'title' 				=> '',
	'text'       			=> '',
	'title_typography_html_tag'	=> 'h5',
	'link'       			=> '',
	'link_target' 			=> '',
	'link_rel'    			=> '',
	'animation_effect' 		=> 'none',
	'text_animation_effect' => 'none',
	'background_type' 		=> 'image'
], $settings ) );

$class = 'stratum-banner';

$id = $image[ 'id' ];
$link = !empty( $link ) ? $link : "#";
$url = wp_get_attachment_image_url( $image[ 'id' ], $image_size );
$srcset = wp_get_attachment_image_srcset($image[ 'id' ], $image_size);

?>
<figure class="<?php echo esc_attr( $class . ' stratum-effect-' . $animation_effect . ' has-text-animation-' . $text_animation_effect  ); ?> ">
        <a href="<?php echo esc_url( $link ); ?>" class="<?php echo esc_attr( $class.'__link' ); ?>"
		   <?php
			if ( ! empty( $link_target ) ) {
				?>
				target="<?php echo esc_attr( $link_target ); ?>"
				<?php
				}
			if ( ! empty( $link_rel ) ) {
				?>
				rel="<?php echo esc_attr( $link_rel ); ?>"
				<?php
			}
			?>
        >
		<?php
        if ( $link ) {
			?>
            <div class="<?php echo esc_attr( $class.'__wrapper' ); ?>">
				<?php
                if ( $background_type == 'video' ) {
					?>
                    <video class="<?php echo esc_attr( $class.'__video' ); ?>" autoplay muted loop>
                        <source src="<?php echo esc_url( $hosted_url[ 'url' ] ); ?>" type='video/mp4'>
                    </video>
				<?php
                } else {
					?>
                    <img src="<?php if ( empty( $id ) ) { echo esc_url(Utils::get_placeholder_image_src()); } else { esc_url( $url ); } ?>" class="<?php echo esc_attr( $class . '__image' . ' wp-image-' . $id ); ?>" srcset="<?php echo esc_attr( $srcset ); ?>"/>
				<?php
                }
				?>
	            <div class="<?php echo esc_attr( $class.'__overlay' ); ?>"></div>
                <figcaption class="<?php echo esc_attr( $class.'__content' ); ?>">
					<div class="<?php echo esc_attr( $class.'__content-wrapper' ); ?>">
						<div class="<?php echo esc_attr( $class.'__content-container' ); ?>">
							<<?php echo esc_html($title_typography_html_tag); ?> class="<?php echo esc_attr( $class.'__title' ); ?>"><?php echo esc_html( $title ); ?></<?php echo esc_html($title_typography_html_tag); ?>>
							<div class="<?php echo esc_attr( $class.'__text' ); ?>"><?php echo esc_html( $text ); ?></div>
						</div>
                    </div>
                </figcaption>
            </div>
		<?php
        }
		?>
    </a>
</figure>
