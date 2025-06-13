jQuery(document).ready(function($) {
  $('#map-settings-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();

    form.find('button').prop('disabled', true);

    $.ajax({
        url: directory_ajax_obj.ajax_url,
        type: 'POST',
        data: formData + '&action=save_map_settings&security=' + directory_ajax_obj.nonce,
        success: function(response) {
            form.find('button').prop('disabled', false);
            form.find('.pmpro_message').remove(); 
            if (response.success) {
                form.prepend('<div class="pmpro_message">' + response.data.message + '</div>');
            } else {
                alert('Error: ' + response.data.message);
            }
        },
        error: function(xhr) {
            form.find('button').prop('disabled', false);
            alert('Ajax error: ' + xhr.status + ' - ' + xhr.statusText);
        }
    });
});


});
