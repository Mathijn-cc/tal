<?php
/**
 * Module Lettertypes: een bibliotheek met Google Fonts en Fontshare, plus eigen lettertypes uploaden.
 *
 * Lettertypes uit de bibliotheek worden één keer door de server opgehaald en daarna lokaal bewaard in
 * uploads/powerhub-lettertypes. De site laadt ze dus nooit van Google of Fontshare (AVG en snelheid).
 * Eigen lettertypes zijn .woff2- of .woff-bestanden uit de mediabibliotheek.
 *
 * Alles wat hier wordt toegevoegd komt bij de lettertypes van het thema (filter powerhub_fonts)
 * en is te kiezen voor elke rol in de tab Typografie.
 */

defined( 'ABSPATH' ) || exit;

const POWERHUB_LETTERTYPES_OPTIE = 'powerhub_lettertypes';
const POWERHUB_LETTERTYPES_MAP   = 'powerhub-lettertypes';

/**
 * De toegevoegde lettertypes, zoals opgeslagen.
 */
function powerhub_lettertypes_opgeslagen() {
	$lijst = get_option( POWERHUB_LETTERTYPES_OPTIE, array() );
	return is_array( $lijst ) ? $lijst : array();
}

/* Toegevoegde lettertypes bij die van het thema; die van het thema gaan voor bij een gelijke sleutel. */
add_filter( 'powerhub_fonts', function ( $fonts ) {
	foreach ( powerhub_lettertypes_opgeslagen() as $sleutel => $font ) {
		if ( ! isset( $fonts[ $sleutel ] ) && ! empty( $font['bestanden'] ) ) {
			$fonts[ $sleutel ] = $font;
		}
	}
	return $fonts;
}, 20 );

/**
 * Reservelettertypes per soort, voor zolang het lettertype laadt.
 */
function powerhub_lettertype_fallback( $soort ) {
	$soort = strtolower( (string) $soort );
	if ( false !== strpos( $soort, 'serif' ) && false === strpos( $soort, 'sans' ) ) {
		return 'Georgia, "Times New Roman", serif';
	}
	if ( false !== strpos( $soort, 'mono' ) ) {
		return 'ui-monospace, SFMono-Regular, Menlo, monospace';
	}
	return 'system-ui, -apple-system, "Segoe UI", sans-serif';
}

function powerhub_lettertypes_mag() {
	return current_user_can( 'manage_options' ) && powerhub_is_beheerder();
}

/* ------------------------------------------------------------------------- */
/* Catalogus: Google Fonts en Fontshare, een week bewaard.                    */
/* ------------------------------------------------------------------------- */

/* Alleen deze hosts worden ooit benaderd: de catalogi, de CSS en de lettertypebestanden van Google en Fontshare. */
const POWERHUB_LETTERTYPES_HOSTS = array( 'fonts.google.com', 'fonts.googleapis.com', 'fonts.gstatic.com', 'api.fontshare.com', 'cdn.fontshare.com' );

/* Grenzen: een lettertypebestand is nooit groter dan 5 MB, een catalogus nooit groter dan 20 MB, hooguit 40 bestanden per lettertype. */
const POWERHUB_LETTERTYPES_MAX_BESTAND = 5242880;
const POWERHUB_LETTERTYPES_MAX_AANTAL  = 40;

function powerhub_lettertypes_ophalen( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! in_array( $host, POWERHUB_LETTERTYPES_HOSTS, true ) ) {
		return null;
	}
	$is_bestand = in_array( $host, array( 'fonts.gstatic.com', 'cdn.fontshare.com' ), true );
	/* wp_safe_remote_get weigert interne adressen en volgt geen omleidingen naar andere hosts. */
	$antwoord = wp_safe_remote_get( $url, array(
		'timeout'             => 20,
		'redirection'         => 0,
		'limit_response_size' => $is_bestand ? POWERHUB_LETTERTYPES_MAX_BESTAND + 1 : 20971520,
		/* Een moderne browser, zodat Google .woff2-bestanden geeft. */
		'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
	) );
	if ( is_wp_error( $antwoord ) || 200 !== (int) wp_remote_retrieve_response_code( $antwoord ) ) {
		return null;
	}
	$body = wp_remote_retrieve_body( $antwoord );
	if ( $is_bestand && strlen( $body ) > POWERHUB_LETTERTYPES_MAX_BESTAND ) {
		return null;
	}
	return $body;
}

