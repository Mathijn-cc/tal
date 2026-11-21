<?php
/**
 * Blok Brede foto met tekst: een brede foto die zich bij scrollen opent tot de volle breedte,
 * met daaronder label, kop, ondertitel en tekst. Bedoeld voor een donker vlak.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'academy';
	$layouts[ $p ] = array(
		'label'      => 'Brede foto met tekst',
		'icoon'      => 'graduation-cap',
		'uitleg'     => 'Brede foto die zich bij scrollen opent, met kop, ondertitel en tekst.',
		'volgorde'   => 60,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				tal_acf_beeld( $p, 'beeld', 'Brede foto', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Liggende foto.' ) ) ),
				powerhub_acf_veld( $p, 'label', 'Label in de marge', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'ondertitel', 'Ondertitel', 'text' ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'textarea', array( 'rows' => 5 ) ),
			),
			tal_acf_uitsnede( $p, 35, 50 ),
			powerhub_acf_weergave( $p, 'donker' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['academy'] = array(
		'beeld'         => tal_beeld_url( 'tal-18.jpg' ),
		'beeld_positie' => 35,
		'label'         => 'De academy',
		'kop'           => 'Vertaalde wijsheid',
		'ondertitel'    => 'Van inzicht naar handvatten.',
		'tekst'         => 'Ik heb heel veel boeken gelezen en modellen uitgeplozen. Ik heb al deze kennis en wijsheid over leiderschap voor jullie vertaald en gebundeld, zodat jij niet meer hoeft te navigeren in het oerwoud aan leiderschapstheorie. In de Academy komen deze handvatten voorbij als jij ze nodig hebt in de praktijk.',
		'achtergrond'   => 'donker',
		'ruimte_boven'  => 'geen',
		'ruimte_onder'  => 'groot',
	);
	return $voorbeelden;
} );
