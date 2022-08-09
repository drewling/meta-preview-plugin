

document.addEventListener( 'DOMContentLoaded', function() {

	var data = false;
	var container = null;
	var init_status = document.getElementById( 'original_post_status' ).value;

	var hash = '';
	var doing_request = false;

	var was_saving = false;
	var is_saving;


	var interval = setInterval( function() {
		container = document.getElementById( 'drewl-meta-preview' );
		if ( container ) {
			clearInterval( interval );
			observe_btn();
			update_widgets();
		}
	}, 300 );


	function observe_btn() {
		// MutationObserver IE11+
		if ( window.MutationObserver == undefined )
			return;

		var observer = new MutationObserver( function( mutations, observer ) {
			mutations.forEach( function( record ) {
				// https://github.com/WordPress/gutenberg/issues/17632
				is_saving = wp.data.select( 'core/editor' ).isSavingPost() && ! wp.data.select( 'core/editor' ).isAutosavingPost();

				var is_done_saving = was_saving && !is_saving;
				was_saving = is_saving;

				if ( is_done_saving ) {
					request_data();
					// console.log( 'done saving' );
				}
			} );
		} );

		var publish_btn = document.querySelector( '.editor-post-publish-button__button' );

		observer.observe( publish_btn, {
			attributes: true
		} );
	}


	function request_data_( html ) {
		jQuery.post( drewl_meta_preview.ajax_url + '?action=drewl_meta_preview_get_data&hash=' + hash + '&id=' + drewl_meta_preview.post_id, { html: html } ).done( function( data_ ) {
			data = data_;
			update_widgets();
			doing_request = false;
		} );
	}
	function request_data() {
		if ( doing_request )
			return;

		doing_request = true;
		var status = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'status' );

		if ( ( init_status == 'draft' && !status ) || status == 'draft' ) {
			jQuery.get( drewl_meta_preview.site_url + '/?p=' + drewl_meta_preview.post_id ).done( function ( html ) {
				request_data_( html );
			} );
		} else {
			request_data_( '' );
		}
	}


	function update_widgets() {
		if ( ! container || ! data )
			return;

		container.innerHTML = data;
		hash = container.children[0].getAttribute( 'data-hash' );
		data = false;
	}

	if ( init_status != 'auto-draft' ) {
		request_data();
	}

} );
