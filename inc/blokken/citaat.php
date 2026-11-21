<?php
/**
 * Blok Citaat: een grote uitspraak die bij scrollen woord voor woord scherp wordt, met de naam eronder.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'citaat';
	$layouts[ $p ] = array(
		'label'      => 'Citaat',
		'icoon'      => 'sparkles',
		'uitleg'     => 'Een grote uitspraak die bij scrollen woord voor woord in beeld komt.',
		'volgorde'   => 100,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'citaat', 'Citaat', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'naam', 'Naam', 'text' ),
			),
			powerhub_acf_weergave( $p, 'citaat' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['citaat'] = array(
		'citaat'      => 'Ik zie zoveel leiders onnodig zwemmen. Dat kan anders.',
		'naam'        => 'Chantal Strijbos',
		'achtergrond' => 'citaat',
	);
	return $voorbeelden;
} );