function powerhub_lettertypes_catalogus_google() {
	$ruw = powerhub_lettertypes_ophalen( 'https://fonts.google.com/metadata/fonts' );
	if ( ! $ruw ) {
		return array();
	}
	$data = json_decode( substr( $ruw, (int) strpos( $ruw, '{' ) ), true );
	$uit  = array();
	foreach ( (array) ( $data['familyMetadataList'] ?? array() ) as $f ) {
		if ( empty( $f['family'] ) ) {
			continue;
		}
		$gewichten = array();
		$cursief   = false;
		foreach ( array_keys( (array) ( $f['fonts'] ?? array() ) ) as $stijl ) {
			if ( 'i' === substr( $stijl, -1 ) ) {
				$cursief = true;
			} else {
				$gewichten[] = (int) $stijl;
			}
		}
		$bereik = '';
		foreach ( (array) ( $f['axes'] ?? array() ) as $as ) {
			if ( 'wght' === ( $as['tag'] ?? '' ) ) {
				$bereik = (int) $as['min'] . '..' . (int) $as['max'];
			}
		}
		$uit[] = array(
			'b' => 'google',
			'n' => $f['family'],
			'c' => (string) ( $f['category'] ?? '' ),
			'w' => array_values( array_unique( array_filter( $gewichten ) ) ),
			'i' => $cursief,
			'v' => $bereik,
			'p' => (int) ( $f['popularity'] ?? 9999 ),
		);
	}
	return $uit;
}

function powerhub_lettertypes_catalogus_fontshare() {
	$uit = array();
	for ( $pagina = 0; $pagina < 5; $pagina++ ) {
		$ruw  = powerhub_lettertypes_ophalen( 'https://api.fontshare.com/v2/fonts?limit=100&offset=' . ( $pagina * 100 ) );
		$data = $ruw ? json_decode( $ruw, true ) : null;
		$rij  = (array) ( $data['fonts'] ?? array() );
		foreach ( $rij as $f ) {
			if ( empty( $f['name'] ) || empty( $f['slug'] ) ) {
				continue;
			}
			$gewichten = array();
			$cursief   = false;
			foreach ( (array) ( $f['styles'] ?? array() ) as $s ) {
				if ( ! empty( $s['is_variable'] ) ) {
					continue;
				}
				if ( ! empty( $s['is_italic'] ) ) {
					$cursief = true;
				} elseif ( isset( $s['weight']['number'] ) ) {
					$gewichten[] = (int) $s['weight']['number'];
				}
			}
			sort( $gewichten );
			$uit[] = array(
				'b' => 'fontshare',
				'n' => $f['name'],
				's' => $f['slug'],
				'c' => (string) ( $f['category'] ?? '' ),
				'w' => array_values( array_unique( array_filter( $gewichten ) ) ),
				'i' => $cursief,
				'v' => '',
				'p' => 0,
			);
		}
		if ( count( $rij ) < 100 ) {
			break;
		}
	}
	return $uit;
}

function powerhub_lettertypes_catalogus() {
	$lijst = get_transient( 'powerhub_lettertypes_catalogus' );
	if ( is_array( $lijst ) && $lijst ) {
		return $lijst;
	}
	$lijst = array_merge( powerhub_lettertypes_catalogus_fontshare(), powerhub_lettertypes_catalogus_google() );
	if ( $lijst ) {
		set_transient( 'powerhub_lettertypes_catalogus', $lijst, WEEK_IN_SECONDS );
	}
	return $lijst;
}

add_action( 'wp_ajax_powerhub_lettertypes_catalogus', function () {
	check_ajax_referer( 'powerhub_lettertypes', 'nonce' );
	if ( ! powerhub_lettertypes_mag() ) {
		wp_send_json_error( 'Geen toegang.', 403 );
	}
	$lijst = powerhub_lettertypes_catalogus();
	if ( ! $lijst ) {
		wp_send_json_error( 'De bibliotheek is nu niet bereikbaar. Typ de naam van het lettertype en kies Toevoegen.' );
	}
	wp_send_json_success( $lijst );
} );

