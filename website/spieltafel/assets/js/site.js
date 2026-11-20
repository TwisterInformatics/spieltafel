/* Spieltafel: Menü auf dem Handy und Spielsuche wie in der App. */
(function () {
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('hauptmenue');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	function normalize(s) {
		return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
	}

	document.querySelectorAll('[data-games]').forEach(function (list) {
		var root = list.parentElement;
		var search = root.querySelector('[data-game-search]');
		var chips = root.querySelector('[data-game-chips]');
		var empty = root.querySelector('[data-games-empty]');
		var cards = Array.prototype.slice.call(list.querySelectorAll('.game-card'));
		var category = '';
		if (!search || !chips) return;
		search.hidden = false;
		chips.hidden = false;

		function update() {
			var words = normalize(search.value).split(/\s+/).filter(Boolean);
			var shown = 0;
			cards.forEach(function (card) {
				var hay = normalize(card.dataset.search);
				var ok = (!category || card.dataset.category === category) &&
					words.every(function (w) { return hay.indexOf(w) !== -1; });
				card.hidden = !ok;
				if (ok) shown++;
			});
			if (empty) empty.hidden = shown > 0;
		}

		search.addEventListener('input', update);
		chips.addEventListener('click', function (e) {
			var chip = e.target.closest('.chip');
			if (!chip) return;
			category = chip.dataset.category;
			chips.querySelectorAll('.chip').forEach(function (c) {
				c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
			});
			update();
		});
	});
})();
