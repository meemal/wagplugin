<?php
/**
 * Member Map Settings Plugin
 * Interfaces with PMPro Member Directory map fields
 */

// Shortcode: [member_map_settings_form]
add_shortcode('member_map_settings_form', function () {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return '<p>Please log in to manage your map settings.</p>';
    }

    // Check if user is pending approval (if function exists)
    if (function_exists('ftd_user_is_pending_approval') && ftd_user_is_pending_approval($user_id)) {
        return '<p>Your account is pending approval. Map settings will be available once approved.</p>';
    }

    // Get the map data - now properly checking both field prefixes
    $map_data = pmpromd_get_consolidated_map_data($user_id);
    
    // Extract individual fields
    $map_enabled = $map_data['optin'];
    $street = $map_data['street'];
    $city = $map_data['city'];
    $state = $map_data['state'];
    $zip = $map_data['zip'];
    $country = $map_data['country'];

    ob_start(); ?>
    <div id="pmpro_form_fieldset-map-settings" class="pmpro">
        <h2 class="pmpro_section_title pmpro_font-x-large">My Genius Map Listing</h2>
        <div class="pmpro_card">
            <div class="pmpro_account-section">
                <div class="pmpro_card_content">   
                    <form id="map-settings-form" class="pmpro_form">
                        <?php wp_nonce_field('save_map_settings', 'map_settings_nonce'); ?>

                        <div style="margin-top:32px;" class="pmpro_form_field pmpro_form_field-checkbox">
                            <label class="pmpro_form_label pmpro_form_label-inline pmpro_clickable">
                                <input type="checkbox" 
                                       name="map_optin" 
                                       id="map_optin"
                                       class="pmpro_form_input pmpro_form_input-checkbox" 
                                       <?php checked($map_enabled); ?>>
                                Show on Membership Map
                            </label>
                            <span class="info-dim">
                                <em>(We ask for your street address only to help place the map marker in the right spot. 
                                Don't worry, your exact location won't be visible to others because the map won't zoom in close.)</em>
                            </span>
                        </div>

                        <br>
                        <div id="map_address_fields" class="pmpro_form_fields" style="display: <?php echo $map_enabled ? 'grid' : 'none'; ?>;">
                            <div class="pmpro_form_field pmpro_form_field-text">
                                <label for="map_street">Street Address <span class="pmpro_required">*</span></label>
                                <input type="text" 
                                       id="map_street" 
                                       name="map_street" 
                                       class="pmpro_form_input pmpro_form_input-text" 
                                       value="<?php echo esc_attr($street); ?>" 
                                       required>
                            </div>
                            
                            <div class="pmpro_form_field pmpro_form_field-text">
                                <label for="map_city">City <span class="pmpro_required">*</span></label>
                                <input type="text" 
                                       id="map_city" 
                                       name="map_city" 
                                       class="pmpro_form_input pmpro_form_input-text" 
                                       value="<?php echo esc_attr($city); ?>" 
                                       required>
                            </div>
                            
                            <div class="pmpro_form_field pmpro_form_field-text">
                                <label for="map_state">State / County <span class="pmpro_required">*</span></label>
                                <input type="text" 
                                       id="map_state" 
                                       name="map_state" 
                                       class="pmpro_form_input pmpro_form_input-text" 
                                       value="<?php echo esc_attr($state); ?>" 
                                       required>
                            </div>
                            
                            <div class="pmpro_form_field pmpro_form_field-text">
                                <label for="map_zip">Zip / Post Code <span class="pmpro_required">*</span></label>
                                <input type="text" 
                                       id="map_zip" 
                                       name="map_zip" 
                                       class="pmpro_form_input pmpro_form_input-text" 
                                       value="<?php echo esc_attr($zip); ?>" 
                                       required>
                            </div>
                            
                            <div class="pmpro_form_field pmpro_form_field-select">
                                <label for="map_country">Country <span class="pmpro_required">*</span></label>
                                <select name="map_country" 
                                        id="map_country" 
                                        class="pmpro_form_input pmpro_form_input-select" 
                                        required>
                                    <?php
                                    global $pmpro_countries, $pmpro_default_country;
                                    if (!$country) $country = $pmpro_default_country;
                                    foreach ($pmpro_countries as $abbr => $name): ?>
                                        <option value="<?php echo esc_attr($abbr); ?>" <?php selected($country, $abbr); ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <p>
                            <button type="submit" class="btn btn-small">Save Map Settings</button> 
                            <a href="/genius-map/" class="btn-small btn btn-secondary">View Genius Map</a>
                        </p>
                    </form>
                    <div id="map-settings-response"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Toggle address fields based on checkbox
        $('#map_optin').change(function() {
            if ($(this).is(':checked')) {
                $('#map_address_fields').slideDown();
                $('#map_address_fields input, #map_address_fields select').attr('required', true);
            } else {
                $('#map_address_fields').slideUp();
                $('#map_address_fields input, #map_address_fields select').attr('required', false);
            }
        });

        // Handle form submission
        $('#map-settings-form').submit(function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=save_map_settings';
            formData += '&security=' + '<?php echo wp_create_nonce('directory_ajax_nonce'); ?>';

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function(response) {
                if (response.success) {
                    $('#map-settings-response').html('<div class="pmpro_success">' + response.data.message + '</div>');
                } else {
                    $('#map-settings-response').html('<div class="pmpro_error">' + response.data.message + '</div>');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});

/**
 * Get consolidated map data from various possible sources
 * This handles the mismatch between pmpromd_ and pmpromm_ prefixes
 */
function pmpromd_get_consolidated_map_data($user_id) {
    // Start with defaults
    $data = [
        'optin' => false,
        'street' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'country' => ''
    ];
    
    // Check if user was approved and had selected to show on map during signup
    $was_approved = get_user_meta($user_id, 'user_approved_timestamp', true);
    $signup_optin = get_user_meta($user_id, 'pmpromd_map_optin', true);
    
    // Priority 1: Check for saved consolidated data (from this plugin)
    $saved_data = get_user_meta($user_id, 'pmpromm_pin_location', true);
    if (is_array($saved_data) && !empty($saved_data)) {
        $data = array_merge($data, $saved_data);
    }
    
    // Priority 2: Check PMPro Member Directory fields (from signup form)
    $pmpromd_street = get_user_meta($user_id, 'pmpromd_street_name', true);
    $pmpromd_city = get_user_meta($user_id, 'pmpromd_city', true);
    
    if (!empty($pmpromd_street) || !empty($pmpromd_city)) {
        $data['street'] = $data['street'] ?: $pmpromd_street;
        $data['city'] = $data['city'] ?: $pmpromd_city;
        $data['state'] = $data['state'] ?: get_user_meta($user_id, 'pmpromd_state', true);
        $data['zip'] = $data['zip'] ?: get_user_meta($user_id, 'pmpromd_zip', true);
        $data['country'] = $data['country'] ?: get_user_meta($user_id, 'pmpromd_country', true);
        
        // If user was approved and had opted in during signup, enable the map
        if ($was_approved && $signup_optin && !isset($saved_data['optin'])) {
            $data['optin'] = true;
        }
    }
    
    // Priority 3: Check individual pmpromm_ fields (legacy)
    if (empty($data['street']) && empty($data['city'])) {
        $data['street'] = $data['street'] ?: get_user_meta($user_id, 'pmpromm_street_name', true);
        $data['city'] = $data['city'] ?: get_user_meta($user_id, 'pmpromm_city', true);
        $data['state'] = $data['state'] ?: get_user_meta($user_id, 'pmpromm_state', true);
        $data['zip'] = $data['zip'] ?: get_user_meta($user_id, 'pmpromm_zip', true);
        $data['country'] = $data['country'] ?: get_user_meta($user_id, 'pmpromm_country', true);
        $data['optin'] = $data['optin'] ?: (bool)get_user_meta($user_id, 'pmpromm_optin', true);
    }
    
    // Priority 4: Fall back to PMPro billing address
    if (empty($data['street']) && empty($data['city'])) {
        $data['street'] = $data['street'] ?: get_user_meta($user_id, 'pmpro_baddress1', true);
        $data['city'] = $data['city'] ?: get_user_meta($user_id, 'pmpro_bcity', true);
        $data['state'] = $data['state'] ?: get_user_meta($user_id, 'pmpro_bstate', true);
        $data['zip'] = $data['zip'] ?: get_user_meta($user_id, 'pmpro_bzipcode', true);
        $data['country'] = $data['country'] ?: get_user_meta($user_id, 'pmpro_bcountry', true);
    }
    
    return $data;
}

// AJAX handler for saving map settings
add_action('wp_ajax_save_map_settings', function () {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'directory_ajax_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'You must be logged in to save settings']);
    }
    
    $optin = isset($_POST['map_optin']) ? true : false;
    
    // Validate required fields if opting in
    if ($optin) {
        $required_fields = ['map_street', 'map_city', 'map_state', 'map_zip', 'map_country'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['message' => 'Please fill in all required fields.']);
            }
        }
    }
    
    // Prepare the data to save
    $map_data = [
        'optin'   => $optin,
        'street'  => sanitize_text_field($_POST['map_street'] ?? ''),
        'city'    => sanitize_text_field($_POST['map_city'] ?? ''),
        'state'   => sanitize_text_field($_POST['map_state'] ?? ''),
        'zip'     => sanitize_text_field($_POST['map_zip'] ?? ''),
        'country' => sanitize_text_field($_POST['map_country'] ?? ''),
    ];
    
    // Save consolidated data
    update_user_meta($user_id, 'pmpromm_pin_location', $map_data);
    
    // Also update individual fields for compatibility with PMPro Member Directory
    update_user_meta($user_id, 'pmpromd_map_optin', $optin);
    update_user_meta($user_id, 'pmpromd_street_name', $map_data['street']);
    update_user_meta($user_id, 'pmpromd_city', $map_data['city']);
    update_user_meta($user_id, 'pmpromd_state', $map_data['state']);
    update_user_meta($user_id, 'pmpromd_zip', $map_data['zip']);
    update_user_meta($user_id, 'pmpromd_country', $map_data['country']);
    
    // Also save with pmpromm_ prefix for backward compatibility
    update_user_meta($user_id, 'pmpromm_optin', $optin);
    update_user_meta($user_id, 'pmpromm_street_name', $map_data['street']);
    update_user_meta($user_id, 'pmpromm_city', $map_data['city']);
    update_user_meta($user_id, 'pmpromm_state', $map_data['state']);
    update_user_meta($user_id, 'pmpromm_zip', $map_data['zip']);
    update_user_meta($user_id, 'pmpromm_country', $map_data['country']);
    
    // Call the original save function if it exists
    if (function_exists('pmpromm_save_pin_location_fields')) {
        pmpromm_save_pin_location_fields($user_id);
    }
    
    wp_send_json_success(['message' => 'Your map settings have been updated. <a href="/genius-map">Visit Map</a>']);
});