/* ------------------------------------------------------------------------- */
/* Toevoegen uit de bibliotheek: CSS opvragen, .woff2-bestanden lokaal zetten. */
/* ------------------------------------------------------------------------- */

/**
 * Het CSS-adres bij Google of Fontshare voor een lettertype.
 */
function powerhub_lettertype_css_url( $bron, $naam, $slug, $gewichten, $cursief, $bereik ) {
	$gewichten = array_values( array_unique( array_map( 'intval', (array) $gewichten ) ) );
	sort( $gewichten );
	if ( 'fontshare' === $bron ) {
		$codes = $gewichten ? $gewichten : array( 400, 700 );
		if ( $cursief ) {
			/* Bij Fontshare is gewicht + 1 de cursieve variant (401 is 400 cursief). */
			foreach ( $codes as $g ) {
				$codes[] = $g + 1;
			}
		}
		return 'https://api.fontshare.com/v2/css?f[]=' . rawurlencode( $slug ) . '@' . implode( ',', $codes ) . '&display=swap';
	}
	$familie = str_replace( '%20', '+', rawurlencode( $naam ) );
	if ( preg_match( '/^(\d+)\.\.(\d+)$/', (string) $bereik ) ) {
		$as = $cursief ? ':ital,wght@0,' . $bereik . ';1,' . $bereik : ':wght@' . $bereik;
	} elseif ( $gewichten ) {
		$rijen = array();
		foreach ( $gewichten as $g ) {
			$rijen[] = '0,' . $g;
		}
		if ( $cursief ) {
			foreach ( $gewichten as $g ) {
				$rijen[] = '1,' . $g;
			}
		}
		$as = ':ital,wght@' . implode( ';', $rijen );
	} else {
		$as = '';
	}
	return 'https://fonts.googleapis.com/css2?family=' . $familie . $as . '&display=swap';
}

/**
 * De @font-face-regels uit CSS: gewicht, stijl, .woff2-adres en tekenbereik.
 * Van Google alleen de Latijnse delen (latin en latin-ext).
 */
function powerhub_lettertype_css_lezen( $css ) {
	$uit = array();
	if ( ! preg_match_all( '#(?:/\*\s*([a-z0-9\-\[\]]+)\s*\*/\s*)?@font-face\s*\{([^}]*)\}#i', (string) $css, $treffers, PREG_SET_ORDER ) ) {
		return $uit;
	}
	foreach ( $treffers as $t ) {
		$deel = strtolower( trim( $t[1] ) );
		if ( $deel && ! in_array( $deel, array( 'latin', 'latin-ext' ), true ) ) {
			continue;
		}
		$blok = $t[2];
		if ( ! preg_match( '#url\(\s*[\'"]?([^\'")]+\.woff2)[\'"]?\s*\)#i', $blok, $url ) ) {
			continue;
		}
		preg_match( '#font-weight:\s*([0-9 ]+)#i', $blok, $gewicht );
		preg_match( '#font-style:\s*(italic|normal|oblique)#i', $blok, $stijl );
		preg_match( '#unicode-range:\s*([^;]+)#i', $blok, $bereik );
		$adres = trim( $url[1] );
		if ( 0 === strpos( $adres, '//' ) ) {
			$adres = 'https:' . $adres;
		}
		$uit[] = array(
			'url'     => $adres,
			'gewicht' => trim( $gewicht[1] ?? '400' ),
			'stijl'   => strtolower( $stijl[1] ?? 'normal' ),
			'unicode' => trim( $bereik[1] ?? '' ),
			'deel'    => $deel,
		);
	}
	/* Latin eerst, zodat het voorladen het belangrijkste bestand pakt. */
	usort( $uit, function ( $a, $b ) {
		return ( 'latin-ext' === $a['deel'] ) <=> ( 'latin-ext' === $b['deel'] );
	} );
	return $uit;
}

