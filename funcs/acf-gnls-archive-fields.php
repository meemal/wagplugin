<?php
/**
 * ACF: Live Sessions archive page content — under the CPT admin menu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GNLS_ARCHIVE_OPTION', 'genius-network-live-sessions-archive' );

add_action( 'acf/init', 'ftd_register_gnls_archive_options_page', 12 );
add_action( 'acf/init', 'ftd_register_gnls_archive_acf_fields', 16 );
add_filter( 'acf/load_value/name=intro_col1_highlights', 'ftd_default_gnls_archive_col1_highlights', 10, 3 );

/**
 * Register archive page settings under Genius Network Live Sessions.
 */
function ftd_register_gnls_archive_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	$slug = FTD_GNLS_ARCHIVE_OPTION;

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Archive page content',
			'menu_title'  => 'Archive page',
			'menu_slug'   => $slug,
			'parent_slug' => 'edit.php?post_type=' . FTD_COMMUNITY_CALL_POST_TYPE,
			'post_id'     => $slug,
			'capability'  => 'edit_posts',
		)
	);
}

/**
 * @return string[]
 */
function ftd_get_gnls_archive_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids  = array();
	$slug = FTD_GNLS_ARCHIVE_OPTION;

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
	$ids[] = 'options';
	$ids[] = 'option';

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Default archive page copy.
 *
 * @return array<string, string>
 */
function ftd_get_gnls_archive_defaults() {
	return array(
		'intro_eyebrow'        => "What it's really about",
		'intro_title'          => 'Quietly working alone, together',
		'intro_tagline'        => 'where waveforms join,',
		'intro_col1_title'     => 'What the Genius Live Calls are all about',
		'intro_col1_content'   => "So many of us are quietly working away on our own little things. A business, a practice, a course, the next idea — and mostly we're doing it on our own. If we do go to a networking event, it's with people who are just not considering the 5D at all!\n\nThese calls are about expanding the work into our working lives, becoming each other's cheerleaders, growing together and sharing both practical and spiritual ideas!\n\nThere's a real shift that happens when people who are on the same wavelength get in a room together. We meet each other's energy. Waveforms that join together increase in amplitude — we can literally raise each other's vibrations. Oh, and we can learn all sorts of business tips too, validate each other and gently nudge to see what we may be missing ourselves! Working with high vibration people brings you to a whole other level!\n\nWe will be each other's cheerleaders.",
		'intro_col2_title'     => 'How a typical call might go',
		'intro_col2_content'   => "A warm welcome to start, and a few words on why we're here.\n\nThen a short topic to bring us all onto the same page, just enough to give us somewhere to begin.\n\nSome real conversation. A few gentle prompts to get us reflecting, and space for anyone who wants to share. If lots of people come along, we'll break into smaller groups so it never feels like a lecture and everyone gets a turn.\n\nThe calls are recorded too, so if you can't make it live, the replay will be there waiting. We are full of ideas on how this could evolve, but we're happy to gently push forward and see where it takes us.",
		'sessions_title'       => 'Sessions',
		'sessions_empty_msg' => 'No live sessions have been published yet. Check back soon — the first call details will appear here.',
	);
}

/**
 * Default highlight bullets for column 1.
 *
 * @return array<int, array{text: string}>
 */
function ftd_get_gnls_archive_col1_highlights_defaults() {
	return array(
		array(
			'text' => "This isn't another meditation group, you've already got that side covered.",
		),
		array(
			'text' => "This isn't a place to sell, although who knows where the connections might lead at some point!",
		),
		array(
			'text' => "We're going to bring in the 5D aspect while we can also talk about the 3D.",
		),
		array(
			'text' => "Ideas will flow faster when we're vibing high together.",
		),
	);
}

/**
 * Normalize a highlight repeater row from ACF.
 *
 * @param array<string, mixed> $row Repeater row.
 * @return array{text: string}|null
 */
