<?php
function ftd_membership_account_notice_shortcode() {
    if (!is_user_logged_in()) return '';

    $user_id = get_current_user_id();

    if (function_exists('ftd_user_is_pending_approval') && ftd_user_is_pending_approval($user_id)) {
        return ftd_alert_box([
            'heading' => 'Membership Pending Approval',
            'body'    => 'We’re reviewing your membership application. You’ll receive an email as soon as you’re approved.',
            'type'    => 'info'
        ]);
    }

    return '';
}
add_shortcode('ftd_membership_pending_notice', 'ftd_membership_account_notice_shortcode');

