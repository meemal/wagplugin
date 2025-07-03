<?php
// Shortcode: [member_map_settings_form]
add_shortcode('member_map_settings_form', function () {
  if (!is_user_logged_in()) {
      return '<p>Please <a href="' . wp_login_url() . '">log in</a> to manage your map settings.</p>';
  }

  $user_id = get_current_user_id();
  $pin = get_user_meta($user_id, 'pmpromm_pin_location', true) ?: [];

  $map_enabled = !empty($pin['optin']);
  $street      = $pin['street']  ?? '';
  $city        = $pin['city']    ?? '';
  $state       = $pin['state']   ?? '';
  $zip         = $pin['zip']     ?? '';
  $country     = $pin['country'] ?? '';

  ob_start(); ?>
<div id="pmpro_form_fieldset-map-settings" class="pmpro">
  <h2 class="pmpro_section_title pmpro_font-x-large">My Genius Map Listing</h2>
  <div class="pmpro_card">
    <div class="pmpro_account-section">
      <div class="pmpro_card_content">   
        <form id="map-settings-form" class="pmpro_form" style="max-width:600px;">
          <?php wp_nonce_field('save_map_settings', 'map_settings_nonce'); ?>

          <div class="pmpro_form_field pmpro_form_field-checkbox">
            <label class="pmpro_form_label pmpro_form_label-inline pmpro_clickable">
              <input type="checkbox" name="pmpromm_optin" class="pmpro_form_input pmpro_form_input-checkbox" <?php checked($map_enabled); ?>>
              Show on Membership Map
            </label>
          </div>

          <br>
          <div id="pmpromm_address_fields" class="pmpro_form_fields " style="display: block !important;">
            <div class="pmpro_form_field pmpro_form_field-text pmpro_form_field-pmpromm_street_name">
              <label for="pmpromm_street_name">Street Address</label>
              <input type="text" id="pmpromm_street_name" name="pmpromm_street_name" class="pmpro_form_input pmpro_form_input-text" value="<?php echo esc_attr($street); ?>">
            </div>
            <div class="pmpro_form_field pmpro_form_field-text pmpro_form_field-pmpromm_city">
              <label for="pmpromm_city">City</label>
              <input type="text" id="pmpromm_city" name="pmpromm_city" class="pmpro_form_input pmpro_form_input-text" value="<?php echo esc_attr($city); ?>">
            </div>
            <div class="pmpro_form_field pmpro_form_field-text pmpro_form_field-pmpromm_state">
              <label for="pmpromm_state">State / County</label>
              <input type="text" id="pmpromm_state" name="pmpromm_state" class="pmpro_form_input pmpro_form_input-text" value="<?php echo esc_attr($state); ?>">
            </div>
            <div class="pmpro_form_field pmpro_form_field-text pmpro_form_field-pmpromm_zip">
              <label for="pmpromm_zip">Zip / Post Code</label>
              <input type="text" id="pmpromm_zip" name="pmpromm_zip" class="pmpro_form_input pmpro_form_input-text" value="<?php echo esc_attr($zip); ?>">
            </div>
            <div class="pmpro_form_field pmpro_form_field-select pmpro_form_field-pmpromm_country">
              <label for="pmpromm_country">Country</label>
              <select name="pmpromm_country" id="pmpromm_country" class="pmpro_form_input pmpro_form_input-select">
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
          <p><button type="submit" class="btn btn-small">Save Map Settings</button> <a href="/genius-map/" class="btn-small btn btn-secondary">View Genius Map</a></p>
        </form>
        <div id="map-settings-response"></div>
      </div>
    </div>
  </div>
</div>
<?php
  return ob_get_clean();
});

// AJAX handler
add_action('wp_ajax_save_map_settings', function () {
  if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'directory_ajax_nonce')) {
    wp_send_json_error(['message' => 'Security check failed']);
  }
  $user_id = get_current_user_id();
  $new_pin = [
    'optin'   => isset($_POST['pmpromm_optin']) ? true : false,
    'street'  => sanitize_text_field($_POST['pmpromm_street_name']),
    'city'    => sanitize_text_field($_POST['pmpromm_city']),
    'state'   => sanitize_text_field($_POST['pmpromm_state']),
    'zip'     => sanitize_text_field($_POST['pmpromm_zip']),
    'country' => sanitize_text_field($_POST['pmpromm_country']),
  ];
  update_user_meta($user_id, 'pmpromm_pin_location', $new_pin);
  if (function_exists('pmpromm_save_pin_location_fields')) {
    pmpromm_save_pin_location_fields($user_id);
  }
  wp_send_json_success(['message' => 'Your map settings have been updated. <a href="/genius-map">Visit Map</a>']);
});

