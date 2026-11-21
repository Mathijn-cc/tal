<?php
/**
 * Blok: over mij (foto met verhaal).
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'over-mij' ); // phpcs:ignore ?>>
	<div class="rij">
		<?php echo tal_foto( $blok['beeld'] ?? '', 'large', 'overmij-beeld', $blok['beeld_positie'] ?? 30, array( 'sizes' => '(max-width: 860px) 100vw, 40vw', 'alt' => $blok['naam'] ?? '' ) ); // phpcs:ignore ?>
		<div class="overmij-tekst">
			<?php if ( ! empty( $blok['label'] ) ) : ?>
				<p class="bovenkop-tal"><?php echo esc_html( $blok['label'] ); ?></p>
			<?php endif; ?>
			<?php echo tal_kop( $blok['kop'] ?? '', 'h2' ); // phpcs:ignore ?>
			<div class="overmij-verhaal">
				<?php if ( ! empty( $blok['lead'] ) ) : ?>
					<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
				<?php endif; ?>
				<?php echo wp_kses_post( $blok['tekst'] ?? '' ); ?>
			</div>
			<?php if ( ! empty( $blok['naam'] ) || ! empty( $blok['knop']['url'] ) ) : ?>
				<div class="overmij-voet">
					<?php if ( ! empty( $blok['naam'] ) ) : ?>
						<p class="kop-m"><?php echo esc_html( $blok['naam'] ); ?></p>
					<?php endif; ?>
					<?php echo powerhub_knop( $blok['knop'] ?? array(), 'actie' ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
