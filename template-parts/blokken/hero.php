<?php
/**
 * Blok: hero. $blok komt uit powerhub_blokken() of uit de voorbeelden van het designsysteem.
 */
defined( 'ABSPATH' ) || exit;

$tag  = 0 === ( $blok['_index'] ?? 0 ) ? 'h1' : 'h2';
$kop  = tal_kop( $blok['kop'] ?? '', $tag, 'kop-hero' );
$mini = '';
if ( ! empty( $blok['kop_beeld'] ) ) {
	$mini = '<span class="hero-mini foto" aria-hidden="true">' . powerhub_beeld( $blok['kop_beeld'], 'medium_large', array( 'class' => 'foto-beeld', 'alt' => '', 'loading' => 'eager', 'sizes' => '320px', 'style' => tal_uitsnede_stijl( array( 'beeld_x' => $blok['kop_beeld_x'] ?? '', 'beeld_positie' => $blok['kop_beeld_y'] ?? '' ), 50, 22 ) ) ) . '</span>';
}
$kop = str_replace( '{foto}', $mini, $kop );
?>
<section <?php echo powerhub_sectie( $blok, 'hero' ); // phpcs:ignore ?>>
	<div class="rij hero-kop">
		<?php echo $kop; // phpcs:ignore ?>
		<?php if ( ! empty( $blok['naschrift'] ) ) : ?>
			<p class="hero-naschrift"><?php echo esc_html( $blok['naschrift'] ); ?></p>
		<?php endif; ?>
		<div class="hero-tekst">
			<?php if ( ! empty( $blok['lead'] ) ) : ?>
				<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
			<?php endif; ?>
			<?php echo tal_alineas( $blok['tekst'] ?? '' ); // phpcs:ignore ?>
			<?php if ( ! empty( $blok['nadruk'] ) ) : ?>
				<p class="hero-nadruk"><?php echo esc_html( $blok['nadruk'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $blok['knop']['url'] ) || ! empty( $blok['link']['url'] ) ) : ?>
				<div class="knoppen">
					<?php echo powerhub_knop( $blok['knop'] ?? array(), 'actie' ); // phpcs:ignore ?>
					<?php echo tal_lijnlink( $blok['link'] ?? array() ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( ! empty( $blok['beeld'] ) ) : ?>
		<div class="rij">
			<?php echo tal_foto( $blok['beeld'], 'breed', 'hero-beeld', $blok, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'alt' => '' ) ); // phpcs:ignore ?>
		</div>
	<?php endif; ?>
</section>
