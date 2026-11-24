<?php
/**
 * Module Typografie: lokaal gehoste lettertypes kiezen voor kop, tekst en accent.
 * Nooit van Google of een ander extern domein geladen (AVG en snelheid).
 *
 * Het project geeft de beschikbare lettertypes op, met bestanden in assets/fonts van het thema:
 *   add_filter( 'powerhub_fonts', function () {
 *       return array(
 *           'barlow' => array(
 *               'naam'      => 'Barlow',
 *               'fallback'  => 'system-ui, sans-serif',
 *               'rollen'    => array( 'kop', 'tekst' ),
 *               'bestanden' => array(
 *                   array( 'bestand' => 'barlow-400.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
 *               ),
 *           ),
 *       );
 *   } );
 *   add_filter( 'powerhub_font_standaard', fn() => array( 'kop' => 'barlow', 'tekst' => 'barlow', 'accent' => 'barlow' ) );
 *
 * Uitvoer: @font-face voor de gekozen lettertypes en de variabelen --letter-kop, --letter-tekst, --letter-accent.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_fonts() {
	return (array) apply_filters( 'powerhub_fonts', array() );
}

function powerhub_font_rollen() {
	/* Extra rollen (bijv. 'smal') via de filter; elke rol geeft --letter-<rol>. */
	return (array) apply_filters( 'powerhub_font_rollen', array(
		'kop'    => 'Koppen',
		'tekst'  => 'Lopende tekst',
		'accent' => 'Accent (cursieve woorden in koppen)',
	) );
}

add_action( 'powerhub_registreren', function () {
	$fonts = powerhub_fonts();
	if ( ! $fonts && ! function_exists( 'powerhub_lettertypes_beheer' ) ) {
		return;
	}
	$standaard = (array) apply_filters( 'powerhub_font_standaard', array() );
	$velden    = array();
	foreach ( powerhub_font_rollen() as $rol => $label ) {
		$keuzes = array();
		foreach ( $fonts as $sleutel => $font ) {
			if ( in_array( $rol, (array) ( $font['rollen'] ?? array_keys( powerhub_font_rollen() ) ), true ) ) {
				$keuzes[ $sleutel ] = $font['naam'];
			}
		}
		if ( ! $keuzes ) {
			continue;
		}
		$velden[ 'font_' . $rol ] = array(
			'type'      => 'keuze',
			'label'     => $label,
			'keuzes'    => $keuzes,
			'standaard' => isset( $standaard[ $rol ], $keuzes[ $standaard[ $rol ] ] ) ? $standaard[ $rol ] : array_key_first( $keuzes ),
		);
	}
	$velden['font_voorladen'] = array(
		'type'      => 'schakel',
		'label'     => 'Lettertypes voorladen',
		'uitleg'    => 'Laadt het kop- en tekstlettertype direct mee, tegen verspringende tekst.',
		'standaard' => true,
	);

	powerhub_tab( 'typografie', array(
		'titel'    => 'Typografie',
		'uitleg'   => 'Lettertypes voor kop, tekst en accent',
		'niveau'   => 'beheer',
		'volgorde' => 40,
		'velden'   => $velden,
		/* Module Lettertypes: bibliotheek en eigen lettertypes onder de keuzes. */
		'weergave' => function_exists( 'powerhub_lettertypes_beheer' ) ? 'powerhub_lettertypes_beheer' : null,
	) );
} );

/**
 * De lettertypes die nu gekozen zijn, per rol.
 */
function powerhub_gekozen_fonts() {
	$fonts   = powerhub_fonts();
	$gekozen = array();
	foreach ( array_keys( powerhub_font_rollen() ) as $rol ) {
		$sleutel = powerhub( 'font_' . $rol );
		if ( $sleutel && isset( $fonts[ $sleutel ] ) ) {
			$gekozen[ $rol ] = $sleutel;
		}
	}
	return $gekozen;
}

/**
 * Waarde veilig in CSS: alles weg wat een declaratie of de style-tag kan breken. Aanhalingstekens blijven.
 */
function powerhub_css_waarde( $waarde ) {
	return trim( preg_replace( '/[<>{};\\\\]/', '', (string) $waarde ) );
}

