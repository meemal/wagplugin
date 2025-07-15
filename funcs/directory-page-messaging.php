<?php
function directory_page_top_messaging(){


  $user_id = get_current_user_id();

  if ( ! is_user_logged_in() ) {
  
    echo ftd_alert_box([
    'heading' => 'Access Restricted',
    'body' => 'You must be a member to view this page. Already a member? <a href="/login/">Login</a>.',
    'type' => 'danger'
    ]);
    echo '<div class="wp-block-columns alignwide is-layout-flex wp-block-columns has-global-padding">';
    echo '<div class="wp-block-column">';
    ftd_render_directory_cta_by_context('view directory');
    echo '</div>';
    echo '<div class="wp-block-column">';
    ftd_render_directory_cta_by_context('add directory');
    echo '</div>';
    echo '</div>';

    return false;
    exit;
}

  if ( function_exists('ftd_user_is_pending_approval') && ftd_user_is_pending_approval($user_id) ) {
          echo ftd_alert_box([
      'heading' => 'Access Pending Approval',
      'body' => 'We’re reviewing your membership application. You’ll receive an email as soon as you’re approved.',
      'type' => 'info'
      ]);

      echo '<div class="wp-block-column">';
      ftd_render_directory_cta_by_context('awaiting approval');
      echo '</div>';
      return false;
      exit;
  }
  return true;
}

function ftd_directory_upgrade_msg(){
  $user_id = get_current_user_id();
  $membership = pmpro_getMembershipLevelForUser($user_id);
  $leveltoadd = get_first_allowed_membership_level();
  if ( ! $membership || (int)$membership->id < (int)$leveltoadd ) {

    echo '<div class="wp-block-column">';
    ftd_render_directory_cta_by_context('upgrade required',   'image-left');
    echo '</div>';
  }
}



function ftd_render_directory_cta_by_context($context = 'logged out', $layout = 'center') {
    $rows = get_field('listing_cta_content', 'option');
    if (empty($rows)) return;

    foreach ($rows as $row) {
        if (isset($row['context']) && strtolower(trim($row['context'])) === strtolower($context)) {
            $heading = $row['listing_cta_heading'] ?? '';
            $body = $row['listing_cta_body'] ?? '';
            $button_label = $row['listings_cta_button_label'] ?? '';
            $button_url = $row['listings_cta_button_url'] ?? '';
            $sub_text = $row['listings_cta_sub_text'] ?? '';
            $image = $row['dl_cta_image'] ?? '';

            if ($layout === 'image-left' && $image && isset($image['url'])) {
                echo '<div class="card directory-cta-block alignwide" style="padding:2rem;">';
                echo '<div class="wp-block-columns is-layout-flex" style="gap:2rem; align-items:center;">';

                echo '<div class="wp-block-column" style="flex-basis:40%">';
                echo '<img class="std-border-radius" src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt'] ?? '') . '" style="width:100%; height:auto;" />';
                echo '</div>';

                echo '<div class="wp-block-column" style="flex-basis:60%">';
                if ($heading) echo '<h2 class="text-purple">' . esc_html($heading) . '</h2>';
                if ($body) echo '<p class="text-midgrey">' . wp_kses_post($body) . '</p>';
                if ($button_label && $button_url) {
                    echo '<a href="' . esc_url($button_url) . '" class="btn">' . esc_html($button_label) . '</a>';
                }
                if ($sub_text) {
                    echo '<p class="text-small text-midgrey" style="margin-top: 1rem;"><em>' . esc_html($sub_text) . '</em></p>';
                }
                echo '</div>'; // end column

                echo '</div>'; // end columns
                echo '</div>'; // end card

            } else {
                echo '<div class="card directory-cta-block has-text-align-center" style="max-width:600px;margin-left:auto;margin-right:auto;">';
                if ($heading) echo '<h2 class="text-purple">' . esc_html($heading) . '</h2>';
                if ($body) echo '<p class="text-midgrey">' . wp_kses_post($body) . '</p>';
                if ($button_label && $button_url) {
                    echo '<a href="' . esc_url($button_url) . '" class="btn">' . esc_html($button_label) . '</a>';
                }
                if ($image && isset($image['url'])) {
                    echo '<div class="example-image" style="margin-top:2rem;"><img class="std-border-radius" src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt'] ?? '') . '" style="max-width:100%; height:auto;" /></div>';
                }
                if ($sub_text) {
                    echo '<p class="text-small text-midgrey" style="margin-top: 1rem;"><em>' . esc_html($sub_text) . '</em></p>';
                }
                echo '</div>';
            }

            break;
        }
    }
}

