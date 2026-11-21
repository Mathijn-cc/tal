<?php
/**
 * Module SEO: Rank Math doet het SEO-werk, Powerhub vult aan waar het thema iets moet doen
 * en bewaakt de valkuilen (staging in Google, productie uit Google, Rank Math niet actief).
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'seo', array(
	'titel'    => 'SEO',
	'uitleg'   => 'Aanvulling op Rank Math',
	'niveau'   => 'beheer',
	'volgorde' => 60,
	'velden'   => array(
		'kruimelpad'            => array( 'type' => 'schakel', 'label' => 'Kruimelpad tonen', 'uitleg' => 'Het kruimelpad van Rank Math boven de inhoud. Zet het ook aan in Rank Math > Algemeen > Kruimelpaden.', 'standaard' => false ),
		'seo_bewaking'          => array( 'type' => 'schakel', 'label' => 'Bewaking', 'uitleg' => 'Waarschuwt beheerders als een live site uit Google wordt gehouden of Rank Math niet actief is.', 'standaard' => true ),
		'bijlagen_doorsturen'   => array( 'type' => 'schakel', 'label' => 'Bijlagepagina\'s doorsturen', 'uitleg' => 'Stuurt de losse pagina van een afbeelding door naar het bestand zelf, tegen dunne pagina\'s in Google.', 'standaard' => true ),
	),
) );

function powerhub_rank_math_actief() {
	return class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * Staging herkennen aan het domein. Uit te breiden met de filter powerhub_staging_domeinen.
 */
function powerhub_is_staging() {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	foreach ( (array) apply_filters( 'powerhub_staging_domeinen', array( 'bijnadaar.nl', 'bijnaonline.com', 'staging', '.local', 'localhost' ) ) as $deel ) {
		if ( false !== stripos( $host, $deel ) ) {
			return true;
		}
	}
	return 'staging' === wp_get_environment_type() || 'development' === wp_get_environment_type();
}

/**
 * Kruimelpad van Rank Math. In templates: powerhub_kruimelpad().
 */
function powerhub_kruimelpad() {
	if ( ! powerhub_aan( 'kruimelpad' ) || is_front_page() || ! function_exists( 'rank_math_the_breadcrumbs' ) ) {
		return;
	}
	echo '<nav class="kruimelpad" aria-label="Kruimelpad">';
	rank_math_the_breadcrumbs();
	echo '</nav>';
}

add_action( 'template_redirect', function () {
	if ( powerhub_aan( 'bijlagen_doorsturen' ) && is_attachment() ) {
		$url = wp_get_attachment_url( get_queried_object_id() );
		if ( $url ) {
			wp_safe_redirect( $url, 301 );
			exit;
		}
	}
} );

add_action( 'admin_notices', function () {
	if ( ! powerhub_aan( 'seo_bewaking' ) || ! powerhub_is_beheerder() ) {
		return;
	}
	$staging = powerhub_is_staging();
	$zichtbaar = (bool) get_option( 'blog_public' );

	if ( ! $staging && ! $zichtbaar ) {
		echo '<div class="notice notice-error"><p><strong>Powerhub:</strong> deze live site wordt uit Google gehouden. Zet onder Instellingen &gt; Lezen het vinkje bij Zichtbaarheid voor zoekmachines uit.</p></div>';
	}
	if ( $staging && $zichtbaar ) {
		echo '<div class="notice notice-warning"><p><strong>Powerhub:</strong> deze staging is zichtbaar voor zoekmachines. Vink onder Instellingen &gt; Lezen Zichtbaarheid voor zoekmachines aan.</p></div>';
	}
	if ( ! powerhub_rank_math_actief() ) {
		echo '<div class="notice notice-warning"><p><strong>Powerhub:</strong> Rank Math is niet actief. Dat is onze standaard voor SEO.</p></div>';
	}
} );
