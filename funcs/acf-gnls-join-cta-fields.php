<?php
/**
 * ACF: Live Sessions join CTA — Genius Directory Settings subpage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_GNLS_JOIN_CTA_SLUG', 'genius-directory-live-sessions-join-cta' );

add_action( 'acf/init', 'ftd_register_gnls_join_cta_subpage', 12 );
add_action( 'acf/init', 'ftd_register_gnls_join_cta_acf_fields', 16 );
add_action( 'acf/init', 'ftd_cleanup_gnls_join_cta_obsolete_fields', 25 );

/**
 * Register join CTA settings under Genius Directory Settings.
 */
function ftd_register_gnls_join_cta_subpage() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	$parent = function_exists( 'ftd_get_genius_directory_settings_parent_slug' )
		? ftd_get_genius_directory_settings_parent_slug()
		: ( defined( 'FTD_GENIUS_DIRECTORY_SETTINGS_OPTION' ) ? FTD_GENIUS_DIRECTORY_SETTINGS_OPTION : 'genius-directory-settings' );

	$slug = FTD_GNLS_JOIN_CTA_SLUG;

	if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( $slug ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Live Sessions join CTA',
			'menu_title'  => 'Live Sessions join CTA',
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
function ftd_get_gnls_join_cta_post_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids  = array();
	$slug = FTD_GNLS_JOIN_CTA_SLUG;

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

	if ( function_exists( 'ftd_get_genius_directory_settings_post_ids' ) ) {
		$ids = array_merge( $ids, ftd_get_genius_directory_settings_post_ids() );
	}

	$ids[] = $slug;
	$ids[] = 'options';
	$ids[] = 'option';

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Default copy for the join CTA.
 *
 * @return array<string, mixed>
 */
function ftd_get_gnls_join_cta_defaults() {
	return array(
		'eyebrow'        => 'COME AND JOIN US',
		'heading'        => 'All members welcome',
		'subtitle'       => 'your seat is waiting,',
		'body'           => "If you're not a member yet, it's time to join, my friend. Join the Genius Directory — it's free — and your invite for the first Live Session will land in your inbox. That's it. You're in.",
		'button_enabled' => 1,
		'button_label'   => 'Join the directory & get your seat',
		'button_url'     => '',
		'footer_tagline' => "the people you're seeking are seeking you,",
	);
}

/**
 * Canonical ACF options post_id for the join CTA settings page.
 *
 * @return string
 */
function ftd_get_gnls_join_cta_options_post_id() {
	static $post_id = null;

	if ( null !== $post_id ) {
		return $post_id;
	}

	$post_id = FTD_GNLS_JOIN_CTA_SLUG;

	if ( function_exists( 'acf_get_options_page' ) ) {
		$page = acf_get_options_page( FTD_GNLS_JOIN_CTA_SLUG );

		if ( is_array( $page ) && ! empty( $page['post_id'] ) ) {
			$post_id = function_exists( 'acf_get_valid_post_id' )
				? acf_get_valid_post_id( $page['post_id'] )
				: (string) $page['post_id'];
		}
	}

	return $post_id;
}

/**
 * Read the Show button toggle from the join CTA options page.
 *
 * @return bool
 */
function ftd_get_gnls_join_cta_button_enabled() {
	$default = (bool) ftd_get_gnls_join_cta_defaults()['button_enabled'];

	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$post_id = ftd_get_gnls_join_cta_options_post_id();
	$value   = get_field( 'button_enabled', $post_id, false );

	// ACF true_false: 1 = on, 0 = off. false/null means never saved — use default (on).
	if ( false === $value || null === $value ) {
		return $default;
	}

	return (bool) $value;
}

/**
 * Read a join CTA option field.
 *
 * @param string $field Field name.
 * @return mixed
 */
function ftd_get_gnls_join_cta_field( $field ) {
	if ( 'button_enabled' === $field ) {
		return ftd_get_gnls_join_cta_button_enabled();
	}

	$defaults = ftd_get_gnls_join_cta_defaults();

	if ( ! function_exists( 'get_field' ) ) {
		return $defaults[ $field ] ?? null;
	}

	$post_id = ftd_get_gnls_join_cta_options_post_id();
	$value   = get_field( $field, $post_id );

	if ( null !== $value && false !== $value && '' !== $value ) {
		return $value;
	}

	foreach ( ftd_get_gnls_join_cta_post_ids() as $fallback_id ) {
		if ( $fallback_id === $post_id ) {
			continue;
		}

		$value = get_field( $field, $fallback_id );

		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	return $defaults[ $field ] ?? null;
}

/**
 * Build settings array for the join CTA render helper.
 *
 * @param array<string, mixed> $overrides Shortcode attribute overrides.
 * @return array<string, mixed>
 */
function ftd_get_gnls_join_cta_settings( $overrides = array() ) {
	$defaults = ftd_get_gnls_join_cta_defaults();

	$settings = array(
		'eyebrow'        => (string) ftd_get_gnls_join_cta_field( 'eyebrow' ),
		'heading'        => (string) ftd_get_gnls_join_cta_field( 'heading' ),
		'subtitle'       => (string) ftd_get_gnls_join_cta_field( 'subtitle' ),
		'body'           => (string) ftd_get_gnls_join_cta_field( 'body' ),
		'button_enabled' => ftd_get_gnls_join_cta_button_enabled(),
		'button_label'   => (string) ftd_get_gnls_join_cta_field( 'button_label' ),
		'button_url'     => (string) ftd_get_gnls_join_cta_field( 'button_url' ),
		'footer_tagline' => (string) ftd_get_gnls_join_cta_field( 'footer_tagline' ),
	);

	foreach ( $overrides as $key => $value ) {
		if ( null === $value || '' === $value ) {
			continue;
		}

		if ( 'button_enabled' === $key ) {
			$settings[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			continue;
		}

		$settings[ $key ] = (string) $value;
	}

	if ( '' === trim( $settings['button_label'] ) ) {
		$settings['button_label'] = $defaults['button_label'];
	}

	if ( '' === trim( $settings['button_url'] ) ) {
		$settings['button_url'] = function_exists( 'ftd_get_gnls_directory_join_url' )
			? ftd_get_gnls_directory_join_url()
			: home_url( '/join-we-are-geniuses/' );
	} elseif ( function_exists( 'ftd_normalize_banner_link_url' ) ) {
		$settings['button_url'] = ftd_normalize_banner_link_url( $settings['button_url'] );
	} else {
		$settings['button_url'] = esc_url_raw( $settings['button_url'] );
	}

	return $settings;
}

/**
 * Register ACF fields for the join CTA.
 */
function ftd_register_gnls_join_cta_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$defaults = ftd_get_gnls_join_cta_defaults();

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ftd_gnls_join_cta',
			'title'                 => 'Live Sessions join CTA',
			'fields'                => array(
				array(
					'key'     => 'field_ftd_gnjc_intro',
					'label'   => '',
					'name'    => '',
					'type'    => 'message',
					'message' => 'Content for the <code>[gnls_join_cta]</code> shortcode — shown at the bottom of the Genius Directory and Live Sessions archive pages.',
				),
				array(
					'key'           => 'field_ftd_gnjc_eyebrow',
					'label'         => 'Eyebrow label',
					'name'          => 'eyebrow',
					'type'          => 'text',
					'default_value' => $defaults['eyebrow'],
				),
				array(
					'key'           => 'field_ftd_gnjc_heading',
					'label'         => 'Heading',
					'name'          => 'heading',
					'type'          => 'text',
					'default_value' => $defaults['heading'],
				),
				array(
					'key'           => 'field_ftd_gnjc_subtitle',
					'label'         => 'Subtitle (italic)',
					'name'          => 'subtitle',
					'type'          => 'text',
					'default_value' => $defaults['subtitle'],
				),
				array(
					'key'           => 'field_ftd_gnjc_body',
					'label'         => 'Body text',
					'name'          => 'body',
					'type'          => 'textarea',
					'rows'          => 5,
					'new_lines'     => 'br',
					'default_value' => $defaults['body'],
				),
				array(
					'key'           => 'field_ftd_gnjc_button_enabled',
					'label'         => 'Show button',
					'name'          => 'button_enabled',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => $defaults['button_enabled'],
				),
				array(
					'key'           => 'field_ftd_gnjc_button_label',
					'label'         => 'Button label',
					'name'          => 'button_label',
					'type'          => 'text',
					'default_value' => $defaults['button_label'],
				),
				array(
					'key'          => 'field_ftd_gnjc_button_url',
					'label'        => 'Button link',
					'name'         => 'button_url',
					'type'         => 'text',
					'placeholder'  => '/join-we-are-geniuses/',
					'instructions' => 'Optional. Defaults to the directory signup URL.',
				),
				array(
					'key'           => 'field_ftd_gnjc_footer_tagline',
					'label'         => 'Footer tagline (italic)',
					'name'          => 'footer_tagline',
					'type'          => 'text',
					'default_value' => $defaults['footer_tagline'],
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => FTD_GNLS_JOIN_CTA_SLUG,
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

/**
 * Remove deprecated share-link fields still stored in ACF / wp_options.
 *
 * Local PHP no longer registers them, but synced DB field groups can keep showing them.
 */
function ftd_cleanup_gnls_join_cta_obsolete_fields() {
	static $done = false;

	if ( $done ) {
		return;
	}

	$done = true;

	$obsolete_field_keys = array(
		'field_ftd_gnjc_share_enabled',
		'field_ftd_gnjc_share_label',
		'field_ftd_gnjc_share_url',
	);

	if ( function_exists( 'acf_get_field' ) && function_exists( 'acf_delete_field' ) ) {
		foreach ( $obsolete_field_keys as $field_key ) {
			$field = acf_get_field( $field_key );

			if ( is_array( $field ) && ! empty( $field['ID'] ) ) {
				acf_delete_field( (int) $field['ID'] );
			}
		}
	}

	if ( function_exists( 'acf_get_local_field_group' ) && function_exists( 'acf_import_field_group' ) ) {
		$group = acf_get_local_field_group( 'group_ftd_gnls_join_cta' );

		if ( is_array( $group ) ) {
			acf_import_field_group( $group );
		}
	}

	$post_id      = ftd_get_gnls_join_cta_options_post_id();
	$option_names = array( 'share_enabled', 'share_link_label', 'share_link_url' );

	foreach ( $option_names as $name ) {
		delete_option( $post_id . '_' . $name );
		delete_option( '_' . $post_id . '_' . $name );
		delete_option( 'options_' . $post_id . '_' . $name );
		delete_option( '_options_' . $post_id . '_' . $name );
		delete_option( 'options_' . $name );
		delete_option( '_options_' . $name );
	}
}