/**
 * Hook into user approval to auto-enable map if they opted in during signup
 */
add_action('user_register', function($user_id) {
    // Store timestamp when user is approved
    if (function_exists('ftd_user_is_pending_approval') && !ftd_user_is_pending_approval($user_id)) {
        update_user_meta($user_id, 'user_approved_timestamp', time());
        
        // Check if they opted in during signup
        $signup_optin = get_user_meta($user_id, 'pmpromd_map_optin', true);
        if ($signup_optin) {
            // Enable the map in our consolidated data
            $map_data = pmpromd_get_consolidated_map_data($user_id);
            $map_data['optin'] = true;
            update_user_meta($user_id, 'pmpromm_pin_location', $map_data);
            update_user_meta($user_id, 'pmpromm_optin', true);
        }
    }
});

/**
 * Add JavaScript to make PMPro Member Directory fields required during checkout
 */
add_action('pmpro_checkout_after_billing_fields', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Make map fields required if user opts in
        var mapOptinField = $('#pmpromd_map_optin');
        var mapFields = $('.pmpromd-map-address-field');
        
		// Try to apply 2-column grid to the container that holds these fields
		function applyMapGridLayout() {
			var container = mapFields.first().closest('.pmpro_form_fields');
			if (!container.length) {
				// Fallback: try the immediate parent group
				container = mapFields.first().closest('.pmpro_checkout-fields, .pmpro_billing-fields, form.pmpro_form');
			}
			if (container.length) {
				container.addClass('pmpromd-map-grid');
			}
			// Also normalize each field wrapper if present
			mapFields.each(function() {
				$(this).closest('.pmpro_form_field').addClass('pmpromd-map-grid-item');
			});
		}
		
		// Update specific label text to requested wording
		function relabelMapFields() {
			var mappings = [
				{ key: 'street', text: 'Street Address' },
				{ key: 'state', text: 'State / County' },
				{ key: 'zip', text: 'Zip Code / Postal Code' }
			];
			
			mapFields.each(function() {
				var $field = $(this);
				var ident = ($field.attr('id') || '') + ' ' + ($field.attr('name') || '');
				var $label = $();
				if ($field.attr('id')) {
					$label = $('label[for="' + $field.attr('id') + '"]');
				}
				if (!$label.length && $field.attr('name')) {
					$label = $('label[for="' + $field.attr('name') + '"]');
				}
				if (!$label.length) return;
				
				for (var i = 0; i < mappings.length; i++) {
					if (ident.indexOf(mappings[i].key) !== -1) {
						var hasReq = $label.find('.pmpro_required').length > 0;
						$label.html(mappings[i].text + (hasReq ? ' <span class="pmpro_required">*</span>' : ''));
						break;
					}
				}
			});
		}
		
        function toggleMapFieldsRequired() {
            if (mapOptinField.is(':checked')) {
                mapFields.each(function() {
                    $(this).attr('required', true);
                    // Add visual indicator
                    var label = $('label[for="' + $(this).attr('id') + '"]');
                    if (label.length && !label.find('.pmpro_required').length) {
                        label.append(' <span class="pmpro_required">*</span>');
                    }
                });
            } else {
                mapFields.each(function() {
                    $(this).attr('required', false);
                    // Remove visual indicator
                    var label = $('label[for="' + $(this).attr('id') + '"]');
                    label.find('.pmpro_required').remove();
                });
            }
        }
        
        // Initial check
		applyMapGridLayout();
		relabelMapFields();
        toggleMapFieldsRequired();
        
        // Toggle on change
        mapOptinField.change(toggleMapFieldsRequired);
        
        // Validate before form submission
        $('form.pmpro_form').submit(function(e) {
            if (mapOptinField.is(':checked')) {
				var $form = $(this);
				var $submitButtons = $form.find('input[type="submit"], button[type="submit"]');
				var $processingEls = $('#pmpro_processing_message, #pmpro_processing, .pmpro_checkout_processing');
                var hasEmptyFields = false;
				var $firstError = null;
                mapFields.each(function() {
                    if (!$(this).val()) {
                        hasEmptyFields = true;
                        $(this).addClass('pmpro_error');
						if (!$firstError) {
							$firstError = $(this);
						}
                    } else {
                        $(this).removeClass('pmpro_error');
                    }
                });
                
                if (hasEmptyFields) {
                    alert('Please fill in all required map location fields.');
                    e.preventDefault();
					
					// Clear any "processing" UI that PMPro may have shown.
					$('body').removeClass('pmpro_processing');
					$submitButtons.prop('disabled', false).removeClass('disabled');
					$processingEls.hide();
					
					// Scroll to the first errored field for clarity.
					if ($firstError && $firstError.length) {
						$('html, body').animate({ scrollTop: $firstError.offset().top - 80 }, 300);
						$firstError.trigger('focus');
					}
                    return false;
                }
            }
        });
    });
    </script>
    <?php
});

