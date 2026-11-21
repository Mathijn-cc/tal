/* Front-end van het thema. Accordeon en tabs; projecteigen gedrag hieronder toevoegen. */
(function () {
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
})();
