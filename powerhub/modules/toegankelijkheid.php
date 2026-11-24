<?php
/**
 * Module Toegankelijkheid en bruikbaarheid.
 *
 * - Tab in Powerhub (beheer) met de grenzen van het project en een rapport: tekstgroottes, contrast,
 *   media zonder alt-tekst en een controlelijst. Plus een kleurchecker.
 * - Hoofdstuk Toegankelijkheid op /designsysteem/ met hetzelfde rapport (tekst en contrast).
 * - Paginacontrole op de voorkant voor beheerders: adminbalk-item Toegankelijkheid, of ?toegankelijkheid=1.
 *
 * Projecten haken in met filters, nooit door deze module aan te passen:
 *   powerhub_contrast_rollen   rol => array( label, token op licht, token op donker, soort )
 *   powerhub_contrast_paren    lijst van array( label, voorgrond-token, achtergrond-token, soort )
 *   powerhub_lopende_tekststijlen   tekststijlen (typography-tokens) die aan de grens voor lopende tekst moeten voldoen
 *   powerhub_toegankelijkheid_taal  verwachte taal van de site (standaard nl)
 * Soort: normaal (lopende tekst), groot (vanaf 24px, of 18.66px vet), ui (iconen, randen, focus).
 * Tokens mag je kort schrijven (tekst-kop) of met groep (kleur.tekst-kop).
 */

defined( 'ABSPATH' ) || exit;

require_once POWERHUB_PAD . '/kern/contrast.php';

add_action( 'powerhub_registreren', function () {
	powerhub_tab( 'toegankelijkheid', array(
		'titel'    => 'Toegankelijkheid',
		'uitleg'   => 'Tekst, contrast, klikdoelen en een controle per pagina',
		'niveau'   => 'beheer',
		'volgorde' => 75,
		'velden'   => array(
			'toegankelijk_kop_grenzen'    => array( 'type' => 'kop', 'label' => 'Grenzen', 'uitleg' => 'Het rapport en de paginacontrole toetsen hieraan.' ),
			'toegankelijk_kleinste_tekst' => array( 'type' => 'getal', 'label' => 'Kleinste tekst', 'uitleg' => 'In pixels. Niets op de site is kleiner, ook geen onderschrift of bovenkop.', 'standaard' => 14, 'min' => 10, 'max' => 24, 'stap' => 1 ),
			'toegankelijk_lopende_tekst'  => array( 'type' => 'getal', 'label' => 'Kleinste lopende tekst', 'uitleg' => 'In pixels, voor alinea\'s en lijsten.', 'standaard' => 16, 'min' => 12, 'max' => 24, 'stap' => 1 ),
			'toegankelijk_niveau'         => array( 'type' => 'keuze', 'label' => 'Contrastniveau', 'uitleg' => 'AA is de wettelijke norm (EN 301 549). AAA is strenger.', 'standaard' => 'AA', 'keuzes' => array( 'AA' => 'AA (4.5 : 1, grote tekst 3 : 1)', 'AAA' => 'AAA (7 : 1, grote tekst 4.5 : 1)' ) ),
			'toegankelijk_klikdoel'       => array( 'type' => 'getal', 'label' => 'Kleinste klikdoel', 'uitleg' => 'In pixels, breedte en hoogte van knoppen en links.', 'standaard' => 44, 'min' => 24, 'max' => 64, 'stap' => 1 ),
			'toegankelijk_uitzonderingen' => array( 'type' => 'tekstvak', 'label' => 'Bewuste uitzonderingen contrast', 'uitleg' => 'Tekstkleuren die bewust onder de contrasteis blijven, één per regel: kleurcode en reden. Bijvoorbeeld: #00cef1 Accentkleur, keuze klant. De paginacontrole toont ze apart en telt ze niet als bevinding.', 'standaard' => '' ),
			'toegankelijk_paginacontrole' => array( 'type' => 'schakel', 'label' => 'Paginacontrole in de adminbalk', 'uitleg' => 'Voor beheerders: controleer elke pagina op de voorkant met één klik.', 'standaard' => true ),
		),
		'weergave' => 'powerhub_toegankelijkheid_tab',
	) );
} );

/* --------------------------------------------------------------------------
 * Instellingen
 * ----------------------------------------------------------------------- */

/**
 * Bewuste contrastuitzonderingen: array( '#00cef1' => 'reden' ). Uit het veld in Powerhub, aan te vullen door het thema
 * via het filter powerhub_toegankelijkheid_uitzonderingen.
 */
function powerhub_toegankelijkheid_uitzonderingen() {
	$lijst = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) powerhub( 'toegankelijk_uitzonderingen', '' ) ) as $regel ) {
		if ( preg_match( '/^\s*(#[0-9a-f]{6})\b\s*(.*)$/i', $regel, $m ) ) {
			$lijst[ strtolower( $m[1] ) ] = trim( $m[2] ) ? trim( $m[2] ) : 'Bewuste keuze.';
		}
	}
	$lijst = (array) apply_filters( 'powerhub_toegankelijkheid_uitzonderingen', $lijst );
	return array_change_key_case( $lijst, CASE_LOWER );
}

function powerhub_toegankelijkheid_instellingen() {
	$niveau = (string) powerhub( 'toegankelijk_niveau', 'AA' );
	return array(
		'kleinste' => (float) powerhub( 'toegankelijk_kleinste_tekst', 14 ) ?: 14,
		'lopend'   => (float) powerhub( 'toegankelijk_lopende_tekst', 16 ) ?: 16,
		'niveau'   => in_array( $niveau, array( 'AA', 'AAA' ), true ) ? $niveau : 'AA',
		'klikdoel' => (float) powerhub( 'toegankelijk_klikdoel', 44 ) ?: 44,
	);
}

