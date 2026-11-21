<?php
/**
 * Module Onderhoud en beveiliging: onderhoudsmodus en de basisverharding die op elke site hoort.
 * Alles staat standaard aan behalve de onderhoudsmodus. Zie ook het websitebeveiligingsbeleid.
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'beveiliging', array(
	'titel'    => 'Onderhoud en beveiliging',
	'uitleg'   => 'Onderhoudsmodus en verharding',
	'niveau'   => 'beheer',
	'volgorde' => 70,
	'velden'   => array(
		'kop_onderhoud'         => array( 'type' => 'kop', 'label' => 'Onderhoud' ),
		'onderhoud'             => array( 'type' => 'schakel', 'label' => 'Onderhoudsmodus', 'uitleg' => 'Bezoekers zien een onderhoudspagina (503, zodat Google later terugkomt). Ingelogde gebruikers zien de site gewoon.' ),
		'onderhoud_tekst'       => array( 'type' => 'tekstvak', 'label' => 'Tekst onderhoudspagina', 'standaard' => 'We werken even aan de website. Kom straks terug.' ),

		'kop_verharding'        => array( 'type' => 'kop', 'label' => 'Verharding' ),
		'xmlrpc_uit'            => array( 'type' => 'schakel', 'label' => 'XML-RPC uit', 'uitleg' => 'Veelgebruikte aanvalsroute voor wachtwoordaanvallen. Alleen aanlaten voor de Jetpack-app of oude koppelingen.', 'standaard' => true ),
		'gebruikers_verbergen'  => array( 'type' => 'schakel', 'label' => 'Gebruikersnamen verbergen', 'uitleg' => 'Blokkeert ?author= en de gebruikerslijst in de REST API voor bezoekers.', 'standaard' => true ),
		'versie_verbergen'      => array( 'type' => 'schakel', 'label' => 'WordPress-versie verbergen', 'standaard' => true ),
		'bestandseditor_uit'    => array( 'type' => 'schakel', 'label' => 'Bestandseditor uit', 'uitleg' => 'Geen thema- en plugincode bewerken in wp-admin. Het thema komt uit GitHub.', 'standaard' => true ),
		'login_melding'         => array( 'type' => 'schakel', 'label' => 'Algemene inlogfout', 'uitleg' => 'Verraadt niet of de gebruikersnaam bestaat.', 'standaard' => true ),

		'kop_opschonen'         => array( 'type' => 'kop', 'label' => 'Opschonen' ),
		'emoji_uit'             => array( 'type' => 'schakel', 'label' => 'Emoji-scripts uit', 'uitleg' => 'Scheelt een extra script op elke pagina.', 'standaard' => true ),
		'dashboard_opschonen'   => array( 'type' => 'schakel', 'label' => 'Dashboard opschonen', 'uitleg' => 'Haalt WordPress-nieuws en welkomstpaneel weg.', 'standaard' => true ),
	),
) );

/* Onderhoudsmodus */
add_action( 'template_redirect', function () {
	if ( ! powerhub_aan( 'onderhoud' ) || is_user_logged_in() ) {
		return;
	}
	status_header( 503 );
	header( 'Retry-After: 3600' );
	nocache_headers();
	$tekst = powerhub( 'onderhoud_tekst' );
	$sjabloon = locate_template( 'onderhoud.php' );
	if ( $sjabloon ) {
		include $sjabloon;
		exit;
	}
	printf(
		'<!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>%1$s</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,sans-serif;background:#f6f6f4;color:#1d2733;text-align:center;padding:2rem}main{max-width:32rem}h1{font-size:1.6rem}</style></head><body><main><h1>%1$s</h1><p>%2$s</p></main></body></html>',
		esc_html( get_bloginfo( 'name' ) ),
		esc_html( $tekst )
	);
	exit;
}, 1 );

/* Melding in de adminbalk zodat niemand de onderhoudsmodus vergeet. */
add_action( 'admin_bar_menu', function ( $balk ) {
	if ( powerhub_aan( 'onderhoud' ) && current_user_can( 'edit_theme_options' ) ) {
		$balk->add_node( array(
			'id'    => 'powerhub-onderhoud',
			'title' => 'Onderhoudsmodus aan',
			'href'  => admin_url( 'admin.php?page=powerhub&tab=beveiliging' ),
			'meta'  => array( 'html' => '<style>#wp-admin-bar-powerhub-onderhoud>a{background:#c0392b!important;color:#fff!important}</style>' ),
		) );
	}
}, 100 );

/* XML-RPC */
add_filter( 'xmlrpc_enabled', function ( $aan ) {
	return powerhub_aan( 'xmlrpc_uit' ) ? false : $aan;
} );
/* xmlrpc_enabled zet alleen de ingelogde methodes uit; daarom het hele verzoek weigeren. */
add_action( 'init', function () {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST && powerhub_aan( 'xmlrpc_uit' ) ) {
		status_header( 403 );
		exit;
	}
}, 0 );
add_filter( 'wp_headers', function ( $headers ) {
	if ( powerhub_aan( 'xmlrpc_uit' ) ) {
		unset( $headers['X-Pingback'] );
	}
	return $headers;
} );

/* Gebruikersnamen */
add_filter( 'rest_endpoints', function ( $routes ) {
	if ( powerhub_aan( 'gebruikers_verbergen' ) && ! is_user_logged_in() ) {
		unset( $routes['/wp/v2/users'], $routes['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $routes;
} );
add_action( 'template_redirect', function () {
	if ( powerhub_aan( 'gebruikers_verbergen' ) && ! is_user_logged_in() && ( isset( $_GET['author'] ) || is_author() ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 5 );

/* Versie */
add_action( 'init', function () {
	if ( powerhub_aan( 'versie_verbergen' ) ) {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}
	if ( powerhub_aan( 'emoji_uit' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
} );

/* Bestandseditor */
add_filter( 'map_meta_cap', function ( $rechten, $recht ) {
	if ( in_array( $recht, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) && powerhub_aan( 'bestandseditor_uit' ) ) {
		return array( 'do_not_allow' );
	}
	return $rechten;
}, 10, 2 );

/* Inlogfout */
add_filter( 'login_errors', function ( $fout ) {
	return powerhub_aan( 'login_melding' ) ? 'De gebruikersnaam of het wachtwoord klopt niet.' : $fout;
} );

/* Dashboard */
add_action( 'wp_dashboard_setup', function () {
	if ( ! powerhub_aan( 'dashboard_opschonen' ) ) {
		return;
	}
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}, 20 );
