<?php
/**
 * Module Toegang (alleen beheer): per tab kiezen of ook de klant (niet-beheerder) hem ziet en mag aanpassen.
 * Standaard volgt dit het niveau van de tab (klant of beheer). Deze tab zelf is altijd alleen voor beheerders.
 */

defined( 'ABSPATH' ) || exit;

/* Als laatste registreren, zodat alle andere tabs al bestaan. */
add_action( 'powerhub_registreren', function () {
	$velden = array(
		'kop_toegang' => array(
			'type'   => 'kop',
			'label'  => 'Zichtbaar voor de klant',
			'uitleg' => 'Aan: ook gebruikers zonder beheerrechten in Powerhub zien deze tab en kunnen hem opslaan. Beheerders zien altijd alles.',
		),
	);
	$tabs = $GLOBALS['powerhub_tabs'];
	uasort( $tabs, function ( $a, $b ) {
		return $a['volgorde'] <=> $b['volgorde'];
	} );
	foreach ( $tabs as $sleutel => $tab ) {
		if ( 'toegang' === $sleutel ) {
			continue;
		}
		$velden[ 'klant_tab_' . $sleutel ] = array(
			'type'      => 'schakel',
			'label'     => $tab['titel'],
			'uitleg'    => 'beheer' === $tab['niveau'] ? 'Standaard alleen beheer.' : 'Standaard ook voor de klant.',
			'standaard' => 'klant' === $tab['niveau'],
		);
	}
	powerhub_tab( 'toegang', array(
		'titel'    => 'Toegang',
		'uitleg'   => 'Welke tabs de klant ziet',
		'niveau'   => 'beheer',
		'volgorde' => 98,
		'velden'   => $velden,
	) );
}, 999 );

/**
 * Ziet de klant deze tab? De keuze uit de tab Toegang, anders het niveau van de tab.
 */
function powerhub_tab_voor_klant( $sleutel, $tab ) {
	if ( 'toegang' === $sleutel ) {
		return false;
	}
	$opgeslagen = powerhub_opgeslagen();
	if ( array_key_exists( 'klant_tab_' . $sleutel, $opgeslagen ) ) {
		return (bool) $opgeslagen[ 'klant_tab_' . $sleutel ];
	}
	return 'klant' === $tab['niveau'];
}
