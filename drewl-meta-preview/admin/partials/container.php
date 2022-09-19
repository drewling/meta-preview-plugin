<?php

defined( 'ABSPATH' ) || exit;

?>

<div id="drewl-meta-preview" class="<?php echo $classes; ?>">
	<div class="drewl-mp-controls">
		<span class="control-icon google">
			<!-- <img src="<?php echo plugin_dir_url( __FILE__ ) ?>../images/google.svg" /> -->
			<!-- <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M15.387 7.226a.153.153 0 0 0-.15-.126H8.655a.156.156 0 0 0-.155.156v2.488c0 .086.07.156.156.156h3.8A4.199 4.199 0 0 1 4.3 8.5a4.2 4.2 0 0 1 4.2-4.2c1.018 0 1.948.365 2.675.968a.16.16 0 0 0 .215-.008l1.762-1.762a.153.153 0 0 0-.005-.223A6.963 6.963 0 0 0 8.5 1.5a7 7 0 1 0 7 7c0-.425-.04-.866-.113-1.274z" fill="#9fa2a6"/></svg> -->
		</span>
		<span class="control-icon facebook">
			<!-- <img src="<?php echo plugin_dir_url( __FILE__ ) ?>../images/facebook.svg" /> -->
			<!-- <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M10.422.5a4.055 4.055 0 0 0-4.055 4.055v2.257h-2.17A.197.197 0 0 0 4 7.01v2.98c0 .11.088.198.197.198h2.17v6.115c0 .109.088.197.197.197h2.981a.197.197 0 0 0 .197-.197v-6.115h2.19c.09 0 .169-.062.19-.15l.746-2.98a.197.197 0 0 0-.191-.246H9.742V4.555a.68.68 0 0 1 .68-.68h2.28a.197.197 0 0 0 .197-.197V.698A.197.197 0 0 0 12.7.5h-2.28z" fill="#9fa2a6"/></svg> -->
		</span>
		<span class="control-icon twitter">
		</span>
		<span class="control-icon linkedin">
		</span>
		<span class="control-icon pinterest">
		</span>
		<span class="control-icon slack">
		</span>
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
