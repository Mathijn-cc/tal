<?php
/**
 * Module Designsysteem: de bron van waarheid van het thema, zichtbaar op /designsysteem/ (alleen ingelogd).
 *
 * In het thema:
 *   designsysteem/tokens.json         alle tokens (DTCG). assets/css/tokens.css wordt hieruit gegenereerd.
 *   designsysteem/componenten/*.php   één voorbeeld per component; de bron wordt naast het voorbeeld getoond.
 *   designsysteem/regels.php          gebruiksregels (kleur, typografie, beweging, content).
 *   filter powerhub_blok_voorbeelden  voorbeeldinhoud per blok, zodat elk blok op de pagina verschijnt.
 *
 * De pagina gebruikt de echte CSS en blokken van het thema. Wat je daar ziet, staat zo op de site.
 */

defined( 'ABSPATH' ) || exit;

require_once POWERHUB_PAD . '/kern/tokens.php';

function powerhub_designsysteem_map() {
	return get_template_directory() . '/designsysteem';
}

function powerhub_designsysteem_pad() {
	return trim( (string) apply_filters( 'powerhub_designsysteem_pad', 'designsysteem' ), '/' );
}

function powerhub_designsysteem_url() {
	return home_url( '/' . powerhub_designsysteem_pad() . '/' );
}

/**
 * Tokens uit het thema, één keer per verzoek gelezen.
 */
function powerhub_tokens() {
	static $tokens = null;
	if ( null === $tokens ) {
		$tokens = powerhub_tokens_lezen( powerhub_designsysteem_map() . '/tokens.json' );
	}
	return $tokens;
}

/**
 * De uiteindelijke waarde van een token: powerhub_token( 'kleur.lime-500' ) geeft #a0d911.
 */
function powerhub_token( $pad, $anders = '' ) {
	$tokens = powerhub_tokens();
	return isset( $tokens[ $pad ] ) ? powerhub_token_oplossen( $tokens[ $pad ]['waarde'], $tokens ) : $anders;
}

/* tokens.css altijd als eerste stijl, zodat het thema erop kan bouwen (afhankelijkheid 'tokens'). */
add_action( 'wp_enqueue_scripts', function () {
	$bestand = get_template_directory() . '/assets/css/tokens.css';
	if ( file_exists( $bestand ) ) {
		wp_register_style( 'tokens', get_template_directory_uri() . '/assets/css/tokens.css', array(), defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : POWERHUB_VERSIE );
	}
}, 1 );

/**
 * Is dit verzoek de designsysteempagina?
 */
function powerhub_is_designsysteem() {
	$pad  = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$thuis = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$pad  = $thuis && 0 === strpos( $pad, $thuis ) ? trim( substr( $pad, strlen( $thuis ) ), '/' ) : $pad;
	return powerhub_designsysteem_pad() === $pad;
}

