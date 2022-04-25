<?php

use Elementor\Utils;

use Stratum\Ajax\Advanced_Posts_Ajax;

extract( shortcode_atts( array(
	'slide_animation_effect'   => '',
	'slide_text_animation_effect'   => '',
	'title_typography_html_tag'   => 'h3',
	'show_image' => '',
	'show_title' => '',
	'show_content' => '',
	'show_excerpt' => '',
	'show_read_more' => '',
	'read_more_text' => '',
	'open_new_tab' => '',
	'excerpt_length' => apply_filters( 'excerpt_length', 25 ),
	'show_meta' => array(),
	'meta_fields_divider' => '',
	'image_size' => '',
	'image_hover_effect' => '',
	'posts_layout' => '',
	'pagination' => '',
	'page_pagination_style' => '',
	'scroll_icon' => '',
	'load_more_text' => '',
	'column_gap' => '',
	'row_gap' => '',
	'masonry' => '',
	'columns' => '',
	'columns_tablet' => '',
	'columns_mobile' => '',
	'animate_on_scroll' => '',
	'animation_effects' => '',

	//Swiper
	'columns_count'				=> '1',
	'slides_in_columns'			=> '1',
	'navigation'				=> 'both',
	'pagination_style'			=> 'bullets',
	//--Swiper
), $settings ) );

//Query builder
$query_args = [];
stratum_build_custom_query( $query_args, $settings );

$q = new \WP_Query( $query_args );

$widget_class = 'stratum-advanced-posts';

$class = stratum_css_class([
	$widget_class,
	'layout-'.$posts_layout,
	($masonry == '' || intval($columns) == 1 || $posts_layout == 'carousel' || $posts_layout == 'list' ? 'masonry-disable' : 'masonry-enable'),
	(($posts_layout == 'grid' && $masonry == '') ? "elementor-grid-{$columns} elementor-grid-tablet-{$columns_tablet} elementor-grid-mobile-{$columns_mobile}" : ''),
	((($posts_layout == 'grid' || $posts_layout == 'list') && $image_hover_effect != 'none') ? "image-effect-".esc_attr( $image_hover_effect ) : ''),
	(($posts_layout == 'carousel' && $slide_animation_effect != 'none') ? "slide-effect-".esc_attr( $slide_animation_effect ) : ''),
	(($posts_layout == 'carousel' && $slide_text_animation_effect != 'none' && (intval($columns_count) == 1 && intval($slides_in_columns) == 1 )) ? "has-text-animation-".esc_attr( $slide_text_animation_effect ) : '')
]);

$wrapper_class = stratum_css_class([
	$widget_class . '__wrapper',
	(($posts_layout == 'grid' && $masonry == '') ? 'elementor-grid' : ''),
	((($posts_layout == 'grid' || $posts_layout == 'list') && ($animate_on_scroll == 'yes' || ($masonry == 'yes' && intval($columns) > 1))) ? "masonry-grid" : ''),
	((($posts_layout == 'grid' || $posts_layout == 'list') && $animate_on_scroll == 'yes') ? "animate_on_scroll ".esc_attr($animation_effects) : ''),
]);

$query_options = [
	//Query args
    'include_ids' => $settings['include_ids'],
    'post_type' => $settings['post_type'],
    'posts_per_page' => $settings['posts_per_page'],
    'order' => $settings['order'],
    'orderby' => $settings['orderby'],
    'ignore_sticky_posts' => $settings['ignore_sticky_posts'],
    'pagination' => $settings['pagination'],
    'exclude_ids' => $settings['exclude_ids'],
    'exclude_current' => $settings['exclude_current'],
    'taxonomies' => $settings['taxonomies'],
	'terms_relation' => $settings['terms_relation'],

	//Settings
	'posts_layout' => $settings['posts_layout'],
	'columns' => $settings['columns'],
	'masonry' => $settings['masonry'],
	'animate_on_scroll' => $settings['animate_on_scroll'],
	'show_title' => $settings['show_title'],
	'show_image' => $settings['show_image'],
	'image_size' => $settings['image_size'],
	'title_typography_html_tag' => $settings['title_typography_html_tag'],
	'title_over_image' => $settings['title_over_image'],
	'show_meta' => $settings['show_meta'],
	'meta_fields_divider' => $settings['meta_fields_divider'],
	'show_content' => $settings['show_content'],
	'show_excerpt' => $settings['show_excerpt'],
	'excerpt_length' => $settings['excerpt_length'],
	'show_read_more' => $settings['show_read_more'],
	'open_new_tab' => $settings['open_new_tab'],
	'read_more_text' => $settings['read_more_text'],
];

