<?php
// Shortcode to display user's own directory listings
add_shortcode('user_directory_listings', function() {
  if (!is_user_logged_in()) {
      return '<p>Please <a href="/login/">log in</a> to view your directory listings.</p>';
  }

  $user_id = get_current_user_id();
  $level = pmpro_getMembershipLevelForUser($user_id);
  $limit = ($level->id == 2) ? 1 : (($level->id == 3) ? 3 : 0);

  $args = [
      'post_type' => 'directory_listing',
      'post_status' => ['publish', 'pending'],
      'author' => $user_id,
      'posts_per_page' => -1,
  ];

  $query = new WP_Query($args);
  $current_count = $query->found_posts;
  wp_reset_postdata();

  ob_start();

  if ($query->have_posts()) {
      echo '<h3>Your Directory Listings</h3>';
      echo '<div class="directory-grid">';
      while ($query->have_posts()) : $query->the_post();
      include plugin_dir_path( __FILE__ ) . '../templates/content-directory_listing_sm.php';


      endwhile;
      echo '</div>';

      if ($current_count >= $limit) {
          echo '<p><strong>You have reached your directory listing limit for your membership level.</strong></p>';
      }

  } else {
      echo '<p>You have not added any directory listings yet.</p>';
  }

  wp_reset_postdata();

  return ob_get_clean();
});
