<?php
/**
 * TAL Leadership, thema-functies.
 *
 * powerhub/  het vaste skelet van Creators Connect. Niet aanpassen in een project.
 * inc/       alles wat specifiek is voor deze klant.
 */

defined( 'ABSPATH' ) || exit;

/* De uitrol plakt hier het runnummer achter (0.1.0 wordt 0.1.0.37), zodat browsers nooit een oude versie tonen. */
define( 'THEMA_VERSIE', '0.1.0' );
define( 'THEMA_PAD', get_template_directory() );
define( 'THEMA_URL', get_template_directory_uri() );

require THEMA_PAD . '/powerhub/powerhub.php';

require THEMA_PAD . '/inc/helpers.php';
require THEMA_PAD . '/inc/instellingen.php';
require THEMA_PAD . '/inc/formulieren.php';
require THEMA_PAD . '/inc/uitrol.php';
require THEMA_PAD . '/inc/toegankelijkheid.php';

/**
 * Basis thema-ondersteuning.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	add_image_size( 'kaart', 960, 600, true );
	add_image_size( 'breed', 2560, 0, false );

	register_nav_menus( array(
		'hoofdmenu' => 'Hoofdmenu',
		'footer_1'  => 'Footer kolom 1',
		'footer_2'  => 'Footer kolom 2',
		'juridisch' => 'Footer juridisch',
	) );
} );

/**
 * Stijlen en scripts van het thema. Powerhub hangt kleuren en lettertypes aan de stijl 'thema'.
 */
add_action( 'wp_enqueue_scripts', function () {
	$laden = array( 'in_footer' => true, 'strategy' => 'defer' );

	wp_enqueue_style( 'thema', THEMA_URL . '/assets/css/thema.css', array( 'tokens' ), THEMA_VERSIE );
	/* SplitText (GSAP) voor de koppen, alleen als de animaties aan staan. Alle beweging van de site staat in thema.js. */
	$nodig = array( 'powerhub-animaties' );
	if ( powerhub_aan( 'animaties' ) ) {
		wp_enqueue_script( 'gsap-splittext', THEMA_URL . '/assets/js/vendor/SplitText.min.js', array( 'gsap' ), '3.15.0', $laden );
		$nodig[] = 'gsap-splittext';
	}
	wp_enqueue_script( 'thema', THEMA_URL . '/assets/js/thema.js', $nodig, THEMA_VERSIE, $laden );

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );
