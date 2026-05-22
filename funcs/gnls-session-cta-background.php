<?php
/**
 * Session promo CTA background — same presets as the page header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GNLS_YOUTUBE_WIDTH', 1280 );
define( 'FTD_GNLS_YOUTUBE_HEIGHT', 720 );

add_action( 'after_setup_theme', 'ftd_register_gnls_youtube_image_size', 20 );

/**
 * Register YouTube thumbnail image size (1280×720).
 */
function ftd_register_gnls_youtube_image_size() {
	add_image_size( 'gnls-youtube-thumb', FTD_GNLS_YOUTUBE_WIDTH, FTD_GNLS_YOUTUBE_HEIGHT, true );
}

/**
 * Resolve session CTA background for a live session post.
 *
 * @param int $post_id Post ID.
 * @return array{color: string, image: string}
 */
function ftd_get_session_cta_background_config( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$color   = '#673f69';
	$image   = '';

	if ( function_exists( 'get_field' ) && $post_id ) {
		$field_color = get_field( 'cta_background_color', $post_id );
		if ( is_string( $field_color ) && '' !== trim( $field_color ) ) {
			$color = trim( $field_color );
		}

		$upload = get_field( 'cta_background_image', $post_id );
		if ( is_array( $upload ) && ! empty( $upload['url'] ) ) {
			$image = (string) $upload['url'];
		} elseif ( is_string( $upload ) && '' !== trim( $upload ) ) {
			$image = trim( $upload );
		}

		if ( '' === $image ) {
			$preset = trim( (string) get_field( 'cta_background_preset', $post_id ) );
			if ( '' !== $preset && function_exists( 'ftd_get_page_header_background_preset_url' ) ) {
				$image = ftd_get_page_header_background_preset_url( $preset );
			}
		}
	}

	return array(
		'color' => $color,
		'image' => $image,
	);
}

/**
 * Inline style attribute for the promo CTA card background.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_session_cta_background_style_attr( $post_id = 0 ) {
	$config = ftd_get_session_cta_background_config( $post_id );
	$parts  = array(
		'background-color:' . esc_attr( $config['color'] ),
	);

	if ( $config['image'] ) {
		$parts[] = 'background-image:url(' . esc_url( $config['image'] ) . ')';
		$parts[] = 'background-position:center';
		$parts[] = 'background-repeat:no-repeat';
		$parts[] = 'background-size:cover';
	}

	return implode( ';', $parts );
}
