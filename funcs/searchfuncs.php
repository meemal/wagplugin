<?php

function filter_directory_listings() {
  check_ajax_referer('directory_ajax_nonce', 'nonce');

  $args = array(
      'post_type' => 'directory_listing',
      'posts_per_page' => -1,
  );

  if (!empty($_POST['search'])) {
      $args['s'] = sanitize_text_field($_POST['search']);
  }

  $tax_query = [];

  if (!empty($_POST['sector'])) {
      $tax_query[] = array(
          'taxonomy' => 'category',
          'field' => 'slug',
          'terms' => sanitize_text_field($_POST['sector']),
      );
  }

  if (!empty($_POST['skills'])) {
      $tax_query[] = array(
          'taxonomy' => 'post_tag',
          'field' => 'slug',
          'terms' => sanitize_text_field($_POST['skills']),
      );
  }

  if (!empty($tax_query)) {
      $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
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
add_action('wp_ajax_filter_directory', 'filter_directory_listings');
add_action('wp_ajax_nopriv_filter_directory', 'filter_directory_listings');
