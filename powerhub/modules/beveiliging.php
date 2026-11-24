<?php
/**
 * Module Onderhoud en beveiliging: onderhoudsmodus en de basisverharding die op elke site hoort.
 * Alles staat standaard aan behalve de onderhoudsmodus. Zie ook het websitebeveiligingsbeleid.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Security Optimizer van SiteGround (SG Security, plugin sg-security) is onze standaard op elke site.
 * Wat die plugin ook kan, laat Powerhub dan aan de plugin over, zodat niets dubbel of tegenstrijdig staat:
 * XML-RPC uitzetten, WordPress-versie verbergen en de thema- en plugineditor uitzetten.
 */
function powerhub_sg_security() {
	$actief = (array) get_option( 'active_plugins', array() );
	if ( in_array( 'sg-security/sg-security.php', $actief, true ) ) {
		return true;
	}
	return is_multisite() && array_key_exists( 'sg-security/sg-security.php', (array) get_site_option( 'active_sitewide_plugins', array() ) );
}

function powerhub_sg_security_taken() {
	return array( 'xmlrpc_uit', 'versie_verbergen', 'bestandseditor_uit' );
}

/**
 * Verharding uit Powerhub, behalve wat SG Security regelt.
 */
function powerhub_verharding( $sleutel ) {
	if ( powerhub_sg_security() && in_array( $sleutel, powerhub_sg_security_taken(), true ) ) {
		return false;
	}
	return powerhub_aan( $sleutel );
}

$powerhub_verharding_velden = array(
	'kop_verharding'        => array(
		'type'   => 'kop',
		'label'  => 'Verharding',
		'uitleg' => powerhub_sg_security()
			? 'SG Security (Security Optimizer) is actief. XML-RPC, de WordPress-versie en de bestandseditor regel je daar, onder Site Security. Powerhub doet alleen wat de plugin niet doet.'
			: 'Let op: SG Security (Security Optimizer van SiteGround) is niet actief. Dat is onze standaard; installeer en activeer de plugin. Tot die tijd doet Powerhub de basis.',
	),
	'xmlrpc_uit'            => array( 'type' => 'schakel', 'label' => 'XML-RPC uit', 'uitleg' => 'Veelgebruikte aanvalsroute voor wachtwoordaanvallen. Alleen aanlaten voor de Jetpack-app of oude koppelingen.', 'standaard' => true ),
	'gebruikers_verbergen'  => array( 'type' => 'schakel', 'label' => 'Gebruikersnamen verbergen', 'uitleg' => 'Blokkeert ?author=, auteurspagina\'s en de gebruikerslijst in de REST API voor bezoekers. SG Security doet dit niet.', 'standaard' => true ),
	'versie_verbergen'      => array( 'type' => 'schakel', 'label' => 'WordPress-versie verbergen', 'standaard' => true ),
	'bestandseditor_uit'    => array( 'type' => 'schakel', 'label' => 'Bestandseditor uit', 'uitleg' => 'Geen thema- en plugincode bewerken in wp-admin. Het thema komt uit GitHub.', 'standaard' => true ),
	'login_melding'         => array( 'type' => 'schakel', 'label' => 'Algemene inlogfout', 'uitleg' => 'Verraadt niet of de gebruikersnaam bestaat. SG Security doet dit niet; inlogpogingen beperken, 2FA en een eigen inlogadres wel (onder Login Security).', 'standaard' => true ),
);
if ( powerhub_sg_security() ) {
	foreach ( powerhub_sg_security_taken() as $sleutel ) {
		unset( $powerhub_verharding_velden[ $sleutel ] );
	}
}

powerhub_tab( 'beveiliging', array(
	'titel'    => 'Onderhoud en beveiliging',
	'uitleg'   => 'Onderhoudsmodus en verharding',
	'niveau'   => 'beheer',
	'volgorde' => 70,
	'velden'   => array(
		'kop_onderhoud'         => array( 'type' => 'kop', 'label' => 'Onderhoud' ),
		'onderhoud'             => array( 'type' => 'schakel', 'label' => 'Onderhoudsmodus', 'uitleg' => 'Bezoekers zien een onderhoudspagina (503, zodat Google later terugkomt). Ingelogde gebruikers zien de site gewoon.' ),
		'onderhoud_tekst'       => array( 'type' => 'tekstvak', 'label' => 'Tekst onderhoudspagina', 'standaard' => 'We werken even aan de website. Kom straks terug.' ),
	) + $powerhub_verharding_velden + array(
		'kop_opschonen'         => array( 'type' => 'kop', 'label' => 'Opschonen' ),
		'emoji_uit'             => array( 'type' => 'schakel', 'label' => 'Emoji-scripts uit', 'uitleg' => 'Scheelt een extra script op elke pagina.', 'standaard' => true ),
		'dashboard_opschonen'   => array( 'type' => 'schakel', 'label' => 'Dashboard opschonen', 'uitleg' => 'Haalt WordPress-nieuws en welkomstpaneel weg.', 'standaard' => true ),
		'reacties_uit'          => array( 'type' => 'schakel', 'label' => 'Reacties uit', 'uitleg' => 'Geen reacties en pingbacks op de hele site, en Reacties verdwijnt uit het menu, de adminbalk en het dashboard. Bestaande reacties blijven bewaard en komen terug als je dit uitzet.', 'standaard' => true ),
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
	return powerhub_verharding( 'xmlrpc_uit' ) ? false : $aan;
} );
/* xmlrpc_enabled zet alleen de ingelogde methodes uit; daarom het hele verzoek weigeren. */
add_action( 'init', function () {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST && powerhub_verharding( 'xmlrpc_uit' ) ) {
		status_header( 403 );
		exit;
	}
}, 0 );
add_filter( 'wp_headers', function ( $headers ) {
	if ( powerhub_verharding( 'xmlrpc_uit' ) ) {
		unset( $headers['X-Pingback'] );
	}
	return $headers;
} );

