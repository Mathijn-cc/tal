<?php
/**
 * Blok: podcast.
 */
defined( 'ABSPATH' ) || exit;

$link  = $blok['knop'] ?? array();
$titel = wp_strip_all_tags( str_replace( '<br>', ' ', (string) ( $blok['kop'] ?? '' ) ) );

/* Spotify-link omzetten naar de embed: open.spotify.com/show/<id> wordt open.spotify.com/embed/show/<id>. */
$embed = '';
if ( ! empty( $blok['spotify'] ) && preg_match( '#open\.spotify\.com/(?:embed/)?(show|episode|playlist)/([A-Za-z0-9]+)#', $blok['spotify'], $m ) ) {
	$embed = 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?utm_source=generator';
}
?>
<section <?php echo powerhub_sectie( $blok, 'podcast' ); // phpcs:ignore ?>>
	<div class="rij">
		<div class="podcast-tekst">
			<?php if ( ! empty( $blok['label'] ) ) : ?>
				<p class="bovenkop-tal"><?php echo esc_html( $blok['label'] ); ?></p>
			<?php endif; ?>
			<?php echo tal_kop( $blok['kop'] ?? '', 'h2' ); // phpcs:ignore ?>
			<?php if ( ! empty( $blok['lead'] ) ) : ?>
				<p class="lead"><?php echo esc_html( $blok['lead'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $blok['tekst'] ) ) : ?>
				<p class="podcast-uitleg"><?php echo esc_html( $blok['tekst'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $link['url'] ) ) : ?>
				<div class="podcast-knop"><?php echo powerhub_knop( $link, 'actie' ); // phpcs:ignore ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $embed ) : ?>
			<div class="podcast-embed">
				<iframe src="<?php echo esc_url( $embed ); ?>" title="<?php echo esc_attr( $titel . ' op Spotify' ); ?>" width="100%" height="600" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
			</div>
		<?php elseif ( ! empty( $link['url'] ) ) : ?>
			<?php if ( ! empty( $blok['beeld'] ) ) : ?>
				<a class="podcast-speler" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $titel . ' op Spotify' ); ?>">
					<?php echo powerhub_beeld( $blok['beeld'], 'large', array( 'alt' => '', 'sizes' => '(max-width: 860px) 100vw, 40vw' ) ); // phpcs:ignore ?>
				</a>
			<?php else : ?>
				<a class="podcast-tegel" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $titel . ' op Spotify' ); ?>">
					<span class="podcast-tegel-titel"><?php echo wp_kses( (string) ( $blok['kop'] ?? '' ), array( 'br' => array() ) ); ?></span>
					<span class="podcast-tegel-voet">
						<span><?php echo esc_html( $blok['tegel_onderschrift'] ?? '' ); ?></span>
						<span class="podcast-speel" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" focusable="false"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
					</span>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