/**
 * Add CSS for required field indicators
 */
add_action('wp_head', function() {
    ?>
    <style>
    .pmpro_required {
        color: #ff0000;
    }
    .pmpro_form_input.pmpro_error {
        border-color: #ff0000;
    }
	
	/* Two-column layout for map address fields at checkout */
	.pmpromd-map-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 16px 16px;
		align-items: start;
	}
	.pmpromd-map-grid .pmpro_form_field {
		margin-bottom: 0;
	}
    .pmpro_success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
    }
    .pmpro_error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    /* Top Affiliates styling */
    .ftd-affiliate-avatar {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        margin-right: 8px !important;
        vertical-align: middle !important;
        display: inline-block !important;
        border: 2px solid #ddd;
    }
    
    .ftd-affiliate-link {
        display: inline-flex !important;
        align-items: center !important;
        text-decoration: none !important;
        color: inherit !important;
        gap: 8px;
    }
    
    .ftd-affiliate-link:hover {
        text-decoration: underline !important;
    }
    
    .ftd-affiliate-link img {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
    }
    
    .card h2 {
        margin-top: 0;
        margin-bottom: 16px;
    }
    </style>
    <?php
});

// Shortcode: [ftd_top_affiliates]  - Displays a leaderboard of top affiliates based on referrals   

