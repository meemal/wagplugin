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