function powerhub_lettertypes_map( $slug ) {
	$basis = wp_upload_dir();
	return array(
		'pad'  => trailingslashit( $basis['basedir'] ) . POWERHUB_LETTERTYPES_MAP . '/' . $slug,
		'rel'  => POWERHUB_LETTERTYPES_MAP . '/' . $slug,
	);
}

/**
 * Lettertype uit de bibliotheek ophalen en lokaal zetten. Ook bruikbaar in uitroltaken.
 * $args: bron (google|fontshare), naam, slug, soort, bereik ('100..900'), cursief, gewichten (array).
 * Geeft de sleutel terug of een WP_Error.
 */
function powerhub_lettertype_installeren( $args ) {
	$args    = wp_parse_args( $args, array( 'bron' => 'google', 'naam' => '', 'slug' => '', 'soort' => '', 'bereik' => '', 'cursief' => false, 'gewichten' => array() ) );
	$bron    = 'fontshare' === $args['bron'] ? 'fontshare' : 'google';
	$naam    = sanitize_text_field( $args['naam'] );
	$slug    = sanitize_title( $args['slug'] ? $args['slug'] : $naam );
	$gewicht = array_filter( array_map( 'absint', (array) $args['gewichten'] ) );
	if ( '' === $naam || '' === $slug ) {
		return new WP_Error( 'naam', 'Geef de naam van het lettertype op.' );
	}
	$sleutel = $bron . '-' . $slug;

	$css = powerhub_lettertypes_ophalen( powerhub_lettertype_css_url( $bron, $naam, $slug, $gewicht, $args['cursief'], $args['bereik'] ) );
	if ( ! $css && 'google' === $bron ) {
		/* Zonder gegevens uit de bibliotheek: alleen het standaardgewicht proberen. */
		$css = powerhub_lettertypes_ophalen( powerhub_lettertype_css_url( $bron, $naam, $slug, array(), false, '' ) );
	}
	$regels = powerhub_lettertype_css_lezen( $css );
	if ( ! $regels ) {
		return new WP_Error( 'niet-gevonden', sprintf( '"%s" is niet gevonden bij %s. Controleer de schrijfwijze.', $naam, 'google' === $bron ? 'Google Fonts' : 'Fontshare' ) );
	}

	$map = powerhub_lettertypes_map( $sleutel );
	if ( ! wp_mkdir_p( $map['pad'] ) ) {
		return new WP_Error( 'map', 'De map in uploads kon niet worden gemaakt.' );
	}
	$bestanden = array();
	foreach ( array_slice( $regels, 0, POWERHUB_LETTERTYPES_MAX_AANTAL ) as $i => $r ) {
		$inhoud = powerhub_lettertypes_ophalen( $r['url'] );
		if ( ! $inhoud || 'wOF2' !== substr( $inhoud, 0, 4 ) ) {
			continue;
		}
		$naam_bestand = sanitize_file_name( sprintf( '%s-%s-%s%s.woff2', $slug, str_replace( ' ', '-', $r['gewicht'] ), $r['stijl'], $r['deel'] ? '-' . $r['deel'] : ( $i ? '-' . $i : '' ) ) );
		if ( false === file_put_contents( $map['pad'] . '/' . $naam_bestand, $inhoud ) ) { // phpcs:ignore
			continue;
		}
		$bestanden[] = array(
			'bestand' => 'uploads:' . $map['rel'] . '/' . $naam_bestand,
			'gewicht' => $r['gewicht'],
			'stijl'   => $r['stijl'],
			'unicode' => $r['unicode'],
		);
	}
	if ( ! $bestanden ) {
		return new WP_Error( 'bestanden', 'De bestanden konden niet worden opgehaald. Probeer het later opnieuw.' );
	}
	powerhub_lettertype_bewaren( $sleutel, $naam, powerhub_lettertype_fallback( $args['soort'] ), $bron, $bestanden );
	return $sleutel;
}

/**
 * Lettertype vastleggen in de lijst van toegevoegde lettertypes.
 */