// [ftd_top_affiliates limit="10"] – Top affiliates by unique referred members (PMPro Affiliates)
function ftd_top_affiliates_shortcode($atts = []) {
	global $wpdb;

	// Only show to logged-in users; return nothing for guests.
	if (!is_user_logged_in()) {
		return '';
	}

	$atts = shortcode_atts([
		'limit' => 10,
	], $atts, 'ftd_top_affiliates');

	$limit = (int) $atts['limit'];
	if ($limit < 1) {
		$limit = 10;
	}

	// Cache to keep widgets light. Key varies by limit.
	$cache_key = 'ftd_top_affiliates_v3_' . $limit;
	$cached = get_transient($cache_key);
	if ($cached !== false) {
		return $cached;
	}

	$leaderboard = [];

	// Prefer accurate counts via PMPro Affiliates + Orders tables if available.
	// Count distinct referred users per affiliate, excluding non-success statuses.
	if (isset($wpdb->pmpro_affiliates)) {
		$orders_table = $wpdb->pmpro_membership_orders ?? $wpdb->prefix . 'pmpro_membership_orders';
		$aff_table = $wpdb->pmpro_affiliates; // set by the add-on on init

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.affiliateuser AS affiliate_user_login, a.code AS affiliate_code, COUNT(DISTINCT o.user_id) AS cnt\n				 FROM {$orders_table} o\n				 JOIN {$aff_table} a ON a.id = o.affiliate_id\n				 WHERE o.status NOT IN('pending','error','refunded','refund','token','review')\n				 GROUP BY a.id\n				 HAVING cnt > 0\n				 ORDER BY cnt DESC\n				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		if (!empty($rows)) {
			foreach ($rows as $r) {
				$login = trim($r['affiliate_user_login']);
				if ($login === '') {
					continue;
				}
				$user_id = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
						$login
					)
				);
				if (!$user_id) {
					// Fallback to user_nicename just in case
					$user_id = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s LIMIT 1",
							$login
						)
					);
				}
				if ($user_id) {
					$user = get_user_by('id', $user_id);
					if ($user) {
						$leaderboard[] = [
							'user_id'      => $user_id,
							'display_name' => $user->display_name ?: $user->user_login,
							'referrals'    => (int) $r['cnt'],
						];
					}
				}
			}
		}
	}

	// Fallback for sites without PMPro Affiliates table: count by usermeta recorded affiliate code on referred users.
	if (empty($leaderboard)) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value AS code, COUNT(*) AS cnt\n				 FROM {$wpdb->usermeta}\n				 WHERE meta_key = %s\n				 GROUP BY meta_value\n				 HAVING cnt > 0\n				 ORDER BY cnt DESC\n				 LIMIT %d",
				'pmpro_affiliate',
				$limit
			),
			ARRAY_A
		);

		foreach ($rows as $r) {
			$code = trim($r['code']);
			$count = (int) $r['cnt'];
			if ($count < 1 || $code === '') {
				continue;
			}
			// Try mapping code -> user via usermeta pmpro_affiliate_code
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
					'pmpro_affiliate_code',
					$code
				)
			);
			if (!$user_id) {
				$user_id = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
						$code
					)
				);
			}
			if ($user_id) {
				$user = get_user_by('id', $user_id);
				if ($user) {
					$leaderboard[] = [
						'user_id'      => $user_id,
						'display_name' => $user->display_name ?: $user->user_login,
						'referrals'    => $count,
					];
				}
			}
		}
	}
    $out  = '<div class="card">';
	if (empty($leaderboard)) {
		$out  .= '<table class="wp-block-table is-style-stripes" style="width: 100%;
text-align: center;">';
		$out .= '<thead><tr><th>Affiliate</th><th>Referrals</th></tr></thead>';
		$out .= '<tbody><tr><td colspan="2"><em>No affiliate referrals yet.</em></td></tr></tbody></table>';
		set_transient($cache_key, $out, MINUTE_IN_SECONDS * 10);
		return $out;
	}

	// Render table (already ordered and limited when using orders table)
	// If using fallback, ensure sorted/limited as well
	usort($leaderboard, function($a, $b){ return $b['referrals'] <=> $a['referrals']; });
	$leaderboard = array_slice($leaderboard, 0, $limit);

    
    $out  .= '<h2>Top Affiliates</h2>';
	$out  .= '<table class="wp-block-table is-style-stripes" style="width: 100%;
