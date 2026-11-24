<?php
/**
 * Module Footer: korte tekst, keurmerken, social media en de vermelding van de maker. Ook voor de klant.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_social_kanalen() {
	return (array) apply_filters( 'powerhub_social_kanalen', array(
		'linkedin'  => 'LinkedIn',
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'youtube'   => 'YouTube',
		'tiktok'    => 'TikTok',
	) );
}

add_action( 'powerhub_registreren', function () {
	$velden = array(
		'footer_tekst'  => array( 'type' => 'tekstvak', 'label' => 'Korte tekst', 'uitleg' => 'Onder het logo in de footer.' ),
		'kop_keurmerken' => array( 'type' => 'kop', 'label' => 'Keurmerken', 'uitleg' => 'Logo\'s van certificaten of keurmerken onder het logo, op een wit vlak. Lege velden worden niet getoond.' ),
		'keurmerk_1'     => array( 'type' => 'beeld', 'label' => 'Keurmerk 1' ),
		'keurmerk_1_url' => array( 'type' => 'url', 'label' => 'Link keurmerk 1' ),
		'keurmerk_2'     => array( 'type' => 'beeld', 'label' => 'Keurmerk 2' ),
		'keurmerk_2_url' => array( 'type' => 'url', 'label' => 'Link keurmerk 2' ),
		'keurmerk_3'     => array( 'type' => 'beeld', 'label' => 'Keurmerk 3' ),
		'keurmerk_3_url' => array( 'type' => 'url', 'label' => 'Link keurmerk 3' ),
		'kop_maker'      => array( 'type' => 'kop', 'label' => 'Onderregel' ),
		'footer_maker'   => array( 'type' => 'schakel', 'label' => 'Toon "Website door creatorsconnect.nl"', 'standaard' => true ),
		'kop_social'     => array( 'type' => 'kop', 'label' => 'Social media', 'uitleg' => 'Lege velden worden niet getoond.' ),
	);
	foreach ( powerhub_social_kanalen() as $sleutel => $naam ) {
		$velden[ 'social_' . $sleutel ] = array( 'type' => 'url', 'label' => $naam );
	}
	powerhub_tab( 'footer', array(
		'titel'    => 'Footer',
		'uitleg'   => 'Tekst en social media',
		'niveau'   => 'klant',
		'volgorde' => 20,
		'velden'   => $velden,
	) );
} );

/**
 * Ingevulde social-kanalen: array( 'linkedin' => array( 'naam' => 'LinkedIn', 'url' => '...' ) ).
 */
function powerhub_social() {
	$uit = array();
	foreach ( powerhub_social_kanalen() as $sleutel => $naam ) {
		$url = powerhub( 'social_' . $sleutel );
		if ( $url ) {
			$uit[ $sleutel ] = array( 'naam' => $naam, 'url' => $url );
		}
	}
	return $uit;
}

/**
 * Ingevulde keurmerken: array( array( 'id' => 12, 'url' => '...' ) ).
 */
function powerhub_keurmerken() {
	$uit = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$id = (int) powerhub( 'keurmerk_' . $i );
		if ( $id ) {
			$uit[] = array( 'id' => $id, 'url' => (string) powerhub( 'keurmerk_' . $i . '_url' ) );
		}
	}
	return $uit;
}