function powerhub_lettertype_bewaren( $sleutel, $naam, $fallback, $bron, $bestanden ) {
	$lijst             = powerhub_lettertypes_opgeslagen();
	$lijst[ $sleutel ] = array(
		'naam'      => $naam,
		'fallback'  => $fallback,
		'bron'      => $bron,
		'bestanden' => array_values( $bestanden ),
	);
	update_option( POWERHUB_LETTERTYPES_OPTIE, $lijst, false );
}

/**
 * Lokale bestanden (bijvoorbeeld uit een thema dat overstapt) naar uploads kopiëren en als lettertype vastleggen.
 * $bestanden: array( array( 'pad' => absoluut pad, 'gewicht' => '400', 'stijl' => 'normal' ) ).
 */
function powerhub_lettertype_overnemen( $sleutel, $naam, $fallback, $bron, $bestanden ) {
	$sleutel = sanitize_key( $sleutel );
	$map     = powerhub_lettertypes_map( $sleutel );
	if ( ! wp_mkdir_p( $map['pad'] ) ) {
		return false;
	}
	$uit = array();
	foreach ( $bestanden as $b ) {
		if ( empty( $b['pad'] ) || ! is_readable( $b['pad'] ) ) {
			return false;
		}
		$naam_bestand = sanitize_file_name( basename( $b['pad'] ) );
		if ( ! copy( $b['pad'], $map['pad'] . '/' . $naam_bestand ) ) {
			return false;
		}
		$uit[] = array(
			'bestand' => 'uploads:' . $map['rel'] . '/' . $naam_bestand,
			'gewicht' => $b['gewicht'] ?? '400',
			'stijl'   => $b['stijl'] ?? 'normal',
			'unicode' => '',
		);
	}
	powerhub_lettertype_bewaren( $sleutel, $naam, $fallback, $bron, $uit );
	return $sleutel;
}

/**
 * Lettertypes per rol vastleggen in de Powerhub-instellingen: array( 'kop' => sleutel, ... ).
 */
function powerhub_lettertypes_rollen_zetten( $rollen ) {
	$opties = get_option( POWERHUB_OPTIE, array() );
	$opties = is_array( $opties ) ? $opties : array();
	foreach ( (array) $rollen as $rol => $sleutel ) {
		$opties[ 'font_' . sanitize_key( $rol ) ] = $sleutel;
	}
	update_option( POWERHUB_OPTIE, $opties );
}

/*
 * Standaardlettertypes van een thema, via de filter powerhub_lettertypes_standaard:
 *   add_filter( 'powerhub_lettertypes_standaard', fn() => array(
 *       array( 'bron' => 'google', 'naam' => 'Inter', 'bereik' => '100..900', 'cursief' => true, 'rollen' => array( 'kop', 'tekst', 'accent' ) ),
 *   ) );
 * Bij de eerste uitrol (en bij het activeren van het thema) haalt de server ze op. Rollen die al een
 * lettertype hebben, blijven zoals ze zijn.
 */
function powerhub_lettertypes_standaard_installeren() {
	$standaard = (array) apply_filters( 'powerhub_lettertypes_standaard', array() );
	if ( ! $standaard ) {
		return true;
	}
	$opties = get_option( POWERHUB_OPTIE, array() );
	$lijst  = powerhub_lettertypes_opgeslagen();
	$rollen = array();
	foreach ( $standaard as $font ) {
		$sleutel = ( 'fontshare' === ( $font['bron'] ?? '' ) ? 'fontshare' : 'google' ) . '-' . sanitize_title( $font['slug'] ?? $font['naam'] );
		if ( ! isset( $lijst[ $sleutel ] ) ) {
			$sleutel = powerhub_lettertype_installeren( $font );
			if ( is_wp_error( $sleutel ) ) {
				return false;
			}
		}
		foreach ( (array) ( $font['rollen'] ?? array() ) as $rol ) {
			if ( empty( $opties[ 'font_' . $rol ] ) ) {
				$rollen[ $rol ] = $sleutel;
			}
		}
	}
	if ( $rollen ) {
		powerhub_lettertypes_rollen_zetten( $rollen );
	}
	return true;
}

