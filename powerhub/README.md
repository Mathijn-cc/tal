# Powerhub

Het vaste skelet van Creators Connect voor custom WordPress-thema's. Elk project krijgt dezelfde `powerhub/`-map; alles wat specifiek is voor een klant staat in `inc/` en `designsysteem/` van het thema.

**Elk project heeft een designsysteem.** `designsysteem/tokens.json` is de bron van waarheid voor kleuren, letters, maten, hoeken, schaduwen en beweging. Daaruit wordt `assets/css/tokens.css` gegenereerd, en de pagina `/designsysteem/` (alleen ingelogd) toont alles met de echte CSS en blokken van het thema.

**Regel nummer één: pas in een project niets aan in `powerhub/`.** Mist er iets, voeg het dan toe aan het startthema en neem de nieuwe versie over in de projecten. Zo blijft elke site met één handeling bij te werken.

## Wat Powerhub doet

| Onderdeel | Niveau | Wat |
|---|---|---|
| Designsysteem | klant | Link naar `/designsysteem/`, overzicht van tokens en componenten |
| Algemeen | klant | Logo's, telefoon, e-mail, adres, hoofdknop |
| Footer | klant | Korte tekst, social media |
| Kleuren | beheer | Gekozen kleuren uit tokens.json overschrijven (`--kleur-<naam>`) |
| Typografie | beheer | Lokale lettertypes, `--letter-kop`, `--letter-tekst`, `--letter-accent` |
| Animaties | beheer | GSAP, ScrollTrigger, Lenis, `data-animatie` |
| SEO | beheer | Aanvulling op Rank Math: kruimelpad, bewaking staging/live, bijlagen doorsturen |
| Onderhoud en beveiliging | beheer | Onderhoudsmodus, XML-RPC uit, gebruikersnamen verbergen, bestandseditor uit, opschonen |
| Toegankelijkheid | beheer | Grenzen (tekst, contrast, klikdoel), rapport, kleurchecker, paginacontrole op de voorkant |
| Gereedschap | beheer | Export, import, reset, systeemoverzicht |

Daarnaast: flexibele ACF-blokken, eenmalige uitroltaken en hulpfuncties voor templates.

## Designsysteem

```
designsysteem/
  tokens.json            bron van waarheid (W3C Design Tokens, DTCG)
  regels.php             gebruiksregels: kleur, typografie, vorm, beweging, content
  componenten/*.php      één voorbeeld per component, op volgorde van bestandsnaam
  bron/                  het originele designsysteem van de klant, als die er is
assets/css/tokens.css    gegenereerd, niet met de hand aanpassen
```

**Tokens.** Groep en naam vormen de CSS-variabele: `kleur.lime-500` wordt `--kleur-lime-500`. Verwijzingen schrijf je als `{kleur.lime-500}` en worden `var(--kleur-lime-500)`. Tekststijlen (`$type: typography`) geven `--maat-<naam>` en `--regel-<naam>`. Een mobiele waarde zet je in `$extensions["nl.creatorsconnect"]["mobiel"]`, een groep zonder voorvoegsel met `$extensions["nl.creatorsconnect"]["prefix"] = ""`. De originele naam uit het designsysteem van de klant bewaar je in `$extensions["nl.creatorsconnect"]["bron"]`.

**Genereren.** `php powerhub/bin/tokens.php` schrijft `assets/css/tokens.css`; `--controle` geeft een fout als het bestand achterloopt. De uitrol genereert altijd opnieuw, dus de site gebruikt altijd `tokens.json`.

**Regels voor thema.css.** Alleen tokens, geen losse kleuren of maten. Gebruik in componenten de semantische kleuren (`--kleur-vlak-*`, `--kleur-tekst-*`, `--kleur-actie-*`, `--kleur-rand-*`). Mist er iets, voeg dan een token toe. De stijl `thema` hangt af van de stijl `tokens`, die Powerhub registreert.

**Componenten.** Een bestand in `designsysteem/componenten/` begint met een docblock: eerste regel de naam, daarna de uitleg. De pagina toont het voorbeeld met de markup eronder.

**Blokken.** Geef per blok voorbeeldinhoud op, dan staat elk blok op de pagina:

