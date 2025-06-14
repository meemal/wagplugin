<?php
/**
 * Template part: User Profile Card
 * Usage context: Used inside the [custom_member_profile] shortcode to render each listing.
 * Assumes $postid is passed from the shortcode context.
 */

 $postid = get_the_ID(); // Ensure $postid is set to the current post ID
if (!$postid) {
    return; // Exit if no post ID is available
}
if (!is_user_logged_in()) {
    return; // Exit if the user is not logged in
}

// Get post data
$profile = get_field('profile_picture');
$headline = get_field('headline');
$entry_id = get_field('associated_ff_post_id');
$status = get_post_status();

$delete_url = add_query_arg([
    'delete_listing' => $postid,
    '_wpnonce'       => wp_create_nonce('delete_listing_' . $postid),
]);
?>

<div class="directory-card-mini card">
    <div class="directory-card-content has-text-align-center">
    <?php if ($profile) : ?>
            <img src="<?php echo esc_url($profile['url']); ?>" alt="Profile Picture" class="profile-pic profile-pic-mini">
        <?php endif; ?>
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>

        <div class="directory-card-buttons">
            <a href="<?php the_permalink(); ?>" class="btn btn-small ftd-view-button" data-post-id="<?= $the_id ?>">View Listing</a>
            <?php if ($entry_id) : ?>
                <a href="/your-directory-listing/?frm_action=edit&entry=<?php echo esc_attr($entry_id); ?>" class="btn btn-small edit-btn">Edit Listing</a>
            <?php endif; ?>
        </div>
    </div>
</div>