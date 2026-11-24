<?php
/**
 * Flexibele ACF-blokken.
 *
 * Elk blok is één bestand in het thema:
 *   inc/blokken/<naam>.php                 definitie (velden) en voorbeeldinhoud, via de filters hieronder
 *   template-parts/blokken/<naam>.php      weergave, met $blok als data
 *   assets/css/blokken/<naam>.css          stijl, alleen geladen op pagina's waar het blok staat
 *   assets/js/blokken/<naam>.js            gedrag, idem (optioneel)
 *
 * Een layout mag twee extra sleutels hebben voor de blokkiezer: 'icoon' (Lucide-naam) en 'uitleg' (één zin).
 *
 * Elk blok heeft dezelfde indeling in tabs:
 *   Inhoud       wat er staat (eigen velden van het blok)
 *   Weergave     achtergrond (vaste kleuren of 50/50 gedeeld), ruimte boven en onder, plus blokeigen opties
 *   Geavanceerd  anker voor links naar het blok, tijdelijk verbergen, decoratieve vorm (filter powerhub_decoraties)
 */

defined( 'ABSPATH' ) || exit;

require_once POWERHUB_PAD . '/kern/contrast.php';

/* Blokdefinities uit het thema automatisch laden. */
add_action( 'after_setup_theme', function () {
	foreach ( glob( get_template_directory() . '/inc/blokken/*.php' ) ?: array() as $bestand ) {
		require_once $bestand;
	}
}, 5 );

/* Hulpfuncties om ACF-velden kort op te schrijven. */

function powerhub_acf_veld( $prefix, $naam, $label, $type, $extra = array() ) {
	return array_merge( array(
		'key'   => 'field_' . $prefix . '_' . $naam,
		'name'  => $naam,
		'label' => $label,
		'type'  => $type,
	), $extra );
}

