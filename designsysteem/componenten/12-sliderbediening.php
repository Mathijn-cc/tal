<?php
/**
 * Sliderbediening
 * Voortgangsstreepjes (actief in de actiekleur) en ronde pijlknoppen.
 */
?>
<div class="slider-bediening" style="max-width: 520px">
	<div class="slider-voortgang"><span class="is-actief"></span><span></span><span></span><span></span></div>
	<div class="slider-pijlen">
		<button class="ronde-knop" aria-label="Vorige"><?php echo powerhub_icoon( 'arrow-left' ); // phpcs:ignore ?></button>
		<button class="ronde-knop" aria-label="Volgende"><?php echo powerhub_icoon( 'arrow-right' ); // phpcs:ignore ?></button>
	</div>
</div>
