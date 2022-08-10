
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

			container.children[0].addEventListener( 'click', handle_click );

			observe_btn();
			update_widgets();
		}
	}, 300 );

	// function ajax( type, url, data, cb ) {
	// 	var xhr = new XMLHttpRequest();
	// 	xhr.open( type, url );

	// 	xhr.onreadystatechange = function() {
	// 		if( xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200 ) {
	// 			cb( this.responseText );
	// 		}
	// 	}
	// 	xhr.send( data );
	// }

	function handle_click( e ) {
		if ( e.target.nodeName == 'SPAN' ) {
			var index = 1;
			var node = e.target;
			while ( ( node = node.previousElementSibling ) ) {
				index++;
			}

			if ( index == 7 ) {
				container.className = 'dmp1 dmp2 dmp3 dmp4 dmp5 dmp6';
			} else {
				container.classList.toggle( 'dmp' + index );
			}
			container.querySelector('input[name=drewl_mp_options]').value = container.className;
		}
	}

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
				}
			} );
		} );

		var publish_btn = document.querySelector( '.editor-post-publish-button__button' );

		observer.observe( publish_btn, {
			attributes: true
		} );
	}


	function request_data_( content ) {
		// WF firewall XSS
		var p = document.createElement( 'p' );
		p.textContent = content;
		// FormData IE10+
		// var fd = new FormData();
		// fd.append( 'content', p.innerHTML );

		// ajax( 'POST', drewl_meta_preview.ajax_url + '?action=drewl_meta_preview_get_data&hash=' + hash + '&id=' + drewl_meta_preview.post_id, fd, function( data_ ) {
		// 	data = data_;
		// 	update_widgets();
		// 	doing_request = false;
		// } );

		jQuery.post( drewl_meta_preview.ajax_url + '?action=drewl_meta_preview_get_data&hash=' + hash + '&id=' + drewl_meta_preview.post_id, { content: p.innerHTML } ).done( function( data_ ) {
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
			// ajax( 'GET', drewl_meta_preview.site_url + '/?p=' + drewl_meta_preview.post_id, '', function( content ) {
			// 	request_data_( content );
			// } );
			// 
			jQuery.get( 'http://localhost/meta-preview/wp-content/plugins/wordpress-serp-preview-plugin/response.json' ).done( function ( content ) {
			// jQuery.get( drewl_meta_preview.site_url + '/?p=' + drewl_meta_preview.post_id ).done( function ( content ) {
				// console.log(content);
				// return;
				request_data_( content.yoast_head != undefined ? '<head>' + content.yoast_head + '</head>' : content );
			} );
		} else {
			request_data_( '' );
		}
	}


	function update_widgets() {
		if ( ! container || ! data )
			return;

		container.children[1].innerHTML = data;
		hash = container.children[1].children[0].getAttribute( 'data-hash' );
		data = false;
	}

	if ( init_status != 'auto-draft' ) {
		request_data();
	}

} );
