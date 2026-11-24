# Powerhub, wijzigingen

Versienummers: hoofdversie (eerste cijfer) als projecten iets moeten aanpassen, tussenversie voor nieuwe functies, laatste cijfer voor reparaties.

## 1.3.0 (24-11-2026)

Rustiger en beter gegroepeerd blokbeheer.

- Groepen in de beheerder: `powerhub_acf_groep()` en `powerhub_acf_groep_eind()` zetten velden die bij elkaar horen in één vlak met kop en uitleg. Weergave (Opmaak, Achtergrond, Ruimte) en Geavanceerd zijn zo opgebouwd.
- De tab Inhoud groepeert automatisch reeksen van minstens twee tekstvelden, knoppen of beeld- en videovelden (`powerhub_groepeer_inhoud()`, filter `powerhub_inhoud_groepen`).
- Uitleg staat altijd onder het veld, ook in herhalers, zodat velden in een rij op gelijke hoogte staan.
- Herhalerrijen zijn losse kaarten met ruimte ertussen.
- De uit- en inklapknoppen van ACF zelf (Nederlandse tekst, teller ervoor) in plaats van eigen dubbele knoppen; het lege sleepvlakje van ACF is verborgen.
- Het geselecteerde blok heeft een donkere kopbalk met witte tekst; iconen wit op de tegel; schakelaars in de themakleur.
- `powerhub_versie()`: Powerhub-bestanden krijgen hun wijzigingstijd als versie, zodat de cache na elke uitrol vernieuwt.
- Focuspunt op foto's: in de beheerder klik je op de foto (of verschuif je het puntje met de pijltjestoetsen). Het punt hoort bij de bijlage en komt op de voorkant als `object-position` op elke `wp_get_attachment_image()`. Functie `powerhub_focuspunt( $id )`.
- Herhalers: rijen als getinte kaarten met een nummerstrook, korte velden automatisch naast elkaar (`powerhub_herhalers_ordenen()`), kolommen ook in herhalerrijen, ingeklapte rij toont alleen de titel, lange herhalers starten ingeklapt.
- Reparatie: blokken en herhalerrijen klappen weer in (het veldenraster overschreef het inklappen van ACF).
## 1.2.4 (22-11-2026)

- Blokken in de editor: velden in één rij zijn even hoog en de invoer staat onderaan, zodat invoervelden op één lijn staan, ook als maar één veld een uitleg heeft. Dunne lijn tussen rijen.
- Velden die bij elkaar horen staan samen op een getint vlak: geef ze de wrapper-class `veld-groep`, het eerste ook `groep-begin`, het laatste `groep-eind`. De 50/50-instellingen gebruiken dit al.

## 1.2.3 (22-11-2026)

- Blokken in de editor: velden staan in een raster van twaalf kolommen. De breedte van een veld (25, 33, 50, 66, 75 of 100) is een vast aantal kolommen, zodat velden over rijen heen uitlijnen. Geen scheidingslijnen en verspringende inspringing meer. Regel voor projecten: laat de breedtes per rij optellen tot 100.

## 1.2.2 (22-11-2026)

- Blokken in de editor: het icoon in de kopbalk van een blok verdwijnt niet meer na aanklikken of inklappen (ACF zet die kopbalk opnieuw neer; Powerhub zet het icoon terug).

## 1.2.1 (21-11-2026)

- Paginacontrole Toegankelijkheid: knop Kopiëren zet alle bevindingen als platte tekst op het klembord (pagina, datum, per soort genummerd, met de grenzen), om te plakken in een ticket of mail. Ook via `powerhubControle.rapport()`.
- Paginacontrole: een achtergrondbeeld dat alleen een streep is (onderstreping van een link, stiftstreep) telt niet meer als 'handmatig controleren'.

## 1.2.0 (20-11-2026)

Blokken als fundament en toegankelijkheid als vaste regel.

- Blokken: elk blok heeft de tabs Inhoud, Weergave en Geavanceerd. Weergave biedt vaste achtergronden uit het project (`powerhub_achtergronden`, met kleurtoken en donker/licht), een 50/50-verdeling (boven/onder of links/rechts) en ruimte boven en onder. Geavanceerd: anker en verbergen.
- Blokken worden automatisch geladen uit `inc/blokken/*.php`; CSS en JS per blok laden alleen op pagina's waar het blok staat.
- Blokkiezer en blokbewerking in de stijl van het project (tokens): zoeken, iconen, uitleg, kleurstalen, contrastwaarschuwing bij 50/50, alles in- of uitklappen.
- Nieuwe helpers: `powerhub_sectie()`, `powerhub_blok()`, `powerhub_knop()`, `powerhub_beeld()`, `powerhub_kop()`, contrastfuncties (`powerhub_contrast()`, `powerhub_is_donker()`).
- Nieuwe module Toegankelijkheid: grenzen in Powerhub, rapport (tekstgroottes, contrast met overschrijvingen, media zonder alt, controlelijst), kleurchecker, hoofdstuk op `/designsysteem/` en een paginacontrole op de voorkant voor beheerders. Filters `powerhub_contrast_rollen`, `powerhub_contrast_paren`, `powerhub_lopende_tekststijlen`, `powerhub_paginacontrole_bereik`.
- Uitroltaken die `false` teruggeven, draaien bij de volgende uitrol opnieuw.

Overstappen van 1.1.0: zet blokken om naar `inc/blokken/<naam>.php` met `powerhub_acf_weergave()`, geef achtergronden een `kleur`-token en gebruik `powerhub_sectie()` in de templates.

## 1.1.0 (20-11-2026)

Designsysteem als vast onderdeel.

- `designsysteem/tokens.json` (DTCG) is de bron van waarheid; `powerhub/bin/tokens.php` genereert `assets/css/tokens.css`. De uitrol genereert altijd opnieuw.
- Module Designsysteem: pagina `/designsysteem/` (alleen ingelogd, niet in Google) met tokens, regels, componenten en blokken, gebouwd met de echte CSS van het thema. Overschrijvingen uit Powerhub zijn zichtbaar.
- Kleuren in Powerhub nemen hun standaard uit tokens.json (`null` als standaard).
- Nieuw: `powerhub_icoon()`, `powerhub_token()`, filters `powerhub_logo_terugval`, `powerhub_font_rollen`, `powerhub_blok_voorbeelden`.
- Opgelost: lettertype-variabelen met aanhalingstekens braken de CSS af.

Overstappen van 1.0.0: maak `designsysteem/tokens.json` en draai `php powerhub/bin/tokens.php`, laat de stijl `thema` afhangen van `tokens`. Zonder tokens.json werkt alles als voorheen.

## 1.0.0 (20-11-2026)

Eerste versie, ontstaan in het Deedmob-thema.

- Tabs en velden als schema; opslaan, opschonen en standaardwaarden gaan automatisch.
- Twee niveaus: klant en beheer (Creators Connect-adres of vinkje in het profiel).
- Opslaan per tab, export, import en reset.
- Flexibele ACF-blokken als kern, met gedeelde weergavevelden (achtergrond, ruimte).
- Eenmalige uitroltaken (`powerhub_na_uitrol`).
- Modules: Algemeen, Footer, Kleuren, Typografie (lokale lettertypes), Animaties (GSAP, Lenis), SEO (aanvulling op Rank Math), Onderhoud en beveiliging, Gereedschap.
