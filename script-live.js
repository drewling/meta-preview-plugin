

var title, image;
var new_title, new_image;
var description = document.querySelector( 'input[name=yoast_wpseo_metadesc]' );

// https://web.archive.org/web/20180324022838/http://demo.nimius.net/debounce_throttle/
wp.data.subscribe( lodash.debounce( function() {
	if ( title == undefined ) {
		title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
	} else {
		new_title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
		if ( new_title != title ) {
			document.querySelector( '.drewl-meta-preview' ).children[1].querySelector( '.drewl-title' ).innerHTML = new_title;
			title = new_title;
		}
	}

	if ( image == undefined ) {
		image = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	} else {
		new_image = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
		if ( new_image != image ) {
			var image_obj = wp.data.select( 'core' ).getMedia( new_image );

			var url = ( image_obj != undefined ? image_obj.source_url : '' );
			document.querySelector( '.drewl-meta-preview' ).children[1].querySelector( 'img' ).src = url;
			image = new_image;
		}
	}
} );

