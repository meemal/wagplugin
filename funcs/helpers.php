<?php

function ftd_get_directory_listing_status_tag($status) {
    $status_class = 'status-tag';
    $status_label = 'Unknown';

    switch ($status) {
        case 'publish':
            $status_class .= ' status-live';
            $status_label = 'Live';
            break;
        case 'pending':
            $status_class .= ' status-pending';
            $status_label = 'Pending Review';
            break;
        case 'disabled':
            $status_class .= ' status-disabled';
            $status_label = 'Disabled';
            break;
    }

    return '<span class="' . esc_attr($status_class) . '">' . esc_html($status_label) . '</span>';
}
