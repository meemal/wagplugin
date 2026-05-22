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
 * Default stats ticker copy.
 *
 * @return array<string, string>
 */
function ftd_get_stats_ticker_text_defaults() {
	return array(
		'offer_eyebrow'         => 'OFFER',
		'founding_spots_label'  => 'of {total} founding spots left',
		'members_label'         => 'members',
		'members_tagline'       => 'the community grows,',
		'map_label'             => 'on the genius map',
		'map_tagline'           => 'sharing their genius,',
		'listings_label'        => 'directory listings',
		'listings_tagline'      => 'sharing their gifts,',
		'creator_label'         => 'creator geniuses',
		'creator_tagline'       => 'building the directory,',
		'quantum_label'         => 'quantum geniuses',
		'quantum_tagline'       => 'leading the way,',
		'last_signup_label'     => 'last sign-up from',
		'last_signup_tagline'   => 'the threshold opens,',
	);
}

/**
 * ACF subfields for stats ticker text.
 *
 * @return array<int, array<string, mixed>>
 */
function ftd_stats_ticker_acf_text_fields() {
	$defaults = ftd_get_stats_ticker_text_defaults();
	$fields   = array(
		array(
			'key'   => 'field_ftd_stt_offer_eyebrow',
			'label' => 'Founding offer eyebrow',
			'name'  => 'offer_eyebrow',
		),
		array(
			'key'           => 'field_ftd_stt_founding_label',
			'label'         => 'Founding spots label',
			'name'          => 'founding_spots_label',
			'instructions'  => 'Use {total} for the spot cap (111). Example: of {total} founding spots left',
		),
		array(
			'key'   => 'field_ftd_stt_members_label',
			'label' => 'Members — label',
			'name'  => 'members_label',
		),
		array(
			'key'   => 'field_ftd_stt_members_tagline',
			'label' => 'Members — tagline',
			'name'  => 'members_tagline',
		),
		array(
			'key'   => 'field_ftd_stt_map_label',
			'label' => 'Genius map — label',
			'name'  => 'map_label',
		),
		array(
			'key'   => 'field_ftd_stt_map_tagline',
			'label' => 'Genius map — tagline',
			'name'  => 'map_tagline',
		),
		array(
			'key'   => 'field_ftd_stt_listings_label',
			'label' => 'Directory listings — label',
			'name'  => 'listings_label',
		),
		array(
			'key'   => 'field_ftd_stt_listings_tagline',
			'label' => 'Directory listings — tagline',
			'name'  => 'listings_tagline',
		),
		array(
			'key'   => 'field_ftd_stt_creator_label',
			'label' => 'Creator geniuses — label',
			'name'  => 'creator_label',
		),
		array(
			'key'   => 'field_ftd_stt_creator_tagline',
			'label' => 'Creator geniuses — tagline',
			'name'  => 'creator_tagline',
		),
		array(
			'key'   => 'field_ftd_stt_quantum_label',
			'label' => 'Quantum geniuses — label',
			'name'  => 'quantum_label',
		),
		array(
			'key'   => 'field_ftd_stt_quantum_tagline',
			'label' => 'Quantum geniuses — tagline',
			'name'  => 'quantum_tagline',
		),
		array(
			'key'   => 'field_ftd_stt_last_signup_label',
			'label' => 'Last sign-up — label',
			'name'  => 'last_signup_label',
		),
		array(
			'key'   => 'field_ftd_stt_last_signup_tagline',
			'label' => 'Last sign-up — tagline',
			'name'  => 'last_signup_tagline',
		),
	);

	foreach ( $fields as $index => $field ) {
		$name = $field['name'];
		$fields[ $index ]['type']          = 'text';
		$fields[ $index ]['default_value'] = $defaults[ $name ] ?? '';
	}

	return $fields;
}

/**
 * Stats ticker copy from ACF (with defaults).
 *
 * @return array<string, string>
 */
function ftd_get_stats_ticker_text_settings() {
	$settings = ftd_get_stats_ticker_text_defaults();

	if ( function_exists( 'ftd_genius_directory_banner_get_field' ) ) {
		$acf = ftd_genius_directory_banner_get_field( 'stats_ticker_text' );
		if ( is_array( $acf ) ) {
			foreach ( array_keys( $settings ) as $key ) {
				if ( isset( $acf[ $key ] ) && is_string( $acf[ $key ] ) && '' !== trim( $acf[ $key ] ) ) {
					$settings[ $key ] = trim( $acf[ $key ] );
				}
			}
		}
	}

	return apply_filters( 'ftd_stats_ticker_text_settings', $settings );
}

