jQuery(document).ready(function($) {
  $('#map-settings-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var button = form.find('button');

    button.addClass('btn-disabled').prop('disabled', true);

    $.ajax({
      url: directory_ajax_obj.ajax_url,
      type: 'POST',
      data: form.serialize() + '&action=save_map_settings&security=' + directory_ajax_obj.nonce,
      success: function(response) {
        button.removeClass('btn-disabled').prop('disabled', false);

        $('#map-settings-message').remove();
        if (response.success) {
          const message = $('<div id="map-settings-message" class="pmpro_message" style="display:none;">' + response.data.message + '</div>');
          form.prepend(message);
          message.fadeIn();
        } else {
          alert('Error: ' + response.data.message);
        }
      },
      error: function(xhr) {
        button.removeClass('btn-disabled').prop('disabled', false);
        alert('Ajax error: ' + xhr.status + ' - ' + xhr.statusText);
      }
    });
  });

});

jQuery(document).ready(function($) {
  var $toggle = $('input[name="pmpromm_optin"]'),
      $fields = $('#pmpromm_address_fields');

      if ($toggle.length) {
        // Explicitly show or hide on load
        if ($toggle.prop('checked')) {
          $fields.show(); // or use .css('display', 'flex') if needed
        } else {
          $fields.hide();
        }

    // On change – slide fields
    $toggle.on('change', function(){
      console
      if ($(this).prop('checked')) {
        $fields.slideDown().css('display', 'flex');
      } else {
        $fields.slideUp();
      }
    });
  }
});
