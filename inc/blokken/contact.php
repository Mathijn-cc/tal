<?php
/**
 * Blok Contact: kop, introzin en contactgegevens naast het contactformulier (Gravity Forms),
 * daaronder de agenda van Calendly om direct een kennismaking te plannen.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'contact';
	$layouts[ $p ] = array(
		'label'      => 'Contact',
		'icoon'      => 'mail',
		'uitleg'     => 'Contactgegevens met het contactformulier, en de Calendly-agenda om een kennismaking te plannen.',
		'volgorde'   => 130,
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'label', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'lead', 'Introzin', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'email', 'E-mailadres', 'email', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Leeg: het e-mailadres uit Powerhub > Algemeen.' ) ) ),
				powerhub_acf_veld( $p, 'telefoon', 'Telefoon', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Leeg: het nummer uit Powerhub > Algemeen (als dat er is).' ) ) ),
				powerhub_acf_tab( $p, 'Formulier' ),
				powerhub_acf_veld( $p, 'formulier_kop', 'Kop boven het formulier', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'formulier', 'Formulier', 'select', array(
					'wrapper'       => array( 'width' => 50, 'class' => 'kies-formulier' ),
					'choices'       => array(),
					'allow_null'    => 1,
					'return_format' => 'value',
					'instructions'  => 'Een formulier uit Gravity Forms. Leeg: het contactformulier dat bij de uitrol is gemaakt.',
				) ),
				powerhub_acf_tab( $p, 'Agenda' ),
				powerhub_acf_veld( $p, 'agenda_label', 'Bovenkop', 'text', powerhub_acf_breed( 50 ) ),
				powerhub_acf_veld( $p, 'agenda_kop', 'Kop', 'text', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => TAL_KOP_UITLEG ) ) ),
				powerhub_acf_veld( $p, 'agenda_tekst', 'Tekst', 'textarea', array( 'rows' => 3 ) ),
				powerhub_acf_veld( $p, 'calendly', 'Calendly-link', 'url', array_merge( powerhub_acf_breed( 50 ), array( 'instructions' => 'Link naar een afspraaktype, bijvoorbeeld calendly.com/naam/30min. Leeg: geen agenda.' ) ) ),
				powerhub_acf_veld( $p, 'agenda_anker', 'Anker van de agenda', 'text', array_merge( powerhub_acf_breed( 50 ), array(
					'instructions'  => 'Knoppen als "Plan een kennismaking" linken naar /contact/#kennismaking.',
					'prepend'       => '#',
					'default_value' => 'kennismaking',
				) ) ),
			),
			powerhub_acf_weergave( $p, 'pagina' )
		),
	);
	return $layouts;
} );

add_filter( 'powerhub_blok_voorbeelden', function ( $voorbeelden ) {
	$voorbeelden['contact'] = array(
		'label'         => 'Contact',
		'kop'           => 'Laten we *kennismaken*.',
		'lead'          => 'Heb je een vraag over de TAL leader journey, of wil je weten of het traject bij jou past? Stuur me een bericht, ik neem snel contact met je op.',
		'email'         => 'chantal@tal-leadership.nl',
		'formulier_kop' => 'Stuur een bericht',
		'agenda_label'  => 'Kennismaken',
		'agenda_kop'    => 'Liever meteen een *moment plannen*?',
		'agenda_tekst'  => 'Kies hieronder een moment voor een vrijblijvende kennismaking van 30 minuten. We bespreken waar jij als leider tegenaan loopt en wat het traject voor je kan betekenen.',
		'calendly'      => TAL_CALENDLY,
		'agenda_anker'  => 'kennismaking',
		'achtergrond'   => 'pagina',
		'ruimte_boven'  => 'klein',
	);
	return $voorbeelden;
} );

/**
 * Calendly-link met de kleuren van het thema, zodat de agenda bij de site past.
 */
function tal_calendly_url( $url ) {
	$kleur = function ( $pad ) {
		$waarde = function_exists( 'powerhub_token' ) ? (string) powerhub_token( $pad, '' ) : '';
		return preg_match( '/^#([0-9a-f]{6})$/i', $waarde, $m ) ? strtolower( $m[1] ) : '';
	};
	return add_query_arg( array_filter( array(
		'locale'           => 'nl',
		'background_color' => $kleur( 'kleur.vlak-kaart' ),
		'text_color'       => $kleur( 'kleur.tekst-kop' ),
		'primary_color'    => $kleur( 'kleur.actie-500' ),
	) ), $url );
}
