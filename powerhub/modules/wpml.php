<?php
/**
 * Module WPML: taalwissel in het menu en vertaalbare Powerhub-teksten.
 * Werkt ook zonder WPML: dan toont de taalwissel alleen de huidige taal, zodat het ontwerp klopt tot WPML er is.
 *
 * Geoptimaliseerd voor WPML:
 * - de taalwissel komt uit wpml_active_languages, met keuze voor code, naam of beide en wat er gebeurt zonder vertaling;
 * - tekstvelden uit Powerhub (zoals de footertekst en de hoofdknop) zijn te vertalen via WPML String Translation;
 * - de veldgroep met blokken staat voor ACF Multilingual op "vertalen" (zelfde blokken per taal, eigen inhoud).
 */

defined( 'ABSPATH' ) || exit;

function powerhub_wpml_actief() {
	return defined( 'ICL_SITEPRESS_VERSION' ) || has_filter( 'wpml_active_languages' );
}

add_action( 'powerhub_registreren', function () {
	powerhub_tab( 'wpml', array(
		'titel'    => 'WPML',
		'uitleg'   => 'Talen en taalwissel',
		'niveau'   => 'beheer',
		'volgorde' => 70,
		'velden'   => array(
			'kop_wpml_status'      => array(
				'type'   => 'kop',
				'label'  => powerhub_wpml_actief() ? 'WPML is actief' : 'WPML is (nog) niet actief',
				'uitleg' => powerhub_wpml_actief() ? 'De taalwissel toont de talen uit WPML.' : 'Tot WPML actief is, toont de taalwissel alleen de huidige taal.',
			),
			'taalwissel'           => array( 'type' => 'schakel', 'label' => 'Taalwissel in het menu', 'uitleg' => 'Knop met de huidige taal; klik toont de andere talen.', 'standaard' => true ),
			'taalwissel_weergave'  => array(
				'type'      => 'keuze',
				'label'     => 'Weergave',
				'keuzes'    => array( 'code' => 'Taalcode (NL)', 'naam' => 'Naam (Nederlands)', 'beide' => 'Beide (NL, Nederlands in de lijst)' ),
				'standaard' => 'beide',
			),
			'taalwissel_ontbrekend' => array(
				'type'      => 'keuze',
				'label'     => 'Pagina zonder vertaling',
				'keuzes'    => array( 'home' => 'Taal tonen, linken naar de homepage van die taal', 'verbergen' => 'Taal verbergen' ),
				'standaard' => 'home',
			),
			'wpml_teksten'         => array( 'type' => 'schakel', 'label' => 'Powerhub-teksten vertaalbaar', 'uitleg' => 'Tekstvelden uit Powerhub verschijnen in WPML, String Translation (domein Powerhub).', 'standaard' => true ),
		),
	) );
} );

/**
 * Talen voor de taalwissel: array( array( 'code' => 'nl', 'naam' => 'Nederlands', 'url' => '...', 'actief' => true ) ).
 */
function powerhub_talen() {
	if ( powerhub_wpml_actief() ) {
		$verbergen = 'verbergen' === powerhub( 'taalwissel_ontbrekend', 'home' );
		$talen     = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => $verbergen ? 1 : 0, 'orderby' => 'custom' ) );
		$uit       = array();
		foreach ( (array) $talen as $taal ) {
			$uit[] = array(
				'code'   => (string) ( $taal['language_code'] ?? $taal['code'] ?? '' ),
				'naam'   => (string) ( $taal['native_name'] ?? $taal['translated_name'] ?? '' ),
				'url'    => (string) ( $taal['url'] ?? '' ),
				'actief' => ! empty( $taal['active'] ),
			);
		}
		if ( $uit ) {
			return $uit;
		}
	}
	$code = strtolower( substr( determine_locale(), 0, 2 ) );
	return array( array( 'code' => $code, 'naam' => '', 'url' => home_url( '/' ), 'actief' => true ) );
}

