<?php
/**
 * Blok Afsluiter: donker vlak met een foto rechts op de achtergrond, een grote vraag en een ronde knop
 * die meebeweegt met de muis.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'cta';
	$layouts[ $p ] = array(
		'label'      => 'Afsluiter',
		'icoon'      => 'calendar-check',
		'uitleg'     => 'Grote vraag met een ronde knop, met een foto op de achtergrond.',
		'volgorde'   => 120,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Label', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'knop', 'Ronde knop', 'link', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'email', 'E-mailadres', 'email', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'regel', 'Regel onder het e-mailadres', 'text', powerhub_acf_breed( 50 ) ),
				tal_acf_beeld( $p, 'beeld', 'Achtergrondfoto', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Staat rechts op twee derde van de breedte, met een lichte donkere laag erover.' ) ) ),
			),
			tal_acf_uitsnede( $p, 35, 55 ),
			powerhub_acf_weergave( $p, 'donker' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['cta'] = array(
		'label'         => 'TAL leader journey',
		'kop'           => 'Ben jij klaar om jouw *leiderschapsreis* met mij te starten?',
		'knop'          => array( 'url' => 'https://calendly.com/chantal-tal-leadership/30min', 'title' => 'Plan een kennismaking' ),
		'email'         => 'chantal@tal-leadership.nl',
		'regel'         => 'tal-leadership.nl',
		'beeld'         => tal_beeld_url( 'tal-5.jpg' ),
		'beeld_positie' => 35,
		'achtergrond'   => 'donker',
		'ruimte_onder'  => 'klein',
		'anker'         => 'contact',
	);
	return $voorbeelden;
} );
