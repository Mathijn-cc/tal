<?php
/**
 * Blok Hero: de opening van de homepage. Grote kop met een kleine foto in de zin, een naschrift links,
 * intro en knoppen rechts, en een brede foto eronder.
 * Het eerste blok op een pagina krijgt een h1, elk volgend blok een h2.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'hero';
	$layouts[ $p ] = array(
		'label'      => 'Hero',
		'icoon'      => 'sparkles',
		'uitleg'     => 'De opening: grote kop met een kleine foto in de zin, intro, knoppen en een brede foto.',
		'volgorde'   => 10,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array( 'instructions' => 'Typ {foto} op de plek waar de kleine foto in de zin staat. ' . TAL_KOP_UITLEG ) ),
				tal_acf_beeld( $p, 'kop_beeld', 'Kleine foto in de kop', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'naschrift', 'Naschrift', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Kort woord in roze onder de kop, links. Bijvoorbeeld "Toch?".' ) ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 2 ) ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'textarea', array( 'rows' => 3, 'instructions' => 'Een lege regel begint een nieuwe alinea.' ) ),
				powerhub_acf_veld( $p, 'nadruk', 'Slotvraag', 'text', array( 'instructions' => 'Komt donker en iets zwaarder onder de tekst.' ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'link', 'Tekstlink', 'link', powerhub_acf_breed( 50 ) ),
				tal_acf_beeld( $p, 'beeld', 'Brede foto', powerhub_acf_breed( 50 ) ),
			),
			tal_acf_uitsnede( $p, 45, 50 ),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['hero'] = array(
		'kop'           => 'Leiderschap is geen vak {foto} dat je op school krijgt',
		'kop_beeld'     => tal_beeld_url( 'tal-7.jpg' ),
		'naschrift'     => 'Toch?',
		'lead'          => 'Als je goed bent in wat je doet, hard werkt en je nek uitsteekt word je leider.',
		'tekst'         => 'Zo ging het althans bij mij. En bij de meesten om me heen.',
		'nadruk'        => 'Maar hoe moet dat dan? Wie helpt je hierbij?',
		'knop'          => array( 'url' => '#traject', 'title' => 'Bekijk ons traject' ),
		'link'          => array( 'url' => '#over-mij', 'title' => 'Leer mij kennen' ),
		'beeld'         => tal_beeld_url( 'tal-16.jpg' ),
		'beeld_positie'  => 45,
		'beeld_x_mobiel' => 70,
		'achtergrond'   => 'pagina',
		'ruimte_boven'  => 'klein',
		'anker'         => 'top',
	);
	return $voorbeelden;
} );
