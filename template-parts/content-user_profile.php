<?php
// Ensure $user is available in scope
if (!isset($user)) {
    return;
}

// Get user data
$display_name = $user->display_name;
$avatar = get_avatar($user->ID, 260, '', $display_name);
$your_story = get_user_meta($user->ID, 'your_story', true);
$events_attended = get_user_meta($user->ID, 'events_attended', true);
$favourite_quote = get_user_meta($user->ID, 'favourite_dr_joe_quote', true);
$favourite_meditation = get_user_meta($user->ID, 'favourite_meditation', true);
$year_work_began = get_user_meta($user->ID, 'year_work_began', true);
$user_email = $user->user_email;
?>

<div class="directory-listing-card card">
    <div class="wp-block-columns">

        <div class="wp-block-column">
            <div class="profile-pic-container"><?php echo $avatar; ?></div>
        </div>
        <div class="wp-block-column is-vertically-aligned-center">
            <h1 class="listing-title"><?= esc_html($display_name); ?></h1>
            <?php if ($year_work_began): ?>
            <h3>Doing the work since <?= esc_html($year_work_began); ?></h3>
            <?php endif; ?>
        </div>
    </div>
    <div class="wp-block-columns directory-content">

        <div class="description description-about">
            <?php if ($your_story): ?>
                <h3>Story</h3>
                <p><?php echo nl2br(esc_html($your_story)); ?></p>
            <?php endif; ?>
       
        </div>
       
    </div>
    <div class="wp-block-columns">
        <div class="description">
            <?php if ($favourite_quote): ?>
                <h3>Favourite Dr Joe Quote</h3>
                <blockquote class='user-card-quote'><?php echo esc_html($favourite_quote); ?></blockquote>
            <?php endif; ?>
        </div>
    </div>
        <div class="wp-block-columns">
        <div class="description">
            <h3>Email</h3>
            <?php if ($user_email): ?>
                <a href="mailto:<?php echo esc_html($user_email); ?>"><?php echo esc_html($user_email); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="wp-block-columns">
        <div class="wp-block-column">
            <div class="description description-events">
                <?php if ($events_attended): ?>
                    <h3>Events Attended</h3>
                    <p><?php echo nl2br(esc_html($events_attended)); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="wp-block-column">
            <div class="description">
       
            <?php if ($favourite_meditation): ?>

                <h3>Favourite Meditation</h3>
                <p><?php echo esc_html($favourite_meditation); ?></p>
            <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="edit-listing-link">
      <a class="alignright" href="/membership-account/edit-your-profile/">Edit Your Profile</a>
   
      
    </div>
</div>


