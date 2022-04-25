<?php

defined( 'ABSPATH' ) || die();

?>

<script type="text/template" id="tmpl-wunderwp-template-library-header-preview">
	<div id="elementor-template-library-header-preview-insert-wrapper" class="elementor-templates-modal__header__item">
		{{{ wunderwp.editor.templates.layout.getTemplateActionButton( obj ) }}}
	</div>
</script>

<script type="text/template" id="tmpl-wunderwp-template-library-template-remote">
	<div class="elementor-template-library-template-body">
		<# if ( 'page' === type ) { #>
			<div class="elementor-template-library-template-screenshot" style="background-image: url({{ thumbnail }});"></div>
		<# } else { #>
			<img src="{{ thumbnail }}">
		<# } #>
		<div class="elementor-template-library-template-preview">
			<i class="eicon-zoom-in" aria-hidden="true"></i>
		</div>
	</div>
	<div class="elementor-template-library-template-footer">
		{{{ wunderwp.editor.templates.layout.getTemplateActionButton( obj ) }}}
		<div class="elementor-template-library-template-name">{{{ title }}} - {{{ type }}}</div>
		<!-- <div class="elementor-template-library-favorite">
			<input id="elementor-template-library-template-{{ template_id }}-favorite-input" class="elementor-template-library-template-favorite-input" type="checkbox"{{ favorite ? " checked" : "" }}>
			<label for="elementor-template-library-template-{{ template_id }}-favorite-input" class="elementor-template-library-template-favorite-label">
				<i class="eicon-heart-o" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo __( 'Favorite', 'wunderwp' ); ?></span>
			</label>
		</div> -->
	</div>
</script>

<script type="text/template" id="tmpl-wunderwp-template-library-templates">
	<#
		var activeSource = wunderwp.editor.templates.getFilter('source');
	#>
	<div id="elementor-template-library-toolbar">
		<# if ( 'pre-made' === activeSource ) {
			var activeType = wunderwp.editor.templates.getFilter('type');
			var config = wunderwp.editor.templates.getConfig( activeType );
			var premade = config['pre-made'] || {}
			var categories = premade.categories || false
			#>
			<div id="elementor-template-library-filter-toolbar-remote" class="elementor-template-library-filter-toolbar">
				<div id="elementor-template-library-order">
					<input type="radio" id="elementor-template-library-order-all" class="elementor-template-library-order-input" name="elementor-template-library-order" value="" checked>
					<label for="elementor-template-library-order-all" class="elementor-template-library-order-label" title="<?php echo __( 'Show all the templates', 'wunderwp' ); ?>"><?php echo __( 'All', 'wunderwp' ); ?></label>
					<input type="radio" id="elementor-template-library-order-elementor" class="elementor-template-library-order-input" name="elementor-template-library-order" value="elementor">
					<label for="elementor-template-library-order-elementor" class="elementor-template-library-order-label" title="<?php echo __( "Show the templates in which only 'Elementor' elements are used.", 'wunderwp' ); ?>"><?php echo __( 'Elementor', 'wunderwp' ); ?></label>
					<input type="radio" id="elementor-template-library-order-elementor-pro" class="elementor-template-library-order-input" name="elementor-template-library-order" value="elementor,elementor-pro">
					<label for="elementor-template-library-order-elementor-pro" class="elementor-template-library-order-label" title="<?php echo __( "Show the templates in which 'Elementor Pro' elements are used.", 'wunderwp' ); ?>"><?php echo __( 'Elementor Pro', 'wunderwp' ); ?></label>
				</div>
				<# if ( categories ) { #>
					<div id="elementor-template-library-filter">
						<select id="elementor-template-library-filter-subtype" class="elementor-template-library-filter-select" data-elementor-filter="subtype">
							<option></option>
							<# categories.forEach( function( category ) {
								var selected = category === wunderwp.editor.templates.getFilter( 'subtype' ) ? ' selected' : '';
								#>
								<option value="{{ category }}"{{{ selected }}}>{{{ category }}}</option>
							<# } ); #>
						</select>
					</div>
				<# } #>
			</div>
		<# } else { #>
			<div id="elementor-template-library-filter-toolbar-local" class="elementor-template-library-filter-toolbar"></div>
		<# } #>
		<div id="elementor-template-library-filter-text-wrapper">
			<label for="elementor-template-library-filter-text" class="elementor-screen-only"><?php echo __( 'Search Templates:', 'wunderwp' ); ?></label>
			<input id="elementor-template-library-filter-text" placeholder="<?php echo esc_attr__( 'Search', 'wunderwp' ); ?>">
			<i class="eicon-search"></i>
		</div>
	</div>
	<# if ( 'custom' === activeSource ) { #>
		<div id="elementor-template-library-order-toolbar-local">
			<div class="elementor-template-library-local-column-1">
				<input type="radio" id="elementor-template-library-order-local-title" class="elementor-template-library-order-input" name="elementor-template-library-order-local" value="title" data-default-ordering-direction="asc">
				<label for="elementor-template-library-order-local-title" class="elementor-template-library-order-label"><?php echo __( 'Name', 'wunderwp' ); ?></label>
			</div>
			<div class="elementor-template-library-local-column-2">
				<input type="radio" id="elementor-template-library-order-local-type" class="elementor-template-library-order-input" name="elementor-template-library-order-local" value="type" data-default-ordering-direction="asc">
				<label for="elementor-template-library-order-local-type" class="elementor-template-library-order-label"><?php echo __( 'Type', 'wunderwp' ); ?></label>
			</div>
			<div class="elementor-template-library-local-column-3">
				<input type="radio" id="elementor-template-library-order-local-author" class="elementor-template-library-order-input" name="elementor-template-library-order-local" value="author" data-default-ordering-direction="asc">
				<label for="elementor-template-library-order-local-author" class="elementor-template-library-order-label"><?php echo __( 'Created By', 'wunderwp' ); ?></label>
			</div>
			<div class="elementor-template-library-local-column-4">
				<input type="radio" id="elementor-template-library-order-local-date" class="elementor-template-library-order-input" name="elementor-template-library-order-local" value="date">
				<label for="elementor-template-library-order-local-date" class="elementor-template-library-order-label"><?php echo __( 'Creation Date', 'wunderwp' ); ?></label>
			</div>
			<div class="elementor-template-library-local-column-5">
				<div class="elementor-template-library-order-label"><?php echo __( 'Actions', 'wunderwp' ); ?></div>
			</div>
		</div>
	<# } #>
	<div id="elementor-template-library-templates-container"></div>
	<# if ( 'pre-made' === activeSource ) { #>
		<div id="elementor-template-library-footer-banner">
			<i class="eicon-nerd" aria-hidden="true"></i>
			<div class="elementor-excerpt"><?php echo __( 'Stay tuned! More awesome templates coming real soon.', 'wunderwp' ); ?></div>
		</div>
	<# } #>
</script>
