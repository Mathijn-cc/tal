<?php
/**
 * Blok Beeld: één brede foto over de volle breedte van het raster (met de zijmarge van de pagina).
 * Komt bij scrollen in beeld als een gordijn en beweegt licht mee.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'beeld';
	$layouts[ $p ] = array(
		'label'      => 'Brede foto',
		'icoon'      => 'sparkles',
		'uitleg'     => 'Eén foto over de volle breedte, met de zijmarge van de pagina.',
		'volgorde'   => 15,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				...tal_acf_beeld_uitsnede( $p, 'Foto', 45, 50, array( 'instructions' => 'Liggende foto, minimaal 2400 pixels breed.' ) ),
				powerhub_acf_veld( $p, 'alt', 'Beschrijving voor schermlezers', 'text', array( 'instructions' => 'Leeg: de beschrijving uit de mediabibliotheek.' ) ),
				powerhub_acf_veld( $p, 'verhouding', 'Verhouding op desktop', 'button_group', array(
					'choices'       => array( '16-8' => 'Breed (2:1)', '21-9' => 'Extra breed (21:9)', '16-9' => 'Film (16:9)' ),
					'default_value' => '16-8',
				) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['beeld'] = array(
		'beeld'          => tal_beeld_url( 'tal-16.jpg' ),
		'verhouding'     => '16-8',
		'beeld_positie'  => 45,
		'beeld_x_mobiel' => 70,
		'achtergrond'    => 'pagina',
		'ruimte_boven'   => 'geen',
	);
	return $voorbeelden;
} );
