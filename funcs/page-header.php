<?php
/**
 * Standardised full-width page header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_PAGE_HEADER_TEMPLATE', 'ftd-page-with-header.php' );

add_filter( 'theme_page_templates', 'ftd_register_page_header_template' );
add_filter( 'template_include', 'ftd_page_header_template_include', 98 );
add_filter( 'body_class', 'ftd_page_header_body_class' );
add_action( 'wp_enqueue_scripts', 'ftd_enqueue_page_header_assets', 24 );

/**
 * Bundled page header background presets (assets/page-header/).
 *
 * @return array<string, array{label: string, file: string}>
 */
function ftd_get_page_header_background_presets() {
	return array(
		'constellation' => array(
			'label' => __( 'Constellation', 'ftd-directory-listings' ),
			'file'  => 'header-01-constellation.png',
		),
		'sunset'        => array(
			'label' => __( 'Sunset', 'ftd-directory-listings' ),
			'file'  => 'header-02-sunset.png',
		),
		'aurora'        => array(
			'label' => __( 'Aurora', 'ftd-directory-listings' ),
			'file'  => 'header-03-aurora.png',
		),
		'geometry'      => array(
			'label' => __( 'Geometry', 'ftd-directory-listings' ),
			'file'  => 'header-04-geometry.png',
		),
		'heartbloom'    => array(
			'label' => __( 'Heart bloom', 'ftd-directory-listings' ),
			'file'  => 'header-05-heartbloom.png',
		),
		'starfield'     => array(
			'label' => __( 'Starfield', 'ftd-directory-listings' ),
			'file'  => 'header-06-starfield.png',
		),
		'sunrise'       => array(
			'label' => __( 'Sunrise', 'ftd-directory-listings' ),
			'file'  => 'header-07-sunrise.png',
		),
	);
}

/**
 * ACF select choices for background presets.
 *
 * @return array<string, string>
 */
function ftd_get_page_header_background_preset_choices() {
	$choices = array(
		'' => __( 'None (colour only)', 'ftd-directory-listings' ),
	);

	foreach ( ftd_get_page_header_background_presets() as $key => $preset ) {
		$choices[ $key ] = $preset['label'];
	}

	return $choices;
}

/**
 * Public URL for a bundled background preset.
 *
 * @param string $preset_key Preset key.
 * @return string
 */
function ftd_get_page_header_background_preset_url( $preset_key ) {
	$presets = ftd_get_page_header_background_presets();
	$preset_key = sanitize_key( (string) $preset_key );

	if ( ! isset( $presets[ $preset_key ] ) ) {
		return '';
	}

	$file = $presets[ $preset_key ]['file'];
	$path = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'assets/page-header/' . $file;

	if ( ! file_exists( $path ) ) {
		return '';
	}

	return plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'assets/page-header/' . $file;
}

/**
 * Resolve background image URL: custom upload overrides preset.
 *
 * @param int|string|null $source Post ID or options slug.
 * @return string
 */
function ftd_resolve_page_header_background_image( $source = null ) {
	$upload = ftd_page_header_get_field( 'page_header_background_image', $source );

	if ( is_array( $upload ) && ! empty( $upload['url'] ) ) {
		$upload = (string) $upload['url'];
	}

	$upload = is_string( $upload ) ? trim( $upload ) : '';

	if ( '' !== $upload ) {
		return $upload;
	}

	$preset = trim( (string) ftd_page_header_get_field( 'page_header_background_preset', $source ) );

	if ( '' === $preset ) {
		return '';
	}

	return ftd_get_page_header_background_preset_url( $preset );
}

/**
 * Default archive header copy (Genius Directory Settings → Live Sessions header).
 *
 * @return array<string, string>
 */
function ftd_get_page_header_archive_default_config() {
	return array(
		'page_header_eyebrow'                => 'GENIUS NETWORK - LIVE SESSIONS',
		'page_header_title'                  => 'Rising',
		'page_header_title_accent'           => 'together',
		'page_header_subtitle'               => "the people you're seeking are seeking you,",
		'page_header_description_override'   => "You've found the directory. Now come and meet the people in it. The Live Sessions are regular calls where we actually come together — not just names on a map, but real faces, real conversations. A chance to connect with other advanced students who get it.",
		'page_header_background_color'       => '#673f69',
		'page_header_background_preset'      => 'constellation',
		'page_header_cta_enabled'            => 1,
		'page_header_cta_label'              => 'Join the directory',
		'page_header_cta_url'                => '/join-we-are-geniuses/',
		'featured_section_title'             => 'A new beginning',
		'featured_section_subtitle'          => 'doing the work at work,',
		'featured_cta_label'                 => 'Save my seat',
	);
}

