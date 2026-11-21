<?php
/**
 * De designsysteempagina. Een thema kan deze vervangen met designsysteem/pagina.php.
 * Alles wordt opgebouwd uit tokens.json, de componentbestanden en de blokken van het thema.
 */

defined( 'ABSPATH' ) || exit;

$tokens      = powerhub_tokens();
$map         = powerhub_designsysteem_map();
$componenten = glob( $map . '/componenten/*.php' ) ?: array();
$regels      = file_exists( $map . '/regels.php' ) ? $map . '/regels.php' : '';
$layouts     = powerhub_blok_layouts();
$voorbeelden = (array) apply_filters( 'powerhub_blok_voorbeelden', array() );

/* Tokens per groep. */
$groepen = array();
foreach ( $tokens as $pad => $token ) {
	$groepen[ $token['groep'] ][ $pad ] = $token;
}

$hoofdstukken = array( 'intro' => 'Over dit designsysteem' );
if ( $regels ) {
	$hoofdstukken['regels'] = 'Regels';
}
foreach ( array_keys( $groepen ) as $groep ) {
	$hoofdstukken[ 'tokens-' . $groep ] = ucfirst( $groep );
}
if ( $componenten ) {
	$hoofdstukken['componenten'] = 'Componenten';
}
if ( $layouts ) {
	$hoofdstukken['blokken'] = 'Blokken';
}
/* Modules kunnen hoofdstukken toevoegen: filter powerhub_designsysteem_hoofdstukken (anker => titel)
   en de weergave in de actie powerhub_designsysteem_hoofdstuk_<anker>. */
$extra_hoofdstukken = (array) apply_filters( 'powerhub_designsysteem_hoofdstukken', array() );
$hoofdstukken       = array_merge( $hoofdstukken, $extra_hoofdstukken );

/**
 * Weergave van één token als regel in een tabel.
 */
