<?php
/**
 * Weergave van één veld in Powerhub, op basis van het type.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_veld_html( $naam, $veld ) {
	$id     = 'powerhub-' . $naam;
	$attr   = 'powerhub[' . $naam . ']';
	$waarde = powerhub( $naam, $veld['standaard'] );
	$uitleg = $veld['uitleg'] ? '<small class="powerhub-uitleg">' . esc_html( $veld['uitleg'] ) . '</small>' : '';

	if ( 'kop' === $veld['type'] ) {
		printf( '<h3 class="powerhub-tussenkop">%s</h3>%s', esc_html( $veld['label'] ), $uitleg ); // phpcs:ignore
		return;
	}

	if ( 'schakel' === $veld['type'] ) {
		printf(
			'<label class="powerhub-schakel" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s><span class="powerhub-schakel-knop" aria-hidden="true"></span><span class="powerhub-schakel-tekst"><strong>%4$s</strong>%5$s</span></label>',
			esc_attr( $id ),
			esc_attr( $attr ),
			checked( (bool) $waarde, true, false ),
			esc_html( $veld['label'] ),
			$uitleg // phpcs:ignore
		);
		return;
	}

	echo '<div class="powerhub-veld">';
	printf( '<label class="powerhub-label" for="%s">%s%s</label>', esc_attr( $id ), esc_html( $veld['label'] ), $uitleg ); // phpcs:ignore
	echo '<div class="powerhub-invoer">';

	switch ( $veld['type'] ) {
		case 'tekstvak':
			printf( '<textarea id="%s" name="%s" rows="4">%s</textarea>', esc_attr( $id ), esc_attr( $attr ), esc_textarea( $waarde ) );
			break;

		case 'keuze':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $attr ) );
			foreach ( $veld['keuzes'] as $sleutel => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $sleutel ), selected( (string) $waarde, (string) $sleutel, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'kleur':
			printf(
				'<span class="powerhub-kleur"><input type="color" id="%1$s" name="%2$s" value="%3$s" data-kleur><code data-kleur-code>%3$s</code></span>',
				esc_attr( $id ),
				esc_attr( $attr ),
				esc_attr( $waarde )
			);
			break;

		case 'beeld':
			$beeld_id = absint( $waarde );
			printf(
				'<div class="powerhub-beeld" data-beeld><div class="powerhub-beeld-voorbeeld" data-beeld-voorbeeld>%1$s</div><input type="hidden" id="%2$s" name="%3$s" value="%4$d"><button type="button" class="button" data-beeld-kies>Kiezen</button> <button type="button" class="button-link" data-beeld-wis>Wissen</button></div>',
				$beeld_id ? wp_get_attachment_image( $beeld_id, 'medium' ) : '',
				esc_attr( $id ),
				esc_attr( $attr ),
				$beeld_id
			);
			break;

		case 'getal':
			printf(
				'<input type="number" id="%s" name="%s" value="%s"%s%s%s>',
				esc_attr( $id ),
				esc_attr( $attr ),
				esc_attr( $waarde ),
				null !== $veld['min'] ? ' min="' . esc_attr( $veld['min'] ) . '"' : '',
				null !== $veld['max'] ? ' max="' . esc_attr( $veld['max'] ) . '"' : '',
				null !== $veld['stap'] ? ' step="' . esc_attr( $veld['stap'] ) . '"' : ''
			);
			break;

		default:
			$type = in_array( $veld['type'], array( 'url', 'email' ), true ) ? $veld['type'] : 'text';
			printf( '<input type="%s" id="%s" name="%s" value="%s">', esc_attr( $type ), esc_attr( $id ), esc_attr( $attr ), esc_attr( $waarde ) );
	}

	echo '</div></div>';
}
