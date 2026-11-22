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
 *   Geavanceerd  anker voor links naar het blok, tijdelijk verbergen
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

	return array_merge(
		array( powerhub_acf_tab( $prefix, 'Weergave' ) ),
		$extra_weergave,
		array(
			powerhub_acf_veld( $prefix, 'achtergrond', 'Achtergrond', 'button_group', array_merge( powerhub_acf_breed( 100, 'kleurkeuze' ), array(
				'choices'       => $keuzes + array( 'gedeeld' => '50/50' ),
				'default_value' => $standaard,
				'instructions'  => '50/50 verdeelt het blok in twee vlakken met een harde overgang, bijvoorbeeld om een beeld over de grens te laten vallen.',
			) ) ),
			powerhub_acf_veld( $prefix, 'vlak_1', 'Eerste helft', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 34, 'kleurkeuze alleen-bolletjes veld-groep groep-begin' ), array(
				'choices'       => $keuzes,
				'default_value' => $standaard,
				'instructions'  => 'De tekst staat in deze helft en krijgt een passende kleur.',
			) ) ),
			powerhub_acf_veld( $prefix, 'vlak_2', 'Tweede helft', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 33, 'kleurkeuze alleen-bolletjes veld-groep' ), array(
				'choices'       => $keuzes,
				'default_value' => (string) array_key_first( $keuzes ),
			) ) ),
			powerhub_acf_veld( $prefix, 'verdeling', 'Verdeling', 'button_group', array_merge( $gedeeld, powerhub_acf_breed( 33, 'veld-groep groep-eind' ), array(
				'choices'       => array( 'boven' => 'Boven / onder', 'links' => 'Links / rechts' ),
				'default_value' => 'boven',
			) ) ),
			powerhub_acf_veld( $prefix, 'ruimte_boven', 'Ruimte boven', 'button_group', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => $ruimte,
				'default_value' => 'normaal',
			) ) ),
			powerhub_acf_veld( $prefix, 'ruimte_onder', 'Ruimte onder', 'button_group', array_merge( powerhub_acf_breed( 50 ), array(
				'choices'       => $ruimte,
				'default_value' => 'normaal',
			) ) ),
			powerhub_acf_tab( $prefix, 'Geavanceerd' ),
			powerhub_acf_veld( $prefix, 'anker', 'Anker', 'text', array_merge( powerhub_acf_breed( 50 ), array(
				'instructions' => 'Voor links naar dit blok, bijvoorbeeld "veelgestelde-vragen" voor /pagina/#veelgestelde-vragen. Kleine letters en streepjes.',
				'prepend'      => '#',
			) ) ),
			powerhub_acf_veld( $prefix, 'verbergen', 'Verbergen', 'true_false', array_merge( powerhub_acf_breed( 50 ), array(
				'ui'           => 1,
				'ui_on_text'   => 'Verborgen',
				'ui_off_text'  => 'Zichtbaar',
				'instructions' => 'Tijdelijk van de site halen zonder de inhoud te verliezen.',
			) ) ),
		)
	);
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
		'position'       => 'acf_after_title',
		'style'          => 'seamless',
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
	$classes[] = 'ruimte-boven-' . ( $blok['ruimte_boven'] ?? 'normaal' );
	$classes[] = 'ruimte-onder-' . ( $blok['ruimte_onder'] ?? 'normaal' );
	if ( $extra_class ) {
		$classes = array_merge( $classes, preg_split( '/\s+/', $extra_class ) );
	}

	$uit = 'class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) ) ) . '"';
	if ( ! empty( $blok['anker'] ) ) {
		$uit .= ' id="' . esc_attr( sanitize_title( $blok['anker'] ) ) . '"';
	}
	if ( $stijl ) {
		$uit .= ' style="' . esc_attr( $stijl ) . '"';
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
			wp_enqueue_style( 'blok-' . $naam, get_template_directory_uri() . $css, array( powerhub_stijl_handle() ), $versie );
		}
		if ( file_exists( get_template_directory() . $js ) ) {
			wp_enqueue_script( 'blok-' . $naam, get_template_directory_uri() . $js, array(), $versie, $laden );
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
