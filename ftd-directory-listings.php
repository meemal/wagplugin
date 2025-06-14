<?php
/**
 * Plugin Name: Directory Listings
 * Description: A custom plugin to handle Directory Listings (CPT, forms, etc.)
 * Version: 1.0
 * Author: Naomi Spirit
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Include all files in the funcs folder
$funcs_dir = plugin_dir_path( __FILE__ ) . 'funcs/';
if ( is_dir( $funcs_dir ) ) {
    foreach ( glob( $funcs_dir . '*.php' ) as $file ) {
        require_once $file;
    }
}

add_action('wp_enqueue_scripts', function() {
    global $post;

    // Always enqueue core directory styles
    wp_enqueue_style(
        'directory-listings-style',
        plugin_dir_url(__FILE__) . 'css/directory-listings.css'
    );

    // Only enqueue toggle CSS if the shortcode is used on the page
    if ( is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'my_directory_listings_account_page') ) {
        wp_enqueue_style(
            'directory-toggle-style',
            plugin_dir_url(__FILE__) . 'css/directory-toggle.css'
        );
    }
        // Only enqueue toggle CSS if the shortcode is used on the page
        if ( is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'custom_member_profile') ) {
            wp_enqueue_style(
                'user-profile-style',
                plugin_dir_url(__FILE__) . 'css/user-profile.css'
            );
        }
    // Only enqueue toggle CSS if the shortcode is used on the page
    if ( is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'genius_levels_cta') ) {
        wp_enqueue_style(
            'genius-cta-style',
            plugin_dir_url(__FILE__) . 'css/genius-cta.css'
        );
    }
    // Enqueue genius-map CSS if we are on the /genius-map/ page
    if ( is_page('genius-map') ) {
        wp_enqueue_style(
            'genius-map-style',
            plugin_dir_url(__FILE__) . 'css/genius-map.css'
        );
    }
   
});

add_action('wp', function () {
    global $post;
    if ($post instanceof WP_Post) {
        if (
            has_shortcode($post->post_content, 'user_directory_listings') ||
            has_shortcode($post->post_content, 'custom_member_profile') ||
            is_post_type_archive('directory_listing') // For archive template part
        ) {
            add_action('wp_enqueue_scripts', 'ftd_enqueue_view_counter_script');
        }
    }
});


function ftd_enqueue_view_counter_script() {
    wp_enqueue_script(
        'ftd-view-counter',
        plugin_dir_url(__FILE__) . 'js/view-counter.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('ftd-view-counter', 'ftdViewCounter', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ftd_view_nonce'),
    ]);
}


// Flush rewrite rules on activation/deactivation
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});


require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'directory-ajax-filter',
        plugin_dir_url(__FILE__) . 'js/directory-ajax-filter.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('directory-ajax-filter', 'directory_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('directory_ajax_nonce')
    ]);

    if (is_page('membership-account')) {  // OR use is_page(123)
        wp_enqueue_script(
            'map-settings-ajax',
            plugin_dir_url(__FILE__) .  '/js/map-settings.js',
            ['jquery'],
            null,
            true
          );
          wp_localize_script('map-settings-ajax', 'directory_ajax_obj', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('directory_ajax_nonce'),
          ]);
    }


});



  


function ftd_get_plugin_template( $template_name, $args = array() ) {
    $template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name . '.php';
    if ( file_exists( $template_path ) ) {
        extract( $args );
        include $template_path;
    }
}