function powerhub_acf_tab( $prefix, $label ) {
	return array(
		'key'       => 'field_' . $prefix . '_tab_' . sanitize_key( $label ),
		'label'     => $label,
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/**
 * Groep velden die bij elkaar horen: een kop met korte uitleg, daarna de velden, afgesloten met powerhub_acf_groep_eind().
 * De velden ertussen krijgen in de beheerder een eigen vlak (admin-blokken.js zet de class in-groep).
 * Zet uitleg hier en niet bij losse velden in een rij: dan staan de invoervelden in een rij op gelijke hoogte.
 */
function powerhub_acf_groep( $prefix, $naam, $titel, $uitleg = '' ) {
	return array(
		'key'     => 'field_' . $prefix . '_groep_' . $naam,
		'name'    => '',
		'label'   => $titel,
		'type'    => 'message',
		'message' => $uitleg,
		'wrapper' => array( 'class' => 'veldgroep-kop' ),
	);
}

function powerhub_acf_groep_eind( $prefix, $naam ) {
	return array(
		'key'     => 'field_' . $prefix . '_groep_' . $naam . '_eind',
		'name'    => '',
		'label'   => '',
		'type'    => 'message',
		'message' => '',
		'wrapper' => array( 'class' => 'veldgroep-eind' ),
	);
}

/**
 * Breedte van een veld in procenten, voor compacte rijen.
 */
function powerhub_acf_breed( $procent, $class = '' ) {
	return array( 'wrapper' => array( 'width' => $procent, 'class' => $class ) );
}

/**
 * Alleen tonen als een ander veld in hetzelfde blok een bepaalde waarde heeft.
 */
function powerhub_acf_als( $prefix, $veld, $waarde, $operator = '==' ) {
	return array( 'conditional_logic' => array( array( array( 'field' => 'field_' . $prefix . '_' . $veld, 'operator' => $operator, 'value' => $waarde ) ) ) );
}

const POWERHUB_ACCENT_UITLEG = 'Zet *sterretjes* om woorden voor het cursieve accent. Nieuwe regel: typ &lt;br&gt;.';

/**
 * De achtergronden die een blok kan kiezen, uit de filter powerhub_achtergronden.
 * Per sleutel: label, kleur (tokenpad of hexwaarde) en of het vlak donker is (dan lichte tekst).
 * Een eenvoudige lijst sleutel => label mag ook.
 */
function powerhub_achtergronden() {
	$ruw  = (array) apply_filters( 'powerhub_achtergronden', array( 'licht' => 'Licht', 'donker' => 'Donker' ) );
	$lijst = array();
	foreach ( $ruw as $sleutel => $waarde ) {
		$waarde = is_array( $waarde ) ? $waarde : array( 'label' => $waarde );
		$kleur  = $waarde['kleur'] ?? '';
		if ( $kleur && '#' !== $kleur[0] && function_exists( 'powerhub_token' ) ) {
			$kleur = (string) powerhub_token( $kleur, '' );
		}
		$lijst[ $sleutel ] = array(
			'label'  => $waarde['label'] ?? $sleutel,
			'kleur'  => $kleur,
			'token'  => $waarde['kleur'] ?? '',
			'donker' => array_key_exists( 'donker', $waarde ) ? (bool) $waarde['donker'] : ( $kleur ? powerhub_is_donker( $kleur ) : false ),
			'tekst'  => $waarde['tekst'] ?? '',
		);
	}
	return $lijst;
}

/**
 * Tabs Weergave en Geavanceerd, voor elk blok gelijk.
 * $extra_weergave: blokeigen weergave-opties (bijv. kolommen of beeld links/rechts), komen bovenaan Weergave.
 */
function powerhub_acf_weergave( $prefix, $achtergrond = '', $extra_weergave = array() ) {
	$achtergronden = powerhub_achtergronden();
	$keuzes        = wp_list_pluck( $achtergronden, 'label' );
	$standaard     = isset( $keuzes[ $achtergrond ] ) ? $achtergrond : (string) array_key_first( $keuzes );
	$ruimte        = array( 'geen' => 'Geen', 'klein' => 'Klein', 'normaal' => 'Normaal', 'groot' => 'Groot' );
	$gedeeld       = powerhub_acf_als( $prefix, 'achtergrond', 'gedeeld' );

	$opmaak = $extra_weergave ? array_merge(
		array( powerhub_acf_groep( $prefix, 'opmaak', 'Opmaak' ) ),
		$extra_weergave,
		array( powerhub_acf_groep_eind( $prefix, 'opmaak' ) )
	) : array();

	return array_merge(
		array( powerhub_acf_tab( $prefix, 'Weergave' ) ),
		$opmaak,
		array(
			powerhub_acf_groep( $prefix, 'achtergrond', 'Achtergrond', '50/50 verdeelt het blok in twee vlakken met een harde overgang. De tekst staat in de eerste helft en krijgt daar een passende kleur.' ),
			powerhub_acf_veld( $prefix, 'achtergrond', 'Kleur', 'button_group', array_merge( powerhub_acf_breed( 100, 'kleurkeuze' ), array(
				'choices'       => $keuzes + array( 'gedeeld' => '50/50' ),
				'default_value' => $standaard,
			) ) ),
			powerhub_acf_veld( $prefix, 'vlak_1', 'Eerste helft', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 34, 'kleurkeuze alleen-bolletjes' ), array(
				'choices'       => $keuzes,
				'default_value' => $standaard,
			) ) ),
			powerhub_acf_veld( $prefix, 'vlak_2', 'Tweede helft', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 33, 'kleurkeuze alleen-bolletjes' ), array(
				'choices'       => $keuzes,
				'default_value' => (string) array_key_first( $keuzes ),
			) ) ),
			powerhub_acf_veld( $prefix, 'verdeling', 'Verdeling', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 33 ), array(
				'choices'       => array( 'boven' => 'Boven / onder', 'links' => 'Links / rechts' ),
				'default_value' => 'boven',
			) ) ),
			powerhub_acf_groep_eind( $prefix, 'achtergrond' ),
			powerhub_acf_groep( $prefix, 'ruimte', 'Ruimte', 'Witruimte boven en onder het blok.' ),
			powerhub_acf_veld( $prefix, 'ruimte_boven', 'Boven', 'button_group', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => $ruimte,
				'default_value' => 'normaal',
			) ) ),
			powerhub_acf_veld( $prefix, 'ruimte_onder', 'Onder', 'button_group', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => $ruimte,
				'default_value' => 'normaal',
			) ) ),
			powerhub_acf_groep_eind( $prefix, 'ruimte' ),
			powerhub_acf_tab( $prefix, 'Geavanceerd' ),
			powerhub_acf_groep( $prefix, 'geavanceerd', 'Koppelen en verbergen', 'Met een anker link je naar dit blok, bijvoorbeeld /pagina/#veelgestelde-vragen. Verbergen haalt het blok tijdelijk van de site zonder de inhoud te verliezen.' ),
			powerhub_acf_veld( $prefix, 'anker', 'Anker', 'text', array_merge( powerhub_acf_breed( 50 ), array(
				'prepend'     => '#',
				'placeholder' => 'veelgestelde-vragen',
			) ) ),
			powerhub_acf_veld( $prefix, 'verbergen', 'Verbergen', 'true_false', array_merge( powerhub_acf_breed( 50 ), array(
				'ui'          => 1,
				'ui_on_text'  => 'Verborgen',
				'ui_off_text' => 'Zichtbaar',
			) ) ),
			powerhub_acf_groep_eind( $prefix, 'geavanceerd' ),
			powerhub_acf_groep( $prefix, 'animatie', 'Animatie', 'Hoe de inhoud van dit blok in beeld komt. Standaard volgt de instelling in Powerhub, tab Animaties.' ),
			powerhub_acf_veld( $prefix, 'animatie', 'Effect', 'select', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => array( '' => 'Standaard (Powerhub)' ) + ( function_exists( 'powerhub_animatie_keuzes' ) ? powerhub_animatie_keuzes() : array() ),
				'default_value' => '',
			) ) ),
			powerhub_acf_veld( $prefix, 'animatie_volgorde', 'Volgorde', 'button_group', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => array( '' => 'Standaard', 'samen' => 'Alles tegelijk', 'na-elkaar' => 'Na elkaar' ),
				'default_value' => '',
			) ) ),
			powerhub_acf_groep_eind( $prefix, 'animatie' ),
		),
		powerhub_acf_decoratie( $prefix )
	);
}

