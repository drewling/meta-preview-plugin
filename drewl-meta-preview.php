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
	}


	public function meta_preview_callback( $post ) {
		$classes = get_option( 'drewl_mp_options', 'dmp1 dmp2 dmp3 dmp4 dmp5 dmp6' );
		include_once plugin_dir_path( __FILE__ ) . '/admin/partials/container.php';
	}

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

	private function render ( $meta ) {
		$names = array( 'Google', 'Facebook', 'Twitter', 'LinkedIn', 'Pinterest', 'Slack' );
		ob_start();
		?>

		<?php foreach ( $names as $name ): ?>
			<div class="drewl-mp-card">
				<?php echo $name; ?>
				<div class="drewl-mp-<?php echo strtolower( $name ); ?>">
					<div class="drewl-image" style="background-image: url(<?php echo $meta['image']; ?>);"></div>
					<div class="drewl-inner">
						<span class="drewl-u">
							<?php echo $this->format_url( $meta, $name ); ?>
						</span>
						<div class="drewl-t"><?php echo $meta['title']; ?></div>
						<div class="drewl-d"><?php echo $meta['description']; ?></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php
		return ob_get_clean();
	}

	private function no_meta_tags() {
		die( '<div class="drewl-info">' . __( 'No meta-tags found. Install any SEO plugins or specify them manually in your theme\'s header.php file.', 'drewl-meta-preview' ) . '</div>' );
	}

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

		// if ( substr( $body, 0, 1 ) == '{' ) {
		// 	$json = json_decode( $body );
		// 	if ( $json && ! empty( $json->yoast_head ) ) {
		// 		$head = '<head>' . $json->yoast_head . '</head>';
		// 	} else {
		// 		$this->no_meta_tags();
		// 	}
		// } else {
		// }
		$start = strpos( $body, '<head>' );
		$end = strpos( $body, '</head>' );
		$head = substr( $body, $start, $end - $start + 7 );

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
			// title and meta-description?

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
}

new DrewlMetaPreviewPlugin();



?>