<?php
/**
 * Module Kleuren: het palet van het project, als CSS-variabelen --kleur-<sleutel>.
 *
 * Het project kiest welke kleuren in Powerhub overschreven mogen worden:
 *   add_filter( 'powerhub_kleuren', function () {
 *       return array( 'lime-500' => array( 'Actie (lime)', null ) );
 *   } );
 * Sleutel lime-500 hoort bij token kleur.lime-500 (CSS: --kleur-lime-500). Standaard null: de waarde uit tokens.json.
 * Zonder designsysteem geef je zelf een standaard op, en hoort dezelfde waarde in de :root van thema.css.
 * Powerhub schrijft alleen kleuren weg die afwijken van de standaard.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_kleuren() {
	$kleuren = (array) apply_filters( 'powerhub_kleuren', array(
		'donker' => array( 'Donker', '#1d2733' ),
		'licht'  => array( 'Licht', '#f6f6f4' ),
		'actie'  => array( 'Actie', '#ffd23f' ),
	) );
	foreach ( $kleuren as $sleutel => $kleur ) {
		if ( empty( $kleur[1] ) && function_exists( 'powerhub_token' ) ) {
			$kleuren[ $sleutel ][1] = powerhub_token( 'kleur.' . $sleutel, '#000000' );
		}
	}
	return $kleuren;
}

add_action( 'powerhub_registreren', function () {
	$velden = array();
	foreach ( powerhub_kleuren() as $sleutel => $kleur ) {
		$velden[ 'kleur_' . $sleutel ] = array(
			'type'      => 'kleur',
			'label'     => $kleur[0],
			'standaard' => $kleur[1],
			'uitleg'    => '--kleur-' . $sleutel,
		);
	}
	powerhub_tab( 'kleuren', array(
		'titel'    => 'Kleuren',
		'uitleg'   => 'Het palet van de site',
		'niveau'   => 'beheer',
		'volgorde' => 30,
		'velden'   => $velden,
	) );
} );

/**
 * Alleen de kleuren die afwijken van de standaard, zodat thema.css de bron blijft.
 */
function powerhub_kleuren_css() {
	$regels = array();
	foreach ( powerhub_kleuren() as $sleutel => $kleur ) {
		$waarde = powerhub( 'kleur_' . $sleutel, $kleur[1] );
		if ( strtolower( $waarde ) !== strtolower( $kleur[1] ) ) {
			$regels[] = '--kleur-' . sanitize_key( $sleutel ) . ':' . $waarde;
		}
	}
	return $regels ? ':root{' . implode( ';', $regels ) . '}' : '';
}

add_action( 'wp_enqueue_scripts', function () {
	$css = powerhub_kleuren_css();
	if ( $css ) {
		wp_add_inline_style( powerhub_stijl_handle(), $css );
	}
}, 30 );
