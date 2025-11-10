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
        toggleMapFieldsRequired();
        
        // Toggle on change
        mapOptinField.change(toggleMapFieldsRequired);
        
        // Validate before form submission
        $('form.pmpro_form').submit(function(e) {
            if (mapOptinField.is(':checked')) {
                var hasEmptyFields = false;
                mapFields.each(function() {
                    if (!$(this).val()) {
                        hasEmptyFields = true;
                        $(this).addClass('pmpro_error');
                    } else {
                        $(this).removeClass('pmpro_error');
                    }
                });
                
                if (hasEmptyFields) {
                    alert('Please fill in all required map location fields.');
                    e.preventDefault();
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
    </style>
    <?php
});