/**
 * Decoratieve vormen achter een blok (bijvoorbeeld een merkvorm), uit de filter powerhub_decoraties:
 * array( 'sleutel' => array( 'label' => 'Ballon', 'bestand' => URL van een SVG, 'verhouding' => breedte / hoogte,
 *   'tweede' => array( 'bestand' => ..., 'verhouding' => ... ) optioneel, 'class' => extra class voor de plaatsing ) ).
 * De vorm wordt als masker getekend, zodat de kleur uit de CSS komt (licht en donker vlak).
 */
function powerhub_decoraties() {
	return (array) apply_filters( 'powerhub_decoraties', array() );
}

function powerhub_acf_decoratie( $prefix ) {
	$decoraties = powerhub_decoraties();
	if ( ! $decoraties ) {
		return array();
	}
	$aan = powerhub_acf_als( $prefix, 'decoratie', '', '!=empty' );
	return array(
		powerhub_acf_groep( $prefix, 'decoratie', 'Decoratie', 'Een vorm in de achtergrond, rustig en zonder functie. Steekt hij uit, dan valt hij half over het blok erboven.' ),
		powerhub_acf_veld( $prefix, 'decoratie', 'Vorm', 'button_group', array_merge( powerhub_acf_breed( 34 ), array(
			'choices'       => array( '' => 'Geen' ) + wp_list_pluck( $decoraties, 'label' ),
			'default_value' => '',
		) ) ),
		powerhub_acf_veld( $prefix, 'decoratie_plek', 'Plek', 'button_group', array_merge( $aan, powerhub_acf_breed( 33 ), array(
			'choices'       => array( 'rechts' => 'Rechts', 'links' => 'Links' ),
			'default_value' => 'rechts',
		) ) ),
		powerhub_acf_veld( $prefix, 'decoratie_uitsteken', 'Uitsteken', 'true_false', array_merge( $aan, powerhub_acf_breed( 33 ), array(
			'ui'            => 1,
			'default_value' => 1,
			'ui_on_text'    => 'Over de rand',
			'ui_off_text'   => 'Binnen het blok',
		) ) ),
		powerhub_acf_groep_eind( $prefix, 'decoratie' ),
	);
}

