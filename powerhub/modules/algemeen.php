<?php
/**
 * Module Algemeen: logo, contactgegevens en de hoofdknop. Ook voor de klant.
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'algemeen', array(
	'titel'    => 'Algemeen',
	'uitleg'   => 'Logo, contactgegevens en de hoofdknop',
	'niveau'   => 'klant',
	'volgorde' => 10,
	'velden'   => array(
		'kop_logo'       => array( 'type' => 'kop', 'label' => 'Logo' ),
		'logo'           => array( 'type' => 'beeld', 'label' => 'Logo', 'uitleg' => 'Voor een lichte achtergrond. Bij voorkeur SVG of PNG.' ),
		'logo_licht'     => array( 'type' => 'beeld', 'label' => 'Logo op donker', 'uitleg' => 'Lichte versie voor donkere vlakken en de footer.' ),

		'kop_contact'    => array( 'type' => 'kop', 'label' => 'Contact' ),
		'telefoon'       => array( 'type' => 'tekst', 'label' => 'Telefoon' ),
		'email'          => array( 'type' => 'email', 'label' => 'E-mail' ),
		'adres'          => array( 'type' => 'tekstvak', 'label' => 'Adres' ),

		'kop_knop'       => array( 'type' => 'kop', 'label' => 'Hoofdknop', 'uitleg' => 'De belangrijkste actie, rechtsboven in het menu.' ),
		'hoofdknop_tekst' => array( 'type' => 'tekst', 'label' => 'Tekst', 'standaard' => 'Neem contact op' ),
		'hoofdknop_link'  => array( 'type' => 'url', 'label' => 'Link', 'standaard' => '/contact/' ),
	),
) );

/**
 * De hoofdknop als link-array voor powerhub_knop().
 */
function powerhub_hoofdknop() {
	return array(
		'url'   => powerhub( 'hoofdknop_link' ),
		'title' => powerhub( 'hoofdknop_tekst' ),
	);
}

/**
 * Telefoonnummer als tel:-link.
 */
function powerhub_telefoon_link() {
	$nummer = preg_replace( '/[^0-9+]/', '', (string) powerhub( 'telefoon' ) );
	return $nummer ? 'tel:' . $nummer : '';
}