$token_regel = function ( $token, $voorbeeld ) use ( $tokens ) {
	$waarde = is_array( $token['waarde'] ) ? wp_json_encode( $token['waarde'], JSON_UNESCAPED_SLASHES ) : (string) $token['waarde'];
	$opgelost = is_array( $token['waarde'] ) ? '' : (string) powerhub_token_oplossen( $token['waarde'], $tokens );
	?>
	<tr>
		<td class="designsysteem-voorbeeldcel"><?php echo $voorbeeld; // phpcs:ignore ?></td>
		<td>
			<code class="designsysteem-naam"><?php echo esc_html( 'typography' === $token['type'] ? '--maat-' . $token['naam'] : $token['css'] ); ?></code>
			<?php if ( $token['beschrijving'] ) : ?>
				<small class="designsysteem-uitleg"><?php echo esc_html( $token['beschrijving'] ); ?></small>
			<?php endif; ?>
		</td>
		<td>
			<code><?php echo esc_html( $waarde ); ?></code>
			<?php if ( $opgelost && $opgelost !== $waarde ) : ?>
				<small class="designsysteem-uitleg">= <?php echo esc_html( $opgelost ); ?></small>
			<?php endif; ?>
			<?php if ( null !== $token['mobiel'] ) : ?>
				<small class="designsysteem-uitleg">mobiel: <?php echo esc_html( $token['mobiel'] ); ?></small>
			<?php endif; ?>
			<small class="designsysteem-aangepast" data-aangepast hidden>Aangepast in Powerhub: <span data-aangepast-waarde></span></small>
		</td>
		<td class="designsysteem-bron"><?php echo $token['bron'] ? '<code>' . esc_html( $token['bron'] ) . '</code>' : ''; ?></td>
	</tr>
	<?php
};

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'designsysteem-pagina' ); ?>>
<div class="designsysteem">
	<nav class="designsysteem-menu" aria-label="Hoofdstukken">
		<a class="designsysteem-merk" href="#intro">
			<?php echo powerhub_logo(); // phpcs:ignore ?>
			<span>Designsysteem</span>
		</a>
		<ol>
			<?php foreach ( $hoofdstukken as $anker => $titel ) : ?>
				<li><a href="#<?php echo esc_attr( $anker ); ?>"><?php echo esc_html( $titel ); ?></a></li>
			<?php endforeach; ?>
		</ol>
		<p class="designsysteem-versie">
			Thema <?php echo esc_html( defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : wp_get_theme()->get( 'Version' ) ); ?><br>
			Powerhub <?php echo esc_html( POWERHUB_VERSIE ); ?>
		</p>
	</nav>

	<main class="designsysteem-inhoud">
		<section id="intro" class="designsysteem-hoofdstuk">
			<p class="designsysteem-bovenkop">Bron van waarheid</p>
			<h1 class="designsysteem-titel">Designsysteem <?php bloginfo( 'name' ); ?></h1>
			<p class="designsysteem-lead">Alle tokens, componenten en blokken van deze site. Deze pagina gebruikt de echte stijlen van het thema: wat je hier ziet, staat zo op de site. Wijzigingen gaan via <code>designsysteem/tokens.json</code> in de repo; kleuren kunnen in Powerhub worden overschreven en dat zie je hier terug.</p>
			<dl class="designsysteem-feiten">
				<div><dt>Tokens</dt><dd><?php echo esc_html( count( $tokens ) ); ?></dd></div>
				<div><dt>Componenten</dt><dd><?php echo esc_html( count( $componenten ) ); ?></dd></div>
				<div><dt>Blokken</dt><dd><?php echo esc_html( count( $layouts ) ); ?></dd></div>
				<div><dt>Tokens gewijzigd</dt><dd><?php echo esc_html( wp_date( 'j M Y', (int) filemtime( $map . '/tokens.json' ) ) ); ?></dd></div>
			</dl>
		</section>

		<?php if ( $regels ) : ?>
			<section id="regels" class="designsysteem-hoofdstuk designsysteem-regels">
				<h2 class="designsysteem-kop">Regels</h2>
				<?php include $regels; ?>
			</section>
		<?php endif; ?>

		<?php foreach ( $groepen as $groep => $lijst ) : ?>
			<section id="tokens-<?php echo esc_attr( $groep ); ?>" class="designsysteem-hoofdstuk">
				<h2 class="designsysteem-kop"><?php echo esc_html( ucfirst( $groep ) ); ?></h2>
				<?php
				$soort = reset( $lijst )['type'];

				if ( 'color' === $soort ) :
					/* Palet: vaste hexkleuren. Verwijzingen, transparant en rgba horen bij de semantische laag. */
					$palet     = array_filter( $lijst, function ( $t ) { return is_string( $t['waarde'] ) && preg_match( '/^#[0-9a-f]{6}$/i', $t['waarde'] ); } );
					$semantisch = array_diff_key( $lijst, $palet );
					?>
					<h3 class="designsysteem-subkop">Palet</h3>
					<div class="designsysteem-stalen">
						<?php foreach ( $palet as $t ) : ?>
							<figure class="designsysteem-staal" data-token="<?php echo esc_attr( $t['css'] ); ?>" data-waarde="<?php echo esc_attr( $t['waarde'] ); ?>">
								<span class="designsysteem-staal-kleur" style="background: var(<?php echo esc_attr( $t['css'] ); ?>)"></span>
								<figcaption>
									<strong><?php echo esc_html( $t['naam'] ); ?></strong>
									<code><?php echo esc_html( $t['waarde'] ); ?></code>
									<small class="designsysteem-aangepast" data-aangepast hidden>Powerhub: <span data-aangepast-waarde></span></small>
								</figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
					<?php if ( $semantisch ) : ?>
						<h3 class="designsysteem-subkop">Semantisch, gebruik deze in componenten</h3>
						<table class="designsysteem-tabel">
							<thead><tr><th>Voorbeeld</th><th>Naam</th><th>Waarde</th><th>Origineel</th></tr></thead>
							<tbody>
								<?php
								foreach ( $semantisch as $t ) {
									$token_regel( $t, '<span class="designsysteem-bolletje" style="background: var(' . esc_attr( $t['css'] ) . ')" data-token="' . esc_attr( $t['css'] ) . '"></span>' );
								}
								?>
							</tbody>
						</table>
					<?php endif; ?>

				<?php elseif ( 'typography' === $soort ) : ?>
					<div class="designsysteem-tekststijlen">
						<?php foreach ( $lijst as $t ) : ?>
							<?php $w = $t['waarde']; ?>
							<div class="designsysteem-tekststijl">
								<div class="designsysteem-tekststijl-info">
									<code><?php echo esc_html( '--maat-' . $t['naam'] ); ?></code>
									<small><?php echo esc_html( ( $w['fontSize'] ?? '' ) . ' / ' . ( $w['lineHeight'] ?? '' ) . ( isset( $w['fontWeight'] ) ? ' / ' . powerhub_token_oplossen( (string) $w['fontWeight'], $tokens ) : '' ) . ( null !== $t['mobiel'] ? ' · mobiel ' . $t['mobiel'] : '' ) ); ?></small>
								</div>
								<p class="kop" style="margin: 0; color: var(--kleur-tekst-kop, inherit); font-family: var(--letter-tekst); font-size: var(--maat-<?php echo esc_attr( $t['naam'] ); ?>); line-height: var(--regel-<?php echo esc_attr( $t['naam'] ); ?>); font-weight: <?php echo esc_attr( powerhub_token_oplossen( (string) ( $w['fontWeight'] ?? '400' ), $tokens ) ); ?>; letter-spacing: <?php echo esc_attr( (string) ( $w['letterSpacing'] ?? 'normal' ) ); ?>">Maatschappelijke inzet <em class="accent">slim geregeld.</em></p>
							</div>
						<?php endforeach; ?>
					</div>

				<?php else : ?>
					<table class="designsysteem-tabel">
						<thead><tr><th>Voorbeeld</th><th>Naam</th><th>Waarde</th><th>Origineel</th></tr></thead>
						<tbody>
							<?php
							foreach ( $lijst as $t ) {
								$var = 'var(' . esc_attr( $t['css'] ) . ')';
								switch ( $soort ) {
									case 'fontFamily':
										$voorbeeld = '<span class="designsysteem-letter" style="font-family: ' . $var . ( 'accent' === $t['naam'] ? '; font-style: italic' : '' ) . '">Aa Bb 123</span>';
										break;
									case 'fontWeight':
										$voorbeeld = '<span class="designsysteem-letter" style="font-weight: ' . $var . '">Aa</span>';
										break;
									case 'shadow':
										$voorbeeld = '<span class="designsysteem-vlak" style="box-shadow: ' . $var . '"></span>';
										break;
									default:
										if ( 'hoek' === $groep ) {
											$voorbeeld = '<span class="designsysteem-vlak designsysteem-vlak-rand" style="border-radius: ' . $var . '"></span>';
										} elseif ( 'ruimte' === $groep ) {
											$voorbeeld = '<span class="designsysteem-balk" style="width: min(' . $var . ', 100%)"></span>';
										} else {
											$voorbeeld = '';
										}
								}
								$token_regel( $t, $voorbeeld );
							}
							?>
						</tbody>
					</table>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>

		<?php if ( $componenten ) : ?>
			<section id="componenten" class="designsysteem-hoofdstuk">
				<h2 class="designsysteem-kop">Componenten</h2>
				<?php foreach ( $componenten as $bestand ) : ?>
					<?php $info = powerhub_designsysteem_component_info( $bestand ); ?>
					<article class="designsysteem-component" id="component-<?php echo esc_attr( basename( $bestand, '.php' ) ); ?>">
						<header>
							<h3 class="designsysteem-subkop"><?php echo esc_html( $info['naam'] ); ?></h3>
							<?php if ( $info['uitleg'] ) : ?>
								<p class="designsysteem-uitleg"><?php echo esc_html( $info['uitleg'] ); ?></p>
							<?php endif; ?>
						</header>
						<div class="designsysteem-podium">
							<?php include $bestand; ?>
						</div>
						<details class="designsysteem-code">
							<summary>Markup</summary>
							<pre><code><?php echo esc_html( $info['bron'] ); ?></code></pre>
						</details>
					</article>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( $layouts ) : ?>
			<section id="blokken" class="designsysteem-hoofdstuk designsysteem-hoofdstuk-breed">
				<h2 class="designsysteem-kop">Blokken</h2>
				<p class="designsysteem-uitleg">De blokken die redacteuren in WordPress kunnen kiezen, met voorbeeldinhoud.</p>
				<?php foreach ( $layouts as $naam => $layout ) : ?>
					<article class="designsysteem-blok">
						<h3 class="designsysteem-subkop"><?php echo esc_html( $layout['label'] ?? $naam ); ?> <code><?php echo esc_html( $naam ); ?></code></h3>
						<?php if ( ! empty( $layout['uitleg'] ) ) : ?>
							<p class="designsysteem-uitleg"><?php echo esc_html( $layout['uitleg'] ); ?></p>
						<?php endif; ?>
						<?php
						if ( isset( $voorbeelden[ $naam ] ) ) {
							/* Een blok kan meerdere voorbeelden hebben (lijst met varianten). */
							$varianten = isset( $voorbeelden[ $naam ][0] ) ? $voorbeelden[ $naam ] : array( $voorbeelden[ $naam ] );
							foreach ( $varianten as $variant ) {
								echo '<div class="designsysteem-blok-podium">';
								powerhub_blok( $naam, $variant, 1 );
								echo '</div>';
							}
						} else {
							echo '<p class="designsysteem-uitleg">Nog geen voorbeeldinhoud (filter powerhub_blok_voorbeelden).</p>';
						}
						?>
					</article>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>
		<?php foreach ( $extra_hoofdstukken as $anker => $titel ) : ?>
			<section id="<?php echo esc_attr( $anker ); ?>" class="designsysteem-hoofdstuk">
				<h2 class="designsysteem-kop"><?php echo esc_html( $titel ); ?></h2>
				<?php do_action( 'powerhub_designsysteem_hoofdstuk_' . $anker ); ?>
			</section>
		<?php endforeach; ?>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
