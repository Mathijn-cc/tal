<?php
/**
 * Blok: traject (claim).
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'traject' ); // phpcs:ignore ?>>
	<div class="rij traject-rij">
		<?php if ( ! empty( $blok['label'] ) ) : ?>
			<p class="marge"><?php echo esc_html( $blok['label'] ); ?></p>
		<?php endif; ?>
		<div class="inhoud">
			<?php echo tal_kop( $blok['kop'] ?? '', 'h2', ! empty( $blok['kop_roze'] ) ? 'is-roze' : '' ); // phpcs:ignore ?>
		</div>
		<?php if ( ! empty( $blok['claim'] ) ) : ?>
			<p class="inhoud traject-claim"><?php echo tal_regel( $blok['claim'] ); // phpcs:ignore ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $blok['lead'] ) || ! empty( $blok['tekst'] ) ) : ?>
			<div class="inhoud traject-tekst">
				<?php if ( ! empty( $blok['lead'] ) ) : ?>
					<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
				<?php endif; ?>
				<div class="opmaak">
					<?php echo wp_kses_post( $blok['tekst'] ?? '' ); ?>
					<?php if ( ! empty( $blok['slotlink']['url'] ) ) : ?>
						<p class="traject-slot"><?php echo tal_lijnlink( $blok['slotlink'], 'lijnlink-groot' ); // phpcs:ignore ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
