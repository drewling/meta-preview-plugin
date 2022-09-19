<?php

defined( 'ABSPATH' ) || exit;

?>

<div id="drewl-meta-preview" class="<?php echo $classes; ?>">
	<div class="drewl-mp-controls">
		<span><i class="control-icon google"></i></span>
		<span><i class="control-icon facebook"></i></span>
		<span><i class="control-icon twitter"></i></span>
		<span><i class="control-icon linkedin"></i></span>
		<span><i class="control-icon pinterest"></i></span>
		<span><i class="control-icon slack"></i></span>
		<span >
			<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.316 1.18a.61.61 0 0 1 0 .874L5.436 7.82a.639.639 0 0 1-.891 0l-3.36-3.294a.61.61 0 0 1 0-.873.639.639 0 0 1 .89 0L4.99 6.509l5.435-5.328a.639.639 0 0 1 .89 0z" fill="#ffffff" stroke="#ffffff"/></svg>
			<?php _e( 'Preview all', 'drewl-meta-preview' ); ?>
		</span>
	</div>
	<div class="drewl-mp-widgets">
		<div class="drewl-info">
		<?php if ( $post->post_status == 'auto-draft' ): ?>
			<?php _e( 'Save or publish the page to see the widgets', 'drewl-meta-preview' ); ?>
		<?php else: ?>
			<?php _e( 'Retrieving data...', 'drewl-meta-preview' ); ?>
		<?php endif; ?>
		</div>
	</div>

	<input type="hidden" name="drewl_mp_options">
</div>
