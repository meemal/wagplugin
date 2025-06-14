jQuery(document).ready(function ($) {
  $('.ftd-view-button').on('click', function () {
    
      const postId = $(this).data('post-id');

      $.post(ftdViewCounter.ajax_url, {
          action: 'ftd_increment_view',
          nonce: ftdViewCounter.nonce,
          post_id: postId
      });
  });
});
