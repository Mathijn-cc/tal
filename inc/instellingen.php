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
			'bestanden' => array(
				array( 'bestand' => 'dm-serif-display-400.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
			),
		),
		'general-sans' => array(
			'naam'      => 'General Sans',
			'fallback'  => 'system-ui, -apple-system, "Segoe UI", sans-serif',
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
		'hoofdknop_link'    => TAL_KENNISMAKING_URL,
		'email'             => 'chantal@tal-leadership.nl',
		'vloeiend_scrollen' => true,
	) );
} );

/* TAL-knoppen hebben geen pijl; de tekst rolt bij hover (thema.css). */
add_filter( 'powerhub_knop_pijl', '__return_empty_string' );

/* Zolang er in Powerhub geen logo is gekozen: het woordmerk, TAL in de halve cirkel (in HTML en CSS).
   Het logo zelf staat in Powerhub > Algemeen; de uitroltaak logo-v1 zet daar assets/beelden/tal-logo-*.png neer. */
add_filter( 'powerhub_logo_terugval', function () {
	return '<span class="woordmerk"><span class="woordmerk-tal">TAL</span><span class="woordmerk-naam">leadership</span></span>';
} );

/* Rank Math ziet de designsysteempagina als 404 en zet daar "Page not found" boven. Juiste titel terugzetten. */
$tal_designsysteem_titel = function ( $titel ) {
	return function_exists( 'powerhub_is_designsysteem' ) && powerhub_is_designsysteem() ? 'Designsysteem · ' . get_bloginfo( 'name' ) : $titel;
};
add_filter( 'pre_get_document_title', $tal_designsysteem_titel, 99 );
add_filter( 'rank_math/frontend/title', $tal_designsysteem_titel, 99 );
add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	return function_exists( 'powerhub_is_designsysteem' ) && powerhub_is_designsysteem() ? array( 'index' => 'noindex', 'follow' => 'nofollow' ) : $robots;
}, 99 );

/* Tab 404: titel, tekst en knoptekst van de pagina die niet bestaat. De knop gaat altijd naar de homepage. */
const TAL_404_STANDAARD = array(
	'niet_gevonden_titel' => 'Even de *weg kwijt*?',
	'niet_gevonden_tekst' => 'Dat overkomt de beste leiders. Deze pagina bestaat niet (meer), maar je hoeft niet onnodig te zwemmen. Even pauze, richting kiezen en met vertrouwen verder. Zo werkt het in leiderschap, en zo werkt het hier.',
	'niet_gevonden_knop'  => 'Terug naar de homepage',
);

add_action( 'powerhub_registreren', function () {
	powerhub_tab( 'niet-gevonden', array(
		'titel'    => '404',
		'uitleg'   => 'De pagina die bezoekers zien als een link niet (meer) werkt',
		'niveau'   => 'klant',
		'volgorde' => 30,
		'velden'   => array(
			'niet_gevonden_titel' => array( 'type' => 'tekst', 'label' => 'Titel', 'standaard' => TAL_404_STANDAARD['niet_gevonden_titel'], 'uitleg' => 'Zet *sterretjes* om woorden voor de koraal stiftstreep.' ),
			'niet_gevonden_tekst' => array( 'type' => 'tekstvak', 'label' => 'Tekst', 'standaard' => TAL_404_STANDAARD['niet_gevonden_tekst'] ),
			'niet_gevonden_knop'  => array( 'type' => 'tekst', 'label' => 'Knoptekst', 'standaard' => TAL_404_STANDAARD['niet_gevonden_knop'], 'uitleg' => 'De knop gaat altijd naar de homepage.' ),
		),
	) );
} );

/**
 * Waarde voor de 404-pagina: uit Powerhub, of de standaardtekst als het veld leeg is gelaten.
 */
function tal_404( $sleutel ) {
	$waarde = trim( (string) powerhub( $sleutel ) );
	return '' !== $waarde ? $waarde : TAL_404_STANDAARD[ $sleutel ];
}
