<?php
/**
 * Module Animaties: GSAP, ScrollTrigger, SplitText en Lenis, lokaal geladen en volledig uit te zetten.
 * Bezoekers met "beperkte beweging" krijgen nooit animaties; cijfers staan dan direct op hun eindwaarde.
 *
 * Drie lagen:
 * - Blokken: de inhoud van elk blok komt in beeld (standaard voor de site, per blok te overschrijven in Geavanceerd).
 * - Koppen: per niveau (h1, h2, h3) een eigen effect, bijvoorbeeld regels die uit een masker schuiven.
 * - Cijfers: getallen in het blok Cijfers tellen op zodra ze in beeld komen (data-teller).
 *
 * Losse elementen in templates: data-animatie="omhoog" (of "vervagen", "links", "rechts", "zoom"),
 * data-animatie-vertraging="0.1", en data-animatie-groep op een ouder om kinderen na elkaar te laten verschijnen.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keuzes, ook gebruikt voor het veld Animatie in Geavanceerd van elk blok.
 */
function powerhub_animatie_keuzes() {
	return array(
		'geen'     => 'Geen',
		'omhoog'   => 'Omhoog vervagen',
		'vervagen' => 'Alleen vervagen',
		'links'    => 'Van links',
		'rechts'   => 'Van rechts',
		'zoom'     => 'Inzoomen',
	);
}

function powerhub_kop_effecten() {
	return array(
		'geen'      => 'Geen',
		'omhoog'    => 'Hele kop omhoog',
		'vervagen'  => 'Hele kop vervagen',
		'regels'    => 'Regels uit een masker',
		'woorden'   => 'Woord voor woord',
		'letters'   => 'Letter voor letter',
		'onthullen' => 'Onthullen van links naar rechts',
	);
}