add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['powerhub-lettertypes-standaard-v1'] = 'powerhub_lettertypes_standaard_installeren';
	return $taken;
} );
add_action( 'after_switch_theme', 'powerhub_lettertypes_standaard_installeren' );

add_action( 'wp_ajax_powerhub_lettertype_toevoegen', function () {
	check_ajax_referer( 'powerhub_lettertypes', 'nonce' );
	if ( ! powerhub_lettertypes_mag() ) {
		wp_send_json_error( 'Geen toegang.', 403 );
	}
	$sleutel = powerhub_lettertype_installeren( array(
		'bron'      => sanitize_key( wp_unslash( $_POST['bron'] ?? '' ) ),
		'naam'      => wp_unslash( $_POST['naam'] ?? '' ),
		'slug'      => wp_unslash( $_POST['slug'] ?? '' ),
		'soort'     => sanitize_text_field( wp_unslash( $_POST['soort'] ?? '' ) ),
		'bereik'    => sanitize_text_field( wp_unslash( $_POST['bereik'] ?? '' ) ),
		'cursief'   => ! empty( $_POST['cursief'] ),
		'gewichten' => explode( ',', (string) wp_unslash( $_POST['gewichten'] ?? '' ) ),
	) );
	if ( is_wp_error( $sleutel ) ) {
		wp_send_json_error( $sleutel->get_error_message() );
	}
	wp_send_json_success( array( 'sleutel' => $sleutel ) );
} );

/* ------------------------------------------------------------------------- */
/* Eigen lettertypes: .woff2 en .woff via de mediabibliotheek.                */
/* ------------------------------------------------------------------------- */

add_filter( 'upload_mimes', function ( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['woff2'] = 'font/woff2';
		$mimes['woff']  = 'font/woff';
	}
	return $mimes;
} );

/* PHP herkent lettertypes niet altijd: op de extensie en de eerste bytes vertrouwen, alleen voor beheerders. */
add_filter( 'wp_check_filetype_and_ext', function ( $data, $bestand, $naam ) {
	$ext = strtolower( pathinfo( (string) $naam, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'woff', 'woff2' ), true ) || ! current_user_can( 'manage_options' ) ) {
		return $data;
	}
	$kop = (string) file_get_contents( $bestand, false, null, 0, 4 ); // phpcs:ignore
	if ( ( 'woff2' === $ext && 'wOF2' === $kop ) || ( 'woff' === $ext && 'wOFF' === $kop ) ) {
		$data['ext']             = $ext;
		$data['type']            = 'font/' . $ext;
		$data['proper_filename'] = false;
	}
	return $data;
}, 10, 3 );

add_action( 'wp_ajax_powerhub_lettertype_eigen', function () {
	check_ajax_referer( 'powerhub_lettertypes', 'nonce' );
	if ( ! powerhub_lettertypes_mag() ) {
		wp_send_json_error( 'Geen toegang.', 403 );
	}
	$naam  = sanitize_text_field( wp_unslash( $_POST['naam'] ?? '' ) );
	$soort = sanitize_text_field( wp_unslash( $_POST['soort'] ?? 'sans' ) );
	$ruw   = json_decode( wp_unslash( $_POST['bestanden'] ?? '[]' ), true );
	if ( '' === $naam || ! is_array( $ruw ) || ! $ruw ) {
		wp_send_json_error( 'Geef een naam op en kies minstens één bestand.' );
	}
	$bestanden = array();
	foreach ( $ruw as $b ) {
		$id  = absint( $b['id'] ?? 0 );
		$ext = strtolower( pathinfo( (string) get_attached_file( $id ), PATHINFO_EXTENSION ) );
		$pad = (string) get_attached_file( $id );
		if ( ! $id || ! in_array( $ext, array( 'woff', 'woff2' ), true ) || ! is_readable( $pad ) || filesize( $pad ) > POWERHUB_LETTERTYPES_MAX_BESTAND ) {
			continue;
		}
		$gewicht = preg_match( '/^\d{3}( \d{3})?$/', (string) ( $b['gewicht'] ?? '' ) ) ? $b['gewicht'] : '400';
		$bestanden[] = array(
			'bestand' => 'bijlage:' . $id,
			'gewicht' => $gewicht,
			'stijl'   => 'italic' === ( $b['stijl'] ?? '' ) ? 'italic' : 'normal',
			'formaat' => $ext,
		);
	}
	if ( ! $bestanden ) {
		wp_send_json_error( 'Alleen .woff2- en .woff-bestanden kunnen worden gebruikt.' );
	}
	$sleutel = 'eigen-' . sanitize_title( $naam );
	powerhub_lettertype_bewaren( $sleutel, $naam, powerhub_lettertype_fallback( $soort ), 'eigen', $bestanden );
	wp_send_json_success( array( 'sleutel' => $sleutel ) );
} );

