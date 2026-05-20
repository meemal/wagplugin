(function () {
	'use strict';

	function initTicker(root) {
		var slides = root.querySelectorAll('.ftd-st-slide');
		var dots = root.querySelectorAll('.ftd-st-dot');

		if (!slides.length) {
			return;
		}

		var interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
		var current = 0;
		var timer = null;

		function show(index) {
			if (index < 0 || index >= slides.length) {
				return;
			}

			current = index;

			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === index);
			});

			dots.forEach(function (dot, i) {
				var active = i === index;
				dot.classList.toggle('is-active', active);
				dot.setAttribute('aria-selected', active ? 'true' : 'false');
			});
		}

		function next() {
			show((current + 1) % slides.length);
		}

		function start() {
			stop();
			timer = window.setInterval(next, interval);
		}

		function stop() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var index = parseInt(dot.getAttribute('data-slide'), 10);
				show(index);
				start();
			});
		});

		root.addEventListener('mouseenter', stop);
		root.addEventListener('mouseleave', start);
		root.addEventListener('focusin', stop);
		root.addEventListener('focusout', start);

		show(0);
		start();
	}

	document.querySelectorAll('[data-ftd-stats-ticker]').forEach(initTicker);
})();
