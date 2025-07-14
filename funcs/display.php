<?php 

add_filter('pmpro_no_access_message_html', function($html, $level_ids) {
    $bg = get_field('no_access_background_image', 'option');
    $bg_url = $bg ? esc_url($bg['url']) : '';

    if (!is_user_logged_in()) {
        $html_out = render_cta_block($bg_url, 'encourage_sign_up_heading_map', 'encourage_sign_up_body');
        $html_out .= render_example_profile();
        $html_out .= do_shortcode('[map_signup_cta_box]');
        return $html_out;
    }

    $user_id    = get_current_user_id();
    $user_level = pmpro_getMembershipLevelForUser($user_id);
    $order      = (function() use ($user_id) {
        $o = new MemberOrder();
        $o->getLastMemberOrder($user_id);
        return $o;
    })();

    if ($user_level && (int)$user_level->id >= 2) {
        return $html;
    }

 echo $order->status. " = order status";

if ($order && $order->status === 'pending' && (int)$order->membership_id >= 2) {

        $html_out  = render_notice_block(
            get_field('waiting_for_verification_heading', 'option'),
            get_field('waiting_for_verification_body', 'option')
        );
        $html_out .= render_example_profile();
        $html_out .= do_shortcode('[map_signup_cta_box]');
        return $html_out;
    }

    $html_out = render_cta_block($bg_url, 'encourage_sign_up_heading_map', 'encourage_signup_body_map_');
    $html_out .= render_example_profile();
    $html_out .= do_shortcode('[map_signup_cta_box]');
     $html_out .= "sdfdsfsd";
    return $html_out;

}, 10, 2);


// 👉 Helpers

function render_cta_block($bg_url, $heading_field, $body_field) {
    $h = get_field($heading_field, 'option') ?: '';
    $b = get_field("encourage_signup_body_map_", 'option');

    return sprintf(
        '<div class="card" style="position:relative; background-image:url(%1$s); background-size:cover; background-position:center; padding:64px; margin:2rem auto; text-align:center;">
            <div style="background:rgba(255,255,255,0.95); padding:32px; max-width:600px; margin:auto;">
                <h2 style="color:#673f69;">'.$h.'</h2>
                <p style="color:#333;">'.$b.'</p>
                <a href="/join-we-are-geniuses/" class="btn">Unlock The Map</a>
                <p style="color:#666; margin-top:16px;">Already level 2+? <a href="/login/">Login</a></p>
            </div>
        </div>',
        esc_url($bg_url), esc_html($h), esc_html($b)
    );
}


function render_notice_block($heading, $body) {
    return sprintf(
        '<div class="card" style="max-width:600px; margin:2rem auto; padding:2rem; background:#fffbe6; border:1px solid #ffd700; border-radius:8px; text-align:center;">
            <h2 style="color:#b48b00;">%1$s</h2>
            <p>%2$s</p>
        </div>',
        esc_html($heading ?: 'Your membership is pending approval'),
        esc_html($body ?: 'Please allow time for manual verification. You’ll gain access once approved.')
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


