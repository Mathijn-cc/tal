<?php
/**
 * Accordeon
 * Gebouwd op details en summary, één tegelijk open. Genummerd voor stappen; Vragen (donkere kaarten) voor veelgestelde vragen op een donkere sectie.
 */
?>
<div class="accordeon accordeon-genummerd" style="max-width: 640px">
	<details open><summary>Eerste stap</summary><div class="accordeon-tekst">Uitleg bij de stap.</div></details>
	<details><summary>Tweede stap</summary><div class="accordeon-tekst">Uitleg bij de stap.</div></details>
</div>
<div class="achtergrond-donker" style="margin-top: var(--ruimte-8); padding: var(--ruimte-8); border-radius: var(--hoek-l)">
	<div class="accordeon accordeon-vragen">
		<details open><summary>Een veelgestelde vraag</summary><div class="accordeon-tekst">Het antwoord.</div></details>
		<details><summary>Nog een vraag</summary><div class="accordeon-tekst">Het antwoord.</div></details>
	</div>
</div>
