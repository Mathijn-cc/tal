<?php
/**
 * Blok: herkenning.
 */
defined( 'ABSPATH' ) || exit;

$vragen = array_filter( wp_list_pluck( (array) ( $blok['vragen'] ?? array() ), 'vraag' ) );
?>
<section <?php echo powerhub_sectie( $blok, 'herkenning' ); // phpcs:ignore ?>>
	<div class="herkenning-podium">
		<div class="rij">
			<?php echo tal_kop( $blok['kop'] ?? '', 'h2', 'herkenning-kop' ); // phpcs:ignore ?>
			<?php if ( $vragen ) : ?>
				<div class="herkenning-kolom">
					<div class="herkenning-vragen">
						<?php foreach ( $vragen as $vraag ) : ?>
							<p class="herkenning-vraag"><?php echo esc_html( $vraag ); ?></p>
						<?php endforeach; ?>
					</div>
					<div class="herkenning-voortgang" aria-hidden="true">
						<div class="herkenning-balk"><i style="transform: scaleX(<?php echo esc_attr( round( 1 / count( $vragen ), 3 ) ); ?>)"></i></div>
						<svg class="herkenning-pijl" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M12 4v15M6 13l6 6 6-6"/></svg>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( ! empty( $blok['slot'] ) || ! empty( $blok['beeld'] ) ) : ?>
		<div class="rij herkenning-slot">
			<?php echo tal_foto( $blok['beeld'] ?? '', 'large', 'herkenning-beeld', $blok ); // phpcs:ignore ?>
			<div class="herkenning-actie">
				<?php if ( ! empty( $blok['slot'] ) ) : ?>
					<p class="kop-m"><?php echo esc_html( $blok['slot'] ); ?></p>
				<?php endif; ?>
				<?php echo powerhub_knop( $blok['knop'] ?? array(), 'actie' ); // phpcs:ignore ?>
			</div>
		</div>
	<?php endif; ?>
</section>
