<?php
/**
 * ACF: Social share tool — Genius Directory Settings → Social sharing subpage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GENIUS_DIRECTORY_SOCIAL_SHARE_SLUG', 'genius-directory-social-share' );

add_action( 'acf/init', 'ftd_register_genius_directory_social_share_subpage', 12 );
add_action( 'acf/init', 'ftd_register_social_share_acf_fields', 16 );

/**
 * @return string
 */
function ftd_get_genius_directory_social_share_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids  = array( FTD_GENIUS_DIRECTORY_SOCIAL_SHARE_SLUG );
	$slug = FTD_GENIUS_DIRECTORY_SOCIAL_SHARE_SLUG;

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

	$ids[] = 'options';
	$ids[] = 'option';

	$ids = array_values( array_unique( array_filter( $ids ) ) );

	return $ids;
}

/**
 * Normalize a social share repeater row from ACF.
 *
 * @param array<string, mixed> $row Repeater row.
 * @return array<string, string>|null
 */
function ftd_parse_social_share_message_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}

	$text = trim( (string) ( $row['message'] ?? $row['field_ftd_ss_msg_text'] ?? '' ) );
	if ( '' === $text ) {
		return null;
	}

	$label = trim( (string) ( $row['label'] ?? $row['field_ftd_ss_msg_label'] ?? '' ) );

	return array(
		'label'   => $label,
		'message' => $text,
	);
}

/**
 * @param array<int, array<string, mixed>> $rows Repeater rows.
 * @return array<int, array<string, string>>
 */
function ftd_parse_social_share_message_rows( $rows ) {
	$messages = array();

	if ( ! is_array( $rows ) ) {
		return $messages;
	}

	foreach ( $rows as $row ) {
		$parsed = ftd_parse_social_share_message_row( $row );
		if ( null !== $parsed ) {
			$messages[] = $parsed;
		}
	}

	return $messages;
}

/**
 * @param string $field Field name.
 * @return mixed
 */
function ftd_genius_directory_social_share_get_field( $field ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	foreach ( ftd_get_genius_directory_social_share_post_ids() as $post_id ) {
		$value = get_field( $field, $post_id );

		if ( false === $value || null === $value ) {
			continue;
		}

		if ( 'social_share_messages' === $field ) {
			$messages = ftd_parse_social_share_message_rows( $value );
			if ( ! empty( $messages ) ) {
				return $messages;
			}
			continue;
		}

		if ( is_array( $value ) ) {
			if ( ! empty( $value ) ) {
				return $value;
			}
			continue;
		}

		if ( '' !== trim( (string) $value ) ) {
			return $value;
		}
	}

	return null;
}

/**
 * Pre-fill empty repeater in admin and on read.
 *
 * @param mixed  $value   Stored value.
 * @param mixed  $post_id Options post id.
 * @param array  $field   Field config.
 * @return mixed
 */
function ftd_acf_load_social_share_messages( $value, $post_id, $field ) {
	if ( ! empty( $value ) && is_array( $value ) ) {
		$parsed = ftd_parse_social_share_message_rows( $value );
		if ( ! empty( $parsed ) ) {
			return $value;
		}
	}

	return ftd_get_social_share_default_messages();
}

add_filter( 'acf/load_value/name=social_share_messages', 'ftd_acf_load_social_share_messages', 10, 3 );

/**
 * Register Social sharing subpage.
 */
function ftd_register_genius_directory_social_share_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	if ( ! function_exists( 'ftd_get_genius_directory_settings_parent_slug' ) ) {
		return;
	}

	$parent = ftd_get_genius_directory_settings_parent_slug();
	$slug   = FTD_GENIUS_DIRECTORY_SOCIAL_SHARE_SLUG;

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Social sharing',
			'menu_title'  => 'Social sharing',
			'menu_slug'   => $slug,
			'parent_slug' => $parent,
			'post_id'     => $slug,
			'capability'  => 'manage_options',
		)
	);
}

/**
 * Default share message options.
 *
 * @return array<int, array<string, string>>
 */
