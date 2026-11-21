<?php
/**
 * Design tokens lezen uit designsysteem/tokens.json (W3C Design Tokens, DTCG).
 * Zonder WordPress bruikbaar, zodat ook de generator (powerhub/bin/tokens.php) dit bestand gebruikt.
 *
 * Naamgeving in CSS: --<groep>-<naam>, bijvoorbeeld kleur.lime-500 wordt --kleur-lime-500.
 * Een groep kan een eigen voorvoegsel krijgen (of geen) met $extensions["nl.creatorsconnect"]["prefix"].
 * Tekststijlen (type typography) geven --maat-<naam> en --regel-<naam>.
 * Mobiele waarde: $extensions["nl.creatorsconnect"]["mobiel"].
 */

const POWERHUB_TOKEN_EXT = 'nl.creatorsconnect';

/**
 * Alle tokens als platte lijst: pad => array( waarde, type, groep, naam, css, beschrijving, mobiel, bron ).
 */
function powerhub_tokens_lezen( $bestand ) {
	if ( ! is_readable( $bestand ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $bestand ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $data ) ) {
		return array();
	}
	$tokens = array();
	foreach ( $data as $groep => $inhoud ) {
		$groep = (string) $groep;
		if ( '$' === $groep[0] || ! is_array( $inhoud ) ) {
			continue;
		}
		$ext    = $inhoud['$extensions'][ POWERHUB_TOKEN_EXT ] ?? array();
		$prefix = array_key_exists( 'prefix', $ext ) ? (string) $ext['prefix'] : $groep;
		powerhub_tokens_doorlopen( $inhoud, $groep, array(), $prefix, $tokens );
	}
	return $tokens;
}

function powerhub_tokens_doorlopen( $knoop, $groep, $pad, $prefix, &$tokens ) {
	foreach ( $knoop as $sleutel => $waarde ) {
		$sleutel = (string) $sleutel; // Sleutels als "4" worden in PHP een getal.
		if ( '$' === $sleutel[0] || ! is_array( $waarde ) ) {
			continue;
		}
		$nieuw_pad = array_merge( $pad, array( $sleutel ) );
		if ( array_key_exists( '$value', $waarde ) ) {
			$naam = implode( '-', $nieuw_pad );
			$ext  = $waarde['$extensions'][ POWERHUB_TOKEN_EXT ] ?? array();
			$tokens[ $groep . '.' . implode( '.', $nieuw_pad ) ] = array(
				'waarde'       => $waarde['$value'],
				'type'         => $waarde['$type'] ?? '',
				'groep'        => $groep,
				'naam'         => $naam,
				'css'          => '--' . ( '' === $prefix ? '' : $prefix . '-' ) . $naam,
				'beschrijving' => $waarde['$description'] ?? '',
				'mobiel'       => $ext['mobiel'] ?? null,
				'bron'         => $ext['bron'] ?? '',
			);
		} else {
			powerhub_tokens_doorlopen( $waarde, $groep, $nieuw_pad, $prefix, $tokens );
		}
	}
}

/**
 * Verwijzingen {groep.naam} als CSS-variabele: var(--groep-naam).
 */
function powerhub_token_als_css( $waarde, $tokens ) {
	if ( is_bool( $waarde ) ) {
		return $waarde ? '1' : '0';
	}
	return preg_replace_callback( '/\{([^}]+)\}/', function ( $m ) use ( $tokens ) {
		return isset( $tokens[ $m[1] ] ) ? 'var(' . $tokens[ $m[1] ]['css'] . ')' : $m[0];
	}, (string) $waarde );
}

/**
 * Verwijzingen volledig oplossen tot de uiteindelijke waarde (voor weergave en Powerhub-standaarden).
 */
function powerhub_token_oplossen( $waarde, $tokens, $diepte = 0 ) {
	if ( $diepte > 10 || ! is_string( $waarde ) ) {
		return $waarde;
	}
	return preg_replace_callback( '/\{([^}]+)\}/', function ( $m ) use ( $tokens, $diepte ) {
		return isset( $tokens[ $m[1] ] ) ? (string) powerhub_token_oplossen( $tokens[ $m[1] ]['waarde'], $tokens, $diepte + 1 ) : $m[0];
	}, $waarde );
}

/**
 * De CSS-declaraties van één token. Tekststijlen geven meerdere regels.
 * Geeft array( 'desktop' => array( naam => waarde ), 'mobiel' => array( naam => waarde ) ).
 */
function powerhub_token_declaraties( $token, $tokens ) {
	$uit = array( 'desktop' => array(), 'mobiel' => array() );
	if ( 'typography' === $token['type'] && is_array( $token['waarde'] ) ) {
		$w = $token['waarde'];
		if ( isset( $w['fontSize'] ) ) {
			$uit['desktop'][ '--maat-' . $token['naam'] ] = powerhub_token_als_css( $w['fontSize'], $tokens );
		}
		if ( isset( $w['lineHeight'] ) ) {
			$uit['desktop'][ '--regel-' . $token['naam'] ] = powerhub_token_als_css( $w['lineHeight'], $tokens );
		}
		if ( null !== $token['mobiel'] ) {
			$uit['mobiel'][ '--maat-' . $token['naam'] ] = (string) $token['mobiel'];
		}
		return $uit;
	}
	$uit['desktop'][ $token['css'] ] = powerhub_token_als_css( $token['waarde'], $tokens );
	if ( null !== $token['mobiel'] ) {
		$uit['mobiel'][ $token['css'] ] = (string) $token['mobiel'];
	}
	return $uit;
}

/**
 * Complete tokens.css.
 */
function powerhub_tokens_css( $tokens, $bron = 'designsysteem/tokens.json' ) {
	$desktop = array();
	$mobiel  = array();
	$groep   = null;
	foreach ( $tokens as $token ) {
		$d = powerhub_token_declaraties( $token, $tokens );
		if ( $token['groep'] !== $groep ) {
			$groep     = $token['groep'];
			$desktop[] = '';
			$desktop[] = "\t/*Tokens -- " . ucfirst( $groep ) . '*/';
		}
		foreach ( $d['desktop'] as $naam => $waarde ) {
			$desktop[] = sprintf( "\t%-30s %s;", $naam . ':', $waarde );
		}
		foreach ( $d['mobiel'] as $naam => $waarde ) {
			$mobiel[] = sprintf( "\t\t%-30s %s;", $naam . ':', $waarde );
		}
	}
	$css  = "/**Designsysteem -- Tokens**/\n";
	$css .= "/* Gegenereerd uit {$bron} met: php powerhub/bin/tokens.php\n   Niet met de hand aanpassen: wijzig tokens.json en genereer opnieuw. */\n";
	$css .= ":root {\n" . ltrim( implode( "\n", $desktop ), "\n" ) . "\n}\n";
	if ( $mobiel ) {
		$css .= "\n/*Tokens -- Mobiel*/\n@media (max-width: 768px) {\n\t:root {\n" . implode( "\n", $mobiel ) . "\n\t}\n}\n";
	}
	return $css;
}
