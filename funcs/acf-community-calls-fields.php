<?php
/**
 * ACF fields for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ftd_ensure_community_calls_acf_local_field_group', 5 );
add_action( 'acf/init', 'ftd_register_community_calls_acf_fields' );
add_filter( 'acf/load_value/name=profile_members', 'ftd_default_community_call_profile_members', 10, 3 );
add_action( 'acf/save_post', 'ftd_cleanup_legacy_community_call_involved_members', 25 );
add_action( 'acf/save_post', 'ftd_cleanup_obsolete_community_call_promo_fields', 26 );

/**
 * Use the plugin PHP field group only — remove stale DB copies that block saves.
 */
function ftd_ensure_community_calls_acf_local_field_group() {
	if ( ! function_exists( 'acf_get_field_group' ) || ! function_exists( 'acf_delete_field_group' ) ) {
		return;
	}

	$group = acf_get_field_group( 'group_ftd_genius_community_call' );

	if ( empty( $group['ID'] ) || ! empty( $group['local'] ) ) {
		return;
	}

	acf_delete_field_group( (int) $group['ID'] );
}

/**
 * Extract a member user ID from a repeater row (name keys or ACF field keys).
 *
 * @param array<string, mixed> $row Repeater row.
 * @return int
 */
function ftd_get_community_call_profile_member_id_from_row( $row ) {
	if ( ! is_array( $row ) ) {
		return (int) $row;
	}

	return (int) (
		$row['member']
		?? $row['field_gcc_profile_member_user']
		?? $row['ID']
		?? $row['id']
		?? 0
	);
}

/**
 * Extract subtitle text from a repeater row.
 *
 * @param array<string, mixed> $row Repeater row.
 * @return string
 */
function ftd_get_community_call_profile_subtitle_from_row( $row ) {
	if ( ! is_array( $row ) ) {
		return '';
	}

	return trim(
		(string) (
			$row['subtitle']
			?? $row['field_gcc_profile_member_subtitle']
			?? ''
		)
	);
}

/**
 * Extract role label from a repeater row.
 *
 * @param array<string, mixed> $row Repeater row.
 * @return string
 */
function ftd_get_community_call_profile_role_from_row( $row ) {
	if ( ! is_array( $row ) ) {
		return '';
	}

	return trim(
		(string) (
			$row['role_label']
			?? $row['field_gcc_profile_member_role']
			?? ''
		)
	);
}

/**
 * Convert legacy profile_members (user ID list) to repeater rows.
 *
 * @param mixed $value   Stored value.
 * @param int   $post_id Post ID.
 * @param array $field   ACF field.
 * @return mixed
 */
function ftd_normalize_community_call_profile_members( $value, $post_id, $field ) {
	unset( $field, $post_id );

	if ( empty( $value ) || ! is_array( $value ) ) {
		return $value;
	}

	$normalized = array();

	foreach ( $value as $item ) {
		$user_id = ftd_get_community_call_profile_member_id_from_row( $item );

		if ( $user_id <= 0 ) {
			continue;
		}

		$normalized[] = array(
			'member'     => $user_id,
			'subtitle'   => ftd_get_community_call_profile_subtitle_from_row( $item ),
			'role_label' => ftd_get_community_call_profile_role_from_row( $item ),
		);
	}

	return $normalized;
}

/**
 * Remove obsolete promo-card ACF values superseded by the featured image + sidebar generator.
 *
 * @param int|string $post_id Post ID.
 */
function ftd_cleanup_obsolete_community_call_promo_fields( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || FTD_COMMUNITY_CALL_POST_TYPE !== get_post_type( $post_id ) ) {
		return;
	}

	foreach (
		array(
			'cta_background_preset',
			'cta_background_image',
			'cta_background_color',
			'_cta_background_preset',
			'_cta_background_image',
			'_cta_background_color',
		) as $key
	) {
		delete_post_meta( $post_id, $key );
	}
}

