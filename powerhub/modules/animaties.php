<?php
/**
 * Module Animaties: GSAP, ScrollTrigger en Lenis, lokaal geladen en volledig uit te zetten.
 * Bezoekers met "beperkte beweging" krijgen nooit animaties.
 *
 * Gebruik in templates: data-animatie="omhoog" (of "vervagen", "links", "rechts") op een element,
 * data-animatie-vertraging="0.1" voor een vertraging, data-animatie-groep op een ouder om kinderen
 * na elkaar te laten verschijnen.
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'animaties', array(
	'titel'    => 'Animaties',
	'uitleg'   => 'Scroll-animaties en vloeiend scrollen',
	'niveau'   => 'beheer',
	'volgorde' => 50,
	'velden'   => array(
		'animaties'         => array( 'type' => 'schakel', 'label' => 'Animaties', 'uitleg' => 'GSAP en ScrollTrigger. Uit: alles staat direct stil op zijn plek.', 'standaard' => true ),
		'vloeiend_scrollen' => array( 'type' => 'schakel', 'label' => 'Vloeiend scrollen', 'uitleg' => 'Lenis. Alleen actief als animaties aan staan.', 'standaard' => false ),
		'animatie_duur'     => array( 'type' => 'getal', 'label' => 'Duur', 'uitleg' => 'In seconden, standaard 0.8.', 'standaard' => 0.8, 'min' => 0.2, 'max' => 2, 'stap' => 0.1 ),
		'animatie_afstand'  => array( 'type' => 'getal', 'label' => 'Afstand', 'uitleg' => 'Hoeveel pixels een element beweegt, standaard 24.', 'standaard' => 24, 'min' => 0, 'max' => 120, 'stap' => 1 ),
	),
) );

add_action( 'wp_enqueue_scripts', function () {
	$laden = array( 'in_footer' => true, 'strategy' => 'defer' );
	$nodig = array();

	if ( powerhub_aan( 'animaties' ) ) {
		wp_enqueue_script( 'gsap', POWERHUB_URL . '/assets/vendor/gsap.min.js', array(), '3', $laden );
		wp_enqueue_script( 'gsap-scrolltrigger', POWERHUB_URL . '/assets/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3', $laden );
		$nodig[] = 'gsap-scrolltrigger';
		if ( powerhub_aan( 'vloeiend_scrollen' ) ) {
			wp_enqueue_script( 'lenis', POWERHUB_URL . '/assets/vendor/lenis.min.js', array(), '1', $laden );
			$nodig[] = 'lenis';
		}
	}

	wp_enqueue_script( 'powerhub-animaties', POWERHUB_URL . '/assets/animaties.js', $nodig, POWERHUB_VERSIE, $laden );
	wp_add_inline_script( 'powerhub-animaties', 'window.powerhub=' . wp_json_encode( array(
		'animaties' => powerhub_aan( 'animaties' ),
		'vloeiend'  => powerhub_aan( 'animaties' ) && powerhub_aan( 'vloeiend_scrollen' ),
		'duur'      => (float) powerhub( 'animatie_duur', 0.8 ),
		'afstand'   => (int) powerhub( 'animatie_afstand', 24 ),
	) ) . ';', 'before' );
}, 5 );

/**
 * Elementen met data-animatie starten verborgen, maar alleen als JavaScript en animaties actief zijn.
 * Vangnet: na 3 seconden altijd zichtbaar.
 */
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
	if ( ! powerhub_aan( 'animaties' ) ) {
		return;
	}
	echo "<script>(function(d){if(window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;d.classList.add('animatie-wacht');setTimeout(function(){d.classList.remove('animatie-wacht');},3000);}(document.documentElement));</script>\n";
	echo "<style>.animatie-wacht [data-animatie]{opacity:0}</style>\n";
}, 1 );
