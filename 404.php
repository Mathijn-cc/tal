<?php
/**
 * Pagina niet gevonden. Titel, tekst en knoptekst staan in Powerhub > 404 (inc/instellingen.php).
 */
get_header();
?>
<section class="sectie sectie-niet-gevonden achtergrond-pagina">
	<div class="rij">
		<p class="niet-gevonden-code" aria-hidden="true">404</p>
		<div class="niet-gevonden-tekst">
			<p class="bovenkop-tal">Pagina niet gevonden</p>
			<?php echo tal_kop( tal_404( 'niet_gevonden_titel' ), 'h1' ); // phpcs:ignore ?>
			<p class="lead"><?php echo nl2br( esc_html( tal_404( 'niet_gevonden_tekst' ) ) ); // phpcs:ignore ?></p>
			<div class="knoppen">
				<?php echo powerhub_knop( array( 'url' => home_url( '/' ), 'title' => tal_404( 'niet_gevonden_knop' ) ), 'actie' ); // phpcs:ignore ?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
