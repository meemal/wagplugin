<?php
/**
 * Copy-link handler for live session share buttons.
 */

(function () {
	document.addEventListener('click', function (event) {
		var button = event.target.closest('.gcc-share-copy');

		if (!button) {
			return;
		}

		var url = button.getAttribute('data-copy-url');

		if (!url) {
			return;
		}

		var label = button.querySelector('.ftd-ss-pill-label');
		var defaultText = label ? label.textContent : '';

		function markCopied() {
			if (label) {
				label.textContent = 'Copied!';
				window.setTimeout(function () {
					label.textContent = defaultText;
				}, 2000);
			}
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(markCopied).catch(function () {
				window.prompt('Copy this link:', url);
			});
			return;
		}

		window.prompt('Copy this link:', url);
	});
})();
