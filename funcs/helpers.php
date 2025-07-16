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
function ftd_alert_box($args = [], $context = '') {
    $defaults = [
        'heading'     => '',
        'body'        => '',
        'type'        => '', // Will be filled from ACF if context provided
        'icon'        => '',
        'dismissible' => true
    ];

    // Load ACF values if context is set
    if (!empty($context)) {
        $acf_heading = get_field("alert_heading_{$context}", 'option') ?? '';
        $acf_body    = get_field("alert_body_{$context}", 'option') ?? '';
        $acf_type    = get_field("alert_type_{$context}", 'option') ?? '';

        if (empty($args['heading'])) $args['heading'] = $acf_heading;
        if (empty($args['body']))    $args['body']    = $acf_body;
        if (empty($args['type']))    $args['type']    = $acf_type;
    }

    $args = wp_parse_args($args, $defaults);

    // Ensure string values to avoid null deprecation errors
    $heading = (string) ($args['heading'] ?? '');
    $body    = (string) ($args['body'] ?? '');
    $type    = (string) ($args['type'] ?? '');

    if ($heading === '' && $body === '') return;

    $types = [
        'info'    => ['#e7f3fe', '#31708f', 'ℹ️'],
        'success' => ['#dff0d8', '#3c763d', '✅'],
        'warning' => ['#fcf8e3', '#8a6d3b', '⚠️'],
        'danger'  => ['#f2dede', '#a94442', '⛔'],
    ];

    [$bg, $color, $default_icon] = $types[$type] ?? $types['info'];
    $icon = $args['icon'] ?: $default_icon;

    ob_start(); ?>
    <div class="ftd-alert-box" style="background:<?= esc_attr($bg); ?>; color:<?= esc_attr($color); ?>; border-left: 5px solid <?= esc_attr($color); ?>; padding:1rem 1.5rem; margin:1rem 0; border-radius:4px; position:relative; max-width:600px; margin-left:auto; margin-right:auto; margin-bottom:64px;">
        <?php if (!empty($args['dismissible'])) : ?>
            <button onclick="this.parentElement.style.display='none';" style="position:absolute; top:8px; right:12px; background:none; border:none; font-size:18px; color:<?= esc_attr($color); ?>;">×</button>
        <?php endif; ?>
        <div style="display:flex; align-items:flex-start; gap:1rem;">
            <div style="font-size:24px; line-height:1;"><?= esc_html($icon); ?></div>
            <div>
                <?php if (!empty($heading)) : ?>
                    <strong style="display:block; margin-bottom:0.25rem;"><?= esc_html($heading); ?></strong>
                <?php endif; ?>
                <div><?= wp_kses_post($body); ?></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
