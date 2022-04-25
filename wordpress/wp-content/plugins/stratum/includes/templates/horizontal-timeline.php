<?php

$class = 'stratum-horizontal-timeline';

$alignment = $settings[ 'horizontal_alignment' ];
$this->add_render_attribute( 'widget', [
    'class' => [
        $class,
        $class . '--align-' . $alignment
    ]
] );

$widget_class = $this->get_render_attribute_string( 'widget' );

?>
<div <?php echo $widget_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="<?php echo esc_attr( $class . '__inner' ); ?>">
        <div class="<?php echo esc_attr( $class . '__track' ); ?>">
            <?php
            /* #region Render card */
            $layout = $settings[ 'horisontal_layout' ]; ?>

			<div class="<?php echo esc_attr( $class . '__list ' . $class . '__list--top' ); ?>"><?php

                foreach ( $settings[ 'items_content' ] as $index => $item ) {

                    $uniqid = uniqid();
                    $is_active = $item[ 'is_item_active' ];
                    $title_html_tag = $settings[ 'item_title_tag' ];
                    $this->add_render_attribute( 'item' . $uniqid, [ 'class' => [
                        $class . '-item',
                        !empty( $is_active ) ? 'is-active' : '',
                        'elementor-repeater-item-' . esc_attr( $item[ '_id' ] )
                    ] ] );

                    $item_class = $this->get_render_attribute_string( 'item' . $uniqid );
					?>
                    <div <?php echo $item_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php
                        if ( $layout != 'bottom' ) {
                            if ( $layout == 'chess' ) {
                                if ( (int)bcmod( strval( $index + 1 ), '2' ) != 0 ) {
                                    echo $this->_generate_card_content( $class, $item, $settings, $title_html_tag, $index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                } else { ?>
                                    <div class="<?php echo esc_attr( $class . '-item__meta' ); ?>">
										<?php echo esc_html( $item[ 'item_meta' ] ); ?>
									</div>
                                <?php }
                            } else {
                                echo $this->_generate_card_content($class, $item, $settings, $title_html_tag, $index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                        } else if ( $layout == 'bottom' ) { ?>
                            <div class="<?php echo esc_attr( $class . '-item__meta' ); ?>">
								<?php echo esc_html( $item[ 'item_meta' ] ); ?>
							</div><?php
                        } ?>
                    </div>
                <?php } ?>
            </div>
			<?php
            /* #endregion */

            /* #region Render points */
			?>
            <div class="<?php echo esc_attr( $class . '__list ' . $class . '__list--middle' ); ?>">
                <div class="<?php echo esc_attr( $class . '__line' ); ?>"></div>
				<?php
                foreach ( $settings[ 'items_content' ] as $index => $item ) {
                    $uniqid = uniqid();
                    $is_active = $item[ 'is_item_active' ];

                    $this->add_render_attribute( 'item' . $uniqid, [ 'class' => [
                        $class . '-item',
                        !empty( $is_active ) ? 'is-active' : '',
                        'elementor-repeater-item-' . esc_attr( $item[ '_id' ] )
                    ] ] );

                    $item_class = $this->get_render_attribute_string( 'item' . $uniqid );
					?>
                    <div <?php echo $item_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <div class="<?php echo esc_attr( $class . '-item__point' ); ?>">
                            <?php echo $this->_generate_point_content( $class, $item, $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
			<?php
            /* #endregion */

            /* #region Render meta */
			?>
            <div class="<?php echo esc_attr( $class . '__list ' . $class . '__list--bottom' ); ?>">
				<?php
                foreach ( $settings[ 'items_content' ] as $index => $item ) {
                    $uniqid = uniqid();
                    $is_active = $item[ 'is_item_active' ];
                    $this->add_render_attribute( 'item' . $uniqid, [ 'class' => [
                        $class . '-item',
                        !empty( $is_active ) ? 'is-active' : '',
                        'elementor-repeater-item-' . esc_attr( $item[ '_id' ] )
                    ] ] );

                    $item_class = $this->get_render_attribute_string( 'item' . $uniqid );
					?>
                    <div <?php echo $item_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php
                        if ( $layout != 'bottom' ) {
                            if ( $layout == 'chess' ) {
                                if ( (int)bcmod( strval( $index + 1 ), '2' ) != 0 ) { ?>
                                    <div class="<?php echo esc_attr( $class . '-item__meta' ); ?>">
										<?php echo esc_html( $item[ 'item_meta' ] ); ?>
									</div>
								<?php
                                } else {
                                    echo $this->_generate_card_content($class, $item, $settings, $title_html_tag, $index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                }
                            } else { ?>
                                <div class="<?php echo esc_attr( $class . '-item__meta' ); ?>">
									<?php echo esc_html( $item[ 'item_meta' ] ); ?>
								</div><?php
                            }
                        } else if ( $layout == 'bottom' ) {
                            echo $this->_generate_card_content($class, $item, $settings, $title_html_tag, $index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } ?>
                    </div>
                <?php } ?>
            </div><?php
            /* #endregion */
			?>
        </div>
    </div>
</div>