/**
 * Groepeert in de tab Inhoud velden die bij elkaar horen, zonder dat elk blok dat zelf hoeft te doen:
 * tekstvelden (bovenkop, kop, intro, tekst), knoppen en beeld of video. Herhalers en overige velden blijven los.
 * Een blok dat zelf al groepen heeft (powerhub_acf_groep) wordt niet aangeraakt. Uit te breiden met de filter powerhub_inhoud_groepen.
 */
function powerhub_groepeer_inhoud( $prefix, $velden ) {
	$groepen = (array) apply_filters( 'powerhub_inhoud_groepen', array(
		'tekst'   => array( 'titel' => 'Tekst', 'velden' => array( 'bovenkop', 'kop', 'intro', 'tekst', 'ondertitel', 'citaat', 'link' ) ),
		'knoppen' => array( 'titel' => 'Knoppen', 'velden' => array( 'knop', 'knop_2' ) ),
		'media'   => array( 'titel' => 'Beeld en video', 'velden' => array( 'media', 'beeld', 'beeld_mobiel', 'foto', 'video', 'poster', 'bron', 'youtube', 'bestand', 'videoknop', 'onderschrift', 'dialoog_titel', 'dialoog_tekst' ) ),
	), $prefix );

	/* Per veld de soort bepalen, alleen in de tab Inhoud en niet als het blok daar zelf groepen heeft. */
	$soorten   = array();
	$in_inhoud = false;
	foreach ( $velden as $i => $veld ) {
		$type          = $veld['type'] ?? '';
		$soorten[ $i ] = '';
		if ( 'tab' === $type ) {
			$in_inhoud = ( 'Inhoud' === ( $veld['label'] ?? '' ) );
			continue;
		}
		if ( false !== strpos( (string) ( $veld['wrapper']['class'] ?? '' ), 'veldgroep-' ) ) {
			$in_inhoud = false;
			continue;
		}
		if ( ! $in_inhoud || in_array( $type, array( 'repeater', 'flexible_content', 'message' ), true ) ) {
			continue;
		}
		foreach ( $groepen as $sleutel => $groep ) {
			if ( in_array( $veld['name'] ?? '', $groep['velden'], true ) ) {
				$soorten[ $i ] = $sleutel;
				break;
			}
		}
	}

	/* Alleen reeksen van minstens twee velden worden een groep; een los veld krijgt geen kader. */
	$uit    = array();
	$aantal = count( $velden );
	$i      = 0;
	$lijst  = array_values( $velden );
	$soort  = array_values( $soorten );
	while ( $i < $aantal ) {
		$s = $soort[ $i ];
		$j = $i;
		while ( $s && $j + 1 < $aantal && $soort[ $j + 1 ] === $s ) {
			$j++;
		}
		if ( $s && $j > $i ) {
			$uit[] = powerhub_acf_groep( $prefix, 'inhoud_' . $s . '_' . $i, $groepen[ $s ]['titel'] );
			for ( $k = $i; $k <= $j; $k++ ) {
				$uit[] = $lijst[ $k ];
			}
			$uit[] = powerhub_acf_groep_eind( $prefix, 'inhoud_' . $s . '_' . $i );
		} else {
			for ( $k = $i; $k <= $j; $k++ ) {
				$uit[] = $lijst[ $k ];
			}
		}
		$i = $j + 1;
	}
	return $uit;
}

/**
 * Herhalers overzichtelijker: korte velden zonder eigen breedte komen naast elkaar (50%), lange tekst houdt de volle breedte.
 * Zo hoeft niet elk blok de breedtes zelf te regelen. Een veld met een eigen breedte blijft ongemoeid.
 */
function powerhub_herhalers_ordenen( $velden ) {
	$kort = array( 'text', 'url', 'email', 'number', 'select', 'button_group', 'true_false', 'link', 'image', 'file', 'date_picker', 'color_picker', 'page_link', 'post_object' );
	foreach ( $velden as &$veld ) {
		if ( 'repeater' !== ( $veld['type'] ?? '' ) || empty( $veld['sub_fields'] ) ) {
			continue;
		}
		foreach ( $veld['sub_fields'] as &$sub ) {
			if ( empty( $sub['wrapper']['width'] ) && in_array( $sub['type'] ?? '', $kort, true ) ) {
				$sub['wrapper']          = (array) ( $sub['wrapper'] ?? array() );
				$sub['wrapper']['width'] = 50;
			}
		}
		unset( $sub );
		$veld['sub_fields'] = powerhub_herhalers_ordenen( $veld['sub_fields'] );
	}
	unset( $veld );
	return $velden;
}

