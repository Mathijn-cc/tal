<footer class="voet-site is-donker">
	<div class="binnen voet-site-rij">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( powerhub( 'footer_naam', 'TAL leadership' ) ); ?></span>
		<?php wp_nav_menu( array( 'theme_location' => 'juridisch', 'container' => 'nav', 'container_class' => 'voet-site-juridisch', 'container_aria_label' => 'Juridisch', 'fallback_cb' => false, 'depth' => 1 ) ); ?>
		<p class="voet-site-maker">Website: <a href="https://creatorsconnect.nl/referral/websites/">Creators Connect</a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
