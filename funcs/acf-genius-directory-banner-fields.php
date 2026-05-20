<?php
/**
 * ACF: Founding spots banner — Genius Directory Settings → Banner subpage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GENIUS_DIRECTORY_BANNER_SLUG', 'genius-directory-banner' );

add_action( 'acf/init', 'ftd_register_genius_directory_banner_subpage', 12 );
add_action( 'acf/init', 'ftd_register_founding_spots_banner_acf_fields', 16 );

/**
 * Parent menu slug for Genius Directory Settings.
 *
 * @return string
 */
function ftd_get_genius_directory_settings_parent_slug() {
	$default = defined( 'FTD_GENIUS_DIRECTORY_SETTINGS_OPTION' )
		? FTD_GENIUS_DIRECTORY_SETTINGS_OPTION
		: 'genius-directory-settings';

	if ( function_exists( 'acf_get_options_pages' ) ) {
		$pages = acf_get_options_pages();
		if ( is_array( $pages ) ) {
			foreach ( $pages as $page ) {
				$menu_title = $page['menu_title'] ?? '';
				$page_title = $page['page_title'] ?? '';
				if (
					false !== stripos( $menu_title, 'Genius Directory Settings' )
					|| false !== stripos( $page_title, 'Genius Directory Settings' )
				) {
					return $page['menu_slug'] ?? $default;
				}
			}
		}
	}

	return $default;
}

/**
 * Register Banner subpage under Genius Directory Settings.
 */
function ftd_register_genius_directory_banner_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	$parent = ftd_get_genius_directory_settings_parent_slug();
	$slug   = FTD_GENIUS_DIRECTORY_BANNER_SLUG;

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Banner',
			'menu_title'  => 'Banner',
			'menu_slug'   => $slug,
			'parent_slug' => $parent,
			'post_id'     => $slug,
			'capability'  => 'manage_options',
		)
	);
}

/**
 * @return string[]
 */
function ftd_get_genius_directory_banner_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids  = array();
	$slug = FTD_GENIUS_DIRECTORY_BANNER_SLUG;

	if ( function_exists( 'acf_get_options_page' ) ) {
		$page = acf_get_options_page( $slug );
		if ( is_array( $page ) ) {
			if ( ! empty( $page['post_id'] ) ) {
				$post_id = function_exists( 'acf_get_valid_post_id' )
					? acf_get_valid_post_id( $page['post_id'] )
					: $page['post_id'];
				$ids[]   = $post_id;
			}
			if ( ! empty( $page['menu_slug'] ) ) {
				$ids[] = $page['menu_slug'];
			}
		}
	}

	$ids[] = $slug;

	if ( function_exists( 'ftd_get_genius_directory_settings_post_ids' ) ) {
		$ids = array_merge( $ids, ftd_get_genius_directory_settings_post_ids() );
	}

	$ids[] = 'options';
	$ids[] = 'option';

	$ids = array_values( array_unique( array_filter( $ids ) ) );

	return $ids;
}

/**
 * @param string $field Field name.
 * @return mixed
 */
function ftd_genius_directory_banner_get_field( $field ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	foreach ( ftd_get_genius_directory_banner_post_ids() as $post_id ) {
		$value = get_field( $field, $post_id );

		if ( false === $value || null === $value ) {
			continue;
		}

		if ( is_array( $value ) ) {
			return $value;
		}

		if ( '' !== $value ) {
			return $value;
		}
	}

	return null;
}

/**
 * Register founding spots banner field group.
 */
function ftd_register_founding_spots_banner_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_founding_spots_banner',
			'title'                 => 'Founding spots banner',
			'fields'                => array(
				array(
					'key'           => 'field_ftd_fsb_enabled',
					'label'         => 'Show banner',
					'name'          => 'founding_spots_banner_enabled',
					'type'          => 'true_false',
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'        => 'field_ftd_fsb_group',
					'label'      => 'Banner',
					'name'       => 'founding_spots_banner',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => array(
						array(
							'key'           => 'field_ftd_fsb_total',
							'label'         => 'Total founding spots',
							'name'          => 'total_spots',
							'type'          => 'number',
							'default_value' => 111,
							'min'           => 1,
							'step'          => 1,
						),
						array(
							'key'           => 'field_ftd_fsb_code',
							'label'         => 'Discount code (PMPro)',
							'name'          => 'discount_code',
							'type'          => 'text',
							'default_value' => 'originalgenius111',
							'instructions'  => 'Used to count signups and in the CTA text. Tokens: {code}, {remaining}, {total}, {used}.',
						),
						array(
							'key'           => 'field_ftd_fsb_stats_eyebrow',
							'label'         => 'Stats eyebrow label',
							'name'          => 'stats_eyebrow',
							'type'          => 'text',
							'default_value' => 'SO FAR',
							'instructions'  => 'Small label before each live stat in the scrolling banner.',
						),
						array(
							'key'           => 'field_ftd_fsb_code_label',
							'label'         => 'Discount code label',
							'name'          => 'code_label',
							'type'          => 'text',
							'default_value' => 'use code',
							'instructions'  => 'Shown inside the dashed code pill on the founding offer slide.',
						),
						array(
							'key'           => 'field_ftd_fsb_signup_label',
							'label'         => 'Sign up button label',
							'name'          => 'signup_button_label',
							'type'          => 'text',
							'default_value' => 'Sign up now',
						),
						array(
							'key'           => 'field_ftd_fsb_link_url',
							'label'         => 'Sign up button URL',
							'name'          => 'link_url',
							'type'          => 'url',
							'default_value' => '/join-we-are-geniuses/',
						),
						array(
							'key'           => 'field_ftd_fsb_progress',
							'label'         => 'Show progress bar',
							'name'          => 'show_progress_bar',
							'type'          => 'true_false',
							'default_value' => 1,
							'ui'            => 1,
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => FTD_GENIUS_DIRECTORY_BANNER_SLUG,
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
