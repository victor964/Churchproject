<?php

use Elementor\Utils;

extract( shortcode_atts( [
    'image' => '',
    'image_size' => 'full',
    'item_title' => '',
    'item_price' => '',
    'title_html_tag' => 'h3',
    'list_title' => '',
    'image_position' => '',
    'show_image' => 'no',
    'title_price_connector' => false
], $settings ) );

$class = 'stratum-price-list';

?>
<div class="<?php echo esc_attr( $class.' '.$image_position ); ?>">
	<?php
    if ( $image[ 'id' ] && $show_image == 'yes' ) {
		$url = wp_get_attachment_image_url( $image[ 'id' ], $image_size );
		$srcset = wp_get_attachment_image_srcset( $image[ 'id' ], $image_size );
		?>
        <div class="<?php echo esc_attr( $class.'__image-wrapper' ); ?>">
            <img src="<?php echo esc_url( $url ); ?>" class="wp-image-<?php echo esc_attr( $image[ 'id' ] . ' ' . $class . '__image' ); ?>" srcset="<?php echo esc_attr( $srcset ); ?>"/>
        </div>
	<?php
    }
	?>
    <div class="<?php echo esc_attr( $class . '__wrapper' ); ?>">
        <div class="<?php echo esc_attr( $class . '__content' ); ?>">
            <<?php echo esc_html($title_html_tag); ?> class="<?php echo esc_attr( $class.'__heading' ); ?>"><?php echo esc_html( $list_title ); ?></<?php echo esc_html($title_html_tag); ?>>
            <div class="<?php echo esc_attr( $class.'__items' ); ?>">
				<?php
                foreach ( $settings[ 'list_items' ] as $index => $item ) {
					?>
                    <div class="<?php echo esc_attr( $class.'__item' ); ?>">
						<?php
                        $tag_name = $item[ 'title_html_tag' ];
                        $title    = $item[ 'item_title' ];
                        $price    = $item[ 'item_price' ];
						?>
                        <<?php echo esc_html($tag_name); ?> class="<?php echo esc_attr( $class.'__title' ); ?>"><?php echo esc_html( $title );?></<?php echo esc_html($tag_name); ?>>
						<?php
                        if ( $title_price_connector == 'yes' ) {
							?>
                            <span class="<?php echo esc_attr( $class.'__connector' ); ?>"></span>
						<?php
                        }
						?>
                        <span class="<?php echo esc_attr( $class.'__price' ); ?>"><?php echo esc_html( $price ); ?></span>
                    </div>
				<?php
                }
				?>
            </div>
        </div>
    </div>
</div>
