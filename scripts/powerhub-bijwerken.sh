#!/usr/bin/env bash
# Haalt de nieuwste Powerhub uit het startthema (Mathijn-cc/cc-thema) en vervangt de map powerhub/ in dit project.
# Projectcode in inc/ blijft onaangeroerd.
#
# Gebruik, in de map van het klantthema:
#   bash scripts/powerhub-bijwerken.sh            nieuwste versie van main
#   bash scripts/powerhub-bijwerken.sh v1.2.0     een vaste versie (tag)

set -euo pipefail

# Alles tussen { } wordt eerst volledig ingelezen: het script mag zichzelf onderweg vervangen.
{

BRON="https://github.com/Mathijn-cc/cc-thema"
VERSIE="${1:-main}"

if [[ ! -f powerhub/powerhub.php ]]; then
	echo "Geen powerhub/ gevonden. Draai dit script in de map van het thema."
	exit 1
fi
if [[ -n "$(git status --porcelain powerhub 2>/dev/null)" ]]; then
	echo "Er staan niet-gecommitte wijzigingen in powerhub/. Die horen in het startthema, niet hier."
	exit 1
fi

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

git clone --quiet --depth 1 --branch "$VERSIE" "$BRON" "$TMP/cc-thema"

lees_versie() { perl -ne "print \$1 if /define\( 'POWERHUB_VERSIE', '([^']+)' \)/" "$1"; }
OUD="$(lees_versie powerhub/powerhub.php)"
NIEUW="$(lees_versie "$TMP/cc-thema/powerhub/powerhub.php")"

if [[ "$OUD" == "$NIEUW" ]]; then
	echo "Powerhub is al bij: $OUD."
	exit 0
fi

rm -rf powerhub
cp -R "$TMP/cc-thema/powerhub" powerhub
cp "$TMP/cc-thema/scripts/powerhub-bijwerken.sh" scripts/powerhub-bijwerken.sh

echo "Powerhub bijgewerkt: $OUD -> $NIEUW."
echo "Lees powerhub/CHANGELOG.md. Bij een nieuwe hoofdversie (eerste cijfer) moet het project mogelijk iets aanpassen."
echo "Controleer daarna met: git diff --stat powerhub/"

exit 0
}
