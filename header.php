<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="naar-inhoud" href="#inhoud">Naar de inhoud</a>
<header class="kop-site">
	<div class="kop-site-binnen">
		<a class="kop-site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, naar de homepage">
			<?php echo powerhub_logo( 'licht' ); // phpcs:ignore ?>
		</a>
		<?php
		wp_nav_menu( array(
			'theme_location'       => 'hoofdmenu',
			'container'            => 'nav',
			'container_class'      => 'kop-site-menu',
			'container_aria_label' => 'Hoofdmenu',
			'fallback_cb'          => false,
			'depth'                => 1,
		) );
		if ( powerhub_aan( 'taalwissel' ) ) :
			?>
			<span class="kop-site-taal">NL <?php echo powerhub_icoon( 'chevron-down' ); // phpcs:ignore ?></span>
			<?php
		endif;
		echo powerhub_knop( powerhub_hoofdknop(), 'actie knop-klein' ); // phpcs:ignore
		?>
	</div>
</header>
<main id="inhoud">
<?php powerhub_kruimelpad(); ?>
