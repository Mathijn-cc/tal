/* Powerhub Toegankelijkheid: paginacontrole op de voorkant, alleen voor beheerders.
   Wordt pas geladen na een klik op "Toegankelijkheid" in de adminbalk, of met ?toegankelijkheid=1.
   Controleert: tekstgrootte, contrast, alt-teksten, namen van knoppen en links, klikdoelen en koppen.
   Een automatische controle vindt niet alles: test ook met toetsenbord en schermlezer. */
(function () {
	if (window.powerhubControle) return;

	var c = Object.assign({ kleinste: 14, lopend: 16, niveau: 'AA', klikdoel: 44 }, window.powerhubToegankelijkheid || {});
	var PANEEL = 'toegankelijkheid-paneel';
	var MARKERING = 'toegankelijkheid-markering';
	var rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var MAX_PER_SOORT = 60;

	var soorten = [
		{ id: 'tekst', titel: 'Tekst te klein' },
		{ id: 'contrast', titel: 'Contrast te laag' },
		{ id: 'handmatig', titel: 'Contrast handmatig controleren' },
		{ id: 'alt', titel: 'Afbeeldingen zonder alt' },
		{ id: 'naam', titel: 'Knoppen en links zonder naam' },
		{ id: 'klikdoel', titel: 'Klikdoelen te klein' },
		{ id: 'koppen', titel: 'Kopstructuur' }
	];

	var bevindingen = [];
	var paneel = null;
	var gemarkeerd = null;
	var vorigeFocus = null;

	/* ---------- Hulpjes ---------- */

	/* Bereik: op bijvoorbeeld /designsysteem/ alleen de voorbeelden controleren, niet de documentatie eromheen. */
	var bereik = c.bereik || '';

	function uitgesloten(el) {
		if (el.closest('#wpadminbar, #' + PANEEL + ', script, style, noscript, template, head')) return true;
		return bereik ? !el.closest(bereik) : false;
	}

	function zichtbaar(el) {
		if (el.checkVisibility && !el.checkVisibility({ opacityProperty: true, visibilityProperty: true })) return false;
		var cs = getComputedStyle(el);
		if (cs.display === 'none' || cs.visibility === 'hidden') return false;
		var r = el.getBoundingClientRect();
		/* Visueel verborgen tekst (screen-reader-text) is 1x1px, een skiplink staat buiten beeld tot hij focus krijgt. */
		if (r.right + window.scrollX <= 0 || r.bottom + window.scrollY <= 0) return false;
		return r.width > 1 && r.height > 1;
	}

	function kleur(waarde) {
		var m = String(waarde).match(/rgba?\(\s*([\d.]+)[\s,]+([\d.]+)[\s,]+([\d.]+)(?:[\s,\/]+([\d.]+%?))?\s*\)/);
		if (!m) return null;
		var a = m[4] === undefined ? 1 : (m[4].slice(-1) === '%' ? parseFloat(m[4]) / 100 : parseFloat(m[4]));
		return [parseFloat(m[1]), parseFloat(m[2]), parseFloat(m[3]), a];
	}

	function mengen(boven, onder) {
		var a = boven[3];
		return [boven[0] * a + onder[0] * (1 - a), boven[1] * a + onder[1] * (1 - a), boven[2] * a + onder[2] * (1 - a), 1];
	}

	function luminantie(rgb) {
		var k = rgb.slice(0, 3).map(function (x) {
			x = x / 255;
			return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * k[0] + 0.7152 * k[1] + 0.0722 * k[2];
	}

	function verhouding(a, b) {
		var l1 = luminantie(a), l2 = luminantie(b);
		return Math.round((Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05) * 100) / 100;
	}

	function hex(rgb) {
		return '#' + rgb.slice(0, 3).map(function (x) { return ('0' + Math.round(x).toString(16)).slice(-2); }).join('');
	}

	/* Een achtergrondbeeld dat alleen een streep is (onderstreping van een link, stiftstreep onder een woord)
	   staat niet achter de letters: niet herhaald en hooguit een kwart van de tekstgrootte hoog. */
	function alleenStreep(cs) {
		var nietHerhaald = cs.backgroundRepeat.split(',').every(function (r) { return r.trim() === 'no-repeat'; });
		if (!nietHerhaald) return false;
		var grens = Math.max(8, parseFloat(cs.fontSize) * 0.35);
		return cs.backgroundSize.split(',').every(function (laag) {
			var delen = laag.trim().split(/\s+/);
			var hoogte = delen[1] || '';
			return /px$/.test(hoogte) && parseFloat(hoogte) <= grens;
		});
	}

	/* Effectieve achtergrond: omhoog door de ouders tot een dekkende kleur. Achtergrondbeeld: handmatig, behalve een streep. */
	function achtergrond(el) {
		var lagen = [];
		for (var n = el; n && n.nodeType === 1; n = n.parentElement) {
			var cs = getComputedStyle(n);
			if (cs.backgroundImage && cs.backgroundImage !== 'none' && !alleenStreep(cs)) return { beeld: n };
			var k = kleur(cs.backgroundColor);
			if (k && k[3] > 0) {
				lagen.push(k);
				if (k[3] >= 1) break;
			}
		}
		var basis = [255, 255, 255, 1];
		for (var i = lagen.length - 1; i >= 0; i--) basis = lagen[i][3] >= 1 ? lagen[i] : mengen(lagen[i], basis);
		return { kleur: basis };
	}

	function beschrijving(el) {
		if (!el) return '';
		var naam = el.tagName.toLowerCase();
		if (el.id) naam += '#' + el.id;
		else if (el.classList.length) naam += '.' + Array.prototype.slice.call(el.classList, 0, 2).join('.');
		return naam;
	}

	function fragment(tekst, lengte) {
		tekst = String(tekst || '').replace(/\s+/g, ' ').trim();
		lengte = lengte || 60;
		return tekst.length > lengte ? tekst.slice(0, lengte - 1) + '…' : tekst;
	}

	function melden(soort, el, tekst, detail) {
		bevindingen.push({ soort: soort, el: el, tekst: tekst, detail: detail || '' });
	}

	/* Toegankelijke naam, vereenvoudigd: aria-label, aria-labelledby, inhoud (met alt van beelden), title. */
	function inhoud(n) {
		var uit = '';
		n.childNodes.forEach(function (k) {
			if (k.nodeType === 3) {
				uit += k.nodeValue;
			} else if (k.nodeType === 1) {
				if (k.getAttribute('aria-hidden') === 'true') return;
				var tag = k.tagName.toLowerCase();
				if (tag === 'img') uit += ' ' + (k.getAttribute('alt') || '');
				else if (tag === 'svg') {
					var t = k.querySelector('title');
					uit += ' ' + (k.getAttribute('aria-label') || '') + (t ? t.textContent : '');
				} else uit += ' ' + inhoud(k);
			}
		});
		return uit.trim();
	}

	function toegankelijkeNaam(el) {
		var label = (el.getAttribute('aria-label') || '').trim();
		if (label) return label;
		var ids = el.getAttribute('aria-labelledby');
		if (ids) {
			var t = ids.split(/\s+/).map(function (id) {
				var d = document.getElementById(id);
				return d ? d.textContent : '';
			}).join(' ').trim();
			if (t) return t;
		}
		if (el.tagName === 'INPUT') {
			if (el.value && /^(button|submit|reset)$/i.test(el.type)) return el.value;
			if (/^submit$/i.test(el.type)) return 'Verzenden';
			if (el.labels && el.labels.length) return el.labels[0].textContent.trim();
		}
		var tekst = inhoud(el);
		if (tekst) return tekst;
		return (el.getAttribute('title') || '').trim();
	}

	/* ---------- Controles ---------- */

	function controleerTekst() {
		var lopers = new Set();
		var gezien = new Set();
		var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
			acceptNode: function (n) { return n.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT; }
		});
		var n;
		while ((n = walker.nextNode())) {
			var el = n.parentElement;
			if (!el || gezien.has(el)) continue;
			gezien.add(el);
			if (uitgesloten(el) || !zichtbaar(el)) continue;
			var cs = getComputedStyle(el);
			var maat = parseFloat(cs.fontSize);
			var gewicht = parseInt(cs.fontWeight, 10) || 400;
			var tekst = fragment(el.textContent, 50);

			/* Tekstgrootte */
			if (maat < c.kleinste - 0.01) {
				melden('tekst', el, '"' + tekst + '"', Math.round(maat * 10) / 10 + 'px, minimaal ' + c.kleinste + 'px');
			} else if (maat < c.lopend - 0.01) {
				var blok = el.closest('p, li, dd, blockquote');
				if (blok && !lopers.has(blok) && blok.textContent.trim().length >= 80 && !el.closest('figcaption, small, nav, footer')) {
					lopers.add(blok);
					melden('tekst', blok, 'Lopende tekst: "' + fragment(blok.textContent, 40) + '"', Math.round(maat * 10) / 10 + 'px, lopende tekst minimaal ' + c.lopend + 'px');
				}
			}

			/* Contrast. Uitgeschakelde bedieningselementen zijn uitgezonderd (WCAG 1.4.3). */
			if (el.closest(':disabled, [aria-disabled="true"]')) continue;
			var voor = kleur(cs.color);
			if (!voor) continue;
			var a = achtergrond(el);
			if (a.beeld) {
				melden('handmatig', el, '"' + tekst + '"', 'Tekst op een achtergrondbeeld (' + beschrijving(a.beeld) + ')');
				continue;
			}
			if (voor[3] < 1) voor = mengen(voor, a.kleur);
			var groot = maat >= 24 || (maat >= 18.66 && gewicht >= 700);
			var eis = c.niveau === 'AAA' ? (groot ? 4.5 : 7) : (groot ? 3 : 4.5);
			var r = verhouding(voor, a.kleur);
			if (r < eis) {
				melden('contrast', el, '"' + tekst + '"', r.toFixed(2) + ' : 1, eis ' + eis + ' : 1 (' + hex(voor) + ' op ' + hex(a.kleur) + ')');
			}
		}
	}

	function controleerAlt() {
		document.querySelectorAll('img:not([alt]), input[type="image"]:not([alt]), [role="img"]:not([aria-label]):not([aria-labelledby])').forEach(function (el) {
			if (uitgesloten(el)) return;
			if (el.getAttribute('role') === 'img' && el.tagName.toLowerCase() === 'img') return;
			var bron = el.currentSrc || el.getAttribute('src') || '';
			melden('alt', el, el.tagName.toLowerCase() === 'img' ? 'Afbeelding zonder alt-attribuut' : beschrijving(el) + ' zonder naam', fragment(bron.split('/').pop(), 50));
		});
	}

	var INTERACTIEF = 'a[href], button, [role="button"], [role="link"], [role="tab"], input[type="button"], input[type="submit"], input[type="reset"]';
	var KLIKBAAR = 'a[href], button, input:not([type="hidden"]), select, textarea, summary, [role="button"], [role="link"], [role="tab"], [role="checkbox"], [role="switch"], [tabindex]:not([tabindex="-1"])';

	function controleerNamen() {
		document.querySelectorAll(INTERACTIEF).forEach(function (el) {
			if (uitgesloten(el) || !zichtbaar(el)) return;
			if (!toegankelijkeNaam(el)) {
				melden('naam', el, (el.tagName === 'A' ? 'Link' : 'Knop') + ' zonder toegankelijke naam', beschrijving(el) + (el.getAttribute('href') ? ' naar ' + fragment(el.getAttribute('href'), 40) : ''));
			}
		});
	}

	/* Een link in lopende tekst (inline, met tekst eromheen) is uitgezonderd van de klikdoelgrootte. */
	function inlineInTekst(el) {
		if (el.tagName !== 'A' || getComputedStyle(el).display !== 'inline') return false;
		var blok = el.parentElement;
		while (blok && getComputedStyle(blok).display === 'inline') blok = blok.parentElement;
		if (!blok) return false;
		return blok.textContent.replace(/\s+/g, '').length > el.textContent.replace(/\s+/g, '').length;
	}

	function controleerKlikdoelen() {
		document.querySelectorAll(KLIKBAAR).forEach(function (el) {
			if (uitgesloten(el) || !zichtbaar(el)) return;
			if (inlineInTekst(el)) return;
			/* Selectievakjes en keuzerondjes met een label: het label is het klikdoel. */
			if (el.tagName === 'INPUT' && /^(checkbox|radio)$/i.test(el.type) && el.labels && el.labels.length) return;
			var r = el.getBoundingClientRect();
			if (r.width < c.klikdoel - 0.5 || r.height < c.klikdoel - 0.5) {
				var naam = fragment(toegankelijkeNaam(el), 40) || beschrijving(el);
				melden('klikdoel', el, naam, Math.round(r.width) + ' x ' + Math.round(r.height) + 'px, minimaal ' + c.klikdoel + ' x ' + c.klikdoel + 'px');
			}
		});
	}

	function controleerKoppen() {
		var koppen = Array.prototype.filter.call(document.querySelectorAll('h1, h2, h3, h4, h5, h6, [role="heading"][aria-level]'), function (el) {
			if (uitgesloten(el)) return false;
			var cs = getComputedStyle(el);
			return cs.display !== 'none' && cs.visibility !== 'hidden';
		});
		var vorige = 0;
		var h1 = [];
		koppen.forEach(function (el) {
			var niveau = el.getAttribute('aria-level') ? parseInt(el.getAttribute('aria-level'), 10) : parseInt(el.tagName.slice(1), 10);
			if (niveau === 1) h1.push(el);
			if (vorige && niveau > vorige + 1) {
				melden('koppen', el, 'h' + niveau + ' na h' + vorige + ': "' + fragment(el.textContent, 40) + '"', 'Er wordt een kopniveau overgeslagen');
			} else if (!vorige && niveau !== 1) {
				melden('koppen', el, 'Eerste kop is een h' + niveau + ': "' + fragment(el.textContent, 40) + '"', 'Een pagina begint met de h1');
			}
			vorige = niveau;
		});
		if (bereik) return;
		if (!h1.length) melden('koppen', null, 'Deze pagina heeft geen h1', 'Het eerste blok hoort een kop te hebben');
		if (h1.length > 1) {
			h1.slice(1).forEach(function (el) {
				melden('koppen', el, 'Extra h1: "' + fragment(el.textContent, 40) + '"', h1.length + ' keer een h1 op deze pagina, één is genoeg');
			});
		}
	}

	function analyseren() {
		bevindingen = [];
		controleerTekst();
		controleerAlt();
		controleerNamen();
		controleerKlikdoelen();
		controleerKoppen();
	}

	/* ---------- Paneel ---------- */

	function maak(tag, attr, tekst) {
		var el = document.createElement(tag);
		Object.keys(attr || {}).forEach(function (k) {
			if (k === 'class') el.className = attr[k];
			else el.setAttribute(k, attr[k]);
		});
		if (tekst !== undefined) el.textContent = tekst;
		return el;
	}

	function markeren(el) {
		if (gemarkeerd) gemarkeerd.classList.remove(MARKERING);
		gemarkeerd = el;
		if (!el) return;
		el.classList.add(MARKERING);
		el.scrollIntoView({ behavior: rustig ? 'auto' : 'smooth', block: 'center', inline: 'nearest' });
	}

	function tekenen() {
		var inhoudEl = paneel.querySelector('[data-inhoud]');
		inhoudEl.textContent = '';
		var samenvatting = paneel.querySelector('[data-samenvatting]');
		var aantal = bevindingen.filter(function (b) { return b.soort !== 'handmatig'; }).length;
		var handmatig = bevindingen.length - aantal;
		samenvatting.textContent = (aantal ? aantal + (aantal === 1 ? ' bevinding' : ' bevindingen') : 'Geen bevindingen') +
			(handmatig ? ', ' + handmatig + ' handmatig controleren' : '') + ' op deze pagina.';

		var schoon = [];
		soorten.forEach(function (soort) {
			var lijst = bevindingen.filter(function (b) { return b.soort === soort.id; });
			if (!lijst.length) {
				if (soort.id !== 'handmatig') schoon.push(soort.titel.toLowerCase());
				return;
			}
			var groep = maak('details', { class: 'tg-groep' + (soort.id === 'handmatig' ? ' is-handmatig' : '') });
			if (soort.id !== 'handmatig') groep.open = true;
			var kop = maak('summary', {});
			kop.appendChild(maak('span', {}, soort.titel));
			kop.appendChild(maak('span', { class: 'tg-aantal' }, String(lijst.length)));
			groep.appendChild(kop);
			var ol = maak('ol', {});
			lijst.slice(0, MAX_PER_SOORT).forEach(function (b) {
				var li = maak('li', {});
				var knop = maak(b.el ? 'button' : 'div', b.el ? { type: 'button', class: 'tg-bevinding' } : { class: 'tg-bevinding' });
				knop.appendChild(maak('span', { class: 'tg-bevinding-tekst' }, b.tekst));
				if (b.detail) knop.appendChild(maak('span', { class: 'tg-bevinding-detail' }, b.detail));
				if (b.el) {
					knop.addEventListener('click', function () {
						paneel.querySelectorAll('.tg-bevinding[aria-current]').forEach(function (k) { k.removeAttribute('aria-current'); });
						knop.setAttribute('aria-current', 'true');
						markeren(b.el);
					});
				}
				li.appendChild(knop);
				ol.appendChild(li);
			});
			groep.appendChild(ol);
			if (lijst.length > MAX_PER_SOORT) groep.appendChild(maak('p', { class: 'tg-meer' }, 'En nog ' + (lijst.length - MAX_PER_SOORT) + ' meer.'));
			inhoudEl.appendChild(groep);
		});
		if (schoon.length) {
			inhoudEl.appendChild(maak('p', { class: 'tg-schoon' }, 'In orde: ' + schoon.join(', ') + '.'));
		}
	}

	/* Alle bevindingen als platte tekst, om te plakken in een ticket, mail of chat. */
	function rapportTekst() {
		var regels = ['Toegankelijkheid: ' + document.title, window.location.href, new Date().toLocaleString('nl-NL'), ''];
		var samenvatting = paneel.querySelector('[data-samenvatting]');
		if (samenvatting) regels.push(samenvatting.textContent, '');
		soorten.forEach(function (soort) {
			var lijst = bevindingen.filter(function (b) { return b.soort === soort.id; });
			if (!lijst.length) return;
			regels.push(soort.titel + ' (' + lijst.length + ')');
			lijst.forEach(function (b, i) {
				regels.push((i + 1) + '. ' + b.tekst + (b.detail ? ' (' + b.detail + ')' : ''));
			});
			regels.push('');
		});
		regels.push('Grenzen: tekst ' + c.kleinste + 'px, lopende tekst ' + c.lopend + 'px, contrast ' + c.niveau + ', klikdoel ' + c.klikdoel + 'px.');
		return regels.join('\n');
	}

	function kopieren(knop) {
		var tekst = rapportTekst();
		var klaar = function (gelukt) {
			knop.textContent = gelukt ? 'Gekopieerd' : 'Kopiëren mislukt';
			setTimeout(function () { knop.textContent = 'Kopiëren'; }, 2000);
		};
		var terugval = function () {
			var veld = maak('textarea', { readonly: '', 'aria-hidden': 'true' });
			veld.value = tekst;
			veld.style.position = 'fixed';
			veld.style.opacity = '0';
			paneel.appendChild(veld);
			veld.select();
			var gelukt = false;
			try { gelukt = document.execCommand('copy'); } catch (e) { gelukt = false; }
			paneel.removeChild(veld);
			knop.focus();
			klaar(gelukt);
		};
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(tekst).then(function () { klaar(true); }, terugval);
		} else {
			terugval();
		}
	}

	function bouwPaneel() {
		paneel = maak('section', { id: PANEEL, role: 'region', 'aria-labelledby': PANEEL + '-titel', tabindex: '-1' });
		var kop = maak('div', { class: 'tg-kop' });
		var titels = maak('div', { class: 'tg-titels' });
		titels.appendChild(maak('strong', { id: PANEEL + '-titel', class: 'tg-titel' }, 'Toegankelijkheid'));
		titels.appendChild(maak('span', { class: 'tg-samenvatting', 'data-samenvatting': '', role: 'status' }));
		kop.appendChild(titels);
		var opnieuw = maak('button', { type: 'button', class: 'tg-knop' }, 'Opnieuw');
		opnieuw.addEventListener('click', function () { markeren(null); analyseren(); tekenen(); });
		var kopieer = maak('button', { type: 'button', class: 'tg-knop', 'aria-live': 'polite' }, 'Kopiëren');
		kopieer.addEventListener('click', function () { kopieren(kopieer); });
		var sluiten = maak('button', { type: 'button', class: 'tg-knop tg-sluiten', 'aria-label': 'Paneel sluiten' });
		sluiten.innerHTML = '<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M3 3l10 10M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
		sluiten.addEventListener('click', sluit);
		kop.appendChild(kopieer);
		kop.appendChild(opnieuw);
		kop.appendChild(sluiten);
		paneel.appendChild(kop);
		paneel.appendChild(maak('div', { class: 'tg-inhoud', 'data-inhoud': '' }));
		paneel.appendChild(maak('p', { class: 'tg-voet' },
			'Grenzen: tekst ' + c.kleinste + 'px, lopende tekst ' + c.lopend + 'px, contrast ' + c.niveau + ', klikdoel ' + c.klikdoel + 'px. ' +
			'Een automatische controle vindt niet alles: test ook met het toetsenbord en een schermlezer.'));
		paneel.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { e.preventDefault(); sluit(); }
		});
		document.body.appendChild(paneel);
	}

	function open() {
		vorigeFocus = document.activeElement;
		if (!paneel) bouwPaneel();
		paneel.hidden = false;
		markeren(null);
		analyseren();
		tekenen();
		paneel.focus({ preventScroll: true });
	}

	function sluit() {
		markeren(null);
		if (paneel) paneel.hidden = true;
		var terug = vorigeFocus && document.contains(vorigeFocus) ? vorigeFocus : document.querySelector('#wp-admin-bar-powerhub-toegankelijkheid > a');
		if (terug && terug.focus) terug.focus();
	}

	window.powerhubControle = { open: open, sluit: sluit, bevindingen: function () { return bevindingen; }, rapport: function () { return paneel ? rapportTekst() : ''; } };

	/* Met ?toegankelijkheid=1 direct openen, als lettertypes en lay-out klaar zijn. */
	if (/[?&]toegankelijkheid=1(&|$)/.test(window.location.search)) {
		var starten = function () { (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () { setTimeout(open, 50); }); };
		if (document.readyState === 'complete') starten();
		else window.addEventListener('load', starten);
	}
})();