/**
 * Default copy for [founding_genius_banner].
 *
 * @return array<string, string>
 */
function ftd_get_founding_genius_banner_text_defaults() {
	return array(
		'badge'          => 'Founding genius offer',
		'headline'       => 'Free lifetime listing &mdash; forever',
		'subtext'        => 'be one of the original {total},',
		'code_label'     => 'use code',
		'joined_text'    => '{used} joined',
		'remaining_text' => '{remaining} left',
		'all_claimed'    => 'All {total} founding spots have been claimed',
		'claimed_notice' => 'The founding offer has now closed. Join now to access our standard plans.',
	);
}

/**
 * ACF subfields for founding genius banner text.
 *
 * @return array<int, array<string, mixed>>
 */
function ftd_founding_genius_banner_acf_text_fields() {
	$defaults = ftd_get_founding_genius_banner_text_defaults();
	$token_help = 'Tokens: {total}, {used}, {remaining}, {code}. Used and remaining update live from membership data.';

	$fields = array(
		array(
			'key'     => 'field_ftd_fgb_intro',
			'label'   => '',
			'name'    => '',
			'type'    => 'message',
			'message' => 'Copy for the <code>[founding_genius_banner]</code> block on Live Sessions pages. ' . $token_help . ' Total spots and discount code come from the sitewide banner settings above.',
		),
		array(
			'key'   => 'field_ftd_fgb_badge',
			'label' => 'Eyebrow label',
			'name'  => 'badge',
		),
		array(
			'key'   => 'field_ftd_fgb_headline',
			'label' => 'Headline',
			'name'  => 'headline',
		),
		array(
			'key'   => 'field_ftd_fgb_subtext',
			'label' => 'Subtext',
			'name'  => 'subtext',
		),
		array(
			'key'   => 'field_ftd_fgb_code_label',
			'label' => 'Discount code label',
			'name'  => 'code_label',
		),
		array(
			'key'   => 'field_ftd_fgb_joined_text',
			'label' => 'Progress — joined label',
			'name'  => 'joined_text',
		),
		array(
			'key'   => 'field_ftd_fgb_remaining_text',
			'label' => 'Progress — remaining label',
			'name'  => 'remaining_text',
		),
		array(
			'key'   => 'field_ftd_fgb_all_claimed',
			'label' => 'All claimed — headline',
			'name'  => 'all_claimed',
		),
		array(
			'key'   => 'field_ftd_fgb_claimed_notice',
			'label' => 'All claimed — notice',
			'name'  => 'claimed_notice',
		),
	);

	foreach ( $fields as $index => $field ) {
		if ( 'message' === ( $field['type'] ?? '' ) ) {
			continue;
		}

		$name = $field['name'];
		$fields[ $index ]['type']          = 'text';
		$fields[ $index ]['default_value'] = $defaults[ $name ] ?? '';
	}

	return $fields;
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
							'instructions'  => 'Shown in the banner CTA. Tokens: {code}, {remaining}, {total}, {used}. Spots remaining = total minus Creator and Quantum Genius members.',
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
							'type'          => 'text',
							'default_value' => '/join-we-are-geniuses/',
							'instructions'  => 'Relative path (e.g. /join-we-are-geniuses/) or full URL (https://…). Relative paths use this site\'s address.',
							'placeholder'   => '/join-we-are-geniuses/',
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
				array(
					'key'           => 'field_ftd_fsb_ticker_text',
					'label'         => 'Stats ticker text',
					'name'          => 'stats_ticker_text',
					'type'          => 'group',
					'layout'        => 'block',
					'instructions'  => 'Copy for each slide in the scrolling stats ticker and sitewide banner. Use {total} in the founding spots label.',
					'sub_fields'    => ftd_stats_ticker_acf_text_fields(),
				),
				array(
					'key'        => 'field_ftd_fgb_group',
					'label'      => 'Founding genius banner',
					'name'       => 'founding_genius_banner',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => ftd_founding_genius_banner_acf_text_fields(),
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
