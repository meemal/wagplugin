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


