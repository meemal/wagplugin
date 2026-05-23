(function () {
	'use strict';

	function copyText(text, onSuccess) {
		if (!text) {
			return;
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(onSuccess).catch(function () {
				fallbackCopy(text, onSuccess);
			});
			return;
		}

		fallbackCopy(text, onSuccess);
	}

	function fallbackCopy(text, onSuccess) {
		var area = document.createElement('textarea');
		area.value = text;
		area.setAttribute('readonly', '');
		area.style.position = 'fixed';
		area.style.left = '-9999px';
		document.body.appendChild(area);
		area.select();

		try {
			document.execCommand('copy');
			onSuccess();
		} catch (err) {
			window.prompt('Copy this link:', text);
		}

		document.body.removeChild(area);
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.gcc-share-copy');

		if (!button) {
			return;
		}

		event.preventDefault();

		var url = button.getAttribute('data-copy-url');

		if (!url) {
			return;
		}

		var label = button.querySelector('.ftd-ss-pill-label');
		var defaultText = label ? label.textContent : '';

		copyText(url, function () {
			button.classList.add('is-copied');

			if (label) {
				label.textContent = 'Copied!';
			}

			window.setTimeout(function () {
				button.classList.remove('is-copied');

				if (label) {
					label.textContent = defaultText;
				}
			}, 2000);
		});
	});
})();
