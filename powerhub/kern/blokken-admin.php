<?php
/**
 * Beheerervaring van de blokken: blokkiezer met zoeken, iconen en uitleg, compacte blokkoppen met de ingevulde kop,
 * kleurbolletjes voor de achtergrond en een contrastwaarschuwing bij 50/50.
 * De stijl gebruikt de tokens van het thema, zodat de beheerder in de huisstijl van de klant werkt.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_is_blokscherm() {
	$scherm = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return $scherm && 'post' === $scherm->base && in_array( $scherm->post_type, powerhub_blok_posttypes(), true );
}

add_action( 'admin_enqueue_scripts', function () {
	if ( ! powerhub_is_blokscherm() ) {
		return;
	}
	$versie = defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : POWERHUB_VERSIE;
	$tokens = get_template_directory() . '/assets/css/tokens.css';
	$deps   = array();
	if ( file_exists( $tokens ) ) {
		wp_enqueue_style( 'tokens', get_template_directory_uri() . '/assets/css/tokens.css', array(), $versie );
		$deps[] = 'tokens';
	}
	wp_enqueue_style( 'powerhub-blokken', POWERHUB_URL . '/assets/admin-blokken.css', $deps, POWERHUB_VERSIE );

	/* Lettertypes van het thema en de kleurbolletjes per achtergrond. */
	$css = function_exists( 'powerhub_typografie_css' ) ? powerhub_typografie_css() : '';
	foreach ( powerhub_achtergronden() as $sleutel => $a ) {
		if ( ! $a['kleur'] ) {
			continue;
		}
		$css .= sprintf(
			'#acf-group_powerhub_blokken .kleurkeuze label:has(input[value="%1$s"])::before{background:%2$s}',
			esc_attr( $sleutel ),
			esc_attr( $a['kleur'] )
		);
	}
	$lijst = array_values( powerhub_achtergronden() );
	if ( count( $lijst ) > 1 ) {
		$css .= sprintf(
			'#acf-group_powerhub_blokken .kleurkeuze label:has(input[value="gedeeld"])::before{background:linear-gradient(to bottom,%s 50%%,%s 50%%)}',
			esc_attr( $lijst[ count( $lijst ) > 2 ? 2 : 1 ]['kleur'] ?: '#333' ),
			esc_attr( $lijst[0]['kleur'] ?: '#eee' )
		);
	}
	wp_add_inline_style( 'powerhub-blokken', $css );

	wp_enqueue_script( 'powerhub-blokken', POWERHUB_URL . '/assets/admin-blokken.js', array( 'jquery' ), POWERHUB_VERSIE, true );

	$info = array();
	foreach ( powerhub_blok_layouts() as $naam => $layout ) {
		$info[ $naam ] = array(
			'icoon'  => powerhub_icoon( $layout['icoon'] ?? 'layers' ) ?: powerhub_icoon( 'layers' ),
			'uitleg' => $layout['uitleg'] ?? '',
		);
	}
	$tekst_licht = function_exists( 'powerhub_token' ) ? powerhub_token( 'kleur.tekst-kop', '#1d2733' ) : '#1d2733';
	$tekst_donker = function_exists( 'powerhub_token' ) ? powerhub_token( 'kleur.tekst-op-donker', '#ffffff' ) : '#ffffff';
	$achtergronden = array();
	foreach ( powerhub_achtergronden() as $sleutel => $a ) {
		$achtergronden[ $sleutel ] = array(
			'label'  => $a['label'],
			'kleur'  => $a['kleur'],
			'tekst'  => $a['donker'] ? $tekst_donker : $tekst_licht,
		);
	}
	wp_localize_script( 'powerhub-blokken', 'powerhubBlokken', array(
		'blokken'       => $info,
		'achtergronden' => $achtergronden,
		'zoeken'        => powerhub_icoon( 'search' ),
	) );
} );

/**
 * Bloktitel in de beheerder: label plus de ingevulde kop, zodat ingeklapte blokken herkenbaar blijven.
 */
add_filter( 'acf/fields/flexible_content/layout_title/name=blokken', function ( $titel, $veld, $layout, $i ) {
	$kop = '';
	foreach ( array( 'kop', 'titel', 'bovenkop' ) as $sleutel ) {
		$waarde = get_sub_field( $sleutel );
		if ( is_string( $waarde ) && '' !== trim( $waarde ) ) {
			$kop = $waarde;
			break;
		}
	}
	$kop = $kop ? wp_strip_all_tags( str_replace( array( '*', '<br>' ), array( '', ' ' ), $kop ) ) : '';
	$verborgen = get_sub_field( 'verbergen' ) ? '<span class="blok-status">Verborgen</span>' : '';

	return sprintf(
		'<span class="blok-label">%s</span>%s%s',
		esc_html( $layout['label'] ),
		$kop ? '<span class="blok-kop">' . esc_html( wp_trim_words( $kop, 10 ) ) . '</span>' : '',
		$verborgen
	);
}, 10, 4 );

/**
 * Toelichting bovenaan het bewerkscherm.
 */
add_action( 'edit_form_after_title', function ( $post ) {
	if ( ! in_array( $post->post_type, powerhub_blok_posttypes(), true ) ) {
		return;
	}
	$links = array( '<a href="' . esc_url( admin_url( 'admin.php?page=powerhub' ) ) . '">Powerhub</a>' );
	if ( function_exists( 'powerhub_designsysteem_url' ) && file_exists( powerhub_designsysteem_map() . '/tokens.json' ) ) {
		$links[] = '<a href="' . esc_url( powerhub_designsysteem_url() . '#blokken' ) . '" target="_blank" rel="noopener">alle blokken in het designsysteem</a>';
	}
	printf(
		'<div class="blokken-intro"><span class="blokken-intro-icoon">%s</span><div><strong>Bouw deze pagina met blokken.</strong> Voeg een blok toe, sleep blokken op volgorde en klap ze in voor overzicht. Elk blok heeft de tabs Inhoud, Weergave en Geavanceerd. Zie ook %s.</div></div>',
		powerhub_icoon( 'layers' ), // phpcs:ignore
		implode( ' en ', $links ) // phpcs:ignore
	);
} );
