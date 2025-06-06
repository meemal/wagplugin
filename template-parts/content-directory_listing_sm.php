<?php
// Use $listing_id directly, no $args
if ( ! isset( $args['listing_id'] ) ) {
    return; // No listing ID passed
}

$listing_id = $args['listing_id'];

$title = get_the_title( $listing_id );
$edit_link = get_edit_post_link( $listing_id );
$view_link = get_permalink( $listing_id );
$author_id = get_post_field( 'post_author', $listing_id );
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_avatar = get_avatar( $author_id, 48 );
$current_user_id = get_current_user_id();

// Generate delete URL with nonce
$delete_url = add_query_arg( array(
    'delete_listing' => $listing_id,
    '_wpnonce'       => wp_create_nonce( 'delete_listing_' . $listing_id ),
) );
?>

<div class="directory-listing-card">
    <div class="listing-header">
        <div class="author-profile">
            <?php echo $author_avatar; ?>
            <span class="author-name"><?php echo esc_html( $author_name ); ?></span>
        </div>
        <h3 class="listing-title"><?php echo esc_html( $title ); ?></h3>
    </div>
    <div class="listing-actions">
        <?php if ($current_user_id === (int) $author_id): ?>
            <a href="<?php echo esc_url( $edit_link ); ?>" class="button">Edit</a>
        
        
        <a href="<?php echo esc_url( $view_link ); ?>" class="button">View</a>
        <?php if ($current_user_id === (int) $author_id): ?>
       
            <a href="<?php echo esc_url( $delete_url ); ?>" class="button delete-listing" onclick="return confirm('Are you sure you want to delete this listing? This action cannot be undone.');">Delete</a>
            <?php endif; ?>
    </div>
</div>



