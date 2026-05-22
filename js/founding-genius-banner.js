(function () {
	'use strict';

	function easeOutCubic(t) {
		return 1 - Math.pow(1 - t, 3);
	}

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	function animateValue(from, to, duration, onUpdate, onComplete) {
		var start = null;

		function frame(timestamp) {
			if (start === null) {
				start = timestamp;
			}

			var progress = Math.min(1, (timestamp - start) / duration);
			var value = Math.round(from + (to - from) * easeOutCubic(progress));

			onUpdate(value);

			if (progress < 1) {
				window.requestAnimationFrame(frame);
				return;
			}

			onUpdate(to);

			if (typeof onComplete === 'function') {
				onComplete();
			}
		}

		window.requestAnimationFrame(frame);
	}

	function setFinalState(banner) {
		var counters = banner.querySelectorAll('[data-fg-count-to]');
		var i;

		for (i = 0; i < counters.length; i++) {
			counters[i].textContent = counters[i].getAttribute('data-fg-count-to');
		}

		var fill = banner.querySelector('.fg-fill');

		if (fill) {
			fill.style.width = fill.getAttribute('data-fg-percent') + '%';
		}

		banner.classList.add('fg-has-animated');
	}

	function animateBanner(banner) {
		if (banner.classList.contains('fg-has-animated')) {
			return;
		}

		if (prefersReducedMotion()) {
			setFinalState(banner);
			return;
		}

		banner.classList.add('fg-is-animating');

		var counters = banner.querySelectorAll('[data-fg-count-to]');
		var fill = banner.querySelector('.fg-fill');
		var percent = fill ? parseFloat(fill.getAttribute('data-fg-percent') || '0') : 0;
		var duration = 1700;
		var pending = counters.length + (fill ? 1 : 0);

		function stepDone() {
			pending -= 1;

			if (pending <= 0) {
				banner.classList.remove('fg-is-animating');
				banner.classList.add('fg-has-animated');

				if (fill) {
					var track = banner.querySelector('.fg-track');

					if (track) {
						track.setAttribute('aria-valuenow', track.getAttribute('data-fg-used') || '0');
					}
				}
			}
		}

		for (var i = 0; i < counters.length; i++) {
			(function (el) {
				var target = parseInt(el.getAttribute('data-fg-count-to'), 10) || 0;

				animateValue(0, target, duration, function (value) {
					el.textContent = String(value);
				}, stepDone);
			})(counters[i]);
		}

		if (fill) {
			window.requestAnimationFrame(function () {
				fill.style.width = Math.min(100, Math.max(0, percent)) + '%';
			});

			window.setTimeout(stepDone, duration);
		}
	}

	function initBanner(banner) {
		if (prefersReducedMotion()) {
			setFinalState(banner);
			return;
		}

		if (!('IntersectionObserver' in window)) {
			setFinalState(banner);
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}

					observer.unobserve(entry.target);
					animateBanner(entry.target);
				});
			},
			{
				threshold: 0.35,
				rootMargin: '0px 0px -5% 0px'
			}
		);

		observer.observe(banner);
	}

	function init() {
		var banners = document.querySelectorAll('.ftd-sc--founding-genius .fg-banner[data-fg-animate]');

		for (var i = 0; i < banners.length; i++) {
			initBanner(banners[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