/* --------------------------------------------------------------------------
 * Kleuren met de overschrijvingen uit Powerhub
 * ----------------------------------------------------------------------- */

/**
 * Tokenpad uit een korte naam: tekst-kop wordt kleur.tekst-kop.
 */
function powerhub_toegankelijkheid_pad( $token ) {
	$token = (string) $token;
	return false === strpos( $token, '.' ) ? 'kleur.' . $token : $token;
}

/**
 * De kleuren die in Powerhub zijn overschreven: tokenpad => waarde.
 */
function powerhub_kleur_overschrijvingen() {
	static $lijst = null;
	if ( null !== $lijst ) {
		return $lijst;
	}
	$lijst = array();
	if ( ! function_exists( 'powerhub_kleuren' ) ) {
		return $lijst;
	}
	foreach ( powerhub_kleuren() as $sleutel => $kleur ) {
		$waarde = (string) powerhub( 'kleur_' . $sleutel, $kleur[1] );
		if ( $waarde && strtolower( $waarde ) !== strtolower( (string) $kleur[1] ) ) {
			$lijst[ 'kleur.' . $sleutel ] = $waarde;
		}
	}
	return $lijst;
}

/**
 * De werkelijke kleur van een token: volgt verwijzingen en neemt bij elke stap een overschrijving uit Powerhub mee.
 * Een hexwaarde wordt ongewijzigd teruggegeven. Null als het token niet bestaat.
 */
function powerhub_kleur_werkelijk( $token, $diepte = 0 ) {
	$token = trim( (string) $token );
	if ( '' === $token || $diepte > 10 ) {
		return null;
	}
	if ( '#' === $token[0] || 0 === stripos( $token, 'rgb' ) ) {
		return $token;
	}
	$pad          = powerhub_toegankelijkheid_pad( trim( $token, '{}' ) );
	$overschreven = powerhub_kleur_overschrijvingen();
	if ( isset( $overschreven[ $pad ] ) ) {
		return $overschreven[ $pad ];
	}
	$tokens = function_exists( 'powerhub_tokens' ) ? powerhub_tokens() : array();
	if ( ! isset( $tokens[ $pad ] ) ) {
		return null;
	}
	$waarde = (string) $tokens[ $pad ]['waarde'];
	if ( preg_match( '/^\{([^}]+)\}$/', $waarde, $m ) ) {
		return powerhub_kleur_werkelijk( $m[1], $diepte + 1 );
	}
	return (string) powerhub_token_oplossen( $waarde, $tokens );
}

/**
 * Het palet van het thema: vaste kleuren uit tokens.json, met overschrijvingen. Voor de kleurchecker.
 */
function powerhub_toegankelijkheid_palet() {
	$palet = array();
	foreach ( function_exists( 'powerhub_tokens' ) ? powerhub_tokens() : array() as $pad => $t ) {
		if ( 'color' === $t['type'] && is_string( $t['waarde'] ) && preg_match( '/^#[0-9a-f]{6}$/i', $t['waarde'] ) ) {
			$palet[] = array( 'naam' => $t['naam'], 'kleur' => strtolower( (string) powerhub_kleur_werkelijk( $pad ) ) );
		}
	}
	return $palet;
}

/* --------------------------------------------------------------------------
 * Controles
 * ----------------------------------------------------------------------- */

/**
 * Een CSS-maat in pixels (px, rem, em uitgaande van 16px). Null als het geen vaste maat is.
 */
function powerhub_toegankelijkheid_px( $maat ) {
	if ( is_numeric( $maat ) ) {
		return (float) $maat;
	}
	if ( preg_match( '/^\s*([\d.]+)\s*(px|rem|em)?\s*$/i', (string) $maat, $m ) ) {
		$getal = (float) $m[1];
		return isset( $m[2] ) && in_array( strtolower( $m[2] ), array( 'rem', 'em' ), true ) ? $getal * 16 : $getal;
	}
	return null;
}

/**
 * Tekstgroottes: elke tekststijl (desktop en mobiel) tegen de grenzen.
 * Geeft een lijst regels: token, scherm, waarde, px, grens, goed.
 */
function powerhub_toegankelijkheid_tekstgroottes() {
	$inst    = powerhub_toegankelijkheid_instellingen();
	$tokens  = function_exists( 'powerhub_tokens' ) ? powerhub_tokens() : array();
	$lopende = (array) apply_filters( 'powerhub_lopende_tekststijlen', array( 'lopend', 'groot' ) );
	$regels  = array();
	foreach ( $tokens as $t ) {
		if ( 'typography' !== $t['type'] || ! is_array( $t['waarde'] ) || ! isset( $t['waarde']['fontSize'] ) ) {
			continue;
		}
		$is_lopend = in_array( $t['naam'], $lopende, true );
		$grens     = $is_lopend ? $inst['lopend'] : $inst['kleinste'];
		$schermen  = array( 'desktop' => powerhub_token_oplossen( (string) $t['waarde']['fontSize'], $tokens ) );
		if ( null !== $t['mobiel'] ) {
			$schermen['mobiel'] = (string) $t['mobiel'];
		}
		foreach ( $schermen as $scherm => $waarde ) {
			$px       = powerhub_toegankelijkheid_px( $waarde );
			$regels[] = array(
				'token'  => '--maat-' . $t['naam'],
				'scherm' => $scherm,
				'waarde' => $waarde,
				'px'     => $px,
				'grens'  => $grens,
				'soort'  => $is_lopend ? 'lopende tekst' : 'tekst',
				'goed'   => null === $px ? null : $px >= $grens,
			);
		}
	}
	return $regels;
}

