<?php
/**
 * Eenmalige taken na de uitrol (zie powerhub/kern/uitrol.php).
 * homepage-v1: zet de foto's in de mediabibliotheek, maakt de homepage met alle blokken en het hoofdmenu.
 * Draait één keer per omgeving. Opnieuw nodig? Verhoog het nummer (homepage-v2), nooit een sleutel hergebruiken.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['homepage-v1'] = 'tal_uitrol_homepage';
	return $taken;
} );

/* De blokken van de homepage, in volgorde. De inhoud komt uit de voorbeelden van elk blok. */
const TAL_HOMEPAGE_BLOKKEN = array( 'hero', 'herkenning', 'traject', 'programma', 'vier-delen', 'academy', 'resultaten', 'voor-wie', 'over-mij', 'citaat', 'podcast', 'cta' );

/**
 * Homepage, foto's en menu aanmaken. Geeft false als ACF Pro nog ontbreekt, dan probeert de volgende uitrol het opnieuw.
 */
function tal_uitrol_homepage() {
	if ( ! function_exists( 'update_field' ) ) {
		echo "ACF Pro ontbreekt nog.\n";
		return false;
	}

	$voorbeelden = (array) apply_filters( 'powerhub_blok_voorbeelden', array() );
	$rijen       = array();
	foreach ( TAL_HOMEPAGE_BLOKKEN as $naam ) {
		if ( empty( $voorbeelden[ $naam ] ) ) {
			continue;
		}
		$rij = $voorbeelden[ $naam ];
		/* Een blok kan meerdere voorbeelden hebben: neem het eerste. */
		if ( isset( $rij[0] ) && is_array( $rij[0] ) ) {
			$rij = $rij[0];
		}
		$rijen[] = array( 'acf_fc_layout' => $naam ) + tal_uitrol_beelden( $rij );
	}

	/* De homepage: een bestaande pagina Home gebruiken, anders een nieuwe. */
	$bestaand = get_posts( array( 'post_type' => 'page', 'title' => 'Home', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	$id       = $bestaand ? (int) $bestaand[0] : wp_insert_post( array(
		'post_type'   => 'page',
		'post_title'  => 'Home',
		'post_status' => 'publish',
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		echo "Homepage aanmaken mislukt.\n";
		return false;
	}
	update_field( 'field_powerhub_blokken', $rijen, $id );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $id );

	tal_uitrol_menu();
	echo 'Homepage klaar (pagina ' . (int) $id . ', ' . count( $rijen ) . " blokken).\n";
	return true;
}

/**
 * Vervangt in een blok alle verwijzingen naar assets/beelden door een bijlage uit de mediabibliotheek.
 */
function tal_uitrol_beelden( $waarde ) {
	if ( is_array( $waarde ) ) {
		return array_map( 'tal_uitrol_beelden', $waarde );
	}
	$basis = THEMA_URL . '/assets/beelden/';
	if ( is_string( $waarde ) && 0 === strpos( $waarde, $basis ) ) {
		$id = tal_uitrol_bijlage( rawurldecode( substr( $waarde, strlen( $basis ) ) ) );
		return $id ? $id : '';
	}
	return $waarde;
}

/**
 * Eén beeld uit het thema in de mediabibliotheek zetten (of de bestaande bijlage teruggeven).
 */
function tal_uitrol_bijlage( $bestand ) {
	$bestand = sanitize_file_name( $bestand );
	$bekend  = (array) get_option( 'tal_uitrol_bijlagen', array() );
	if ( ! empty( $bekend[ $bestand ] ) && get_post( $bekend[ $bestand ] ) ) {
		return (int) $bekend[ $bestand ];
	}
	$pad = THEMA_PAD . '/assets/beelden/' . $bestand;
	if ( ! is_readable( $pad ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload = wp_upload_bits( $bestand, null, file_get_contents( $pad ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		echo 'Beeld overgeslagen: ' . esc_html( $bestand ) . ' (' . esc_html( $upload['error'] ) . ")\n";
		return 0;
	}
	$id = wp_insert_attachment( array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => 'Chantal Strijbos, TAL leadership',
		'post_status'    => 'inherit',
	), $upload['file'] );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', 'Chantal Strijbos' );
	$bekend[ $bestand ] = $id;
	update_option( 'tal_uitrol_bijlagen', $bekend, false );
	return (int) $id;
}

/**
 * Hoofdmenu met de ankers op de homepage.
 */
function tal_uitrol_menu() {
	$locaties = get_nav_menu_locations();
	if ( ! empty( $locaties['hoofdmenu'] ) && wp_get_nav_menu_object( $locaties['hoofdmenu'] ) ) {
		return;
	}
	$menu = wp_get_nav_menu_object( 'Hoofdmenu' );
	$id   = $menu ? (int) $menu->term_id : wp_create_nav_menu( 'Hoofdmenu' );
	if ( is_wp_error( $id ) ) {
		return;
	}
	if ( ! wp_get_nav_menu_items( $id ) ) {
		$punten = array(
			'traject'   => 'Traject',
			'programma' => 'Programma',
			'voor-wie'  => 'Voor wie',
			'over-mij'  => 'Over mij',
			'podcast'   => 'Podcast',
		);
		foreach ( $punten as $anker => $titel ) {
			wp_update_nav_menu_item( $id, 0, array(
				'menu-item-title'  => $titel,
				'menu-item-url'    => home_url( '/#' . $anker ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			) );
		}
	}
	$locaties['hoofdmenu'] = $id;
	set_theme_mod( 'nav_menu_locations', $locaties );
}

/* Naam en ondertitel van de site (staat in de titelbalk en bij Rank Math). */
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['instellingen-v1'] = 'tal_uitrol_instellingen';
	return $taken;
} );

function tal_uitrol_instellingen() {
	update_option( 'blogname', 'TAL Leadership' );
	update_option( 'blogdescription', 'Persoonlijk traineeship voor leiders' );
	return true;
}

/* Podcastblok op de homepage: de echte Spotify-speler. */
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['podcast-spotify-v1'] = 'tal_uitrol_spotify';
	return $taken;
} );

function tal_uitrol_spotify() {
	if ( ! function_exists( 'update_sub_field' ) ) {
		return false;
	}
	$id    = (int) get_option( 'page_on_front' );
	$rijen = $id ? (array) get_post_meta( $id, 'blokken', true ) : array();
	$plek  = array_search( 'podcast', $rijen, true );
	if ( false === $plek ) {
		echo "Geen podcastblok op de homepage.\n";
		return true;
	}
	update_sub_field( array( 'field_powerhub_blokken', $plek + 1, 'field_podcast_spotify' ), 'https://open.spotify.com/show/5GOZrxoesUgtTvcfqINttf', $id );
	echo 'Spotify-speler gezet (blok ' . ( $plek + 1 ) . ").\n";
	return true;
}

/* Hero op mobiel: uitsnede naar rechts, zodat Chantal in beeld staat. */
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['hero-uitsnede-v1'] = 'tal_uitrol_hero_uitsnede';
	return $taken;
} );

