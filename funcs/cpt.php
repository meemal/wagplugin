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



  // Helper function to DRY
  function register_directory_taxonomy($taxonomy, $singular, $plural) {
      register_taxonomy($taxonomy, 'directory_listing', [
          'labels' => [
              'name' => $plural,
              'singular_name' => $singular,
              'search_items' => 'Search ' . $plural,
              'all_items' => 'All ' . $plural,
              'edit_item' => 'Edit ' . $singular,
              'update_item' => 'Update ' . $singular,
              'add_new_item' => 'Add New ' . $singular,
              'new_item_name' => 'New ' . $singular . ' Name',
              'menu_name' => $plural,
          ],
          'hierarchical' => false,
          'public' => true,
          'show_ui' => true,
          'show_in_rest' => true,
          'rewrite' => [ 'slug' => $taxonomy ],
      ]);
  }

  register_directory_taxonomy('attracting', 'Attracting', 'Attracting');
  register_directory_taxonomy('services', 'Service', 'Services');
 
});




