
document.addEventListener( 'DOMContentLoaded', function() {

	var data = false;
	var container = null;
	var init_status = document.getElementById( 'original_post_status' ).value;

	var hash = '';
	var doing_request = false;

	var was_saving = false;
	var is_saving;

	// var yoast_title = document.getElementById('replacement-variable-editor-field-6');

	// var yoast_event = new Event('input');
	
	// yoast_title.addEventListener('input', function () { 
	// 	console.log(yoast_title.value);
	// });

	// yoast_title.dispatchEvent(yoast_event);
	
	// var yoast_title = yoast_title_container.querySelector('[data-text="true"]').value;
	// console.log('Title here: ' + yoast_title.innerHTML);
	
	var interval = setInterval( function() {
		container = document.getElementById( 'drewl-meta-preview' );
		if ( container ) {
			clearInterval( interval );

			container.children[0].addEventListener( 'click', handle_click );

			observe_btn();
			update_widgets();
		}
	}, 300 );

	function ajax( type, url, data, cb ) {
		var xhr = new XMLHttpRequest();
		xhr.open( type, url );

		xhr.onreadystatechange = function() {
			if( xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200 ) {
				cb( this.responseText );
			}
		}
		xhr.send( data );
	}

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
				if ( container.classList.length == 1 && container.classList.contains( 'dmp' + index ) )
					return;
				// toggle.toggle IE10+
				container.classList.toggle( 'dmp' + index );
			}
			container.querySelector('input[name=drewl_mp_options]').value = container.className;
			var titles = document.getElementsByClassName("drewl-t"); 
			for(let title of titles){
				title.firstChild.nodeValue = "Change Text";
			}
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
		var fd = new FormData();
		fd.append( 'content', p.innerHTML );

		ajax( 'POST', drewl_meta_preview.ajax_url + '?action=drewl_meta_preview_get_data&hash=' + hash + '&id=' + drewl_meta_preview.post_id, fd, function( data_ ) {
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
			// http://localhost/meta-preview/wp-content/plugins/wordpress-serp-preview-plugin/response.json
			ajax( 'GET', drewl_meta_preview.site_url + '/?p=' + drewl_meta_preview.post_id, '', function( content ) {
				request_data_( content );
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

function drewl_observer( targetNode, field ) {
	
	// Options for the observer (which mutations to observe)
	const config = { 
		childList: true, 
		characterData: true, 
		subtree: true 
	};

	// Callback function to execute when mutations are observed
	const callback = ( mutationList, observer ) => {
		for ( const mutation of mutationList ) {
			// if (mutation.type === 'childList') {
				if ( field === 'title' ) {
					let drewl_titles = document.getElementsByClassName( 'drewl-t' );
					if (
						document.querySelector( '#yoast-google-preview-title-metabox span[data-text="true"]' ) !== null 
						&& document.querySelector( '#yoast-google-preview-title-metabox span[data-text="true"]' ) !== undefined 
					) {
						for ( let i = 0; i < drewl_titles.length; i++ ) {
							drewl_titles[i].innerHTML = document.querySelector( '#yoast-google-preview-title-metabox span[data-text="true"]' ).innerHTML;
						}
					}
				}
				if ( field === 'description' ) {
					let drewl_descriptions = document.getElementsByClassName( 'drewl-d' );
					if (
						document.querySelector( '#yoast-google-preview-description-metabox span[data-text="true"]' ) !== null 
						&& document.querySelector( '#yoast-google-preview-description-metabox span[data-text="true"]' ) !== undefined 
					) {
						for ( let i = 0; i < drewl_descriptions.length; i++ ) {
							drewl_descriptions[i].innerHTML = document.querySelector( '#yoast-google-preview-description-metabox span[data-text="true"]' ).innerHTML;
						}
					}
				}
			// }
		}
	};

	// Create an observer instance linked to the callback function
	const observer = new MutationObserver( callback );

	// Start observing the target node for configured mutations
	observer.observe( targetNode, config );
}

function drewl_change_slug( slug ) {
	let drewl_slugs = document.getElementsByClassName( 'drewl-u' );
	
	for ( let i = 0; i < drewl_slugs.length; i++ ) {
		let slug_obj = drewl_slugs[i];
		if ( slug_obj.closest( '.drewl-mp-google' ) !== null ) { // targets only google preview
			let slug_str = slug_obj.querySelector( 'span' ).innerHTML;
			let reg_search = /[a-z-0-9]*$/;
			let res = slug_str.replace( reg_search, slug );
			slug_obj.querySelector( 'span' ).innerHTML = res;
		}
	}
}

window.onload = function() { 
    let title = document.querySelector( '#yoast-google-preview-title-metabox' );
	let slug = document.querySelector( '#yoast-google-preview-slug-metabox' );
	let description = document.querySelector( '#yoast-google-preview-description-metabox' );

	// Handles slug changes
	slug.addEventListener( 'keyup', () => {
		drewl_change_slug( slug.value );
	} );

	// Handles title and description changes
	drewl_observer( title, 'title' );
	drewl_observer( description, 'description' );
};