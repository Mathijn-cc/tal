<?php
/**
 * Module Algemeen: logo, favicon, contactgegevens en de hoofdknop. Ook voor de klant.
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'algemeen', array(
	'titel'    => 'Algemeen',
	'uitleg'   => 'Logo, favicon, contactgegevens en de hoofdknop',
	'niveau'   => 'klant',
	'volgorde' => 10,
	'velden'   => array(
		'kop_logo'       => array( 'type' => 'kop', 'label' => 'Logo' ),
		'logo'           => array( 'type' => 'beeld', 'label' => 'Logo', 'uitleg' => 'Voor een lichte achtergrond. Bij voorkeur SVG of PNG.' ),
		'logo_licht'     => array( 'type' => 'beeld', 'label' => 'Logo op donker', 'uitleg' => 'Lichte versie voor donkere vlakken en de footer.' ),
		'favicon'        => array( 'type' => 'beeld', 'label' => 'Favicon', 'uitleg' => 'Het icoontje in het browsertabblad, bij bladwijzers en op het beginscherm van een telefoon. Vierkante PNG van minstens 512 bij 512 pixels, met een eigen achtergrondkleur zodat het ook in een donker tabblad zichtbaar is. Gaat voor het site-icoon van WordPress.' ),

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

/**
 * Favicon uit Powerhub. Powerhub haakt in op het site-icoon van WordPress (get_site_icon_url),
 * zodat WordPress zelf de juiste tags zet op de voorkant, in wp-admin en op de inlogpagina.
 * Van een PNG of JPG maakt Powerhub eenmalig vierkante kopieën per maat in uploads/powerhub-favicon.
 * Een SVG gaat ongewijzigd door, maar telefoons tonen dan geen icoon op het beginscherm.
 */
function powerhub_favicon_url( $maat = 512 ) {
	$id = absint( powerhub( 'favicon' ) );
	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	$bron = get_attached_file( $id );
	if ( ! $bron || ! is_readable( $bron ) ) {
		return '';
	}
	if ( 'image/svg+xml' === get_post_mime_type( $id ) ) {
		return wp_get_attachment_url( $id );
	}
	$maat    = max( 16, min( 1024, absint( $maat ) ) );
	$uploads = wp_get_upload_dir();
	$map     = trailingslashit( $uploads['basedir'] ) . 'powerhub-favicon';
	$naam    = $id . '-' . filemtime( $bron ) . '-' . $maat . '.png';
	$pad     = $map . '/' . $naam;
	if ( ! file_exists( $pad ) ) {
		wp_mkdir_p( $map );
		$bewerker = wp_get_image_editor( $bron );
		if ( is_wp_error( $bewerker ) ) {
			return wp_get_attachment_url( $id );
		}
		$bewerker->resize( $maat, $maat, true );
		$opgeslagen = $bewerker->save( $pad, 'image/png' );
		if ( is_wp_error( $opgeslagen ) ) {
			return wp_get_attachment_url( $id );
		}
	}
	return trailingslashit( $uploads['baseurl'] ) . 'powerhub-favicon/' . $naam;
}

add_filter( 'get_site_icon_url', function ( $url, $maat ) {
	$eigen = powerhub_favicon_url( $maat );
	return $eigen ? $eigen : $url;
}, 10, 2 );
