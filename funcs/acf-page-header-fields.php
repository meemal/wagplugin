<?php
/**
 * ACF: Standardised page header — Pages + Live Sessions archive settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_PAGE_HEADER_ARCHIVE_OPTION', 'genius-directory-live-sessions-header' );
define( 'FTD_PAGE_HEADER_GROUP_NAME', 'page_header' );

add_action( 'acf/init', 'ftd_register_page_header_archive_subpage', 12 );
add_action( 'acf/init', 'ftd_register_page_header_acf_fields', 16 );

/**
 * Register Live Sessions archive header settings under Genius Directory Settings.
 */
function ftd_register_page_header_archive_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	$parent = function_exists( 'ftd_get_genius_directory_settings_parent_slug' )
		? ftd_get_genius_directory_settings_parent_slug()
		: ( defined( 'FTD_GENIUS_DIRECTORY_SETTINGS_OPTION' ) ? FTD_GENIUS_DIRECTORY_SETTINGS_OPTION : 'genius-directory-settings' );

	$slug = FTD_PAGE_HEADER_ARCHIVE_OPTION;

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Live Sessions header',
			'menu_title'  => 'Live Sessions header',
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
function ftd_get_page_header_archive_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids  = array();
	$slug = FTD_PAGE_HEADER_ARCHIVE_OPTION;

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

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Map logical field keys (used in PHP) to group sub-field names.
 *
 * @return array<string, string>
 */
function ftd_page_header_field_key_map() {
	return array(
		'page_header_eyebrow'              => 'eyebrow',
		'page_header_title'                  => 'title',
		'page_header_title_accent'           => 'title_accent',
		'page_header_subtitle'             => 'subtitle',
		'page_header_description_override'   => 'description_override',
		'page_header_background_preset'    => 'background_preset',
		'page_header_background_image'     => 'background_image',
		'page_header_background_color'     => 'background_color',
		'page_header_cta_enabled'          => 'cta_enabled',
		'page_header_cta_label'            => 'cta_label',
		'page_header_cta_url'              => 'cta_url',
	);
}

/**
 * Shared page header content fields (nested ACF group sub-fields).
 *
 * @return array<int, array<string, mixed>>
 */
