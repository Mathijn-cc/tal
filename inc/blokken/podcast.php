<?php
/**
 * Blok Podcast: tekst met knop links, rechts de Spotify-speler (embed).
 * Zonder Spotify-link: een afbeelding die doorlinkt, of een roze tegel met de titel en een afspeelknop.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'podcast';
	$layouts[ $p ] = array(
		'label'      => 'Podcast',
		'icoon'      => 'sparkles',
		'uitleg'     => 'Tekst met knop en de Spotify-speler (of een afbeelding of tegel).',
		'volgorde'   => 110,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 2 ) ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'textarea', array( 'rows' => 2 ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link', array( 'instructions' => 'Link naar de podcast. De afbeelding of tegel linkt hier ook naartoe.' ) ),
				powerhub_acf_veld( $p, 'spotify', 'Spotify-link voor de speler', 'url', array( 'instructions' => 'Link naar de show of een aflevering op open.spotify.com. Dan staat de echte Spotify-speler op de pagina.' ) ),
				tal_acf_beeld( $p, 'beeld', 'Afbeelding van de speler', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Alleen als er geen Spotify-link is. Leeg: een roze tegel met de titel.' ) ) ),
				powerhub_acf_veld( $p, 'tegel_onderschrift', 'Onderschrift op de tegel', 'text', array_merge( powerhub_acf_breed( 50 ), powerhub_acf_als( $p, 'beeld', '', '==empty' ) ) ),
			),
			powerhub_acf_weergave( $p, 'wit' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['podcast'] = array(
		'label'              => 'De podcast',
		'kop'                => 'Leiden met<br>korte ei',
		'lead'               => 'Dé podcast voor ambitieuze leiders die met plezier, rust en vertrouwen willen leidinggeven.',
		'tekst'              => 'In korte maar krachtige afleveringen ontvang jij praktische handvatten, tips en waarde die je meteen kan toepassen.',
		'knop'               => array( 'url' => 'https://open.spotify.com/show/5GOZrxoesUgtTvcfqINttf', 'title' => 'Luister op Spotify', 'target' => '_blank' ),
		'spotify'            => 'https://open.spotify.com/show/5GOZrxoesUgtTvcfqINttf',
		'tegel_onderschrift' => 'TAL leadership · Chantal Strijbos',
		'achtergrond'        => 'wit',
		'anker'              => 'podcast',
	);
	return $voorbeelden;
} );
