/* Powerhub: lettertypebibliotheek (Google Fonts en Fontshare) en eigen lettertypes. */
(function ($) {
	var vak = document.querySelector('[data-lettertypes]');
	if (!vak || !window.powerhubLettertypes) return;
	var cfg = window.powerhubLettertypes;

	var zoek = vak.querySelector('[data-lettertype-zoek]');
	var bron = vak.querySelector('[data-lettertype-bron]');
	var status = vak.querySelector('[data-lettertype-status]');
	var resultaten = vak.querySelector('[data-lettertype-resultaten]');
	var catalogus = null;
	var laden = null;

	function melding(tekst, fout) {
		status.textContent = tekst || '';
		status.classList.toggle('is-fout', !!fout);
	}

	function verzoek(actie, data) {
		return $.post(cfg.ajax, $.extend({ action: actie, nonce: cfg.nonce }, data || {}));
	}

	function herladen() {
		var url = new URL(window.location.href);
		url.searchParams.set('tab', 'typografie');
		url.searchParams.delete('melding');
		window.location.href = url.toString();
	}

	/* De catalogus pas ophalen bij de eerste zoekopdracht. */
	function haalCatalogus() {
		if (catalogus || laden) return laden;
		melding('Bibliotheek laden...');
		laden = verzoek('powerhub_lettertypes_catalogus').then(function (res) {
			if (res && res.success) {
				catalogus = res.data;
				melding('');
			} else {
				catalogus = [];
				melding(res && res.data ? res.data : 'De bibliotheek is nu niet bereikbaar.', true);
			}
		}, function () {
			catalogus = [];
			melding('De bibliotheek is nu niet bereikbaar. Typ de naam en kies Toevoegen.', true);
		});
		return laden;
	}

	var bronNaam = { google: 'Google Fonts', fontshare: 'Fontshare' };

	function rij(font) {
		var li = document.createElement('li');
		var naam = document.createElement('strong');
		naam.textContent = font.n;
		var info = document.createElement('small');
		var stijlen = font.v ? 'variabel ' + font.v.replace('..', ' tot ') : (font.w && font.w.length ? font.w.length + ' gewicht' + (font.w.length === 1 ? '' : 'en') : '');
		info.textContent = [bronNaam[font.b], font.c, stijlen, font.i ? 'met cursief' : ''].filter(Boolean).join(' · ');
		var knop = document.createElement('button');
		knop.type = 'button';
		knop.className = 'button';
		knop.textContent = 'Toevoegen';
		knop.addEventListener('click', function () { toevoegen(font, knop); });
		li.appendChild(naam);
		li.appendChild(info);
		li.appendChild(knop);
		return li;
	}

	function toon() {
		var term = zoek.value.trim().toLowerCase();
		resultaten.innerHTML = '';
		if (term.length < 2) return;
		var filter = bron.value;
		var treffers = (catalogus || []).filter(function (f) {
			return (!filter || f.b === filter) && f.n.toLowerCase().indexOf(term) !== -1;
		});
		/* Exacte en beginnende namen eerst, daarna Fontshare, daarna de populairste bij Google. */
		treffers.sort(function (a, b) {
			var sa = a.n.toLowerCase() === term ? 0 : (a.n.toLowerCase().indexOf(term) === 0 ? 1 : 2);
			var sb = b.n.toLowerCase() === term ? 0 : (b.n.toLowerCase().indexOf(term) === 0 ? 1 : 2);
			return sa - sb || (a.b === b.b ? (a.p || 0) - (b.p || 0) : (a.b === 'fontshare' ? -1 : 1));
		});
		treffers.slice(0, 25).forEach(function (f) { resultaten.appendChild(rij(f)); });

		/* Niets gevonden of geen bibliotheek: op naam proberen. */
		if (!treffers.length) {
			var naam = zoek.value.trim();
			['fontshare', 'google'].forEach(function (b) {
				if (filter && filter !== b) return;
				resultaten.appendChild(rij({ b: b, n: naam, c: 'op naam', w: [], i: false, v: '' }));
			});
		}
	}

	var wacht;
	zoek.addEventListener('input', function () {
		clearTimeout(wacht);
		wacht = setTimeout(function () { $.when(haalCatalogus()).always(toon); }, 200);
	});
	bron.addEventListener('change', toon);
	zoek.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });

	function toevoegen(font, knop) {
		knop.disabled = true;
		knop.textContent = 'Ophalen...';
		melding('"' + font.n + '" wordt opgehaald en lokaal gezet...');
		verzoek('powerhub_lettertype_toevoegen', {
			bron: font.b,
			naam: font.n,
			slug: font.s || '',
			soort: font.c || '',
			bereik: font.v || '',
			cursief: font.i ? 1 : 0,
			gewichten: (font.w || []).join(',')
		}).then(function (res) {
			if (res && res.success) {
				melding('"' + font.n + '" is toegevoegd. Kies het hierboven bij een rol en sla op.');
				herladen();
			} else {
				knop.disabled = false;
				knop.textContent = 'Toevoegen';
				melding(res && res.data ? res.data : 'Dat lukte niet.', true);
			}
		}, function () {
			knop.disabled = false;
			knop.textContent = 'Toevoegen';
			melding('Dat lukte niet. Probeer het opnieuw.', true);
		});
	}

	/* Verwijderen. */
	$(vak).on('click', '[data-lettertype-weg]', function () {
		var knop = this;
		if (!window.confirm('Dit lettertype verwijderen? De bestanden gaan weg van de server.')) return;
		knop.disabled = true;
		verzoek('powerhub_lettertype_verwijderen', { sleutel: knop.dataset.lettertypeWeg }).then(function (res) {
			if (res && res.success) {
				herladen();
			} else {
				knop.disabled = false;
				window.alert(res && res.data ? res.data : 'Dat lukte niet.');
			}
		});
	});

	/* Eigen lettertype: bestanden uit de mediabibliotheek, gewicht en stijl uit de bestandsnaam geraden. */
	var eigen = vak.querySelector('[data-lettertype-eigen]');
	var lijst = eigen.querySelector('[data-eigen-bestanden]');
	var opslaan = eigen.querySelector('[data-eigen-opslaan]');
	var naamVeld = eigen.querySelector('[data-eigen-naam]');
	var gekozen = [];

	var woorden = { thin: 100, hairline: 100, extralight: 200, ultralight: 200, light: 300, regular: 400, book: 400, normal: 400, medium: 500, semibold: 600, demibold: 600, bold: 700, extrabold: 800, ultrabold: 800, black: 900, heavy: 900 };

	function raad(bestand) {
		var n = bestand.toLowerCase().replace(/\.(woff2?|ttf|otf)$/, '');
		var platte = n.replace(/[^a-z0-9]/g, '');
		var gewicht = '400';
		if (/variable|variabel|\bvf\b/.test(n)) {
			gewicht = '100 900';
		} else {
			var getal = n.match(/(?:^|[^0-9])([1-9]00)(?:[^0-9]|$)/);
			if (getal) {
				gewicht = getal[1];
			} else {
				Object.keys(woorden).sort(function (a, b) { return b.length - a.length; }).some(function (w) {
					if (platte.indexOf(w) !== -1) { gewicht = String(woorden[w]); return true; }
					return false;
				});
			}
		}
		return { gewicht: gewicht, stijl: /italic|oblique|cursief/.test(n) ? 'italic' : 'normal' };
	}

	function keuzelijst(opties, waarde) {
		var s = document.createElement('select');
		opties.forEach(function (o) {
			var opt = document.createElement('option');
			opt.value = o[0];
			opt.textContent = o[1];
			if (o[0] === waarde) opt.selected = true;
			s.appendChild(opt);
		});
		return s;
	}

	var gewichten = [['100', '100 Thin'], ['200', '200 Extra light'], ['300', '300 Light'], ['400', '400 Regular'], ['500', '500 Medium'], ['600', '600 Semibold'], ['700', '700 Bold'], ['800', '800 Extra bold'], ['900', '900 Black'], ['100 900', 'Variabel (100 tot 900)']];

	function tekenLijst() {
		lijst.innerHTML = '';
		gekozen.forEach(function (b, i) {
			var li = document.createElement('li');
			var naam = document.createElement('code');
			naam.textContent = b.naam;
			var g = keuzelijst(gewichten, b.gewicht);
			g.setAttribute('aria-label', 'Gewicht van ' + b.naam);
			g.addEventListener('change', function () { b.gewicht = g.value; });
			var s = keuzelijst([['normal', 'Recht'], ['italic', 'Cursief']], b.stijl);
			s.setAttribute('aria-label', 'Stijl van ' + b.naam);
			s.addEventListener('change', function () { b.stijl = s.value; });
			var weg = document.createElement('button');
			weg.type = 'button';
			weg.className = 'button-link';
			weg.textContent = 'Weghalen';
			weg.addEventListener('click', function () { gekozen.splice(i, 1); tekenLijst(); });
			li.appendChild(naam);
			li.appendChild(g);
			li.appendChild(s);
			li.appendChild(weg);
			lijst.appendChild(li);
		});
		opslaan.disabled = !gekozen.length;
	}

	eigen.querySelector('[data-eigen-kies]').addEventListener('click', function () {
		var kader = wp.media({ title: 'Lettertypebestanden kiezen (.woff2 of .woff)', multiple: true, button: { text: 'Gebruiken' } });
		kader.on('select', function () {
			kader.state().get('selection').toJSON().forEach(function (b) {
				var bestand = b.filename || (b.url || '').split('/').pop();
				if (!/\.woff2?$/i.test(bestand)) return;
				if (gekozen.some(function (x) { return x.id === b.id; })) return;
				var r = raad(bestand);
				gekozen.push({ id: b.id, naam: bestand, gewicht: r.gewicht, stijl: r.stijl });
				if (!naamVeld.value && b.title) {
					naamVeld.value = b.title.replace(/[-_ ]?(variable|italic|regular|bold|light|medium|thin|black|[1-9]00).*$/i, '').replace(/[-_]+/g, ' ').trim();
				}
			});
			tekenLijst();
		});
		kader.open();
	});

	opslaan.addEventListener('click', function () {
		opslaan.disabled = true;
		verzoek('powerhub_lettertype_eigen', {
			naam: naamVeld.value,
			soort: eigen.querySelector('[data-eigen-soort]').value,
			bestanden: JSON.stringify(gekozen.map(function (b) { return { id: b.id, gewicht: b.gewicht, stijl: b.stijl }; }))
		}).then(function (res) {
			if (res && res.success) {
				herladen();
			} else {
				opslaan.disabled = false;
				window.alert(res && res.data ? res.data : 'Dat lukte niet.');
			}
		});
	});
})(jQuery);
