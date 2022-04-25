<?php

extract( shortcode_atts( array(
	'items'		     => 6,
	'columns'		 => 3,
	'columns_mobile' => 3,
	'columns_tablet' => 3,
	'animate_on_scroll' => '',
	'animation_effects'   => '',
), $settings ) );

//Get Access Token
$stratum_api = get_option( 'stratum_api', [] );
$access_token = isset( $stratum_api['instagram_access_token'] ) ? $stratum_api['instagram_access_token'] : '';

//If Empty Token
if ( isset( $access_token ) && empty( $access_token ) ) {
	if ( current_user_can( 'manage_options' ) ) {
		?><p><?php
		echo wp_kses(
			sprintf(
				__( 'Instagram Access Token is not set. <a href="%s" target="_blank">Connect Instagram Account</a>.', 'stratum' ),
				admin_url( 'admin.php?page=stratum-settings#stratum_api' )
			),
			array( 'a' => array( 'href' => array(), 'target' => array() ) )
		);
		?></p><?php
		return;
	} else {
		return '';
	}
}

$instagram_media = false;
if ( false === $instagram_media ) {

	//Get data from Instagram
	$response = wp_remote_get(
		'https://graph.instagram.com/me/media?fields=id,media_type,media_url,permalink,caption,thumbnail_url,children{media_url,thumbnail_url}&access_token=' . $access_token . '&limit=100',
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $response ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p>' . $response->get_error_message() . '</p>';
		} else {
			return '';
		}
	} else {
		$instagram_media = json_decode( wp_remote_retrieve_body( $response ) );

		//JSON valid
		if ( json_last_error() === JSON_ERROR_NONE ) {
			if ( isset( $instagram_media->error ) ) {
				?><p><?php
				echo wp_kses(
					sprintf(
						__( 'The access token could not be decrypted. Your access token is currently invalid. <a href="%s" target="_blank">Please re-authorize your Instagram account</a>.', 'stratum' ),
						admin_url( 'admin.php?page=stratum-settings#stratum_api' )
					),
					array( 'a' => array( 'href' => array(), 'target' => array() ) )
				);
				?></p><?php

				return;
			} else {
				if ( $instagram_media->data ) {
					//Cache response
					set_transient( 'stratum_instagram_response_data', $instagram_media, 30 * MINUTE_IN_SECONDS );
				} else {
					if ( current_user_can( 'manage_options' ) ) {
						return '<p>' . $instagram_media->meta->error_message . '</p>';
					} else {
						return '';
					}
				}
			}
		} else {
			return esc_html__( 'Error in json_decode.', 'stratum' );
		}
	}
}

$widget_name = 'stratum-instagram';

$class = $block_name = 'stratum-instagram';

$wrapper_class = 'stratum-instagram__wrapper masonry-grid' . ( $animate_on_scroll == 'yes' ? (' ' . $animation_effects . ' animate_on_scroll') : '' );

?>
<div class="<?php echo esc_attr( $class ); ?>">
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php
	$counter = 1;
	foreach ( $instagram_media->data as $key => $value ) {
		if ( $counter <= $items ) {

			$alt = '';
			if ( isset( $value->caption ) ) {
				$alt = wp_trim_words( $value->caption );
			}
			?>
			<div class="<?php echo esc_attr( $widget_name ); ?>__item masonry-item">
				<div class="<?php echo esc_attr( $widget_name ); ?>__media-wrapper">
					<a class="<?php echo esc_attr( $widget_name ); ?>__media-link" target="_blank" href="<?php echo esc_url( $value->permalink ); ?>">
						<?php
						if ( $value->media_type == 'IMAGE' || $value->media_type == 'CAROUSEL_ALBUM' ){ ?>
							<img class="<?php echo esc_attr( $widget_name ); ?>__media" src="<?php echo esc_url( $value->media_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>"/>
						<?php } elseif ($value->media_type == 'VIDEO'){ ?>
							<img class="<?php echo esc_attr( $widget_name ); ?>__media" src="<?php echo esc_url( $value->thumbnail_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>"/>
						<?php } ?>
					</a>
				</div>
			</div>
		<?php }
		$counter ++;
	} ?>
	</div>
</div>
