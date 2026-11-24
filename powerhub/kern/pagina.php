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
	wp_enqueue_style( 'powerhub', POWERHUB_URL . '/assets/admin.css', array(), powerhub_versie( '/assets/admin.css' ) );
	wp_enqueue_script( 'powerhub', POWERHUB_URL . '/assets/admin.js', array( 'jquery' ), powerhub_versie( '/assets/admin.js' ), true );
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
	$tabs = array_filter( powerhub_tabs(), 'powerhub_mag_tab', ARRAY_FILTER_USE_BOTH );
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
			<?php echo powerhub_schakel( 'lijn', 'powerhub-kop-schakel' ); // phpcs:ignore ?>
			<div class="powerhub-merk">
				<span class="powerhub-merk-icoon" aria-hidden="true"><?php echo powerhub_schakel( 'vol' ); // phpcs:ignore ?></span>
				<div>
					<p class="powerhub-merk-titel">Powerhub</p>
					<p class="powerhub-merk-sub"><?php echo esc_html( $thema->get( 'Name' ) ); ?> <?php echo esc_html( defined( 'THEMA_VERSIE' ) ? THEMA_VERSIE : $thema->get( 'Version' ) ); ?> · Powerhub <?php echo esc_html( POWERHUB_VERSIE ); ?></p>
				</div>
			</div>
			<?php if ( powerhub_is_beheerder() ) : ?>
				<span class="powerhub-niveau">
					<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
					Beheerder
				</span>
			<?php endif; ?>
		</header>

		<?php if ( isset( $meldingen[ $melding ] ) ) : ?>
			<div class="powerhub-melding powerhub-melding-<?php echo esc_attr( $meldingen[ $melding ][0] ); ?>" role="status"><?php echo esc_html( $meldingen[ $melding ][1] ); ?></div>
		<?php endif; ?>

		<div class="powerhub-indeling">
			<?php
			/* Tabs in groepen (submenu): array( 'design' => array( 'titel', 'uitleg', 'tabs' => array( sleutels ) ) ). */
			$groepen  = (array) apply_filters( 'powerhub_tabgroepen', array(
				'design' => array(
					'titel'  => 'Design',
					'uitleg' => 'Kleuren, typografie en designsysteem',
					'tabs'   => array( 'kleuren', 'typografie', 'designsysteem' ),
				),
			) );
			$in_groep = array();
			foreach ( $groepen as $g => $groep ) {
				$groepen[ $g ]['tabs'] = array_values( array_filter( (array) $groep['tabs'], function ( $t ) use ( $tabs ) {
					return isset( $tabs[ $t ] );
				} ) );
				foreach ( $groepen[ $g ]['tabs'] as $t ) {
					$in_groep[ $t ] = $g;
				}
			}
			$getoond = array();
			$tab_knop = function ( $sleutel, $tab, $sub = false ) {
				?>
				<button type="button" role="tab" class="<?php echo $sub ? 'powerhub-subtab' : ''; ?>" id="powerhub-tab-<?php echo esc_attr( $sleutel ); ?>" aria-controls="powerhub-paneel-<?php echo esc_attr( $sleutel ); ?>" data-tab="<?php echo esc_attr( $sleutel ); ?>">
					<span class="powerhub-tab-titel">
						<?php echo esc_html( $tab['titel'] ); ?>
						<?php if ( powerhub_is_beheerder() && function_exists( 'powerhub_tab_voor_klant' ) && ! powerhub_tab_voor_klant( $sleutel, $tab ) ) : ?>
							<span class="powerhub-slot" title="Alleen zichtbaar voor beheerders. Aan te passen in de tab Toegang.">
								<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
								<span class="screen-reader-text">Alleen voor beheerders</span>
							</span>
						<?php endif; ?>
					</span>
				</button>
				<?php
			};
			?>
			<nav class="powerhub-tabs" role="tablist" aria-label="Onderdelen">
				<?php foreach ( $tabs as $sleutel => $tab ) : ?>
					<?php if ( isset( $in_groep[ $sleutel ] ) ) : ?>
						<?php
						$g = $in_groep[ $sleutel ];
						if ( isset( $getoond[ $g ] ) ) {
							continue;
						}
						$getoond[ $g ] = true;
						$open          = in_array( $actief, $groepen[ $g ]['tabs'], true );
						?>
						<div class="powerhub-groep<?php echo $open ? ' is-open' : ''; ?>" data-groep>
							<button type="button" class="powerhub-groep-knop" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="powerhub-groep-<?php echo esc_attr( $g ); ?>">
								<span class="powerhub-tab-titel">
									<?php echo esc_html( $groepen[ $g ]['titel'] ); ?>
									<svg class="powerhub-groep-pijl" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
								</span>
							</button>
							<div class="powerhub-groep-tabs" id="powerhub-groep-<?php echo esc_attr( $g ); ?>"<?php echo $open ? '' : ' hidden'; ?>>
								<?php foreach ( $groepen[ $g ]['tabs'] as $t ) : ?>
									<?php $tab_knop( $t, $tabs[ $t ], true ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php else : ?>
						<?php $tab_knop( $sleutel, $tab ); ?>
					<?php endif; ?>
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

/* Het menu-item Powerhub in de zijbalk van WordPress in de kleuren van Creators Connect, op elke beheerpagina. */
add_action( 'admin_head', function () {
	echo '<style id="powerhub-menu">'
		. '#adminmenu #toplevel_page_powerhub.current > a.menu-top,#adminmenu #toplevel_page_powerhub.wp-has-current-submenu > a.menu-top{background:#11281d;color:#fdfff2}'
		. '#adminmenu #toplevel_page_powerhub.current > a.menu-top .wp-menu-image:before,#adminmenu #toplevel_page_powerhub.wp-has-current-submenu > a.menu-top .wp-menu-image:before{color:#e7fb79}'
		. '#adminmenu #toplevel_page_powerhub:not(.current):not(.wp-has-current-submenu) > a.menu-top:hover .wp-menu-image:before{color:#e7fb79}'
		. '</style>';
} );