function tal_uitrol_hero_uitsnede() {
	if ( ! function_exists( 'update_sub_field' ) ) {
		return false;
	}
	$id    = (int) get_option( 'page_on_front' );
	$rijen = $id ? (array) get_post_meta( $id, 'blokken', true ) : array();
	$plek  = array_search( 'hero', $rijen, true );
	if ( false !== $plek ) {
		update_sub_field( array( 'field_powerhub_blokken', $plek + 1, 'field_hero_beeld_x_mobiel' ), 70, $id );
		update_sub_field( array( 'field_powerhub_blokken', $plek + 1, 'field_hero_beeld_x' ), 50, $id );
	}
	return true;
}

/* Hero-knop "Bekijk het traject" en de Apple Podcasts-knop in het podcastblok. */
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['knoppen-v1'] = 'tal_uitrol_knoppen';
	return $taken;
} );

function tal_uitrol_knoppen() {
	if ( ! function_exists( 'update_sub_field' ) ) {
		return false;
	}
	$id    = (int) get_option( 'page_on_front' );
	$rijen = $id ? (array) get_post_meta( $id, 'blokken', true ) : array();
	$hero  = array_search( 'hero', $rijen, true );
	if ( false !== $hero ) {
		$knop = get_post_meta( $id, 'blokken_' . $hero . '_knop', true );
		$knop = is_array( $knop ) ? $knop : array( 'url' => '#traject', 'target' => '' );
		$knop['title'] = 'Bekijk het traject';
		update_sub_field( array( 'field_powerhub_blokken', $hero + 1, 'field_hero_knop' ), $knop, $id );
	}
	$podcast = array_search( 'podcast', $rijen, true );
	if ( false !== $podcast ) {
		update_sub_field( array( 'field_powerhub_blokken', $podcast + 1, 'field_podcast_knop_2' ), array( 'url' => TAL_APPLE_PODCAST, 'title' => 'Luister op Apple Podcasts', 'target' => '_blank' ), $id );
	}
	echo "Knoppen bijgewerkt.\n";
	return true;
}
