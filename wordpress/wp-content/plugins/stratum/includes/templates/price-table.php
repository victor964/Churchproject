<?php

extract( shortcode_atts( array(
	'title' 									=> '',
	'title_typography' 							=> array(),
	'title_typography_html_tag' 				=> '',
	'subtitle' 									=> '',
	'subtitles_typography' 						=> array(),
	'subtitles_typography_html_tag' 			=> '',
	'price_text' 								=> '',
	'price' 									=> '',
	'price_typography' 							=> array(),
	'price_typography_html_tag' 				=> '',
	'price_currency' 							=> '',
	'price_description' 						=> '',
	'content_items' 							=> '',
	'align' 									=> '',
	'button_show' 								=> '',
	'button_text' 								=> '',
	'button_url' 								=> '',
	'title_color' 								=> '',
	'title_color_hover' 						=> '',
	'subtitle_color' 							=> '',
	'subtitle_color_hover' 						=> '',
	'price_color' 								=> '',
	'price_color_hover' 						=> '',
	'price_text_color' 							=> '',
	'price_text_color_hover' 					=> '',
	'description_color' 						=> '',
	'description_color_hover' 					=> '',
	'content_color' 							=> '',
	'content_color_hover' 						=> '',
	'button_color_font' 						=> '',
	'button_color_font_hover' 					=> '',
	'button_color_background' 					=> '',
	'button_color_background_hover' 			=> '',
), $settings ) );

$class = 'stratum-price-table';

$title 	= wp_kses( $title, array(
	'span'		=> array(),
	'mark' 		=> array(),
	'b'			=> array(),
	'strong'	=> array(),
	'br'		=> array()
), $title );

?>
<div class="<?php echo esc_attr($class); ?>">
	<div class="<?php echo esc_attr($class); ?>__wrapper">
		<?php
		//Headers
		if( !empty($subtitle) || !empty($title) ) {
			?>
			<div class="<?php echo esc_attr($class); ?>__header">
				<?php
				if ( !empty($subtitle) ) {
					?>
					<<?php echo esc_attr($subtitles_typography_html_tag); ?> class="<?php echo esc_attr($class); ?> __subtitle"><?php echo esc_html($subtitle); ?></<?php echo esc_attr($subtitles_typography_html_tag); ?>>
				<?php
				}
				if ( !empty($title) ) {
					?>
					<<?php echo esc_attr($title_typography_html_tag); ?> class="<?php echo esc_attr($class);?>__title"><?php echo esc_html($title); ?></<?php echo esc_attr($title_typography_html_tag); ?>>
				<?php
				}
				?>
			</div>
			<?php
		}

		//Price section
		if ( !empty($price_text) || !empty($price) || !empty($price_description) ) {
			?>
			<div class="<?php echo esc_attr($class); ?>__price-wrapper">
				<?php
				if ( !empty($price_text) ) {
					?>
					<div class="<?php echo esc_attr($class); ?>__price-text"><?php echo esc_html($price_text); ?></div>
				<?php
				}

				if ( !empty($price) ) {
					?>
					<p class="<?php echo esc_attr($class); ?>__price"><?php echo esc_html($price);
					if ( !empty( $price_currency ) ) {
						?><i class="<?php echo esc_attr($class); ?>__price-currency"><?php echo esc_html($price_currency); ?></i><?php
					}
					?></p>
				<?php
				}

				if ( !empty($price_description) ) {
					?>
					<p class="<?php echo esc_attr($class); ?>__price-description"><?php echo esc_html($price_description); ?></p>
				<?php
				}
				?>
			</div>
		<?php
		}

		//Content section
		if ( !empty($content_items) ) {
			?>
			<div class="<?php echo esc_attr($class); ?>__content-wrapper">
				<ul>
				<?php
				foreach ($content_items as $key => $item) {
					$item_id = 'elementor-repeater-item-'.esc_attr($item['_id']);
					?>
					<li class="<?php echo esc_attr($item_id . ' ' . $class . '__content');?>"><?php
						if ( !empty($item['item_icon']) ) {
						?><i class="<?php echo esc_attr($item['item_icon']); ?>"></i> <?php
						}
						echo esc_html($item['item_text']);
					?></li>
				<?php
				}
				?>
				</ul>
			</div>
		<?php
		}

		if ( $button_show == 'yes' ) {
			//Button
			?>
			<div class="<?php echo esc_attr($class); ?>__button elementor-widget-button">
				<a href="<?php echo esc_url($button_url['url']); ?>" class="button elementor-button" <?php
					if ( $button_url['is_external'] ) {
						?>
						target="_blank"
					<?php
					}
					?>
				><?php echo esc_html($button_text); ?></a>
			</div>
		<?php
		}
		?>
	</div>
</div>
