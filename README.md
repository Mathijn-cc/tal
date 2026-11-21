# cc-thema, het startthema van Creators Connect

De nieuwe standaard voor onze WordPress-sites: een custom thema met flexibele ACF-blokken en **Powerhub**, het instellingenportaal in wp-admin. Geen pagebuilder; elke site is snel, eigen en via GitHub uitgerold.

Uitleg over Powerhub zelf: [`powerhub/README.md`](powerhub/README.md).

## Nieuw project starten

1. Op GitHub: **Use this template** → nieuwe **privé** repo, bijvoorbeeld `Mathijn-cc/klantnaam`.
2. Clone de nieuwe repo en draai in die map:
   ```bash
   bash scripts/nieuw-project.sh klantnaam "Klant Naam"
   ```
   Dit vult naam en mapnaam in, zet de uitrol-workflow klaar en vervangt deze README door de projectversie.
3. SSH-sleutel aanmaken op je Mac (één per project, zodat je hem los kunt intrekken):
   ```bash
   ssh-keygen -t ed25519 -N "" -C "github-klantnaam" -f ~/.ssh/github-klantnaam
   pbcopy < ~/.ssh/github-klantnaam.pub   # SiteGround > SSH Keys Manager > Import
   pbcopy < ~/.ssh/github-klantnaam       # GitHub repo > Settings > Secrets > STAGING_SSH_KEY
   ```
4. SiteGround (bijnadaar.nl): subdomein `klantnaam.bijnadaar.nl`, WordPress, ACF Pro, Rank Math.
5. **Designsysteem opbouwen:** waarden van het merk in `designsysteem/tokens.json` (namen houden), dan `php powerhub/bin/tokens.php`. Regels in `designsysteem/regels.php`, lettertypes in `assets/fonts` en `inc/instellingen.php`. Controleer alles op `/designsysteem/`. Heeft de klant een eigen designsysteem, zet het origineel in `designsysteem/bron/` en vertaal het naar onze namen.
6. Committen en pushen. De eerste uitrol start vanzelf; activeer daarna het thema.

## Powerhub bijwerken in een bestaand project

Verbeteringen aan Powerhub maak je **hier**, in het startthema, nooit in een klantproject. Verhoog `POWERHUB_VERSIE` en vul `powerhub/CHANGELOG.md` aan. Daarna in het klantproject:

```bash
bash scripts/powerhub-bijwerken.sh
```

## Wat zit erin

- `powerhub/`: het vaste skelet (instellingen, toegang, blokken-kern, animaties, SEO-aanvulling, beveiliging).
- `designsysteem/`: de bron van waarheid. `tokens.json` (startwaarden), `regels.php` (in te vullen) en de basiscomponenten. Zichtbaar op `/designsysteem/` voor ingelogde gebruikers.
- `assets/css/thema.css`: basiscomponenten (knop, bovenkop, tegel, kaarten, vinklijst, accordeon, tabs, sliderbediening, paneel, kop en footer), alleen met tokens.
- `inc/`: projectcode: welke kleuren Powerhub mag overschrijven, lettertypes, achtergronden (`instellingen.php`) en toegankelijkheidsgrenzen (`toegankelijkheid.php`).
- `inc/blokken/<naam>.php`: één bestand per blok (velden en voorbeeldinhoud). Worden automatisch geladen. Met `template-parts/blokken/<naam>.php` en, als nodig, `assets/css/blokken/<naam>.css` en `assets/js/blokken/<naam>.js`; die laden alleen op pagina's waar het blok staat. Tekst is het referentieblok, Hero een tweede voorbeeld.
- `.github/workflows/uitrollen-staging.yml.voorbeeld`: uitrol naar staging met back-up, versienummer, verkleinen, uitroltaken en rookstest.
- `scripts/`: nieuw project en Powerhub bijwerken.

Dit startthema zelf rolt nergens naartoe uit; de workflow wordt pas actief na `nieuw-project.sh`.

## Regels

Elk project heeft een designsysteem, en dat is de bron van waarheid. Geen losse kleuren of maten in de CSS: ontbreekt er iets, dan komt er een token bij.

Toegankelijkheid is geen controle achteraf. Geen tekst kleiner dan 14px, lopende tekst minimaal 16px, contrast volgens WCAG AA en klikdoelen van minimaal 44px. Powerhub, tab Toegankelijkheid, toont per achtergrond of elke tekstrol voldoet en heeft een kleurchecker; ingelogde beheerders kunnen elke pagina laten controleren via de adminbalk. Accent, link en focus wisselen in `thema.css` automatisch mee met lichte en donkere vlakken (`--context-accent`, `--context-link`, `--context-focus`).

Blokken hebben altijd drie tabs: Inhoud, Weergave (achtergrond, ook 50/50, en ruimte) en Geavanceerd (anker, verbergen). Houd de tab Inhoud kort: velden naast elkaar met `powerhub_acf_breed()`, keuzes als knoppengroep, voorwaardelijke velden met `powerhub_acf_als()`.
