<?php
/**
 * Module Gereedschap: systeemoverzicht, export, import en reset.
 */

defined( 'ABSPATH' ) || exit;

powerhub_tab( 'gereedschap', array(
	'titel'    => 'Gereedschap',
	'uitleg'   => 'Systeem, export en herstel',
	'niveau'   => 'beheer',
	'volgorde' => 90,
	'weergave' => 'powerhub_gereedschap_weergave',
) );

function powerhub_systeem_regels() {
	$thema = wp_get_theme();
	$acf   = defined( 'ACF_PRO' ) ? 'ACF Pro ' . ( defined( 'ACF_VERSION' ) ? ACF_VERSION : '' ) : ( function_exists( 'acf' ) ? 'ACF (zonder Pro)' : false );
	$rank  = defined( 'RANK_MATH_VERSION' ) ? 'Rank Math ' . RANK_MATH_VERSION : false;
	$taken = (array) get_option( 'powerhub_uitroltaken', array() );

	$tokens_status = array( 'Geen designsysteem', false );
	if ( function_exists( 'powerhub_tokens' ) && powerhub_tokens() ) {
		$tokens_css    = get_template_directory() . '/assets/css/tokens.css';
		$klopt         = is_readable( $tokens_css ) && file_get_contents( $tokens_css ) === powerhub_tokens_css( powerhub_tokens() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$tokens_status = array( count( powerhub_tokens() ) . ' tokens, tokens.css ' . ( $klopt ? 'klopt' : 'loopt achter: draai php powerhub/bin/tokens.php' ), $klopt );
	}

	return array(
		'Designsysteem'          => $tokens_status,
		'Thema'                  => array( $thema->get( 'Name' ) . ' ' . ( defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : $thema->get( 'Version' ) ), true ),
		'Powerhub'               => array( POWERHUB_VERSIE, true ),
		'WordPress'              => array( get_bloginfo( 'version' ), true ),
		'PHP'                    => array( PHP_VERSION, version_compare( PHP_VERSION, '8.1', '>=' ) ),
		'ACF'                    => array( $acf ?: 'Niet actief', defined( 'ACF_PRO' ) ),
		'Rank Math'              => array( $rank ?: 'Niet actief', (bool) $rank ),
		'Omgeving'               => array( powerhub_is_staging() ? 'Staging' : 'Live', true ),
		'Zoekmachines'           => array( get_option( 'blog_public' ) ? 'Zichtbaar' : 'Uitgesloten', powerhub_is_staging() !== (bool) get_option( 'blog_public' ) ),
		'Uitgevoerde uitroltaken' => array( $taken ? implode( ', ', $taken ) : 'Geen', true ),
	);
}

function powerhub_gereedschap_weergave() {
	$post = esc_url( admin_url( 'admin-post.php' ) );
	?>
	<div class="powerhub-gereedschap">
		<div class="powerhub-kaart">
			<h3>Exporteren</h3>
			<p>Alle Powerhub-instellingen als JSON-bestand, als back-up of om naar een andere omgeving te zetten.</p>
			<form method="post" action="<?php echo $post; // phpcs:ignore ?>">
				<input type="hidden" name="action" value="powerhub_export">
				<?php wp_nonce_field( 'powerhub_export' ); ?>
				<button type="submit" class="button">Bestand downloaden</button>
			</form>
		</div>

		<div class="powerhub-kaart">
			<h3>Importeren</h3>
			<p>Zet een export terug. Logo's verwijzen naar de mediabibliotheek en kloppen alleen op dezelfde site.</p>
			<form method="post" action="<?php echo $post; // phpcs:ignore ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="powerhub_import">
				<?php wp_nonce_field( 'powerhub_import' ); ?>
				<input type="file" name="powerhub_bestand" accept="application/json,.json" required>
				<p><button type="submit" class="button">Importeren</button></p>
			</form>
		</div>

		<div class="powerhub-kaart powerhub-kaart-gevaar">
			<h3>Standaardwaarden</h3>
			<p>Zet alle Powerhub-instellingen terug. Maak eerst een export.</p>
			<form method="post" action="<?php echo $post; // phpcs:ignore ?>" data-bevestig="Alle Powerhub-instellingen terugzetten naar de standaard?">
				<input type="hidden" name="action" value="powerhub_reset">
				<?php wp_nonce_field( 'powerhub_reset' ); ?>
				<button type="submit" class="button button-link-delete">Alles terugzetten</button>
			</form>
		</div>
	</div>

	<table class="powerhub-systeem">
		<tbody>
			<?php foreach ( powerhub_systeem_regels() as $label => $regel ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $label ); ?></th>
					<td class="<?php echo $regel[1] ? 'powerhub-status-goed' : 'powerhub-status-fout'; ?>"><?php echo esc_html( $regel[0] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
