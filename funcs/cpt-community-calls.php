<?php
/**
 * Custom post type: Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		$plural   = ftd_gnls_label_plural();
		$singular = ftd_gnls_label_singular();

		register_post_type(
			FTD_COMMUNITY_CALL_POST_TYPE,
			array(
				'labels'              => array(
					'name'               => $plural,
					'singular_name'      => $singular,
					'add_new'            => __( 'Add New Session', 'ftd-directory-listings' ),
					'add_new_item'       => __( 'Add New Live Session', 'ftd-directory-listings' ),
					'edit_item'          => __( 'Edit Live Session', 'ftd-directory-listings' ),
					'new_item'           => __( 'New Live Session', 'ftd-directory-listings' ),
					'view_item'          => __( 'View Live Session', 'ftd-directory-listings' ),
					'search_items'       => __( 'Search Live Sessions', 'ftd-directory-listings' ),
					'not_found'          => __( 'No live sessions found', 'ftd-directory-listings' ),
					'not_found_in_trash' => __( 'No live sessions found in Trash', 'ftd-directory-listings' ),
					'all_items'          => $plural,
					'archives'           => $plural,
					'menu_name'          => $plural,
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'rewrite'             => array(
					'slug'       => 'genius-network-live-sessions',
					'with_front' => false,
				),
				'menu_icon'           => 'dashicons-microphone',
				'menu_position'       => 21,
				'supports'            => array( 'title', 'thumbnail' ),
				'capability_type'     => 'post',
			)
		);
	}
);

add_action( 'template_redirect', 'ftd_gnls_redirect_legacy_urls', 1 );

/**
 * Redirect old community-calls URLs to the new live sessions slug.
 */
function ftd_gnls_redirect_legacy_urls() {
	if ( is_admin() ) {
		return;
	}

	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );

	if ( ! is_string( $path ) || false === strpos( $path, '/genius-community-calls' ) ) {
		return;
	}

	$new_path = str_replace( '/genius-community-calls', '/genius-network-live-sessions', $path );

	wp_safe_redirect( home_url( $new_path ), 301 );
	exit;
}

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
			return;
		}

		$query->set(
			'meta_query',
			array(
				'relation'                => 'OR',
				'call_datetime_clause'    => array(
					'key'     => 'call_datetime',
					'compare' => 'EXISTS',
				),
				'no_call_datetime_clause' => array(
					'key'     => 'call_datetime',
					'compare' => 'NOT EXISTS',
				),
			)
		);
		$query->set(
			'orderby',
			array(
				'call_datetime_clause' => 'ASC',
				'date'                 => 'DESC',
			)
		);
	}
);
