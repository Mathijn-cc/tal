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

function powerhub_lettertypes_ophalen( $url ) {
	$antwoord = wp_remote_get( $url, array(
		'timeout'    => 20,
		/* Een moderne browser, zodat Google .woff2-bestanden geeft. */
		'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
	) );
	if ( is_wp_error( $antwoord ) || 200 !== (int) wp_remote_retrieve_response_code( $antwoord ) ) {
		return null;
	}
	return wp_remote_retrieve_body( $antwoord );
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

add_action( 'wp_ajax_powerhub_lettertype_toevoegen', function () {
	check_ajax_referer( 'powerhub_lettertypes', 'nonce' );
	if ( ! powerhub_lettertypes_mag() ) {
		wp_send_json_error( 'Geen toegang.', 403 );
	}
	$bron    = 'fontshare' === ( $_POST['bron'] ?? '' ) ? 'fontshare' : 'google';
	$naam    = sanitize_text_field( wp_unslash( $_POST['naam'] ?? '' ) );
	$slug    = sanitize_title( wp_unslash( $_POST['slug'] ?? $naam ) );
	$soort   = sanitize_text_field( wp_unslash( $_POST['soort'] ?? '' ) );
	$bereik  = sanitize_text_field( wp_unslash( $_POST['bereik'] ?? '' ) );
	$cursief = ! empty( $_POST['cursief'] );
	$gewicht = array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['gewichten'] ?? '' ) ) ) );
	if ( '' === $naam || '' === $slug ) {
		wp_send_json_error( 'Geef de naam van het lettertype op.' );
	}
	$sleutel = $bron . '-' . $slug;

	$css = powerhub_lettertypes_ophalen( powerhub_lettertype_css_url( $bron, $naam, $slug, $gewicht, $cursief, $bereik ) );
	if ( ! $css && 'google' === $bron ) {
		/* Zonder gegevens uit de bibliotheek: alleen het standaardgewicht proberen. */
		$css = powerhub_lettertypes_ophalen( powerhub_lettertype_css_url( $bron, $naam, $slug, array(), false, '' ) );
	}
	$regels = powerhub_lettertype_css_lezen( $css );
	if ( ! $regels ) {
		wp_send_json_error( sprintf( '"%s" is niet gevonden bij %s. Controleer de schrijfwijze.', $naam, 'google' === $bron ? 'Google Fonts' : 'Fontshare' ) );
	}

	$map = powerhub_lettertypes_map( $sleutel );
	if ( ! wp_mkdir_p( $map['pad'] ) ) {
		wp_send_json_error( 'De map in uploads kon niet worden gemaakt.' );
	}
	$bestanden = array();
	foreach ( $regels as $i => $r ) {
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
		wp_send_json_error( 'De bestanden konden niet worden opgehaald. Probeer het later opnieuw.' );
	}

	$lijst             = powerhub_lettertypes_opgeslagen();
	$lijst[ $sleutel ] = array(
		'naam'      => $naam,
		'fallback'  => powerhub_lettertype_fallback( $soort ),
		'bron'      => $bron,
		'bestanden' => $bestanden,
	);
	update_option( POWERHUB_LETTERTYPES_OPTIE, $lijst, false );
	wp_send_json_success( array( 'sleutel' => $sleutel, 'bestanden' => count( $bestanden ) ) );
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
		if ( ! $id || ! in_array( $ext, array( 'woff', 'woff2' ), true ) ) {
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
	$sleutel           = 'eigen-' . sanitize_title( $naam );
	$lijst             = powerhub_lettertypes_opgeslagen();
	$lijst[ $sleutel ] = array(
		'naam'      => $naam,
		'fallback'  => powerhub_lettertype_fallback( $soort ),
		'bron'      => 'eigen',
		'bestanden' => $bestanden,
	);
	update_option( POWERHUB_LETTERTYPES_OPTIE, $lijst, false );
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

		<h3 class="powerhub-tussenkop">Bibliotheek</h3>
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
				<div class="powerhub-invoer"><input type="text" id="powerhub-eigen-naam" data-eigen-naam placeholder="Bijvoorbeeld ES Klarheit Kurrent"></div>
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
			<div class="powerhub-acties">
				<button type="button" class="button button-primary" data-eigen-opslaan disabled>Lettertype toevoegen</button>
			</div>
		</div>
	</div>
	<?php
}