/* ------------------------------------------------------------------------- */
/* Verwijderen, niet als het lettertype nog voor een rol gekozen is.          */
/* ------------------------------------------------------------------------- */

add_action( 'wp_ajax_powerhub_lettertype_verwijderen', function () {
	check_ajax_referer( 'powerhub_lettertypes', 'nonce' );
	if ( ! powerhub_lettertypes_mag() ) {
		wp_send_json_error( 'Geen toegang.', 403 );
	}
	$sleutel = sanitize_key( wp_unslash( $_POST['sleutel'] ?? '' ) );
	$lijst   = powerhub_lettertypes_opgeslagen();
	if ( ! isset( $lijst[ $sleutel ] ) ) {
		wp_send_json_error( 'Dit lettertype bestaat niet meer.' );
	}
	$opgeslagen = powerhub_opgeslagen();
	foreach ( powerhub_font_rollen() as $rol => $label ) {
		if ( ( $opgeslagen[ 'font_' . $rol ] ?? '' ) === $sleutel ) {
			wp_send_json_error( sprintf( 'Dit lettertype is gekozen voor "%s". Kies daar eerst een ander lettertype.', $label ) );
		}
	}
	if ( 'eigen' !== ( $lijst[ $sleutel ]['bron'] ?? '' ) ) {
		$map = powerhub_lettertypes_map( $sleutel );
		foreach ( (array) glob( $map['pad'] . '/*.woff2' ) as $bestand ) {
			wp_delete_file( $bestand );
		}
		@rmdir( $map['pad'] ); // phpcs:ignore
	}
	unset( $lijst[ $sleutel ] );
	update_option( POWERHUB_LETTERTYPES_OPTIE, $lijst, false );
	wp_send_json_success();
} );

/* ------------------------------------------------------------------------- */
/* Beheer in de tab Typografie.                                               */
/* ------------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_powerhub' !== $hook || ! powerhub_lettertypes_mag() ) {
		return;
	}
	wp_enqueue_script( 'powerhub-lettertypes', POWERHUB_URL . '/assets/lettertypes.js', array( 'jquery', 'powerhub' ), powerhub_versie( '/assets/lettertypes.js' ), true );
	wp_localize_script( 'powerhub-lettertypes', 'powerhubLettertypes', array(
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'powerhub_lettertypes' ),
	) );
	/* Voorbeelden van de toegevoegde lettertypes in hun eigen letter. */
	$css = '';
	foreach ( powerhub_lettertypes_opgeslagen() as $sleutel => $font ) {
		foreach ( (array) $font['bestanden'] as $b ) {
			$css .= powerhub_font_face( $font['naam'], $b );
		}
	}
	if ( $css ) {
		wp_add_inline_style( 'powerhub', $css );
	}
}, 20 );

