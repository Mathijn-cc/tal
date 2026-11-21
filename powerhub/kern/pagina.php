<?php
/**
 * De Powerhub-pagina in wp-admin.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page(
		'Powerhub',
		'Powerhub',
		'edit_theme_options',
		'powerhub',
		'powerhub_pagina',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black"><path d="M13.5 2 4 13.5h6.5L9 22l11-12.5h-6.8z"/></svg>' ),
		3
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_powerhub' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'powerhub', POWERHUB_URL . '/assets/admin.css', array(), POWERHUB_VERSIE );
	wp_enqueue_script( 'powerhub', POWERHUB_URL . '/assets/admin.js', array( 'jquery' ), POWERHUB_VERSIE, true );
} );

function powerhub_meldingen() {
	return array(
		'opgeslagen' => array( 'succes', 'Opgeslagen.' ),
		'import'     => array( 'succes', 'De instellingen zijn geïmporteerd.' ),
		'reset'      => array( 'succes', 'Alle instellingen staan weer op de standaardwaarden.' ),
		'fout'       => array( 'fout', 'Dat lukte niet. Controleer het bestand en probeer het opnieuw.' ),
	);
}

function powerhub_pagina() {
	$tabs = array_filter( powerhub_tabs(), 'powerhub_mag_tab' );
	if ( ! $tabs ) {
		wp_die( 'Je hebt geen toegang tot Powerhub.', 403 );
	}

	$actief   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$actief   = isset( $tabs[ $actief ] ) ? $actief : array_key_first( $tabs );
	$melding  = isset( $_GET['melding'] ) ? sanitize_key( wp_unslash( $_GET['melding'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$meldingen = powerhub_meldingen();
	$thema    = wp_get_theme();
	?>
	<div class="wrap powerhub" data-powerhub data-actief="<?php echo esc_attr( $actief ); ?>">
		<h1 class="screen-reader-text">Powerhub</h1>

		<header class="powerhub-kop">
			<div class="powerhub-merk">
				<span class="powerhub-merk-icoon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M13.5 2 4 13.5h6.5L9 22l11-12.5h-6.8z"/></svg>
				</span>
				<div>
					<p class="powerhub-merk-titel">Powerhub</p>
					<p class="powerhub-merk-sub"><?php echo esc_html( $thema->get( 'Name' ) ); ?> <?php echo esc_html( defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : $thema->get( 'Version' ) ); ?> · Powerhub <?php echo esc_html( POWERHUB_VERSIE ); ?></p>
				</div>
			</div>
			<?php if ( powerhub_is_beheerder() ) : ?>
				<span class="powerhub-niveau">Beheerder</span>
			<?php endif; ?>
		</header>

		<?php if ( isset( $meldingen[ $melding ] ) ) : ?>
			<div class="powerhub-melding powerhub-melding-<?php echo esc_attr( $meldingen[ $melding ][0] ); ?>" role="status"><?php echo esc_html( $meldingen[ $melding ][1] ); ?></div>
		<?php endif; ?>

		<div class="powerhub-indeling">
			<nav class="powerhub-tabs" role="tablist" aria-label="Onderdelen">
				<?php foreach ( $tabs as $sleutel => $tab ) : ?>
					<button type="button" role="tab" id="powerhub-tab-<?php echo esc_attr( $sleutel ); ?>" aria-controls="powerhub-paneel-<?php echo esc_attr( $sleutel ); ?>" data-tab="<?php echo esc_attr( $sleutel ); ?>">
						<span class="powerhub-tab-titel">
							<?php echo esc_html( $tab['titel'] ); ?>
							<?php if ( 'beheer' === $tab['niveau'] ) : ?>
								<span class="powerhub-slot" title="Alleen voor beheerders">beheer</span>
							<?php endif; ?>
						</span>
						<?php if ( $tab['uitleg'] ) : ?>
							<small><?php echo esc_html( $tab['uitleg'] ); ?></small>
						<?php endif; ?>
					</button>
				<?php endforeach; ?>
			</nav>

			<div class="powerhub-panelen">
				<?php foreach ( $tabs as $sleutel => $tab ) : ?>
					<section class="powerhub-paneel" role="tabpanel" id="powerhub-paneel-<?php echo esc_attr( $sleutel ); ?>" aria-labelledby="powerhub-tab-<?php echo esc_attr( $sleutel ); ?>" data-paneel="<?php echo esc_attr( $sleutel ); ?>">
						<header class="powerhub-paneel-kop">
							<h2><?php echo esc_html( $tab['titel'] ); ?></h2>
							<?php if ( $tab['uitleg'] ) : ?>
								<p><?php echo esc_html( $tab['uitleg'] ); ?></p>
							<?php endif; ?>
						</header>

						<?php if ( $tab['velden'] ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="powerhub-formulier">
								<input type="hidden" name="action" value="powerhub_opslaan">
								<input type="hidden" name="powerhub_tab" value="<?php echo esc_attr( $sleutel ); ?>">
								<?php wp_nonce_field( 'powerhub_opslaan_' . $sleutel ); ?>
								<?php
								foreach ( $tab['velden'] as $naam => $veld ) {
									powerhub_veld_html( $naam, $veld );
								}
								?>
								<div class="powerhub-acties">
									<button type="submit" class="button button-primary">Opslaan</button>
								</div>
							</form>
						<?php endif; ?>

						<?php
						if ( is_callable( $tab['weergave'] ) ) {
							call_user_func( $tab['weergave'] );
						}
						?>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
