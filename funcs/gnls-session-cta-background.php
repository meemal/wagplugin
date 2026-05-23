<?php
/**
 * Session promo CTA background fallback (featured image is the primary background).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GNLS_YOUTUBE_WIDTH', 1280 );
define( 'FTD_GNLS_YOUTUBE_HEIGHT', 720 );
define( 'FTD_GNLS_CAPTURE_RENDER_WIDTH', 1280 );
define( 'FTD_GNLS_CAPTURE_RENDER_HEIGHT', 720 );
define( 'FTD_GNLS_SOCIAL_WIDTH', 1200 );
define( 'FTD_GNLS_SOCIAL_HEIGHT', 630 );

add_action( 'after_setup_theme', 'ftd_register_gnls_youtube_image_size', 20 );

/**
 * Register generated promo image sizes.
 */
function ftd_register_gnls_youtube_image_size() {
	add_image_size( 'gnls-youtube-thumb', FTD_GNLS_YOUTUBE_WIDTH, FTD_GNLS_YOUTUBE_HEIGHT, true );
	add_image_size( 'gnls-social-og', FTD_GNLS_SOCIAL_WIDTH, FTD_GNLS_SOCIAL_HEIGHT, true );
}

/**
 * Fallback background colour when no featured image is set.
 *
 * @param int $post_id Post ID.
 * @return array{color: string, image: string}
 */
function ftd_get_session_cta_background_config( $post_id = 0 ) {
	unset( $post_id );

	return array(
		'color' => '#673f69',
		'image' => '',
	);
}

/**
 * Attachment ID for a generated YouTube thumbnail.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ftd_get_community_call_youtube_thumbnail_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return $post_id > 0 ? (int) get_post_meta( $post_id, 'youtube_thumbnail', true ) : 0;
}

/**
 * Attachment ID for a generated social share image.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ftd_get_community_call_social_share_thumbnail_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return $post_id > 0 ? (int) get_post_meta( $post_id, 'social_share_thumbnail', true ) : 0;
}
