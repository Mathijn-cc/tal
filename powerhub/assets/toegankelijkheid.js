/* Powerhub Toegankelijkheid: kleurchecker (WCAG 2.1 contrastverhouding). Vanilla, zonder afhankelijkheden. */
(function () {
	var eisen = {
		'normaal-AA': 4.5,
		'normaal-AAA': 7,
		'groot-AA': 3,
		'groot-AAA': 4.5,
		'ui-AA': 3
	};

	function hexNaarRgb(hex) {
		var h = String(hex || '').trim().replace(/^#/, '').toLowerCase();
		if (/^[0-9a-f]{3}$/.test(h)) h = h.replace(/(.)/g, '$1$1');
		if (!/^[0-9a-f]{6}$/.test(h)) return null;
		return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)];
	}

	function luminantie(rgb) {
		var k = rgb.map(function (c) {
			c = c / 255;
			return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * k[0] + 0.7152 * k[1] + 0.0722 * k[2];
	}

	function verhouding(a, b) {
		var l1 = luminantie(a), l2 = luminantie(b);
		return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
	}

	function normaliseer(hex) {
		var rgb = hexNaarRgb(hex);
		return rgb ? '#' + rgb.map(function (c) { return ('0' + c.toString(16)).slice(-2); }).join('') : null;
	}

	function checker(el) {
		var velden = {};
		el.querySelectorAll('[data-rol]').forEach(function (v) {
			velden[v.dataset.rol] = {
				kiezer: v.querySelector('[data-kiezer]'),
				hex: v.querySelector('[data-hex]'),
				stalen: v.querySelectorAll('[data-kleur]')
			};
		});
		var voorbeeld = el.querySelector('[data-voorbeeld]');
		var uitkomst = el.querySelector('[data-verhouding]');
		var badges = el.querySelectorAll('[data-badge]');

		badges.forEach(function (b) {
			var sr = document.createElement('span');
			sr.className = 'screen-reader-text';
			sr.setAttribute('data-badge-sr', '');
			b.appendChild(sr);
		});

		function waarde(rol) {
			return normaliseer(velden[rol].hex.value) || velden[rol].kiezer.value;
		}

		function bijwerken() {
			var voor = waarde('voor'), achter = waarde('achter');
			voorbeeld.style.color = voor;
			voorbeeld.style.background = achter;
			var r = verhouding(hexNaarRgb(voor), hexNaarRgb(achter));
			/* Zelfde afronding als powerhub_contrast() in PHP, zodat checker en rapport hetzelfde zeggen. */
			var getoond = Math.round(r * 100) / 100;
			uitkomst.textContent = getoond.toFixed(2);
			badges.forEach(function (b) {
				var goed = getoond >= eisen[b.dataset.badge];
				b.classList.toggle('is-goed', goed);
				b.classList.toggle('is-fout', !goed);
				b.querySelector('[data-badge-sr]').textContent = goed ? ': voldoet' : ': voldoet niet';
			});
			Object.keys(velden).forEach(function (rol) {
				var huidig = waarde(rol);
				velden[rol].stalen.forEach(function (s) {
					s.setAttribute('aria-pressed', s.dataset.kleur === huidig ? 'true' : 'false');
				});
			});
		}

		function zet(rol, hex) {
			var n = normaliseer(hex);
			if (!n) return;
			velden[rol].kiezer.value = n;
			velden[rol].hex.value = n;
			velden[rol].hex.removeAttribute('aria-invalid');
			bijwerken();
		}

		Object.keys(velden).forEach(function (rol) {
			var v = velden[rol];
			v.kiezer.addEventListener('input', function () { zet(rol, v.kiezer.value); });
			v.hex.addEventListener('input', function () {
				var n = normaliseer(v.hex.value);
				if (n) {
					v.hex.removeAttribute('aria-invalid');
					v.kiezer.value = n;
					bijwerken();
				} else {
					v.hex.setAttribute('aria-invalid', 'true');
				}
			});
			v.hex.addEventListener('blur', function () { if (normaliseer(v.hex.value)) zet(rol, v.hex.value); });
			v.stalen.forEach(function (s) {
				s.addEventListener('click', function () { zet(rol, s.dataset.kleur); });
			});
		});

		var omdraaien = el.querySelector('[data-omdraaien]');
		if (omdraaien) {
			omdraaien.addEventListener('click', function () {
				var voor = waarde('voor'), achter = waarde('achter');
				zet('voor', achter);
				zet('achter', voor);
			});
		}

		bijwerken();
	}

	function start() { document.querySelectorAll('[data-kleurchecker]').forEach(checker); }
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
	else start();
})();
