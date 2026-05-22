(function ($) {
	'use strict';

	var CAPTURE_WIDTH = 1280;
	var CAPTURE_HEIGHT = 720;
	var CAPTURE_SCALE = 2;

	function setStatus(message) {
		$('#ftd-gnls-cta-capture-status').text(message || '');
	}

	function waitForCaptureAssets(doc, target) {
		var waits = [];

		if (doc.fonts && doc.fonts.ready) {
			waits.push(doc.fonts.ready);
		}

		target.querySelectorAll('img').forEach(function (img) {
			if (img.complete && img.naturalWidth > 0) {
				return;
			}

			waits.push(
				new Promise(function (resolve) {
					img.addEventListener('load', resolve, { once: true });
					img.addEventListener('error', resolve, { once: true });
				})
			);
		});

		return Promise.all(waits).then(function () {
			return new Promise(function (resolve) {
				window.setTimeout(resolve, 250);
			});
		});
	}

	function downscaleCanvas(sourceCanvas) {
		var output = document.createElement('canvas');
		output.width = CAPTURE_WIDTH;
		output.height = CAPTURE_HEIGHT;

		var ctx = output.getContext('2d');
		ctx.imageSmoothingEnabled = true;
		ctx.imageSmoothingQuality = 'high';
		ctx.drawImage(sourceCanvas, 0, 0, CAPTURE_WIDTH, CAPTURE_HEIGHT);

		return output;
	}

	$('#ftd-gnls-cta-capture-btn').on('click', function () {
		var $btn = $(this);
		var postId = $btn.data('post-id');
		var nonce = $btn.data('nonce');
		var previewUrl = $btn.data('preview-url');
		var $frame = $('#ftd-gnls-cta-capture-frame');

		if (!postId || !previewUrl || typeof html2canvas !== 'function') {
			setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.error) || 'Error');
			return;
		}

		$btn.prop('disabled', true);
		setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.working) || 'Working…');

		$frame.off('load').on('load', function () {
			var doc = $frame[0].contentDocument || $frame[0].contentWindow.document;
			var target = doc.getElementById('gnls-cta-capture-target');
			var card = target ? target.querySelector('.gnls-cta--capture') : null;

			if (!card) {
				$btn.prop('disabled', false);
				setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.error) || 'Error');
				return;
			}

			waitForCaptureAssets(doc, card)
				.then(function () {
					return html2canvas(card, {
						backgroundColor: '#111111',
						scale: CAPTURE_SCALE,
						useCORS: true,
						allowTaint: false,
						logging: false,
						imageTimeout: 20000,
						scrollX: 0,
						scrollY: 0
					});
				})
				.then(downscaleCanvas)
				.then(function (canvas) {
					return $.ajax({
						url: ftdGnlsCtaCapture.ajaxUrl,
						method: 'POST',
						data: {
							action: 'ftd_save_gnls_cta_thumbnail',
							post_id: postId,
							nonce: nonce,
							image: canvas.toDataURL('image/png')
						}
					});
				})
				.then(function (response) {
					if (!response || !response.success) {
						throw new Error((response && response.data && response.data.message) || 'Error');
					}

					setStatus(response.data.message || ftdGnlsCtaCapture.i18n.done);

					if (response.data.url) {
						var $box = $btn.closest('.inside');
						$box.find('p img').remove();
						$('<p><img src="' + response.data.url + '" alt="" style="max-width:100%;height:auto;" /></p>').insertAfter(
							$box.find('.description').first()
						);
					}
				})
				.catch(function () {
					setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.error) || 'Error');
				})
				.finally(function () {
					$btn.prop('disabled', false);
				});
		});

		$frame.attr('src', previewUrl);
	});
})(jQuery);