text-align: center;">';
	$out .= '<thead><tr><th>Affiliate</th><th>Referrals</th></tr></thead><tbody>';

	foreach ($leaderboard as $row) {
		$user = get_user_by('id', $row['user_id']);
		$default_profile_url = home_url('/profile/' . $user->user_login . '/');
		// Allow theme/plugins to override where to link via filter.
		$profile_url = apply_filters('ftd_top_affiliates_profile_url', $default_profile_url, $row['user_id']);
		
		// Get user avatar
		$avatar = get_avatar($row['user_id'], 32, '', $row['display_name'], ['class' => 'ftd-affiliate-avatar']);
		
		$out .= '<tr>';
		$out .= '<td style="text-align: left;"><a href="' . esc_url($profile_url) . '" class="ftd-affiliate-link">' . $avatar . '<span>' . esc_html($row['display_name']) . '</span></a></td>';
		$out .= '<td style="text-align: center;">' . esc_html($row['referrals']) . '</td>';
		$out .= '</tr>';
	}

	$out .= '</tbody></table>';
    $out .= '</div>';
	// cache 10 minutes
	set_transient($cache_key, $out, MINUTE_IN_SECONDS * 10);
	return $out;
}
add_shortcode('ftd_top_affiliates', 'ftd_top_affiliates_shortcode');

