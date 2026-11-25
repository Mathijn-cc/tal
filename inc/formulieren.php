<?php
/**
 * Gravity Forms in de stijl van het thema.
 * De formulieren staan als export in inc/formulieren/<naam>.json en worden bij de uitrol geïmporteerd (inc/uitrol.php).
 * Formulieren met de class tal-formulier krijgen de opmaak uit assets/css/blokken/contact.css.
 */

defined( 'ABSPATH' ) || exit;

/* Geen eigen CSS van Gravity Forms: het thema regelt de opmaak. */
add_filter( 'gform_disable_css', '__return_true' );

/* Formulieren met deze class (instelling CSS-class in Gravity Forms) krijgen de themaknop en -opmaak. */
const TAL_FORMULIER_CLASS = 'tal-formulier';

function tal_is_thema_formulier( $form ) {
	return false !== strpos( (string) ( $form['cssClass'] ?? '' ), TAL_FORMULIER_CLASS );
}

/**
 * Verzendknop als <button> met de rollende tekst van de themaknop. Alle attributen van Gravity Forms blijven staan.
 */
add_filter( 'gform_submit_button', function ( $knop, $form ) {
	if ( ! tal_is_thema_formulier( $form ) ) {
		return $knop;
	}
	$tekst = ! empty( $form['button']['text'] ) ? $form['button']['text'] : 'Verstuur';
	/* Nieuwere versies van Gravity Forms geven al een <button>: classes erbij en de tekst in een span. */
	if ( preg_match( '/<button([^>]*)>(.*?)<\/button>/is', $knop, $b ) ) {
		$attr = preg_match( '/\sclass=/i', $b[1] ) ? preg_replace( '/\sclass=([\'"])(.*?)\1/i', ' class=$1$2 knop knop-actie$1', $b[1] ) : $b[1] . ' class="knop knop-actie"';
		return str_replace( $b[0], '<button' . $attr . '><span>' . esc_html( wp_strip_all_tags( $b[2] ) ? wp_strip_all_tags( $b[2] ) : $tekst ) . '</span></button>', $knop );
	}
	if ( ! preg_match( '/<input([^>]*)>/i', $knop, $m ) ) {
		return $knop;
	}
	$attr  = preg_replace( '/\s(type|value)=([\'"]).*?\2/i', '', $m[1] );
	$attr  = preg_replace( '/\sclass=([\'"])(.*?)\1/i', ' class=$1$2 knop knop-actie$1', $attr );
	$attr  = rtrim( $attr, " /" );
	return '<button type="submit"' . $attr . '><span>' . esc_html( $tekst ) . '</span></button>';
}, 10, 2 );

/**
 * Formulier tonen op id. Zonder Gravity Forms of zonder formulier: een melding voor de redactie, niets voor bezoekers.
 */
function tal_formulier( $id ) {
	$id = (int) $id;
	if ( ! $id ) {
		$id = (int) get_option( 'tal_contactformulier' );
	}
	if ( ! $id || ! function_exists( 'gravity_form' ) || ! class_exists( 'GFAPI' ) || ! GFAPI::get_form( $id ) ) {
		return current_user_can( 'edit_posts' ) ? '<p class="formulier-melding">Kies in het blok een Gravity Forms formulier (of activeer Gravity Forms).</p>' : '';
	}
	return do_shortcode( sprintf( '[gravityform id="%d" title="false" description="false" ajax="true" theme="gravity-theme"]', $id ) );
}

/**
 * Keuzelijst met alle Gravity Forms formulieren voor ACF-velden met de class kies-formulier.
 */
add_filter( 'acf/load_field/type=select', function ( $veld ) {
	if ( false === strpos( (string) ( $veld['wrapper']['class'] ?? '' ), 'kies-formulier' ) || ! class_exists( 'GFAPI' ) ) {
		return $veld;
	}
	$veld['choices'] = array();
	foreach ( (array) GFAPI::get_forms() as $form ) {
		$veld['choices'][ (int) $form['id'] ] = $form['title'];
	}
	return $veld;
} );

/**
 * Formulier uit inc/formulieren importeren, één keer. Geeft het formulier-id, of 0 als het (nog) niet kan.
 */
function tal_formulier_importeren( $naam, $optie ) {
	if ( ! class_exists( 'GFAPI' ) || ! class_exists( 'GFCommon' ) ) {
		return 0;
	}
	$bestaand = (int) get_option( $optie );
	if ( $bestaand && GFAPI::get_form( $bestaand ) ) {
		return $bestaand;
	}
	$pad = THEMA_PAD . '/inc/formulieren/' . sanitize_file_name( $naam ) . '.json';
	if ( ! is_readable( $pad ) ) {
		return 0;
	}
	$json = (string) file_get_contents( $pad ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$ids  = array();
	if ( ! class_exists( 'GFExport' ) ) {
		require_once GFCommon::get_base_path() . '/export.php';
	}
	if ( class_exists( 'GFExport' ) ) {
		GFExport::import_json( $json, $ids );
	}
	$id = (int) ( $ids[0] ?? 0 );
	if ( ! $id ) {
		$data = json_decode( $json, true );
		$id   = isset( $data['0'] ) ? GFAPI::add_form( $data['0'] ) : 0;
		$id   = is_wp_error( $id ) ? 0 : (int) $id;
	}
	if ( $id ) {
		update_option( $optie, $id, false );
	}
	return $id;
}

