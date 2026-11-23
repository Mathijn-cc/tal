<?php
/**
 * Blok Checklist: kop met introzin en knop die blijft staan, met een lijst met vinkjes ernaast.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'voor-wie';
	$layouts[ $p ] = array(
		'label'      => 'Checklist',
		'icoon'      => 'check',
		'uitleg'     => 'Kop, introzin en knop links (blijft staan), een lijst met vinkjes rechts.',
		'volgorde'   => 80,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 2 ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link' ),
				tal_acf_lijst( $p, 'punten', 'Punten', array( 'tekst' => array( 'Punt', 'text' ) ), array( 'layout' => 'table', 'button_label' => 'Punt toevoegen' ) ),
			),
			powerhub_acf_weergave( $p, 'tint' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['voor-wie'] = array(
		'label'       => 'Voor wie',
		'kop'         => 'Voor leiders die niet onnodig willen zwemmen',
		'lead'        => 'Leren houdt nooit op, maar een gefundeerde basis helpt jou de rest van je carrière.',
		'knop'        => array( 'url' => TAL_KENNISMAKING_URL, 'title' => 'Plan een kennismaking' ),
		'punten'      => array(
			array( 'tekst' => 'Heeft tussen 0 en 5 jaar ervaring in een rol als leidinggevende, teamleider of projectleider.' ),
			array( 'tekst' => 'Zoekt houvast, handvatten en begeleiding in de manier van leiderschap.' ),
			array( 'tekst' => 'Wil leiden vanuit rust en vertrouwen.' ),
			array( 'tekst' => 'Is leergierig, ambitieus en enthousiast.' ),
			array( 'tekst' => 'Heeft een ‘niet lullen maar poetsen’ mentaliteit.' ),
			array( 'tekst' => 'Is flexibel en pragmatisch.' ),
			array( 'tekst' => 'Wil begrijpen waarom we doen wat we doen.' ),
		),
		'achtergrond' => 'tint',
		'anker'       => 'voor-wie',
	);
	return $voorbeelden;
} );
