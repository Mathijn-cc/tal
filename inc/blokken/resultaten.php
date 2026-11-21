<?php
/**
 * Blok Resultaten: label, kop en tegels met een lijnicoon en een korte uitkomst.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p      = 'resultaten';
	$iconen = wp_list_pluck( tal_uitkomst_iconen(), 0 );
	$layouts[ $p ] = array(
		'label'      => 'Resultaten',
		'icoon'      => 'chart-column',
		'uitleg'     => 'Tegels met een icoon en een uitkomst, drie naast elkaar.',
		'volgorde'   => 70,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Label in de marge', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				tal_acf_lijst( $p, 'uitkomsten', 'Uitkomsten', array(
					'icoon' => array( 'Icoon', 'select', array( 'choices' => $iconen, 'default_value' => 'zon', 'wrapper' => array( 'width' => 30 ) ) ),
					'tekst' => array( 'Uitkomst', 'textarea', array( 'rows' => 2, 'wrapper' => array( 'width' => 70 ) ) ),
				), array( 'layout' => 'table', 'button_label' => 'Uitkomst toevoegen' ) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['resultaten'] = array(
		'label'       => 'Wat levert het op?',
		'kop'         => 'Waar jij staat na<br>een half jaar',
		'uitkomsten'  => array(
			array( 'icoon' => 'zon', 'tekst' => 'Je werk is leuker.' ),
			array( 'icoon' => 'cirkels', 'tekst' => 'Je hebt fijner contact met je medewerkers.' ),
			array( 'icoon' => 'pijl', 'tekst' => 'Je krijgt je team moeiteloos mee.' ),
			array( 'icoon' => 'golf', 'tekst' => 'Je ervaart minder stress en groeit sneller door.' ),
			array( 'icoon' => 'agenda', 'tekst' => 'Je agenda is leger, je bent minder operationeel nodig en ervaart meer rust.' ),
			array( 'icoon' => 'kringen', 'tekst' => 'Je ziet je impact, krijgt complimenten over resultaat en zit zelf lekkerder in je vel.' ),
		),
		'achtergrond' => 'pagina',
	);
	return $voorbeelden;
} );
