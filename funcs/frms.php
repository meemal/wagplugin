<?php
add_action('frm_after_create_entry', 'add_tags_to_post_from_form', 30, 2);
function add_tags_to_post_from_form($entry_id, $form_id) {
    if ($form_id != 2) { // Your form ID
        return;
    }

    $tags_field_id = 24; // Your "New Tags" text field ID

    // Get the entry object
    $entry = FrmEntry::getOne($entry_id);

    if (!$entry || empty($entry->post_id)) {
        return; // No post created, so no action needed
    }

    $post_id = $entry->post_id;

    // Get the tag string from the form
    $tag_string = FrmProEntriesController::get_field_value_shortcode(array(
        'field_id' => $tags_field_id,
        'entry' => $entry_id
    ));

    if ($tag_string) {
        $tags_array = array_map('trim', explode(',', $tag_string));
        // Assign tags to the post (append to existing tags)
        wp_set_post_terms($post_id, $tags_array, 'post_tag', true);
    }
}

add_action('frm_after_create_entry', 'ftd_save_entry_id_to_acf', 30, 2);

function ftd_save_entry_id_to_acf($entry_id, $form_id) {
    $target_form_id = 2; // Replace with your actual Formidable Form ID

    if ($form_id != $target_form_id) {
        return; // Exit if it's not the target form
    }

    global $wpdb;

    // Retrieve the post_id directly from the frm_items table
    $post_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        )
    );

    if ($post_id) {
        // Update the ACF field with the entry ID
        update_field('associated_ff_post_id', $entry_id, $post_id);
    } else {
        error_log("No post_id found for Formidable entry ID: $entry_id");
    }
}