/**
 * Posttypes met blokken. Standaard alleen pagina's.
 */
function powerhub_blok_posttypes() {
	return (array) apply_filters( 'powerhub_blok_posttypes', array( 'page' ) );
}

/**
 * Alle layouts, met icoon en uitleg apart bewaard voor de blokkiezer.
 */
function powerhub_blok_layouts() {
	static $layouts = null;
	if ( null === $layouts ) {
		$layouts = (array) apply_filters( 'powerhub_blok_layouts', array() );
		uasort( $layouts, function ( $a, $b ) {
			return ( $a['volgorde'] ?? 50 ) <=> ( $b['volgorde'] ?? 50 );
		} );
	}
	return $layouts;
}

add_action( 'acf/init', function () {
	$layouts = powerhub_blok_layouts();
	if ( ! $layouts || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$acf_layouts = array();
	foreach ( $layouts as $naam => $layout ) {
		unset( $layout['icoon'], $layout['uitleg'], $layout['volgorde'] );
		$layout['sub_fields'] = powerhub_herhalers_ordenen( powerhub_groepeer_inhoud( $naam, $layout['sub_fields'] ?? array() ) );
		$acf_layouts[ 'layout_' . $naam ] = wp_parse_args( $layout, array(
			'key'     => 'layout_' . $naam,
			'name'    => $naam,
			'label'   => ucfirst( $naam ),
			'display' => 'block',
		) );
	}

	$locaties = array();
	foreach ( powerhub_blok_posttypes() as $type ) {
		$locaties[] = array( array( 'param' => 'post_type', 'operator' => '==', 'value' => $type ) );
	}

	acf_add_local_field_group( array(
		'key'            => 'group_powerhub_blokken',
		'title'          => 'Blokken',
		'fields'         => array(
			array(
				'key'          => 'field_powerhub_blokken',
				'name'         => 'blokken',
				'label'        => 'Blokken',
				'type'         => 'flexible_content',
				'button_label' => 'Blok toevoegen',
				'layouts'      => $acf_layouts,
			),
		),
		'location'       => $locaties,
		'position'              => 'acf_after_title',
		'style'                 => 'seamless',
		/* Uitleg onder het veld: dan staan labels en invoervelden in een rij altijd op gelijke hoogte. */
		'instruction_placement' => 'field',
		/* ACF Multilingual (WPML): dezelfde blokken per taal, de inhoud wordt vertaald. */
		'acfml_field_group_mode' => 'translation',
		'hide_on_screen' => array( 'the_content' ),
	) );
} );

/* Posttypes met blokken werken niet met de blokeditor. */
add_filter( 'use_block_editor_for_post_type', function ( $gebruik, $type ) {
	return in_array( $type, powerhub_blok_posttypes(), true ) ? false : $gebruik;
}, 10, 2 );

add_action( 'init', function () {
	foreach ( powerhub_blok_posttypes() as $type ) {
		remove_post_type_support( $type, 'editor' );
	}
}, 20 );

/**
 * Alle blokken van een bericht renderen. Geeft false als er geen blokken zijn.
 */
function powerhub_blokken( $post_id = null ) {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'blokken', $post_id ) ) {
		return false;
	}
	$index = 0;
	while ( have_rows( 'blokken', $post_id ) ) {
		the_row();
		$layout = get_row_layout();
		$blok   = get_row( true );
		if ( ! empty( $blok['verbergen'] ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<!-- Powerhub: blok ' . esc_html( $layout ) . ' is verborgen -->';
			}
			continue;
		}
		powerhub_blok( $layout, $blok, $index++ );
	}
	return true;
}

/**
 * Eén blok renderen met losse data. Ook gebruikt door de designsysteempagina en vaste templates (bijv. de CTA onder artikelen).
 */