function ftd_parse_gnls_archive_highlight_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}

	$text = trim( (string) ( $row['text'] ?? $row['field_ftd_gnla_col1_highlight_text'] ?? '' ) );

	if ( '' === $text ) {
		return null;
	}

	return array(
		'text' => $text,
	);
}

/**
 * @param array<int, array<string, mixed>> $rows Repeater rows.
 * @return array<int, array{text: string}>
 */
function ftd_parse_gnls_archive_highlight_rows( $rows ) {
	$highlights = array();

	if ( ! is_array( $rows ) ) {
		return $highlights;
	}

	foreach ( $rows as $row ) {
		$parsed = ftd_parse_gnls_archive_highlight_row( $row );

		if ( null !== $parsed ) {
			$highlights[] = $parsed;
		}
	}

	return $highlights;
}

/**
 * Pre-fill empty highlight repeater in admin and on read.
 *
 * @param mixed $value   Stored value.
 * @param mixed $post_id Options post id.
 * @param array $field   ACF field array.
 * @return mixed
 */
function ftd_default_gnls_archive_col1_highlights( $value, $post_id, $field ) {
	if ( ! empty( $value ) ) {
		return $value;
	}

	return ftd_get_gnls_archive_col1_highlights_defaults();
}

/**
 * Read highlight bullets for column 1.
 *
 * @return array<int, array{text: string}>
 */
function ftd_get_gnls_archive_col1_highlights() {
	if ( ! function_exists( 'get_field' ) ) {
		return ftd_get_gnls_archive_col1_highlights_defaults();
	}

	foreach ( ftd_get_gnls_archive_post_ids() as $post_id ) {
		$value = get_field( 'intro_col1_highlights', $post_id );

		if ( false === $value || null === $value ) {
			continue;
		}

		$highlights = ftd_parse_gnls_archive_highlight_rows( $value );

		if ( ! empty( $highlights ) ) {
			return $highlights;
		}
	}

	return ftd_get_gnls_archive_col1_highlights_defaults();
}

/**
 * Read an archive page option field.
 *
 * @param string $field Field name.
 * @return mixed
 */
