<?php
/**
 * Template loader and assets for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'template_include', 'ftd_community_calls_template_include', 99 );

/**
 * Load plugin templates for community calls.
 *
 * @param string $template Current template path.
 * @return string
 */
function ftd_community_calls_template_include( $template ) {
	if ( is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		$plugin_template = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'templates/single-genius_comm_call.php';

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	if ( is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		$plugin_template = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'templates/archive-genius_comm_call.php';

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}

add_action( 'wp_enqueue_scripts', 'ftd_enqueue_community_calls_styles', 25 );
add_action( 'wp_enqueue_scripts', 'ftd_register_live_session_share_assets', 15 );

/**
 * Register live session share script.
 */
function ftd_register_live_session_share_assets() {
	wp_register_script(
		'ftd-live-session-share',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/live-session-share.js',
		array(),
		FTD_DIRECTORY_LISTINGS_VERSION,
		true
	);

	if ( function_exists( 'ftd_register_social_share_assets' ) ) {
		ftd_register_social_share_assets();
	} else {
		wp_register_style(
			'ftd-sc-social-share',
			plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/social-share.css',
			array( 'directory-listings-style' ),
			FTD_DIRECTORY_LISTINGS_VERSION
		);
	}

	wp_register_style(
		'ftd-sc-founding-genius',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/founding-genius-banner.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	wp_register_style(
		'ftd-sc-wag-features-ctas',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/wag-features-ctas.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	if ( function_exists( 'ftd_register_gnls_join_cta_assets' ) ) {
		ftd_register_gnls_join_cta_assets();
	}
}

/**
 * Enqueue community calls CSS.
 */
function ftd_enqueue_community_calls_styles() {
	$is_calls_archive = is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE );
	$is_calls_single  = is_singular( FTD_COMMUNITY_CALL_POST_TYPE );

	if ( ! $is_calls_single && ! $is_calls_archive ) {
		return;
	}

	wp_enqueue_style(
		'ftd-community-calls',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/community-calls.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	if ( $is_calls_single || $is_calls_archive ) {
		wp_enqueue_style( 'ftd-sc-founding-genius' );
		wp_enqueue_style( 'ftd-sc-wag-features-ctas' );
	}

	if ( $is_calls_archive ) {
		wp_enqueue_style( 'ftd-sc-gnls-session-cta' );
		wp_enqueue_style( 'ftd-sc-gnls-join-cta' );
		wp_enqueue_style( 'ftd-sc-social-share' );
		wp_enqueue_script( 'ftd-live-session-share' );
	}
}
