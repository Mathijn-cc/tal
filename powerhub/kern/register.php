<?php
/**
 * Register van tabs en velden, en het ophalen van waarden.
 *
 * Een tab:
 *   powerhub_tab( 'contact', array(
 *       'titel'    => 'Contact',
 *       'uitleg'   => 'Telefoon en e-mail',
 *       'niveau'   => 'klant',          // klant: ook de klant ziet de tab. beheer: alleen Creators Connect.
 *       'volgorde' => 20,
 *       'velden'   => array(
 *           'telefoon' => array( 'type' => 'tekst', 'label' => 'Telefoon' ),
 *       ),
 *   ) );
 *
 * Veldtypes: tekst, tekstvak, url, email, getal, kleur, beeld, schakel, keuze, kop (alleen een tussenkop).
 * Veldsleutels zijn sitebreed uniek: gebruik bij projectvelden een duidelijke naam.
 * Een tab zonder velden kan met 'weergave' een eigen functie tonen.
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['powerhub_tabs'] = array();

/**
 * Tab toevoegen. Mag op elk moment voordat Powerhub voor het eerst wordt uitgelezen,
 * het veiligst in de actie powerhub_registreren of direct bij het laden van het thema.
 */
function powerhub_tab( $sleutel, $tab ) {
	$tab = wp_parse_args( $tab, array(
		'titel'    => ucfirst( $sleutel ),
		'uitleg'   => '',
		'niveau'   => 'beheer',
		'volgorde' => 50,
		'velden'   => array(),
		'weergave' => null,
	) );
	foreach ( $tab['velden'] as $naam => $veld ) {
		$tab['velden'][ $naam ] = wp_parse_args( $veld, array(
			'type'      => 'tekst',
			'label'     => $naam,
			'uitleg'    => '',
			'standaard' => powerhub_standaard_voor_type( $veld['type'] ?? 'tekst' ),
			'keuzes'    => array(),
			'min'       => null,
			'max'       => null,
			'stap'      => null,
		) );
	}
	$GLOBALS['powerhub_tabs'][ $sleutel ] = $tab;
}

/**
 * Velden toevoegen aan een bestaande tab, bijvoorbeeld extra projectvelden bij Algemeen.
 */
function powerhub_velden_toevoegen( $tab, $velden ) {
	if ( ! isset( $GLOBALS['powerhub_tabs'][ $tab ] ) ) {
		return;
	}
	$bestaand = $GLOBALS['powerhub_tabs'][ $tab ];
	$bestaand['velden'] = array_merge( $bestaand['velden'], $velden );
	powerhub_tab( $tab, $bestaand );
}

function powerhub_standaard_voor_type( $type ) {
	switch ( $type ) {
		case 'schakel':
			return false;
		case 'beeld':
		case 'getal':
			return 0;
		default:
			return '';
	}
}

/**
 * Alle tabs, op volgorde. De actie powerhub_registreren draait één keer, bij het eerste gebruik.
 */
function powerhub_tabs() {
	static $klaar = false;
	if ( ! $klaar ) {
		$klaar = true;
		do_action( 'powerhub_registreren' );
	}
	static $standaard = null;
	if ( null === $standaard ) {
		/* Projecten kunnen standaardwaarden overschrijven zonder de module aan te passen. */
		$standaard = (array) apply_filters( 'powerhub_standaardwaarden', array() );
		foreach ( $GLOBALS['powerhub_tabs'] as $tab_sleutel => $tab ) {
			foreach ( $tab['velden'] as $naam => $veld ) {
				if ( array_key_exists( $naam, $standaard ) ) {
					$GLOBALS['powerhub_tabs'][ $tab_sleutel ]['velden'][ $naam ]['standaard'] = $standaard[ $naam ];
				}
			}
		}
	}
	$tabs = $GLOBALS['powerhub_tabs'];
	uasort( $tabs, function ( $a, $b ) {
		return $a['volgorde'] <=> $b['volgorde'];
	} );
	return $tabs;
}

/**
 * Alle velden in één lijst, met de tab erbij.
 */
function powerhub_alle_velden() {
	$velden = array();
	foreach ( powerhub_tabs() as $tab_sleutel => $tab ) {
		foreach ( $tab['velden'] as $naam => $veld ) {
			if ( 'kop' === $veld['type'] ) {
				continue;
			}
			$veld['tab']     = $tab_sleutel;
			$velden[ $naam ] = $veld;
		}
	}
	return $velden;
}

/**
 * Opgeslagen waarden aangevuld met standaardwaarden. Eén keer per verzoek opgehaald.
 */
function powerhub_waarden( $vernieuwen = false ) {
	static $cache = null;
	if ( null === $cache || $vernieuwen ) {
		$opgeslagen = get_option( POWERHUB_OPTIE, array() );
		$opgeslagen = is_array( $opgeslagen ) ? $opgeslagen : array();
		$cache      = array();
		foreach ( powerhub_alle_velden() as $naam => $veld ) {
			$cache[ $naam ] = array_key_exists( $naam, $opgeslagen ) ? $opgeslagen[ $naam ] : $veld['standaard'];
		}
	}
	return $cache;
}

/**
 * Eén waarde ophalen: powerhub( 'telefoon' ).
 */
function powerhub( $sleutel, $anders = '' ) {
	$waarden = powerhub_waarden();
	if ( ! array_key_exists( $sleutel, $waarden ) ) {
		return $anders;
	}
	/* Filter voor bijvoorbeeld vertalingen (module WPML). Alleen als er iets aan hangt, om dit snel te houden. */
	if ( has_filter( 'powerhub_waarde' ) ) {
		$velden = powerhub_alle_velden();
		return apply_filters( 'powerhub_waarde', $waarden[ $sleutel ], $sleutel, $velden[ $sleutel ] ?? array() );
	}
	return $waarden[ $sleutel ];
}

/**
 * Schakelaar uitlezen: powerhub_aan( 'animaties' ).
 */
function powerhub_aan( $sleutel ) {
	return (bool) powerhub( $sleutel, false );
}
