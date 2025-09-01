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
		
		// Skip if no new terms were entered
		if (empty($terms)) continue;

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
			// Get existing terms for this post
			$existing_term_ids = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
			
			// Merge new terms with existing ones (don't overwrite)
			$all_term_ids = array_unique(array_merge($existing_term_ids ?: [], $new_term_ids));
		
			// Set the combined term set
			$result = wp_set_post_terms($post_id, $all_term_ids, $taxonomy);
			
			if (is_wp_error($result)) {
				error_log("Failed to set terms for $taxonomy: " . $result->get_error_message());
			} else {
				error_log("Successfully set terms for $taxonomy on post $post_id: " . implode(', ', $all_term_ids));
			}
		}

		// Clear the original free-text field to prevent re-processing
		FrmEntryMeta::update_entry_meta($entry_id, $field_id, null, '');
	}
}

function ftd_parse_terms($field_id, $entry_id) {
	$val = FrmProEntriesController::get_field_value_shortcode([
		'field_id' => $field_id,
		'entry'    => $entry_id,
	]);
	
	// Handle both comma-separated and individual terms
	if (empty($val)) return [];
	
	// Split by comma and clean up each term
	$terms = array_map('trim', explode(',', $val));
	
	// Filter out empty terms and return unique values
	return array_unique(array_filter($terms));
}

// Run on multiple hooks with different priorities to ensure terms persist
add_action('frm_after_create_entry', 'add_tags_to_post_from', 20, 2); // Higher priority
add_action('frm_after_update_entry', 'add_tags_to_post_from', 20, 2); // Higher priority

// Also run on post save with very high priority to ensure terms persist
add_action('save_post_directory_listing', 'ftd_ensure_terms_persist', 99, 3);
function ftd_ensure_terms_persist($post_id, $post, $update) {
	// Only run on updates, not creation
	if (!$update) return;
	
	// Check if this post has an associated entry
	$entry_id = get_field('associated_ff_post_id', $post_id);
	if (!$entry_id) return;
	
	// Get the entry to check form ID
	$entry = FrmEntry::getOne($entry_id);
	if (!$entry || $entry->form_id != 2) return;
	
	// Re-apply terms if they were lost
	$taxonomies = ['post_tag', 'attracting', 'services'];
	
	foreach ($taxonomies as $taxonomy) {
		$current_terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'names']);
		if (empty($current_terms)) {
			// Check if there are terms in the entry that should be applied
			$taxonomy_field_map = [
				'services'   => 32,
				'attracting' => 31,
				'post_tag'   => 14,
			];
			
			if (isset($taxonomy_field_map[$taxonomy])) {
				$entry_terms = FrmProEntriesController::get_field_value_shortcode([
					'field_id' => $taxonomy_field_map[$taxonomy],
					'entry'    => $entry_id,
				]);
				
				if (!empty($entry_terms)) {
					$term_ids = [];
					$term_names = array_map('trim', explode(',', $entry_terms));
					
					foreach ($term_names as $term_name) {
						$term_data = term_exists($term_name, $taxonomy);
						if ($term_data) {
							$term_id = is_array($term_data) ? $term_data['term_id'] : $term_data;
							$term_ids[] = (int) $term_id;
							error_log("Re-applying term: $term_name (ID: $term_id) for $taxonomy on post $post_id");
						}
					}
					
					if (!empty($term_ids)) {
						wp_set_post_terms($post_id, $term_ids, $taxonomy);
						error_log("Re-applied terms for $taxonomy on post $post_id: " . implode(', ', $term_names));
					}
				}
			}
		}
	}
}

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

// Add this new function to sync form entry changes to the CPT
add_action('frm_after_update_entry', 'ftd_sync_entry_tags_to_cpt', 30, 2);
function ftd_sync_entry_tags_to_cpt($entry_id, $form_id) {
    if ($form_id != 2) return;
    
    // Get the post ID from the entry
    $entry = FrmEntry::getOne($entry_id);
    if (!$entry || empty($entry->post_id)) return;
    
    $post_id = $entry->post_id;
    
    // Map of form fields to taxonomies
    $field_taxonomy_map = [
        32 => 'services',
        31 => 'attracting',
        14 => 'post_tag'
    ];
    
    foreach ($field_taxonomy_map as $field_id => $taxonomy) {
        // Get the current value from the form entry
        $entry_terms = FrmProEntriesController::get_field_value_shortcode([
            'field_id' => $field_id,
            'entry'    => $entry_id,
        ]);
        
        if (!empty($entry_terms)) {
            $term_names = array_map('trim', explode(',', $entry_terms));
            $term_ids = [];
            
            foreach ($term_names as $term_name) {
                $term_data = term_exists($term_name, $taxonomy);
                if ($term_data) {
                    $term_id = is_array($term_data) ? $term_data['term_id'] : $term_data;
                    $term_ids[] = (int) $term_id;
                }
            }
            
            if (!empty($term_ids)) {
                // Set the terms on the CPT
                wp_set_object_terms($post_id, $term_ids, $taxonomy);
                error_log("Synced entry tags to CPT: {$taxonomy} = " . implode(', ', $term_names));
            }
        }
    }
}