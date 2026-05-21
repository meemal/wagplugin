<?php
/**
 * ACF: WAG Features CTAs — Genius Directory Settings options page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Options page slug used for get_field( $name, $post_id ). */
define( 'FTD_GENIUS_DIRECTORY_SETTINGS_OPTION', 'genius-directory-settings' );

add_action( 'acf/init', 'ftd_register_genius_directory_settings_options_page', 5 );
add_action( 'acf/init', 'ftd_register_wag_features_ctas_acf_fields', 15 );

/**
 * Card accent presets (gradient + badge colours).
 *
 * @return array<string, string>
 */
function ftd_feature_cta_style_choices() {
	return array(
		'magenta' => 'Magenta — newly launched',
		'coral'   => 'Coral — coming soon',
		'sunset'  => 'Sunset — popular / emphasis',
		'plum'    => 'Plum — limited / beta',
	);
}

/**
 * Candidate ACF post_id values for Genius Directory Settings (most specific first).
 *
 * @return string[]
 */
function ftd_get_genius_directory_settings_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids    = array();
	$slug   = FTD_GENIUS_DIRECTORY_SETTINGS_OPTION;
	$found  = false;

	if ( function_exists( 'acf_get_options_pages' ) ) {
		$pages = acf_get_options_pages();
		if ( is_array( $pages ) ) {
			foreach ( $pages as $page ) {
				$menu_title = $page['menu_title'] ?? '';
				$page_title = $page['page_title'] ?? '';
				if (
					false === stripos( $menu_title, 'Genius Directory Settings' )
					&& false === stripos( $page_title, 'Genius Directory Settings' )
				) {
					continue;
				}

				$found = true;
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
	}

	if ( ! $found && function_exists( 'acf_get_options_page' ) ) {
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
	$ids[] = 'options';
	$ids[] = 'option';

	$ids = array_values( array_unique( array_filter( $ids ) ) );

	return $ids;
}

/**
 * Primary ACF post_id for Genius Directory Settings.
 *
 * @return string
 */
function ftd_get_genius_directory_settings_option_id() {
	$ids = ftd_get_genius_directory_settings_post_ids();

	return $ids[0] ?? FTD_GENIUS_DIRECTORY_SETTINGS_OPTION;
}

/**
 * Ensure Genius Directory Settings exists in wp-admin.
 */
function ftd_register_genius_directory_settings_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	$slug = FTD_GENIUS_DIRECTORY_SETTINGS_OPTION;

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
					return;
				}
			}
		}
	}

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => 'Genius Directory Settings',
			'menu_title'  => 'Genius Directory Settings',
			'menu_slug'   => $slug,
			'post_id'     => $slug,
			'capability'  => 'manage_options',
			'redirect'    => false,
			'icon_url'    => 'dashicons-groups',
			'position'    => 58,
		)
	);
}

/**
 * Read an ACF field from Genius Directory Settings, trying all known post_id values.
 *
 * @param string $field Field name.
 * @return mixed
 */
function ftd_genius_directory_settings_get_field( $field ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	foreach ( ftd_get_genius_directory_settings_post_ids() as $post_id ) {
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
 * Register WAG Features CTAs field group (four feature cards).
 */
function ftd_register_wag_features_ctas_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$option_id = ftd_get_genius_directory_settings_option_id();

	$cards = array(
		array(
			'index'        => 1,
			'tab'          => 'Genius directory',
			'title'        => 'Genius directory',
			'description'  => 'Easily find advanced Joe Dispenza students based on their expertise!',
			'style'        => 'sunset',
			'secondary'    => 'Levels & Prices',
		),
		array(
			'index'        => 2,
			'tab'          => 'Genius map',
			'title'        => 'Genius map',
			'description'  => 'Zoom in on Geniuses in your area or worldwide!',
			'style'        => 'magenta',
			'secondary'    => 'Levels & Prices',
		),
		array(
			'index'        => 3,
			'tab'          => 'Share your genius',
			'title'        => 'Share your genius',
			'description'  => 'Let others connect with you by location or skills!',
			'style'        => 'coral',
			'secondary'    => '',
		),
		array(
			'index'        => 4,
			'tab'          => 'Genius Network Call',
			'title'        => 'Genius Network Call',
			'description'  => "Let's raise each other up professionally!",
			'style'        => 'plum',
			'secondary'    => '',
		),
	);

	$fields = array();

	foreach ( $cards as $card ) {
		$fields[] = array(
			'key'   => 'field_ftd_fcta_tab_' . $card['index'],
			'label' => $card['tab'],
			'type'  => 'tab',
		);
		$fields[] = ftd_feature_cta_group_fields( $card );
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_wag_features_ctas',
			'title'                 => 'WAG Features CTAs',
			'fields'                => $fields,
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => $option_id,
					),
				),
			),
			'menu_order'            => 10,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/**
 * One feature CTA group (generic field names).
 *
 * @param array<string, mixed> $card Card config.
 * @return array<string, mixed>
 */
function ftd_feature_cta_group_fields( $card ) {
	$n = (int) $card['index'];

	return array(
		'key'        => "field_ftd_fcta_{$n}",
		'label'      => 'Feature CTA ' . $n,
		'name'       => "feature_cta_{$n}",
		'type'       => 'group',
		'layout'     => 'block',
		'sub_fields' => array(
			array(
				'key'           => "field_ftd_fcta_{$n}_image",
				'label'         => 'Feature image',
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'instructions'  => 'Optional. Shown in the top area; gradient shows when empty.',
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_style",
				'label'         => 'Card accent style',
				'name'          => 'style',
				'type'          => 'select',
				'choices'       => ftd_feature_cta_style_choices(),
				'default_value' => $card['style'],
				'allow_null'    => 0,
				'ui'            => 1,
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_small_label",
				'label'         => 'Feature CTA small label',
				'name'          => 'small_label',
				'type'          => 'text',
				'placeholder'   => 'e.g. New, Coming soon, Popular, Beta',
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_title",
				'label'         => 'Feature CTA Title',
				'name'          => 'title',
				'type'          => 'text',
				'default_value' => $card['title'],
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_description",
				'label'         => 'Feature CTA description',
				'name'          => 'description',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => $card['description'],
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_link",
				'label'         => 'Primary button link',
				'name'          => 'link',
				'type'          => 'link',
				'instructions'  => 'Main CTA (defaults to “Read more” on the front end).',
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_button_label",
				'label'         => 'Primary button label',
				'name'          => 'button_label',
				'type'          => 'text',
				'default_value' => 'Read more',
				'placeholder'   => 'Read more',
			),
			array(
				'key'           => "field_ftd_fcta_{$n}_feature_link",
				'label'         => 'Feature link',
				'name'          => 'feature_link',
				'type'          => 'link',
				'instructions'  => $card['secondary']
					? 'Links the feature image and title. e.g. ' . $card['secondary']
					: 'Links the feature image and title.',
			),
		),
	);
}
