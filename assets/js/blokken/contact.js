/* Blok Contact: de agenda van Calendly laadt pas als hij bijna in beeld is (of direct bij een link naar #kennismaking).
   Zo staat er geen script van Calendly op de pagina voor bezoekers die er niet komen. */
(function () {
	var agendas = document.querySelectorAll('.sectie-contact .calendly-inline-widget');
	if (!agendas.length) return;

	var geladen = false;
	function laden() {
		if (geladen) return;
		geladen = true;
		var script = document.createElement('script');
		script.src = 'https://assets.calendly.com/assets/external/widget.js';
		script.async = true;
		document.head.appendChild(script);
	}

	var doel = window.location.hash ? document.getElementById(decodeURIComponent(window.location.hash.slice(1))) : null;
	if (doel && doel.classList.contains('contact-agenda')) {
		laden();
		return;
	}
	if (!('IntersectionObserver' in window)) {
		laden();
		return;
	}
	var kijker = new IntersectionObserver(function (items) {
		items.forEach(function (item) {
			if (item.isIntersecting) {
				laden();
				kijker.disconnect();
			}
		});
	}, { rootMargin: '600px 0px' });
	Array.prototype.forEach.call(agendas, function (el) { kijker.observe(el); });
})();
