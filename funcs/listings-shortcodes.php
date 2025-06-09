<?php

// Directory Listing Displays
// 1. Display user's own directory listings as small cards on sidebar - [user_directory_listings_sb]
// 2. Display user's own directory listings as list with editing quick links and directory listings allowance- [my_directory_listings_account_page]
//  - Bundles with [my_directory_listings_as_list] to display listings in a table format
//  - And [directory_listing_usage]
// 3. Display user's own directory listings as list with editing quick links - [my_directory_listings_as_list]
// 4. Display listings for users profile as small cards in directory-grid - [profile_directory_listings_cards]

// Directory Listing Messaging
// 5. Display usage and upgrade options - [directory_listing_usage]

//
//
// 1. Display user's own directory listings as small cards on sidebar - [user_directory_listings_sb]
function ftd_sb_user_directory_listings_shortcode($atts) {
    ob_start();

    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return '<p>Please log in to view your listings.</p>';
    }

    $args = array(
        'post_type'      => 'directory_listing',
        'author'         => $current_user_id,
        'post_status'    => array('publish', 'pending', 'disabled'),
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="wp-block-group">';
        echo '<h3 class="has-text-align-center">My Directory Listings</h3>';
        
        while ($query->have_posts()) {
            $query->the_post();
            $profile = get_field('profile_picture');
            $headline = get_field('headline');
            $entry_id = get_field('associated_ff_post_id');
            $status = get_post_status();
            ?>
            <div class="directory-card-mini card">
                <?php echo ftd_get_directory_listing_status_tag($status); ?>

                <div class="directory-card-content has-text-align-center">
                    <?php if ($profile) : ?>
                        <img src="<?php echo esc_url($profile['url']); ?>" alt="Profile Picture" class="profile-pic profile-pic-mini">
                    <?php endif; ?>
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>

                    <div class="directory-card-buttons">
                        <a href="<?php the_permalink(); ?>" class="btn btn-small">View Listing</a>
                        <?php if ($entry_id) : ?>
                            <a href="/your-directory-listing/?frm_action=edit&entry=<?php echo esc_attr($entry_id); ?>" class="btn btn-small edit-btn">Edit Listing</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        echo '</div>'; // .wp-block-group
        wp_reset_postdata();
    } else {
        echo '<p class="has-text-align-center">You have no directory listings yet. <a href="/add-directory-listing/" class="btn btn-small">Add Your First Listing</a></p>';
    }

    return ob_get_clean();
}
add_shortcode('user_directory_listings_sb', 'ftd_sb_user_directory_listings_shortcode');
//

// 2. Display user's own directory listings as list with editing quick links
function my_directory_listings_account_page_shortcode() {
    ob_start();

    ?>
    <div class="pmpro">
    <h2 class="pmpro_section_title pmpro_font-x-large">My Directory Listings</h2>
        <div class="pmpro_card">
        
    
            <div class="pmpro_account-section">
                <div class="pmpro_card_content">   
                    <?php
                    // Run the content of [directory_listing_usage] shortcode
                    echo do_shortcode('[my_directory_listings_as_list]');
                    ?>
                </div>
            </div>
            <div class="pmpro_account-section">
                <div class="pmpro_card_content">   
                <div class="directory-listing-usage--cta has-text-align-center aligncenter">
                <?php
            
                // Run the content of [directory_listing_usage] shortcode
                echo do_shortcode('[directory_listing_usage]');
                ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('my_directory_listings_account_page', 'my_directory_listings_account_page_shortcode');
//

// 3. Display user's own directory listings as list with editing quick links - [my_directory_listings_as_list]
function my_directory_listings_as_list_shortcode() {
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return '<p>Please log in to view your listings.</p>';
    }
    wp_cache_flush();

    $args = array(
        'post_type'      => 'directory_listing',
        'author'         => $current_user_id,
        'post_status'    => array('publish', 'pending', 'disabled'),
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    ob_start();
    ?>
    <div class="pmpro_box">
        <?php if ($query->have_posts()) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Enabled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($query->have_posts()) : $query->the_post(); 
                        $entry_id = get_field('associated_ff_post_id');
                        $post_id = get_the_ID();
                        $status = get_post_status();
                       
                        ?>
                        <tr>
                            <td><strong><?php the_title(); ?></strong></td>
                            <td><?= ftd_get_directory_listing_status_tag($status); ?></td>
                            <td>
                            <a href="<?php the_permalink(); ?>" class="pmpro_actionlink">View</a>
                            <?php if ($entry_id) : ?>
                                | <a href="/your-directory-listing/?frm_action=edit&entry=<?php echo esc_attr($entry_id); ?>" class="pmpro_actionlink">Edit</a>
                            <?php endif; ?>
                            | <a href="<?php echo esc_url( add_query_arg([
                                'delete_listing' => $post_id,
                                '_wpnonce' => wp_create_nonce( 'delete_listing_' . $post_id )
                            ], get_permalink() ) ); ?>" class="pmpro_actionlink" onclick="return confirm('Are you sure you want to delete this listing?');">Delete</a>


                            </td>
                            <td>
                                <?php if ($status !== 'pending') : ?>
                                    <form method="get" style="display:inline;" onChange="this.submit();">
                                        <input type="hidden" name="toggle_visibility" value="<?php echo esc_attr($post_id); ?>">
                                        <input type="hidden" name="toggle_action" value="<?php echo $status === 'disabled' ? 'enable' : 'disable'; ?>">
                                        <label class="toggle-switch">
                                            <input type="checkbox" <?php checked($status !== 'disabled'); ?> onclick="this.form.submit();">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </form>
                                <?php else : ?>
                                    <em>-</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
 
        <?php endif;
        wp_reset_postdata(); ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('my_directory_listings_as_list', 'my_directory_listings_as_list_shortcode');