/**
 * Remove legacy involved_members once profile_members rows exist.
 *
 * @param int|string $post_id Post ID.
 */
function ftd_cleanup_legacy_community_call_involved_members( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || FTD_COMMUNITY_CALL_POST_TYPE !== get_post_type( $post_id ) ) {
		return;
	}

	$rows = ftd_read_community_call_profile_members_meta( $post_id );

	if ( ! empty( $rows ) ) {
		delete_post_meta( $post_id, 'involved_members' );
	}
}

/**
 * Read profile_members rows directly from post meta when ACF load fails.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{member: int, subtitle: string, role_label: string}>
 */
function ftd_read_community_call_profile_members_meta( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || ! metadata_exists( 'post', $post_id, 'profile_members' ) ) {
		return array();
	}

	$count = (int) get_post_meta( $post_id, 'profile_members', true );

	if ( $count <= 0 ) {
		return array();
	}

	$rows = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$user_id = (int) get_post_meta( $post_id, 'profile_members_' . $i . '_member', true );

		if ( $user_id <= 0 ) {
			$user_id = (int) get_post_meta( $post_id, 'profile_members_' . $i . '_user', true );
		}

		if ( $user_id <= 0 ) {
			continue;
		}

		$rows[] = array(
			'member'     => $user_id,
			'subtitle'   => (string) get_post_meta( $post_id, 'profile_members_' . $i . '_subtitle', true ),
			'role_label' => (string) get_post_meta( $post_id, 'profile_members_' . $i . '_role_label', true ),
		);
	}

	return $rows;
}

/**
 * Default profile members on new calls only.
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

	if ( ! $post instanceof WP_Post || 'auto-draft' !== $post->post_status ) {
		return $value;
	}

	$default_id = ftd_get_naomi_spirit_user_id();

	return $default_id
		? array(
			array(
				'member'     => $default_id,
				'subtitle'   => '',
				'role_label' => '',
			),
		)
		: $value;
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
					'message' => 'Set the <strong>featured image</strong> (sidebar) as the poster background, then click <strong>Generate promo images</strong> in the Promo images box.',
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
					'key'           => 'field_gcc_social_share_message',
					'label'         => 'Social share message',
					'name'          => 'social_share_message',
					'type'          => 'textarea',
					'rows'          => 6,
					'new_lines'     => 'br',
					'instructions'  => 'Message used when sharing this session (WhatsApp, X, email, copy). Placeholders: {title}, {times}, {url}, {image}. Put {url} on its own line so the link is recognised. {image} adds the promo image link on the next line (helps WhatsApp show the poster).',
					'placeholder'   => "Join us for {title} — a Genius Network Live Session with We Are Geniuses.\n{times}\n{url}\n{image}",
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
					'type'          => 'repeater',
					'layout'        => 'block',
					'button_label'  => 'Add member',
					'min'           => 0,
					'max'           => 0,
					'instructions'  => 'Members featured on the promo card and single call page. Pick a member for each row, then add optional subtitle and role.',
					'sub_fields'    => array(
						array(
							'key'           => 'field_gcc_profile_member_user',
							'label'         => 'Member',
							'name'          => 'member',
							'type'          => 'user',
							'role'          => '',
							'allow_null'    => 1,
							'return_format' => 'id',
							'required'      => 0,
						),
						array(
							'key'           => 'field_gcc_profile_member_subtitle',
							'label'         => 'Subtitle',
							'name'          => 'subtitle',
							'type'          => 'text',
							'default_value' => '',
							'placeholder'   => 'We Are Geniuses',
							'instructions'  => 'Optional. Shown under the member name.',
						),
						array(
							'key'           => 'field_gcc_profile_member_role',
							'label'         => 'Role',
							'name'          => 'role_label',
							'type'          => 'text',
							'default_value' => '',
							'placeholder'   => 'Founder · hosting',
							'instructions'  => 'Optional. Shown on the single call page.',
						),
					),
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
