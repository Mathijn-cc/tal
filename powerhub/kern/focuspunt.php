<?php
/**
 * Focuspunt van een foto: het deel dat altijd in beeld blijft als de foto wordt bijgesneden (object-fit: cover).
 * In de beheerder klik je op de foto (of gebruik je de pijltjestoetsen op het puntje); het punt wordt bij de bijlage
 * bewaard, dus het geldt overal waar die foto staat: blokken, herhalers en uitgelichte afbeeldingen.
 * Op de voorkant krijgt elke <img> van wp_get_attachment_image() de bijbehorende object-position.
 */

defined( 'ABSPATH' ) || exit;

const POWERHUB_FOCUS_META = '_powerhub_focuspunt';

/**
 * Focuspunt als array( x, y ) in procenten, of null als er geen is ingesteld.
 */
function powerhub_focuspunt( $id ) {
	$waarde = (string) get_post_meta( (int) $id, POWERHUB_FOCUS_META, true );
	if ( ! preg_match( '/^(\d{1,3}(?:\.\d+)?) (\d{1,3}(?:\.\d+)?)$/', $waarde, $m ) ) {
		return null;
	}
	return array( min( 100, (float) $m[1] ), min( 100, (float) $m[2] ) );
}

/* Voorkant: object-position op de afbeelding zelf. */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $bijlage ) {
	$punt = powerhub_focuspunt( $bijlage->ID );
	if ( ! $punt ) {
		return $attr;
	}
	$stijl         = sprintf( 'object-position:%s%% %s%%;', round( $punt[0], 1 ), round( $punt[1], 1 ) );
	$attr['style'] = isset( $attr['style'] ) ? rtrim( $attr['style'], '; ' ) . ';' . $stijl : $stijl;
	return $attr;
}, 10, 2 );

/* Beheerder: het punt meegeven aan de mediabibliotheek, zodat het script het kan tonen. */
add_filter( 'wp_prepare_attachment_for_js', function ( $data, $bijlage ) {
	$punt                  = powerhub_focuspunt( $bijlage->ID );
	$data['powerhubFocus'] = $punt ? $punt : null;
	return $data;
}, 10, 2 );

/* Opslaan via admin-ajax. */
add_action( 'wp_ajax_powerhub_focuspunt', function () {
	check_ajax_referer( 'powerhub_focuspunt', 'nonce' );
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	if ( ! $id || 'attachment' !== get_post_type( $id ) || ! wp_attachment_is_image( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( 'Geen toegang tot deze afbeelding.', 403 );
	}
	if ( ! empty( $_POST['wissen'] ) ) {
		delete_post_meta( $id, POWERHUB_FOCUS_META );
		wp_send_json_success( null );
	}
	$x = isset( $_POST['x'] ) ? max( 0, min( 100, round( (float) $_POST['x'], 1 ) ) ) : 50;
	$y = isset( $_POST['y'] ) ? max( 0, min( 100, round( (float) $_POST['y'], 1 ) ) ) : 50;
	update_post_meta( $id, POWERHUB_FOCUS_META, $x . ' ' . $y );
	wp_send_json_success( array( $x, $y ) );
} );