//Add terms from taxonomies list
if (!empty($settings['taxonomies'])){
	foreach ($settings['taxonomies'] as $taxonomy_key => $taxonomy_name) {
		if (isset($settings[$taxonomy_name.'_terms'])){
			$query_options[$taxonomy_name.'_terms'] = $settings[$taxonomy_name.'_terms'];
		}
	}
}

$masonry_options = [
    'columns' => $columns,
    'column_gap' => $column_gap['size'],
    'row_gap' => $row_gap['size'],
];

//Generate options for swiper
$slider_options = stratum_generate_swiper_options($settings);

if ( $posts_layout == 'grid' || $posts_layout == 'list' ) {
	?>
	<div class="<?php echo esc_attr( $class ); ?>"
		<?php
		if ( $pagination == 'yes' && ( $page_pagination_style == 'load_more_btn' || $page_pagination_style == 'load_more_scroll' ) ) {
		?>
		 data-query-options="<?php echo esc_attr( json_encode( $query_options ) ); ?>"
		<?php
		}
		?>
	>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-masonry-options="<?php echo esc_attr( json_encode( $masonry_options ) ); ?>">
			<?php
			if ( $posts_layout == 'grid' && intval($columns) > 1 && $masonry == 'yes' ) {
			?>
				<div class="grid-sizer masonry-col-<?php echo esc_attr( $columns ); ?>"></div>
			<?php
			}

			//Get Articles
			echo Advanced_Posts_Ajax::get_instance()->get_articles($settings, 'render'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>

		<?php
		if ( $pagination == 'yes' ) {
			if ( $page_pagination_style == 'load_more_btn' || $page_pagination_style == 'load_more_scroll' ) {
				?>
				<nav class="ajax_load_more_pagination <?php if ( $page_pagination_style == 'load_more_scroll' ) { ?> load_more_scroll <?php } ?>" role="navigation">
					<?php
					if ( $page_pagination_style == 'load_more_scroll' ) {
					?>
						<span class="<?php echo esc_attr( $widget_class . '__ajax-load-more-arrow' ); ?>"><i class="<?php echo esc_attr( $scroll_icon ); ?>"></i></span>
					<?php
					}
					?>
					<a class="<?php echo esc_attr( $widget_class . '__ajax-load-more-btn' ); ?>" href="#" data-current-page="1" data-max-page="<?php echo esc_attr($q->max_num_pages); ?>"><?php echo esc_html($load_more_text); ?></a>
				</nav>
				<?php
			} else if ( $page_pagination_style == 'navigation' ) {
				?>
				<nav class="navigation pagination" role="navigation">
					<h2 class="screen-reader-text"><?php echo esc_html__('Posts navigation', 'stratum'); ?></h2>
					<div class="nav-links">
						<?php
						$pagination_args = array(
							'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'total'        => $q->max_num_pages,
							'current'      => max( 1, get_query_var( 'paged' ) ),
							'format'       => '?paged=%#%',
							'show_all'     => false,
							'type'         => 'plain',
							'end_size'     => 2,
							'mid_size'     => 1,
							'prev_next'    => true,
							'prev_text'    => sprintf( '<i></i> %1$s', esc_html_x( '<', 'Previous post', 'stratum' ) ),
							'next_text'    => sprintf( '%1$s <i></i>', esc_html_x( '>', 'Next post', 'stratum' ) ),
							'add_args'     => false,
							'add_fragment' => ''
						);

						$pagination_args = apply_filters( 'stratum/widgets/advanced-posts/pagination_args', $pagination_args );
						echo paginate_links( $pagination_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					</div>
				</nav>
				<?php
			}
		}
		?>
	</div>
<?php
} elseif ( $posts_layout == 'carousel' ) {
	?>
	<div class="<?php echo esc_attr( $class ); ?>" data-slider-options="<?php echo esc_attr( json_encode( $slider_options ) ); ?>">
		<div class="swiper-container">
			<div class="swiper-wrapper">
				<?php
				if ( $q->have_posts() ) {

					while( $q->have_posts() ):
						$q->the_post();

						$post_id = get_the_ID();
						$url = get_the_post_thumbnail_url($post_id, $image_size);

						?>
						<div class="swiper-slide <?php echo esc_attr( $widget_class . '__post' ); ?>">
							<div class="<?php echo esc_attr( $widget_class . '__image' ); ?>" style="background-image: url('<?php echo esc_url($url); ?>');"></div>
							<div class="<?php echo esc_attr( $widget_class . '__slide-content' ); ?>">
								<div class="<?php echo esc_attr( $widget_class . '__slide-wrapper' ); ?>">
									<div class="<?php echo esc_attr( $widget_class . '__slide-container' ); ?>">

										<?php
										if ( !empty( $show_meta ) ) {
											?>
											<div class="<?php echo esc_attr( $widget_class . '__entry-meta' ); ?>">
											<?php
											if ( in_array("date", $show_meta) ) {
												$archive_year  = get_the_time('Y');
												$archive_month = get_the_time('m');
												$archive_day   = get_the_time('d');
												?>
												<span class="<?php echo esc_attr( $widget_class . '__post-date' ); ?>">
													<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
														<a href="<?php echo esc_url( get_day_link( $archive_year, $archive_month, $archive_day) ); ?>">
															<?php echo esc_html( get_the_date( '' ) ); ?>
														</a>
													</time>
												</span>
											<?php
											}

											if ( in_array("author", $show_meta) ) {
												if ( in_array("date", $show_meta) ) {
													?>
													<span class="<?php echo esc_attr( $widget_class . '__meta-fields-divider' ); ?>"><?php echo esc_html($meta_fields_divider); ?></span>
													<?php
												}
												?>
												<div class="<?php echo esc_attr( $widget_class . '__post-author' ); ?>">
													<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
														<?php echo esc_html( get_the_author() ); ?>
													</a>
												</div>
											<?php
											}

											if ( in_array("categories", $show_meta) ) {
												if ( in_array("date", $show_meta) || in_array("author", $show_meta) ) {
												?>
													<span class="<?php echo esc_attr( $widget_class . '__meta-fields-divider' ); ?>"><?php echo esc_html($meta_fields_divider); ?></span>
												<?php
												}
												?>
												<div class="<?php echo esc_attr( $widget_class . '__post-categories' ); ?>">
													<?php echo get_the_category_list(', '); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</div>
											<?php
											}

											if ( in_array("comments", $show_meta) ) {
												if ( in_array("date", $show_meta) || in_array("author", $show_meta) || in_array("categories", $show_meta) ) {
												?>
													<span class="<?php echo esc_attr( $widget_class . '__meta-fields-divider' ); ?>"><?php echo esc_html($meta_fields_divider); ?></span>
												<?php
												}
												?>
												<div class="<?php echo esc_attr( $widget_class . '__post-comments' ); ?>">
													<a href="<?php echo esc_url( get_comments_link() ); ?>">
														<?php
														if ( get_comments_number() ) {
															echo esc_html(
																sprintf(
																	_n( '%d Comment', '%d Comments', get_comments_number(), 'stratum' ),
																	get_comments_number()
																)
															);
														} else {
															echo esc_html__( 'No comments', 'stratum' );
														}
														?>
													</a>
												</div>
											<?php
											}
											?>
											</div>
										<?php
										}

										if ( $show_title == 'yes' ) {
											the_title( '<'.esc_attr($title_typography_html_tag).' class="'.esc_attr( $widget_class . '__post-title' ).'"><a href="'.esc_url(get_permalink()).'">', '</a></'.esc_attr($title_typography_html_tag).'>' );
										}

										if ( $show_content == 'yes' ) {
											?>
											<div class="<?php echo esc_attr( $widget_class . '__post-content' ); ?>">
												<?php
												if ( $show_excerpt == 'yes' ) {

													if ( $excerpt_length ) {
														\Stratum\Excerpt_Helper::get_instance()->setExcerptLength( $excerpt_length );
														add_filter( 'excerpt_length', array( 'Stratum\Excerpt_Helper', 'excerpt_length' ), 999 );
													}

													the_excerpt();

													remove_filter( 'excerpt_length', array( 'Stratum\Excerpt_Helper', 'excerpt_length' ), 999 );

												} else {
													the_content();
												}
												?>
											</div>
										<?php
										}

										if ( $show_read_more == 'yes' ) {
											?>
											<div class="<?php echo esc_attr( $widget_class . '__entry-footer' ); ?>">
												<div class="<?php echo esc_attr( $widget_class . '__read-more' ); ?>">
													<a href="<?php the_permalink() ?>" <?php if ( $open_new_tab == 'yes') { ?> target="_blank" <?php } ?>>
														<?php echo esc_html($read_more_text); ?>
													</a>
												</div>
											</div>
										<?php
										}
										?>
									</div>
								</div>
							</div>
							<div class="<?php echo esc_attr( $widget_class . '__overlay' ); ?>"></div>
						</div>
					<?php
					endwhile;
					wp_reset_postdata();

				} else {
					?><p><?php echo esc_html__( 'Nothing found.', 'stratum' ); ?></p><?php
				}
				?>
				</div>
			<?php

			if ( $navigation == 'both' || $navigation == 'pagination' ) {
				if ($pagination_style == 'scrollbar'){
					?><div class="swiper-scrollbar"></div><?php
				} else {
					?><div class="swiper-pagination"></div><?php
				}
			}
			?>
		</div>
		<?php

		if ( $navigation == 'both' || $navigation == 'arrows' ) {
		?>
			<div class="stratum-swiper-button-prev"></div>
			<div class="stratum-swiper-button-next"></div>
		<?php
		}
		?>
	</div>
<?php
}
