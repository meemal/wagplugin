<?php
/**
 * Shortcode stylesheet registry and conditional enqueue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode tag => CSS path relative to plugin root.
 *
 * @return array<string, string>
 */
function ftd_get_shortcode_styles() {
	return array(
		'founding_genius_banner'             => 'css/founding-genius-banner.css',
		'genius_levels_cta'                  => 'css/genius-cta.css',
		'WAG_Features_CTAS'                  => 'css/wag-features-ctas.css',
		'genius_calls'                       => 'css/wag-features-ctas.css',
		'wag_stats_ticker'                   => 'css/founding-spots-banner.css',
		'ftd_stats_ticker'                   => 'css/founding-spots-banner.css',
		'wag_social_share'                   => 'css/social-share.css',
		'ftd_social_share'                   => 'css/social-share.css',
		'wag_favourite_quotes'               => 'css/favourite-quotes.css',
		'ftd_favourite_quotes'               => 'css/favourite-quotes.css',
		'custom_member_profile'              => 'css/user-profile.css',
		'my_directory_listings_account_page' => 'css/directory-toggle.css',
	);
}

/**
 * Enqueue component CSS when a registered shortcode appears in post content.
 */
function ftd_enqueue_shortcode_styles() {
	global $post;

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$base_handle    = 'directory-listings-style';
	$plugin_url     = plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE );
	$enqueued_files = array();

	foreach ( ftd_get_shortcode_styles() as $tag => $relative_path ) {
		if ( ! has_shortcode( $post->post_content, $tag ) ) {
			continue;
		}

		if ( in_array( $relative_path, $enqueued_files, true ) ) {
			continue;
		}
		$enqueued_files[] = $relative_path;

		$handle = 'ftd-sc-' . sanitize_title( basename( $relative_path, '.css' ) );
		wp_enqueue_style(
			$handle,
			$plugin_url . $relative_path,
			array( $base_handle ),
			FTD_DIRECTORY_LISTINGS_VERSION
		);
	}
}

/**
 * Enqueue single-listing layout CSS (replaces theme footer link tag).
 */
function ftd_enqueue_directory_single_styles() {
	if ( ! is_singular( 'directory_listing' ) ) {
		return;
	}

	wp_enqueue_style(
		'ftd-directory-single',
		get_stylesheet_directory_uri() . '/css/directory-style.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);
}
