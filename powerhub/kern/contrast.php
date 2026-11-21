<?php
/**
 * Contrast volgens WCAG 2.1 (relatieve luminantie). Zonder WordPress bruikbaar.
 *
 * Eisen: AA normale tekst 4.5, AA grote tekst (vanaf 24px, of 18.66px vet) 3.0,
 * AAA normale tekst 7.0, AAA grote tekst 4.5, niet-tekst (iconen, randen van invoervelden) 3.0.
 */

/**
 * Kleur naar array( r, g, b, a ) met waarden 0..255 en alfa 0..1. Begrijpt #rgb, #rrggbb, #rrggbbaa en rgb()/rgba().
 */
function powerhub_kleur_rgb( $kleur ) {
	$kleur = strtolower( trim( (string) $kleur ) );
	if ( preg_match( '/^#([0-9a-f]{3,8})$/', $kleur, $m ) ) {
		$hex = $m[1];
		if ( 3 === strlen( $hex ) || 4 === strlen( $hex ) ) {
			$hex = preg_replace( '/(.)/', '$1$1', $hex );
		}
		$a = 8 === strlen( $hex ) ? hexdec( substr( $hex, 6, 2 ) ) / 255 : 1;
		return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ), $a );
	}
	if ( preg_match( '/^rgba?\(\s*([\d.]+)[\s,]+([\d.]+)[\s,]+([\d.]+)(?:[\s,\/]+([\d.]+%?))?\s*\)$/', $kleur, $m ) ) {
		$a = isset( $m[4] ) ? ( str_ends_with( $m[4], '%' ) ? (float) $m[4] / 100 : (float) $m[4] ) : 1;
		return array( (float) $m[1], (float) $m[2], (float) $m[3], $a );
	}
	if ( 'transparent' === $kleur ) {
		return array( 0, 0, 0, 0 );
	}
	return null;
}

/**
 * Een (half)transparante kleur over een ondergrond leggen.
 */
function powerhub_kleur_mengen( $boven, $onder ) {
	$a = $boven[3];
	return array(
		$boven[0] * $a + $onder[0] * ( 1 - $a ),
		$boven[1] * $a + $onder[1] * ( 1 - $a ),
		$boven[2] * $a + $onder[2] * ( 1 - $a ),
		1,
	);
}

function powerhub_luminantie( $rgb ) {
	$kanalen = array();
	foreach ( array_slice( $rgb, 0, 3 ) as $c ) {
		$c          = $c / 255;
		$kanalen[] = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $kanalen[0] + 0.7152 * $kanalen[1] + 0.0722 * $kanalen[2];
}

/**
 * Contrastverhouding tussen voorgrond en achtergrond (1 tot 21). Null als een kleur onleesbaar is.
 */
function powerhub_contrast( $voor, $achter ) {
	$v = powerhub_kleur_rgb( $voor );
	$a = powerhub_kleur_rgb( $achter );
	if ( ! $v || ! $a ) {
		return null;
	}
	$a = $a[3] < 1 ? powerhub_kleur_mengen( $a, array( 255, 255, 255, 1 ) ) : $a;
	$v = $v[3] < 1 ? powerhub_kleur_mengen( $v, $a ) : $v;
	$l1 = powerhub_luminantie( $v );
	$l2 = powerhub_luminantie( $a );
	return round( ( max( $l1, $l2 ) + 0.05 ) / ( min( $l1, $l2 ) + 0.05 ), 2 );
}

/**
 * Minimale verhouding per soort en niveau.
 * Soort: normaal (lopende tekst), groot (koppen vanaf 24px of 18.66px vet), ui (iconen, randen, focus).
 */
function powerhub_contrast_eis( $soort = 'normaal', $niveau = 'AA' ) {
	$eisen = array(
		'AA'  => array( 'normaal' => 4.5, 'groot' => 3.0, 'ui' => 3.0 ),
		'AAA' => array( 'normaal' => 7.0, 'groot' => 4.5, 'ui' => 3.0 ),
	);
	return $eisen[ $niveau ][ $soort ] ?? 4.5;
}

/**
 * Is de lichtste van twee kleuren donker genoeg voor witte tekst? Handig om tekstkleur te kiezen.
 */
function powerhub_is_donker( $kleur ) {
	$rgb = powerhub_kleur_rgb( $kleur );
	return $rgb ? powerhub_contrast( '#ffffff', $kleur ) >= powerhub_contrast( '#000000', $kleur ) : false;
}
