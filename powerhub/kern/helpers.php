<?php
/**
 * Hulpfuncties voor templates: koppen met accent, knoppen, logo, sectie-classes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * De stijl waar Powerhub zijn CSS aan hangt. Het thema laadt zijn hoofdstijl als 'thema'.
 */
function powerhub_stijl_handle() {
	return (string) apply_filters( 'powerhub_stijl_handle', 'thema' );
}

/**
 * Kop met accentwoorden: *slim geregeld* wordt het cursieve accent, &lt;br&gt; een nieuwe regel.
 */
function powerhub_kop( $tekst, $tag = 'h2', $class = '' ) {
	if ( '' === trim( (string) $tekst ) ) {
		return '';
	}
	$html = esc_html( $tekst );
	$html = preg_replace( '/\*(.+?)\*/u', '<em class="accent">$1</em>', $html );
	$html = str_replace( '&lt;br&gt;', '<br>', $html );
	return sprintf( '<%1$s class="%2$s">%3$s</%1$s>', tag_escape( $tag ), esc_attr( trim( 'kop ' . $class ) ), $html );
}

/**
 * Knop vanuit een ACF-linkveld of array( 'url' => ..., 'title' => ... ).
 */
function powerhub_knop( $link, $stijl = 'actie' ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	$pijl = '<svg class="knop-pijl" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	return sprintf(
		'<a class="knop knop-%1$s" href="%2$s"%3$s><span>%4$s</span>%5$s</a>',
		esc_attr( $stijl ),
		esc_url( $link['url'] ),
		! empty( $link['target'] ) ? ' target="_blank" rel="noopener"' : '',
		esc_html( ! empty( $link['title'] ) ? $link['title'] : 'Lees meer' ),
		apply_filters( 'powerhub_knop_pijl', $pijl )
	);
}

/**
 * Logo uit Powerhub. Variant 'licht' is het logo voor een donkere achtergrond.
 */
function powerhub_logo( $variant = 'standaard', $class = '' ) {
	$id = 'licht' === $variant ? (int) powerhub( 'logo_licht' ) : 0;
	$id = $id ?: (int) powerhub( 'logo' );
	if ( ! $id ) {
		$terugval = '<span class="' . esc_attr( trim( 'logo-tekst ' . $class ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		/* Het thema kan een eigen logobestand leveren zolang er in Powerhub niets is gekozen. */
		return (string) apply_filters( 'powerhub_logo_terugval', $terugval, $variant );
	}
	/* Volledig formaat: een logo is klein, en een verkleinde kopie wordt onscherp op een scherm met hoge resolutie. */
	return wp_get_attachment_image( $id, 'full', false, array(
		'class'    => trim( 'logo ' . $class ),
		'alt'      => get_bloginfo( 'name' ),
		'loading'  => 'eager',
		'decoding' => 'async',
	) );
}


/**
 * Icoon als inline SVG uit assets/iconen/<naam>.svg van het thema (Lucide-lijniconen).
 * Geeft een lege string als het icoon niet bestaat.
 */
function powerhub_icoon( $naam, $class = '' ) {
	static $cache = array();
	$naam = sanitize_file_name( $naam );
	if ( ! array_key_exists( $naam, $cache ) ) {
		$bestand        = get_template_directory() . '/assets/iconen/' . $naam . '.svg';
		$cache[ $naam ] = is_readable( $bestand ) ? trim( (string) file_get_contents( $bestand ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( '' === $cache[ $naam ] ) {
		return '';
	}
	$svg = preg_replace( '/<!--.*?-->/s', '', $cache[ $naam ] );
	$svg = preg_replace( '/\sclass="[^"]*"/', '', $svg, 1 );
	return preg_replace( '/<svg\b/', '<svg class="' . esc_attr( trim( 'icoon icoon-' . $naam . ' ' . $class ) ) . '" aria-hidden="true" focusable="false"', $svg, 1 );
}

/**
 * Beeld uit een media-ID (uit ACF) of een URL (voorbeeldinhoud). Alt-tekst komt uit de mediabibliotheek;
 * geef 'alt' => '' mee voor puur decoratieve beelden.
 */
function powerhub_beeld( $waarde, $maat = 'large', $attr = array() ) {
	if ( empty( $waarde ) ) {
		return '';
	}
	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( is_numeric( $waarde ) ) {
		return wp_get_attachment_image( (int) $waarde, $maat, false, $attr );
	}
	if ( is_array( $waarde ) && isset( $waarde['ID'] ) ) {
		return wp_get_attachment_image( (int) $waarde['ID'], $maat, false, $attr );
	}
	$html = '<img src="' . esc_url( (string) $waarde ) . '"';
	foreach ( $attr as $naam => $inhoud ) {
		$html .= ' ' . esc_attr( $naam ) . '="' . esc_attr( $inhoud ) . '"';
	}
	if ( ! isset( $attr['alt'] ) ) {
		$html .= ' alt=""';
	}
	return $html . '>';
}
