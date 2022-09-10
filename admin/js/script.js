// /* global wp, drewl_meta_preview */
document.addEventListener( 'DOMContentLoaded', () => {
	let data = false;
	let container = null;
	let init_status = document.getElementById( 'original_post_status' ).value;

	let hash = '';
	let doing_request = false;

	let was_saving = false;
	let is_saving;

	let interval = setInterval( () => {
		container = document.getElementById( 'drewl-meta-preview' );
		if ( container ) {
			clearInterval( interval );

			container.children[0].addEventListener( 'click', handleClidk );

			observeBtn();
			updateWidgets();
		}
	}, 300 );

	let ajax = ( type, url, data, cb ) => {
		let xhr = new XMLHttpRequest();
		xhr.open( type, url );

		xhr.onreadystatechange = function () {
			if( xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200 ) {
				cb( this.responseText );
			}
		}
		xhr.send( data );
	}

	let handleClidk = ( e ) => {
		if ( e.target.nodeName == 'SPAN' ) {
			let index = 1;
			let node = e.target;
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
		}
	}

	let observeBtn = () => {
		// MutationObserver IE11+
		if ( window.MutationObserver == undefined )
			return;

		// eslint-disable-next-line no-unused-vars
		let observer = new MutationObserver((mutations, observer) => {
			// eslint-disable-next-line no-unused-vars
			mutations.forEach( ( record ) => {
				// https://github.com/WordPress/gutenberg/issues/17632
				is_saving = wp.data.select( 'core/editor' ).isSavingPost() && ! wp.data.select( 'core/editor' ).isAutosavingPost();

				let is_done_saving = was_saving && !is_saving;
				was_saving = is_saving;

				if ( is_done_saving ) {
					requestData();
				}
			} );
		} );

		let publish_btn = document.querySelector( '.editor-post-publish-button__button' );

		observer.observe( publish_btn, {
			attributes: true
		} );
	}

	let requestData_ = ( content ) => {
		// WF firewall XSS
		let p = document.createElement( 'p' );
		p.textContent = content;
		// FormData IE10+
		let fd = new FormData();
		fd.append( 'content', p.innerHTML );

		ajax( 'POST', drewl_meta_preview.ajax_url + '?action=drewl_meta_preview_get_data&hash=' + hash + '&id=' + drewl_meta_preview.post_id, fd, ( data_ ) => {
			data = data_;
			updateWidgets();
			doing_request = false;
		} );
	}

	let requestData = () => {
		if ( doing_request )
			return;

		doing_request = true;
		let status = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'status' );

		if ( ( init_status == 'draft' && !status ) || status == 'draft' ) {
			ajax( 'GET', drewl_meta_preview.site_url + '/?p=' + drewl_meta_preview.post_id, '', ( content ) => {
				requestData_( content );
			} );

		} else {
			requestData_( '' );
		}
	}


	let updateWidgets = () => {
		if ( ! container || ! data )
			return;

		container.children[1].innerHTML = data;
		hash = container.children[1].children[0].getAttribute( 'data-hash' );
		data = false;
	}

	if ( init_status != 'auto-draft' ) {
		requestData();
	}
} );

/**
 * Observes changes on 'targetNode' and update the value of 'field'
 * @param object targetNode
 * @param string field
 */
let drewlObserver = ( targetNode, field ) => {

	// Options for the observer (which mutations to observe)
	const config = {
		childList: true,
		characterData: true,
		subtree: true
	};

	// Callback function to execute when mutations are observed
	// eslint-disable-next-line no-unused-vars
	const callback = (mutationList, observer) => {
		// eslint-disable-next-line no-unused-vars
		for ( const mutation of mutationList ) {
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
		}
	};

	// Create an observer instance linked to the callback function
	const observer = new MutationObserver( callback );

	// Start observing the target node for configured mutations
	observer.observe( targetNode, config );
}

/**
 * Changes slug in the meta preview section as the user changes the Yoast slug
 * @param title slug
 */
let drewlChangeSlug = ( slug ) => {
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

window.onload = () => {
    let title = document.querySelector( '#yoast-google-preview-title-metabox' );
	let slug = document.querySelector( '#yoast-google-preview-slug-metabox' );
	let description = document.querySelector( '#yoast-google-preview-description-metabox' );

	// Handles slug changes
	slug.addEventListener( 'keyup', () => {
		drewlChangeSlug( slug.value );
	} );

	// Handles title and description changes
	drewlObserver( title, 'title' );
	drewlObserver( description, 'description' );
};
