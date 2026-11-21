/* Designsysteem: laat zien welke tokens in Powerhub zijn overschreven, en markeert het actieve hoofdstuk. */
(function () {
	var stijl = getComputedStyle(document.documentElement);

	function normaal(waarde) {
		var proef = document.createElement('span');
		proef.style.color = waarde;
		document.body.appendChild(proef);
		var uit = getComputedStyle(proef).color;
		proef.remove();
		return uit;
	}

	/* Paletstalen: vergelijk de waarde uit tokens.json met wat de pagina werkelijk gebruikt. */
	document.querySelectorAll('.designsysteem-staal[data-token]').forEach(function (staal) {
		var echt = stijl.getPropertyValue(staal.dataset.token).trim();
		if (echt && normaal(echt) !== normaal(staal.dataset.waarde)) {
			var melding = staal.querySelector('[data-aangepast]');
			melding.querySelector('[data-aangepast-waarde]').textContent = echt;
			melding.hidden = false;
		}
	});

	/* Actief hoofdstuk in het menu. */
	var links = {};
	document.querySelectorAll('.designsysteem-menu ol a').forEach(function (a) { links[a.getAttribute('href').slice(1)] = a; });
	if (!('IntersectionObserver' in window)) return;
	var waarnemer = new IntersectionObserver(function (items) {
		items.forEach(function (item) {
			if (!item.isIntersecting || !links[item.target.id]) return;
			Object.keys(links).forEach(function (k) { links[k].classList.remove('is-actief'); });
			links[item.target.id].classList.add('is-actief');
		});
	}, { rootMargin: '-20% 0px -70% 0px' });
	document.querySelectorAll('.designsysteem-hoofdstuk[id]').forEach(function (h) { waarnemer.observe(h); });
})();
