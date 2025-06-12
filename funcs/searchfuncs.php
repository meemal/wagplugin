<?php

function ftd_directory_ajax_search() {
    $search = sanitize_text_field($_POST['search'] ?? '');
    $sector = sanitize_text_field($_POST['sector'] ?? '');

    $args = [
      'post_type'   => 'directory_listing',
      'post_status' => 'publish',
      's'           => $search,
      'paged'       => 1,
    ];

    $tax_query = ['relation' => 'OR'];
    foreach (['post_tag','services','attracting'] as $tax) {
      if ($search) {
        $tax_query[] = ['taxonomy'=>$tax,'field'=>'name','terms'=>$search,'operator'=>'LIKE'];
      }
    }
    if ($sector) {
      $tax_query[] = ['taxonomy'=>'category','field'=>'slug','terms'=>$sector];
    }
    if (count($tax_query)>1) {
      $args['tax_query'] = $tax_query;
    }

    $q = new WP_Query($args);

    ob_start();
    if ($q->have_posts()) {
      while ($q->have_posts()) {
        $q->the_post();
        get_template_part('template-parts/content', 'directory_listing_archive');
      }
    } else {
      echo '<p>No listings found.</p>';
    }
    wp_reset_postdata();

    wp_send_json_success(ob_get_clean());
}
add_action('wp_ajax_directory_filter', 'ftd_directory_ajax_search');
add_action('wp_ajax_nopriv_directory_filter', 'ftd_directory_ajax_search');



function ftd_directory_enqueue() {
    wp_enqueue_script('directory-filter-js', get_stylesheet_directory_uri() . '/js/directory-filter.js', ['jquery'], null, true);
  }
  add_action('wp_enqueue_scripts', 'ftd_directory_enqueue');
  