#!/usr/bin/env bash
# Maakt van een verse kopie van het startthema een klantthema.
#
# Gebruik (in de map van de nieuwe repo, na "Use this template" en clonen):
#   bash scripts/nieuw-project.sh klantnaam "Klant Naam"
#
# - klantnaam: korte naam in kleine letters, ook de mapnaam van het thema en het subdomein (klantnaam.bijnadaar.nl)
# - "Klant Naam": de naam zoals hij in WordPress verschijnt

set -euo pipefail

SLUG="${1:-}"
NAAM="${2:-}"

if [[ -z "$SLUG" || -z "$NAAM" ]]; then
	echo "Gebruik: bash scripts/nieuw-project.sh klantnaam \"Klant Naam\""
	exit 1
fi
if [[ ! "$SLUG" =~ ^[a-z0-9-]+$ ]]; then
	echo "De korte naam mag alleen kleine letters, cijfers en streepjes bevatten."
	exit 1
fi
if [[ ! -f .github/workflows/uitrollen-staging.yml.voorbeeld ]]; then
	echo "Dit project is al ingericht (of je staat niet in de themamap)."
	exit 1
fi

export SLUG NAAM
for bestand in style.css functions.php .github/workflows/uitrollen-staging.yml.voorbeeld scripts/README-project.md; do
	perl -pi -e 's/__SLUG__/$ENV{SLUG}/g; s/__NAAM__/$ENV{NAAM}/g' "$bestand"
done

mv .github/workflows/uitrollen-staging.yml.voorbeeld .github/workflows/uitrollen-staging.yml
mv scripts/README-project.md README.md
rm scripts/nieuw-project.sh

echo
echo "Klaar: thema '$NAAM' ($SLUG)."
echo
echo "Nog te doen:"
echo "  1. Repo secret STAGING_SSH_KEY toevoegen (Settings > Secrets and variables > Actions)."
echo "  2. In SiteGround: subdomein $SLUG.bijnadaar.nl, WordPress, ACF Pro en Rank Math."
echo "  3. Designsysteem: merkwaarden in designsysteem/tokens.json, dan php powerhub/bin/tokens.php. Regels in designsysteem/regels.php."
echo "  4. Committen en pushen: de eerste uitrol start vanzelf. Daarna het thema activeren."
