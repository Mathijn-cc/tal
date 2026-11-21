<?php
/**
 * Blok Tekst. $blok komt uit powerhub_blok().
 */
defined( 'ABSPATH' ) || exit;

$indeling = in_array( $blok['indeling'] ?? '', array( 'gesplitst', 'kolommen' ), true ) ? $blok['indeling'] : 'smal';
$donker   = false !== strpos( powerhub_sectie( $blok, 'tekst' ), 'is-donker' );
?>
<section <?php echo powerhub_sectie( $blok, 'tekst', 'tekst-' . $indeling ); // phpcs:ignore ?>>
	<div class="binnen tekst-raster">
		<div class="tekst-kopdeel">
			<?php if ( ! empty( $blok['bovenkop'] ) ) : ?>
				<p class="bovenkop"><?php echo esc_html( $blok['bovenkop'] ); ?></p>
			<?php endif; ?>
			<?php echo powerhub_kop( $blok['kop'] ?? '', 0 === $blok['_index'] ? 'h1' : 'h2' ); // phpcs:ignore ?>
		</div>
		<div class="tekst-inhoud">
			<?php if ( ! empty( $blok['tekst'] ) ) : ?>
				<div class="opmaak"><?php echo wp_kses_post( $blok['tekst'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $blok['knop']['url'] ) || ! empty( $blok['knop_2']['url'] ) ) : ?>
				<div class="knoppen">
					<?php
					echo powerhub_knop( $blok['knop'] ?? array(), 'actie' ); // phpcs:ignore
					echo powerhub_knop( $blok['knop_2'] ?? array(), $donker ? 'op-donker' : 'secundair' ); // phpcs:ignore
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
