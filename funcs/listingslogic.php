<?php 


function display_add_user_directory_listings() {
  if ( ! is_user_logged_in() ) {
      return '<p>Please log in to manage your directory listings.</p>';
  }

  $user_id = get_current_user_id();

  // Get the user's membership level
  $membership_level = pmpro_getMembershipLevelForUser( $user_id );
  $level_id = $membership_level ? $membership_level->id : 0;

  // Define listing limits per membership level
  $listing_limits = array(
      1 => 0,
      2 => 1,
      3 => 3,
  );

  $allowed_listings = isset( $listing_limits[ $level_id ] ) ? $listing_limits[ $level_id ] : 0;

  // Count the user's existing listings
  $args = array(
      'post_type'      => 'directory_listing', // Update to your custom post type
      'author'         => $user_id,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'fields'         => 'ids',
  );
  $user_listings = get_posts( $args );
  $listing_count = count( $user_listings );

  ob_start();

  // // Display existing listings
  // if ( $listing_count > 0 ) {
  //     echo '<h3>Your Existing Directory Listings</h3><ul>';
  //     foreach ( $user_listings as $listing_id ) {
  //       ftd_get_plugin_template( 'content-directory_listing_sm', array(
  //           'listing_id' => $listing_id,
  //       ));
  //   }
    
  //     echo '</ul>';
  // } else {
  //     echo '<p>You have no directory listings.</p>';
  // }

  // Display remaining listings info
  $remaining = $allowed_listings - $listing_count;
  if ( $allowed_listings > 0 ) {
      echo '<p>You have ' . esc_html( $remaining ) . ' of ' . esc_html( $allowed_listings ) . ' listings remaining.</p>';
  } else {
      echo '<p>Your current membership level does not allow directory listings.</p>';
  }

  // Display Add Listing form or Upgrade button
  if ( $remaining > 0 ) {
    echo '<h3>Add New Directory Listing</h3>';
      echo do_shortcode( '[formidable id=2]' );
  } elseif ( $allowed_listings == 0 || $remaining == 0 ) {
      echo '<p><a href="/membership-account/membership-levels/" class="button">Upgrade Membership</a></p>';
  }

  return ob_get_clean();
}
add_shortcode( 'add_user_directory_listings', 'display_add_user_directory_listings' );



// Handle deletion requests
function handle_directory_listing_delete() {
  if (
      isset( $_GET['delete_listing'] ) &&
      isset( $_GET['_wpnonce'] ) &&
      wp_verify_nonce( $_GET['_wpnonce'], 'delete_listing_' . $_GET['delete_listing'] )
  ) {
      $listing_id = intval( $_GET['delete_listing'] );

      if ( get_current_user_id() === (int) get_post_field( 'post_author', $listing_id ) ) {
          wp_trash_post( $listing_id ); // Or wp_delete_post() if you prefer permanent deletion
          wp_safe_redirect( remove_query_arg( array( 'delete_listing', '_wpnonce' ) ) );
          exit;
      }
  }
}
add_action( 'template_redirect', 'handle_directory_listing_delete' );
