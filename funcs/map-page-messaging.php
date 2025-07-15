<?php 

add_filter('pmpro_no_access_message_html', function($html, $level_ids) {
   
    $bg = get_field('no_access_background_image', 'option');
    $bg_url = $bg ? esc_url($bg['url']) : '';
    $user_id = get_current_user_id();
    // Handle based on context
    switch (true) {
        case is_post_type_archive('directory_listing'):
            return  "IS DIRECTORY PAGE";

        case is_page('genius-map'):
        default:
            // Not logged in
            if (!is_user_logged_in()) {
                  echo ftd_alert_box([
    'heading' => 'Access Restricted',
    'body' => 'You must be a member to view this page. Already a member? <a href="/login/">Login</a>.',
    'type' => 'danger'
    ]);
                $html_out = render_map_cta_block($bg_url, 'encourage_sign_up_heading_map', 'encourage_signup_body_map_');
                $html_out .= render_example_profile();
                $html_out .= do_shortcode('[map_signup_cta_box]');
                return $html_out;
            }

            if (ftd_user_is_pending_approval($user_id)) {
                   echo ftd_alert_box([
      'heading' => 'Access Pending Approval',
      'body' => 'We’re reviewing your membership application. You’ll receive an email as soon as you’re approved.',
      'type' => 'info'
      ]);
                $html_out = render_map_cta_block($bg_url, 'wait_for_approval_map_heading', 'wait_for_approval_map_body', false);
                $html_out .= render_example_profile();
                $html_out .= do_shortcode('[map_signup_cta_box]');
                return $html_out;
            }

            $membership = pmpro_getMembershipLevelForUser($user_id);
            if ($membership && (int) $membership->id >= 2) {
                return $html;
            }

            $html_out = render_map_cta_block($bg_url, 'encourage_sign_up_heading_map', 'encourage_signup_body_map_');
            $html_out .= render_example_profile();
            $html_out .= do_shortcode('[map_signup_cta_box]');
            return $html_out;
    }
}, 10, 2);



// 👉 Helpers

function render_map_cta_block($bg_url, $heading_field, $body_field, $show_button = true) {
    $h = get_field($heading_field, 'option') ?: '';
    $b = get_field($body_field, 'option') ?: '';

    $button_html = $show_button
        ? '<a href="/join-we-are-geniuses/" class="btn">Unlock The Map</a>
           <p class="text-midgrey" style="margin-top:16px;">Already level 2+? <a href="/login/">Login</a></p>'
        : '';

    return sprintf(
        '<div class="card" style="position:relative; background-image:url(%1$s); background-size:cover; background-position:center; padding:64px; margin:2rem auto; text-align:center;">
            <div class="card" style="max-width:600px; margin:auto;">
                <h2 class="text-purple">%2$s</h2>
                <p class="text-midgrey">%3$s</p>
                %4$s
            </div>
        </div>',
        esc_url($bg_url),
        esc_html($h),
        wp_kses_post($b),
        $button_html
    );
}

function render_notice_block($heading, $body) {
    return sprintf(
        '<div class="card" style="max-width:600px; margin:2rem auto; padding:2rem; background:#fffbe6; border:1px solid #ffd700; border-radius:8px; text-align:center;">
            <h2 style="color:#b48b00;">%1$s</h2>
            <p>%2$s</p>
        </div>',
        esc_html($heading ?: 'Your membership is pending approval'),
        wp_kses_post($body ?: 'Please allow time for manual verification. You’ll gain access once approved.')
    );
}

function render_example_profile() {
    $heading = get_field('example_profile_heading', 'option') ?: '';
    $body    = get_field('example_profile_body', 'option') ?: '';
    $img     = get_field('example_profile_image', 'option');

    if (!$img || empty($img['url'])) {
        return '';
    }

    return sprintf(
        '<div class="card" style="margin:2rem auto; text-align:center;">
            %s
            %s
            <img class="std-border-radius" src="%s" alt="%s" style="max-width:100%%; height:auto; border-radius:8px;" />
        </div>',
        $heading ? '<h3 style="margin-bottom:0.5rem;margin-top:1rem;">' . esc_html($heading) . '</h3>' : '',
        $body ? '<div style="margin-bottom:2rem;max-width:600px;margin-left:auto;margin-right:auto;">' . wp_kses_post($body) . '</div>' : '',
        esc_url($img['url']),
        esc_attr($img['alt'] ?? 'Example Profile')
    );
}