/**
 * Taalwissel als HTML: een knop met de huidige taal en een lijst met de andere talen (disclosure, toetsenbord en schermlezer).
 */
function powerhub_taalwissel( $class = '' ) {
	if ( ! powerhub_aan( 'taalwissel' ) ) {
		return '';
	}
	$talen    = powerhub_talen();
	$weergave = powerhub( 'taalwissel_weergave', 'beide' );
	$huidig   = $talen[0];
	foreach ( $talen as $taal ) {
		if ( $taal['actief'] ) {
			$huidig = $taal;
		}
	}
	$label  = 'naam' === $weergave && $huidig['naam'] ? $huidig['naam'] : strtoupper( $huidig['code'] );
	$anders = array_filter( $talen, function ( $t ) {
		return ! $t['actief'];
	} );
	$pijl = function_exists( 'powerhub_icoon' ) ? powerhub_icoon( 'chevron-down' ) : '';

	if ( ! $anders ) {
		return sprintf( '<span class="%s"><span class="taalwissel-huidig">%s</span>%s</span>', esc_attr( trim( 'taalwissel ' . $class ) ), esc_html( $label ), $pijl );
	}
	wp_enqueue_script( 'powerhub-taalwissel', POWERHUB_URL . '/assets/taalwissel.js', array(), powerhub_versie( '/assets/taalwissel.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );

	$id    = 'taalwissel-' . wp_unique_id();
	$items = '';
	foreach ( $anders as $taal ) {
		$tekst = 'code' === $weergave || ! $taal['naam'] ? strtoupper( $taal['code'] ) : ( 'beide' === $weergave ? strtoupper( $taal['code'] ) . ' <span class="taalwissel-naam">' . esc_html( $taal['naam'] ) . '</span>' : esc_html( $taal['naam'] ) );
		$items .= sprintf( '<li><a href="%s" hreflang="%s" lang="%s">%s</a></li>', esc_url( $taal['url'] ), esc_attr( $taal['code'] ), esc_attr( $taal['code'] ), wp_kses( $tekst, array( 'span' => array( 'class' => true ) ) ) );
	}
	return sprintf(
		'<div class="%1$s" data-taalwissel><button type="button" class="taalwissel-knop" aria-expanded="false" aria-controls="%2$s"><span class="screen-reader-text">Taal: </span>%3$s%4$s</button><ul class="taalwissel-lijst" id="%2$s" hidden>%5$s</ul></div>',
		esc_attr( trim( 'taalwissel ' . $class ) ),
		esc_attr( $id ),
		esc_html( $label ),
		$pijl,
		$items
	);
}

/* Powerhub-teksten via WPML String Translation: registreren bij opslaan, vertalen bij uitlezen. */
add_filter( 'powerhub_waarde', function ( $waarde, $sleutel, $veld ) {
	if ( is_admin() || ! powerhub_wpml_actief() || ! is_string( $waarde ) || '' === $waarde ) {
		return $waarde;
	}
	if ( ! in_array( $veld['type'] ?? '', array( 'tekst', 'tekstvak' ), true ) || ! powerhub( 'wpml_teksten', true ) ) {
		return $waarde;
	}
	return apply_filters( 'wpml_translate_single_string', $waarde, 'Powerhub', $sleutel );
}, 10, 3 );

add_action( 'update_option_' . POWERHUB_OPTIE, function ( $oud, $nieuw ) {
	if ( ! powerhub_wpml_actief() || ! is_array( $nieuw ) ) {
		return;
	}
	foreach ( powerhub_alle_velden() as $sleutel => $veld ) {
		if ( in_array( $veld['type'], array( 'tekst', 'tekstvak' ), true ) && ! empty( $nieuw[ $sleutel ] ) && is_string( $nieuw[ $sleutel ] ) ) {
			do_action( 'wpml_register_single_string', 'Powerhub', $sleutel, $nieuw[ $sleutel ] );
		}
	}
}, 10, 2 );