function powerhub_blok( $naam, $blok, $index = 1 ) {
	$bestand = locate_template( 'template-parts/blokken/' . sanitize_file_name( $naam ) . '.php' );
	if ( ! $bestand ) {
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<!-- Powerhub: geen template voor blok ' . esc_html( $naam ) . ' -->';
		}
		return;
	}
	powerhub_blok_assets( array( $naam ) );
	$blok           = (array) $blok;
	$blok['_index'] = $index;
	$blok['_naam']  = $naam;
	include $bestand;
}

/**
 * Attributen voor de sectie van een blok: class, id en (bij 50/50) de twee vlakken.
 * Gebruik: <section <?php echo powerhub_sectie( $blok, 'hero' ); ?>>
 */
function powerhub_sectie( $blok, $naam, $extra_class = '' ) {
	$achtergronden = powerhub_achtergronden();
	$classes       = array( 'sectie', 'sectie-' . $naam );
	$stijl         = '';
	$keuze         = $blok['achtergrond'] ?? '';
	$keuze         = ( 'gedeeld' === $keuze || isset( $achtergronden[ $keuze ] ) ) ? $keuze : (string) array_key_first( $achtergronden );

	if ( 'gedeeld' === $keuze ) {
		$een = isset( $achtergronden[ $blok['vlak_1'] ?? '' ] ) ? $blok['vlak_1'] : (string) array_key_first( $achtergronden );
		$twee = isset( $achtergronden[ $blok['vlak_2'] ?? '' ] ) ? $blok['vlak_2'] : (string) array_key_first( $achtergronden );
		$classes[] = 'achtergrond-gedeeld';
		$classes[] = 'verdeling-' . ( 'links' === ( $blok['verdeling'] ?? '' ) ? 'links' : 'boven' );
		$classes[] = 'tekst-op-' . $een;
		if ( $achtergronden[ $een ]['donker'] ) {
			$classes[] = 'is-donker';
		}
		$stijl = sprintf( '--vlak-1: var(--achtergrond-%s); --vlak-2: var(--achtergrond-%s);', sanitize_key( $een ), sanitize_key( $twee ) );
	} else {
		$classes[] = 'achtergrond-' . $keuze;
		if ( $achtergronden[ $keuze ]['donker'] ?? false ) {
			$classes[] = 'is-donker';
		}
	}
	$decoraties = powerhub_decoraties();
	$decoratie  = (string) ( $blok['decoratie'] ?? '' );
	if ( $decoratie && isset( $decoraties[ $decoratie ] ) ) {
		$classes[] = 'heeft-decoratie';
		$classes[] = 'decoratie-' . ( 'links' === ( $blok['decoratie_plek'] ?? '' ) ? 'links' : 'rechts' );
		if ( ! isset( $blok['decoratie_uitsteken'] ) || ! empty( $blok['decoratie_uitsteken'] ) ) {
			$classes[] = 'decoratie-uitsteken';
		}
		$d      = $decoraties[ $decoratie ];
		$stijl .= sprintf( ' --decoratie: url("%s"); --decoratie-verhouding: %s;', esc_url_raw( $d['bestand'] ), (float) ( $d['verhouding'] ?? 1 ) );
		/* Optioneel een tweede vorm (::after) en een eigen class voor de plaatsing in het thema. */
		if ( ! empty( $d['tweede']['bestand'] ) ) {
			$classes[] = 'heeft-decoratie-2';
			$stijl    .= sprintf( ' --decoratie-2: url("%s"); --decoratie-2-verhouding: %s;', esc_url_raw( $d['tweede']['bestand'] ), (float) ( $d['tweede']['verhouding'] ?? 1 ) );
		}
		if ( ! empty( $d['class'] ) ) {
			$classes[] = $d['class'];
		}
	}
	$classes[] = 'ruimte-boven-' . ( $blok['ruimte_boven'] ?? 'normaal' );
	$classes[] = 'ruimte-onder-' . ( $blok['ruimte_onder'] ?? 'normaal' );
	if ( $extra_class ) {
		$classes = array_merge( $classes, preg_split( '/\s+/', $extra_class ) );
	}

	$uit = 'class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) ) ) . '"';
	if ( ! empty( $blok['anker'] ) ) {
		$uit .= ' id="' . esc_attr( sanitize_title( $blok['anker'] ) ) . '"';
	}
	if ( trim( $stijl ) ) {
		$uit .= ' style="' . esc_attr( trim( $stijl ) ) . '"';
	}
	/* Animatie per blok (Geavanceerd); leeg volgt Powerhub, tab Animaties. */
	if ( ! empty( $blok['animatie'] ) ) {
		$uit .= ' data-blokanimatie="' . esc_attr( sanitize_key( $blok['animatie'] ) ) . '"';
	}
	if ( ! empty( $blok['animatie_volgorde'] ) ) {
		$uit .= ' data-blokvolgorde="' . esc_attr( sanitize_key( $blok['animatie_volgorde'] ) ) . '"';
	}
	return $uit;
}

