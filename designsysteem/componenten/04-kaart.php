<?php
/**
 * Kaart
 * Wit vlak met hoek 20px en een zachte schaduw. Varianten: zweef (tilt 2px bij hover), tint, donker (op donker, zonder schaduw) en uitgelicht (rand in de actiekleur, maximaal één per groep).
 */
?>
<div class="raster raster-3">
	<div class="kaart kaart-zweef"><h4>Kaart</h4><p class="tekst-klein" style="margin-top: 8px">Standaard kaart, tilt bij hover.</p></div>
	<div class="kaart kaart-tint"><h4>Tint</h4><p class="tekst-klein" style="margin-top: 8px">Zachte tint, geen schaduw.</p></div>
	<div class="kaart kaart-uitgelicht"><h4>Uitgelicht</h4><p class="tekst-klein" style="margin-top: 8px">rand in de actiekleur, één per groep.</p></div>
</div>
<div class="achtergrond-donker raster raster-2" style="margin-top: var(--ruimte-6); padding: var(--ruimte-8); border-radius: var(--hoek-l)">
	<div class="kaart kaart-donker"><h4>Donker</h4><p class="tekst-klein" style="margin-top: 8px">Lichter donker op donker, geen schaduw.</p></div>
	<div class="kaart kaart-donker kaart-uitgelicht"><h4>Donker, uitgelicht</h4><p class="tekst-klein" style="margin-top: 8px">Met rand in de actiekleur.</p></div>
</div>