function ftd_get_gnls_archive_field( $field ) {
	if ( ! function_exists( 'get_field' ) ) {
		$defaults = ftd_get_gnls_archive_defaults();

		return $defaults[ $field ] ?? null;
	}

	foreach ( ftd_get_gnls_archive_post_ids() as $post_id ) {
		$value = get_field( $field, $post_id );

		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	$defaults = ftd_get_gnls_archive_defaults();

	return $defaults[ $field ] ?? null;
}

/**
 * Build settings array for the archive page.
 *
 * @return array<string, string>
 */
function ftd_get_gnls_archive_settings() {
	$defaults = ftd_get_gnls_archive_defaults();
	$settings = array();

	foreach ( array_keys( $defaults ) as $key ) {
		$value = ftd_get_gnls_archive_field( $key );

		$settings[ $key ] = is_string( $value ) ? $value : (string) ( $defaults[ $key ] ?? '' );
	}

	return $settings;
}

/**
 * Format archive WYSIWYG / textarea content for front-end output.
 *
 * @param string $content Raw content.
 * @return string
 */
function ftd_format_gnls_archive_content( $content ) {
	$content = trim( (string) $content );

	if ( '' === $content ) {
		return '';
	}

	if ( false !== strpos( $content, '<p' ) || false !== strpos( $content, '<ul' ) || false !== strpos( $content, '<ol' ) ) {
		return wp_kses_post( $content );
	}

	return wp_kses_post( wpautop( $content ) );
}

/**
 * Register ACF fields for the archive page.
 */
function ftd_register_gnls_archive_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$defaults = ftd_get_gnls_archive_defaults();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_gnls_archive',
			'title'                 => 'Archive page content',
			'fields'                => array(
				array(
					'key'     => 'field_ftd_gnla_intro',
					'label'   => 'Intro section',
					'name'    => '',
					'type'    => 'tab',
					'placement' => 'top',
				),
				array(
					'key'           => 'field_ftd_gnla_intro_msg',
					'label'         => '',
					'name'          => '',
					'type'          => 'message',
					'message'       => 'Centred header above the two-column explainer on the <a href="' . esc_url( get_post_type_archive_link( FTD_COMMUNITY_CALL_POST_TYPE ) ) . '" target="_blank">Live Sessions archive</a>.',
				),
				array(
					'key'           => 'field_ftd_gnla_intro_eyebrow',
					'label'         => 'Eyebrow label',
					'name'          => 'intro_eyebrow',
					'type'          => 'text',
					'default_value' => $defaults['intro_eyebrow'],
				),
				array(
					'key'           => 'field_ftd_gnla_intro_title',
					'label'         => 'Main heading',
					'name'          => 'intro_title',
					'type'          => 'text',
					'default_value' => $defaults['intro_title'],
				),
				array(
					'key'           => 'field_ftd_gnla_intro_tagline',
					'label'         => 'Tagline (italic)',
					'name'          => 'intro_tagline',
					'type'          => 'text',
					'default_value' => $defaults['intro_tagline'],
				),
				array(
					'key'     => 'field_ftd_gnla_col1',
					'label'   => 'Column 1',
					'name'    => '',
					'type'    => 'tab',
					'placement' => 'top',
				),
				array(
					'key'           => 'field_ftd_gnla_col1_title',
					'label'         => 'Column heading',
					'name'          => 'intro_col1_title',
					'type'          => 'text',
					'default_value' => $defaults['intro_col1_title'],
				),
				array(
					'key'           => 'field_ftd_gnla_col1_content',
					'label'         => 'Column content',
					'name'          => 'intro_col1_content',
					'type'          => 'wysiwyg',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'tabs'          => 'visual',
					'default_value' => $defaults['intro_col1_content'],
				),
				array(
					'key'          => 'field_ftd_gnla_col1_highlights',
					'label'        => 'Highlight bullets',
					'name'         => 'intro_col1_highlights',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add bullet',
					'instructions' => 'Styled bullet list shown below the column content.',
					'sub_fields'   => array(
						array(
							'key'   => 'field_ftd_gnla_col1_highlight_text',
							'label' => 'Bullet text',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 2,
						),
					),
				),
				array(
					'key'     => 'field_ftd_gnla_col2',
					'label'   => 'Column 2',
					'name'    => '',
					'type'    => 'tab',
					'placement' => 'top',
				),
				array(
					'key'           => 'field_ftd_gnla_col2_title',
					'label'         => 'Column heading',
					'name'          => 'intro_col2_title',
					'type'          => 'text',
					'default_value' => $defaults['intro_col2_title'],
				),
				array(
					'key'           => 'field_ftd_gnla_col2_content',
					'label'         => 'Column content',
					'name'          => 'intro_col2_content',
					'type'          => 'wysiwyg',
					'toolbar'       => 'basic',
					'media_upload'  => 0,
					'tabs'          => 'visual',
					'default_value' => $defaults['intro_col2_content'],
				),
				array(
					'key'     => 'field_ftd_gnla_sessions',
					'label'   => 'Sessions grid',
					'name'    => '',
					'type'    => 'tab',
					'placement' => 'top',
				),
				array(
					'key'           => 'field_ftd_gnla_sessions_title',
					'label'         => 'Section heading',
					'name'          => 'sessions_title',
					'type'          => 'text',
					'default_value' => $defaults['sessions_title'],
				),
				array(
					'key'           => 'field_ftd_gnla_sessions_empty',
					'label'         => 'Empty state message',
					'name'          => 'sessions_empty_msg',
					'type'          => 'textarea',
					'rows'          => 3,
					'default_value' => $defaults['sessions_empty_msg'],
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => FTD_GNLS_ARCHIVE_OPTION,
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
