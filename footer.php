</main>
<footer class="voet-site is-donker">
	<div class="binnen voet-site-rij">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( powerhub( 'footer_naam', 'TAL leadership' ) ); ?></span>
		<?php wp_nav_menu( array( 'theme_location' => 'juridisch', 'container' => 'nav', 'container_class' => 'voet-site-juridisch', 'container_aria_label' => 'Juridisch', 'fallback_cb' => false, 'depth' => 1 ) ); ?>
		<p class="voet-site-maker">Website: <a href="<?php echo esc_url( powerhub_maker_url() ); ?>" target="_blank" rel="noopener">Creators Connect<span class="screen-reader-text"> (opent in een nieuw venster)</span></a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