// [ftd_top_affiliates_month limit="10"] – Top affiliates by referrals for current month only
function ftd_top_affiliates_month_shortcode($atts = []) {
	global $wpdb;

	// Only show to logged-in users; return nothing for guests.
	if (!is_user_logged_in()) {
		return '';
	}

	$atts = shortcode_atts([
		'limit' => 10,
	], $atts, 'ftd_top_affiliates_month');

	$limit = (int) $atts['limit'];
	if ($limit < 1) {
		$limit = 10;
	}

	// Get current month start and end dates
	$month_start = date('Y-m-01 00:00:00');
	$month_end = date('Y-m-t 23:59:59');

	// Cache to keep widgets light. Key varies by limit and month.
	$cache_key = 'ftd_top_affiliates_month_v1_' . $limit . '_' . date('Y-m');
	$cached = get_transient($cache_key);
	if ($cached !== false) {
		return $cached;
	}

	$leaderboard = [];

	// Prefer accurate counts via PMPro Affiliates + Orders tables if available.
	// Count distinct referred users per affiliate for current month only, excluding non-success statuses.
	if (isset($wpdb->pmpro_affiliates)) {
		$orders_table = $wpdb->pmpro_membership_orders ?? $wpdb->prefix . 'pmpro_membership_orders';
		$aff_table = $wpdb->pmpro_affiliates; // set by the add-on on init

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.affiliateuser AS affiliate_user_login, a.code AS affiliate_code, COUNT(DISTINCT o.user_id) AS cnt
				 FROM {$orders_table} o
				 JOIN {$aff_table} a ON a.id = o.affiliate_id
				 WHERE o.status NOT IN('pending','error','refunded','refund','token','review')
				 AND o.timestamp >= %s AND o.timestamp <= %s
				 GROUP BY a.id
				 HAVING cnt > 0
				 ORDER BY cnt DESC
				 LIMIT %d",
				$month_start,
				$month_end,
				$limit
			),
			ARRAY_A
		);

		if (!empty($rows)) {
			foreach ($rows as $r) {
				$login = trim($r['affiliate_user_login']);
				if ($login === '') {
					continue;
				}
				$user_id = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
						$login
					)
				);
				if (!$user_id) {
					// Fallback to user_nicename just in case
					$user_id = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s LIMIT 1",
							$login
						)
					);
				}
				if ($user_id) {
					$user = get_user_by('id', $user_id);
					if ($user) {
						$leaderboard[] = [
							'user_id'      => $user_id,
							'display_name' => $user->display_name ?: $user->user_login,
							'referrals'    => (int) $r['cnt'],
						];
					}
				}
			}
		}
	}

	// Fallback for sites without PMPro Affiliates table: count by usermeta recorded affiliate code on referred users.
	// Note: This fallback doesn't have date filtering capability, so it will show all-time data
	if (empty($leaderboard)) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value AS code, COUNT(*) AS cnt
				 FROM {$wpdb->usermeta}
				 WHERE meta_key = %s
				 GROUP BY meta_value
				 HAVING cnt > 0
				 ORDER BY cnt DESC
				 LIMIT %d",
				'pmpro_affiliate',
				$limit
			),
			ARRAY_A
		);

		foreach ($rows as $r) {
			$code = trim($r['code']);
			$count = (int) $r['cnt'];
			if ($count < 1 || $code === '') {
				continue;
			}
			// Try mapping code -> user via usermeta pmpro_affiliate_code
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
					'pmpro_affiliate_code',
					$code
				)
			);
			if (!$user_id) {
				$user_id = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
						$code
					)
				);
			}
			if ($user_id) {
				$user = get_user_by('id', $user_id);
				if ($user) {
					$leaderboard[] = [
						'user_id'      => $user_id,
						'display_name' => $user->display_name ?: $user->user_login,
						'referrals'    => $count,
					];
				}
			}
		}
	}

	$out  = '<div class="card">';
    $out  = '<h2>Top Affiliates - ' . date('F Y') . '</h2>';
	if (empty($leaderboard)) {
		$out  .= '<table class="wp-block-table is-style-stripes" style="width: 100%; text-align: center;">';
		$out .= '<thead><tr><th>Affiliate</th><th>Referrals</th></tr></thead>';
		$out .= '<tbody><tr><td colspan="2"><em>No affiliate referrals this month yet.</em></td></tr></tbody></table>';
		set_transient($cache_key, $out, MINUTE_IN_SECONDS * 10);
		return $out;
	}

	// Render table (already ordered and limited when using orders table)
	// If using fallback, ensure sorted/limited as well
	usort($leaderboard, function($a, $b){ return $b['referrals'] <=> $a['referrals']; });
	$leaderboard = array_slice($leaderboard, 0, $limit);

	$out  .= '<h2>Top Affiliates - ' . date('F Y') . '</h2>';
	$out  .= '<table class="wp-block-table is-style-stripes" style="width: 100%; text-align: center;">';
	$out .= '<thead><tr><th>Affiliate</th><th>Referrals</th></tr></thead><tbody>';

	foreach ($leaderboard as $row) {
		$user = get_user_by('id', $row['user_id']);
		$default_profile_url = home_url('/profile/' . $user->user_login . '/');
		// Allow theme/plugins to override where to link via filter.
		$profile_url = apply_filters('ftd_top_affiliates_profile_url', $default_profile_url, $row['user_id']);
		
		// Get user avatar
		$avatar = get_avatar($row['user_id'], 32, '', $row['display_name'], ['class' => 'ftd-affiliate-avatar']);
		
		$out .= '<tr>';
		$out .= '<td style="text-align: left;"><a href="' . esc_url($profile_url) . '" class="ftd-affiliate-link">' . $avatar . '<span>' . esc_html($row['display_name']) . '</span></a></td>';
		$out .= '<td style="text-align: center;">' . esc_html($row['referrals']) . '</td>';
		$out .= '</tr>';
	}

	$out .= '</tbody></table>';
	$out .= '</div>';

	// cache 10 minutes
	set_transient($cache_key, $out, MINUTE_IN_SECONDS * 10);
	return $out;
}
add_shortcode('ftd_top_affiliates_month', 'ftd_top_affiliates_month_shortcode');