/* Gebruikersnamen */
add_filter( 'rest_endpoints', function ( $routes ) {
	if ( powerhub_verharding( 'gebruikers_verbergen' ) && ! is_user_logged_in() ) {
		unset( $routes['/wp/v2/users'], $routes['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $routes;
} );
add_action( 'template_redirect', function () {
	if ( powerhub_verharding( 'gebruikers_verbergen' ) && ! is_user_logged_in() && ( isset( $_GET['author'] ) || is_author() ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 5 );

/* Versie */
add_action( 'init', function () {
	if ( powerhub_verharding( 'versie_verbergen' ) ) {
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
	if ( in_array( $recht, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) && powerhub_verharding( 'bestandseditor_uit' ) ) {
		return array( 'do_not_allow' );
	}
	return $rechten;
}, 10, 2 );

/* Inlogfout */
add_filter( 'login_errors', function ( $fout ) {
	return powerhub_verharding( 'login_melding' ) ? 'De gebruikersnaam of het wachtwoord klopt niet.' : $fout;
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

/*
 * Reacties uit: overal dicht, nergens zichtbaar. Bestaande reacties blijven in de database staan.
 */
function powerhub_reacties_uit() {
	return powerhub_aan( 'reacties_uit' );
}

/* Geen ondersteuning meer voor reacties en trackbacks: weg uit de editor, de lijsten en Snel bewerken. */
add_action( 'init', function () {
	if ( ! powerhub_reacties_uit() ) {
		return;
	}
	foreach ( get_post_types() as $soort ) {
		if ( post_type_supports( $soort, 'comments' ) ) {
			remove_post_type_support( $soort, 'comments' );
			remove_post_type_support( $soort, 'trackbacks' );
		}
	}
}, 100 );

/* Dicht voor nieuwe reacties en pingbacks, en geen bestaande reacties op de voorkant. */
add_filter( 'comments_open', function ( $open ) {
	return powerhub_reacties_uit() ? false : $open;
}, 20 );
add_filter( 'pings_open', function ( $open ) {
	return powerhub_reacties_uit() ? false : $open;
}, 20 );
add_filter( 'comments_array', function ( $reacties ) {
	return powerhub_reacties_uit() ? array() : $reacties;
}, 20 );
add_filter( 'get_comments_number', function ( $aantal ) {
	return powerhub_reacties_uit() ? 0 : $aantal;
}, 20 );
add_filter( 'feed_links_show_comments_feed', function ( $tonen ) {
	return powerhub_reacties_uit() ? false : $tonen;
} );
add_action( 'template_redirect', function () {
	if ( powerhub_reacties_uit() && is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

/* Geen X-Pingback-kop en geen reacties via de REST API of XML-RPC. */
add_filter( 'wp_headers', function ( $koppen ) {
	if ( powerhub_reacties_uit() ) {
		unset( $koppen['X-Pingback'] );
	}
	return $koppen;
} );
add_filter( 'rest_endpoints', function ( $routes ) {
	if ( powerhub_reacties_uit() ) {
		foreach ( array_keys( $routes ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/comments' ) ) {
				unset( $routes[ $route ] );
			}
		}
	}
	return $routes;
} );
add_filter( 'xmlrpc_methods', function ( $methodes ) {
	if ( powerhub_reacties_uit() ) {
		unset( $methodes['wp.newComment'], $methodes['pingback.ping'], $methodes['pingback.extensions.getPingbacks'] );
	}
	return $methodes;
} );

/* Weg uit het menu (Reacties en Instellingen › Discussie), de adminbalk en het dashboard. */
add_action( 'admin_menu', function () {
	if ( ! powerhub_reacties_uit() ) {
		return;
	}
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}, 999 );
add_action( 'admin_init', function () {
	if ( ! powerhub_reacties_uit() ) {
		return;
	}
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
add_action( 'admin_bar_menu', function ( $balk ) {
	if ( powerhub_reacties_uit() ) {
		$balk->remove_node( 'comments' );
	}
}, 999 );
add_action( 'wp_dashboard_setup', function () {
	if ( powerhub_reacties_uit() ) {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
}, 999 );
/* Het tellertje "x reacties" in Activiteit en Op dit moment. */
add_action( 'admin_head-index.php', function () {
	if ( powerhub_reacties_uit() ) {
		echo '<style>#dashboard_right_now .comment-count,#dashboard_right_now .comment-mod-count,#dashboard_activity #latest-comments{display:none}</style>';
	}
} );
