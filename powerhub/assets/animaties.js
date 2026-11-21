/* Powerhub: basisanimaties. Projecteigen animaties horen in assets/js van het thema. */
(function () {
	var d = document.documentElement;
	var instellingen = window.powerhub || {};
	var rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function vrijgeven() { d.classList.remove('animatie-wacht'); }

	if (!instellingen.animaties || rustig || !window.gsap || !window.ScrollTrigger) {
		vrijgeven();
		return;
	}

	gsap.registerPlugin(ScrollTrigger);

	if (instellingen.vloeiend && window.Lenis) {
		var lenis = new Lenis();
		window.powerhubLenis = lenis;
		lenis.on('scroll', ScrollTrigger.update);
		gsap.ticker.add(function (tijd) { lenis.raf(tijd * 1000); });
		gsap.ticker.lagSmoothing(0);
	}

	var afstand = instellingen.afstand || 24;
	var beginstand = {
		omhoog: { y: afstand },
		vervagen: {},
		links: { x: -afstand },
		rechts: { x: afstand }
	};

	document.querySelectorAll('[data-animatie]').forEach(function (el) {
		var soort = beginstand[el.dataset.animatie] ? el.dataset.animatie : 'omhoog';
		var groep = el.closest('[data-animatie-groep]');
		var index = groep ? Array.prototype.indexOf.call(groep.querySelectorAll('[data-animatie]'), el) : 0;
		var vanaf = Object.assign({ opacity: 0 }, beginstand[soort]);

		gsap.fromTo(el, vanaf, {
			opacity: 1,
			x: 0,
			y: 0,
			duration: instellingen.duur || 0.8,
			delay: (parseFloat(el.dataset.animatieVertraging) || 0) + index * 0.08,
			ease: 'power3.out',
			scrollTrigger: { trigger: el, start: 'top 88%', once: true }
		});
	});

	vrijgeven();
})();
