<?php
/**
 * Blok: resultaten.
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'resultaten' ); // phpcs:ignore ?>>
	<div class="rij resultaten-kop">
		<?php if ( ! empty( $blok['label'] ) ) : ?>
			<p class="marge"><?php echo esc_html( $blok['label'] ); ?></p>
		<?php endif; ?>
		<?php echo tal_kop( $blok['kop'] ?? '', 'h2', 'inhoud' ); // phpcs:ignore ?>
	</div>
	<div class="rij">
		<ul class="uitkomsten">
			<?php foreach ( (array) ( $blok['uitkomsten'] ?? array() ) as $uitkomst ) : ?>
				<?php if ( empty( $uitkomst['tekst'] ) ) { continue; } ?>
				<li class="uitkomst">
					<span class="uitkomst-icoon"><?php echo tal_uitkomst_icoon( $uitkomst['icoon'] ?? 'zon' ); // phpcs:ignore ?></span>
					<p><?php echo esc_html( $uitkomst['tekst'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
