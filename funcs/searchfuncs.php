<?php
function enqueue_directory_scripts() {
  wp_enqueue_script('jquery');
  wp_enqueue_script('directory-ajax-filter', get_template_directory_uri() . '/js/directory-ajax-filter.js', array('jquery'), null, true);

  wp_localize_script('directory-ajax-filter', 'directory_ajax_obj', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('directory_ajax_nonce')
  ));
}
add_action('wp_enqueue_scripts', 'enqueue_directory_scripts');


// AJAX Filter Handler
add_action('wp_ajax_filter_directory', 'filter_directory_listings');
add_action('wp_ajax_nopriv_filter_directory', 'filter_directory_listings');
function filter_directory_listings() {
    check_ajax_referer('directory_ajax_nonce', 'nonce');

    $args = [
        'post_type' => 'directory_listing',
        'posts_per_page' => -1,
    ];

    if (!empty($_POST['search'])) {
        $args['s'] = sanitize_text_field($_POST['search']);
    }

    $tax_query = [];

    if (!empty($_POST['sector'])) {
        $tax_query[] = [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => sanitize_text_field($_POST['sector']),
        ];
    }

    if (!empty($_POST['skills'])) {
        $tax_query[] = [
            'taxonomy' => 'post_tag',
            'field' => 'slug',
            'terms' => sanitize_text_field($_POST['skills']),
        ];
    }

    if (!empty($tax_query)) {
        $args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            get_template_part('template-parts/content', 'directory_listing');
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>No listings found.</p>';
    endif;

    wp_die();
}
