<?php

defined( 'ABSPATH' ) || exit;

$allowed_html = array(
    'span' => array(),
);
?>

<?php foreach ( $names as $name ): ?>
	<div class="drewl-mp-card">
		<div class="drewl-title">
			<span><i class="title-icon <?php echo strtolower( esc_attr( $name ) ); ?>"></i></span>
			<?php echo esc_html( $name ); ?>
		</div>

		<div class="drewl-mp-<?php echo strtolower( esc_attr( $name ) ); ?>">
			<div id="drewl-image-<?php echo strtolower( esc_attr( $name ) ); ?>" class="drewl-image" style="background-image: url(<?php echo esc_url( $meta['image'] ); ?>);"></div>

			<div class="drewl-inner">
				<span class="drewl-u">
					<?php
					$url = $this->format_url( $meta, $name );
					echo wp_kses( $url, $allowed_html );
					?>
				</span>
				<div class="drewl-t"><?php echo esc_html( $meta['title'] ); ?></div>
				<div class="drewl-d"><?php echo esc_html( $meta['description'] ); ?></div>
			</div>
		</div>
	</div>
<?php endforeach; ?>
