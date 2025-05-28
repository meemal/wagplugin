<?php  // Shortcode for frontend Directory Listing submission form
add_shortcode('submit_directory_form', function() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/login/">log in</a> to submit a listing.</p>';
    }

    ob_start();

    // Handle form submission
    if (isset($_POST['directory_submit'])) {
        $current_user = wp_get_current_user();

        // Sanitize fields
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $category = intval($_POST['category']);
        $tags = sanitize_text_field($_POST['tags']);
        $headline = sanitize_text_field($_POST['headline']);
        $business_name = sanitize_text_field($_POST['business_name']);
        $looking_for = sanitize_textarea_field($_POST['looking_for']);
        $website = esc_url_raw($_POST['website']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);
        $facebook = esc_url_raw($_POST['facebook']);
        $linkedin = esc_url_raw($_POST['linkedin']);
        $youtube = esc_url_raw($_POST['youtube']);

        // Create post
        $post_id = wp_insert_post([
            'post_title' => $business_name,
            'post_content' => $content,
            'post_type' => 'directory_listing',
            'post_status' => 'pending', // Change to 'publish' if auto-approve
            'post_author' => $current_user->ID,
        ]);

        if ($post_id) {
            // Set category and tags
            wp_set_post_terms($post_id, [$category], 'category');
            wp_set_post_terms($post_id, explode(',', $tags), 'post_tag');

            // Save ACF fields
            if (function_exists('update_field')) {
                update_field('headline', $headline, $post_id);
                update_field('business_name', $business_name, $post_id);
                update_field('looking_for', $looking_for, $post_id);
                update_field('website', $website, $post_id);
                update_field('phone', $phone, $post_id);
                update_field('email', $email, $post_id);
                update_field('facebook', $facebook, $post_id);
                update_field('linkedin', $linkedin, $post_id);
                update_field('youtube', $youtube, $post_id);

                // Handle image uploads
                if (!empty($_FILES['cover_picture']['name'])) {
                    $cover_id = media_handle_upload('cover_picture', 0);
                    if (is_wp_error($cover_id)) {
                        echo '<p>Error uploading cover picture.</p>';
                    } else {
                        update_field('cover_picture', $cover_id, $post_id);
                    }
                }

                if (!empty($_FILES['profile_picture']['name'])) {
                    $profile_id = media_handle_upload('profile_picture', 0);
                    if (is_wp_error($profile_id)) {
                        echo '<p>Error uploading profile picture.</p>';
                    } else {
                        update_field('profile_picture', $profile_id, $post_id);
                    }
                }
            }

            echo '<p>Your listing has been submitted and is awaiting review.</p>';
            // Send admin email
            $admin_email = get_option('admin_email'); // Or set a specific address
            $listing_link = admin_url('post.php?post=' . $post_id . '&action=edit');

            wp_mail(
                $admin_email,
                'New Directory Listing Pending Review',
                "A new directory listing has been submitted and is pending review.\n\nView it here: $listing_link"
            );

        }
    }

    // Display the form
    ?>
    <form method="post" enctype="multipart/form-data">
        <p><label>Business Name:<br><input type="text" name="business_name" required></label></p>
        <p><label>Headline:<br><input type="text" name="headline"></label></p>
        <p><label>Description:<br><textarea name="content" rows="4"></textarea></label></p>
        <p><label>Looking For:<br><textarea name="looking_for" rows="3"></textarea></label></p>

        <p><label>Sector (Category):<br><?php wp_dropdown_categories(['taxonomy' => 'category', 'name' => 'category', 'hide_empty' => false]); ?></label></p>

        <p><label>Skills (Tags, comma separated):<br><input type="text" name="tags"></label></p>

        <p><label>Website:<br><input type="url" name="website"></label></p>
        <p><label>Phone Number:<br><input type="text" name="phone"></label></p>
        <p><label>Email:<br><input type="email" name="email"></label></p>
        <p><label>Facebook:<br><input type="url" name="facebook"></label></p>
        <p><label>LinkedIn:<br><input type="url" name="linkedin"></label></p>
        <p><label>YouTube:<br><input type="url" name="youtube"></label></p>

        <p><label>Cover Picture:<br><input type="file" name="cover_picture" accept="image/*"></label></p>
        <p><label>Profile Picture:<br><input type="file" name="profile_picture" accept="image/*"></label></p>

        <p><input type="submit" name="directory_submit" value="Submit Listing"></p>
    </form>
    <?php

    return ob_get_clean();
});
