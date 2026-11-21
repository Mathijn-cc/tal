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
<div class="korrel" aria-hidden="true"></div>
<?php
$tal_menu = wp_nav_menu( array(
	'theme_location' => 'hoofdmenu',
	'container'      => false,
	'items_wrap'     => '%3$s',
	'fallback_cb'    => false,
	'depth'          => 1,
	'echo'           => false,
) );
$tal_knop = powerhub_hoofdknop();
?>
<header class="kop-site">
	<div class="kop-site-binnen">
		<a class="kop-site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, naar de homepage">
			<?php echo powerhub_logo(); // phpcs:ignore ?>
		</a>
		<div class="kop-site-rechts">
			<?php if ( $tal_menu ) : ?>
				<nav class="kop-site-menu" aria-label="Hoofdmenu">
					<ul><?php echo $tal_menu; // phpcs:ignore ?></ul>
				</nav>
			<?php endif; ?>
			<?php echo powerhub_knop( $tal_knop, 'actie knop-klein kop-site-knop' ); // phpcs:ignore ?>
			<button class="menuknop" type="button" aria-label="Menu openen" aria-expanded="false" aria-controls="menupaneel"><span></span><span></span></button>
		</div>
	</div>
</header>

<div class="menupaneel" id="menupaneel" role="dialog" aria-modal="true" aria-label="Menu" hidden>
	<div class="menupaneel-kop">
		<a class="kop-site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, naar de homepage">
			<?php echo powerhub_logo(); // phpcs:ignore ?>
		</a>
		<button class="sluitknop" type="button" aria-label="Menu sluiten">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M5 5l14 14M19 5L5 19"/></svg>
		</button>
	</div>
	<?php if ( $tal_menu ) : ?>
		<nav class="menupaneel-menu" aria-label="Mobiel menu">
			<ul><?php echo $tal_menu; // phpcs:ignore ?></ul>
		</nav>
	<?php endif; ?>
	<div class="menupaneel-voet">
		<?php echo powerhub_knop( $tal_knop, 'actie' ); // phpcs:ignore ?>
		<?php if ( powerhub( 'email' ) ) : ?>
			<a class="lijnlink" href="mailto:<?php echo esc_attr( antispambot( powerhub( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( powerhub( 'email' ) ) ); ?></a>
		<?php endif; ?>
	</div>
</div>
