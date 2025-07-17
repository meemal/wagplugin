<?php

function ftd_render_simple_card($heading = '', $body = '', $button = '', $subtext = '') {
    echo '<div class="card  has-text-align-center">';

    if (!empty($heading)) {
        echo '<h3 class="text-purple">' . esc_html($heading) . '</h3>';
    }

    if (!empty($body)) {
        echo '<p class="text-midgrey" style="margin-bottom: 1rem;">' . wp_kses_post($body) . '</p>';
    }

    if (!empty($button)) {
        echo $button;
    }

    if (!empty($subtext)) {
        echo '<p class="text-small text-midgrey" style="margin-top: 1rem;"><em>' . esc_html($subtext) . '</em></p>';
    }

    echo '</div>';
}
