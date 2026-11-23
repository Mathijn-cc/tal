<?php
/**
 * Blok Over mij: staande foto links, rechts bovenkop, kop, introzin, tekst, naam en knop.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'over-mij';
	$layouts[ $p ] = array(
		'label'      => 'Foto met verhaal',
		'icoon'      => 'heart-handshake',
		'uitleg'     => 'Staande foto met een persoonlijk verhaal, naam en knop.',
		'volgorde'   => 90,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				...tal_acf_beeld_uitsnede( $p, 'Foto', 30, 50 ),
				powerhub_acf_veld( $p, 'label', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'tekst', 'Tekst', 'wysiwyg', array( 'media_upload' => 0, 'toolbar' => 'basic', 'tabs' => 'visual' ) ),
				powerhub_acf_veld( $p, 'naam', 'Naam', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'knop', 'Knop', 'link', powerhub_acf_breed( 50 ) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['over-mij'] = array(
		'beeld'         => tal_beeld_url( 'tal-1.jpg' ),
		'beeld_positie' => 30,
		'label'         => 'Over mij',
		'kop'           => 'Leiderschap<br>leer je niet uit een boek.',
		'lead'          => 'Als je goed bent in wat je doet, hard werkt en je nek uitsteekt word je leider. Toch? Zo ging het althans bij mij. En bij de meesten om me heen. En dan ben je ineens leidinggevende en stuur je mensen aan. Maar hoe moet dat dan? Wie helpt je hierbij?',
		'tekst'         => '<p>Die zoektocht ken ik maar al te goed. Op mijn 26e was ik ineens operations manager en moest 2 fabrieken aansturen. Hierna heb ik in diverse managementrollen ervaren hoe uitdagend het is om leiding te geven. Door hulp te zoeken bij coaches en uit boeken, maar vooral veel doen, uitproberen en vallen en opstaan, ontdekte ik mijn kracht: anderen helpen om in hun leiderschap te groeien.</p><p>Ik zag in al deze rollen 1 ding: er is onvoldoende tijd of capaciteit in organisaties om leiders te begeleiden. Daarom zie ik het als mijn missie om leiders sneller te laten groeien, in hun eigen praktijk, met de juiste handvatten en iemand naast zich. Zodat ze niet onnodig hoeven te zwemmen.</p>',
		'naam'          => 'Chantal Strijbos',
		'knop'          => array( 'url' => TAL_KENNISMAKING_URL, 'title' => 'Plan een kennismaking' ),
		'achtergrond'   => 'pagina',
		'anker'         => 'over-mij',
	);
	return $voorbeelden;
} );
