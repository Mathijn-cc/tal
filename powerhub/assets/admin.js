/* Powerhub: tabs, kleurvelden en beeldkiezers. */
(function ($) {
	var hub = document.querySelector('[data-powerhub]');
	if (!hub) return;
	document.documentElement.classList.add('js');

	var knoppen = hub.querySelectorAll('[data-tab]');
	var panelen = hub.querySelectorAll('[data-paneel]');

	/* Groepen (submenu): openen en sluiten; de groep met de actieve tab gaat vanzelf open. */
	function groepOpen(groep, open) {
		var knop = groep.querySelector('.powerhub-groep-knop');
		groep.classList.toggle('is-open', open);
		knop.setAttribute('aria-expanded', open ? 'true' : 'false');
		groep.querySelector('.powerhub-groep-tabs').hidden = !open;
	}
	hub.querySelectorAll('[data-groep]').forEach(function (groep) {
		groep.querySelector('.powerhub-groep-knop').addEventListener('click', function () {
			var open = !groep.classList.contains('is-open');
			groepOpen(groep, open);
			if (open) {
				var eerste = groep.querySelector('[data-tab]');
				if (eerste) { toon(eerste.dataset.tab, true); }
			}
		});
	});

	function toon(tab, bijwerken) {
		hub.querySelectorAll('[data-groep]').forEach(function (groep) {
			if (groep.querySelector('[data-tab="' + tab + '"]')) { groepOpen(groep, true); }
		});
		knoppen.forEach(function (k) {
			var actief = k.dataset.tab === tab;
			k.setAttribute('aria-selected', actief ? 'true' : 'false');
			k.tabIndex = actief ? 0 : -1;
		});
		panelen.forEach(function (p) { p.hidden = p.dataset.paneel !== tab; });
		if (typeof maat === 'function') { maat(); }
		if (bijwerken) {
			/* Altijd bovenaan de instellingen beginnen: het paneel zelf naar boven, en de pagina tot het paneel in beeld is. */
			var paneel = hub.querySelector('[data-paneel="' + tab + '"]');
			if (paneel) {
				paneel.scrollTop = 0;
				var boven = paneel.getBoundingClientRect().top;
				if (boven < 0 || boven > window.innerHeight * 0.5) {
					var stil = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
					window.scrollTo({ top: window.scrollY + boven - 48, behavior: stil ? 'auto' : 'smooth' });
				}
			}
			var url = new URL(window.location.href);
			url.searchParams.set('tab', tab);
			url.searchParams.delete('melding');
			window.history.replaceState(null, '', url);
		}
	}

	/* Zijbalk en paneel zijn precies zo hoog als de ruimte eronder in beeld, zodat Opslaan altijd zichtbaar is. */
	var breed = window.matchMedia('(min-width: 961px)');
	var tabsLijst = hub.querySelector('.powerhub-tabs');
	var wacht = false;
	function maat() {
		wacht = false;
		var vakken = [tabsLijst].concat(Array.prototype.filter.call(panelen, function (p) { return !p.hidden; }));
		vakken.forEach(function (el) {
			if (!el) return;
			if (!breed.matches) { el.style.maxHeight = ''; return; }
			var boven = Math.max(48, el.getBoundingClientRect().top);
			el.style.maxHeight = Math.max(240, window.innerHeight - boven - 16) + 'px';
		});
	}
	function maatLater() {
		if (!wacht) { wacht = true; window.requestAnimationFrame(maat); }
	}
	window.addEventListener('scroll', maatLater, { passive: true });
	window.addEventListener('resize', maatLater);
	window.addEventListener('load', maatLater);
	if (window.ResizeObserver) { new ResizeObserver(maatLater).observe(hub); }

	knoppen.forEach(function (k, i) {
		k.addEventListener('click', function () { toon(k.dataset.tab, true); });
		k.addEventListener('keydown', function (e) {
			var richting = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
			if (!richting) return;
			e.preventDefault();
			var zichtbaar = Array.prototype.filter.call(knoppen, function (x) { return !x.closest('[hidden]'); });
			var j = zichtbaar.indexOf(k);
			var volgende = zichtbaar[(j + richting + zichtbaar.length) % zichtbaar.length];
			volgende.focus();
			toon(volgende.dataset.tab, true);
		});
	});
	toon(hub.dataset.actief || knoppen[0].dataset.tab, false);

	/* Kleur: de hexcode naast het veld meeschrijven. */
	hub.querySelectorAll('[data-kleur]').forEach(function (veld) {
		var code = veld.parentNode.querySelector('[data-kleur-code]');
		veld.addEventListener('input', function () { code.textContent = veld.value; });
	});

	/* Beeld: mediabibliotheek openen. */
	$(hub).on('click', '[data-beeld-kies]', function () {
		var veld = $(this).closest('[data-beeld]');
		var kader = wp.media({ title: 'Beeld kiezen', multiple: false, library: { type: 'image' }, button: { text: 'Gebruiken' } });
		kader.on('select', function () {
			var b = kader.state().get('selection').first().toJSON();
			var url = b.sizes && b.sizes.medium ? b.sizes.medium.url : b.url;
			veld.find('input[type="hidden"]').val(b.id);
			veld.find('[data-beeld-voorbeeld]').html($('<img alt="">').attr('src', url));
		});
		kader.open();
	});

	$(hub).on('click', '[data-beeld-wis]', function () {
		var veld = $(this).closest('[data-beeld]');
		veld.find('input[type="hidden"]').val(0);
		veld.find('[data-beeld-voorbeeld]').empty();
	});

	/* Bevestigen voor gevaarlijke acties. */
	$(hub).on('submit', '[data-bevestig]', function (e) {
		if (!window.confirm(this.dataset.bevestig)) e.preventDefault();
	});
})(jQuery);
