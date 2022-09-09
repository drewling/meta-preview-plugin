<?php
/**
 * Plugin Name: Meta Preview
 * Plugin URI: https://wordpress.org/plugins/drewl-meta-preview/
 * Description: OpenGraph meta-tags preview plugin
 * Version: 1.0.0
 * Author: drewl.com
 * Author URI: https://drewl.com/
 * License: GPL2+
 * Text Domain: drewl-meta-preview
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;



class DrewlMetaPreviewPlugin {
	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		add_action( 'init', function() {
			load_plugin_textdomain( 'drewl-meta-preview', false,
				plugin_dir_path( __FILE__ ) . '/languages' );

			// allows access to drafts via REST API
			if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
				global $wp_post_statuses;
				$wp_post_statuses['draft']->public = true;
			}
		} );

		add_action( 'add_meta_boxes', function() {
			add_meta_box( 'drewl_meta_preview', __( 'Meta Preview', 'drewl-meta-preview' ),
				array( $this, 'meta_preview_callback' ), array( 'page', 'post' ), 'normal' );
		} );

		add_action( 'admin_enqueue_scripts', function( $hook ) {
			global $post_type, $post;

			if( in_array( $hook, array( 'post.php', 'post-new.php' ) ) && in_array( $post_type, array( 'page', 'post' ) ) ) {

				wp_enqueue_style( 'drewl-meta-preview', plugin_dir_url( __FILE__ ) . 'admin/css/style.css',
					array(),
					filemtime( plugin_dir_path( __FILE__ ) . '/admin/css/style.css' )
				);

				wp_enqueue_script( 'drewl-meta-preview', plugin_dir_url( __FILE__ ) . 'admin/js/script.js',
					array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components'/*, 'wp-editor'*/ ),
					filemtime( plugin_dir_path( __FILE__ ) . '/admin/js/script.js' ), true
				);
				wp_localize_script( 'drewl-meta-preview', 'drewl_meta_preview',
					array( 
						'ajax_url'	=> admin_url( 'admin-ajax.php' ),
						'post_id'	=> $post->ID,
						'site_url'	=> get_site_url(),
					)
				);
			}
		} );

		add_action( 'wp_ajax_drewl_meta_preview_get_data', array( $this, 'get_data' ) );

		add_action( 'save_post', function( $post_id ) {
			if ( empty( $_POST['drewl_mp_options'] ) || ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			update_option( 'drewl_mp_options', htmlentities( $_POST['drewl_mp_options'] ) );
		} );

		add_filter('script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2);

		add_action( 'wp_head', array( $this, 'handle_open_graph_tags' ) );
	}

	/**
	 * Metabox rendering function
	 */
	public function meta_preview_callback( $post ) {
		$classes = get_option( 'drewl_mp_options', 'dmp1' );
		include_once plugin_dir_path( __FILE__ ) . '/admin/partials/container.php';
	}

	/**
	 * Format URLs based on social network
	 */
	private function format_url( $meta, $name ) {
		$url = parse_url( $meta['url'] );
		$out = '';

		if ( $name == 'Google' ) {
			$out = $url['scheme'] . '://' . $url['host'] ;
			if ( $url['path'] ) {
				$out .= '<span>' . str_replace( '/', ' › ', rtrim( $url['path'], '/' ) ) . '</span>';
			}
		}

		if ( in_array( $name, array( 'Twitter', 'Facebook', 'LinkedIn', 'Pinterest' ) ) ) {
			$out = $url['host'];
		}

		if ( $name == 'Slack' ) {
			$out = ( $meta['name'] ? $meta['name'] : $url['scheme'] . '://' . $url['host'] );
			$out = '<span class="drewl-icon" style="background-image: url(' . $meta['icon'] . ');"></span>' . $out;
		}

		return $out;
	}

	/**
	 * Widgets rendering function
	 */
	private function render ( $meta ) {
		$names = array( 'Google', 'Facebook', 'Twitter', 'LinkedIn', 'Pinterest', 'Slack' );
		ob_start();
		include_once plugin_dir_path( __FILE__ ) . '/admin/partials/item.php';
		return ob_get_clean();
	}

	/**
	 * Couldn't find any meta-tags function
	 */
	private function no_meta_tags() {
		die( '<div class="drewl-info">' . __( 'No meta-tags found. Install any SEO plugins or specify them manually in your theme\'s header.php file.', 'drewl-meta-preview' ) . '</div>' );
	}

	/**
	 * Handle ajax request
	 */
	public function get_data() {
		$url = get_permalink( $_GET['id'] );

		$body = '';

		if ( ! empty( $_POST['content'] ) ) {
			// draft posts can't be acccessed via wp_remote_get
			$body = stripslashes( $_POST['content'] );
		} else {
			$response = wp_remote_get( $url, array(
				'timeout' => 10,
			) );

			if ( ! is_array( $response ) || is_wp_error( $response ) ) {
				die( '<div class="drewl-info" style="color: #cc1818;">' . __( 'Connection problem', 'drewl-meta-preview' ) . '</div>' );
			}

			$body = $response['body'];
		}

		if ( empty( $body ) ) {
			$this->no_meta_tags();
		}

		$body = html_entity_decode( $body );
		$head = '';

		if ( substr( $body, 0, 1 ) == '{' ) {
			$json = json_decode( $body );
			if ( $json && ! empty( $json->yoast_head ) ) {
				$head = '<head>' . $json->yoast_head . '</head>';
			} else {
				$this->no_meta_tags();
			}
		} else {
			$start = strpos( $body, '<head>' );
			$end = strpos( $body, '</head>' );
			$head = substr( $body, $start, $end - $start + 7 );
		}

		$dom = new DOMDocument();
		$dom->loadHtml( $head );

		$meta = array( 'title' => '', 'description' => '', 'image' => '', 'url' => '', 'icon' => '', 'name' => '' );

		foreach ( $dom->getElementsByTagName( '*' ) as $item ) {
			if ( ( $property = $item->getAttribute( 'property' ) ) && ( $content = $item->getAttribute( 'content' ) ) ) {

				if ( $property == 'og:title' )
					$meta['title'] = $item->getAttribute( 'content' );
				if ( $property == 'og:description' )
					$meta['description'] = $item->getAttribute( 'content' );
				if ( $property == 'og:image' )
					$meta['image'] = $item->getAttribute( 'content' );
				if ( $property == 'og:url' )
					$meta['url'] = $item->getAttribute( 'content' );
				if ( $property == 'og:site_name' )
					$meta['name'] = $item->getAttribute( 'content' );
			}

			if ( ( $rel = $item->getAttribute( 'rel' ) ) && $rel == 'icon' ) {
				$meta['icon'] = $item->getAttribute( 'href' );
			}
		}

		if ( empty( $meta['url'] ) || empty( $meta['title'] ) ) {
			$this->no_meta_tags();
		}

		$out = $this->render( $meta );

		$hash = md5( $out );
		if ( $hash == $_GET['hash'] ) {
			header( 'HTTP/1.1 304 Not Modified' );
		} else {
			echo '<div data-hash="' . $hash . '">' . $out . '</div>';
		}

		exit;
	}

	public function add_defer_attribute($tag, $handle) {
		if ( 'drewl-meta-preview' !== $handle )
		  return $tag;
		return str_replace( ' src', ' defer="defer" src', $tag );
	}

	/**
	 * Fallback if Yoast is not installed
	 */
	public function handle_open_graph_tags() {
		global $post;

		$meta_description = ( $post->post_excerpt != '' ) ? $post->post_excerpt : wp_trim_words( $post->post_content, 10 );

		if ( ! defined( 'WPSEO_VERSION' ) ) {
			echo '<!-- og tags -->' . PHP_EOL;
			echo '<meta property="og:url"                content="' . get_permalink( $post->ID ) . '" />' . PHP_EOL;
			echo '<meta property="og:type"               content="' . $post->post_type . '" />' . PHP_EOL;
			echo '<meta property="og:title"              content="' . $post->post_title . '" />' . PHP_EOL;
			echo '<meta property="og:description"        content="' . $meta_description . '" />' . PHP_EOL;
			echo '<meta property="og:image"              content="' . get_the_post_thumbnail_url( $post->ID, 'large' ) . '" />' . PHP_EOL;
			echo '<meta property="og:site_name"          content="' . get_bloginfo( 'name' ) . '" />' . PHP_EOL;
			echo '<!-- og tags -->' . PHP_EOL;
		}
	}
}

new DrewlMetaPreviewPlugin();



?>