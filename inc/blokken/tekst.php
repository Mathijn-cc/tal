<?php
/**
 * Blok Tekst: bovenkop, kop, tekst en knoppen. Smal, gesplitst (kop links, tekst rechts) of in twee kolommen.
 * Referentieblok: elk blok volgt deze opbouw (definitie, voorbeelden, template, CSS).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'tekst';
	$layouts[ $p ] = array(
		'label'      => 'Tekst',
		'icoon'      => 'align-left',
		'uitleg'     => 'Kop en tekst met knoppen. Smal, gesplitst met de kop links, of in twee kolommen.',
		'volgorde'   => 20,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'bovenkop', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => POWERHUB_ACCENT_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'wysiwyg', array( 'media_upload' => 0, 'toolbar' => 'basic', 'tabs' => 'visual' ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'knop_2', 'Tweede knop', 'link', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Secundair, begint vaak met "Of".' ) ) ),
			),
			powerhub_acf_weergave( $p, 'pagina', array(
				powerhub_acf_veld( $p, 'indeling', 'Indeling', 'button_group', array(
					'choices'       => array( 'smal' => 'Smal', 'gesplitst' => 'Kop links', 'kolommen' => 'Twee kolommen' ),
					'default_value' => 'smal',
				) ),
			) )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['tekst'] = array(
		array(
			'kop'         => 'Een tekstblok met *een accent.*',
			'tekst'       => '<p>Een alinea lopende tekst van een paar zinnen. Zo zie je de regellengte, de regelafstand en hoe een <a href="#">link in de tekst</a> eruitziet.</p><p>Een tweede alinea, korter.</p>',
			'knop'        => array( 'url' => '#', 'title' => 'Neem contact op' ),
			'knop_2'      => array( 'url' => '#', 'title' => 'Of lees meer' ),
			'indeling'    => 'smal',
			'achtergrond' => 'pagina',
		),
		array(
			'bovenkop'    => 'Gesplitst',
			'kop'         => 'Kop links, *tekst rechts.*',
			'tekst'       => '<p>Handig voor een introductie bij een onderwerp. Op mobiel staat de kop boven de tekst.</p>',
			'indeling'    => 'gesplitst',
			'achtergrond' => 'donker',
		),
		array(
			'kop'         => 'Twee kolommen op *een gedeelde achtergrond.*',
			'tekst'       => '<p>De tekst loopt over twee kolommen. De achtergrond is 50/50 verdeeld.</p><p>Controleer bij een gedeelde achtergrond altijd het contrast: de tekst staat op beide vlakken.</p>',
			'indeling'    => 'kolommen',
			'achtergrond' => 'gedeeld',
			'vlak_1'      => 'wit',
			'vlak_2'      => 'tint-zacht',
			'verdeling'   => 'boven',
		),
	);
	return $voorbeelden;
} );
