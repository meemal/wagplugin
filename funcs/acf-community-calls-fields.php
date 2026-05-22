<?php
/**
 * ACF fields for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ftd_register_community_calls_acf_fields' );
add_filter( 'acf/load_value/name=profile_members', 'ftd_default_community_call_profile_members', 10, 3 );

/**
 * Default profile members on new calls.
 *
 * @param mixed $value   Stored value.
 * @param int   $post_id Post ID.
 * @return mixed
 */
function ftd_default_community_call_profile_members( $value, $post_id ) {
	if ( ! empty( $value ) ) {
		return $value;
	}

	$post = $post_id ? get_post( $post_id ) : null;

	if ( $post instanceof WP_Post && 'auto-draft' !== $post->post_status ) {
		$existing = get_post_meta( $post_id, 'profile_members', true );

		if ( '' !== $existing && false !== $existing && null !== $existing ) {
			return $value;
		}

		$legacy = get_post_meta( $post_id, 'involved_members', true );

		if ( '' !== $legacy && false !== $legacy && null !== $legacy ) {
			return $value;
		}
	}

	$default_id = ftd_get_naomi_spirit_user_id();

	return $default_id ? array( $default_id ) : $value;
}

/**
 * Register community call field group.
 */
function ftd_register_community_calls_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_genius_community_call',
			'title'                 => 'Live Session Details',
			'fields'                => array(
				array(
					'key'     => 'field_gcc_intro',
					'label'   => '',
					'name'    => '',
					'type'    => 'message',
					'message' => 'Use the <strong>title</strong> field above for the session name. Set the <strong>featured image</strong> in the sidebar for the promo card artwork (right-hand panel). Use the <strong>YouTube thumbnail</strong> box in the sidebar to generate a 1280×720 PNG from the promo card.',
				),
				array(
					'key'           => 'field_gcc_short_description',
					'label'         => 'Short description',
					'name'          => 'short_description',
					'type'          => 'textarea',
					'rows'          => 3,
					'new_lines'     => 'br',
					'instructions'  => 'Brief summary for archive cards, SEO, and non-member teasers.',
					'placeholder'   => 'Monday 2 June · 7pm UK / 8pm CT',
				),
				array(
					'key'          => 'field_gcc_long_description',
					'label'        => 'Long description',
					'name'         => 'long_description',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => 0,
					'instructions' => 'Full session description shown on the single page.',
				),
				array(
					'key'           => 'field_gcc_what_do_i_need',
					'label'         => 'What do I need',
					'name'          => 'what_do_i_need',
					'type'          => 'textarea',
					'rows'          => 3,
					'new_lines'     => 'br',
					'default_value' => 'Come as you are. No prep needed. Just bring yourself.',
					'instructions'  => 'Shown on the single page under “What do I need”.',
				),
				array(
					'key'           => 'field_gcc_profile_members',
					'label'         => 'Profile links',
					'name'          => 'profile_members',
					'type'          => 'user',
					'role'          => '',
					'allow_null'    => 1,
					'multiple'      => 1,
					'return_format' => 'id',
					'instructions'  => 'Members featured on this call. Links to their profile pages. Defaults to Naomi Spirit on new calls.',
				),
				array(
					'key'           => 'field_gcc_youtube_link',
					'label'         => 'YouTube link',
					'name'          => 'youtube_link',
					'type'          => 'text',
					'default_value' => 'Coming soon',
					'instructions'  => 'Paste a YouTube URL when ready, or leave as “Coming soon”.',
					'placeholder'   => 'Coming soon',
				),
				array(
					'key'            => 'field_gcc_call_datetime',
					'label'          => 'Call date & time',
					'name'           => 'call_datetime',
					'type'           => 'date_time_picker',
					'display_format' => 'l j F Y g:i a',
					'return_format'  => 'Y-m-d H:i:s',
					'first_day'      => 1,
					'instructions'   => 'Optional. Used to sort calls on the archive page.',
				),
				array(
					'key'   => 'field_gcc_promo_tab',
					'label' => 'Promo card',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_gcc_cta_bg_preset',
					'label'         => 'Background preset',
					'name'          => 'cta_background_preset',
					'type'          => 'select',
					'choices'       => function_exists( 'ftd_get_page_header_background_preset_choices' ) ? ftd_get_page_header_background_preset_choices() : array(),
					'allow_null'    => 1,
					'ui'            => 1,
					'default_value' => '',
					'instructions'  => 'Same presets as the page header. Upload a custom image below to override.',
				),
				array(
					'key'           => 'field_gcc_cta_bg_image',
					'label'         => 'Custom background image',
					'name'          => 'cta_background_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'Optional. Overrides the preset above when set.',
				),
				array(
					'key'           => 'field_gcc_cta_bg_color',
					'label'         => 'Background colour',
					'name'          => 'cta_background_color',
					'type'          => 'color_picker',
					'default_value' => '#673f69',
					'instructions'  => 'Applied to the text panel of the promo card (1280×720 layout).',
				),
				array(
					'key'           => 'field_gcc_youtube_thumbnail',
					'label'         => 'YouTube thumbnail',
					'name'          => 'youtube_thumbnail',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'readonly'      => 1,
					'instructions'  => 'Generated via the YouTube thumbnail box in the sidebar. 1280×720 PNG suitable for YouTube.',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => FTD_COMMUNITY_CALL_POST_TYPE,
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
