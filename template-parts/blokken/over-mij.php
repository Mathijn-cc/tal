<?php
/**
 * Blok: over mij (foto met verhaal).
 */
defined( 'ABSPATH' ) || exit;

$email = ! empty( $blok['email'] ) ? $blok['email'] : powerhub( 'email' );
?>
<section <?php echo powerhub_sectie( $blok, 'over-mij' ); // phpcs:ignore ?>>
	<div class="rij">
		<?php echo tal_foto( $blok['beeld'] ?? '', 'large', 'overmij-beeld', $blok, array( 'sizes' => '(max-width: 860px) 100vw, 40vw', 'alt' => $blok['naam'] ?? '' ) ); // phpcs:ignore ?>
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
			<?php if ( ! empty( $blok['naam'] ) || $email ) : ?>
				<div class="overmij-voet">
					<?php if ( ! empty( $blok['naam'] ) ) : ?>
						<p class="kop-m"><?php echo esc_html( $blok['naam'] ); ?></p>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a class="lijnlink" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $blok['knop']['url'] ) ) : ?>
			<div class="overmij-knop"><?php echo powerhub_knop( $blok['knop'], 'actie' ); // phpcs:ignore ?></div>
		<?php endif; ?>
	</div>
</section>
