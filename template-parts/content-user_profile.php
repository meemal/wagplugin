<?php
// Ensure $user is available in scope
if (!isset($user)) {
    return;
}

// Get user data
$display_name = $user->display_name;
$avatar = get_avatar($user->ID, 260, '', $display_name, ['class' => 'directory-listing-profile-pic']);
$your_story = get_user_meta($user->ID, 'your_story', true);
$events_attended = get_user_meta($user->ID, 'events_attended', true);
$favourite_quote = get_user_meta($user->ID, 'favourite_dr_joe_quote', true);

?>

<div class="directory-listing-card card">
    <div class="listing-header">
        <div class="profile-pic-container"><?php echo $avatar; ?></div>
        <h1 class="listing-title"><?php echo esc_html($display_name); ?></h1>
   
    </div>

    <div class="wp-block-columns directory-content">
        <div class="wp-block-column">
            <div class="description description-about">
                <?php if ($your_story): ?>
                    <h3>Your Story</h3>
                    <p><?php echo nl2br(esc_html($your_story)); ?></p>
                <?php endif; ?>

     
            </div>
        </div>

        <div class="wp-block-column">
            <div class="description description-attracting">
            <?php if ($events_attended): ?>
                    <h3>Events Attended</h3>
                    <p><?php echo nl2br(esc_html($events_attended)); ?></p>
                <?php endif; ?>

                <?php if ($favourite_quote): ?>
                    <h3>Favourite Dr Joe Quote</h3>
                    <blockquote><?php echo esc_html($favourite_quote); ?></blockquote>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
