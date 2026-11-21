<?php
/**
 * Blok Programma: kaarten met foto en korte tekst op een rij. Op desktop schuift de rij bij scrollen
 * zijwaarts (de sectie staat in het midden van het scherm stil), op mobiel veeg je erdoorheen.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'programma';
	$layouts[ $p ] = array(
		'label'      => 'Kaarten op een rij',
		'icoon'      => 'layers',
		'uitleg'     => 'Foto met tekst op een rij die bij scrollen zijwaarts beweegt.',
		'volgorde'   => 40,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Label in de marge', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				tal_acf_lijst( $p, 'kaarten', 'Kaarten', array(
					'beeld'  => array( 'Foto', 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'wrapper' => array( 'width' => 30 ) ) ),
					'tekst'  => array( 'Tekst', 'textarea', array( 'rows' => 2, 'wrapper' => array( 'width' => 70 ) ) ),
				), array( 'min' => 2, 'button_label' => 'Kaart toevoegen' ) ),
			),
			powerhub_acf_weergave( $p, 'wit' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['programma'] = array(
		'label'       => 'Wat het traject je biedt',
		'kop'         => 'Opbouw programma',
		'kaarten'     => array(
			array( 'beeld' => tal_beeld_url( 'tal-3.jpg' ), 'tekst' => '6 maanden intensieve begeleiding met theoretisch fundament en handvatten.' ),
			array( 'beeld' => tal_beeld_url( 'tal-17.jpg' ), 'tekst' => 'Persoonlijke groei als leider in 1:1 coaching.' ),
			array( 'beeld' => tal_beeld_url( 'tal-11.jpg' ), 'tekst' => 'Hulp bij lastige situaties in jouw eigen praktijk.' ),
			array( 'beeld' => tal_beeld_url( 'tal-8.jpg' ), 'tekst' => 'Leren van andere leiders door community en intervisie.' ),
			array( 'beeld' => tal_beeld_url( 'tal-9.jpg' ), 'tekst' => 'Academy met vertaalde wijsheid uit theorie en modellen.' ),
		),
		'achtergrond' => 'wit',
		'anker'       => 'programma',
	);
	return $voorbeelden;
} );
