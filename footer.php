</main>
<footer class="voet-site">
	<div class="voet-site-hoofd">
		<div>
			<?php
			$logo = powerhub_logo( 'licht', 'voet-site-logo' );
			echo str_replace( 'class="logo"', 'class="logo voet-site-logo"', $logo ); // phpcs:ignore
			?>
			<?php if ( powerhub( 'footer_tekst' ) ) : ?>
				<p class="voet-site-slogan"><?php echo esc_html( powerhub( 'footer_tekst' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php
		/* Kolommen uit de footermenu's. De naam van het menu is de kop van de kolom. */
		foreach ( array( 'footer_1', 'footer_2' ) as $plek ) :
			$locaties = get_nav_menu_locations();
			if ( empty( $locaties[ $plek ] ) ) {
				continue;
			}
			$menu = wp_get_nav_menu_object( $locaties[ $plek ] );
			?>
			<div>
				<p class="voet-site-kop"><?php echo esc_html( $menu ? $menu->name : '' ); ?></p>
				<?php wp_nav_menu( array( 'theme_location' => $plek, 'container' => 'nav', 'container_class' => 'voet-site-links', 'fallback_cb' => false, 'depth' => 1 ) ); ?>
			</div>
		<?php endforeach; ?>

		<div>
			<p class="voet-site-kop">Contact</p>
			<div class="voet-site-links">
				<?php if ( powerhub( 'email' ) ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( powerhub( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( powerhub( 'email' ) ) ); ?></a>
				<?php endif; ?>
				<?php if ( powerhub( 'telefoon' ) ) : ?>
					<a href="<?php echo esc_url( powerhub_telefoon_link() ); ?>"><?php echo esc_html( powerhub( 'telefoon' ) ); ?></a>
				<?php endif; ?>
				<?php if ( powerhub( 'adres' ) ) : ?>
					<span><?php echo nl2br( esc_html( powerhub( 'adres' ) ) ); ?></span>
				<?php endif; ?>
				<?php foreach ( powerhub_social() as $kanaal ) : ?>
					<a href="<?php echo esc_url( $kanaal['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $kanaal['naam'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="voet-site-juridisch">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<?php wp_nav_menu( array( 'theme_location' => 'juridisch', 'container' => false, 'fallback_cb' => false, 'depth' => 1 ) ); ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
