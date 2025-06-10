<?php


function add_tags_to_post_from($entry_id, $form_id) {
    if ($form_id != 2) return;

    $entry = FrmEntry::getOne($entry_id);
    if (!$entry || empty($entry->post_id)) {
        error_log("No post ID found for entry $entry_id");
        return;
    }

    $post_id = $entry->post_id;

    $taxonomy_fields = [
        'post_tag'   => 35,
        'attracting' => 36,
        'services'   => 24,
    ];

    foreach ($taxonomy_fields as $taxonomy => $field_id) {
        $terms = ftd_parse_terms($field_id, $entry_id);
        $new_term_ids = [];

        foreach ($terms as $term_name) {
            $term_name = sanitize_text_field($term_name);

            // Skip blank or numeric values
            if ($term_name === '' || is_numeric($term_name)) continue;

            $term_data = term_exists($term_name, $taxonomy);
            if (!$term_data) {
                $term_data = wp_insert_term($term_name, $taxonomy);
                if (is_wp_error($term_data)) {
                    error_log("Error inserting term '$term_name' in $taxonomy: " . $term_data->get_error_message());
                    continue;
                }
            }

            $term_id = is_array($term_data) ? $term_data['term_id'] : $term_data;
            if (is_numeric($term_id)) {
                $new_term_ids[] = (int) $term_id;
            }
        }

        if (!empty($new_term_ids)) {
            // Combine new and existing term IDs
            $existing_term_ids = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
            $all_term_ids = array_unique(array_merge($existing_term_ids ?: [], $new_term_ids));
        
            // Set the full term set
            $result = wp_set_post_terms($post_id, $all_term_ids, $taxonomy);
            
            
            if (is_wp_error($result)) {
                error_log("Failed to set terms for $taxonomy: " . $result->get_error_message());
            } else {
                error_log("Set terms for $taxonomy on post $post_id: " . implode(', ', $all_term_ids));
            }
        }

        // Update the taxonomy field in the Formidable entry (so terms show selected in form)
            $form_field_map = [
                'services'   => 32,
                'attracting' => 31,
                'post_tag'   => 14,
            ];

            if (!empty($term_ids) && isset($form_field_map[$taxonomy])) {
                FrmEntryMeta::update_entry_meta($entry_id, $form_field_map[$taxonomy], null, $term_ids);
            }


        // Clear the original free-text field
        FrmEntryMeta::update_entry_meta($entry_id, $field_id, null, '');
        
    }
}

function ftd_parse_terms($field_id, $entry_id) {
    $val = FrmProEntriesController::get_field_value_shortcode([
        'field_id' => $field_id,
        'entry'    => $entry_id,
    ]);
    return array_filter(array_map('trim', explode(',', $val)));
}

add_action('frm_after_create_entry', 'add_tags_to_post_from', 10, 2);
add_action('frm_after_update_entry', 'add_tags_to_post_from', 10, 2);
// add_action('save_post_directory_listing', 'ftd_force_assign_tax_terms', 20, 3);



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

add_action('frm_after_create_entry', 'ftd_save_entry_id_to_acf', 20, 2);