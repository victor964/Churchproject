<?php

use \Elementor\Frontend;

extract( shortcode_atts( array(
	'content_type'    	=> '',
	'content_items'    	=> array(),
	'content_animation' => '',
), $settings ) );

$frontend 	     = new Frontend;

$class = 'stratum-content-switcher';
$wrap_class = $class;

$animation_class = stratum_css_class( [ ( $content_animation != 'none' ? $content_animation.'-animation' : 'none-animation' ) ] );


if ( $content_type === 'multiple' ) {
	$wrap_class .= ' is-multiple';
}

if ( $content_type === 'toggle' ) {
	$wrap_class .= ' is-toggle';
}

$once_active_nav     = false;
$once_active_sw      = false;
$once_active_content = false;

$is_active	         = false;

$unique_id 			 = uniqid();

foreach ( $content_items as $index => $item ) {
	if ( $item[ 'active' ] ) {
		$is_active = true;
	}
}
?>

<div class="<?php echo esc_attr( $wrap_class ); ?> ">
	<div class="<?php echo esc_attr( $class . '__wrapper' ); ?>">
		<div class="<?php echo esc_attr( $class . '__nav' ) ;?>">
			<?php
			if ( $content_type === 'multiple' ) :
				?>
				<div class="<?php echo esc_attr( $class . '__nav-content' ); ?>">
					<div class="<?php echo esc_attr( $class . '__nav-pill' ); ?>"></div>
					<ul class="<?php echo esc_attr( $class . '__nav-items' ); ?>">
						<?php
						foreach ( $content_items as $index => $item ) :
							$item_class   = stratum_css_class( [
								$class . '__nav-item',
								( ( $item[ 'active' ] == 'yes' && $once_active_nav == false) || ( $index == 0 && $is_active == false ) ? 'is-active' : '' )
							] );

							if ( $item[ 'active' ] == 'yes' ) :
								$once_active_nav = true;
							endif;

							if ( $content_type !== 'multiple' ) {
								$toggleNavCounter++;

								if ( $toggleNavCounter === 3 ) {
									break;
								}
							}

							if ( $item[ 'title' ] != '' ) :
								?>
								<li data-tab-id="<?php echo esc_attr( $index ); ?>" class="<?php echo esc_attr( $item_class ); ?>">
									<a class="<?php echo esc_attr( $class . '__nav-button' ); ?>" href="#" data-content="id-content-<?php echo esc_attr( $item[ '_id' ] . $unique_id ); ?>">
										<span class="<?php echo esc_attr( $class . '__nav-title' );?>"><?php echo esc_html__( $item[ 'title' ], 'stratum' ); ?></span>
									</a>
								</li>
							<?php
							endif;
						endforeach;
						?>
					</ul>
				</div>
			<?php
			else :
				$toggleNavCounter = 0;

				?>
				<label class="<?php echo esc_attr( $class . '__label' ); ?>">
				<?php
					foreach ( $content_items as $index => $item ) :
						$toggleNavCounter++;

						$item_class   = stratum_css_class( [
							$class . '__nav-item',
							( ( $item[ 'active' ] == 'yes' && $once_active_sw == false) || ( $index == 0 && $is_active == false ) ? 'is-active' : '' )
						] );

						if ( $item[ 'active' ] == 'yes' ) :
							$once_active_sw = true;
						endif;

						if ( $toggleNavCounter === 3 ) {
							break;
						}

						if ( $item[ 'title' ] != '' ) :
							?>
							<a class="<?php echo esc_attr( $item_class . ' ' . $class . '__nav-button' ); ?>" href="#" data-content="id-content-<?php echo esc_attr( $item[ '_id' ] . $unique_id ); ?>">
								<span class="<?php echo esc_attr( $class . '__nav-title' ); ?>"><?php echo esc_html( $item[ 'title' ] ); ?></span>
							</a>

							<?php
							if ( $toggleNavCounter === 1 ) {
								?>
								<input type="checkbox" />
								<i class="<?php echo esc_attr( $class . '__toggle' ); ?>"></i>
								<?php
							}

						endif;
					endforeach;
					?>
				</label>
			<?php
			endif;
			?>
		</div>
		<div class="<?php echo esc_attr( $class . '__content' . ' ' . $animation_class ); ?>">
			<?php
			foreach ( $content_items as $index => $item ) :
				$item_class   = stratum_css_class( [
					$class . '__item',
					( ( $item[ 'active' ] == 'yes' && $once_active_content == false) || ( $index == 0 && $is_active == false ) ? 'is-active' : '' )
				] );

				if ( $item[ 'active' ] == 'yes' ) :
					$once_active_content = true;
				endif;

				if ( $content_type !== 'multiple' ) {
					$toggleContentCounter = 0;
					$toggleContentCounter++;

					if ( $toggleContentCounter === 3 ) {
						break;
					}
				}

				if ( ! empty( $item[ 'content_template' ] ) ) :
					?>
					<div class="<?php echo esc_attr( $item_class ); ?>" id="id-content-<?php echo esc_attr( $item[ '_id' ] . $unique_id ); ?>" >
						<div class="<?php echo esc_attr( $class . '__item-wrapper' ); ?>">
							<?php echo $frontend->get_builder_content( $item[ 'content_template' ], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				<?php
				else :
				?>
					<div class="content-not-found" id="id-content-<?php echo esc_attr( $item[ '_id' ] . $unique_id ); ?>"></div>
				<?php
				endif;

			endforeach;
			?>
		</div>
	</div>
</div>
