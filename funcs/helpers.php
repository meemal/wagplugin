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


function ftd_get_directory_usage_message( $used, $allowed ) {
    $remaining = max( 0, $allowed - $used );

    if ( $remaining > 0 ) {
        return sprintf(
            'You have created <strong>%d</strong> of an available <strong>%d</strong> listings. You have <strong>%d</strong> listing%s remaining.',
            $used,
            $allowed,
            $remaining,
            $remaining === 1 ? '' : 's'
        );
    }

    return sprintf(
        'You have used <strong>%d</strong> of your available listings. You have <strong>no</strong> listings remaining.',
        $allowed
    );
}
