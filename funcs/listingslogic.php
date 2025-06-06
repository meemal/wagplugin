<?php 
function render_listing_section( $header_field, $message_field, $button = null ) {
    $html  = '<h3>' . esc_html( get_field( $header_field, 'option' ) ) . '</h3>';
    $html .= wp_kses_post( get_field( $message_field, 'option' ) );

    if ( $button === 'upgrade' ) {
        $html .= '<p><a href="/membership-account/membership-levels/" class="button">Upgrade Membership</a></p>';
    } elseif ( $button === 'contact' ) {
        $html .= '<p><a href="/contact" class="button">Contact Us</a></p>';
    }

    return $html;
}

// function display_add_user_directory_listings() {
//     if ( ! is_user_logged_in() ) {
//         return '<p>Please log in to manage your directory listings.</p>';
//     }

//     $user_id = get_current_user_id();
//     $membership_level = pmpro_getMembershipLevelForUser( $user_id );
//     $level_id = $membership_level ? $membership_level->id : 0;

//     // Get allowed listings
//     $allowed_listings = 0;
//     if ( have_rows( 'membership_level_allowance', 'option' ) ) {
//         while ( have_rows( 'membership_level_allowance', 'option' ) ) {
//             the_row();
//             if ( get_sub_field( 'membership_id' ) == $level_id ) {
//                 $allowed_listings = (int) get_sub_field( 'number_of_allowed_directory_listings' );
//                 break;
//             }
//         }
//     }

//     // Count user's listings
//     $listings = get_posts( array(
//         'post_type'      => 'directory_listing',
//         'author'         => $user_id,
//         'post_status'    => array( 'publish', 'pending', 'disabled' ),
//         'posts_per_page' => -1,
//         'fields'         => 'ids',
//     ) );
    
//     $listing_count = count( $listings );

//     ob_start();

//     // Case 1: User has more listings than allowed (legacy issue or manual override)
//     if ( $listing_count > $allowed_listings && $allowed_listings > 0 ) {
//         echo render_listing_section( 'directory_amount_exceeded_header', 'directory_amount_exceeded', 'upgrade' );

//     // Case 2: User has listings available
//     } elseif ( $listing_count < $allowed_listings ) {
//         echo render_listing_section( 'directory_listings_used_header', 'directory_listings_used', 'upgrade' );
//         echo '<p>You have ' . esc_html( $allowed_listings - $listing_count ) . ' of ' . esc_html( $allowed_listings ) . ' listings remaining.</p>';
//         echo '<h3>Add New Directory Listing</h3>';
//         echo do_shortcode( '[formidable id=2]' );

//     // Case 3: Plan allows 0 listings
//     } elseif ( $allowed_listings === 0 ) {
//         echo render_listing_section( 'no_directory_listings_header', 'no_directory_listings', 'upgrade' );

//     // Case 4: User has used all listings, check if upgrade is possible
//     } elseif ( $listing_count === $allowed_listings ) {
//         $can_upgrade = false;
//         $levels = pmpro_getAllLevels( true, true );

//         foreach ( $levels as $level ) {
//             if ( $level->id != $level_id ) {
//                 if ( have_rows( 'membership_level_allowance', 'option' ) ) {
//                     while ( have_rows( 'membership_level_allowance', 'option' ) ) {
//                         the_row();
//                         if ( get_sub_field( 'membership_id' ) == $level->id ) {
//                             if ( (int) get_sub_field( 'number_of_allowed_directory_listings' ) > $allowed_listings ) {
//                                 $can_upgrade = true;
//                                 break 2;
//                             }
//                         }
//                     }
//                 }
//             }
//         }

//         if ( $can_upgrade ) {
//             echo render_listing_section( 'directory_listings_used_header', 'directory_listings_used', 'upgrade' );
//         } else {
//             echo render_listing_section( 'directory_listings_topped_heading', 'directory_amount_topped', 'contact' );
//         }
//     }

//     return ob_get_clean();
// }
// add_shortcode( 'add_user_directory_listings', 'display_add_user_directory_listings' );


function display_add_user_directory_listings() {
    if ( ! is_user_logged_in() ) {
        return '<p>Please log in to manage your directory listings.</p>';
    }

    $user_id = get_current_user_id();
    $membership_level = pmpro_getMembershipLevelForUser( $user_id );
    $level_id = $membership_level ? $membership_level->id : 0;

    // Fetch allowed listings from ACF
    $allowed_listings = 0;
    if ( have_rows( 'membership_level_allowance', 'option' ) ) {
        while ( have_rows( 'membership_level_allowance', 'option' ) ) {
            the_row();
            if ( get_sub_field( 'membership_id' ) == $level_id ) {
                $allowed_listings = (int) get_sub_field( 'number_of_allowed_directory_listings' );
                break;
            }
        }
    }

    // Count current listings
    $listing_count = count( get_posts( array(
        'post_type'      => 'directory_listing',
        'author'         => $user_id,
        'post_status'    => array( 'publish', 'pending', 'disabled' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) ) );

    ob_start();

    // If user can add more listings, show the form
    if ( $listing_count < $allowed_listings ) {
        echo '<p>' . ftd_get_directory_usage_message( $listing_count, $allowed_listings ) . '</p>';
        echo '<h3>Add New Directory Listing</h3>';
        echo do_shortcode( '[formidable id=2]' );
    } else {
        // Otherwise, show the appropriate message
        $can_upgrade = false;
        $levels = pmpro_getAllLevels( true, true );

        foreach ( $levels as $level ) {
            if ( $level->id != $level_id ) {
                if ( have_rows( 'membership_level_allowance', 'option' ) ) {
                    while ( have_rows( 'membership_level_allowance', 'option' ) ) {
                        the_row();
                        if ( get_sub_field( 'membership_id' ) == $level->id ) {
                            if ( (int) get_sub_field( 'number_of_allowed_directory_listings' ) > $allowed_listings ) {
                                $can_upgrade = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if ( $listing_count > $allowed_listings && $allowed_listings > 0 ) {
            echo render_listing_section( 'directory_amount_exceeded_header', 'directory_amount_exceeded', 'upgrade' );
        } elseif ( $allowed_listings === 0 ) {
            echo render_listing_section( 'no_directory_listings_header', 'no_directory_listings', 'upgrade' );
        } elseif ( $can_upgrade ) {
            echo render_listing_section( 'directory_listings_used_header', 'directory_listings_used', 'upgrade' );
        } else {
            echo render_listing_section( 'directory_listings_topped_heading', 'directory_amount_topped', 'contact' );
        }
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
  
            // Delete the post (CPT)
            wp_trash_post( $listing_id ); // Or wp_delete_post()
  
            // Get and delete the associated Formidable entry
            $entry_id = get_field( 'associated_ff_post_id', $listing_id );
            if ( $entry_id ) {
                FrmEntry::destroy( $entry_id );
            }
  
            wp_safe_redirect( add_query_arg( 'listing_deleted', '1', '/membership-account/' ) );
            exit;
        }
    }
  }
  
add_action( 'template_redirect', 'handle_directory_listing_delete' );


