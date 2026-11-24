# Powerhub bijwerken in een klantproject

Dit bestand reist mee met elke versie van Powerhub. Lees het bij elke update, samen met `CHANGELOG.md`.

## Zo werk je bij

Verbeteringen aan Powerhub maak je alleen in het startthema (Mathijn-cc/cc-thema), nooit in een klantproject. In het klantproject:

1. Werk op een schone stand: alles gecommit, geen wijzigingen in `powerhub/`.
2. Haal de nieuwste Powerhub op, in de map van het klantthema:
   ```bash
   bash scripts/powerhub-bijwerken.sh            # nieuwste versie
   bash scripts/powerhub-bijwerken.sh v1.6.1     # of een vaste versie
   ```
   Het script vervangt alleen `powerhub/` en zichzelf. De projectcode in `inc/`, `assets/` en de templates blijft staan.
3. Lees `powerhub/CHANGELOG.md` vanaf de versie die het project had, en de overstapstappen hieronder voor elke versie die je passeert.
4. Controleer lokaal of op staging: homepage, `/designsysteem/` en de Powerhub-pagina in wp-admin.
5. Committen en pushen. De uitrol draait daarna vanzelf de uitroltaken (zie `kern/uitrol.php`).

Het script waarschuwt als het project nog lettertypes of een logo in het thema heeft. Die moeten eenmalig naar Powerhub, zie hieronder.

## Overstapstappen per versie

### Naar 1.4.0: vermelding "Website door creatorsconnect.nl"

De instelling `footer_maker` bestaat niet meer: de vermelding staat altijd in de footer, met UTM-codes.

- Gebruikt het project een eigen `footer.php`? Zet de link dan op `powerhub_maker_url()`:
  ```php
  <a href="<?php echo esc_url( powerhub_maker_url() ); ?>" target="_blank" rel="noopener">creatorsconnect.nl<span class="screen-reader-text"> (opent in een nieuw venster)</span></a>
  ```
- Haal `powerhub( 'footer_maker' )` overal weg.

### Naar 1.5.0 en 1.6.0: lettertypes uit het thema naar Powerhub

Lettertypes horen niet meer in het thema (`assets/fonts` met de filter `powerhub_fonts`). Ze staan in Powerhub, lokaal in `uploads/powerhub-lettertypes`, en zijn per rol te kiezen in **Powerhub › Design › Typografie**.

Bij een bestaand project moet je de lettertypes **eenmalig zelf overzetten**. Powerhub doet dat niet vanzelf, want het weet niet welk lettertype bij welke rol hoort. Kies een van twee manieren.

**A. Met de hand, via Powerhub (het eenvoudigst)**

1. Werk Powerhub bij, commit en rol uit. De oude lettertypes uit het thema blijven gewoon werken zolang de filter `powerhub_fonts` nog in het project staat.
2. Ga in wp-admin naar Powerhub › Design › Typografie en kies **Lettertype toevoegen**:
   - Staat het lettertype bij Google Fonts of Fontshare? Zoek het op en kies Toevoegen. De server haalt het één keer op en zet het lokaal.
   - Een eigen of betaald lettertype? Upload de .woff2-bestanden bij **Eigen lettertype uploaden**. Controleer of de licentie gebruik op de website toestaat.
3. Kies bij Koppen, Lopende tekst en Accent het nieuwe lettertype en sla op. Controleer de site.
4. Haal daarna in het project de filters `powerhub_fonts` en `powerhub_font_standaard` uit `inc/instellingen.php` weg en verwijder de lettertypebestanden uit `assets/fonts`. Een lettertype dat alleen in het logo staat (via `@font-face` in de eigen CSS) mag blijven.
5. Commit en rol uit.

Doe dit op **elke omgeving** (staging en live), want de lettertypes staan in de database en uploads van die omgeving. Of zet de database en uploads over.

**B. Met een uitroltaak, zonder klikken (handig bij staging én live)**

Zet in `inc/uitrol.php` van het project een taak die de bestanden uit het thema naar uploads kopieert en aan de rollen koppelt. Pas de namen, bestanden en gewichten aan:

