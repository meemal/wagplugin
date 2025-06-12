jQuery(function($){
    $('#directory-filter-form').on('submit', function(e){
      e.preventDefault();
      var $form = $(this),
          data = {
            action: 'directory_filter',
            search: $form.find('[name="search"]').val(),
            sector: $form.find('[name="sector"]').val()
          };
      $.post($form.data('ajax-url'), data, function(res){
        if (res.success) {
          $('#directory-listings').html(res.data);
        }
      });
    });
  });
  