//

// 4. Display listings for users profile as small cards in directory-grid - [profile_directory_listings_cards]
// Shortcode to display user's own directory listings
// add_shortcode('profile_directory_listings_cards', function() {


//     global $current_user;
//     $profile_user_id = $current_user->ID;
//     if (!$profile_user_id) {
//         return '<p>User not found.</p>';
//     }

//     // Get the profile user's display name
//     $profile_user = get_userdata($profile_user_id);
//     $profile_display_name = $profile_user ? $profile_user->display_name : 'Genius';

//     // Query the directory listings for the profile user
//     $args = [
//         'post_type'      => 'directory_listing',
//         'post_status'    => ['publish'],
//         'author'         => $profile_user_id,
//         'posts_per_page' => -1,
//     ];
//     $query = new WP_Query($args);

//     ob_start();

//     echo '<h3>' . esc_html($profile_display_name) . ' - Directory Listings</h3>';

//     if ($query->have_posts()) {
//         echo '<div class="directory-grid">';
//         while ($query->have_posts()) {
//             $query->the_post();
//             // Include your template part for displaying each listing
//             include plugin_dir_path(__FILE__) . '../templates/content-directory_listing_sm.php';
//         }
//         echo '</div>';
//     } else {
//         // Get the ACF field value for no listings message
//         $no_listings_message = get_field('no_listings_on_profile', 'option');
//         echo '<p>' . esc_html($no_listings_message) . '</p>';
//     }

//     wp_reset_postdata();

//     return ob_get_clean();
// });

//

// 5. Display usage and upgrade options - [directory_listing_usage]
function display_directory_listing_usage() {
    // Get current user ID
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return '<p>Please log in to view your directory listing usage.</p>';
    }

    // Get user's membership level
    $membership_level = pmpro_getMembershipLevelForUser( $user_id );
    if ( ! $membership_level ) {
        return '<p>No membership level found for your account.</p>';
    }

    $membership_id = $membership_level->id;

    // Get the membership level allowances from ACF options
    $allowances = get_field( 'membership_level_allowance', 'option' );
    $allowed_listings = 0;

    if ( $allowances ) {
        foreach ( $allowances as $allowance ) {
            if ( isset( $allowance['membership_id'] ) && $allowance['membership_id'] == $membership_id ) {
                $allowed_listings = isset( $allowance['number_of_allowed_directory_listings'] ) ? intval( $allowance['number_of_allowed_directory_listings'] ) : 0;
                break;
            }
        }
    }

    // Count the number of directory listings created by the user
    $args = array(
        'post_type'      => 'directory_listing',
        'author'         => $user_id,
        'post_status'    => array( 'publish', 'pending', 'draft', 'disabled' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );
    $user_listings = get_posts( $args );
    $used_listings = count( $user_listings );
  // Conditional messages
  if ( $allowed_listings === 0 ) {
    echo '<h3 class="pmpro_card_title pmpro_font-large">No directory listings included in your membership plan</h3>
    <p>Upgrade your plan to create an offer, the community is here for you!</strong></p>
    <p><a href="/membership-levels/" class="btn btn-small">Upgrade Your Membership</a></p>';
    return;
    }
    // Display the usage information
    $output  = '<div class="directory-listing-usage card">';
  
    $output  .= '<p>' . ftd_get_directory_usage_message( $used_listings, $allowed_listings ) . '</p>';
    
  if ( $used_listings >= $allowed_listings ) {
        $output .= '<p><strong>You have reached your directory listing limit.</strong> </p> <p><a href="/membership-levels/" class="btn btn-small">Upgrade</a></p>';
    } else {
        $remaining = $allowed_listings - $used_listings;
        $output .= '<p>You can add <strong>' . esc_html( $remaining ) . '</strong> more directory listing(s).</p>';

        // Add New Listing Button (conditionally shown)
        $output .= '<p><a class="btn" href="/add-directory-listing/" class="btn btn-small">Add New Listing</a></p>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode( 'directory_listing_usage', 'display_directory_listing_usage' );




add_action('template_redirect', function() {
    if (
        is_user_logged_in() &&
        isset($_GET['toggle_visibility'], $_GET['toggle_action']) &&
        ($post_id = absint($_GET['toggle_visibility'])) &&
        get_current_user_id() === (int) get_post_field('post_author', $post_id)
    ) {
        $new_status = ($_GET['toggle_action'] === 'disable') ? 'disabled' : 'publish';

        // Update post status
        wp_update_post([
            'ID' => $post_id,
            'post_status' => $new_status,
        ]);

        // Redirect back without query args
        wp_safe_redirect(remove_query_arg(['toggle_visibility', 'toggle_action'], wp_get_referer() ?: home_url()));
        exit;
    }
});