/**
 * Adres van een lettertypebestand. Gewoon een bestandsnaam: assets/fonts van het thema.
 * 'uploads:pad' staat in de uploads (module Lettertypes), 'bijlage:123' is een bestand uit de mediabibliotheek.
 * Zo blijven de adressen kloppen als de site naar een ander domein verhuist.
 */
function powerhub_font_url( $bestand ) {
	$bestand = (string) $bestand;
	if ( 0 === strpos( $bestand, 'uploads:' ) ) {
		$basis = wp_upload_dir();
		return trailingslashit( $basis['baseurl'] ) . implode( '/', array_map( 'rawurlencode', explode( '/', substr( $bestand, 8 ) ) ) );
	}
	if ( 0 === strpos( $bestand, 'bijlage:' ) ) {
		return (string) wp_get_attachment_url( absint( substr( $bestand, 8 ) ) );
	}
	return get_template_directory_uri() . '/assets/fonts/' . rawurlencode( $bestand );
}

/**
 * Eén @font-face-regel voor een bestand van een lettertype.
 */
function powerhub_font_face( $naam, $b ) {
	$formaat = 'woff' === ( $b['formaat'] ?? '' ) ? 'woff' : 'woff2';
	$bereik  = ! empty( $b['unicode'] ) && preg_match( '/^[uU0-9a-fA-F+?\-, ]+$/', $b['unicode'] ) ? ';unicode-range:' . $b['unicode'] : '';
	return sprintf(
		"@font-face{font-family:'%s';src:url('%s') format('%s');font-weight:%s;font-style:%s;font-display:swap%s;}",
		powerhub_css_waarde( str_replace( "'", '', $naam ) ),
		esc_url( powerhub_font_url( $b['bestand'] ) ),
		$formaat,
		powerhub_css_waarde( $b['gewicht'] ?? '400' ),
		powerhub_css_waarde( $b['stijl'] ?? 'normal' ),
		$bereik
	);
}

function powerhub_typografie_css() {
	$fonts   = powerhub_fonts();
	$gekozen = powerhub_gekozen_fonts();
	if ( ! $gekozen ) {
		return '';
	}
	$css = '';
	foreach ( array_unique( $gekozen ) as $sleutel ) {
		foreach ( (array) $fonts[ $sleutel ]['bestanden'] as $b ) {
			$css .= powerhub_font_face( $fonts[ $sleutel ]['naam'], $b );
		}
	}
	$variabelen = array();
	foreach ( $gekozen as $rol => $sleutel ) {
		$variabelen[] = sprintf( "--letter-%s:'%s',%s", sanitize_key( $rol ), powerhub_css_waarde( str_replace( "'", '', $fonts[ $sleutel ]['naam'] ) ), powerhub_css_waarde( $fonts[ $sleutel ]['fallback'] ?? 'sans-serif' ) );
	}
	return $css . ':root{' . implode( ';', $variabelen ) . '}';
}

add_action( 'wp_enqueue_scripts', function () {
	$css = powerhub_typografie_css();
	if ( $css ) {
		wp_add_inline_style( powerhub_stijl_handle(), $css );
	}
}, 30 );

/**
 * Voorladen: alleen het eerste (normale) bestand van het kop- en tekstlettertype.
 */
add_action( 'wp_head', function () {
	if ( ! powerhub_aan( 'font_voorladen' ) ) {
		return;
	}
	$fonts   = powerhub_fonts();
	$gekozen = powerhub_gekozen_fonts();
	$gedaan  = array();
	foreach ( array( 'kop', 'tekst' ) as $rol ) {
		if ( empty( $gekozen[ $rol ] ) ) {
			continue;
		}
		/* Per rol alleen het eerste normale bestand. Hetzelfde bestand niet twee keer. */
		foreach ( (array) $fonts[ $gekozen[ $rol ] ]['bestanden'] as $b ) {
			if ( 'normal' !== ( $b['stijl'] ?? 'normal' ) ) {
				continue;
			}
			if ( ! in_array( $b['bestand'], $gedaan, true ) ) {
				$gedaan[] = $b['bestand'];
				printf( '<link rel="preload" href="%s" as="font" type="font/%s" crossorigin>' . "\n", esc_url( powerhub_font_url( $b['bestand'] ) ), 'woff' === ( $b['formaat'] ?? '' ) ? 'woff' : 'woff2' );
			}
			break;
		}
	}
}, 2 );
