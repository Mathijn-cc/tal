<?php
/**
 * Blok: academy (brede foto met tekst).
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'academy' ); // phpcs:ignore ?>>
	<?php if ( ! empty( $blok['beeld'] ) ) : ?>
		<div class="rij">
			<?php echo tal_foto( $blok['beeld'], 'breed', 'academy-beeld', $blok, array( 'sizes' => '100vw' ) ); // phpcs:ignore ?>
		</div>
	<?php endif; ?>
	<div class="rij academy-tekst">
		<?php if ( ! empty( $blok['label'] ) ) : ?>
			<p class="marge"><?php echo esc_html( $blok['label'] ); ?></p>
		<?php endif; ?>
		<div class="inhoud academy-kolommen">
			<div class="academy-titel">
				<?php echo tal_kop( $blok['kop'] ?? '', 'h2' ); // phpcs:ignore ?>
				<?php if ( ! empty( $blok['ondertitel'] ) ) : ?>
					<p class="kop-m academy-ondertitel"><?php echo esc_html( $blok['ondertitel'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $blok['tekst'] ) ) : ?>
				<p class="lead"><?php echo esc_html( $blok['tekst'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