add_action( 'template_redirect', function () {
	if ( ! powerhub_is_designsysteem() ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		auth_redirect();
	}
	status_header( 200 );
	nocache_headers();
	add_filter( 'wp_robots', 'wp_robots_no_robots' );
	add_filter( 'pre_get_document_title', function () {
		return 'Designsysteem · ' . get_bloginfo( 'name' );
	} );
	add_filter( 'body_class', function ( $classes ) {
		return array_diff( $classes, array( 'error404' ) );
	} );
	add_action( 'wp_enqueue_scripts', function () {
		powerhub_blok_assets( array_keys( powerhub_blok_layouts() ) );
		wp_enqueue_style( 'powerhub-designsysteem', POWERHUB_URL . '/assets/designsysteem.css', array(), POWERHUB_VERSIE );
		wp_enqueue_script( 'powerhub-designsysteem', POWERHUB_URL . '/assets/designsysteem.js', array(), POWERHUB_VERSIE, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}, 40 );

	$sjabloon = locate_template( 'designsysteem/pagina.php' );
	include $sjabloon ? $sjabloon : POWERHUB_PAD . '/designsysteem/pagina.php';
	exit;
}, 0 );

/* Snelkoppelingen: adminbalk en Powerhub. */
add_action( 'admin_bar_menu', function ( $balk ) {
	if ( is_user_logged_in() && file_exists( powerhub_designsysteem_map() . '/tokens.json' ) ) {
		$balk->add_node( array(
			'id'    => 'powerhub-designsysteem',
			'title' => 'Designsysteem',
			'href'  => powerhub_designsysteem_url(),
		) );
	}
}, 90 );

powerhub_tab( 'designsysteem', array(
	'titel'    => 'Designsysteem',
	'uitleg'   => 'De bron van waarheid van het thema',
	'niveau'   => 'klant',
	'volgorde' => 15,
	'weergave' => 'powerhub_designsysteem_tab',
) );

function powerhub_designsysteem_tab() {
	$bestand = powerhub_designsysteem_map() . '/tokens.json';
	if ( ! file_exists( $bestand ) ) {
		echo '<p>Dit thema heeft nog geen designsysteem. Maak <code>designsysteem/tokens.json</code> aan (zie powerhub/README.md).</p>';
		return;
	}
	$tokens = powerhub_tokens();
	$aantal = array();
	foreach ( $tokens as $t ) {
		$aantal[ $t['groep'] ] = ( $aantal[ $t['groep'] ] ?? 0 ) + 1;
	}
	$componenten = glob( powerhub_designsysteem_map() . '/componenten/*.php' ) ?: array();
	?>
	<p>Alle kleuren, letters, maten en componenten van de site staan op één pagina. Die pagina gebruikt de echte stijlen van het thema, dus wat je daar ziet staat zo op de site.</p>
	<p><a class="button button-primary" href="<?php echo esc_url( powerhub_designsysteem_url() ); ?>" target="_blank" rel="noopener">Designsysteem openen</a></p>
	<table class="powerhub-systeem">
		<tbody>
			<tr><th scope="row">Link</th><td><code><?php echo esc_html( powerhub_designsysteem_url() ); ?></code> (alleen voor ingelogde gebruikers)</td></tr>
			<tr><th scope="row">Tokens</th><td><?php echo esc_html( count( $tokens ) ); ?> (<?php echo esc_html( implode( ', ', array_map( function ( $g, $n ) { return $g . ' ' . $n; }, array_keys( $aantal ), $aantal ) ) ); ?>)</td></tr>
			<tr><th scope="row">Componenten</th><td><?php echo esc_html( count( $componenten ) ); ?></td></tr>
			<tr><th scope="row">Laatst gewijzigd</th><td><?php echo esc_html( wp_date( 'j F Y, H:i', filemtime( $bestand ) ) ); ?></td></tr>
		</tbody>
	</table>
	<?php
}

/**
 * Hulp voor de pagina: naam en uitleg van een componentbestand uit de eerste docblock.
 */
function powerhub_designsysteem_component_info( $bestand ) {
	$bron = (string) file_get_contents( $bestand ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$info = array( 'naam' => ucfirst( str_replace( '-', ' ', basename( $bestand, '.php' ) ) ), 'uitleg' => '', 'bron' => $bron );
	if ( preg_match( '#/\*\*(.*?)\*/#s', $bron, $m ) ) {
		$regels = array_values( array_filter( array_map( function ( $r ) {
			return trim( ltrim( trim( $r ), '*' ) );
		}, explode( "\n", $m[1] ) ) ) );
		if ( $regels ) {
			$info['naam']   = rtrim( preg_replace( '/^Component:\s*/i', '', array_shift( $regels ) ), '.' );
			$info['uitleg'] = implode( ' ', $regels );
		}
		/* Toon in de bron alleen de markup, zonder de docblock en de PHP-kop. */
		$info['bron'] = trim( preg_replace( '#^<\?php.*?\?>\s*#s', '', $bron ) );
	}
	return $info;
}
