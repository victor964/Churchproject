<?php

$class = 'stratum-vertical-timeline';

$alignment = $settings[ 'vertical_alignment' ];
$this->add_render_attribute( 'widget', [
    'class' => [
        $class,
        $class . '--align-' .  $alignment
    ],
    'data-animation' => esc_attr( $settings[ 'animate_cards' ] )
]);

$this->add_render_attribute( 'inner', [
    'class' => [
        $class . '-item__inner',
        $class . '-item__inner' . $this->_get_alignment( $settings, 'vertical' )
    ]
]);

$item_classes = [
    $class . '-item',
    $this->_get_alignment( $settings, 'horizontal' )
];

$widget_classes = $this->get_render_attribute_string( 'widget' );
$inner_classes  = $this->get_render_attribute_string( 'inner' );

?>
<div <?php echo $widget_classes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr( $class . '__line' ); ?>">
        <div class="<?php echo esc_attr( $class . '__line-progress' ); ?>"></div>
    </div>

    <div class="<?php echo esc_attr( $class . '__list' ); ?>"><?php

        foreach ( $settings[ 'image_content' ] as $index => $item ) {

            $merge = array_merge( $item_classes, [ 'elementor-repeater-item-' . esc_attr( $item[ '_id' ] ) ] );
            $title_html_tag = $settings[ 'title_tag' ];

            $this->add_render_attribute( 'item' . $index, [ 'class' => $merge ] );
            $item_class = $this->get_render_attribute_string( 'item' . $index );
			?>
            <div <?php echo $item_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <div <?php echo $inner_classes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <div class="<?php echo esc_attr( $class . '-item__card' ); ?>">
                        <div class="<?php echo esc_attr( $class . '-item__card-inner' );?>">
							<?php
                            /* #region Render image */
                            if ( !empty( $item[ 'show_item_image' ] ) ) {
                                echo $this->_get_timeline_image( $class, $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                            /* #endregion */
							?>
                            <div class="<?php echo esc_attr( $class . '-item__card-content' );?>">
							<?php
								if ( ! empty( $item[ 'item_link' ][ 'url' ] ) ) {
									$this->add_link_attributes( 'url' . $index, $item[ 'item_link' ] ); ?>
									<a class="<?php echo esc_attr( $class . '-item__card-link' );?>" <?php
										echo $this->get_render_attribute_string( 'url' . $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>>
								<?php } ?>

                                <<?php echo esc_html($title_html_tag); ?> class="<?php echo esc_attr( $class . '-item__card-title' );?>">
									<?php echo esc_html( $item[ 'item_title' ] ); ?>
								</<?php echo esc_html($title_html_tag); ?>>

								<?php
								if ( ! empty( $item[ 'item_link' ][ 'url' ] ) ) { ?>
									</a>
								<?php }

								if ( $item[ 'item_description_type' ] === 'default' ) { ?>
									<div class="<?php echo esc_attr( $class . '-item__card-description' ); ?>">
										<?php echo esc_html( $item[ 'item_description' ] ); ?>
									</div>
								<?php } else { ?>
									<div class="<?php echo esc_attr( $class . '-item__card-description' ); ?>">
										<?php echo wp_kses_post( $item[ 'item_description_editor' ] ); ?>
									</div>
								<?php } ?>
                            </div>
                            <div class="<?php echo esc_attr( $class . '-item__card-arrow' ); ?>"></div>
                        </div>
                    </div>

                    <div class="<?php echo esc_attr( $class . '-item__point' ); ?>">
                        <?php
							echo $this->_generate_point_content( $class, $item, $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
                    </div>

                    <div class="<?php echo esc_attr( $class . '-item__meta' ); ?>">
                        <div class="<?php echo esc_attr( $class . '-item__meta-content' ); ?>">
							<?php echo esc_html( $item[ 'item_meta' ] ); ?>
						</div>
                    </div>

                </div>
            </div>
        <?php } ?>
    </div>
</div>
