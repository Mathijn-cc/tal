/* Powerhub: animaties voor blokken, koppen, losse elementen en oplopende cijfers.
 * Instellingen komen uit Powerhub, tab Animaties (window.powerhub). Projecteigen animaties horen in assets/js van het thema.
 *
 * - Blokken: elke .sectie met .binnen komt in beeld. data-blokanimatie op de sectie overschrijft het effect
 *   (Geavanceerd > Animatie); "geen" zet het uit voor dat blok.
 * - Koppen: h1, h2 en h3 met class "kop" krijgen het effect van hun niveau. Regels, woorden en letters via SplitText
 *   (met aria-label, zodat schermlezers de hele kop horen).
 * - Cijfers: [data-teller] telt op van nul naar de waarde; voor- en achtervoegsel blijven staan. De echte waarde staat
 *   voor schermlezers in een verborgen kopie, de breedte ligt vooraf vast zodat er niets verspringt.
 */
(function () {
	var d = document.documentElement;
	var ph = window.powerhub || {};
	var rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var mobiel = window.matchMedia('(max-width: 767px)').matches;

	function vrijgeven() { d.classList.remove('animatie-wacht'); }

	/* ---------- Cijfers (werkt ook zonder GSAP) ---------- */

	function ontleed(tekst) {
		var m = String(tekst).match(/^(\D*?)(\d+(?:[.,]\d+)?)(.*)$/);
		if (!m) return null;
		var getal = m[2];
		var scheider = getal.indexOf(',') > -1 ? ',' : '.';
		var decimalen = getal.indexOf(scheider) > -1 ? getal.split(scheider)[1].length : 0;
		return { voor: m[1], waarde: parseFloat(getal.replace(',', '.')), decimalen: decimalen, scheider: scheider, na: m[3] };
	}

	function tellers() {
		var lijst = Array.prototype.slice.call(document.querySelectorAll('[data-teller]'));
		lijst.forEach(function (el) {
			var eind = el.textContent.trim();
			var delen = ontleed(eind);
			if (!delen || !ph.animaties || rustig) return;

			/* Vaste breedte op de eindwaarde, verborgen echte waarde voor schermlezers, zichtbare teller aria-hidden. */
			el.style.display = 'inline-block';
			el.style.minWidth = el.getBoundingClientRect().width + 'px';
			el.style.fontVariantNumeric = 'tabular-nums';
			el.textContent = '';
			var zicht = document.createElement('span');
			zicht.setAttribute('aria-hidden', 'true');
			var echt = document.createElement('span');
			echt.className = 'screen-reader-text';
			echt.textContent = eind;
			el.appendChild(zicht);
			el.appendChild(echt);

			function toon(fractie) {
				var n = (delen.waarde * fractie).toFixed(delen.decimalen);
				zicht.textContent = delen.voor + n.replace('.', delen.scheider) + delen.na;
			}
			toon(0);

			function speel() {
				var duur = (ph.tellerDuur || 1.8) * 1000;
				var begin = null;
				function stap(tijd) {
					if (begin === null) begin = tijd;
					var t = Math.min(1, (tijd - begin) / duur);
					toon(1 - Math.pow(1 - t, 4)); /* zacht uitlopen */
					if (t < 1) requestAnimationFrame(stap); else zicht.textContent = eind;
				}
				requestAnimationFrame(stap);
			}

			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (r) {
					if (!r[0].isIntersecting) return;
					io.disconnect();
					speel();
				}, { rootMargin: '0px 0px -10% 0px' });
				io.observe(el);
			} else {
				speel();
			}
		});
	}

	tellers();

	if (!ph.animaties || rustig || !window.gsap || !window.ScrollTrigger) {
		vrijgeven();
		return;
	}

	gsap.registerPlugin(ScrollTrigger);
	if (window.SplitText) gsap.registerPlugin(SplitText);

	if (ph.vloeiend && window.Lenis) {
		var lenis = new Lenis();
		window.powerhubLenis = lenis;
		lenis.on('scroll', ScrollTrigger.update);
		gsap.ticker.add(function (tijd) { lenis.raf(tijd * 1000); });
		gsap.ticker.lagSmoothing(0);
	}

	var schaal = mobiel ? 0.5 : 1;
	var afstand = (ph.afstand || 24) * schaal;
	var duur = (ph.duur || 0.8) * (mobiel ? 0.75 : 1);
	var easing = ph.easing || 'power3.out';

	function trigger(el) {
		return {
			trigger: el,
			start: ph.start || 'top 88%',
			once: !ph.herhalen,
			toggleActions: ph.herhalen ? 'play none none reverse' : 'play none none none'
		};
	}

	function beginstand(soort) {
		return {
			omhoog: { opacity: 0, y: afstand },
			vervagen: { opacity: 0 },
			links: { opacity: 0, x: -afstand },
			rechts: { opacity: 0, x: afstand },
			zoom: { opacity: 0, scale: 0.94 }
		}[soort] || { opacity: 0, y: afstand };
	}

	/* ---------- Blokken ---------- */

	document.querySelectorAll('.sectie').forEach(function (sectie) {
		var soort = sectie.getAttribute('data-blokanimatie') || ph.blok || 'omhoog';
		var binnen = sectie.querySelector(':scope > .binnen');
		if (!binnen) return;
		if (soort === 'geen') {
			gsap.set(binnen, { opacity: 1 });
			return;
		}
		var naElkaar = (sectie.getAttribute('data-blokvolgorde') || ph.volgorde) === 'na-elkaar';
		var doelen = naElkaar && binnen.children.length > 1 ? Array.prototype.slice.call(binnen.children) : [binnen];
		if (naElkaar) gsap.set(binnen, { opacity: 1 });

		gsap.fromTo(doelen, beginstand(soort), {
			opacity: 1, x: 0, y: 0, scale: 1,
			duration: duur,
			ease: easing,
			stagger: naElkaar ? (ph.tussenpoos || 0.12) : 0,
			scrollTrigger: trigger(sectie),
			clearProps: 'transform'
		});
	});

	/* ---------- Koppen ---------- */

	var koppen = ph.koppen || {};
	Object.keys(koppen).forEach(function (niveau) {
		var effect = koppen[niveau];
		document.querySelectorAll('.sectie ' + niveau + '.kop').forEach(function (kop) {
			var kopDuur = (ph.kopDuur || 0.9) * (mobiel ? 0.8 : 1);
			var tussen = ph.kopTussenpoos || 0.08;
			var kopEase = ph.kopEasing || 'expo.out';
			var st = trigger(kop.closest('.sectie') || kop);

			if (effect === 'omhoog' || effect === 'vervagen') {
				gsap.fromTo(kop, beginstand(effect), { opacity: 1, y: 0, duration: kopDuur, ease: kopEase, scrollTrigger: st });
				return;
			}
			if (effect === 'onthullen') {
				gsap.set(kop, { opacity: 1 });
				gsap.fromTo(kop, { clipPath: 'inset(0 100% 0 0)' }, { clipPath: 'inset(0 0% 0 0)', duration: kopDuur * 1.4, ease: kopEase, scrollTrigger: st, clearProps: 'clipPath' });
				return;
			}
			if (!window.SplitText) {
				gsap.fromTo(kop, beginstand('omhoog'), { opacity: 1, y: 0, duration: kopDuur, ease: kopEase, scrollTrigger: st });
				return;
			}

			gsap.set(kop, { opacity: 1 });
			SplitText.create(kop, {
				type: effect === 'letters' ? 'words,chars' : (effect === 'regels' ? 'lines' : 'words'),
				mask: effect === 'regels' ? 'lines' : false,
				linesClass: 'kop-regel',
				wordsClass: 'kop-woord',
				charsClass: 'kop-letter',
				autoSplit: true,
				onSplit: function (self) {
					var delen = effect === 'letters' ? self.chars : (effect === 'regels' ? self.lines : self.words);
					/* Accentwoorden als laatste: eerst de gewone delen, dan wat in het accent staat. */
					if (ph.kopAccent === 'na') {
						var gewoon = delen.filter(function (x) { return !x.closest('.accent') && !x.querySelector('.accent'); });
						var accent = delen.filter(function (x) { return gewoon.indexOf(x) === -1; });
						delen = gewoon.concat(accent);
					}
					var van = effect === 'regels' ? { yPercent: 105 } : (effect === 'letters' ? { opacity: 0, yPercent: 60 } : { opacity: 0, y: '0.4em' });
					return gsap.from(delen, Object.assign(van, {
						duration: kopDuur,
						ease: kopEase,
						stagger: effect === 'letters' ? tussen / 3 : tussen,
						scrollTrigger: st
					}));
				}
			});
		});
	});

	/* ---------- Losse elementen (data-animatie) ---------- */

	document.querySelectorAll('[data-animatie]').forEach(function (el) {
		var groep = el.closest('[data-animatie-groep]');
		var index = groep ? Array.prototype.indexOf.call(groep.querySelectorAll('[data-animatie]'), el) : 0;
		gsap.fromTo(el, beginstand(el.dataset.animatie), {
			opacity: 1, x: 0, y: 0, scale: 1,
			duration: duur,
			delay: (parseFloat(el.dataset.animatieVertraging) || 0) + index * 0.08,
			ease: easing,
			scrollTrigger: trigger(el)
		});
	});

	/* Lettertypes en beelden veranderen de hoogtes: posities opnieuw meten. */
	if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ScrollTrigger.refresh(); });
	window.addEventListener('load', function () { ScrollTrigger.refresh(); }, { once: true });

	vrijgeven();
})();
