<?php
/**
 * Projectinstellingen voor Powerhub.
 * Bron van waarheid is designsysteem/tokens.json; hier staat alleen wat Powerhub daarmee mag.
 * Zie powerhub/README.md voor alle mogelijkheden.
 */

defined( 'ABSPATH' ) || exit;

/* Kleuren die in Powerhub overschreven mogen worden. Standaard (null) komt uit tokens.json. */
add_filter( 'powerhub_kleuren', function () {
	return array(
		'donker-800' => array( 'Donker (structuur)', null ),
		'actie-500'  => array( 'Actie', null ),
		'actie-600'  => array( 'Actie, hover', null ),
		'accent-500' => array( 'Accent', null ),
		'grijs-100'  => array( 'Paginavlak', null ),
	);
} );

/*
 * Lettertypes, lokaal in assets/fonts (woff2). De families staan in tokens.json (letter.*);
 * hier alleen de bestanden. Zonder lettertypes gebruikt de site de systeemletters uit tokens.json.
 *
 * add_filter( 'powerhub_fonts', function () {
 *     return array(
 *         'naam' => array(
 *             'naam'      => 'Lettertype',
 *             'fallback'  => 'system-ui, sans-serif',
 *             'rollen'    => array( 'kop', 'tekst', 'accent' ),
 *             'bestanden' => array(
 *                 array( 'bestand' => 'lettertype-400.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
 *             ),
 *         ),
 *     );
 * } );
 * add_filter( 'powerhub_font_standaard', fn() => array( 'kop' => 'naam', 'tekst' => 'naam', 'accent' => 'naam' ) );
 */

/* Sectie-achtergronden: label, kleurtoken en of het vlak donker is (dan wisselen tekst, links en focus naar de lichte varianten). Redacteuren kunnen ook twee vlakken 50/50 combineren. */
add_filter( 'powerhub_achtergronden', function () {
	return array(
		'pagina'     => array( 'label' => 'Lichtgrijs', 'kleur' => 'kleur.vlak-pagina' ),
		'wit'        => array( 'label' => 'Wit', 'kleur' => 'kleur.vlak-kaart' ),
		'donker'     => array( 'label' => 'Donker', 'kleur' => 'kleur.vlak-donker', 'donker' => true ),
		'tint'       => array( 'label' => 'Tint', 'kleur' => 'kleur.vlak-tint' ),
		'tint-zacht' => array( 'label' => 'Tint zacht', 'kleur' => 'kleur.vlak-tint-zacht' ),
	);
} );

/* Standaardwaarden overschrijven. */
add_filter( 'powerhub_standaardwaarden', function ( $standaard ) {
	return array_merge( $standaard, array(
		'hoofdknop_tekst' => 'Neem contact op',
		'hoofdknop_link'  => '/contact/',
	) );
} );

/* De pijl staat met CSS achter elke knop (.knop::after); geen tweede pijl uit Powerhub. */
add_filter( 'powerhub_knop_pijl', '__return_empty_string' );