/**
 * Oude naam, voor bestaande templates: alleen de classes.
 */
function powerhub_sectie_class( $blok, $naam, $extra = '' ) {
	preg_match( '/class="([^"]*)"/', powerhub_sectie( $blok, $naam, $extra ), $m );
	return $m[1] ?? '';
}

/**
 * CSS-variabelen --achtergrond-<sleutel> voor de 50/50 vlakken, uit de achtergronden met een tokenkleur.
 */
function powerhub_achtergrond_css() {
	$regels = array();
	foreach ( powerhub_achtergronden() as $sleutel => $a ) {
		if ( $a['token'] && '#' !== $a['token'][0] ) {
			$regels[] = '--achtergrond-' . sanitize_key( $sleutel ) . ':var(--' . str_replace( '.', '-', $a['token'] ) . ')';
		} elseif ( $a['kleur'] ) {
			$regels[] = '--achtergrond-' . sanitize_key( $sleutel ) . ':' . $a['kleur'];
		}
	}
	return $regels ? ':root{' . implode( ';', $regels ) . '}' : '';
}

/**
 * CSS en JS van blokken laden, alleen voor de blokken die op de pagina staan.
 */
function powerhub_blok_assets( $namen ) {
	$laden = array( 'in_footer' => true, 'strategy' => 'defer' );
	$versie = defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : POWERHUB_VERSIE;
	foreach ( array_unique( (array) $namen ) as $naam ) {
		$naam = sanitize_file_name( $naam );
		if ( wp_style_is( 'blok-' . $naam, 'enqueued' ) || wp_script_is( 'blok-' . $naam, 'enqueued' ) ) {
			continue;
		}
		$css = '/assets/css/blokken/' . $naam . '.css';
		$js  = '/assets/js/blokken/' . $naam . '.js';
		if ( file_exists( get_template_directory() . $css ) ) {
			wp_enqueue_style( 'blok-' . $naam, get_template_directory_uri() . $css, array( powerhub_stijl_handle() ), $versie . '.' . filemtime( get_template_directory() . $css ) );
		}
		if ( file_exists( get_template_directory() . $js ) ) {
			wp_enqueue_script( 'blok-' . $naam, get_template_directory_uri() . $js, array(), $versie . '.' . filemtime( get_template_directory() . $js ), $laden );
		}
	}
}

add_action( 'wp_enqueue_scripts', function () {
	$css = powerhub_achtergrond_css();
	if ( $css ) {
		wp_add_inline_style( powerhub_stijl_handle(), $css );
	}
	/* Blokken van de huidige pagina vooraf laden, zodat de stijl in de <head> staat. ACF bewaart de layoutnamen als lijst. */
	if ( is_singular() ) {
		$layouts = get_post_meta( get_queried_object_id(), 'blokken', true );
		if ( is_array( $layouts ) ) {
			powerhub_blok_assets( $layouts );
		}
	}
	/* Vaste templates (blog, artikel) kunnen hun blokken opgeven. */
	powerhub_blok_assets( (array) apply_filters( 'powerhub_vaste_blokken', array() ) );
}, 25 );

/**
 * Admin-melding als ACF Pro ontbreekt.
 */
add_action( 'admin_notices', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p><strong>Powerhub:</strong> activeer ACF Pro, anders werken de blokken niet.</p></div>';
	}
} );
