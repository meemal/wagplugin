(function () {
	'use strict';

	function initSocialShare(root) {
		var dataEl = root.querySelector('#ftd-social-share-data');
		if (!dataEl) {
			return;
		}

		var slides;
		try {
			slides = JSON.parse(dataEl.textContent);
		} catch (e) {
			return;
		}

		if (!slides || !slides.length) {
			return;
		}

		var tabs = root.querySelectorAll('.ftd-ss-tab');
		var panels = root.querySelectorAll('.ftd-ss-panel');
		var copyBtn = root.querySelector('[data-ftd-ss-copy]');
		var shareLinks = root.querySelectorAll('[data-share]');
		var copyLabel = copyBtn ? copyBtn.querySelector('.ftd-ss-pill-label') : null;
		var copyDefault = copyLabel ? copyLabel.textContent : 'Copy';
		var affiliateLink = root.getAttribute('data-affiliate-url') || '';

		var current = 0;

		function updateShareLinks(index) {
			var slide = slides[index];
			if (!slide || !slide.urls) {
				return;
			}

			shareLinks.forEach(function (link) {
				var key = link.getAttribute('data-share');
				if (slide.urls[key]) {
					link.setAttribute('href', slide.urls[key]);
				}
			});
		}

		function show(index) {
			if (index < 0 || index >= slides.length) {
				return;
			}

			current = index;

			tabs.forEach(function (tab, i) {
				var active = i === index;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
			});

			panels.forEach(function (panel, i) {
				var active = i === index;
				panel.classList.toggle('is-active', active);
				if (active) {
					panel.removeAttribute('hidden');
				} else {
					panel.setAttribute('hidden', '');
				}
			});

			updateShareLinks(index);
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var index = parseInt(tab.getAttribute('data-index'), 10);
				show(index);
			});
		});

		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				var slide = slides[current];
				if (!slide || !slide.text) {
					return;
				}

				var text = slide.text;
				if (affiliateLink) {
					text = text + '\n\n' + affiliateLink;
				}

				var done = function () {
					copyBtn.classList.add('is-copied');
					if (copyLabel) {
						copyLabel.textContent = 'Copied!';
					}
					window.setTimeout(function () {
						copyBtn.classList.remove('is-copied');
						if (copyLabel) {
							copyLabel.textContent = copyDefault;
						}
					}, 2000);
				};

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(done).catch(fallbackCopy);
				} else {
					fallbackCopy();
				}

				function fallbackCopy() {
					var area = document.createElement('textarea');
					area.value = text;
					area.setAttribute('readonly', '');
					area.style.position = 'fixed';
					area.style.left = '-9999px';
					document.body.appendChild(area);
					area.select();
					try {
						document.execCommand('copy');
						done();
					} catch (err) {
						// ignore
					}
					document.body.removeChild(area);
				}
			});
		}

		root.querySelectorAll('[data-ftd-ss-close]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				root.classList.remove('is-open');
			});
		});

		show(0);
	}

	document.querySelectorAll('[data-ftd-social-share]').forEach(initSocialShare);
})();
