<?php
/**
 * Eenmalige taken na een uitrol. De workflow roept na elke uitrol de actie powerhub_na_uitrol aan (wp eval).
 * Elke taak draait één keer per omgeving; wat gedaan is, staat in de optie powerhub_uitroltaken.
 *
 * Taak toevoegen in het project:
 *   add_filter( 'powerhub_uitroltaken', function ( $taken ) {
 *       $taken['homepage-v1'] = 'mijn_functie';
 *       return $taken;
 *   } );
 * Nooit een bestaande sleutel hergebruiken: verhoog het nummer (homepage-v2).
 * Geeft de functie false terug, dan wordt de taak niet als gedaan gemarkeerd (bijv. als ACF nog ontbreekt).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'powerhub_na_uitrol', function () {
	$gedaan = (array) get_option( 'powerhub_uitroltaken', array() );
	foreach ( (array) apply_filters( 'powerhub_uitroltaken', array() ) as $sleutel => $functie ) {
		if ( in_array( $sleutel, $gedaan, true ) ) {
			continue;
		}
		if ( ! is_callable( $functie ) ) {
			echo 'Overgeslagen, functie bestaat niet: ' . esc_html( $sleutel ) . "\n";
			continue;
		}
		/* Een taak die false teruggeeft, is (nog) niet gelukt en draait bij de volgende uitrol opnieuw. */
		if ( false === call_user_func( $functie ) ) {
			echo 'Nog niet gelukt, volgende uitrol opnieuw: ' . esc_html( $sleutel ) . "\n";
			continue;
		}
		$gedaan[] = $sleutel;
		echo 'Taak uitgevoerd: ' . esc_html( $sleutel ) . "\n";
	}
	update_option( 'powerhub_uitroltaken', $gedaan, false );
} );
