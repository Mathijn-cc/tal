<?php
/**
 * Blok: hero. $blok komt uit powerhub_blokken() of uit de voorbeelden van het designsysteem.
 */
defined( 'ABSPATH' ) || exit;

$tag = 0 === ( $blok['_index'] ?? 0 ) ? 'h1' : 'h2';
?>
<section <?php echo powerhub_sectie( $blok, 'hero' ); // phpcs:ignore ?>>
	<div class="binnen hero-raster">
		<div class="hero-tekst">
			<?php if ( ! empty( $blok['bovenkop'] ) ) : ?>
				<p class="bovenkop"><?php echo esc_html( $blok['bovenkop'] ); ?></p>
			<?php endif; ?>
			<?php echo powerhub_kop( $blok['kop'] ?? '', $tag, 'tekst-hero' ); // phpcs:ignore ?>
			<?php if ( ! empty( $blok['intro'] ) ) : ?>
				<p class="hero-intro tekst-groot"><?php echo esc_html( $blok['intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $blok['knop']['url'] ) || ! empty( $blok['knop_2']['url'] ) ) : ?>
				<div class="knoppen">
					<?php
					echo powerhub_knop( $blok['knop'] ?? array(), 'actie knop-groot' ); // phpcs:ignore
					$op_donker = false !== strpos( powerhub_sectie( $blok, 'hero' ), 'is-donker' );
					echo powerhub_knop( $blok['knop_2'] ?? array(), $op_donker ? 'op-donker knop-groot' : 'secundair knop-groot' ); // phpcs:ignore
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $blok['beeld'] ) ) : ?>
			<div class="hero-beeld">
				<?php
				echo is_numeric( $blok['beeld'] )
					? wp_get_attachment_image( (int) $blok['beeld'], 'breed', false, array( 'loading' => 'eager', 'fetchpriority' => 'high' ) )
					: '<img src="' . esc_url( $blok['beeld'] ) . '" alt="">';
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