function ftd_get_page_header_content_acf_fields() {
	return array(
		array(
			'key'           => 'field_ftd_ph_eyebrow',
			'label'         => 'Eyebrow label',
			'name'          => 'eyebrow',
			'type'          => 'text',
			'placeholder'   => 'GENIUS NETWORK - LIVE SESSIONS',
		),
		array(
			'key'           => 'field_ftd_ph_title',
			'label'         => 'Title (first colour)',
			'name'          => 'title',
			'type'          => 'text',
			'placeholder'   => 'Rising',
		),
		array(
			'key'           => 'field_ftd_ph_title_accent',
			'label'         => 'Title accent (second colour)',
			'name'          => 'title_accent',
			'type'          => 'text',
			'placeholder'   => 'together',
		),
		array(
			'key'           => 'field_ftd_ph_subtitle',
			'label'         => 'Subtitle',
			'name'          => 'subtitle',
			'type'          => 'text',
			'placeholder'   => "the people you're seeking are seeking you,",
		),
		array(
			'key'           => 'field_ftd_ph_description_override',
			'label'         => 'Description override',
			'name'          => 'description_override',
			'type'          => 'textarea',
			'rows'          => 4,
			'new_lines'     => 'br',
			'instructions'  => 'Optional. Leave blank to use this page’s SEO meta description.',
		),
		array(
			'key'           => 'field_ftd_ph_bg_preset',
			'label'         => 'Background preset',
			'name'          => 'background_preset',
			'type'          => 'select',
			'choices'       => ftd_get_page_header_background_preset_choices(),
			'allow_null'    => 1,
			'ui'            => 1,
			'default_value' => '',
			'instructions'  => 'Pick a bundled background. Upload a custom image below to override.',
		),
		array(
			'key'           => 'field_ftd_ph_bg_image',
			'label'         => 'Custom background image',
			'name'          => 'background_image',
			'type'          => 'image',
			'return_format' => 'url',
			'preview_size'  => 'medium',
			'instructions'  => 'Optional. Overrides the preset above when set.',
		),
		array(
			'key'           => 'field_ftd_ph_bg_color',
			'label'         => 'Background colour',
			'name'          => 'background_color',
			'type'          => 'color_picker',
			'default_value' => '#673f69',
		),
		array(
			'key'           => 'field_ftd_ph_cta_enabled',
			'label'         => 'Show button',
			'name'          => 'cta_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 0,
			'instructions'  => 'Optional call-to-action button below the description.',
		),
		array(
			'key'               => 'field_ftd_ph_cta_label',
			'label'             => 'Button label',
			'name'              => 'cta_label',
			'type'              => 'text',
			'placeholder'       => 'Join the directory',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_ftd_ph_cta_enabled',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		),
		array(
			'key'               => 'field_ftd_ph_cta_url',
			'label'             => 'Button link',
			'name'              => 'cta_url',
			'type'              => 'text',
			'placeholder'       => '/join-we-are-geniuses/',
			'instructions'      => 'Optional. Defaults to the directory signup URL.',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_ftd_ph_cta_enabled',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		),
	);
}

/**
 * Reusable ACF group field wrapping all header content settings.
 *
 * @param bool $conditional_on_enable When true, show only if page header is enabled.
 * @return array<string, mixed>
 */
function ftd_get_page_header_group_acf_field( $conditional_on_enable = false ) {
	$field = array(
		'key'        => 'field_ftd_ph_group',
		'label'      => 'Header content',
		'name'       => FTD_PAGE_HEADER_GROUP_NAME,
		'type'       => 'group',
		'layout'     => 'block',
		'sub_fields' => ftd_get_page_header_content_acf_fields(),
	);

	if ( $conditional_on_enable ) {
		$field['conditional_logic'] = array(
			array(
				array(
					'field'    => 'field_ftd_ph_enabled',
					'operator' => '==',
					'value'    => '1',
				),
			),
		);
	}

	return $field;
}

/**
 * Apply archive default values to group sub-fields.
 *
 * @param array<int, array<string, mixed>> $sub_fields Sub-fields.
 * @param array<string, mixed>             $defaults   Defaults keyed by logical field names.
 * @return array<int, array<string, mixed>>
 */
function ftd_apply_page_header_group_defaults( $sub_fields, $defaults ) {
	$key_map = array_flip( ftd_page_header_field_key_map() );

	foreach ( $sub_fields as $index => $field ) {
		if ( empty( $field['name'] ) || ! isset( $key_map[ $field['name'] ] ) ) {
			continue;
		}

		$logical = $key_map[ $field['name'] ];

		if ( ! isset( $defaults[ $logical ] ) ) {
			continue;
		}

		if ( in_array( $field['type'], array( 'text', 'textarea', 'color_picker', 'select', 'true_false' ), true ) ) {
			$sub_fields[ $index ]['default_value'] = $defaults[ $logical ];
		}
	}

	return $sub_fields;
}

/**
 * Register ACF field groups for page header.
 */
function ftd_register_page_header_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$archive_defaults = ftd_get_page_header_archive_default_config();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_page_header_page',
			'title'                 => 'Page header',
			'fields'                => array(
				array(
					'key'           => 'field_ftd_ph_enabled',
					'label'         => 'Enable page header',
					'name'          => 'page_header_enabled',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
					'instructions'  => 'Show the full-width hero header on this page. You can also assign the “WAG Page with Header” template.',
				),
				ftd_get_page_header_group_acf_field( true ),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'menu_order'            => 5,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);

	$archive_group = ftd_get_page_header_group_acf_field( false );
	$archive_group['sub_fields'] = ftd_apply_page_header_group_defaults(
		$archive_group['sub_fields'],
		$archive_defaults
	);

	$archive_fields = array(
		array(
			'key'     => 'field_ftd_ph_archive_intro',
			'label'   => '',
			'name'    => '',
			'type'    => 'message',
			'message' => 'Header shown at the top of the <strong>Genius Network Live Sessions</strong> archive page.',
		),
		$archive_group,
		array(
			'key'           => 'field_ftd_ph_featured_title',
			'label'         => 'Featured session — section title',
			'name'          => 'featured_section_title',
			'type'          => 'text',
			'default_value' => 'A new beginning',
			'instructions'  => 'Heading above the featured session card.',
		),
		array(
			'key'           => 'field_ftd_ph_featured_subtitle',
			'label'         => 'Featured session — section subtitle',
			'name'          => 'featured_section_subtitle',
			'type'          => 'text',
			'default_value' => 'doing the work at work,',
			'instructions'  => 'Italic tagline under the section heading.',
		),
		array(
			'key'           => 'field_ftd_ph_featured_cta',
			'label'         => 'Featured session — button label',
			'name'          => 'featured_cta_label',
			'type'          => 'text',
			'default_value' => 'Save my seat',
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_page_header_archive',
			'title'                 => 'Live Sessions archive header',
			'fields'                => $archive_fields,
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => FTD_PAGE_HEADER_ARCHIVE_OPTION,
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);
}
