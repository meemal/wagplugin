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
// add_action('frm_after_update_entry', 'add_tags_to_post_from', 10, 2);
// add_action('save_post_directory_listing', 'ftd_force_assign_tax_terms', 20, 3);

//This website is in perfect alignment with where I am - working with geniuses and being abundant! There is so much room for expansion and so many ways I can further offer benefits to the community - I just can't wait to do more!

//If any genius wants to help I would love to have you on board! Im looking for funding in order to invest even more time making this better, user testers to help me as I make updates, customer support to help me with the moderation ensuring that the directory listings and users are all authentic! If anyone can see where they would like to add value I would love to hear from you!

add_filter('frm_new_post', 'ftd_add_entry_id_to_new_post', 20, 2);
function ftd_add_entry_id_to_new_post($post, $args) {
    if ($args['form']->id != 2) {
        return $post; // Only for Form ID 2
    }

    $entry_id = $args['entry']->id;
    if (!$entry_id) return $post;

    // Set ACF meta directly before post is saved
    $post['post_custom']['associated_ff_post_id'] = $entry_id;
    $post['post_custom']['dl_post_url'] = "/?post_type=directory_listing&p=".$entry_id;

    return $post;
}

// add_action('frm_after_create_entry', 'ftd_save_dl_url_to_entry', 20, 2);
// function ftd_save_dl_url_to_entry($entry_id, $form_id) {
//     if ($form_id != 2) return;

//     // First, get the post ID that was created by this entry
//     $entry = FrmEntry::getOne($entry_id);
//     if (empty($entry->post_id)) return;

//     $post_id = $entry->post_id;

//     // Now read ACF field from that post
//     $associated_entry_id = get_field('associated_ff_post_id', $post_id);
//     if (!$associated_entry_id) return;

//     // Build URL using the post ID (or entry ID, depending on your intent)
//     $url = "/?post_type=directory_listing&p=" . $post_id;

//     // Save to FF field ID 37
//     FrmEntryMeta::update_entry_meta($entry_id, 37, null, $url);

//     error_log("DEBUG: Saved $url to entry #$entry_id, field 37");
// }
