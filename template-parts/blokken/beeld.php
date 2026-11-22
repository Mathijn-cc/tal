<?php
/**
 * Blok: brede foto.
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $blok['beeld'] ) ) {
	return;
}
$verhouding = in_array( $blok['verhouding'] ?? '', array( '16-8', '21-9', '16-9' ), true ) ? $blok['verhouding'] : '16-8';
$attr       = array( 'sizes' => '100vw' );
if ( ! empty( $blok['alt'] ) ) {
	$attr['alt'] = $blok['alt'];
}
?>
<section <?php echo powerhub_sectie( $blok, 'beeld' ); // phpcs:ignore ?>>
	<div class="rij">
		<?php echo tal_foto( $blok['beeld'], 'breed', 'beeld-foto verhouding-' . $verhouding, $blok, $attr ); // phpcs:ignore ?>
	</div>
</section>
