/* Spieltreff: Spamschutz (Rechenaufgabe im Browser), Spielauswahl, Rückfragen. */
(function () {
	'use strict';

	/* Spamschutz: Aufgabe holen, Zahl suchen, deren SHA-256 zur Aufgabe passt. */
	function hex(buffer) {
		var bytes = new Uint8Array(buffer);
		var out = '';
		for (var i = 0; i < bytes.length; i++) {
			out += ('0' + bytes[i].toString(16)).slice(-2);
		}
		return out;
	}

	async function solve(task) {
		var enc = new TextEncoder();
		var batch = 200;
		for (var n = 0; n <= task.maxnumber; n += batch) {
			var jobs = [];
			for (var k = n; k < n + batch && k <= task.maxnumber; k++) {
				jobs.push(crypto.subtle.digest('SHA-256', enc.encode(task.salt + k)));
			}
			var hashes = await Promise.all(jobs);
			for (var j = 0; j < hashes.length; j++) {
				if (hex(hashes[j]) === task.challenge) {
					return n + j;
				}
			}
		}
		return -1;
	}

	function initCaptcha(box) {
		var form = box.closest('form');
		var input = box.querySelector('input[name="st_pow"]');
		var text = box.querySelector('.captcha-text');
		var buttons = form ? form.querySelectorAll('[data-needs-pow], button[type="submit"]') : [];
		var started = false;

		function set(state) {
			box.dataset.state = state;
			text.textContent = text.dataset[state] || '';
			buttons.forEach(function (b) { b.disabled = state !== 'done'; });
		}

		function fail() {
			set('fail');
		}

		function run() {
			if (started) return;
			started = true;
			set('wait');
			if (!window.crypto || !crypto.subtle) {
				fail();
				return;
			}
			fetch(box.dataset.pow, { credentials: 'same-origin', cache: 'no-store' })
				.then(function (r) { return r.json(); })
				.then(function (task) {
					return solve(task).then(function (number) {
						if (number < 0) throw new Error('pow');
						input.value = btoa(JSON.stringify({
							algorithm: task.algorithm,
							challenge: task.challenge,
							number: number,
							salt: task.salt,
							signature: task.signature
						}));
						set('done');
					});
				})
				.catch(fail);
		}

		buttons.forEach(function (b) { b.disabled = true; });
		// Erst rechnen, wenn jemand das Formular benutzt, oder spätestens nach kurzer Zeit.
		if (form) {
			form.addEventListener('focusin', run, { once: true });
			form.addEventListener('pointerdown', run, { once: true });
		}
		setTimeout(run, 1500);
	}

	document.querySelectorAll('.captcha[data-pow]').forEach(initCaptcha);

	/* Spielauswahl mit Suche. */
	document.querySelectorAll('[data-game-picker]').forEach(function (picker) {
		var search = picker.querySelector('[data-picker-search]');
		var items = Array.prototype.slice.call(picker.querySelectorAll('.st-game'));
		if (!search) return;
		search.hidden = false;
		function norm(s) {
			return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
		}
		search.addEventListener('input', function () {
			var q = norm(search.value);
			items.forEach(function (item) {
				var checked = item.querySelector('input').checked;
				item.hidden = q !== '' && !checked && item.dataset.search.indexOf(q) === -1;
			});
		});
		search.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') e.preventDefault();
		});
	});

	/* Felder ein- und ausblenden (z. B. Mindestanzahl). */
	document.querySelectorAll('[data-toggle]').forEach(function (box) {
		var target = document.querySelector(box.dataset.toggle);
		if (!target) return;
		function update() {
			target.hidden = !box.checked;
		}
		box.addEventListener('change', update);
		update();
	});

	/* Teilen: Link kopieren und Bild für Instagram. */
	function copy(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).catch(function () {});
		}
		var area = document.createElement('textarea');
		area.value = text;
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild(area);
		area.select();
		try { document.execCommand('copy'); } catch (e) { /* nichts */ }
		document.body.removeChild(area);
		return Promise.resolve();
	}

	document.querySelectorAll('[data-share]').forEach(function (box) {
		var url = box.dataset.url;
		var copyBtn = box.querySelector('[data-copy-link]');
		var igBtn = box.querySelector('[data-share-image]');
		var hint = box.querySelector('[data-ig-hint]');

		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				copy(url).then(function () {
					var label = copyBtn.querySelector('span');
					var old = label.textContent;
					label.textContent = copyBtn.dataset.done;
					setTimeout(function () { label.textContent = old; }, 2000);
				});
			});
		}

		if (igBtn) {
			igBtn.addEventListener('click', function () {
				igBtn.disabled = true;
				fetch(igBtn.dataset.shareImage, { credentials: 'same-origin' })
					.then(function (r) {
						if (!r.ok) throw new Error('bild');
						return r.blob();
					})
					.then(function (blob) {
						var file = new File([blob], 'spieltreff.png', { type: 'image/png' });
						// Auf dem Handy: Teilen-Menü mit Bild, dort Instagram (Story oder Beitrag) wählen.
						if (navigator.canShare && navigator.canShare({ files: [file] })) {
							copy(url);
							return navigator.share({ files: [file], title: box.dataset.title, text: url }).catch(function () {});
						}
						// Am Computer: Bild herunterladen, Link kopieren, Hinweis zeigen.
						var a = document.createElement('a');
						a.href = URL.createObjectURL(blob);
						a.download = 'spieltreff-instagram.png';
						document.body.appendChild(a);
						a.click();
						a.remove();
						copy(url);
						if (hint) hint.hidden = false;
					})
					.catch(function () { window.open(igBtn.dataset.shareImage, '_blank'); })
					.then(function () { igBtn.disabled = false; });
			});
		}
	});

	/* Rückfrage vor Abmelden, Absagen und Löschen. */
	document.querySelectorAll('form[data-confirm]').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			if (!window.confirm(form.dataset.confirm)) {
				e.preventDefault();
			}
		});
	});
})();
