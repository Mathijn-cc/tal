<?php
/**
 * Twee niveaus: de klant beheert inhoudelijke tabs (niveau klant), Creators Connect ook de techniek (niveau beheer).
 *
 * Beheerder is wie beheerrechten heeft én
 * - een e-mailadres op een domein uit de filter powerhub_beheer_domeinen (standaard creatorsconnect.nl), of
 * - in zijn profiel als Powerhub-beheerder is aangevinkt.
 */

defined( 'ABSPATH' ) || exit;

function powerhub_is_beheerder( $gebruiker = null ) {
	$gebruiker = $gebruiker ? ( is_object( $gebruiker ) ? $gebruiker : get_userdata( $gebruiker ) ) : wp_get_current_user();
	if ( ! $gebruiker || ! $gebruiker->exists() || ! user_can( $gebruiker, 'manage_options' ) ) {
		return false;
	}
	if ( get_user_meta( $gebruiker->ID, 'powerhub_beheerder', true ) ) {
		return true;
	}
	$domein   = strtolower( (string) substr( strrchr( (string) $gebruiker->user_email, '@' ), 1 ) );
	$domeinen = array_map( 'strtolower', (array) apply_filters( 'powerhub_beheer_domeinen', array( 'creatorsconnect.nl' ) ) );
	return in_array( $domein, $domeinen, true );
}

/**
 * Mag de huidige gebruiker deze tab zien en opslaan?
 */
function powerhub_mag_tab( $tab, $sleutel = '' ) {
	if ( powerhub_is_beheerder() ) {
		return true;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return false;
	}
	/* De beheerder kiest in de tab Toegang welke tabs de klant ziet. */
	if ( $sleutel && function_exists( 'powerhub_tab_voor_klant' ) ) {
		return powerhub_tab_voor_klant( $sleutel, $tab );
	}
	return 'klant' === $tab['niveau'];
}

/**
 * Vinkje in het gebruikersprofiel, alleen zichtbaar en aan te passen door een beheerder.
 */
function powerhub_profiel_veld( $gebruiker ) {
	if ( ! powerhub_is_beheerder() || ! user_can( $gebruiker, 'manage_options' ) ) {
		return;
	}
	?>
	<h2>Powerhub</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Beheerder</th>
			<td>
				<label>
					<input type="checkbox" name="powerhub_beheerder" value="1" <?php checked( (bool) get_user_meta( $gebruiker->ID, 'powerhub_beheerder', true ) ); ?>>
					Mag de technische tabs van Powerhub zien en aanpassen
				</label>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'powerhub_profiel_veld' );
add_action( 'edit_user_profile', 'powerhub_profiel_veld' );

function powerhub_profiel_opslaan( $gebruiker_id ) {
	if ( ! powerhub_is_beheerder() || ! current_user_can( 'edit_user', $gebruiker_id ) ) {
		return;
	}
	if ( ! empty( $_POST['powerhub_beheerder'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- WordPress controleert de nonce van het profielformulier.
		update_user_meta( $gebruiker_id, 'powerhub_beheerder', 1 );
	} else {
		delete_user_meta( $gebruiker_id, 'powerhub_beheerder' );
	}
}
add_action( 'personal_options_update', 'powerhub_profiel_opslaan' );
add_action( 'edit_user_profile_update', 'powerhub_profiel_opslaan' );
