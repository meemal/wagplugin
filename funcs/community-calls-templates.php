<?php
/**
 * Template loader and assets for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'template_include', 'ftd_community_calls_template_include', 99 );
add_filter( 'body_class', 'ftd_community_calls_body_class' );
add_action( 'template_redirect', 'ftd_maybe_serve_community_call_ics' );

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

/**
 * Body classes for community call templates.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ftd_community_calls_body_class( $classes ) {
	if ( is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		$classes[] = 'gcc-single-session';
	}

	return $classes;
}

/**
 * Serve .ics download for a single community call.
 */
function ftd_maybe_serve_community_call_ics() {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return;
	}

	if ( empty( $_GET['ftd_gcc_ics'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$content = ftd_get_community_call_ics_content( get_the_ID() );

	if ( '' === $content ) {
		status_header( 404 );
		exit;
	}

	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="community-call.ics"' );
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

add_action( 'wp_enqueue_scripts', 'ftd_enqueue_community_calls_styles', 25 );
add_action( 'wp_enqueue_scripts', 'ftd_register_live_session_share_assets', 15 );
add_action( 'wp_head', 'ftd_output_community_call_social_meta', 5 );
add_filter( 'wpseo_opengraph_image', 'ftd_filter_community_call_share_image' );
add_filter( 'wpseo_twitter_image', 'ftd_filter_community_call_share_image' );
add_filter( 'wpseo_opengraph_image_id', 'ftd_filter_community_call_share_image_id' );
add_filter( 'rank_math/opengraph/facebook/og_image', 'ftd_filter_community_call_share_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'ftd_filter_community_call_share_image' );

/**
 * Prefer the session promo image for SEO plugin share tags.
 *
 * @param string $image Existing image URL.
 * @return string
 */
function ftd_filter_community_call_share_image( $image ) {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return $image;
	}

	$og = ftd_get_community_call_og_image_data( get_the_ID() );

	return ! empty( $og['url'] ) ? $og['url'] : $image;
}

/**
 * Prefer the session promo attachment for SEO plugin share tags.
 *
 * @param int|string $image_id Existing attachment ID.
 * @return int|string
 */
function ftd_filter_community_call_share_image_id( $image_id ) {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return $image_id;
	}

	$attachment_id = ftd_get_community_call_share_image_id( get_the_ID() );

	return $attachment_id > 0 ? $attachment_id : $image_id;
}

/**
 * Open Graph / Twitter meta for live session pages without an SEO plugin.
 */
function ftd_output_community_call_social_meta() {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return;
	}

	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	$post_id = get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = wp_strip_all_tags( get_the_title( $post_id ) );
	$content = ftd_get_live_session_share_content( $post_id );
	$desc    = $content['text'];

	if ( '' === $desc ) {
		$desc = $title;
	}

	$image = ftd_get_community_call_og_image_data( $post_id );

	echo '<meta property="og:type" content="article" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";

	if ( ! empty( $image['url'] ) ) {
		echo '<meta property="og:image" content="' . esc_url( $image['url'] ) . '" />' . "\n";
		echo '<meta property="og:image:secure_url" content="' . esc_url( $image['url'] ) . '" />' . "\n";

		if ( ! empty( $image['width'] ) && ! empty( $image['height'] ) ) {
			echo '<meta property="og:image:width" content="' . esc_attr( (string) $image['width'] ) . '" />' . "\n";
			echo '<meta property="og:image:height" content="' . esc_attr( (string) $image['height'] ) . '" />' . "\n";
		}

		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:image" content="' . esc_url( $image['url'] ) . '" />' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary" />' . "\n";
	}

	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
}

/**
 * Register live session share script.
 */
function ftd_register_live_session_share_assets() {
	wp_register_script(
		'ftd-live-session-share',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/live-session-share.js',
		array(),
		ftd_get_plugin_asset_version(),
		true
	);

	if ( function_exists( 'ftd_register_social_share_assets' ) ) {
		ftd_register_social_share_assets();
	} else {
		wp_register_style(
			'ftd-sc-social-share',
			plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/social-share.css',
			array( 'directory-listings-style' ),
			ftd_get_plugin_asset_version()
		);
	}

	wp_register_style(
		'ftd-sc-founding-genius',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/founding-genius-banner.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
	);

	wp_register_style(
		'ftd-sc-wag-features-ctas',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/wag-features-ctas.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
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
		ftd_get_plugin_asset_version()
	);

	if ( $is_calls_single || $is_calls_archive ) {
		wp_enqueue_style( 'ftd-sc-founding-genius' );
		wp_enqueue_style( 'ftd-sc-wag-features-ctas' );
	}

	if ( $is_calls_single ) {
		wp_enqueue_style( 'ftd-sc-social-share' );
		wp_enqueue_style( 'ftd-sc-gnls-join-cta' );
		wp_enqueue_script( 'ftd-live-session-share' );
	}

	if ( $is_calls_archive ) {
		wp_enqueue_style( 'ftd-sc-gnls-session-cta' );
		wp_enqueue_style( 'ftd-sc-gnls-join-cta' );
		wp_enqueue_style( 'ftd-sc-social-share' );
		wp_enqueue_script( 'ftd-live-session-share' );
	}
}
