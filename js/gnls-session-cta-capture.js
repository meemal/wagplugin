(function ($) {
	'use strict';

	function setStatus($target, message) {
		if ($target && $target.length) {
			$target.text(message || '');
		}
	}

	function waitForStylesheets(doc) {
		var links = doc.querySelectorAll('link[rel="stylesheet"]');
		var waits = [];

		links.forEach(function (link) {
			if (link.sheet) {
				return;
			}

			waits.push(
				new Promise(function (resolve) {
					link.addEventListener('load', resolve, { once: true });
					link.addEventListener('error', resolve, { once: true });
				})
			);
		});

		return Promise.all(waits);
	}

	function waitForCaptureAssets(doc, root, card) {
		var waits = [waitForStylesheets(doc)];

		if (doc.fonts && doc.fonts.load) {
			waits.push(doc.fonts.load('700 68px Jost'));
			waits.push(doc.fonts.load('700 62px Jost'));
			waits.push(doc.fonts.load('600 16px Roboto'));
			waits.push(doc.fonts.load('400 15px Roboto'));
		}

		if (doc.fonts && doc.fonts.ready) {
			waits.push(doc.fonts.ready);
		}

		root.querySelectorAll('img').forEach(function (img) {
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
			return waitForPosterLayout(card);
		});
	}

	function waitForPosterLayout(card) {
		return new Promise(function (resolve) {
			var attempts = 0;
			var maxAttempts = 12;

			function check() {
				var title = card.querySelector('.gnls-cta-title');
				var inner = card.querySelector('.gnls-cta-poster-inner');
				var ready = title && title.offsetHeight > 0 && inner && inner.offsetHeight > 0;

				if (ready || attempts >= maxAttempts) {
					window.requestAnimationFrame(function () {
						window.requestAnimationFrame(resolve);
					});
					return;
				}

				attempts += 1;
				window.setTimeout(check, 400);
			}

			check();
		});
	}

	function normalizeCanvas(sourceCanvas, outputWidth, outputHeight) {
		if (sourceCanvas.width === outputWidth && sourceCanvas.height === outputHeight) {
			return sourceCanvas;
		}

		var output = document.createElement('canvas');
		output.width = outputWidth;
		output.height = outputHeight;

		var ctx = output.getContext('2d');
		ctx.imageSmoothingEnabled = true;
		ctx.imageSmoothingQuality = 'high';
		ctx.drawImage(
			sourceCanvas,
			0,
			0,
			sourceCanvas.width,
			sourceCanvas.height,
			0,
			0,
			outputWidth,
			outputHeight
		);

		return output;
	}

	function updatePreview($wrap, previewTarget, url) {
		if (!url || !previewTarget) {
			return;
		}

		var label = previewTarget.indexOf('social') !== -1 ? 'Social' : 'YouTube';
		var className = previewTarget.replace('.', '');

		$wrap.find(previewTarget).remove();
		$(
			'<p class="' +
				className +
				'"><strong>' +
				label +
				'</strong><br><img src="' +
				url +
				'" alt="" style="max-width:100%;height:auto;border:1px solid #ddd;" /></p>'
		).appendTo($wrap.find('.ftd-gnls-promo-previews'));
	}

	function getCaptureSteps($btn) {
		var steps = null;
		var raw = $btn.attr('data-steps');

		if (raw) {
			try {
				steps = JSON.parse(raw);
			} catch (e) {
				steps = null;
			}
		}

		if ((!steps || !steps.length) && window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.steps) {
			steps = ftdGnlsCtaCapture.steps;
		}

		return steps;
	}

	function captureStep($frame, step) {
		return new Promise(function (resolve, reject) {
			$frame.off('load').on('load', function () {
				var doc = $frame[0].contentDocument || $frame[0].contentWindow.document;
				var target = doc.getElementById('gnls-cta-capture-target');
				var card = target ? target.querySelector(step.cardSelector) : null;

				if (!target || !card) {
					reject(new Error('Error'));
					return;
				}

				waitForCaptureAssets(doc, target, card)
					.then(function () {
						return html2canvas(target, {
							backgroundColor: null,
							scale: 1,
							width: step.renderWidth,
							height: step.renderHeight,
							useCORS: true,
							allowTaint: false,
							logging: false,
							imageTimeout: 20000,
							scrollX: 0,
							scrollY: 0
						});
					})
					.then(function (canvas) {
						return normalizeCanvas(canvas, step.renderWidth, step.renderHeight);
					})
					.then(function (canvas) {
						return $.ajax({
							url: ftdGnlsCtaCapture.ajaxUrl,
							method: 'POST',
							data: {
								action: step.captureAction,
								post_id: step.postId,
								nonce: step.nonce,
								image: canvas.toDataURL('image/png')
							}
						});
					})
					.then(function (response) {
						if (!response || !response.success) {
							throw new Error('Error');
						}

						resolve(response.data || {});
					})
					.catch(reject);
			});

			$frame.attr({
				width: step.renderWidth,
				height: step.renderHeight,
				src: step.previewUrl
			});
		});
	}

	$('#ftd-gnls-generate-promo-btn').on('click', function () {
		var $btn = $(this);
		var $wrap = $btn.closest('.inside');
		var $status = $('#ftd-gnls-capture-status');
		var $frame = $('#ftd-gnls-cta-capture-frame');
		var i18n = (window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n) || {};
		var steps = getCaptureSteps($btn);

		if (typeof html2canvas !== 'function' || !steps || !steps.length) {
			setStatus($status, i18n.error || 'Error');
			return;
		}

		$btn.prop('disabled', true);
		setStatus($status, i18n.working || 'Generating images…');

		var chain = Promise.resolve();

		steps.forEach(function (step, index) {
			chain = chain.then(function () {
				if (index > 0) {
					setStatus($status, step.workingLabel || i18n.working || 'Generating images…');
				}

				return captureStep($frame, step).then(function (data) {
					updatePreview($wrap, step.previewTarget, data.url);
				});
			});
		});

		chain
			.then(function () {
				setStatus($status, i18n.done || 'Promo images saved.');
			})
			.catch(function () {
				setStatus($status, i18n.error || 'Error');
			})
			.finally(function () {
				$btn.prop('disabled', false);
			});
	});
})(jQuery);