/**
 * Register plugin page template in the admin picker.
 *
 * @param array<string, string> $templates Templates.
 * @return array<string, string>
 */
function ftd_register_page_header_template( $templates ) {
	$templates[ FTD_PAGE_HEADER_TEMPLATE ] = __( 'WAG Page with Header', 'ftd-directory-listings' );

	return $templates;
}

/**
 * Load the plugin page template when selected or when header is enabled.
 *
 * @param string $template Current template path.
 * @return string
 */
function ftd_page_header_template_include( $template ) {
	if ( ! is_page() ) {
		return $template;
	}

	$page_id = get_queried_object_id();

	if ( ! $page_id ) {
		return $template;
	}

	$selected = get_page_template_slug( $page_id );

	if ( FTD_PAGE_HEADER_TEMPLATE === $selected || ftd_page_header_is_enabled( $page_id ) ) {
		$plugin_template = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'templates/page-with-header.php';

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}

/**
 * @param int $post_id Page ID.
 * @return bool
 */
function ftd_page_header_is_enabled( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_queried_object_id();

	if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	return (bool) ftd_page_header_get_field( 'page_header_enabled', $post_id );
}

/**
 * Resolve ACF source ID for the current header context.
 *
 * @return int|string|null
 */
function ftd_page_header_get_source_id() {
	if ( is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return FTD_PAGE_HEADER_ARCHIVE_OPTION;
	}

	if ( is_page() ) {
		return get_queried_object_id();
	}

	return null;
}

/**
 * Resolve a logical page header field name to its group sub-field key.
 *
 * @param string $field Logical or legacy field name.
 * @return string
 */
function ftd_page_header_resolve_sub_field_key( $field ) {
	$map = function_exists( 'ftd_page_header_field_key_map' ) ? ftd_page_header_field_key_map() : array();

	if ( isset( $map[ $field ] ) ) {
		return $map[ $field ];
	}

	if ( 0 === strpos( $field, 'page_header_' ) ) {
		return substr( $field, strlen( 'page_header_' ) );
	}

	return $field;
}

/**
 * Whether an ACF value should be treated as empty for page header fields.
 *
 * @param mixed  $value     Field value.
 * @param string $sub_field Group sub-field key.
 * @return bool
 */
function ftd_page_header_field_is_empty( $value, $sub_field ) {
	if ( 'cta_enabled' === $sub_field ) {
		return null === $value || false === $value;
	}

	return null === $value || false === $value || '' === $value;
}

/**
 * Read a page header field from the nested ACF group, with legacy fallbacks.
 *
 * @param string        $sub_field Group sub-field key.
 * @param int|string    $source    Post ID or options slug.
 * @return mixed|null
 */
function ftd_page_header_get_group_field( $sub_field, $source ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$group_name = defined( 'FTD_PAGE_HEADER_GROUP_NAME' ) ? FTD_PAGE_HEADER_GROUP_NAME : 'page_header';
	$group      = get_field( $group_name, $source );

	if ( is_array( $group ) && array_key_exists( $sub_field, $group ) ) {
		$value = $group[ $sub_field ];

		if ( 'cta_enabled' === $sub_field ) {
			return (bool) $value;
		}

		if ( ! ftd_page_header_field_is_empty( $value, $sub_field ) ) {
			return $value;
		}
	}

	$legacy_keys = array(
		'page_header_' . $sub_field,
		$group_name . '_' . $sub_field,
	);

	foreach ( $legacy_keys as $legacy_key ) {
		$value = get_field( $legacy_key, $source );

		if ( 'cta_enabled' === $sub_field && null !== $value && false !== $value ) {
			return (bool) $value;
		}

		if ( ! ftd_page_header_field_is_empty( $value, $sub_field ) ) {
			return $value;
		}
	}

	return null;
}

/**
 * Read a page header ACF/meta field for a source.
 *
 * @param string          $field  Field name.
 * @param int|string|null $source Post ID or options slug.
 * @return mixed
 */
function ftd_page_header_get_field( $field, $source = null ) {
	if ( null === $source ) {
		$source = ftd_page_header_get_source_id();
	}

	if ( null === $source ) {
		return null;
	}

	$sub_field         = ftd_page_header_resolve_sub_field_key( $field );
	$is_archive_source = FTD_PAGE_HEADER_ARCHIVE_OPTION === $source
		|| ( function_exists( 'ftd_get_page_header_archive_post_ids' ) && in_array( $source, ftd_get_page_header_archive_post_ids(), true ) );

	if ( ! function_exists( 'get_field' ) ) {
		if ( $is_archive_source ) {
			$defaults = ftd_get_page_header_archive_default_config();

			return $defaults[ $field ] ?? null;
		}

		return null;
	}

	if ( $is_archive_source ) {
		foreach ( ftd_get_page_header_archive_post_ids() as $post_id ) {
			$value = ftd_page_header_get_group_field( $sub_field, $post_id );

			if ( null !== $value ) {
				return $value;
			}

			$value = get_field( $field, $post_id );

			if ( ! ftd_page_header_field_is_empty( $value, $sub_field ) ) {
				return $value;
			}
		}

		$defaults = ftd_get_page_header_archive_default_config();

		return $defaults[ $field ] ?? null;
	}

	$value = ftd_page_header_get_group_field( $sub_field, $source );

	if ( null !== $value ) {
		return $value;
	}

	$value = get_field( $field, $source );

	if ( ! ftd_page_header_field_is_empty( $value, $sub_field ) ) {
		return $value;
	}

	return null;
}

/**
 * SEO meta description for a page or archive context.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_page_meta_description( $post_id = 0 ) {
	if ( is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) && ! $post_id ) {
		return __( 'Genius Network Live Sessions for We Are Geniuses members — connect, share, and bring the work into your work.', 'ftd-directory-listings' );
	}

	$post_id = $post_id ? (int) $post_id : get_queried_object_id();

	if ( ! $post_id ) {
		return '';
	}

	$meta_keys = array(
		'_yoast_wpseo_metadesc',
		'rank_math_description',
		'_aioseo_description',
		'_genesis_description',
	);

	foreach ( $meta_keys as $key ) {
		$value = get_post_meta( $post_id, $key, true );

		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return trim( $value );
		}
	}

	$post = get_post( $post_id );

	if ( $post instanceof WP_Post && '' !== trim( (string) $post->post_excerpt ) ) {
		return trim( $post->post_excerpt );
	}

	return '';
}

/**
 * Build header config for the current context.
 *
 * @param int|string|null $source Optional source override.
 * @return array<string, string>|null
 */
function ftd_get_page_header_config( $source = null ) {
	$source = null !== $source ? $source : ftd_page_header_get_source_id();

	if ( null === $source ) {
		return null;
	}

	if ( is_numeric( $source ) && ! ftd_page_header_is_enabled( (int) $source ) ) {
		return null;
	}

	$eyebrow      = trim( (string) ftd_page_header_get_field( 'page_header_eyebrow', $source ) );
	$title        = trim( (string) ftd_page_header_get_field( 'page_header_title', $source ) );
	$title_accent = trim( (string) ftd_page_header_get_field( 'page_header_title_accent', $source ) );
	$subtitle     = trim( (string) ftd_page_header_get_field( 'page_header_subtitle', $source ) );
	$override     = ftd_page_header_get_field( 'page_header_description_override', $source );
	$bg_color     = trim( (string) ftd_page_header_get_field( 'page_header_background_color', $source ) );
	$bg_image     = ftd_resolve_page_header_background_image( $source );
	$cta_enabled_value = ftd_page_header_get_field( 'page_header_cta_enabled', $source );
	$cta_label           = trim( (string) ftd_page_header_get_field( 'page_header_cta_label', $source ) );
	$cta_url             = trim( (string) ftd_page_header_get_field( 'page_header_cta_url', $source ) );
	$cta_enabled         = null === $cta_enabled_value ? ( '' !== $cta_label ) : (bool) $cta_enabled_value;

	if ( '' === $title && '' === $title_accent && '' === $eyebrow ) {
		return null;
	}

	$description = is_string( $override ) ? trim( $override ) : '';

	if ( '' === $description ) {
		$post_id_for_meta = is_numeric( $source ) ? (int) $source : 0;
		$description      = ftd_get_page_meta_description( $post_id_for_meta );
	}

	if ( '' === $bg_color ) {
		$bg_color = '#673f69';
	}

	if ( $cta_enabled ) {
		if ( '' === $cta_url ) {
			$cta_url = function_exists( 'ftd_get_gnls_directory_join_url' )
				? ftd_get_gnls_directory_join_url()
				: home_url( '/join-we-are-geniuses/' );
		} elseif ( function_exists( 'ftd_normalize_banner_link_url' ) ) {
			$cta_url = ftd_normalize_banner_link_url( $cta_url );
		} else {
			$cta_url = esc_url_raw( $cta_url );
		}
	} else {
		$cta_label = '';
		$cta_url   = '';
	}

	return array(
		'eyebrow'      => $eyebrow,
		'title'        => $title,
		'title_accent' => $title_accent,
		'subtitle'     => $subtitle,
		'description'  => $description,
		'bg_image'     => $bg_image,
		'bg_color'     => $bg_color,
		'cta_enabled'  => $cta_enabled,
		'cta_label'    => $cta_label,
		'cta_url'      => $cta_url,
	);
}

/**
 * Add body class when the standard page header is active.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ftd_page_header_body_class( $classes ) {
	if ( ftd_should_render_page_header() ) {
		$classes[] = 'ftd-has-page-header';
	}

	return $classes;
}

/**
 * Whether the header should render on the current request.
 *
 * @return bool
 */
function ftd_should_render_page_header() {
	if ( is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) ) {
		return null !== ftd_get_page_header_config( FTD_PAGE_HEADER_ARCHIVE_OPTION );
	}

	if ( is_page() ) {
		return null !== ftd_get_page_header_config( get_queried_object_id() );
	}

	return false;
}

