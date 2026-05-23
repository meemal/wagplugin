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
			window.prompt('Copy this message:', text);
		}

		document.body.removeChild(area);
	}

	function extensionForBlob(blob) {
		if (!blob || !blob.type) {
			return 'jpg';
		}

		if (blob.type.indexOf('png') !== -1) {
			return 'png';
		}

		if (blob.type.indexOf('webp') !== -1) {
			return 'webp';
		}

		return 'jpg';
	}

	function shareWithImage(link) {
		var text = link.getAttribute('data-share-text') || '';
		var imageUrl = link.getAttribute('data-share-image');

		if (!imageUrl || !navigator.share) {
			return Promise.resolve(false);
		}

		return fetch(imageUrl)
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Image fetch failed');
				}

				return response.blob();
			})
			.then(function (blob) {
				var file = new File(
					[blob],
					'live-session.' + extensionForBlob(blob),
					{ type: blob.type || 'image/jpeg' }
				);
				var shareData = {
					text: text,
					files: [file]
				};

				if (!navigator.canShare || !navigator.canShare(shareData)) {
					return false;
				}

				return navigator.share(shareData).then(function () {
					return true;
				});
			})
			.catch(function () {
				return false;
			});
	}

	document.addEventListener('click', function (event) {
		var whatsappLink = event.target.closest('.gcc-share-whatsapp');

		if (whatsappLink && whatsappLink.getAttribute('data-share-image')) {
			event.preventDefault();

			shareWithImage(whatsappLink).then(function (shared) {
				if (!shared) {
					window.open(whatsappLink.getAttribute('href'), '_blank', 'noopener,noreferrer');
				}
			});

			return;
		}

		var button = event.target.closest('.gcc-share-copy');

		if (!button) {
			return;
		}

		event.preventDefault();

		var text = button.getAttribute('data-copy-text') || button.getAttribute('data-copy-url');

		if (!text) {
			return;
		}

		var label = button.querySelector('.ftd-ss-pill-label');
		var defaultText = label ? label.textContent : '';

		copyText(text, function () {
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
