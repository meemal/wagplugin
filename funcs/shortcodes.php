<?php

function ftd_genius_buttons_shortcode() {
  $output = '<div style="text-align: center;">';

  if (!is_user_logged_in()) {
      // User not logged in
      $output .= '
          <a href="/membership-account/join-we-are-geniuses/" class="btn btn-outline">JOIN THE GENIUSES</a><br>
          <p style="color:white;padding-top:16px;">Already a member? <a href="/login/" >Log in</a></p>
      ';
  } else {
      // Logged in user
      $user_id = get_current_user_id();
      $level = pmpro_getMembershipLevelForUser($user_id);
      $level_id = $level ? (int) $level->id : 0;

      $directory = '<a href="/genius-directory" class="btn">GENIUS DIRECTORY</a>';
      $map = '<a href="/genius-map" class="btn btn-secondary">GENIUS MAP</a>';
      $upgrade = '<a href="/membership-account/membership-checkout/" class="btn btn-outline">UPGRADE NOW TO SHARE YOUR GENIUS</a>';

      if ($level_id === 1) {
          $output .= "$directory<br>$upgrade";
      } elseif ($level_id === 2) {
          $output .= "$directory  $map<br>$upgrade";
      } elseif ($level_id >= 3) {
          $output .= "$directory  $map";
      } else {
          $output .= $directory;
      }
  }

  $output .= '</div>';
  return $output;
}
add_shortcode('genius_buttons', 'ftd_genius_buttons_shortcode');

function ftd_welcome_genius_shortcode() {
  if (!is_user_logged_in()) {
      return ''; // Show nothing to non-logged-in users
  }

  $user = wp_get_current_user();
  $first_name = $user->first_name ? esc_html($user->first_name) : esc_html($user->display_name);

  return "<h3 style='text-align:center;'>Welcome <span class='golden'>{$first_name}</span>, great to have you here!</h3>";
}
add_shortcode('welcome_genius', 'ftd_welcome_genius_shortcode');


function ftd_genius_cta_shortcode($atts) {
    $atts = shortcode_atts([
        'level_id' => 1,
        'title' => 'Quantum Genius',
        'subtitle' => 'For advanced Joe Dispenza students',
        'body' => 'Along with all the other benefits, with this level you can share up to 3 genius directory listings!',
        'button_text' => '', // will be dynamically set
    ], $atts);

    $level_id = (int)$atts['level_id'];
    $level = function_exists('pmpro_getLevel') ? pmpro_getLevel($level_id) : null;
    $price = $level ? pmpro_getLevelCost($level) : '';
    $description = shortcode_exists('pmpro_level_description') ? do_shortcode('[pmpro_level_description id="' . $level_id . '"]') : '';

    $user_id = get_current_user_id();
    $user_level = $user_id ? pmpro_getMembershipLevelForUser($user_id) : null;
    $user_level_id = $user_level ? (int)$user_level->id : 0;

    // Set button logic
    $button_class = 'btn';
    if (!is_user_logged_in()) {
        $button_text = 'JOIN';
        $button_link = esc_url(site_url("/membership-checkout/?level={$level_id}"));
    } elseif ($user_level_id === $level_id) {
        $button_text = 'Your Level';
        $button_link = '';
    } elseif ($user_level_id < $level_id) {
        $button_text = 'Upgrade';
        $button_link = esc_url(site_url("/membership-checkout/?level={$level_id}"));
    } else {
        $button_text = 'Downgrade';
        $button_class .= ' btn-secondary';
        $button_link = esc_url(site_url("/membership-checkout/?level={$level_id}"));
    }

    ob_start();
    ?>
    <div class="genius-cta">
        <div class="cta-badge"><?php echo esc_html($level_id); ?></div>
        <h2 class="cta-title"><?php echo esc_html($atts['title']); ?></h2>
        <strong class="cta-subtitle"><?php echo esc_html($atts['subtitle']); ?></strong>
        <p class="cta-body"><?php echo !empty($description) ? wp_kses_post($description) : esc_html($atts['body']); ?></p>
        <?php if (!empty($price)): ?>
            <p class="cta-price"><?php echo wp_kses_post($price); ?></p>
        <?php endif; ?>
        <?php if ($button_link): ?>
            <a href="<?php echo $button_link; ?>" class="<?php echo esc_attr($button_class); ?>"><?php echo esc_html($button_text); ?></a>
        <?php else: ?>
            <span class="btn disabled"><?php echo esc_html($button_text); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('genius_levels_cta', 'ftd_genius_cta_shortcode');




function ftd_pmpro_level_description_shortcode($atts) {
  $atts = shortcode_atts([
      'id' => 1, // Default to level ID 1
  ], $atts);

  $level = pmpro_getLevel($atts['id']);
  if ($level && !empty($level->description)) {
      return wp_kses_post($level->description);
  }

  return '';
}
add_shortcode('pmpro_level_description', 'ftd_pmpro_level_description_shortcode');
