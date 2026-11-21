# __NAAM__ thema

Custom WordPress-thema voor __NAAM__, gebouwd door Creators Connect op het startthema (cc-thema). Pagina's bestaan uit flexibele ACF-blokken; instellingen en functies staan in Powerhub.

## Werkwijze

- Deze repo is de enige bron van het thema. Pas nooit bestanden direct op de server aan.
- Elke push naar `main` wordt automatisch uitgerold naar de staging (__SLUG__.bijnadaar.nl), zie het tabblad Actions.
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
