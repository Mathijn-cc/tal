<?php
/**
 * Citaatkaart
 * Kop, citaat in het cursieve accent en de afzender. Alleen echte referenties gebruiken.
 */
?>
<div class="raster raster-2" style="max-width: 760px">
	<figure class="kaart kaart-citaat" style="margin: 0">
		<p class="kaart-citaat-kop">Korte kop.</p>
		<blockquote>Een citaat van een echte klant, in één of twee zinnen.</blockquote>
		<figcaption class="kaart-citaat-afzender">
			<span class="tegel tegel-klein tegel-donker"><?php echo powerhub_icoon( 'users-round' ); // phpcs:ignore ?></span>
			<span><span class="kaart-citaat-naam">Naam</span><br><span class="kaart-citaat-rol">Functie, organisatie</span></span>
		</figcaption>
	</figure>
</div>
