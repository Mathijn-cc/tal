<?php
/**
 * Regels van het designsysteem. Wordt getoond op /designsysteem/.
 * Vul dit per project in: dit zijn de afspraken waar elke pagina, elk blok en elke tekst zich aan houdt.
 * Heeft de klant een eigen designsysteem? Zet het origineel in designsysteem/bron/ en noteer hier de afwijkingen.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="designsysteem-afwijking">
	<strong>Afwijkingen van het designsysteem van de klant</strong>
	<p>Nog geen. Noteer hier elke bewuste afwijking, met de datum en wie besliste.</p>
</div>

<h3>Kleur</h3>
<ul>
	<li>Welke kleuren dragen de structuur, welke is de stem van het merk, en waar mag de actiekleur wel en niet voorkomen.</li>
	<li>Gebruik in componenten de semantische tokens (<code>--kleur-vlak-*</code>, <code>--kleur-tekst-*</code>, <code>--kleur-actie-*</code>, <code>--kleur-rand-*</code>), niet het palet.</li>
</ul>

<h3>Secties</h3>
<ul>
	<li>Welke achtergronden wisselen elkaar af. Nooit meer dan twee tegelijk in beeld.</li>
</ul>

<h3>Typografie</h3>
<ul>
	<li>Letter en gewicht voor koppen, tekst, knoppen en bovenkoppen.</li>
	<li>Kopformule: een gewone zin, het slot in het cursieve accent.</li>
</ul>

<h3>Vorm</h3>
<ul>
	<li>Hoeken van knoppen, kaarten en panelen. Schaduwen en wanneer niet.</li>
</ul>

<h3>Beweging</h3>
<ul>
	<li>Duur en curve van overgangen. Wat er niet mag (bijvoorbeeld parallax of laadanimaties).</li>
</ul>

<h3>Iconen</h3>
<ul>
	<li>Lucide-lijniconen, lijndikte 2, lokaal in <code>assets/iconen</code> (<code>powerhub_icoon( 'naam' )</code>).</li>
</ul>

<h3>Content</h3>
<ul>
	<li>Aanspreekvorm (je of u), schrijfwijze van CTA's, hoofdlettergebruik, wat niet mag.</li>
</ul>