/**
 * Standaardrollen voor tekst op een achtergrond. Projecten vullen aan of vervangen met powerhub_contrast_rollen.
 */
function powerhub_contrast_rollen() {
	return (array) apply_filters( 'powerhub_contrast_rollen', array(
		'kop'     => array( 'Kop', 'tekst-kop', 'tekst-op-donker', 'groot' ),
		'lopend'  => array( 'Lopende tekst', 'tekst-lopend', 'tekst-op-donker-gedempt', 'normaal' ),
		'gedempt' => array( 'Gedempte tekst', 'tekst-gedempt', 'tekst-op-donker-gedempt', 'normaal' ),
		'link'    => array( 'Link', 'tekst-link', 'tekst-link-op-donker', 'normaal' ),
		'accent'  => array( 'Accent in koppen', 'tekst-accent-op-licht', 'tekst-accent', 'groot' ),
		'focus'   => array( 'Focusrand', 'focus-op-licht', 'focus', 'ui' ),
	) );
}

/**
 * Contrast van alle rollen op alle achtergronden, plus de losse paren.
 * Ontbrekende tokens worden overgeslagen.
 */
function powerhub_toegankelijkheid_contrast() {
	$niveau = powerhub_toegankelijkheid_instellingen()['niveau'];
	$regels = array();

	$toevoegen = function ( $rol, $achtergrond, $voor_token, $achter_token, $soort ) use ( $niveau, &$regels ) {
		$voor   = powerhub_kleur_werkelijk( $voor_token );
		$achter = powerhub_kleur_werkelijk( $achter_token );
		if ( ! $voor || ! $achter ) {
			return;
		}
		$verhouding = powerhub_contrast( $voor, $achter );
		if ( null === $verhouding ) {
			return;
		}
		$soort    = in_array( $soort, array( 'normaal', 'groot', 'ui' ), true ) ? $soort : 'normaal';
		$eis      = powerhub_contrast_eis( $soort, $niveau );
		$regels[] = array(
			'rol'          => $rol,
			'achtergrond'  => $achtergrond,
			'voor'         => $voor,
			'achter'       => $achter,
			'voor_token'   => preg_replace( '/^kleur\./', '', (string) $voor_token ),
			'achter_token' => preg_replace( '/^kleur\./', '', (string) $achter_token ),
			'soort'        => $soort,
			'verhouding'   => $verhouding,
			'eis'          => $eis,
			'goed'         => $verhouding >= $eis,
		);
	};

	$achtergronden = function_exists( 'powerhub_achtergronden' ) ? powerhub_achtergronden() : array();
	foreach ( $achtergronden as $a ) {
		$achter = $a['token'] ? $a['token'] : $a['kleur'];
		if ( ! $achter ) {
			continue;
		}
		foreach ( powerhub_contrast_rollen() as $rol ) {
			$rol   = array_values( (array) $rol );
			$token = $a['donker'] ? ( $rol[2] ?? '' ) : ( $rol[1] ?? '' );
			if ( $token ) {
				$toevoegen( $rol[0] ?? '', $a['label'], $token, $achter, $rol[3] ?? 'normaal' );
			}
		}
	}

	foreach ( (array) apply_filters( 'powerhub_contrast_paren', array() ) as $paar ) {
		$paar = array_values( (array) $paar );
		if ( count( $paar ) < 3 ) {
			continue;
		}
		$achter_naam = preg_replace( '/^kleur\./', '', (string) $paar[2] );
		$toevoegen( $paar[0], $achter_naam, $paar[1], $paar[2], $paar[3] ?? 'normaal' );
	}

	return $regels;
}

/**
 * Afbeeldingen in de mediabibliotheek zonder alt-tekst: aantal en de eerste 20.
 */
function powerhub_toegankelijkheid_media() {
	$query = new WP_Query( array(
		'post_type'              => 'attachment',
		'post_status'            => 'inherit',
		'post_mime_type'         => 'image',
		'posts_per_page'         => 20,
		'fields'                 => 'ids',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => false,
		'update_post_term_cache' => false,
		'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array( 'key' => '_wp_attachment_image_alt', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_wp_attachment_image_alt', 'value' => '' ),
		),
	) );
	return array( 'aantal' => (int) $query->found_posts, 'ids' => array_map( 'intval', $query->posts ) );
}

/**
 * Pagina's met blokken waarvan het eerste blok geen kop heeft: die pagina's missen een h1.
 * Het eerste blok maakt van zijn kop de h1 (zie de blokken in het thema).
 */