/**
 * Enqueue page header styles when needed.
 */
function ftd_enqueue_page_header_assets() {
	if ( ! ftd_should_render_page_header() ) {
		return;
	}

	wp_enqueue_style(
		'ftd-page-header',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/page-header.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);
}

/**
 * Render the standardised page header.
 *
 * @param int|string|null $source Optional source override.
 * @return string
 */
function ftd_render_page_header( $source = null ) {
	$config = ftd_get_page_header_config( $source );

	if ( null === $config ) {
		return '';
	}

	ftd_enqueue_page_header_assets();

	$style_parts = array(
		'background-color:' . esc_attr( $config['bg_color'] ),
	);

	if ( $config['bg_image'] ) {
		$style_parts[] = 'background-image:url(' . esc_url( $config['bg_image'] ) . ')';
	}

	$style_attr = implode( ';', $style_parts );

	ob_start();
	?>
	<section class="ftd-sc ftd-sc--page-header ftd-page-header" style="<?php echo esc_attr( $style_attr ); ?>">
		<div class="ftd-page-header-inner">
			<?php if ( $config['eyebrow'] ) : ?>
				<p class="ftd-page-header-eyebrow"><?php echo esc_html( $config['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( $config['title'] || $config['title_accent'] ) : ?>
				<h1 class="ftd-page-header-title">
					<?php if ( $config['title'] ) : ?>
						<span class="ftd-page-header-title-main"><?php echo esc_html( $config['title'] ); ?></span>
					<?php endif; ?>
					<?php if ( $config['title_accent'] ) : ?>
						<span class="ftd-page-header-title-accent"><?php echo esc_html( $config['title_accent'] ); ?></span>
					<?php endif; ?>
				</h1>
			<?php endif; ?>

			<?php if ( $config['subtitle'] ) : ?>
				<p class="ftd-page-header-subtitle"><?php echo esc_html( $config['subtitle'] ); ?></p>
			<?php endif; ?>

			<?php if ( $config['description'] ) : ?>
				<div class="ftd-page-header-description">
					<?php echo wp_kses_post( wpautop( $config['description'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $config['cta_enabled'] ) && $config['cta_label'] && $config['cta_url'] ) : ?>
				<p class="ftd-page-header-cta-wrap">
					<a class="btn ftd-page-header-cta" href="<?php echo esc_url( $config['cta_url'] ); ?>">
						<span class="ftd-page-header-cta-icon" aria-hidden="true">▶</span>
						<span><?php echo esc_html( $config['cta_label'] ); ?></span>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Echo the page header for templates.
 *
 * @param int|string|null $source Optional source override.
 * @return void
 */
function ftd_the_page_header( $source = null ) {
	echo ftd_render_page_header( $source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
}
