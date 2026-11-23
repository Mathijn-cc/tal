# TAL Leadership thema

Custom WordPress-thema voor TAL Leadership, gebouwd door Creators Connect op het startthema (cc-thema). Pagina's bestaan uit flexibele ACF-blokken; instellingen en functies staan in Powerhub.

## Werkwijze

- Deze repo is de enige bron van het thema. Pas nooit bestanden direct op de server aan.
- Elke push naar `main` wordt automatisch uitgerold naar de staging (tal.bijnadaar.nl), zie het tabblad Actions.
- Bij elke uitrol wordt eerst een back-up van het thema op de server gemaakt (`~/thema-backups/`, de laatste tien blijven bewaard).
- Het versienummer wordt per uitrol automatisch opgehoogd.

## Opbouw

- `powerhub/`: het vaste skelet van Creators Connect. **Niet aanpassen in dit project**, zie `powerhub/README.md`.
- `designsysteem/`: de bron van waarheid (tokens.json, regels, componenten). Zichtbaar op `/designsysteem/` voor ingelogde gebruikers. Na een wijziging in tokens.json: `php powerhub/bin/tokens.php`.
- `inc/instellingen.php`: welke kleuren Powerhub mag overschrijven, lettertypes, achtergronden en eigen Powerhub-tabs.
- `inc/blokken.php`: de ACF-blokken.
- `template-parts/blokken/<naam>.php`: de weergave per blok.
- `assets/`: thema-CSS en -JS, lokale lettertypes.

## Powerhub bijwerken

```bash
bash scripts/powerhub-bijwerken.sh
```

## Benodigd

- ACF Pro en Rank Math op de site.
- Repo secret `STAGING_SSH_KEY`: de private SSH-sleutel van de SiteGround-gebruiker.
- Host, poort en gebruiker staan in de workflow. De repo moet daarom privé blijven.

## TAL Leadership

- **Ontwerp:** Design-artifact "TAL Leadership homepagina" (Claude). Kleuren, maten en teksten komen daaruit.
- **Letters:** DM Serif Display (alle koppen), General Sans (tekst), Kalnia alleen voor TAL in het logo. Lokaal in `assets/fonts`.
- **Koppen:** `*woorden*` krijgen de koraal stiftstreep (`tal_kop()` in `inc/helpers.php`), `<br>` is een nieuwe regel. In de hero zet `{foto}` de kleine foto in de zin.
- **Blokken:** hero, herkenning, traject (claim), programma (kaarten op een rij), vier-delen (lagen), academy (brede foto met tekst), resultaten, voor-wie (checklist), over-mij, citaat, podcast, cta (afsluiter), contact (gegevens, formulier en Calendly).
- **Beweging:** alles staat in `assets/js/thema.js` (GSAP, ScrollTrigger, SplitText, Lenis). Uit via Powerhub > Animaties, en altijd uit bij beperkte beweging.
- **Eerste uitrol:** de taak `homepage-v1` (`inc/uitrol.php`) zet de foto's uit `assets/beelden` in de mediabibliotheek, maakt de pagina Home met alle blokken en het hoofdmenu. Vereist ACF Pro; zonder ACF probeert de volgende uitrol het opnieuw.
- **Contact:** de pagina `/contact/` heeft het blok contact: gegevens, het Gravity Forms contactformulier en de Calendly-agenda (anker `#kennismaking`, script laadt pas als de agenda bijna in beeld is). Alle knoppen "Plan een kennismaking" gaan naar `/contact/#kennismaking`, overige contactlinks naar `/contact/` (`TAL_KENNISMAKING_URL` en `TAL_CONTACT_URL` in `inc/helpers.php`).
- **Formulieren:** export in `inc/formulieren/<naam>.json`, import bij de uitrol (`contactformulier-v1`, vereist Gravity Forms). Formulieren met de class `tal-formulier` krijgen de themaknop; Gravity Forms laadt geen eigen CSS (`inc/formulieren.php`). Meldingen gaan naar het beheerdersadres van WordPress; zet dat voor livegang op het adres van Chantal.
- **404:** `404.php`, met titel, tekst en knoptekst in Powerhub > 404 (`inc/instellingen.php`). De knop gaat altijd naar de homepage; een leeg veld valt terug op de standaardtekst.
- **Nog aan te leveren:** schermafbeelding van de Spotify-speler voor het podcastblok (tot die tijd staat er een roze tegel).
