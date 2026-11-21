<?php
/**
 * Blok: afsluiter.
 */
defined( 'ABSPATH' ) || exit;
?>
<section <?php echo powerhub_sectie( $blok, 'cta' ); // phpcs:ignore ?>>
	<?php if ( ! empty( $blok['beeld'] ) ) : ?>
		<div class="cta-beeld" aria-hidden="true">
			<?php echo powerhub_beeld( $blok['beeld'], 'breed', array( 'class' => 'foto-beeld', 'alt' => '', 'sizes' => '(max-width: 860px) 100vw, 66vw', 'style' => tal_uitsnede_stijl( $blok, 55, 35 ) ) ); // phpcs:ignore ?>
		</div>
		<div class="cta-waas" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="rij cta-rij">
		<?php if ( ! empty( $blok['label'] ) ) : ?>
			<p class="marge cta-label"><?php echo esc_html( $blok['label'] ); ?></p>
		<?php endif; ?>
		<?php echo tal_kop( $blok['kop'] ?? '', 'h2', 'cta-kop' ); // phpcs:ignore ?>
		<div class="cta-actie">
			<?php if ( ! empty( $blok['knop']['url'] ) ) : ?>
				<a class="magneet" href="<?php echo esc_url( $blok['knop']['url'] ); ?>"<?php echo ! empty( $blok['knop']['target'] ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $blok['knop']['title'] ?? '' ); ?></a>
			<?php endif; ?>
			<div class="cta-contact">
				<?php if ( ! empty( $blok['email'] ) ) : ?>
					<a class="lijnlink" href="mailto:<?php echo esc_attr( antispambot( $blok['email'] ) ); ?>"><?php echo esc_html( antispambot( $blok['email'] ) ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $blok['regel'] ) ) : ?>
					<span><?php echo esc_html( $blok['regel'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
