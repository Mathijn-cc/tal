<?php
/**
 * Blok Traject: label in de marge, kop, een grote claimzin die bij scrollen woord voor woord inkleurt,
 * en daaronder een introzin met tekst in twee kolommen.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'traject';
	$layouts[ $p ] = array(
		'label'      => 'Claim',
		'icoon'      => 'map-pin',
		'uitleg'     => 'Kop met een grote claimzin die bij scrollen inkleurt, en tekst in twee kolommen.',
		'volgorde'   => 30,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Label in de marge', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'kop_roze', 'Kop in roze', 'true_false', array( 'ui' => 1 ) ),
				powerhub_acf_veld( $p, 'claim', 'Claim', 'textarea', array( 'rows' => 3, 'instructions' => 'Grote zin die bij scrollen inkleurt. ' . TAL_KOP_UITLEG ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'wysiwyg', array( 'media_upload' => 0, 'toolbar' => 'basic', 'tabs' => 'visual' ) ),
				powerhub_acf_veld( $p, 'slotlink', 'Slotlink', 'link', array( 'instructions' => 'Grote tekstlink onder de tekst.' ) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['traject'] = array(
		'label'       => 'Het traject',
		'kop'         => 'TAL leader journey',
		'kop_roze'    => true,
		'claim'       => 'Het is geen algemene leiderschapsopleiding, het is geen coachingstraject. Het is jouw *persoonlijke traineeship*.',
		'lead'        => 'TAL leader journey is hét programma voor leiders die meer plezier en rust willen in hun werk en met minder stress willen leidinggeven.',
		'tekst'       => '<p>Waarin jouw praktijk centraal staat en je hulp krijgt bij de uitdagingen waar jij tegenaan loopt. Met daaronder een fundament van handvatten vanuit de concrete vertaling van theorie in handige filmpjes.</p><p>Want zeg nou eerlijk, wie heeft zin om eindeloze stapels boeken te lezen? En wie wil eindeloze details over modellen?</p><p>Door intervisie leer je van andere leiders, vind je herkenning en kan je samen sparren over uitdagingen.</p>',
		'slotlink'    => array( 'url' => 'https://calendly.com/chantal-tal-leadership/30min', 'title' => 'Waar wacht je nog op?' ),
		'achtergrond' => 'pagina',
		'anker'       => 'traject',
	);
	return $voorbeelden;
} );
