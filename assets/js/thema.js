/* Front-end van het thema TAL Leadership.
   Menu en ankerlinks werken altijd. Alle beweging (GSAP, ScrollTrigger, SplitText) alleen als de animaties
   in Powerhub aan staan en de bezoeker geen beperkte beweging heeft ingesteld. */
(function () {
	var instellingen = window.powerhub || {};
	var rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var lenis = window.powerhubLenis || null;
	/* Onderkant van de vaste kop: hoogte van de kop plus de WordPress-balk als die vast staat (ingelogd). */
	var kopHoogte = function () {
		var kop = document.querySelector('.kop-site');
		var balk = document.getElementById('wpadminbar');
		var extra = balk && getComputedStyle(balk).position === 'fixed' ? balk.offsetHeight : 0;
		return (kop ? kop.offsetHeight : 76) + extra;
	};

	/* Vloeiend scrollen iets minder zwevend dan de standaard van Lenis. */
	if (lenis && lenis.options) lenis.options.lerp = 0.15;

	/* Vlakken die zelf scrollen (zoals het menu van het designsysteem) buiten Lenis houden, anders vangt Lenis het scrollen af. */
	if (lenis) Array.prototype.forEach.call(document.querySelectorAll('.designsysteem-menu, .menupaneel, iframe'), function (el) {
		el.setAttribute('data-lenis-prevent', '');
	});

	/* ---------- Mobiel menu ---------- */
	/* De hamburger in de kop opent en sluit het paneel en draait daarbij naar een kruis (CSS). */
	var paneel = document.getElementById('menupaneel');
	var knop = document.querySelector('.kop-site .menuknop');
	var sluitTimer = null;
	function isOpen() { return document.body.classList.contains('menu-open'); }
	function menuOpen() {
		if (!paneel) return;
		clearTimeout(sluitTimer);
		paneel.classList.remove('is-sluiten');
		paneel.hidden = false;
		paneel.classList.add('is-open');
		document.body.classList.add('menu-open');
		knop.setAttribute('aria-expanded', 'true');
		knop.setAttribute('aria-label', 'Menu sluiten');
		if (lenis) lenis.stop();
	}
	function menuDicht(terugFocus) {
		if (!paneel || !isOpen()) return;
		document.body.classList.remove('menu-open');
		knop.setAttribute('aria-expanded', 'false');
		knop.setAttribute('aria-label', 'Menu openen');
		if (lenis) lenis.start();
		paneel.classList.remove('is-open');
		paneel.classList.add('is-sluiten');
		sluitTimer = setTimeout(function () { paneel.hidden = true; paneel.classList.remove('is-sluiten'); }, rustig ? 0 : 450);
		if (terugFocus) knop.focus({ preventScroll: true });
	}
	if (knop && paneel) {
		knop.addEventListener('click', function () { if (isOpen()) menuDicht(); else menuOpen(); });
		paneel.addEventListener('click', function (e) { if (e.target.closest('a')) menuDicht(); });
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') menuDicht(true);
			/* Focus blijft bij de knop en de links in het paneel. */
			if (e.key !== 'Tab' || !isOpen()) return;
			var items = [knop].concat(Array.prototype.slice.call(paneel.querySelectorAll('a, button')));
			var eerste = items[0], laatste = items[items.length - 1];
			if (e.shiftKey && document.activeElement === eerste) { e.preventDefault(); laatste.focus(); }
			else if (!e.shiftKey && document.activeElement === laatste) { e.preventDefault(); eerste.focus(); }
		});
		window.addEventListener('resize', function () { if (window.innerWidth > 960) menuDicht(); });
	}

	/* ---------- Ankerlinks ---------- */
	/* Altijd de actuele positie meten. Een vastgezette sectie zit in een pin-spacer: meet vanaf de bovenkant daarvan,
	   anders kom je midden in de zijwaartse scroll uit. */
	function naarAnker(id, direct) {
		var doel = id === 'top' ? document.body : document.getElementById(id);
		if (!doel) return false;
		if (lenis) lenis.resize();
		var huidig = lenis ? lenis.scroll : window.scrollY;
		var meetpunt = doel.closest('.pin-spacer') || doel;
		var y = id === 'top' ? 0 : Math.max(0, Math.round(meetpunt.getBoundingClientRect().top + huidig - kopHoogte()));
		if (lenis) {
			lenis.scrollTo(y, direct || rustig ? { immediate: true, force: true } : { duration: 1.4, easing: function (t) { return 1 - Math.pow(1 - t, 4); }, force: true });
		} else {
			window.scrollTo({ top: y, behavior: direct || rustig ? 'auto' : 'smooth' });
		}
		return true;
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[href*="#"]') : null;
		if (!a || a.target === '_blank') return;
		var url = new URL(a.href, window.location.href);
		if (url.pathname !== window.location.pathname || url.host !== window.location.host || !url.hash) return;
		var id = decodeURIComponent(url.hash.slice(1));
		if (id !== 'top' && !document.getElementById(id)) return;
		e.preventDefault();
		/* even wachten als het mobiele menu nog sluit */
		setTimeout(function () {
			naarAnker(id, false);
			if (history.replaceState) history.replaceState(null, '', '#' + id);
		}, document.body.classList.contains('menu-open') ? 60 : 0);
	});

	/* Binnenkomen met een anker in de link (bijvoorbeeld vanaf /contact/ naar /#traject): de browser springt al
	   voordat koppen, foto's en vastgezette secties hun plek hebben. Daarom zelf scrollen zodra alles staat. */
	var startAnker = window.location.hash ? decodeURIComponent(window.location.hash.slice(1)) : '';
	if (startAnker && 'scrollRestoration' in history) history.scrollRestoration = 'manual';
	window.talNaarStartAnker = function () {
		if (!startAnker) return;
		naarAnker(startAnker, true);
		/* nog één keer na late beelden of de Calendly-agenda */
		setTimeout(function () { naarAnker(startAnker, true); startAnker = ''; }, 500);
	};

	/* ---------- Accordeon en tabs (componenten van het startthema) ---------- */
	/* Accordeon: één tegelijk open. */
	document.querySelectorAll('.accordeon details').forEach(function (item) {
		item.addEventListener('toggle', function () {
			if (!item.open) return;
			item.parentElement.querySelectorAll('details').forEach(function (ander) {
				if (ander !== item) ander.open = false;
			});
		});
	});

	/* Tabs: aria-selected bijwerken, ook met de pijltjestoetsen. */
	document.querySelectorAll('.tabs').forEach(function (rij) {
		var tabs = Array.prototype.slice.call(rij.querySelectorAll('.tab'));
		function kies(tab) {
			tabs.forEach(function (t) {
				var actief = t === tab;
				t.setAttribute('aria-selected', actief ? 'true' : 'false');
				t.tabIndex = actief ? 0 : -1;
			});
			rij.dispatchEvent(new CustomEvent('tab-gekozen', { detail: { tab: tab, index: tabs.indexOf(tab) } }));
		}
		rij.addEventListener('click', function (e) {
			var tab = e.target.closest('.tab');
			if (tab) kies(tab);
		});
		rij.addEventListener('keydown', function (e) {
			var richting = { ArrowRight: 1, ArrowLeft: -1 }[e.key];
			if (!richting) return;
			var huidig = tabs.indexOf(document.activeElement);
			if (huidig < 0) return;
			var volgende = tabs[(huidig + richting + tabs.length) % tabs.length];
			volgende.focus();
			kies(volgende);
		});
	});

	/* Zonder animaties: na het laden naar het anker. */
	if (!instellingen.animaties || rustig || !window.gsap || !window.ScrollTrigger) {
		if (document.readyState === 'complete') window.talNaarStartAnker();
		else window.addEventListener('load', window.talNaarStartAnker, { once: true });
	}

	/* ---------- Beweging ---------- */
	var gsap = window.gsap;
	var ST = window.ScrollTrigger;
	if (!instellingen.animaties || rustig || !gsap || !ST) return;
	gsap.registerPlugin(ST);
	var Split = window.SplitText;
	if (Split) gsap.registerPlugin(Split);

	var breed = function () { return window.innerWidth > 860; };
	var opScroll = function (el, start) { return { trigger: el, start: start || 'top 86%', once: true }; };
	var alle = function (sel, ouder) { return Array.prototype.slice.call((ouder || document).querySelectorAll(sel)); };

	var klaar = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
	klaar.then(function () {

		/* Hero: de kop woord voor woord omhoog uit een masker, kort na elkaar; daarna komt Toch? erachteraan. */
		if (Split) alle('.sectie-hero .hero-kop').forEach(function (heroKop) {
			var kop = heroKop.querySelector('h1, h2');
			if (!kop) return;
			var hoogteVoor = kop.getBoundingClientRect().height;
			var split = Split.create(kop, { type: 'words', mask: 'words' });
			/* Ruimte rondom elk woord in het masker, zodat schreven en uitlopers (s, j, g, t) niet worden afgekapt. */
			var ruimte = function (m) {
				m.style.padding = '0.12em 0.1em 0.28em';
				m.style.margin = '-0.12em -0.1em -0.28em';
			};
			(split.masks || []).forEach(ruimte);
			var verschil = kop.getBoundingClientRect().height - hoogteVoor;
			if (Math.abs(verschil) > 0.5) kop.style.marginBottom = (-verschil) + 'px';
			var tl = gsap.timeline({ delay: 0.15 });
			/* Start ruim onder het masker (dat heeft ruimte voor uitlopers), zodat er vooraf niets van de letters zichtbaar is. */
			tl.from(split.words, { yPercent: 140, duration: 1, ease: 'power4.out', stagger: 0.045 });
			var naschrift = heroKop.querySelector('.hero-naschrift');
			var nsplit = null;
			if (naschrift) {
				nsplit = Split.create(naschrift, { type: 'words', mask: 'words' });
				(nsplit.masks || []).forEach(ruimte);
				tl.from(nsplit.words, { yPercent: 140, duration: 1.1, ease: 'power4.out' }, '-=0.35');
			}
			/* Na de animatie de maskers openzetten: niets kan meer worden afgekapt. */
			tl.eventCallback('onComplete', function () {
				(split.masks || []).concat(naschrift && nsplit ? nsplit.masks || [] : []).forEach(function (m) { m.style.overflow = 'visible'; });
			});
		});

		/* Koppen en ondertitels (tussenkoppen, labels): regel voor regel omhoog uit een masker. */
		if (Split) alle('main h1, main h2, main .kop-m, main .marge, main .bovenkop-tal').forEach(function (kop) {
			if (kop.closest('.hero-kop')) return;
			var hoogteVoor = kop.getBoundingClientRect().height;
			var split = Split.create(kop, { type: 'lines', mask: 'lines' });
			/* De regels onder elkaar als flex-kolom: dan tellen de negatieve marges van de maskers gewoon op
			   in plaats van in elkaar te vallen, en is de opgeknipte kop exact even hoog als de gewone kop. */
			kop.style.display = 'flex';
			kop.style.flexDirection = 'column';
			/* Ruimte boven en onder elke regel, zodat j, g, p en accenten niet worden afgesneden. */
			(split.masks || []).forEach(function (m) {
				m.style.paddingTop = '0.12em';
				m.style.marginTop = '-0.12em';
				m.style.paddingBottom = '0.28em';
				m.style.marginBottom = '-0.28em';
			});
			/* Een foto in de regel kan de opgesplitste kop iets hoger maken: dat verschil opvangen,
			   zodat de tekst eronder niet verspringt. */
			var verschil = kop.getBoundingClientRect().height - hoogteVoor;
			if (Math.abs(verschil) > 0.5) kop.style.marginBottom = (-verschil) + 'px';
			gsap.from(split.lines, {
				yPercent: 105, duration: 1.2, ease: 'power4.out', stagger: 0.08, scrollTrigger: opScroll(kop),
				/* Niet terugzetten bij een stiftstreep (die animeert nog) of een foto in de kop:
				   terugzetten verandert daar de hoogte van de kop en dan verspringt de tekst eronder. */
				onComplete: function () { if (!kop.querySelector('.onderstreep, .hero-mini')) { split.revert(); kop.style.marginBottom = ''; kop.style.display = ''; kop.style.flexDirection = ''; } }
			});
		});

		/* Foto's: gordijn bij in beeld komen, het beeld schuift tegengesteld mee, daarna lichte parallax. */
		alle('main .foto').forEach(function (foto, i) {
			if (foto.classList.contains('academy-beeld') || foto.classList.contains('hero-mini')) return;
			var beeld = foto.querySelector('.foto-beeld');
			var vanRechts = i % 2 === 1;
			var tl = gsap.timeline({ scrollTrigger: opScroll(foto, 'top 96%') });
			tl.fromTo(foto, { clipPath: vanRechts ? 'inset(0% 0% 0% 100%)' : 'inset(0% 100% 0% 0%)' },
				{ clipPath: 'inset(0% 0% 0% 0%)', duration: 0.85, ease: 'power3.inOut' });
			if (beeld) {
				tl.fromTo(beeld, { xPercent: vanRechts ? 16 : -16, scale: 1.18 }, { xPercent: 0, scale: 1, duration: 1.15, ease: 'expo.out' }, 0.05);
				gsap.fromTo(beeld, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: foto, start: 'top bottom', end: 'bottom top', scrub: true } });
			}
		});

		/* Hero: de kleine foto opent zich in de kop. Met clip-path, dus zonder herberekening van de tekst. */
		var mini = document.querySelector('.sectie-hero .hero-mini');
		if (mini) {
			var mb = mini.querySelector('.foto-beeld');
			/* Ook onzichtbaar bij de start: een dichtgeknepen foto laat anders een dun streepje zien. */
			gsap.fromTo(mini, { clipPath: 'inset(0% 50% 0% 50% round 0.36em)', autoAlpha: 0 },
				{ clipPath: 'inset(0% 0% 0% 0% round 0.36em)', autoAlpha: 1, duration: 1.3, delay: 0.5, ease: 'expo.inOut', clearProps: 'clipPath' });
			if (mb) gsap.fromTo(mb, { scale: 1.25 }, { scale: 1, duration: 1.6, delay: 0.5, ease: 'expo.out', force3D: true });
		}

		/* Herkenning: één vraag tegelijk, de sectie staat even stil. Elke vraag verdwijnt helemaal voor de volgende komt;
		   snappen zorgt dat het scrollen altijd eindigt op een volledige vraag. */
		alle('.sectie-herkenning').forEach(function (sectie) {
			var vragen = alle('.herkenning-vraag', sectie);
			if (vragen.length < 2) return;
			sectie.classList.add('is-gepind');
			var balkje = sectie.querySelector('.herkenning-balk i');
			var bak = sectie.querySelector('.herkenning-vragen');
			var meet = function () {
				var hoogste = Math.max.apply(null, vragen.map(function (v) { return v.offsetHeight; }));
				if (bak) bak.style.minHeight = (hoogste + 24) + 'px';
			};
			meet();
			ST.addEventListener('refreshInit', meet);
			gsap.set(vragen.slice(1), { autoAlpha: 0, yPercent: 60 });
			var tl = gsap.timeline({
				scrollTrigger: {
					trigger: sectie.querySelector('.herkenning-podium'),
					start: function () { return 'top top+=' + (kopHoogte() + (breed() ? 72 : 56)); },
					end: '+=' + (vragen.length * 60) + '%',
					pin: true,
					refreshPriority: 2,
					scrub: 0.5,
					snap: { snapTo: 'labels', duration: { min: 0.25, max: 0.7 }, delay: 0.08, ease: 'power2.inOut' }
				}
			});
			tl.addLabel('vraag0');
			vragen.forEach(function (v, i) {
				if (i === 0) return;
				tl.to({}, { duration: 0.6 });
				tl.to(vragen[i - 1], { autoAlpha: 0, yPercent: -40, duration: 0.45, ease: 'power2.in' });
				tl.fromTo(v, { autoAlpha: 0, yPercent: 40 }, { autoAlpha: 1, yPercent: 0, duration: 0.45, ease: 'power2.out' });
				if (balkje) tl.to(balkje, { scaleX: (i + 1) / vragen.length, duration: 0.9, ease: 'none' }, '<-0.45');
				tl.addLabel('vraag' + i);
			});
			tl.to({}, { duration: 0.6 });
		});

		/* Claim: kleurt woord voor woord in met het scrollen. */
		if (Split) alle('.traject-claim').forEach(function (claim) {
			var woorden = Split.create(claim, { type: 'words' }).words;
			/* Beginkleur haalt 3:1 op crème, zodat ook de nog niet ingekleurde woorden leesbaar zijn. */
			var start = getComputedStyle(document.documentElement).getPropertyValue('--kleur-donker-400').trim() || '#888A97';
			var eind = getComputedStyle(claim).color;
			gsap.fromTo(woorden, { color: start }, { color: eind, stagger: 0.1, ease: 'none',
				scrollTrigger: { trigger: claim, start: 'top 80%', end: 'bottom 45%', scrub: 0.8 } });
		});

		/* Citaat: woorden komen woord voor woord scherp in beeld. */
		if (Split) alle('.sectie-citaat blockquote').forEach(function (citaat) {
			var delen = Split.create(citaat, { type: 'words' }).words;
			gsap.fromTo(delen,
				{ autoAlpha: 0.08, yPercent: 35, filter: 'blur(10px)' },
				{ autoAlpha: 1, yPercent: 0, filter: 'blur(0px)', ease: 'power2.out', stagger: 0.12,
					scrollTrigger: { trigger: citaat, start: 'top 88%', end: 'center 52%', scrub: 0.65 } });
			var naam = citaat.parentElement.querySelector('figcaption');
			if (naam) gsap.fromTo(naam, { autoAlpha: 0, x: -24 }, { autoAlpha: 1, x: 0, ease: 'none',
				scrollTrigger: { trigger: naam, start: 'top 92%', end: 'top 75%', scrub: 0.8 } });
		});

		/* Stiftstreep: groeit mee met scrollen. In de afsluiter eerder, elders als de zin goed in beeld staat. */
		alle('main .onderstreep').forEach(function (streep) {
			gsap.fromTo(streep, { backgroundSize: '0% 0.3em' }, { backgroundSize: '100% 0.3em', ease: 'power1.inOut',
				scrollTrigger: streep.closest('.sectie-cta')
					? { trigger: streep, start: 'top 92%', end: 'top 68%', scrub: 0.6 }
					: { trigger: streep, start: 'top 58%', end: 'top 32%', scrub: 0.6 } });
		});

		/* Programma: verticaal scrollen wordt zijwaarts bewegen, met de rij in het midden van het scherm. */
		if (breed()) alle('.sectie-programma').forEach(function (sectie) {
			var kader = sectie.querySelector('.programma-kader');
			var spoor = sectie.querySelector('.programma-spoor');
			if (!kader || !spoor) return;
			sectie.classList.add('is-gepind');
			var afstand = function () { return Math.max(0, spoor.scrollWidth - kader.clientWidth); };
			gsap.to(spoor, { x: function () { return -afstand(); }, ease: 'none',
				scrollTrigger: {
					trigger: kader,
					start: 'center center',
					end: function () { return '+=' + afstand(); },
					pin: sectie,
					pinSpacing: true,
					anticipatePin: 1,
					refreshPriority: 1,
					scrub: 0.8,
					invalidateOnRefresh: true
				} });
		});

		/* Brede foto (academy): opent zich van klein naar de volle breedte. */
		alle('.academy-beeld').forEach(function (vlak) {
			var tijd = { trigger: vlak, start: 'top bottom', end: 'top 70%', scrub: 0.4 };
			gsap.fromTo(vlak, { clipPath: 'inset(8% 12% 8% 12%)' }, { clipPath: 'inset(0% 0% 0% 0%)', ease: 'none', scrollTrigger: tijd });
			var b = vlak.querySelector('.foto-beeld');
			if (b) gsap.fromTo(b, { scale: 1.15 }, { scale: 1, ease: 'none', scrollTrigger: Object.assign({}, tijd) });
		});

		/* Resultaten en checklist: rustig binnenkomen. */
		alle('.uitkomst').forEach(function (t, i) {
			gsap.from(t, { y: 40, autoAlpha: 0, duration: 1, delay: (i % 3) * 0.08, ease: 'power3.out', scrollTrigger: opScroll(t, 'top 92%') });
		});
		alle('.checklist li').forEach(function (li) {
			gsap.from(li, { y: 16, autoAlpha: 0, duration: 0.8, ease: 'power3.out', scrollTrigger: opScroll(li, 'top 94%') });
		});

		/* Afsluiter: de achtergrondfoto beweegt rustig mee. */
		alle('.sectie-cta').forEach(function (sectie) {
			var b = sectie.querySelector('.cta-beeld .foto-beeld');
			if (b) gsap.fromTo(b, { yPercent: -5, scale: 1.03 }, { yPercent: 5, scale: 1, ease: 'none',
				scrollTrigger: { trigger: sectie, start: 'top bottom', end: 'bottom top', scrub: true } });
		});

		/* Afsluiter: ronde knop die naar de muis toe trekt. */
		if (window.matchMedia('(pointer: fine)').matches) alle('.magneet').forEach(function (magneet) {
			var zone = magneet.parentElement;
			zone.addEventListener('pointermove', function (e) {
				var r = magneet.getBoundingClientRect();
				gsap.to(magneet, { x: (e.clientX - (r.left + r.width / 2)) * 0.28, y: (e.clientY - (r.top + r.height / 2)) * 0.28, duration: 0.7, ease: 'power3.out' });
			});
			zone.addEventListener('pointerleave', function () {
				gsap.to(magneet, { x: 0, y: 0, duration: 0.9, ease: 'elastic.out(1, 0.5)' });
			});
		});

		/* Vastgezette secties in de volgorde van de pagina, en opnieuw meten als alle beelden er zijn. */
		ST.sort();
		ST.refresh();
		if (document.readyState === 'complete') { ST.sort(); ST.refresh(); window.talNaarStartAnker(); }
		else window.addEventListener('load', function () { ST.sort(); ST.refresh(); window.talNaarStartAnker(); }, { once: true });
	});
})();
