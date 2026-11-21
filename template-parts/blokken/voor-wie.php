<?php
/**
 * Blok: voor wie (checklist).
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'voor-wie' ); // phpcs:ignore ?>>
	<div class="rij">
		<div class="voorwie-kop">
			<?php if ( ! empty( $blok['label'] ) ) : ?>
				<p class="bovenkop-tal"><?php echo esc_html( $blok['label'] ); ?></p>
			<?php endif; ?>
			<?php echo tal_kop( $blok['kop'] ?? '', 'h2' ); // phpcs:ignore ?>
			<?php if ( ! empty( $blok['lead'] ) ) : ?>
				<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $blok['knop']['url'] ) ) : ?>
				<div class="voorwie-knop"><?php echo powerhub_knop( $blok['knop'], 'actie' ); // phpcs:ignore ?></div>
			<?php endif; ?>
		</div>
		<ul class="checklist">
			<?php foreach ( (array) ( $blok['punten'] ?? array() ) as $punt ) : ?>
				<?php if ( empty( $punt['tekst'] ) ) { continue; } ?>
				<li>
					<span class="vink" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
					<span><?php echo esc_html( $punt['tekst'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
