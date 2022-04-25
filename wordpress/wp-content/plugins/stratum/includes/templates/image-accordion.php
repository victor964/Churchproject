<?php

extract(shortcode_atts([
    'direction'   => 'horizontal',
    'active_type' => 'activate-on-click',
    'skew_switcher'  => '',
    'skew_direction' => 'right',
    'content_align'  => 'center',
    'hovered_default_active' => 0,
    'opened_default_active'  => 0
], $settings ));

$class = $this->get_name();

/* #region Widget classes */
$widget_class = [ $class, 'image-accordion-' . esc_attr( $direction ), $active_type ];

$skew_class = '';
if ( $skew_switcher && $direction == 'horizontal' ) {
    $skew_class = 'skew-direction-' . $skew_direction;
    array_push(
        $widget_class,
        'image-accordion-skew'
    );
}

$this->add_render_attribute( 'widget', 'class', $widget_class );
$widget_class = $this->get_render_attribute_string( 'widget' );
/* #endregion */

?>
<div <?php echo $widget_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="<?php echo esc_attr( trim( $class . '__container' . ' ' . $skew_class ) ); ?>"><?php

    foreach ( $settings[ 'image_content' ] as $index => $item ) {

        /* #region Item classes */
        $item_classes = [ $class . '__item' ,'elementor-repeater-item-' . esc_attr( $item[ '_id' ] ) ];
        $default_active = $active_type == 'activate-on-click' ? $opened_default_active : $hovered_default_active;

        if ( $default_active && ($default_active - 1) == $index ) {
            array_push( $item_classes, 'default-active' );
        }

        $this->add_render_attribute( 'item' . $index, [ 'class' => $item_classes ] );
        $item_classes = $this->get_render_attribute_string( 'item' . $index );
        /* #endregion */
		?>
        <div <?php echo $item_classes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="<?php echo esc_attr( $class . '__background' ); ?>"></div>
            <div class="<?php echo esc_attr( $class . '__overlay' ); ?>">
				<?php
                /* #region Content classes */
                $this->add_render_attribute( 'content', [ 'class' => [ $class . '__content', 'image-accordion-' . $content_align ] ] );
                $content_classes = $this->get_render_attribute_string( 'content' );
                /* #endregion */

                if ( $item[ 'content_switcher' ] ) {

                    $title = $item[ 'item_title' ];
                    $description = $item[ 'item_description' ];
					?>
                    <div <?php echo $content_classes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php
                        if ( $item[ 'icon_switcher' ] ) {
                            $icon = $item[ 'icon_updated' ]; ?>
                            <i class="<?php echo esc_attr( $class . '__icon ' . $icon ); ?>"></i>
                        <?php } ?>
                        <h3 class="<?php echo esc_attr( $class . '__title' ); ?>">
							<?php echo esc_html( $title ); ?>
						</h3>
                        <div class="<?php echo esc_attr( $class . '__description' ); ?>">
							<?php echo esc_html( $description ) ?>
						</div>
						<?php
                        /* #region Render button */
                        $link 		 = $item[ 'link' ];
                        $button_text = $item[ 'button_text' ];
                        $show_button = $item[ 'show_button' ];

                        if ( ! empty( $button_text ) && $show_button == 'yes' ) {
                            echo $this->image_accordion_render_button( $index, $button_text, $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        /* #endregion */
					?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    </div>
</div>
