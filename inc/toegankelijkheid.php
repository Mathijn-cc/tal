<?php
/**
 * Instellingen voor de Powerhub-module Toegankelijkheid.
 * De standaardrollen (kop, lopende tekst, gedempt, link, accent, focus) per achtergrond controleert de module zelf;
 * hier staan de losse kleurparen van componenten in thema.css en de grenzen van dit project.
 */

defined( 'ABSPATH' ) || exit;

/* Kleurparen buiten de sectie-achtergronden: label, voorgrond-token, achtergrond-token, soort (normaal, groot, ui). */
add_filter( 'powerhub_contrast_paren', function ( $paren ) {
	return array_merge( (array) $paren, array(
		array( 'Knoptekst op actieknop', 'actie-primair-tekst', 'actie-primair', 'normaal' ),
		array( 'Knoptekst op actieknop, hover', 'actie-primair-tekst', 'actie-primair-hover', 'normaal' ),
		array( 'Icoon op accenttegel', 'tegel-accent-tekst', 'tegel-accent', 'ui' ),
		array( 'Icoon op actietegel', 'tegel-actie-tekst', 'tegel-actie', 'ui' ),
		array( 'Gedempte tekst in de footer', 'tekst-op-donker-gedempt', 'vlak-donker', 'normaal' ),
	) );
} );

/* Grenzen: geen tekst onder 14px, lopende tekst 16px, WCAG AA, klikdoelen 44px. */
add_filter( 'powerhub_standaardwaarden', function ( $standaard ) {
	return array_merge( $standaard, array(
		'toegankelijk_kleinste_tekst' => 14,
		'toegankelijk_lopende_tekst'  => 16,
		'toegankelijk_niveau'         => 'AA',
		'toegankelijk_klikdoel'       => 44,
		'toegankelijk_paginacontrole' => true,
	) );
} );
