<?php

add_action('wp_ajax_ftd_increment_view', 'ftd_increment_view');
add_action('wp_ajax_nopriv_ftd_increment_view', 'ftd_increment_view');

function ftd_increment_view() {
    check_ajax_referer('ftd_view_nonce', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    $post_type = get_post_type($post_id);

    if (!$post_id || $post_type !== 'directory_listing') {
            error_log("Invalid post ID or type. ID: $post_id, type: $post_type");
            wp_send_json_error('Invalid post ID or post type.');
    }

    // Check if the current user is the owner of the listing
    $current_user_id = get_current_user_id();
    $owner_id = (int) get_post_field('post_author', $post_id);

    if ($current_user_id && $current_user_id === $owner_id) {
            wp_send_json_success(['new_count' => (int) get_field('view_count', $post_id)]);
    }

    $views = (int) get_field('view_count', $post_id);
    $new_views = $views + 1;
    $success = update_field('view_count', $new_views, $post_id);

    if ($success) {
            wp_send_json_success(['new_count' => $new_views]);
    } else {
            wp_send_json_error('Failed to update view count.');
    }
}
