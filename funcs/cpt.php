<?php 


// 1️⃣ Register Custom Post Type: Directory Listing
add_action('init', function() {
  register_post_type('directory_listing', [
      'labels' => [
          'name' => 'Directory Listings',
          'singular_name' => 'Directory Listing',
          'add_new' => 'Add New Listing',
          'add_new_item' => 'Add New Directory Listing',
          'edit_item' => 'Edit Directory Listing',
          'new_item' => 'New Directory Listing',
          'view_item' => 'View Directory Listing',
          'search_items' => 'Search Listings',
          'not_found' => 'No Listings Found',
          
      ],
      'public' => true,
      'has_archive' => true,
      'rewrite' => [ 'slug' => 'genius-directory' ],
      'supports' => [ 'title', 'editor', 'thumbnail', 'author' ],
      'taxonomies' => [ 'category', 'post_tag' ],
      'show_in_rest' => true,
      'menu_icon' => 'dashicons-businessman',
  ]);
});
