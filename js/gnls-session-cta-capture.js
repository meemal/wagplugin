(function ($) {
	'use strict';

	function setStatus(message) {
		$('#ftd-gnls-cta-capture-status').text(message || '');
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

			if (!target) {
				$btn.prop('disabled', false);
				setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.error) || 'Error');
				return;
			}

			html2canvas(target, {
				backgroundColor: null,
				scale: 1,
				useCORS: true,
				allowTaint: true,
				width: 1280,
				height: 720,
				windowWidth: 1280,
				windowHeight: 720
			}).then(function (canvas) {
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
			}).then(function (response) {
				if (!response || !response.success) {
					throw new Error((response && response.data && response.data.message) || 'Error');
				}

				setStatus(response.data.message || ftdGnlsCtaCapture.i18n.done);

				if (response.data.url) {
					var $box = $btn.closest('.inside');
					$box.find('p img').remove();
					$('<p><img src="' + response.data.url + '" alt="" style="max-width:100%;height:auto;" /></p>').insertAfter($box.find('.description').first());
				}
			}).catch(function () {
				setStatus((window.ftdGnlsCtaCapture && ftdGnlsCtaCapture.i18n.error) || 'Error');
			}).finally(function () {
				$btn.prop('disabled', false);
			});
		});

		$frame.attr('src', previewUrl);
	});
})(jQuery);
