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
		'donker-800' => array( 'Inkt (structuur)', null ),
		'accent-500' => array( 'Roze', null ),
		'actie-500'  => array( 'Koraal (actie en stiftstreep)', null ),
		'grijs-100'  => array( 'Crème (paginavlak)', null ),
	);
} );

/* Lettertypes, lokaal in assets/fonts. DM Serif Display voor alle koppen, General Sans voor tekst. Kalnia staat alleen in het logo (thema.css). */
add_filter( 'powerhub_fonts', function () {
	return array(
		'dm-serif-display' => array(
			'naam'      => 'DM Serif Display',
			'fallback'  => 'Georgia, "Times New Roman", serif',
			'rollen'    => array( 'kop', 'accent' ),
			'bestanden' => array(
				array( 'bestand' => 'dm-serif-display-400.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
			),
		),
		'general-sans' => array(
			'naam'      => 'General Sans',
			'fallback'  => 'system-ui, -apple-system, "Segoe UI", sans-serif',
			'rollen'    => array( 'tekst' ),
			'bestanden' => array(
				array( 'bestand' => 'general-sans-variabel.woff2', 'gewicht' => '200 700', 'stijl' => 'normal' ),
				array( 'bestand' => 'general-sans-variabel-cursief.woff2', 'gewicht' => '200 700', 'stijl' => 'italic' ),
			),
		),
	);
} );
add_filter( 'powerhub_font_standaard', fn() => array( 'kop' => 'dm-serif-display', 'tekst' => 'general-sans', 'accent' => 'dm-serif-display' ) );

/* Sectie-achtergronden. Nooit meer dan twee achtergronden tegelijk in beeld. */
add_filter( 'powerhub_achtergronden', function () {
	return array(
		'pagina'     => array( 'label' => 'Crème', 'kleur' => 'kleur.vlak-pagina' ),
		'wit'        => array( 'label' => 'Wit', 'kleur' => 'kleur.vlak-kaart' ),
		'tint'       => array( 'label' => 'Blos', 'kleur' => 'kleur.vlak-tint' ),
		'tint-zacht' => array( 'label' => 'Steen', 'kleur' => 'kleur.vlak-tint-zacht' ),
		'donker'     => array( 'label' => 'Inkt', 'kleur' => 'kleur.vlak-donker', 'donker' => true ),
		'citaat'     => array( 'label' => 'Bordeaux', 'kleur' => 'kleur.vlak-citaat', 'donker' => true ),
	);
} );

/* Standaardwaarden: de kennismakingsknop en vloeiend scrollen staan meteen goed. */
add_filter( 'powerhub_standaardwaarden', function ( $standaard ) {
	return array_merge( $standaard, array(
		'hoofdknop_tekst'   => 'Plan een kennismaking',
		'hoofdknop_link'    => 'https://calendly.com/chantal-tal-leadership/30min',
		'email'             => 'chantal@tal-leadership.nl',
		'vloeiend_scrollen' => true,
	) );
} );

/* TAL-knoppen hebben geen pijl; de tekst rolt bij hover (thema.css). */
add_filter( 'powerhub_knop_pijl', '__return_empty_string' );

/* Zolang er in Powerhub geen logo is gekozen: het woordmerk, TAL in de halve cirkel. */
add_filter( 'powerhub_logo_terugval', function () {
	return '<span class="woordmerk"><span class="woordmerk-tal">TAL</span><span class="woordmerk-naam">leadership</span></span>';
} );
