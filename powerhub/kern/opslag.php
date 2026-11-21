<?php
/**
 * Opslaan, opschonen, export, import en reset.
 * Opslaan gaat per tab: alleen de velden van die tab worden bijgewerkt, de rest blijft staan.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Eén waarde opschonen op basis van het veldtype.
 */
function powerhub_opschonen( $veld, $ruw ) {
	switch ( $veld['type'] ) {
		case 'schakel':
			return ! empty( $ruw );
		case 'getal':
			if ( ! is_numeric( $ruw ) ) {
				return $veld['standaard'];
			}
			$getal = 0 + $ruw;
			if ( null !== $veld['min'] ) {
				$getal = max( $veld['min'], $getal );
			}
			if ( null !== $veld['max'] ) {
				$getal = min( $veld['max'], $getal );
			}
			return $getal;
		case 'kleur':
			return sanitize_hex_color( (string) $ruw ) ?: $veld['standaard'];
		case 'beeld':
			return absint( $ruw );
		case 'url':
			return esc_url_raw( (string) $ruw );
		case 'email':
			return sanitize_email( (string) $ruw );
		case 'keuze':
			return array_key_exists( (string) $ruw, $veld['keuzes'] ) ? (string) $ruw : $veld['standaard'];
		case 'tekstvak':
			return sanitize_textarea_field( (string) $ruw );
		default:
			return sanitize_text_field( (string) $ruw );
	}
}

function powerhub_opgeslagen() {
	$opgeslagen = get_option( POWERHUB_OPTIE, array() );
	return is_array( $opgeslagen ) ? $opgeslagen : array();
}

function powerhub_terug( $tab, $melding ) {
	wp_safe_redirect( add_query_arg( array(
		'page'    => 'powerhub',
		'tab'     => $tab,
		'melding' => $melding,
	), admin_url( 'admin.php' ) ) );
	exit;
}

/**
 * Tab opslaan.
 */
add_action( 'admin_post_powerhub_opslaan', function () {
	$tab_sleutel = isset( $_POST['powerhub_tab'] ) ? sanitize_key( wp_unslash( $_POST['powerhub_tab'] ) ) : '';
	check_admin_referer( 'powerhub_opslaan_' . $tab_sleutel );

	$tabs = powerhub_tabs();
	if ( ! isset( $tabs[ $tab_sleutel ] ) || ! powerhub_mag_tab( $tabs[ $tab_sleutel ] ) ) {
		wp_die( 'Je hebt geen toegang tot deze instellingen.', 403 );
	}

	$invoer     = isset( $_POST['powerhub'] ) ? wp_unslash( (array) $_POST['powerhub'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- per veld opgeschoond.
	$opgeslagen = powerhub_opgeslagen();
	foreach ( $tabs[ $tab_sleutel ]['velden'] as $naam => $veld ) {
		if ( 'kop' === $veld['type'] ) {
			continue;
		}
		$opgeslagen[ $naam ] = powerhub_opschonen( $veld, $invoer[ $naam ] ?? null );
	}
	update_option( POWERHUB_OPTIE, $opgeslagen, true );
	powerhub_waarden( true );
	do_action( 'powerhub_opgeslagen', $tab_sleutel, $opgeslagen );

	powerhub_terug( $tab_sleutel, 'opgeslagen' );
} );

/**
 * Export als JSON-bestand.
 */
add_action( 'admin_post_powerhub_export', function () {
	check_admin_referer( 'powerhub_export' );
	if ( ! powerhub_is_beheerder() ) {
		wp_die( 'Alleen voor beheerders.', 403 );
	}
	$naam = sanitize_file_name( wp_parse_url( home_url(), PHP_URL_HOST ) . '-powerhub-' . gmdate( 'Ymd-His' ) . '.json' );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $naam );
	echo wp_json_encode( array(
		'powerhub' => POWERHUB_VERSIE,
		'site'     => home_url(),
		'datum'    => gmdate( 'c' ),
		'waarden'  => powerhub_opgeslagen(),
	), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
} );

/**
 * Import uit een eerder geëxporteerd bestand. Onbekende sleutels worden genegeerd.
 * Beelden verwijzen naar media-ID's: die kloppen alleen binnen dezelfde site.
 */
add_action( 'admin_post_powerhub_import', function () {
	check_admin_referer( 'powerhub_import' );
	if ( ! powerhub_is_beheerder() ) {
		wp_die( 'Alleen voor beheerders.', 403 );
	}
	$bestand = $_FILES['powerhub_bestand']['tmp_name'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$data    = $bestand && is_uploaded_file( $bestand ) ? json_decode( (string) file_get_contents( $bestand ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( empty( $data['waarden'] ) || ! is_array( $data['waarden'] ) ) {
		powerhub_terug( 'gereedschap', 'fout' );
	}
	$opgeslagen = powerhub_opgeslagen();
	foreach ( powerhub_alle_velden() as $naam => $veld ) {
		if ( array_key_exists( $naam, $data['waarden'] ) ) {
			$opgeslagen[ $naam ] = powerhub_opschonen( $veld, $data['waarden'][ $naam ] );
		}
	}
	update_option( POWERHUB_OPTIE, $opgeslagen, true );
	powerhub_terug( 'gereedschap', 'import' );
} );

/**
 * Alles terug naar de standaardwaarden.
 */
add_action( 'admin_post_powerhub_reset', function () {
	check_admin_referer( 'powerhub_reset' );
	if ( ! powerhub_is_beheerder() ) {
		wp_die( 'Alleen voor beheerders.', 403 );
	}
	delete_option( POWERHUB_OPTIE );
	powerhub_terug( 'gereedschap', 'reset' );
} );