```php
function project_uitrol_lettertypes() {
	if ( ! function_exists( 'powerhub_lettertype_overnemen' ) ) {
		return false;
	}
	$map   = get_template_directory() . '/assets/fonts/';
	$kop   = powerhub_lettertype_overnemen( 'google-dm-serif-display', 'DM Serif Display', 'Georgia, "Times New Roman", serif', 'google', array(
		array( 'pad' => $map . 'dm-serif-display-400.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
	) );
	$tekst = powerhub_lettertype_overnemen( 'fontshare-general-sans', 'General Sans', 'system-ui, sans-serif', 'fontshare', array(
		array( 'pad' => $map . 'general-sans-variabel.woff2', 'gewicht' => '200 700', 'stijl' => 'normal' ),
		array( 'pad' => $map . 'general-sans-variabel-cursief.woff2', 'gewicht' => '200 700', 'stijl' => 'italic' ),
	) );
	if ( ! $kop || ! $tekst ) {
		return false;
	}
	powerhub_lettertypes_rollen_zetten( array( 'kop' => $kop, 'accent' => $kop, 'tekst' => $tekst ) );
	return true;
}
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['lettertypes-naar-powerhub-v1'] = 'project_uitrol_lettertypes';
	return $taken;
} );
```

De sleutel begint met de bron: `google-`, `fontshare-` of `eigen-`. Haal in dezelfde commit de filters `powerhub_fonts` en `powerhub_font_standaard` weg. Laat de bestanden in `assets/fonts` nog staan tot de taak op **alle** omgevingen gedraaid heeft (zie de uitvoer van de uitrol: "Taak uitgevoerd: lettertypes-naar-powerhub-v1"). Haal ze pas daarna weg in een volgende commit.

Nieuwe projecten krijgen standaard Inter (filter `powerhub_lettertypes_standaard` in `inc/instellingen.php`). Bestaande projecten met al gekozen lettertypes houden die.

### Logo uit het thema naar Powerhub

Het logo hoort in Powerhub (**Algemeen › Logo** en **Logo op donker**), niet als bestand in het thema. `powerhub_logo()` toont het gekozen logo. Alleen als er niets gekozen is, valt het terug op de filter `powerhub_logo_terugval` (standaard de sitenaam als tekst).

Heeft een project het logo nog als bestand in het thema (bijvoorbeeld `assets/beelden/logo.svg` via `powerhub_logo_terugval` of hard in `header.php`)? Zet het dan eenmalig over:

**A. Met de hand**

1. Upload het logo in de mediabibliotheek. Gebruik een SVG als dat kan, anders een PNG op 3x of 4x de getoonde grootte.
2. Kies het in Powerhub bij Logo, en de lichte variant bij Logo op donker. Sla op.
3. Zorg dat `header.php` en `footer.php` `powerhub_logo()` gebruiken (`powerhub_logo( 'licht' )` op een donkere achtergrond).
4. Haal het logobestand en de filter `powerhub_logo_terugval` uit het project. De terugval naar de sitenaam blijft werken.
5. Doe stap 1 en 2 op elke omgeving, of zet de database en uploads over.

**B. Met een uitroltaak**

Zet het logo als bijlage in de mediabibliotheek en koppel het aan Powerhub, alleen als er nog niets gekozen is:

```php
function project_uitrol_logo() {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$opties = powerhub_opgeslagen();
	foreach ( array( 'logo' => 'logo.png', 'logo_licht' => 'logo-licht.png' ) as $veld => $bestand ) {
		$pad = get_template_directory() . '/assets/beelden/' . $bestand;
		if ( ! empty( $opties[ $veld ] ) || ! is_readable( $pad ) ) {
			continue;
		}
		$upload = wp_upload_bits( $bestand, null, file_get_contents( $pad ) );
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}
		$soort = wp_check_filetype( $bestand );
		$id    = wp_insert_attachment( array( 'post_mime_type' => $soort['type'], 'post_title' => get_bloginfo( 'name' ) . ' logo', 'post_status' => 'inherit' ), $upload['file'] );
		if ( ! $id || is_wp_error( $id ) ) {
			return false;
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		$opties[ $veld ] = $id;
	}
	update_option( POWERHUB_OPTIE, $opties, true );
	return true;
}
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['logo-naar-powerhub-v1'] = 'project_uitrol_logo';
	return $taken;
} );
```

Een SVG kan WordPress niet uploaden zonder een plugin die SVG toestaat (bijvoorbeeld Safe SVG). Gebruik dan PNG, of upload de SVG met de hand na het installeren van die plugin. Haal het logobestand uit het thema pas weg als de taak op alle omgevingen gedraaid heeft.

## Controle na het overstappen

- In de broncode van de voorkant staan de `@font-face`-regels met adressen in `/wp-content/uploads/powerhub-lettertypes/`, en geen verwijzingen naar Google of Fontshare.
- `--letter-kop`, `--letter-tekst` en `--letter-accent` hebben de juiste lettertypes (DevTools, `:root`).
- Het logo in de kop en de footer komt uit `/wp-content/uploads/`.
- `/designsysteem/` toont de juiste lettertypes en het logo.