function powerhub_lettertypes_beheer() {
	if ( ! powerhub_lettertypes_mag() ) {
		return;
	}
	$lijst  = powerhub_lettertypes_opgeslagen();
	$bronnen = array( 'google' => 'Google Fonts', 'fontshare' => 'Fontshare', 'eigen' => 'Eigen lettertype' );
	?>
	<div class="powerhub-lettertypes" data-lettertypes>
		<h3 class="powerhub-tussenkop">Toegevoegde lettertypes</h3>
		<small class="powerhub-uitleg">Staan lokaal op deze site en zijn hierboven te kiezen voor elke rol. Kies na het toevoegen een rol en sla op.</small>
		<?php if ( $lijst ) : ?>
			<ul class="powerhub-lettertypes-lijst">
				<?php foreach ( $lijst as $sleutel => $font ) : ?>
					<li>
						<span class="powerhub-lettertype-voorbeeld" style="font-family:'<?php echo esc_attr( powerhub_css_waarde( str_replace( "'", '', $font['naam'] ) ) ); ?>',<?php echo esc_attr( powerhub_css_waarde( $font['fallback'] ) ); ?>"><?php echo esc_html( $font['naam'] ); ?></span>
						<small><?php echo esc_html( ( $bronnen[ $font['bron'] ] ?? '' ) . ' · ' . count( (array) $font['bestanden'] ) . ' bestand' . ( 1 === count( (array) $font['bestanden'] ) ? '' : 'en' ) ); ?></small>
						<button type="button" class="button-link powerhub-lettertype-weg" data-lettertype-weg="<?php echo esc_attr( $sleutel ); ?>">Verwijderen</button>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="powerhub-lettertypes-leeg">Nog geen lettertypes toegevoegd.</p>
		<?php endif; ?>

		<div class="powerhub-lettertypes-open">
			<button type="button" class="button" data-lettertypes-open aria-expanded="false" aria-controls="powerhub-lettertypes-toevoegen">Lettertype toevoegen</button>
		</div>

		<div class="powerhub-lettertypes-toevoegen" id="powerhub-lettertypes-toevoegen" hidden>
		<h3 class="powerhub-tussenkop">Uit de bibliotheek</h3>
		<small class="powerhub-uitleg">Google Fonts en Fontshare. Bij toevoegen haalt de server de bestanden één keer op en zet ze lokaal; de site laadt nooit van Google of Fontshare.</small>
		<div class="powerhub-lettertypes-zoek">
			<input type="search" placeholder="Zoek een lettertype, bijvoorbeeld Satoshi of Inter" data-lettertype-zoek aria-label="Zoek een lettertype">
			<select data-lettertype-bron aria-label="Bron">
				<option value="">Alle bronnen</option>
				<option value="fontshare">Fontshare</option>
				<option value="google">Google Fonts</option>
			</select>
		</div>
		<p class="powerhub-lettertypes-status" data-lettertype-status role="status" aria-live="polite"></p>
		<ul class="powerhub-lettertypes-resultaten" data-lettertype-resultaten></ul>

		<h3 class="powerhub-tussenkop">Eigen lettertype uploaden</h3>
		<small class="powerhub-uitleg">Voor een huisstijllettertype dat niet in de bibliotheek staat. Gebruik .woff2-bestanden (of .woff) en controleer of de licentie gebruik op de website toestaat.</small>
		<div class="powerhub-lettertypes-eigen" data-lettertype-eigen>
			<div class="powerhub-veld">
				<label class="powerhub-label" for="powerhub-eigen-naam">Naam</label>
				<div class="powerhub-invoer"><input type="text" id="powerhub-eigen-naam" data-eigen-naam placeholder="Naam van het lettertype"></div>
			</div>
			<div class="powerhub-veld">
				<label class="powerhub-label" for="powerhub-eigen-soort">Soort</label>
				<div class="powerhub-invoer">
					<select id="powerhub-eigen-soort" data-eigen-soort>
						<option value="sans">Schreefloos</option>
						<option value="serif">Schreef</option>
						<option value="mono">Monospace</option>
					</select>
				</div>
			</div>
			<div class="powerhub-veld">
				<span class="powerhub-label">Bestanden</span>
				<div class="powerhub-invoer">
					<button type="button" class="button" data-eigen-kies>Bestanden kiezen</button>
					<ul class="powerhub-lettertypes-bestanden" data-eigen-bestanden></ul>
				</div>
			</div>
			<div class="powerhub-lettertypes-knoppen">
				<button type="button" class="button button-primary" data-eigen-opslaan disabled>Eigen lettertype opslaan</button>
			</div>
		</div>
		</div>
	</div>
	<?php
}
