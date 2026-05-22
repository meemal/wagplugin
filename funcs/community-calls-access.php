<?php
/**
 * Member access and SEO for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'ftd_community_call_output_meta_description', 1 );

/**
 * Output meta description on community call pages.
 */
function ftd_community_call_output_meta_description() {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) && ! is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return;
	}

	$description = '';

	if ( is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		$description = ftd_get_community_call_meta_description();
	} elseif ( is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		$description = __( 'Genius Network Live Sessions for We Are Geniuses members — connect, share, and bring the work into your work.', 'ftd-directory-listings' );
	}

	if ( '' === $description ) {
		return;
	}

	echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
}

add_filter( 'document_title_parts', 'ftd_community_call_document_title' );

/**
 * Append subtitle to browser title on single calls.
 *
 * @param array<string, string> $title Title parts.
 * @return array<string, string>
 */
function ftd_community_call_document_title( $title ) {
	if ( ! is_singular( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return $title;
	}

	$subtitle = ftd_get_community_call_short_description();

	if ( $subtitle ) {
		$title['title'] = trim( get_the_title() . ' · ' . $subtitle );
	}

	return $title;
}
