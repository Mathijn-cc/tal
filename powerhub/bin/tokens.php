<?php
/**
 * Genereert assets/css/tokens.css uit designsysteem/tokens.json.
 *
 * Gebruik, in de map van het thema:
 *   php powerhub/bin/tokens.php            schrijft assets/css/tokens.css
 *   php powerhub/bin/tokens.php --controle geeft een fout als tokens.css niet meer klopt met tokens.json
 *
 * De uitrol-workflow draait dit vóór elke uitrol, dus de site gebruikt altijd tokens.json.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit;
}

require __DIR__ . '/../kern/tokens.php';

$thema  = dirname( __DIR__, 2 );
$bron   = $thema . '/designsysteem/tokens.json';
$doel   = $thema . '/assets/css/tokens.css';
$tokens = powerhub_tokens_lezen( $bron );

if ( ! $tokens ) {
	fwrite( STDERR, "Geen tokens gevonden in designsysteem/tokens.json (of het bestand is geen geldige JSON).\n" );
	exit( 1 );
}

$css = powerhub_tokens_css( $tokens );

if ( in_array( '--controle', $argv, true ) ) {
	if ( ! is_readable( $doel ) || file_get_contents( $doel ) !== $css ) {
		fwrite( STDERR, "assets/css/tokens.css klopt niet met tokens.json. Draai: php powerhub/bin/tokens.php\n" );
		exit( 1 );
	}
	echo 'tokens.css klopt (' . count( $tokens ) . " tokens).\n";
	exit( 0 );
}

if ( ! is_dir( dirname( $doel ) ) ) {
	mkdir( dirname( $doel ), 0755, true );
}
file_put_contents( $doel, $css );
echo 'assets/css/tokens.css geschreven (' . count( $tokens ) . " tokens).\n";
