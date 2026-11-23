<?php
/**
 * Hulpfuncties van TAL Leadership, bovenop die van Powerhub.
 */

defined( 'ABSPATH' ) || exit;

/* Uitleg bij kopvelden: TAL gebruikt geen cursief accent maar een stiftstreep. */
define( 'TAL_KOP_UITLEG', 'Zet *sterretjes* om woorden voor de koraal stiftstreep. &lt;br&gt; is een nieuwe regel.' );

/* Contact. Alle knoppen op de site gaan naar de contactpagina; Plan een kennismaking gaat direct naar de agenda daarop. */
const TAL_CALENDLY         = 'https://calendly.com/chantal-tal-leadership/30min';
const TAL_CONTACT_URL      = '/contact/';
const TAL_KENNISMAKING_URL = '/contact/#kennismaking';

/**
 * Kop in de TAL-stijl: *woorden* krijgen de stiftstreep, <br> blijft een nieuwe regel.
 */
function tal_kop( $tekst, $tag = 'h2', $class = '' ) {
	$html = powerhub_kop( $tekst, $tag, $class );
	return str_replace( array( '<em class="accent">', '</em>' ), array( '<span class="onderstreep">', '</span>' ), $html );
}

/**
 * Lopende tekst met dezelfde stiftstreep, zonder koptag.
 */
function tal_regel( $tekst ) {
	$html = esc_html( (string) $tekst );
	$html = preg_replace( '/\*(.+?)\*/u', '<span class="onderstreep">$1</span>', $html );
	return str_replace( '&lt;br&gt;', '<br>', $html );
}

/**
 * Foto in een kader. Het beeld is iets hoger dan het kader, zodat het kan meebewegen bij scrollen.
 * $positie: verticale uitsnede in procenten (0 is boven, 100 is onder), of het hele blok voor de uitsnede (zie tal_acf_uitsnede).
 */
function tal_foto( $beeld, $maat = 'large', $class = '', $positie = 40, $attr = array() ) {
	if ( empty( $beeld ) ) {
		return '';
	}
	$attr = wp_parse_args( $attr, array(
		'class' => 'foto-beeld',
		'style' => tal_uitsnede_stijl( is_array( $positie ) ? $positie : array( 'beeld_positie' => $positie ) ),
		'sizes' => '(max-width: 860px) 100vw, 60vw',
	) );
	return '<div class="' . esc_attr( trim( 'foto ' . $class ) ) . '">' . powerhub_beeld( $beeld, $maat, $attr ) . '</div>';
}

/**
 * Tekstlink met de groeiende onderstreping.
 */
function tal_lijnlink( $link, $class = '' ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( trim( 'lijnlink ' . $class ) ),
		esc_url( $link['url'] ),
		! empty( $link['target'] ) ? ' target="_blank" rel="noopener"' : '',
		esc_html( $link['title'] ?? '' )
	);
}

/**
 * Lijniconen voor de resultaten. Eigen tekeningen, zodat ze bij het woordmerk passen.
 */
function tal_uitkomst_iconen() {
	return array(
		'zon'      => array( 'Opkomende zon', '<path d="M6 30h32"/><path d="M12 30a10 10 0 0 1 20 0"/><path d="M22 8v5M9.3 13.3l3.5 3.5M34.7 13.3l-3.5 3.5"/>' ),
		'cirkels'  => array( 'Twee cirkels', '<circle cx="16.5" cy="22" r="10"/><circle cx="27.5" cy="22" r="10"/>' ),
		'pijl'     => array( 'Stippen met pijl', '<circle cx="9" cy="22" r="3.5"/><circle cx="20" cy="22" r="3.5"/><path d="M27 22h11M33 16l6 6-6 6"/>' ),
		'golf'     => array( 'Golf omhoog', '<path d="M5 30c3-5 5-5 8 0s5 5 8 0l16-16"/><path d="M30 14h7v7"/>' ),
		'agenda'   => array( 'Agenda', '<rect x="7" y="7" width="30" height="30" rx="1"/><path d="M7 17h30M17 17v20"/><path d="M22 27h9"/>' ),
		'kringen'  => array( 'Kringen', '<circle cx="22" cy="22" r="3"/><circle cx="22" cy="22" r="9.5"/><circle cx="22" cy="22" r="16" stroke-dasharray="2 4"/>' ),
	);
}

function tal_uitkomst_icoon( $naam ) {
	$iconen = tal_uitkomst_iconen();
	if ( ! isset( $iconen[ $naam ] ) ) {
		$naam = 'zon';
	}
	return '<svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $iconen[ $naam ][1] . '</svg>';
}

/**
 * Pad of URL van een meegeleverd beeld in assets/beelden (voor de voorbeeldinhoud).
 */
function tal_beeld_url( $bestand ) {
	return THEMA_URL . '/assets/beelden/' . rawurlencode( $bestand );
}

/**
 * ACF-herhaler kort opschrijven. $velden: naam => array( label, type, extra ).
 */
function tal_acf_lijst( $prefix, $naam, $label, $velden, $extra = array() ) {
	$sub = array();
	foreach ( $velden as $veld => $def ) {
		$sub[] = powerhub_acf_veld( $prefix . '_' . $naam, $veld, $def[0], $def[1], $def[2] ?? array() );
	}
	return powerhub_acf_veld( $prefix, $naam, $label, 'repeater', array_merge( array(
		'layout'       => 'block',
		'button_label' => 'Toevoegen',
		'sub_fields'   => $sub,
	), $extra ) );
}

