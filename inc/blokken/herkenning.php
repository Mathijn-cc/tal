<?php
/**
 * Blok Herkenning: een kop met vragen die bij scrollen één voor één in beeld komen (de sectie staat even stil),
 * daaronder een foto, een slotvraag en een knop.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'herkenning';
	$layouts[ $p ] = array(
		'label'      => 'Herkenning',
		'icoon'      => 'search',
		'uitleg'     => 'Vragen die elkaar bij scrollen vervangen, met een slotvraag en knop.',
		'volgorde'   => 20,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array( 'instructions' => TAL_KOP_UITLEG ) ),
				tal_acf_lijst( $p, 'vragen', 'Vragen', array( 'vraag' => array( 'Vraag', 'textarea', array( 'rows' => 2 ) ) ), array( 'min' => 1, 'max' => 6, 'layout' => 'table', 'button_label' => 'Vraag toevoegen' ) ),
				...tal_acf_beeld_uitsnede( $p, 'Foto', 40, 50, array( 'instructions' => 'Staande foto.' ) ),
				powerhub_acf_veld( $p, 'slot', 'Slotvraag', 'textarea', array( 'rows' => 2 ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link' ),
			),
			powerhub_acf_weergave( $p, 'tint' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['herkenning'] = array(
		'kop'           => 'Herken je dit?',
		'vragen'        => array(
			array( 'vraag' => 'Dat werk steeds weer als boemerang naar je terug komt?' ),
			array( 'vraag' => 'En dat het best lastig is om de aandacht en aansturing van je mensen, de resultaten en jezelf in balans te houden?' ),
			array( 'vraag' => 'Dat je veel dingen doet op onderbuik gevoel maar niet precies weet waarom en of dit wel de beste manier is?' ),
			array( 'vraag' => 'Dat je graag iemand zou hebben die af en toe over je schouder meekijkt, vragen stelt, kritisch is en tips geeft?' ),
		),
		'beeld'         => tal_beeld_url( 'tal-10.jpg' ),
		'beeld_positie' => 40,
		'slot'          => 'Ben jij klaar om jouw rol als leider met meer plezier, rust en vertrouwen uit te voeren?',
		'knop'          => array( 'url' => 'https://calendly.com/chantal-tal-leadership/30min', 'title' => 'Plan een kennismaking' ),
		'achtergrond'   => 'tint',
	);
	return $voorbeelden;
} );
