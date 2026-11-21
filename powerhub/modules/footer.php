<?php
/**
 * Module Footer: korte tekst en social media. Ook voor de klant.
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
		'kop_social'    => array( 'type' => 'kop', 'label' => 'Social media', 'uitleg' => 'Lege velden worden niet getoond.' ),
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
