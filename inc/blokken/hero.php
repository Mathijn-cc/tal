<?php
/**
 * Blok Hero: de opening van een pagina. Bovenkop, kop, intro, knoppen en een beeld.
 * Het eerste blok op een pagina krijgt een h1, elk volgend blok een h2.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'hero';
	$layouts[ $p ] = array(
		'label'      => 'Hero',
		'icoon'      => 'sparkles',
		'uitleg'     => 'De opening van een pagina met kop, intro, knoppen en een beeld.',
		'volgorde'   => 10,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'bovenkop', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => POWERHUB_ACCENT_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'intro', 'Intro', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'knop_2', 'Tweede knop', 'link', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'beeld', 'Beeld', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium' ) ),
			),
			powerhub_acf_weergave( $p, 'donker' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['hero'] = array(
		'bovenkop'    => 'Bovenkop',
		'kop'         => 'Een gewone zin met *een accent.*',
		'intro'       => 'Een korte introductie van één of twee zinnen die uitlegt wat de bezoeker hier kan.',
		'knop'        => array( 'url' => '#', 'title' => 'Neem contact op' ),
		'knop_2'      => array( 'url' => '#', 'title' => 'Of lees meer' ),
		'achtergrond' => 'donker',
	);
	return $voorbeelden;
} );