/**
 * Beeldveld met de standaardinstellingen van het thema.
 */
function tal_acf_beeld( $prefix, $naam, $label, $extra = array() ) {
	return powerhub_acf_veld( $prefix, $naam, $label, 'image', array_merge( array( 'return_format' => 'id', 'preview_size' => 'medium' ), $extra ) );
}

/**
 * Uitsnede van een beeld: horizontaal en verticaal, met een eigen uitsnede voor mobiel (leeg is gelijk aan desktop).
 * Velden: beeld_x, beeld_positie (verticaal, bestaande naam), beeld_x_mobiel, beeld_y_mobiel.
 */
function tal_acf_uitsnede( $prefix, $standaard_y = 40, $standaard_x = 50, $voor = '' ) {
	$getal = function ( $naam, $label, $standaard, $uitleg, $groep = 'veld-groep' ) use ( $prefix, $voor ) {
		return powerhub_acf_veld( $prefix, $naam, $voor ? $voor . ': ' . lcfirst( $label ) : $label, 'number', array_merge( powerhub_acf_breed( 25, $groep ), array(
			'min'           => 0,
			'max'           => 100,
			'step'          => 1,
			'append'        => '%',
			'default_value' => $standaard,
			'instructions'  => $uitleg,
		) ) );
	};
	return array(
		$getal( 'beeld_x', 'Uitsnede horizontaal', $standaard_x, '0 is links, 100 is rechts.', 'veld-groep groep-begin' ),
		$getal( 'beeld_positie', 'Uitsnede verticaal', $standaard_y, '0 is boven, 100 is onder.' ),
		$getal( 'beeld_x_mobiel', 'Mobiel horizontaal', '', 'Leeg: gelijk aan desktop.' ),
		$getal( 'beeld_y_mobiel', 'Mobiel verticaal', '', 'Leeg: gelijk aan desktop.', 'veld-groep groep-eind' ),
	);
}

/**
 * Beeldveld op volle breedte met de vier uitsnede-velden er direct onder (één rij van vier).
 */
function tal_acf_beeld_uitsnede( $prefix, $label, $standaard_y = 40, $standaard_x = 50, $extra = array() ) {
	return array_merge( array( tal_acf_beeld( $prefix, 'beeld', $label, $extra ) ), tal_acf_uitsnede( $prefix, $standaard_y, $standaard_x ) );
}

/**
 * Inline stijl met de uitsnede als CSS-variabelen (--x, --y, --x-mobiel, --y-mobiel). De CSS kiest per schermbreedte.
 */
function tal_uitsnede_stijl( $blok, $standaard_x = 50, $standaard_y = 40 ) {
	$waarde = function ( $sleutel, $anders ) use ( $blok ) {
		return isset( $blok[ $sleutel ] ) && '' !== $blok[ $sleutel ] && null !== $blok[ $sleutel ] ? max( 0, min( 100, (int) $blok[ $sleutel ] ) ) : $anders;
	};
	$x  = $waarde( 'beeld_x', $standaard_x );
	$y  = $waarde( 'beeld_positie', $standaard_y );
	$xm = $waarde( 'beeld_x_mobiel', $x );
	$ym = $waarde( 'beeld_y_mobiel', $y );
	return sprintf( '--x: %d%%; --y: %d%%; --x-mobiel: %d%%; --y-mobiel: %d%%;', $x, $y, $xm, $ym );
}

/**
 * Oude enkele schuif voor de verticale uitsnede (niet meer in gebruik in de blokken).
 */
function tal_acf_positie( $prefix, $naam = 'beeld_positie', $standaard = 40 ) {
	return powerhub_acf_veld( $prefix, $naam, 'Uitsnede (verticaal)', 'range', array_merge( powerhub_acf_breed( 50 ), array(
		'min'           => 0,
		'max'           => 100,
		'step'          => 1,
		'append'        => '%',
		'default_value' => $standaard,
		'instructions'  => '0 toont de bovenkant van de foto, 100 de onderkant.',
	) ) );
}

/**
 * Tekst uit een tekstvak als losse alinea's (elke lege regel is een nieuwe alinea).
 */
function tal_alineas( $tekst, $class = '' ) {
	$uit = '';
	foreach ( preg_split( '/\R{2,}/', trim( (string) $tekst ) ) as $alinea ) {
		if ( '' !== trim( $alinea ) ) {
			$uit .= '<p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . nl2br( esc_html( trim( $alinea ) ) ) . '</p>';
		}
	}
	return $uit;
}

/**
 * Anker in de link (bijvoorbeeld /contact/#kennismaking): vóór de pagina opbouwt uit de adresbalk halen,
 * zodat de browser niet direct naar beneden springt. thema.js begint bovenaan en scrolt daarna vloeiend naar het anker.
 */
add_action( 'wp_head', function () {
	if ( is_admin() || powerhub_is_designsysteem() ) {
		return;
	}
	echo "<script>(function(){var h=location.hash;if(!h||h.length<2||!history.replaceState)return;window.talStartAnker=decodeURIComponent(h.slice(1));if('scrollRestoration' in history)history.scrollRestoration='manual';history.replaceState(null,'',location.pathname+location.search);})();</script>\n";
}, 1 );
