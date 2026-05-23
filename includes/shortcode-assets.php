<?php
/**
 * Shortcode stylesheet registry and conditional enqueue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache-busting version for enqueued CSS/JS.
 *
 * Uses the newest modification time across plugin css/ and js/ files so copied
 * assets get a new ?ver= on deploy without manually bumping the plugin version.
 *
 * @return string
 */
function ftd_get_plugin_asset_version() {
	static $version = null;

	if ( null !== $version ) {
		return $version;
	}

	$base   = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE );
	$latest = (int) @filemtime( FTD_DIRECTORY_LISTINGS_FILE );

	foreach ( array( 'css', 'js' ) as $dir ) {
		$files = glob( $base . $dir . '/*.{css,js}', GLOB_BRACE );

		if ( ! is_array( $files ) ) {
			continue;
		}

		foreach ( $files as $file ) {
			$latest = max( $latest, (int) @filemtime( $file ) );
		}
	}

	$version = $latest > 0 ? (string) $latest : FTD_DIRECTORY_LISTINGS_VERSION;

	return $version;
}

/**
 * Shortcode tag => CSS path relative to plugin root.
 *
 * Also register new styles in ftd_enqueue_shortcode_showcase_assets() (shortcode-showcase.php)
 * and add the shortcode to ftd_get_shortcode_showcase_groups().
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
		'gnls_session_cta'                   => 'css/gnls-session-cta.css',
		'live_session_cta'                   => 'css/gnls-session-cta.css',
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
			ftd_get_plugin_asset_version()
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

	$theme_css = get_stylesheet_directory() . '/css/directory-style.css';
	$version   = file_exists( $theme_css ) ? (string) filemtime( $theme_css ) : ftd_get_plugin_asset_version();

	wp_enqueue_style(
		'ftd-directory-single',
		get_stylesheet_directory_uri() . '/css/directory-style.css',
		array( 'directory-listings-style' ),
		$version
	);
}
