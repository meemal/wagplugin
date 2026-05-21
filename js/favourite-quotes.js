(function () {
	'use strict';

	function initFavouriteQuotes(root) {
		var slides = root.querySelectorAll('.ftd-fq-slide');

		if (!slides.length) {
			return;
		}

		var autoplay = root.getAttribute('data-autoplay') === '1';
		var interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
		var current = 0;
		var timer = null;

		function show(index) {
			if (index < 0 || index >= slides.length) {
				return;
			}

			current = index;

			slides.forEach(function (slide, i) {
				var active = i === index;
				slide.classList.toggle('is-active', active);
				if (active) {
					slide.removeAttribute('hidden');
				} else {
					slide.setAttribute('hidden', '');
				}
			});
		}

		function next() {
			show((current + 1) % slides.length);
		}

		function startAutoplay() {
			stopAutoplay();
			if (!autoplay || slides.length < 2) {
				return;
			}
			timer = window.setInterval(next, interval);
		}

		function stopAutoplay() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		root.addEventListener('mouseenter', stopAutoplay);
		root.addEventListener('mouseleave', startAutoplay);
		root.addEventListener('focusin', stopAutoplay);
		root.addEventListener('focusout', startAutoplay);

		show(0);
		startAutoplay();
	}

	document.querySelectorAll('[data-ftd-favourite-quotes]').forEach(initFavouriteQuotes);
})();
