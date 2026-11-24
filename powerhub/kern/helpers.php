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
 * Link naar Creators Connect voor de vermelding "Website door creatorsconnect.nl", altijd met UTM-codes,
 * zodat in de statistieken van creatorsconnect.nl te zien is vanaf welke klantsite iemand komt.
 * utm_source is het domein van deze site (zonder www), bijvoorbeeld tal-leadership.nl.
 */
function powerhub_maker_url( $plek = 'footer' ) {
	$domein = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$domein = preg_replace( '/^www\./', '', $domein );
	return add_query_arg( array(
		'utm_source'   => rawurlencode( $domein ),
		'utm_medium'   => 'referral',
		'utm_campaign' => 'website-door-creators-connect',
		'utm_content'  => rawurlencode( sanitize_key( $plek ) ),
	), 'https://creatorsconnect.nl/referral/websites/' );
}

/**
 * De schakel van Creators Connect als SVG: gevuld (het favicon) of als lijn.
 */
function powerhub_schakel( $soort = 'vol', $class = '' ) {
	$d = 'M262.4,524.8c-36.1,0-70.2-6.6-101.2-19.4-30.9-12.6-58.4-30.5-81.8-53.1l-1.1-1.1c-23.7-23.1-42.7-50.9-56.5-82.9C8,336.3,1,300.8,1,263v-1.8c.2-36.5,7.1-71,20.4-102.6l.6-1.5c13.2-31.2,31.6-58.6,54.7-81.4,23.3-23,51.1-41.4,82.6-54.6h.4C191.2,7.8,225.7,1.1,262.1,1.1h.5c37,0,71.8,6.7,103.5,19.9,31.7,13.3,59.4,31.9,82.4,55.2,23,23.7,41.1,51.5,53.8,82.9,4.4,11,8.2,22.7,11.3,34.8l.2.8h122.6l.2-.8c3.2-12.3,7.2-24.2,11.9-35.3l.6-1.5c13.6-31.4,32-58.8,54.8-81.4,23.2-23,51.1-41.4,82.9-54.8,32-13.2,66.5-19.9,102.7-19.9,37.2,0,72,6.8,103.6,20,31.7,13.1,59.4,31.7,82.4,55.3,22.9,23.3,41,51.1,53.8,82.6v.4c12.8,31.5,19.3,66.3,19.3,103.4v9.1c-.9,34.4-7.3,66.7-19.1,96l-.6,1.4c-13.3,32-31.6,59.7-54.5,82.3-23.2,22.9-50.8,41-82.2,53.7-31.8,13-66.3,19.5-102.5,19.5h-.6c-34.7,0-68.1-6.300-99.4-18.8l-1.5-.6c-31.6-13.1-59.5-31.3-82.9-54.2-23.2-22.6-42-50-55.8-81.4l-.7-1.5c-5.1-11.9-9.3-24.1-12.3-36.2l-.2-.8h-122.2l-.2.8c-3,12.8-7.1,25.4-12.1,37.4-13.3,32.1-31.6,59.7-54.4,82.2-22.8,22.6-50.5,40.7-82.3,53.7-31.3,12.8-65.3,19.3-100.9,19.5h-1.7Z M261.4,386.1c19.1,0,37-2.8,53.8-8.3,16.9-5.6,31.7-13.5,44.2-23.8,12.8-10.5,23-23.5,30.5-39,7.6-15.6,11.3-33.2,11.3-52.6,0-39.4-13.2-70-39.7-91.9-26.3-21.9-59.8-32.7-100.2-32.7-20.5.1-39.4,3.2-56.5,9.4s-31.8,15.1-44.2,26.3c-12.3,11.5-21.9,24.7-28.7,39.6-6.5,13.9-9.900,29.1-10.3,45.7v3.6c0,38.1,13.4,68.3,40.2,90.5,26.7,22.2,60,33.3,99.7,33.3h-.1Z M888.3,386.1c19.1,0,37-2.8,53.8-8.3,16.9-5.6,31.7-13.5,44.2-23.8,12.8-10.5,23-23.5,30.5-39,7.6-15.6,11.3-33.2,11.3-52.6,0-39.4-13.2-70-39.7-91.9-26.3-21.9-59.8-32.7-100.2-32.7-20.5.1-39.4,3.2-56.5,9.4s-31.8,15.1-44.2,26.3c-12.3,11.5-21.9,24.7-28.7,39.6-6.5,13.9-9.9,29.1-10.3,45.7v3.6c0,38.1,13.4,68.3,40.2,90.5,26.7,22.2,60,33.3,99.7,33.3h0Z';
	if ( 'lijn' === $soort ) {
		return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 1149.6 525.7" fill="none" aria-hidden="true" focusable="false"><path d="' . $d . '" stroke="currentColor" stroke-width="1.5" vector-effect="non-scaling-stroke"/></svg>';
	}
	return '<svg class="' . esc_attr( $class ) . '" viewBox="-125.2 -437.15 1400 1400" aria-hidden="true" focusable="false"><g transform="rotate(-45 574.8 262.85)"><path d="' . $d . '" fill="currentColor" fill-rule="evenodd"/></g></svg>';
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
