<?php

defined( 'ABSPATH' ) || exit;

?>

<?php foreach ( $names as $name ): ?>
	<div class="drewl-mp-card">
		<div class="drewl-title">
			<span><i class="title-icon <?php echo strtolower($name); ?>"></i></span>
			<?php echo $name; ?>
		</div>

		<div class="drewl-mp-<?php echo strtolower( $name ); ?>">
			<div id="drewl-image-<?php echo strtolower( $name ); ?>" class="drewl-image" style="background-image: url(<?php echo $meta['image']; ?>);"></div>

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
