<?php

use Elementor\Utils;

extract( shortcode_atts( array(
	'title_price_connector' => false,
	'items_divider'			=> false,
	'menu_items'		    => array(),
), $settings ) );

$class = 'stratum-price-menu';

?>
<div class="<?php echo esc_attr( $class ); ?>">
    <div class="<?php echo esc_attr( $class.'__items' ); ?>">
		<?php
        foreach ( $menu_items as $index => $item ) {
            ?>
            <div class="<?php echo esc_attr( 'elementor-repeater-item-'.$item['_id'] . ' ' . $class . '__item-wrapper' ); ?>">
                <div class="<?php echo esc_attr( $class . '__item' ); ?>">
					<?php
                    $id = $item[ 'image' ][ 'id' ];

                    if ( $id && $item[ 'show_image' ] ) {
                        $image_size = $item[ 'image_size' ];
						$url = wp_get_attachment_image_url( $id, $image_size );
						$srcset = wp_get_attachment_image_srcset( $id, $image_size );
						?>
                        <div class="<?php echo esc_attr( $class . '__image image-align-' . $item[ 'image_align' ] ); ?>">
                            <img class="wp-image-<?php echo esc_attr( $id ); ?>" src="<?php echo esc_url( $url ); ?>" srcset="<?php echo esc_attr( $srcset ); ?>"/>
                        </div>
						<?php
                    }
					?>
                    <div class="<?php echo esc_attr( $class . '__content' ); ?>">
                        <div class="<?php echo esc_attr( $class . '__header' ); ?>">
                            <?php
							$tag_name   = $item[ 'title_html_tag' ];
							$menu_title = $item[ 'menu_title' ];
							$menu_price = $item[ 'menu_price' ];
							?>
                            <<?php echo esc_html($tag_name); ?> class="<?php echo esc_attr( $class . '__title' ); ?>"><?php echo esc_html( $menu_title ); ?></<?php echo esc_html($tag_name); ?>>
							<?php
                            if ( $title_price_connector == 'yes' ) {
								?>
                                <span class="<?php echo esc_attr( $class . '__connector' ); ?>"></span>
								<?php
                            }
							?>
                            <span class="<?php echo esc_attr( $class . '__price' ); ?>"><?php echo esc_html( $menu_price ); ?></span>
                        </div>
                        <div class="<?php echo esc_attr( $class . '__description' ); ?>"><?php echo esc_html( $item[ 'menu_description' ] ); ?></div>
						<?php
                        if ( $items_divider == 'yes') {
							?>
                            <div class="<?php echo esc_attr( $class . '__divider' ); ?>"></div>
						<?php
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
