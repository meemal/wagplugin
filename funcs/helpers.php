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

function ftd_user_is_pending_approval( $user_id = null ) {
    if ( empty( $user_id ) ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        $user_id = get_current_user_id();
    }

    $levels = pmpro_getMembershipLevelsForUser( $user_id );
    if ( empty( $levels ) ) {
        return false;
    }

    foreach ( $levels as $level ) {
        $status = PMPro_Approvals::getUserApprovalStatus( $user_id, $level->id );
        if ( strtolower( $status ) === 'pending' ) {
            return true;
        }
    }
    return false;
}
