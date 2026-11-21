<?php
/**
 * Blok: vier delen (lagen).
 */
defined( 'ABSPATH' ) || exit;

$lagen = array_values( array_filter( (array) ( $blok['lagen'] ?? array() ), fn( $laag ) => ! empty( $laag['naam'] ) ) );
?>
<section <?php echo powerhub_sectie( $blok, 'vier-delen' ); // phpcs:ignore ?>>
	<div class="rij lagen-kop">
		<?php echo tal_kop( $blok['kop'] ?? '', 'h2' ); // phpcs:ignore ?>
		<?php if ( ! empty( $blok['intro'] ) ) : ?>
			<p class="lagen-intro"><?php echo esc_html( $blok['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $lagen ) : ?>
		<dl class="lagen">
			<?php foreach ( $lagen as $i => $laag ) : ?>
				<div class="laag<?php echo count( $lagen ) - 1 === $i ? ' is-donker' : ''; ?>" style="--laag: <?php echo (int) $i; ?>">
					<dt class="laag-naam"><?php echo esc_html( $laag['naam'] ); ?></dt>
					<dd class="laag-vraag"><?php echo esc_html( $laag['vraag'] ?? '' ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>
</section>
