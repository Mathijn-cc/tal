<?php
/**
 * Blok: citaat.
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $blok['citaat'] ) ) {
	return;
}
?>
<section <?php echo powerhub_sectie( $blok, 'citaat' ); // phpcs:ignore ?>>
	<figure class="rij citaat">
		<blockquote><?php echo esc_html( $blok['citaat'] ); ?></blockquote>
		<?php if ( ! empty( $blok['naam'] ) ) : ?>
			<figcaption><?php echo esc_html( $blok['naam'] ); ?></figcaption>
		<?php endif; ?>
	</figure>
</section>