function powerhub_toegankelijkheid_zonder_h1() {
	$zonder = array();
	$paginas = get_posts( array(
		'post_type'      => function_exists( 'powerhub_blok_posttypes' ) ? powerhub_blok_posttypes() : array( 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'meta_key'       => 'blokken', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_compare'   => 'EXISTS',
	) );
	$zonder_kop = (array) apply_filters( 'powerhub_toegankelijkheid_kopblokken', array() );
	foreach ( $paginas as $id ) {
		$blokken = get_post_meta( $id, 'blokken', true );
		if ( ! is_array( $blokken ) || ! $blokken ) {
			continue;
		}
		$eerste = (string) reset( $blokken );
		/* Een project kan blokken opgeven die altijd zelf een h1 maken. */
		if ( in_array( $eerste, $zonder_kop, true ) ) {
			continue;
		}
		if ( '' === trim( wp_strip_all_tags( (string) get_post_meta( $id, 'blokken_0_kop', true ) ) ) ) {
			$zonder[ $id ] = $eerste;
		}
	}
	return $zonder;
}

/* --------------------------------------------------------------------------
 * Weergave
 * ----------------------------------------------------------------------- */

function powerhub_toegankelijkheid_status( $goed, $tekst_goed = 'Voldoet', $tekst_fout = 'Voldoet niet' ) {
	if ( null === $goed ) {
		return '<span class="toegankelijkheid-status is-handmatig">Handmatig</span>';
	}
	if ( 'nvt' === $goed ) {
		return '<span class="toegankelijkheid-status is-nvt">N.v.t.</span>';
	}
	return $goed
		? '<span class="toegankelijkheid-status is-goed">' . esc_html( $tekst_goed ) . '</span>'
		: '<span class="toegankelijkheid-status is-fout">' . esc_html( $tekst_fout ) . '</span>';
}

function powerhub_toegankelijkheid_getal( $getal ) {
	return rtrim( rtrim( number_format( (float) $getal, 2, '.', '' ), '0' ), '.' );
}

/**
 * Rapport tekstgroottes.
 */
function powerhub_toegankelijkheid_rapport_tekst( $regels = null ) {
	$regels = null === $regels ? powerhub_toegankelijkheid_tekstgroottes() : $regels;
	$inst   = powerhub_toegankelijkheid_instellingen();
	$fout   = count( array_filter( $regels, function ( $r ) { return false === $r['goed']; } ) );
	?>
	<div class="toegankelijkheid-onderdeel">
		<h3 class="toegankelijkheid-kop">Tekstgroottes</h3>
		<p class="toegankelijkheid-uitleg">Kleinste tekst <?php echo esc_html( powerhub_toegankelijkheid_getal( $inst['kleinste'] ) ); ?>px, lopende tekst minimaal <?php echo esc_html( powerhub_toegankelijkheid_getal( $inst['lopend'] ) ); ?>px. Elke tekststijl uit tokens.json, ook de mobiele waarde.</p>
		<?php if ( ! $regels ) : ?>
			<p class="toegankelijkheid-uitleg">Geen tekststijlen gevonden in tokens.json.</p>
		<?php else : ?>
			<p class="toegankelijkheid-samenvatting">
				<span class="toegankelijkheid-teller is-goed"><strong><?php echo esc_html( count( $regels ) - $fout ); ?></strong> voldoen</span>
				<span class="toegankelijkheid-teller <?php echo $fout ? 'is-fout' : 'is-leeg'; ?>"><strong><?php echo esc_html( $fout ); ?></strong> te klein</span>
			</p>
			<div class="toegankelijkheid-scroll">
				<table class="toegankelijkheid-tabel">
					<thead><tr><th scope="col">Token</th><th scope="col">Scherm</th><th scope="col">Waarde</th><th scope="col">Grens</th><th scope="col">Status</th></tr></thead>
					<tbody>
						<?php foreach ( $regels as $r ) : ?>
							<tr class="<?php echo false === $r['goed'] ? 'is-fout' : ''; ?>">
								<td><code><?php echo esc_html( $r['token'] ); ?></code><?php echo 'lopende tekst' === $r['soort'] ? ' <small>lopende tekst</small>' : ''; ?></td>
								<td><?php echo esc_html( $r['scherm'] ); ?></td>
								<td><code><?php echo esc_html( $r['waarde'] ); ?></code></td>
								<td><?php echo esc_html( powerhub_toegankelijkheid_getal( $r['grens'] ) ); ?>px</td>
								<td><?php echo powerhub_toegankelijkheid_status( $r['goed'], 'Voldoet', 'Te klein' ); // phpcs:ignore ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Rapport contrast.
 */
function powerhub_toegankelijkheid_rapport_contrast( $regels = null ) {
	$regels  = null === $regels ? powerhub_toegankelijkheid_contrast() : $regels;
	$inst    = powerhub_toegankelijkheid_instellingen();
	$fout    = array_filter( $regels, function ( $r ) { return ! $r['goed']; } );
	$soorten = array( 'normaal' => 'tekst', 'groot' => 'grote tekst', 'ui' => 'niet-tekst' );
	?>
	<div class="toegankelijkheid-onderdeel">
		<h3 class="toegankelijkheid-kop">Contrast</h3>
		<p class="toegankelijkheid-uitleg">Niveau <?php echo esc_html( $inst['niveau'] ); ?>, met de kleuren zoals ze nu op de site staan (tokens.json plus overschrijvingen in Powerhub). Tekst 4.5 : 1, grote tekst en niet-tekst 3 : 1<?php echo 'AAA' === $inst['niveau'] ? ' (AAA: 7 en 4.5)' : ''; ?>.</p>
		<?php if ( ! $regels ) : ?>
			<p class="toegankelijkheid-uitleg">Geen kleurparen gevonden. Controleer de achtergronden (filter powerhub_achtergronden) en de rollen (filter powerhub_contrast_rollen).</p>
		<?php else : ?>
			<p class="toegankelijkheid-samenvatting">
				<span class="toegankelijkheid-teller is-goed"><strong><?php echo esc_html( count( $regels ) - count( $fout ) ); ?></strong> voldoen</span>
				<span class="toegankelijkheid-teller <?php echo $fout ? 'is-fout' : 'is-leeg'; ?>"><strong><?php echo esc_html( count( $fout ) ); ?></strong> voldoen niet</span>
			</p>
			<div class="toegankelijkheid-scroll">
				<table class="toegankelijkheid-tabel">
					<thead><tr><th scope="col">Rol</th><th scope="col">Achtergrond</th><th scope="col">Kleuren</th><th scope="col">Verhouding</th><th scope="col">Eis</th><th scope="col">Status</th></tr></thead>
					<tbody>
						<?php foreach ( $regels as $r ) : ?>
							<tr class="<?php echo $r['goed'] ? '' : 'is-fout'; ?>">
								<td><?php echo esc_html( $r['rol'] ); ?> <small><?php echo esc_html( $soorten[ $r['soort'] ] ); ?></small></td>
								<td><?php echo esc_html( $r['achtergrond'] ); ?></td>
								<td>
									<span class="toegankelijkheid-paar" style="<?php echo esc_attr( 'background:' . $r['achter'] . ';color:' . $r['voor'] ); ?>" aria-hidden="true">Aa</span>
									<span class="toegankelijkheid-bolletje" style="<?php echo esc_attr( 'background:' . $r['voor'] ); ?>" aria-hidden="true"></span>
									<span class="toegankelijkheid-bolletje" style="<?php echo esc_attr( 'background:' . $r['achter'] ); ?>" aria-hidden="true"></span>
									<small><code><?php echo esc_html( $r['voor_token'] ); ?></code> op <code><?php echo esc_html( $r['achter_token'] ); ?></code></small>
								</td>
								<td><strong><?php echo esc_html( number_format( $r['verhouding'], 2, '.', '' ) ); ?></strong> : 1</td>
								<td><?php echo esc_html( powerhub_toegankelijkheid_getal( $r['eis'] ) ); ?> : 1</td>
								<td><?php echo powerhub_toegankelijkheid_status( $r['goed'] ); // phpcs:ignore ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Kleurchecker: twee kleuren, live verhouding en badges. De werking zit in assets/toegankelijkheid.js.
 */
function powerhub_toegankelijkheid_kleurchecker() {
	static $nummer = 0;
	++$nummer;
	$palet  = powerhub_toegankelijkheid_palet();
	$voor   = powerhub_kleur_werkelijk( 'tekst-kop' ) ?: '#1f2328';
	$achter = '#ffffff';
	$lijst  = function_exists( 'powerhub_achtergronden' ) ? powerhub_achtergronden() : array();
	$eerste = $lijst ? reset( $lijst ) : null;
	if ( $eerste && ( $eerste['token'] || $eerste['kleur'] ) ) {
		$achter = powerhub_kleur_werkelijk( $eerste['token'] ? $eerste['token'] : $eerste['kleur'] ) ?: $achter;
	}
	$id = 'kleurchecker-' . $nummer;
	?>
	<div class="toegankelijkheid-onderdeel">
		<h3 class="toegankelijkheid-kop" id="<?php echo esc_attr( $id ); ?>-kop">Kleurchecker</h3>
		<p class="toegankelijkheid-uitleg">Kies twee kleuren of klik op een kleur uit het palet. De verhouding volgt WCAG 2.1.</p>
		<div class="kleurchecker" data-kleurchecker data-palet="<?php echo esc_attr( wp_json_encode( $palet ) ); ?>" role="group" aria-labelledby="<?php echo esc_attr( $id ); ?>-kop">
			<div class="kleurchecker-velden">
				<?php
				foreach ( array( 'voor' => array( 'Voorgrond', $voor ), 'achter' => array( 'Achtergrond', $achter ) ) as $rol => $info ) :
					$veld_id = $id . '-' . $rol;
					$hex     = preg_match( '/^#[0-9a-f]{6}$/i', $info[1] ) ? strtolower( $info[1] ) : '#000000';
					?>
					<div class="kleurchecker-veld" data-rol="<?php echo esc_attr( $rol ); ?>">
						<label class="kleurchecker-label" for="<?php echo esc_attr( $veld_id ); ?>"><?php echo esc_html( $info[0] ); ?></label>
						<div class="kleurchecker-invoer">
							<input type="color" value="<?php echo esc_attr( $hex ); ?>" aria-label="<?php echo esc_attr( $info[0] . ', kleurkiezer' ); ?>" data-kiezer>
							<input type="text" id="<?php echo esc_attr( $veld_id ); ?>" value="<?php echo esc_attr( $hex ); ?>" spellcheck="false" autocomplete="off" inputmode="text" pattern="#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?" data-hex>
						</div>
						<?php if ( $palet ) : ?>
							<div class="kleurchecker-palet" role="group" aria-label="<?php echo esc_attr( $info[0] . ' uit het palet' ); ?>">
								<?php foreach ( $palet as $kleur ) : ?>
									<button type="button" class="kleurchecker-staal" style="<?php echo esc_attr( 'background:' . $kleur['kleur'] ); ?>" data-kleur="<?php echo esc_attr( $kleur['kleur'] ); ?>" title="<?php echo esc_attr( $kleur['naam'] . ' ' . $kleur['kleur'] ); ?>"><span class="screen-reader-text"><?php echo esc_html( $kleur['naam'] . ' ' . $kleur['kleur'] ); ?></span></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ( 'voor' === $rol ) : ?>
						<button type="button" class="kleurchecker-omdraaien" data-omdraaien>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 4 3 8l4 4"/><path d="M3 8h18"/><path d="m17 20 4-4-4-4"/><path d="M21 16H3"/></svg>
							<span>Omdraaien</span>
						</button>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<div class="kleurchecker-uitslag">
				<div class="kleurchecker-voorbeeld" data-voorbeeld>
					<p class="kleurchecker-voorbeeld-normaal">Normale tekst, 16px. Vrijwilligers vinden sneller hun plek.</p>
					<p class="kleurchecker-voorbeeld-groot">Grote tekst, 24px</p>
				</div>
				<div class="kleurchecker-score">
					<p class="kleurchecker-verhouding" aria-live="polite"><strong data-verhouding>-</strong> : 1</p>
					<ul class="kleurchecker-badges">
						<li data-badge="normaal-AA">Tekst AA <span>4.5</span></li>
						<li data-badge="normaal-AAA">Tekst AAA <span>7</span></li>
						<li data-badge="groot-AA">Grote tekst AA <span>3</span></li>
						<li data-badge="groot-AAA">Grote tekst AAA <span>4.5</span></li>
						<li data-badge="ui-AA">UI en iconen <span>3</span></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Controlelijst: automatische regels waar dat kan, de rest handmatig.
 */
function powerhub_toegankelijkheid_controlelijst( $tekst, $contrast, $media ) {
	$inst       = powerhub_toegankelijkheid_instellingen();
	$tekst_fout = count( array_filter( $tekst, function ( $r ) { return false === $r['goed']; } ) );
	$con_fout   = count( array_filter( $contrast, function ( $r ) { return ! $r['goed']; } ) );
	$taal       = (string) apply_filters( 'powerhub_toegankelijkheid_taal', 'nl' );
	$locale     = get_locale();
	$zonder_h1  = powerhub_toegankelijkheid_zonder_h1();

	/* Beperkte beweging: de animatiemodule respecteert die altijd; kijk ook of de CSS van het thema er rekening mee houdt. */
	$css_thema  = get_template_directory() . '/assets/css/thema.css';
	$css_rustig = file_exists( $css_thema ) && false !== strpos( (string) file_get_contents( $css_thema ), 'prefers-reduced-motion' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$module     = isset( $GLOBALS['powerhub_tabs']['animaties'] );
	if ( $module ) {
		$beweging        = true;
		$beweging_uitleg = $css_rustig ? 'De animatiemodule en de CSS van het thema houden rekening met prefers-reduced-motion.' : 'De animatiemodule respecteert het altijd. Let op: assets/css/thema.css heeft geen regel voor prefers-reduced-motion; controleer de overgangen.';
	} else {
		$beweging        = $css_rustig ? true : 'nvt';
		$beweging_uitleg = $css_rustig ? 'De CSS van het thema houdt rekening met prefers-reduced-motion.' : 'Geen animatiemodule actief.';
	}

	$h1_uitleg = 'Het eerste blok van elke pagina heeft een kop (die wordt de h1).';
	if ( $zonder_h1 ) {
		$links = array();
		foreach ( array_slice( $zonder_h1, 0, 10, true ) as $id => $blok ) {
			$links[] = '<a href="' . esc_url( (string) get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: '#' . $id ) . '</a> (' . esc_html( $blok ) . ')';
		}
		$h1_uitleg .= ' Zonder kop: ' . implode( ', ', $links ) . ( count( $zonder_h1 ) > 10 ? ' en meer.' : '.' );
	}

	$regels = array(
		array( 'Geen tekst kleiner dan ' . powerhub_toegankelijkheid_getal( $inst['kleinste'] ) . 'px, lopende tekst minimaal ' . powerhub_toegankelijkheid_getal( $inst['lopend'] ) . 'px.', esc_html( $tekst_fout ? $tekst_fout . ' tekststijl(en) te klein, zie Tekstgroottes.' : 'Alle tekststijlen voldoen.' ), $tekst ? 0 === $tekst_fout : 'nvt' ),
		array( 'Contrast ' . $inst['niveau'] . ' voor alle tekst op alle achtergronden.', esc_html( $con_fout ? $con_fout . ' combinatie(s) voldoen niet, zie Contrast.' : 'Alle combinaties voldoen.' ), $contrast ? 0 === $con_fout : 'nvt' ),
		array( 'Elke afbeelding heeft een alt-tekst, decoratieve beelden een lege alt.', esc_html( $media['aantal'] ? $media['aantal'] . ' afbeelding(en) zonder alt-tekst. Decoratief? Dan is dat goed, anders aanvullen.' : 'Alle afbeeldingen hebben een alt-tekst.' ), 0 === $media['aantal'] ),
		array( 'Beperkte beweging wordt gerespecteerd.', esc_html( $beweging_uitleg ), $beweging ),
		array( 'De taal van de site is ingesteld (' . $taal . ').', esc_html( 'Nu: ' . $locale . '. Instellingen > Algemeen > Taal van de site; dit bepaalt lang="" en de voorleesstem.' ), 0 === strpos( strtolower( $locale ), strtolower( $taal ) ) ),
		array( 'Eén h1 per pagina.', $h1_uitleg, $zonder_h1 ? false : true ),
		array( 'Focus is altijd zichtbaar.', 'Tab door een pagina: elk element dat focus krijgt heeft een duidelijke rand, ook op donkere vlakken.', null ),
		array( 'Klikdoelen zijn minimaal ' . powerhub_toegankelijkheid_getal( $inst['klikdoel'] ) . ' bij ' . powerhub_toegankelijkheid_getal( $inst['klikdoel'] ) . 'px.', 'Knoppen, iconen, menu-items en sliderknoppen. Links in lopende tekst zijn uitgezonderd. De paginacontrole meet dit per pagina.', null ),
		array( 'Linkteksten zeggen waar je heen gaat.', 'Geen losse "Lees meer" of "Klik hier": geef context met een aria-label of verborgen tekst.', null ),
		array( 'Video laadt pas na een klik.', 'YouTube via youtube-nocookie.com, nooit automatisch met geluid; bewegende loops met pauzeknop.', null ),
		array( 'Alles werkt met het toetsenbord.', 'Menu, tabs, accordeons, sliders en formulieren: bedienbaar met Tab, Enter, spatie en pijltjes, zonder dat de focus vastloopt.', null ),
	);
	?>
	<div class="toegankelijkheid-onderdeel">
		<h3 class="toegankelijkheid-kop">Controlelijst bruikbaarheid</h3>
		<ul class="toegankelijkheid-lijst">
			<?php foreach ( $regels as $r ) : ?>
				<li>
					<?php echo powerhub_toegankelijkheid_status( $r[2], 'Goed', 'Actie nodig' ); // phpcs:ignore ?>
					<div>
						<strong><?php echo esc_html( $r[0] ); ?></strong>
						<span><?php echo wp_kses( $r[1], array( 'a' => array( 'href' => true ) ) ); ?></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * De tab in Powerhub: rapport onder de velden.
 */
function powerhub_toegankelijkheid_tab() {
	$tekst    = powerhub_toegankelijkheid_tekstgroottes();
	$contrast = powerhub_toegankelijkheid_contrast();
	$media    = powerhub_toegankelijkheid_media();
	?>
	<div class="toegankelijkheid" id="toegankelijkheid-rapport">
		<h2 class="toegankelijkheid-titel">Rapport</h2>
		<p class="toegankelijkheid-uitleg">Wat het designsysteem garandeert. Of een pagina in de praktijk klopt, zie je met de paginacontrole: knop Toegankelijkheid in de adminbalk op de voorkant<?php echo powerhub_aan( 'toegankelijk_paginacontrole' ) ? '' : ' (nu uitgeschakeld)'; ?>.</p>
		<?php if ( powerhub_aan( 'toegankelijk_paginacontrole' ) ) : ?>
			<p><a class="button" href="<?php echo esc_url( add_query_arg( 'toegankelijkheid', '1', home_url( '/' ) ) ); ?>" target="_blank" rel="noopener">Controleer de homepage<span class="screen-reader-text"> (opent in nieuw venster)</span></a></p>
		<?php endif; ?>
		<?php
		powerhub_toegankelijkheid_controlelijst( $tekst, $contrast, $media );
		powerhub_toegankelijkheid_rapport_tekst( $tekst );
		powerhub_toegankelijkheid_rapport_contrast( $contrast );
		?>
		<div class="toegankelijkheid-onderdeel">
			<h3 class="toegankelijkheid-kop">Media zonder alt-tekst</h3>
			<?php if ( ! $media['aantal'] ) : ?>
				<p class="toegankelijkheid-samenvatting"><span class="toegankelijkheid-teller is-goed">Alle afbeeldingen in de mediabibliotheek hebben een alt-tekst.</span></p>
			<?php else : ?>
				<p class="toegankelijkheid-samenvatting"><span class="toegankelijkheid-teller is-fout"><strong><?php echo esc_html( $media['aantal'] ); ?></strong> zonder alt-tekst</span></p>
				<p class="toegankelijkheid-uitleg">Puur decoratieve beelden mogen een lege alt hebben. Alle andere: beschrijf wat er te zien is, in één zin.<?php echo $media['aantal'] > 20 ? ' Hieronder de nieuwste 20.' : ''; ?></p>
				<ul class="toegankelijkheid-media">
					<?php foreach ( $media['ids'] as $id ) : ?>
						<li>
							<?php echo wp_get_attachment_image( $id, array( 48, 48 ), true, array( 'alt' => '' ) ); ?>
							<a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ?: basename( (string) get_attached_file( $id ) ) ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php powerhub_toegankelijkheid_kleurchecker(); ?>
	</div>
	<?php
}

/* --------------------------------------------------------------------------
 * Beheer: stijl en script
 * ----------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_powerhub' !== $hook || ! powerhub_is_beheerder() ) {
		return;
	}
	wp_enqueue_style( 'powerhub-toegankelijkheid', POWERHUB_URL . '/assets/toegankelijkheid.css', array( 'powerhub' ), powerhub_versie( '/assets/toegankelijkheid.css' ) );
	wp_enqueue_script( 'powerhub-toegankelijkheid', POWERHUB_URL . '/assets/toegankelijkheid.js', array(), powerhub_versie( '/assets/toegankelijkheid.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

/* --------------------------------------------------------------------------
 * Designsysteem: hoofdstuk Toegankelijkheid
 * ----------------------------------------------------------------------- */

add_filter( 'powerhub_designsysteem_hoofdstukken', function ( $hoofdstukken ) {
	$hoofdstukken['toegankelijkheid'] = 'Toegankelijkheid';
	return $hoofdstukken;
} );

add_action( 'powerhub_designsysteem_hoofdstuk_toegankelijkheid', function () {
	$inst = powerhub_toegankelijkheid_instellingen();
	?>
	<div class="toegankelijkheid">
		<p class="designsysteem-uitleg">Het palet en de tekststijlen getoetst aan WCAG 2.1 niveau <?php echo esc_html( $inst['niveau'] ); ?>. Kleinste tekst <?php echo esc_html( powerhub_toegankelijkheid_getal( $inst['kleinste'] ) ); ?>px, lopende tekst <?php echo esc_html( powerhub_toegankelijkheid_getal( $inst['lopend'] ) ); ?>px, klikdoelen <?php echo esc_html( powerhub_toegankelijkheid_getal( $inst['klikdoel'] ) ); ?>px. De grenzen staan in Powerhub, tab Toegankelijkheid.</p>
		<?php
		powerhub_toegankelijkheid_rapport_tekst();
		powerhub_toegankelijkheid_rapport_contrast();
		powerhub_toegankelijkheid_kleurchecker();
		?>
	</div>
	<?php
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'powerhub_is_designsysteem' ) && powerhub_is_designsysteem() && is_user_logged_in() ) {
		wp_enqueue_style( 'powerhub-toegankelijkheid', POWERHUB_URL . '/assets/toegankelijkheid.css', array(), powerhub_versie( '/assets/toegankelijkheid.css' ) );
		wp_enqueue_script( 'powerhub-toegankelijkheid', POWERHUB_URL . '/assets/toegankelijkheid.js', array(), powerhub_versie( '/assets/toegankelijkheid.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
}, 45 );

/* --------------------------------------------------------------------------
 * Voorkant: paginacontrole voor beheerders
 * ----------------------------------------------------------------------- */

function powerhub_paginacontrole_actief() {
	return ! is_admin() && is_user_logged_in() && powerhub_is_beheerder() && powerhub_aan( 'toegankelijk_paginacontrole' );
}

function powerhub_paginacontrole_direct() {
	return isset( $_GET['toegankelijkheid'] ) && '1' === $_GET['toegankelijkheid']; // phpcs:ignore WordPress.Security.NonceVerification
}

add_action( 'admin_bar_menu', function ( $balk ) {
	if ( ! powerhub_paginacontrole_actief() ) {
		return;
	}
	$balk->add_node( array(
		'id'    => 'powerhub-toegankelijkheid',
		'title' => 'Toegankelijkheid',
		'href'  => add_query_arg( 'toegankelijkheid', '1' ),
		'meta'  => array( 'title' => 'Controleer deze pagina op toegankelijkheid' ),
	) );
}, 91 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! powerhub_paginacontrole_actief() ) {
		return;
	}
	$inst   = powerhub_toegankelijkheid_instellingen();
	$config = array(
		'kleinste' => $inst['kleinste'],
		'lopend'   => $inst['lopend'],
		'niveau'   => $inst['niveau'],
		'klikdoel' => $inst['klikdoel'],
		'uitzonderingen' => (object) powerhub_toegankelijkheid_uitzonderingen(),
		/* Alleen dit deel van de pagina controleren (CSS-selector). Op /designsysteem/: de voorbeelden, niet de documentatie. */
		'bereik'   => (string) apply_filters( 'powerhub_paginacontrole_bereik', function_exists( 'powerhub_is_designsysteem' ) && powerhub_is_designsysteem() ? '.designsysteem-podium, .designsysteem-blok-podium' : '' ),
		'css'      => POWERHUB_URL . '/assets/toegankelijkheid-controle.css?ver=' . rawurlencode( powerhub_versie( '/assets/toegankelijkheid-controle.css' ) ),
		'js'       => POWERHUB_URL . '/assets/toegankelijkheid-controle.js?ver=' . rawurlencode( powerhub_versie( '/assets/toegankelijkheid-controle.js' ) ),
	);
	if ( powerhub_paginacontrole_direct() ) {
		wp_enqueue_style( 'powerhub-toegankelijkheid-controle', POWERHUB_URL . '/assets/toegankelijkheid-controle.css', array(), powerhub_versie( '/assets/toegankelijkheid-controle.css' ) );
		wp_enqueue_script( 'powerhub-toegankelijkheid-controle', POWERHUB_URL . '/assets/toegankelijkheid-controle.js', array(), powerhub_versie( '/assets/toegankelijkheid-controle.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
	/* Kleine lader: het script zelf komt pas na een klik op het adminbalk-item. */
	wp_register_script( 'powerhub-toegankelijkheid-lader', false, array(), POWERHUB_VERSIE, true );
	wp_enqueue_script( 'powerhub-toegankelijkheid-lader' );
	wp_add_inline_script( 'powerhub-toegankelijkheid-lader', 'window.powerhubToegankelijkheid=' . wp_json_encode( $config ) . ';'
		. '(function(c){document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("#wp-admin-bar-powerhub-toegankelijkheid > a");if(!a)return;e.preventDefault();'
		. 'if(window.powerhubControle){window.powerhubControle.open();return;}if(c.laden)return;c.laden=true;'
		. 'var l=document.createElement("link");l.rel="stylesheet";l.href=c.css;document.head.appendChild(l);'
		. 'var s=document.createElement("script");s.src=c.js;s.onload=function(){window.powerhubControle&&window.powerhubControle.open();};document.body.appendChild(s);});}(window.powerhubToegankelijkheid));' );
}, 50 );