/**
 * Custom user-friendly PMP Affiliates Report shortcode override
 * Goal: super simple—just show the user's code and a ready-to-copy link.
 */
function ftd_custom_affiliates_report_shortcode($atts, $content = null, $code = '') {
	// Logged out: show nothing.
	if (!is_user_logged_in()) {
		return '';
	}

	$pmpro_affiliates = function_exists('pmpro_affiliates_getAffiliatesForUser')
		? pmpro_affiliates_getAffiliatesForUser()
		: array();

	// Pick the primary/first code if multiple.
	$primary_code = '';
	if (!empty($pmpro_affiliates)) {
		// Each item typically has ->code
		$primary_code = trim((string) $pmpro_affiliates[0]->code);
	}

	// If we still don't have a code, try the user meta fallback.
	if ($primary_code === '') {
		$user_id = get_current_user_id();
		$meta_code = get_user_meta($user_id, 'pmpro_affiliate_code', true);
		if (is_string($meta_code) && $meta_code !== '') {
			$primary_code = $meta_code;
		}
	}

	// If no code is found, keep it simple: short friendly message.
	if ($primary_code === '') {
		return '<div class="pmpro pmpro-card"><div class="pmpro_card pmpro_card_content"><p>You don\'t have an affiliate code yet.</p></div></div>';
	}

	$base_url = site_url('/');
	$link = trailingslashit($base_url) . '?pa=' . rawurlencode($primary_code);
	
	// Build WhatsApp share message
	$site_name = get_bloginfo('name');
	$default_message = sprintf('Join %s with my link: %s', $site_name, $link);
	$whatsapp_message = apply_filters('ftd_affiliate_whatsapp_message', $default_message, $primary_code, $link);
	$whatsapp_url = 'https://api.whatsapp.com/send?text=' . rawurlencode($whatsapp_message);

	ob_start();
	?>
	<div class="pmpro" style="max-width: 720px;">
		<div class="pmpro_card">
			<div class="pmpro_card_content">
				<h2 class="pmpro_card_title pmpro_font-large" style="margin-bottom: 12px;">Share With Geniuses</h2>
				<p>Use this link to share your We Are Geniuses with others to earn rewards.</p>
				<div class="pmpro_form_field pmpro_form_field-text" style="display:block;">
					<input type="text" id="ftd_aff_link" readonly value="<?php echo esc_url($link); ?>" class="pmpro_form_input pmpro_form_input-text" style="font-size:16px; padding:10px; width:100%; background:#f8f9fa; border:2px solid #e9ecef;">
					<button type="button" class="button button-primary" onclick="navigator.clipboard.writeText(document.getElementById('ftd_aff_link').value)" style="margin-top:8px; width:100%;">Copy Link</button>
				</div>
				<div style="margin-top: 12px; display: flex; gap: 8px;">
					<a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="button" style="flex: 1; text-align: center; background-color: #25D366; color: white; border-color: #25D366; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle;">
							<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
						</svg>
						Share on WhatsApp
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php
	$out = ob_get_clean();
	return $out;
}

