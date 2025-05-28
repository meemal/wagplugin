jQuery(document).ready(function($) {
    $('#directory-filter-form').on('submit', function(e) {
        e.preventDefault();

        var filterData = $(this).serialize();
        filterData += '&action=filter_directory';
        filterData += '&nonce=' + directory_ajax_obj.nonce;

        $.ajax({
            url: directory_ajax_obj.ajax_url,
            type: 'POST',
            data: filterData,
            success: function(response) {
                $('#directory-listings').html(response);
            }
        });
    });
});
