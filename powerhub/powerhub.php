<?php
/**
 * Powerhub, het vaste skelet van Creators Connect voor custom thema's.
 *
 * Deze map is in elk project identiek. Projectcode past hier niets aan, maar haakt in via:
 * - powerhub_tab()                 eigen tab met velden in Powerhub
 * - designsysteem/tokens.json      de bron van waarheid: tokens, zie modules/designsysteem.php
 * - filter powerhub_kleuren        welke kleuren in Powerhub overschreven mogen worden
 * - filter powerhub_fonts          de lokaal meegeleverde lettertypes
 * - filter powerhub_blok_layouts   de ACF-blokken
 * - filter powerhub_uitroltaken    eenmalige taken na een uitrol
 * Zie README.md in deze map.
 */

defined( 'ABSPATH' ) || exit;

define( 'POWERHUB_VERSIE', '1.2.3' );
define( 'POWERHUB_PAD', __DIR__ );
define( 'POWERHUB_URL', get_template_directory_uri() . '/powerhub' );
const POWERHUB_OPTIE = 'powerhub';

/* Kern */
require POWERHUB_PAD . '/kern/register.php';
require POWERHUB_PAD . '/kern/toegang.php';
require POWERHUB_PAD . '/kern/opslag.php';
require POWERHUB_PAD . '/kern/velden.php';
require POWERHUB_PAD . '/kern/pagina.php';
require POWERHUB_PAD . '/kern/helpers.php';
require POWERHUB_PAD . '/kern/blokken.php';
require POWERHUB_PAD . '/kern/blokken-admin.php';
require POWERHUB_PAD . '/kern/uitrol.php';

/* Standaardmodules. Een project zet een module uit met de filter powerhub_modules. */
$powerhub_modules = apply_filters( 'powerhub_modules', array(
	'designsysteem',
	'toegankelijkheid',
	'algemeen',
	'kleuren',
	'typografie',
	'animaties',
	'footer',
	'seo',
	'beveiliging',
	'gereedschap',
) );
foreach ( $powerhub_modules as $powerhub_module ) {
	$powerhub_bestand = POWERHUB_PAD . '/modules/' . sanitize_file_name( $powerhub_module ) . '.php';
	if ( file_exists( $powerhub_bestand ) ) {
		require $powerhub_bestand;
	}
}
unset( $powerhub_modules, $powerhub_module, $powerhub_bestand );