// Ensure our override wins: re-register on init after PMPro loads its shortcodes.
function ftd_register_custom_pmpro_affiliates_report_shortcode() {
	// Remove any existing handler first, then add ours.
	remove_shortcode('pmpro_affiliates_report');
	add_shortcode('pmpro_affiliates_report', 'ftd_custom_affiliates_report_shortcode');
}
add_action('init', 'ftd_register_custom_pmpro_affiliates_report_shortcode', 1000);

/**
 * Sidebar-friendly affiliate share card for logged-in users.
 * Usage: [ftd_affiliate_sidebar]
 */
function ftd_affiliate_sidebar_shortcode($atts = []) {
	// Only show to logged-in users; return nothing for guests.
	if (!is_user_logged_in()) {
		return '';
	}
	
	// Resolve user's primary affiliate code using existing add-on if available, else fallback to user meta.
	$pmpro_affiliates = function_exists('pmpro_affiliates_getAffiliatesForUser')
		? pmpro_affiliates_getAffiliatesForUser()
		: array();
	$primary_code = '';
	if (!empty($pmpro_affiliates)) {
		$primary_code = trim((string) $pmpro_affiliates[0]->code);
	}
	if ($primary_code === '') {
		$user_id = get_current_user_id();
		$meta_code = get_user_meta($user_id, 'pmpro_affiliate_code', true);
		if (is_string($meta_code) && $meta_code !== '') {
			$primary_code = $meta_code;
		}
	}
	// If still no code, keep UI minimal.
	if ($primary_code === '') {
		return '<div class="pmpro pmpro_card"><div class="pmpro_card_content"><p>You don\'t have an affiliate code yet.</p></div></div>';
	}
	
	// Build share link
	$base_url = site_url('/');
	$link = trailingslashit($base_url) . '?pa=' . rawurlencode($primary_code);
	
	// Build WhatsApp share message
	$site_name = get_bloginfo('name');
	$default_message = sprintf('Join %s with my link: %s', $site_name, $link);
	$whatsapp_message = apply_filters('ftd_affiliate_whatsapp_message', $default_message, $primary_code, $link);
	$whatsapp_url = 'https://api.whatsapp.com/send?text=' . rawurlencode($whatsapp_message);
	
	// Determine referrals page URL, allow override via filter
	$default_referrals_url = home_url('/affiliates/');
	$referrals_url = home_url('/my-referrals/');
	
	// Unique ID for input field (avoid collisions if shortcode appears multiple times)
	$uid = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : (string) mt_rand(1000, 999999);
	$input_id = 'ftd_aff_link_' . $uid;
	
	ob_start();
	?>
	<div class="pmpro" style="max-width: 100%;">
		<div class="pmpro_card">
			<div class="pmpro_card_content">
				<h4 class="has-text-align-center" style="margin-top: 24px;margin-bottom: 8px;"><span class="text-purple">Share With Friends</span></h4>
				<p style="margin-top:0;">Use the your link to share We Are Geniuses with friends, earn rewards & grow our network!</p>
				<div class="pmpro_form_field pmpro_form_field-text" style="display:block;">
					<input type="text" id="<?php echo esc_attr($input_id); ?>" readonly value="<?php echo esc_url($link); ?>" class="pmpro_form_input pmpro_form_input-text" style="font-size:14px; padding:10px; width:100%; background:#f8f9fa; border:2px solid #e9ecef;">
					<button type="button" class="button button-primary" onclick="navigator.clipboard.writeText(document.getElementById('<?php echo esc_js($input_id); ?>').value)" style="margin-top:8px; width:100%;">Copy Link</button>
				</div>
				<a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="button" style="display:flex; width:100%; box-sizing:border-box; text-align:center; margin-top:8px; background-color: #25D366; color: white; border-color: #25D366; align-items: center; justify-content: center; gap: 8px;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle;">
						<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
					</svg>
					Share on WhatsApp
				</a>
				<a href="<?php echo esc_url($referrals_url); ?>" class="button" style="display:block; width:100%; box-sizing:border-box; text-align:center; margin-top:8px;">View Your Referrals</a>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('ftd_affiliate_sidebar', 'ftd_affiliate_sidebar_shortcode');