```php
add_filter( 'powerhub_blok_voorbeelden', function ( $v ) {
	$v['hero'] = array( 'kop' => 'Kop met *accent.*', 'achtergrond' => 'donker' );
	return $v;
} );
```

**Kleuren in Powerhub.** Het project kiest welke kleuren overschreven mogen worden; zonder standaard (`null`) komt de waarde uit tokens.json. Een overschrijving is op `/designsysteem/` zichtbaar bij de kleur.

```php
add_filter( 'powerhub_kleuren', fn() => array( 'actie-500' => array( 'Actie', null ) ) );
```

**Iconen.** Lucide-lijniconen in `assets/iconen/<naam>.svg`, in templates `powerhub_icoon( 'naam' )`.

## Twee niveaus

- **Klant** (`edit_theme_options`): ziet de tabs met niveau `klant`.
- **Beheer**: beheerrechten én een e-mailadres op `@creatorsconnect.nl`, of in het gebruikersprofiel aangevinkt als Powerhub-beheerder. Andere domeinen toevoegen kan met de filter `powerhub_beheer_domeinen`.

Opslaan gaat per tab en wordt op de server gecontroleerd; een klant kan geen beheertab opslaan, ook niet met een zelfgemaakt verzoek.

## Inhaken vanuit het project

```php
// Palet. Dezelfde standaardwaarden horen in de :root van thema.css.
add_filter( 'powerhub_kleuren', fn() => array(
	'donker' => array( 'Donker', '#2d3e50' ),
	'actie'  => array( 'Actie', '#a0d911' ),
) );

// Lettertypes, bestanden in assets/fonts van het thema.
add_filter( 'powerhub_fonts', fn() => array(
	'barlow' => array(
		'naam'      => 'Barlow',
		'fallback'  => 'system-ui, sans-serif',
		'rollen'    => array( 'kop', 'tekst', 'accent' ),
		'bestanden' => array(
			array( 'bestand' => 'barlow-latin-400-normal.woff2', 'gewicht' => '400', 'stijl' => 'normal' ),
		),
	),
) );

// Standaardwaarden van bestaande velden overschrijven.
add_filter( 'powerhub_standaardwaarden', fn( $s ) => array_merge( $s, array(
	'hoofdknop_tekst' => 'Plan een demo',
) ) );

// Eigen tab.
add_action( 'powerhub_registreren', function () {
	powerhub_tab( 'project', array(
		'titel'    => 'Project',
		'niveau'   => 'beheer',     // of 'klant'
		'volgorde' => 80,
		'velden'   => array(
			'taalwissel' => array( 'type' => 'schakel', 'label' => 'Taalwissel', 'standaard' => true ),
		),
	) );
} );

// Velden bij een bestaande tab.
add_action( 'powerhub_registreren', function () {
	powerhub_velden_toevoegen( 'algemeen', array(
		'kvk' => array( 'type' => 'tekst', 'label' => 'KvK-nummer' ),
	) );
}, 20 );

// Een standaardmodule uitzetten.
add_filter( 'powerhub_modules', fn( $m ) => array_diff( $m, array( 'animaties' ) ) );
```

Veldtypes: `tekst`, `tekstvak`, `url`, `email`, `getal` (met `min`, `max`, `stap`), `kleur`, `beeld`, `schakel`, `keuze` (met `keuzes`), `kop` (tussenkop). Opschonen gebeurt automatisch per type.

Uitlezen in templates: `powerhub( 'telefoon' )`, `powerhub_aan( 'taalwissel' )`.

## Blokken

```php
add_filter( 'powerhub_blok_layouts', function ( $layouts ) {
	$p = 'tekst';
	$layouts[ $p ] = array(
		'label'      => 'Tekst',
		'sub_fields' => array_merge(
			array(
				powerhub_acf_tab( $p, 'Inhoud' ),
				powerhub_acf_veld( $p, 'kop', 'Kop', 'text', array( 'instructions' => POWERHUB_ACCENT_UITLEG ) ),
			),
			powerhub_acf_weergave( $p )   // achtergrond en ruimte, voor elk blok gelijk
		),
	);
	return $layouts;
} );
```

