<?php
/**
 * Blok Lagen: kop met intro, daaronder lagen die bij scrollen over elkaar schuiven.
 * Elke laag krijgt een eigen vlak (wit, blos, steen); de laatste laag is donker.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'vier-delen';
	$layouts[ $p ] = array(
		'label'      => 'Lagen',
		'icoon'      => 'layers',
		'uitleg'     => 'Onderdelen als lagen die bij scrollen over elkaar schuiven. De laatste laag is donker.',
		'volgorde'   => 50,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array( 'instructions' => TAL_KOP_UITLEG ) ),
				powerhub_acf_veld( $p, 'intro', 'Intro', 'textarea', array( 'rows' => 4 ) ),
				tal_acf_lijst( $p, 'lagen', 'Lagen', array(
					'naam'  => array( 'Naam', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
					'vraag' => array( 'Vraag of uitleg', 'textarea', array( 'rows' => 2, 'wrapper' => array( 'width' => 67 ) ) ),
				), array( 'min' => 2, 'max' => 6, 'layout' => 'table', 'button_label' => 'Laag toevoegen' ) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['vier-delen'] = array(
		'kop'          => 'Eén traject. Vier delen.',
		'intro'        => 'De meeste leiders zijn naar voren geschoven vanuit de inhoud en hebben weinig handvatten meegekregen over het leiderschap zelf. Modellen en theorieën komen voorbij, maar hier zit weinig structuur achter. Het TAL traject brengt dit allemaal samen in een logische structuur.',
		'lagen'        => array(
			array( 'naam' => 'Jij als leider', 'vraag' => 'Wie ben ik en wat neem ik mee?' ),
			array( 'naam' => 'Jij en anderen', 'vraag' => 'Wie stuur ik aan en wat hebben zij nodig?' ),
			array( 'naam' => 'Je organisatie', 'vraag' => 'Wat staat ons te doen, waar moet ik regie op houden?' ),
			array( 'naam' => 'Fundament', 'vraag' => 'Modellen en theorie: alleen relevant als ze voor jou ook in praktijk gebracht worden.' ),
		),
		'achtergrond'  => 'pagina',
		'ruimte_onder' => 'geen',
	);
	return $voorbeelden;
} );
