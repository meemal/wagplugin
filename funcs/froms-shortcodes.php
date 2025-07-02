<?php 
function user_directory_listings_shortcode( $atts ) {
  if ( ! is_user_logged_in() ) {
      return '<p>Please log in to view your directory listings.</p>';
  }

  $atts = shortcode_atts( array(
      'form_id' => '', // Formidable Form ID
  ), $atts, 'user_directory_listings' );

  $form_id = $atts['form_id'];

  if ( empty( $form_id ) ) {
      return '<p>Form ID is required.</p>';
  }

  $user_id = get_current_user_id();

  $args = array(
      'post_type'      => 'directory_listing',
      'author'         => $user_id,
      'post_status'    => array( 'publish', 'pending', 'draft', 'disabled' ),
      'posts_per_page' => -1,
  );

  $listings = get_posts( $args );

  if ( empty( $listings ) ) {
      return;
  }

  ob_start();

  echo '<div class="user-directory-listings">';

  foreach ( $listings as $listing ) {
      $title    = get_the_title( $listing->ID );
      $headline = get_field( 'headline', $listing->ID );
      $profile  = get_field( 'profile_picture', $listing->ID );
      $profile_url = is_array( $profile ) && ! empty( $profile['url'] ) ? $profile['url'] : '';

      // Formidable Entry ID from Post Meta
      $entry_id = get_post_meta( $listing->ID, '_frm_entry_id', true );

      $edit_link = $entry_id ? do_shortcode( '[frm-entry-edit-link id=' . esc_attr( $form_id ) . ' entry=' . esc_attr( $entry_id ) . ' label="Edit Listing"]' ) : '';

      echo '<div class="directory-card card">';
      echo '<div class="directory-card-inner">';
      
      // Column 1: Profile Pic
      echo '<div class="directory-col profile-pic">';
      if ( $profile_url ) {
          echo '<img src="' . esc_url( $profile_url ) . '" alt="' . esc_attr( $title ) . '" />';
      }
      echo '</div>';

      // Column 2: Title + Headline
      echo '<div class="directory-col listing-info">';
      echo '<h3>' . esc_html( $title ) . '</h3>';
      if ( $headline ) {
          echo '<p>' . esc_html( $headline ) . '</p>';
      }
      echo '</div>';

      // Column 3: Edit Button
      echo '<div class="directory-col edit-button">';
      if ( $edit_link ) {
          echo $edit_link;
      } else {
          echo '<p><em>Edit link not available</em></p>';
      }
      echo '</div>';

      echo '</div>'; // directory-card-inner
      echo '</div>'; // directory-card
  }

  echo '</div>';

  return ob_get_clean();
}

add_shortcode( 'edit_user_directory_listings', 'user_directory_listings_shortcode' );