Weergave: `template-parts/blokken/tekst.php` met `$blok` als data. In `page.php`: `powerhub_blokken()`.
Achtergronden per project: filter `powerhub_achtergronden`. Andere posttypes met blokken: filter `powerhub_blok_posttypes`.

## Hulpfuncties

- `powerhub_kop( $tekst, 'h2', $class )`: `*accent*` wordt cursief, `<br>` een nieuwe regel.
- `powerhub_knop( $acf_link, 'actie' )`
- `powerhub_logo( 'standaard' | 'licht' )`, met filter `powerhub_logo_terugval` voor een logobestand uit het thema
- `powerhub_icoon( 'naam' )`, `powerhub_token( 'kleur.actie-500' )`
- `powerhub_hoofdknop()`, `powerhub_telefoon_link()`, `powerhub_social()`, `powerhub_kruimelpad()`
- `powerhub_sectie_class( $blok, 'naam' )`

## Animaties

`data-animatie="omhoog"` (of `vervagen`, `links`, `rechts`) op een element. `data-animatie-groep` op een ouder laat de kinderen na elkaar verschijnen. Respecteert altijd "beperkte beweging".

## Uitroltaken

```php
add_filter( 'powerhub_uitroltaken', function ( $taken ) {
	$taken['homepage-v1'] = 'mijn_functie';   // draait één keer per omgeving
	return $taken;
} );
```

De workflow roept na elke uitrol `do_action( 'powerhub_na_uitrol' )` aan.

## Toegankelijkheid

Tab **Toegankelijkheid** (beheer) met de grenzen van het project: kleinste tekst (14), kleinste lopende tekst (16), contrastniveau (AA of AAA), kleinste klikdoel (44) en de schakelaar voor de paginacontrole. Daaronder een rapport:

- **Tekstgroottes**: elke tekststijl uit tokens.json, desktop en mobiel. Stijlen uit `powerhub_lopende_tekststijlen` (standaard `lopend`, `groot`) toetsen aan de grens voor lopende tekst.
- **Contrast**: elke rol op elke achtergrond uit `powerhub_achtergronden`, met de werkelijke kleuren (tokens.json plus overschrijvingen in Powerhub), en losse paren.
- **Media zonder alt-tekst** en een **controlelijst** (automatisch waar het kan, de rest handmatig).
- **Kleurchecker** met het palet van het thema. Rapport en checker staan ook op `/designsysteem/#toegankelijkheid`.

```php
// Rollen: label, token op licht, token op donker, soort (normaal, groot, ui). Ontbrekende tokens worden overgeslagen.
add_filter( 'powerhub_contrast_rollen', function ( $rollen ) {
	$rollen['bovenkop'] = array( 'Bovenkop', 'tekst-gedempt', 'tekst-op-donker-gedempt', 'normaal' );
	return $rollen;
} );

// Losse paren: label, voorgrond, achtergrond, soort.
add_filter( 'powerhub_contrast_paren', fn( $p ) => array_merge( $p, array(
	array( 'Knoptekst op actieknop', 'actie-primair-tekst', 'actie-primair', 'normaal' ),
) ) );
```

**Paginacontrole.** Beheerders zien op de voorkant het adminbalk-item Toegankelijkheid (of open een pagina met `?toegankelijkheid=1`). Het script laadt pas na de klik en meldt: te kleine tekst, te laag contrast (tekst op een achtergrondbeeld: handmatig), afbeeldingen zonder alt, knoppen en links zonder naam, te kleine klikdoelen (links in lopende tekst uitgezonderd), overgeslagen kopniveaus en meer dan één h1. Klik op een bevinding om het element te zien. Met `powerhub_paginacontrole_bereik` (CSS-selector) beperk je de controle; op `/designsysteem/` zijn dat de voorbeelden.

Andere filters: `powerhub_toegankelijkheid_taal` (standaard `nl`), `powerhub_toegankelijkheid_kopblokken` (blokken die zelf een h1 maken zonder veld `kop`).

## Versies

`POWERHUB_VERSIE` in `powerhub.php`. Verhoog bij elke wijziging in het startthema: patch voor fixes, minor voor nieuwe functies, major als projecten iets moeten aanpassen.