function ftd_get_social_share_default_messages() {
	return array(
		array(
			'label'   => 'Option 1',
			'message' => "I've recently joined We Are Geniuses, and I wanted to share it with you. It's a networking space for advanced Joe Dispenza students — somewhere you can find a genius accountant, doctor, or business coach, all within a community that just gets it. Ever wished you could talk with like-minded people about how they bring \"the work\" into their work? That's exactly what this is.\nIf you'd like to join me, right now you can claim a free membership for life with code originalgenius111 at checkout — that includes your spot on the Genius Map, a personal profile, business listings, and access to community calls.\n🔗 wearegeniuses.com\nThe people you're seeking are seeking you. 🤍",
		),
		array(
			'label'   => 'Option 2',
			'message' => "I've found something I think you'd love. It's called We Are Geniuses — a networking space just for advanced Joe Dispenza students. Finally, somewhere to connect with like-minded people about how they bring \"the work\" into their work, and to find genius professionals you can actually trust.\nI've joined, and right now there's a free membership for life with code originalgenius111 at checkout.\nCome find me on there 🔗 wearegeniuses.com 🤍",
		),
		array(
			'label'   => 'Option 3',
			'message' => "You know how hard it can be to find people who truly understand the way we see things? I joined We Are Geniuses for exactly that reason — a networking space for advanced Joe Dispenza students, where you can find genius accountants, doctors, coaches, and more, and talk with like-minded people about how they bring \"the work\" into their work.\nIt comes with your spot on the Genius Map, a personal profile, business listings, and access to community calls where we lift each other up — professionally and personally.\nRight now it's a free membership for life with code originalgenius111 at checkout.\n🔗 wearegeniuses.com — hope to see you there. 🤍",
		),
		array(
			'label'   => 'Option 4',
			'message' => "I'm on We Are Geniuses — a networking space for advanced Joe Dispenza students — and I'd love for you to join me. Find genius professionals you can trust, connect with like-minded people, and come along to community calls where we network and grow together.\nFree membership for life right now with code originalgenius111 at checkout.\n🔗 wearegeniuses.com 🤍",
		),
	);
}

/**
 * Register social share ACF fields.
 */
function ftd_register_social_share_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_social_share',
			'title'                 => 'Social share messages',
			'fields'                => array(
				array(
					'key'           => 'field_ftd_ss_intro',
					'label'         => 'Introduction',
					'name'          => '',
					'type'          => 'message',
					'message'       => 'Edit the tab labels and message text for [wag_social_share]. Use wearegeniuses.com in the copy — it is replaced on the front end with each member\'s affiliate link.<br><br><strong>MailPoet emails:</strong> open the personalization picker → <strong>We Are Geniuses → Affiliate link</strong> (new editor), or type <code>[wag:affiliate_link]</code> (classic editor). The subscriber email must match their WordPress login email, and they need a PMPro affiliate code.',
				),
				array(
					'key'           => 'field_ftd_ss_heading',
					'label'         => 'Heading',
					'name'          => 'social_share_heading',
					'type'          => 'text',
					'default_value' => 'Share the gathering',
				),
				array(
					'key'           => 'field_ftd_ss_subtitle',
					'label'         => 'Subtitle',
					'name'          => 'social_share_subtitle',
					'type'          => 'text',
					'default_value' => 'pick a voice, pass it on,',
				),
				array(
					'key'           => 'field_ftd_ss_voices_label',
					'label'         => 'Voices section label',
					'name'          => 'social_share_voices_label',
					'type'          => 'text',
					'default_value' => 'PICK A VOICE',
				),
				array(
					'key'     => 'field_ftd_ss_link_base_note',
					'label'   => 'Affiliate link',
					'name'    => '',
					'type'    => 'message',
					'message' => 'Join links are built as <code>https://wearegeniuses.com/join-we-are-geniuses/?pa=MEMBER_CODE</code> (hardcoded). Each member\'s PMPro affiliate code is appended automatically on the front end and in MailPoet.',
				),
				array(
					'key'          => 'field_ftd_ss_messages',
					'label'        => 'Share message options',
					'name'         => 'social_share_messages',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add message option',
					'min'          => 1,
					'max'          => 12,
					'sub_fields'   => array(
						array(
							'key'           => 'field_ftd_ss_msg_label',
							'label'         => 'Tab label',
							'name'          => 'label',
							'type'          => 'text',
							'instructions'  => 'Shown on the voice tab (e.g. The Invitation, Option 1).',
							'placeholder'   => 'Option 1',
						),
						array(
							'key'          => 'field_ftd_ss_msg_text',
							'label'        => 'Message text',
							'name'         => 'message',
							'type'         => 'textarea',
							'rows'         => 12,
							'new_lines'    => 'br',
							'instructions' => 'Full share copy. Include wearegeniuses.com where you want the member affiliate link inserted.',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => FTD_GENIUS_DIRECTORY_SOCIAL_SHARE_SLUG,
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
