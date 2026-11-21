<?php
/**
 * Secties en achtergronden
 * De achtergronden uit inc/instellingen.php. Nooit meer dan twee tegelijk in beeld.
 */
?>
<div class="raster" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr))">
	<?php foreach ( (array) apply_filters( 'powerhub_achtergronden', array() ) as $sleutel => $naam ) : ?>
		<div class="achtergrond-<?php echo esc_attr( $sleutel ); ?>" style="padding: var(--ruimte-6); border: 1px solid var(--kleur-rand-subtiel); border-radius: var(--hoek-m)">
			<p class="bovenkop" style="margin: 0 0 8px"><?php echo esc_html( $naam ); ?></p>
			<code style="font-size: 12px">.achtergrond-<?php echo esc_html( $sleutel ); ?></code>
		</div>
	<?php endforeach; ?>
</div>
