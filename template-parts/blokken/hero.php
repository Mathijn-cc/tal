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
		<?php if ( ! empty( $blok['kop_beeld'] ) ) : ?>
			<?php /* Op desktop staat de kleine foto als grote foto rechts naast de kop; op mobiel in de zin. */ ?>
			<?php echo tal_foto( $blok['kop_beeld'], 'large', 'hero-foto', array( 'beeld_x' => $blok['kop_beeld_x'] ?? '', 'beeld_positie' => $blok['kop_beeld_y'] ?? '' ), array( 'loading' => 'eager', 'sizes' => '(max-width: 860px) 1px, 32vw', 'alt' => '' ) ); // phpcs:ignore ?>
		<?php endif; ?>
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
</section>
