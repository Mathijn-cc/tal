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
					<?php if ( ! $sub ) : ?>
						<span class="powerhub-tab-icoon" aria-hidden="true"><?php echo powerhub_tab_icoon( $sleutel, $tab ); // phpcs:ignore ?></span>
					<?php endif; ?>
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
								<span class="powerhub-tab-icoon" aria-hidden="true"><?php echo powerhub_tab_icoon( $g, $groepen[ $g ] ); // phpcs:ignore ?></span>
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

/**
 * Icoon voor een tab of groep in de zijbalk: 'icoon' in powerhub_tab() of de groep, anders de vaste lijst hieronder,
 * anders schuifjes. Eigen iconen via de filter powerhub_tab_iconen: array( 'sleutel' => '<path .../>' ).
 * Lijnen in 24 bij 24, zoals de rest van de iconen in Powerhub.
 */
function powerhub_tab_icoon( $sleutel, $tab = array() ) {
	$iconen = (array) apply_filters( 'powerhub_tab_iconen', array(
		'algemeen'         => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
		'design'           => '<path d="M12 3a9 9 0 1 0 0 18c1.1 0 1.8-.8 1.8-1.8 0-.5-.2-.9-.5-1.3-.3-.3-.5-.7-.5-1.2 0-1 .8-1.7 1.8-1.7H17a4 4 0 0 0 4-4c0-4.4-4-8-9-8z"/><circle cx="7.5" cy="11.5" r="1"/><circle cx="10.5" cy="7.5" r="1"/><circle cx="15.5" cy="8.5" r="1"/>',
		'footer'           => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 15h18"/>',
		'animaties'        => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/>',
		'seo'              => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'blog'             => '<path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
		'beveiliging'      => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'wpml'             => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'toegankelijkheid' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
		'gereedschap'      => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>',
		'toegang'          => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
		'niet-gevonden'    => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6"/><path d="M12 17.5h.01"/>',
	) );
	$standaard = '<path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/><circle cx="15" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="17" cy="18" r="2"/>';
	$sleutel   = ! empty( $tab['icoon'] ) ? $tab['icoon'] : $sleutel;
	$pad       = $iconen[ $sleutel ] ?? $standaard;
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $pad . '</svg>';
}
