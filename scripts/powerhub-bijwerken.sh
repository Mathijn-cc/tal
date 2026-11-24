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
echo "Lees powerhub/BIJWERKEN.md: daar staan de overstapstappen per versie."

# Wat nog eenmalig uit het thema naar Powerhub moet.
if grep -rqs "powerhub_fonts" inc/ || ls assets/fonts/*.woff2 >/dev/null 2>&1; then
	echo ""
	echo "LET OP: dit project heeft nog lettertypes in het thema (filter powerhub_fonts of assets/fonts)."
	echo "Zet ze eenmalig over naar Powerhub › Design › Typografie, zie powerhub/BIJWERKEN.md (Naar 1.5.0 en 1.6.0)."
fi
if grep -rqs "powerhub_logo_terugval" inc/ functions.php || ls assets/beelden/*logo* assets/images/*logo* >/dev/null 2>&1; then
	echo ""
	echo "LET OP: dit project heeft mogelijk nog een logo in het thema."
	echo "Zet het eenmalig over naar Powerhub › Algemeen › Logo, zie powerhub/BIJWERKEN.md (Logo uit het thema naar Powerhub)."
fi
if grep -rqs "footer_maker" --include=*.php . --exclude-dir=powerhub; then
	echo ""
	echo "LET OP: footer_maker bestaat niet meer. Gebruik powerhub_maker_url(), zie powerhub/BIJWERKEN.md (Naar 1.4.0)."
fi
echo "Lees powerhub/CHANGELOG.md. Bij een nieuwe hoofdversie (eerste cijfer) moet het project mogelijk iets aanpassen."
echo "Controleer daarna met: git diff --stat powerhub/"

exit 0
}
