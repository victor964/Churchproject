<?php

extract(shortcode_atts([
    'image' => [],
    'link'  => [],
    'image_size'     => 'full',
    'flip_effect'    => 'flip',
    'flip_direction' => 'right',
    'icon_shape'     => 'circle',
    'icon_view'      => 'default',
    'show_button'    => '',
    'button_text'    => ''
], $settings ));

$class = $this->get_name();


/* #region Flip Box classes */
$widget_class = [ $class, 'flip-box-effect-'.esc_attr( $flip_effect ) ];
$icon_class   = [ $class.'__icon-wrapper', 'stratum-view-'.$icon_view ];

if ( $flip_effect == 'flip' || $flip_effect == 'slide' || $flip_effect == 'push' ) {
    array_push(
        $widget_class,
        'flip-box-direction-'.esc_attr( $flip_direction )
    );
}

if ( $icon_view != 'default' && $icon_shape == 'circle' || $icon_shape == 'square' ) {
    array_push(
        $icon_class,
        'stratum-shape-'.esc_attr( $icon_shape )
    );
}

$this->add_render_attribute( 'widget', 'class', $widget_class );
$this->add_render_attribute( 'icon-wrapper', 'class', $icon_class );
/* #endregion */

$widget_class = $this->get_render_attribute_string( 'widget'  );

?>
<div <?php echo $widget_class // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="<?php echo esc_attr( $class.'__inner' ); ?>">
        <div class="<?php echo esc_attr( $class . '__layer ' . $class . '__front' ); ?>">
            <div class="<?php echo esc_attr( $class.'__layer__overlay' ); ?>">
                <div class="<?php echo esc_attr( $class.'__layer__inner' ); ?>">
					<?php
                    $graphic = $settings[ 'graphic_element' ];
                    if ( $graphic == 'icon' ) {
						echo $this->flip_box_render_icon( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else if ( $graphic == 'image' ) {
                        echo $this->flip_box_render_image( $image, $image_size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }

					$title = $settings[ 'front_title_text' ];
                    $description = $settings[ 'front_description_text' ];
					?>
                    <h3 class="<?php echo esc_attr( $class.'__title' ); ?>"><?php echo esc_html( $title ); ?></h3>
                    <div class="<?php echo esc_attr( $class.'__description' ); ?>"><?php echo esc_html( $description ); ?></div>
                </div>
            </div>
        </div>

        <div class="<?php echo esc_attr( $class . '__layer ' . $class . '__back' ); ?>">
            <div class="<?php echo esc_attr( $class . '__layer__overlay' ); ?>">
                <div class="<?php echo esc_attr( $class.'__layer__inner' ); ?>">
					<?php
                    $title = $settings[ 'back_title_text' ];
                    $description = $settings[ 'back_description_text' ];
					?>
                    <h3 class="<?php echo esc_attr( $class.'__title' ); ?>"><?php echo esc_html( $title ); ?></h3>
                    <div class="<?php echo esc_attr( $class.'__description' ); ?>"><?php echo esc_html( $description ); ?></div>
					<?php
                    if ( !empty($button_text) && $show_button == 'yes' ) {
                        echo $this->flip_box_render_button( $button_text, $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
					?>
				</div>
            </div>
        </div>
    </div>
</div>