add_action( 'powerhub_registreren', function () {
	$easing = array(
		'power2.out'    => 'Rustig (power2)',
		'power3.out'    => 'Vloeiend (power3)',
		'expo.out'      => 'Snel en zacht uitlopen (expo)',
		'sine.out'      => 'Zacht (sine)',
		'back.out(1.4)' => 'Met een klein veertje (back)',
	);
	powerhub_tab( 'animaties', array(
		'titel'    => 'Animaties',
		'uitleg'   => 'Blokken, koppen en cijfers',
		'niveau'   => 'beheer',
		'volgorde' => 50,
		'velden'   => array(
			'kop_animatie_algemeen' => array( 'type' => 'kop', 'label' => 'Algemeen', 'uitleg' => 'GSAP met ScrollTrigger. Wie op zijn apparaat minder beweging heeft ingesteld, ziet nooit animaties.' ),
			'animaties'         => array( 'type' => 'schakel', 'label' => 'Animaties', 'uitleg' => 'Uit: alles staat direct op zijn plek. Cijfers tellen dan ook niet op.', 'standaard' => true ),
			'vloeiend_scrollen' => array( 'type' => 'schakel', 'label' => 'Vloeiend scrollen', 'uitleg' => 'Lenis. Alleen actief als animaties aan staan.', 'standaard' => false ),
			'animatie_easing'   => array( 'type' => 'keuze', 'label' => 'Verloop', 'keuzes' => $easing, 'standaard' => 'power3.out' ),
			'animatie_start'    => array( 'type' => 'keuze', 'label' => 'Begint wanneer', 'keuzes' => array( '95' => 'Het blok net in beeld komt', '88' => 'Het blok een stukje in beeld is', '75' => 'Het blok een kwart in beeld is' ), 'standaard' => '88' ),
			'animatie_herhalen' => array( 'type' => 'schakel', 'label' => 'Opnieuw bij terugscrollen', 'uitleg' => 'Uit: elke animatie speelt één keer.', 'standaard' => false ),

			'kop_animatie_blokken' => array( 'type' => 'kop', 'label' => 'Blokken', 'uitleg' => 'Hoe de inhoud van een blok in beeld komt. Per blok aan te passen in Geavanceerd. Op mobiel is de beweging de helft.' ),
			'blok_animatie'     => array( 'type' => 'keuze', 'label' => 'Effect', 'keuzes' => powerhub_animatie_keuzes(), 'standaard' => 'omhoog' ),
			'blok_volgorde'     => array( 'type' => 'keuze', 'label' => 'Volgorde', 'keuzes' => array( 'samen' => 'Alles tegelijk', 'na-elkaar' => 'Onderdelen na elkaar' ), 'standaard' => 'samen' ),
			'animatie_duur'     => array( 'type' => 'getal', 'label' => 'Duur', 'uitleg' => 'In seconden.', 'standaard' => 0.8, 'min' => 0.2, 'max' => 3, 'stap' => 0.1 ),
			'animatie_afstand'  => array( 'type' => 'getal', 'label' => 'Afstand', 'uitleg' => 'Hoeveel pixels de inhoud beweegt.', 'standaard' => 24, 'min' => 0, 'max' => 120, 'stap' => 1 ),
			'blok_tussenpoos'   => array( 'type' => 'getal', 'label' => 'Tussenpoos', 'uitleg' => 'Seconden tussen onderdelen bij "na elkaar".', 'standaard' => 0.12, 'min' => 0, 'max' => 1, 'stap' => 0.02 ),

			'kop_animatie_koppen' => array( 'type' => 'kop', 'label' => 'Koppen', 'uitleg' => 'Een eigen effect per kopniveau. Schermlezers lezen de kop gewoon in zijn geheel voor.' ),
			'kop_h1_effect'     => array( 'type' => 'keuze', 'label' => 'H1 (paginakop)', 'keuzes' => powerhub_kop_effecten(), 'standaard' => 'geen' ),
			'kop_h2_effect'     => array( 'type' => 'keuze', 'label' => 'H2 (blokkop)', 'keuzes' => powerhub_kop_effecten(), 'standaard' => 'geen' ),
			'kop_h3_effect'     => array( 'type' => 'keuze', 'label' => 'H3 (kaarten, tussenkoppen)', 'keuzes' => powerhub_kop_effecten(), 'standaard' => 'geen' ),
			'koppen_duur'       => array( 'type' => 'getal', 'label' => 'Duur', 'uitleg' => 'In seconden, per regel, woord of letter.', 'standaard' => 0.9, 'min' => 0.2, 'max' => 3, 'stap' => 0.1 ),
			'koppen_tussenpoos' => array( 'type' => 'getal', 'label' => 'Tussenpoos', 'uitleg' => 'Seconden tussen regels, woorden of letters.', 'standaard' => 0.08, 'min' => 0, 'max' => 0.5, 'stap' => 0.01 ),
			'koppen_easing'     => array( 'type' => 'keuze', 'label' => 'Verloop', 'keuzes' => $easing, 'standaard' => 'expo.out' ),
			'koppen_accent'     => array( 'type' => 'keuze', 'label' => 'Accentwoorden', 'keuzes' => array( 'mee' => 'Gewoon mee', 'na' => 'Komen als laatste' ), 'standaard' => 'mee' ),

			'kop_animatie_cijfers' => array( 'type' => 'kop', 'label' => 'Cijfers', 'uitleg' => 'Getallen in het blok Cijfers tellen op vanaf nul. Voor- en achtervoegsels (K+, %, x, uur) blijven staan.' ),
			'teller_duur'       => array( 'type' => 'getal', 'label' => 'Duur', 'uitleg' => 'In seconden.', 'standaard' => 1.8, 'min' => 0.4, 'max' => 5, 'stap' => 0.1 ),
		),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	$laden = array( 'in_footer' => true, 'strategy' => 'defer' );
	$nodig = array();
	$aan   = powerhub_aan( 'animaties' );

	$koppen = array();
	foreach ( array( 'h1', 'h2', 'h3' ) as $niveau ) {
		$effect = (string) powerhub( 'kop_' . $niveau . '_effect', 'geen' );
		if ( 'geen' !== $effect && isset( powerhub_kop_effecten()[ $effect ] ) ) {
			$koppen[ $niveau ] = $effect;
		}
	}
	$splitsen = (bool) array_intersect( $koppen, array( 'regels', 'woorden', 'letters' ) );

	if ( $aan ) {
		wp_enqueue_script( 'gsap', POWERHUB_URL . '/assets/vendor/gsap.min.js', array(), '3.15.0', $laden );
		wp_enqueue_script( 'gsap-scrolltrigger', POWERHUB_URL . '/assets/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.15.0', $laden );
		$nodig[] = 'gsap-scrolltrigger';
		if ( $splitsen ) {
			wp_enqueue_script( 'gsap-splittext', POWERHUB_URL . '/assets/vendor/SplitText.min.js', array( 'gsap' ), '3.15.0', $laden );
			$nodig[] = 'gsap-splittext';
		}
		if ( powerhub_aan( 'vloeiend_scrollen' ) ) {
			wp_enqueue_script( 'lenis', POWERHUB_URL . '/assets/vendor/lenis.min.js', array(), '1', $laden );
			$nodig[] = 'lenis';
		}
	}

	wp_enqueue_script( 'powerhub-animaties', POWERHUB_URL . '/assets/animaties.js', $nodig, powerhub_versie( '/assets/animaties.js' ), $laden );
	wp_add_inline_script( 'powerhub-animaties', 'window.powerhub=' . wp_json_encode( array(
		'animaties' => $aan,
		'vloeiend'  => $aan && powerhub_aan( 'vloeiend_scrollen' ),
		'easing'    => (string) powerhub( 'animatie_easing', 'power3.out' ),
		'start'     => 'top ' . (int) powerhub( 'animatie_start', 88 ) . '%',
		'herhalen'  => powerhub_aan( 'animatie_herhalen' ),
		'blok'      => (string) powerhub( 'blok_animatie', 'omhoog' ),
		'volgorde'  => (string) powerhub( 'blok_volgorde', 'samen' ),
		'duur'      => (float) powerhub( 'animatie_duur', 0.8 ),
		'afstand'   => (int) powerhub( 'animatie_afstand', 24 ),
		'tussenpoos' => (float) powerhub( 'blok_tussenpoos', 0.12 ),
		'koppen'    => (object) $koppen,
		'kopDuur'   => (float) powerhub( 'koppen_duur', 0.9 ),
		'kopTussenpoos' => (float) powerhub( 'koppen_tussenpoos', 0.08 ),
		'kopEasing' => (string) powerhub( 'koppen_easing', 'expo.out' ),
		'kopAccent' => (string) powerhub( 'koppen_accent', 'mee' ),
		'tellerDuur' => (float) powerhub( 'teller_duur', 1.8 ),
	) ) . ';', 'before' );
}, 5 );

/**
 * Wat gaat animeren start verborgen, maar alleen als JavaScript en animaties actief zijn en de bezoeker beweging toestaat.
 * Vangnet: na 3 seconden is alles altijd zichtbaar, ook als een script niet laadt.
 */
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
	if ( ! powerhub_aan( 'animaties' ) ) {
		return;
	}
	echo "<script>(function(d){if(window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;d.classList.add('animatie-wacht');setTimeout(function(){d.classList.remove('animatie-wacht');},3000);}(document.documentElement));</script>\n";
	$verborgen = array( '.animatie-wacht [data-animatie]' );
	if ( 'geen' !== powerhub( 'blok_animatie', 'omhoog' ) ) {
		$verborgen[] = '.animatie-wacht .sectie:not([data-blokanimatie="geen"]) > .binnen';
	}
	$verborgen[] = '.animatie-wacht .sectie[data-blokanimatie]:not([data-blokanimatie="geen"]) > .binnen';
	foreach ( array( 'h1', 'h2', 'h3' ) as $niveau ) {
		if ( 'geen' !== powerhub( 'kop_' . $niveau . '_effect', 'geen' ) ) {
			$verborgen[] = '.animatie-wacht .sectie ' . $niveau . '.kop';
		}
	}
	echo '<style>' . implode( ',', $verborgen ) . "{opacity:0}"
		/* Het masker van "regels" knipt anders onderkasten (g, j, p) en cursieve uitlopers af: wat ruimte erbij, met negatieve marge terug. */
		. '.kop-regel-mask{padding:0 .12em .2em;margin:0 -.12em -.2em}'
		. "</style>\n";
}, 1 );
