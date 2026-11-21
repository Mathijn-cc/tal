<?php
/**
 * Blok: programma (kaarten op een rij).
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'programma' ); // phpcs:ignore ?>>
	<div class="rij programma-kop">
		<?php if ( ! empty( $blok['label'] ) ) : ?>
			<p class="marge"><?php echo esc_html( $blok['label'] ); ?></p>
		<?php endif; ?>
		<?php echo tal_kop( $blok['kop'] ?? '', 'h2', 'inhoud' ); // phpcs:ignore ?>
	</div>
	<div class="programma-kader">
		<ul class="programma-spoor">
			<?php foreach ( (array) ( $blok['kaarten'] ?? array() ) as $kaart ) : ?>
				<li class="programma-kaart">
					<?php echo tal_foto( $kaart['beeld'] ?? '', 'large', 'programma-beeld', 38, array( 'sizes' => '(max-width: 860px) 76vw, 34vw' ) ); // phpcs:ignore ?>
					<?php if ( ! empty( $kaart['tekst'] ) ) : ?>
						<p class="lead"><?php echo esc_html( $kaart['tekst'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
