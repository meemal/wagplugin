<?php
// Register the custom shortcode [custom_member_profile]
add_shortcode('custom_member_profile', function () {
  if (!is_user_logged_in()) {
      return '<p>You must be logged in to view member profiles.</p>';
  }

  global $wp;
  $path = trim($wp->request, '/');
  $segments = explode('/', $path);
  
  // Check if URL matches /profile/username
  if (isset($segments[0]) && $segments[0] === 'profile' && !empty($segments[1])) {
      $username = sanitize_title($segments[1]);
      $user = get_user_by('slug', $username);
  } else {
      $user = wp_get_current_user();
  }
  

  if (!$user || !$user->ID) {
      return '<p>User profile not found.</p>';
  }

  ob_start();

  // Include profile content template
  include plugin_dir_path(__FILE__) . '../template-parts/content-user_profile.php';

  // Query for this user's directory listings
  $query = new WP_Query([
      'post_type'      => 'directory_listing',
      'post_status'    => 'publish',
      'author'         => $user->ID,
      'posts_per_page' => -1
  ]);

  if ($query->have_posts()) {
      echo '<h2>' . esc_html($user->display_name) . '\'s Directory Listings</h2>';
      echo '<div class="directory-grid">';
      while ($query->have_posts()) {
        
          $query->the_post();
          include plugin_dir_path(__FILE__) . '../template-parts/content-directory_listing_user_card.php';
      }
      echo '</div>';
  } else {
      echo '<p>This member has not added any directory listings yet.</p>';
  }

  wp_reset_postdata();

  return ob_get_clean();
});

add_action( 'pmpro_member_links_bottom', 'ftd_add_directory_link' );
function ftd_add_directory_link() {
    echo '<li><a href="' . esc_url( home_url('/profile/') ) . '">View Profile</a></li>';
}


/**
 * Add a “Business Directory” button to the Profile actions.
 */
add_filter( 'pmpro_account_profile_action_links', 'ftd_add_directory_action_link' );
function ftd_add_directory_action_link( $links ) {
    $user_id = get_current_user_id();
    $levels = pmpro_getMembershipLevelsForUser( $user_id );
    if ($levels[0]->id == 1) {
        return $links; // Only add link for non-free members
    }
    $directory_link  = sprintf(
        '<a id="ftd-directory-link"  href="%s" title="%s">%s</a>',
        esc_url( home_url( '/profile/' ) ),
        esc_attr__( 'View Profile', 'your-text-domain' ),
        esc_html__( 'View Profile', 'your-text-domain' )
    );

    // Append it to the end
     array_unshift( $links, $directory_link );

    return $links;
}
