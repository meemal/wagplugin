<?php
/**
 * Shortcode: [gnls_session_cta]
 *
 * Promotional live session CTA block with event details and featured image.
 *
 * Usage:
 *   [gnls_session_cta]
 *   [gnls_session_cta id="123"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'gnls_session_cta', 'ftd_gnls_session_cta_shortcode' );
add_shortcode( 'live_session_cta', 'ftd_gnls_session_cta_shortcode' );

add_action( 'wp_enqueue_scripts', 'ftd_register_gnls_session_cta_assets', 15 );

/**
 * Register live session CTA stylesheet.
 */
function ftd_register_gnls_session_cta_assets() {
	wp_register_style(
		'ftd-sc-gnls-session-cta',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/gnls-session-cta.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
	);
}

/**
 * Resolve session post ID for the CTA shortcode.
 *
 * @param int $post_id Optional explicit post ID.
 * @return int
 */
function ftd_get_gnls_session_cta_post_id( $post_id = 0 ) {
	$post_id = (int) $post_id;

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );

		if ( $post instanceof WP_Post && FTD_COMMUNITY_CALL_POST_TYPE === $post->post_type && 'publish' === $post->post_status ) {
			return $post_id;
		}
	}

	return function_exists( 'ftd_get_featured_live_session_id' ) ? ftd_get_featured_live_session_id() : 0;
}

/**
 * Site domain label for CTA footer.
 *
 * @return string
 */
function ftd_get_gnls_session_cta_site_label() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	return is_string( $host ) ? $host : 'wearegeniuses.com';
}

/**
 * Site URL for CTA footer link.
 *
 * @return string
 */
function ftd_get_gnls_session_cta_site_url() {
	return home_url( '/' );
}

/**
 * We Are Geniuses white stacked logo URL for session promo card header.
 *
 * @return string
 */
function ftd_get_wag_logo_sm_url() {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	$plugin_dir = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE );
	$plugin_url = plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE );

	$svg_white_path = $plugin_dir . 'assets/we-are-geniuses-logo-white.svg';

	if ( file_exists( $svg_white_path ) ) {
		$url = $plugin_url . 'assets/we-are-geniuses-logo-white.svg';
		return $url;
	}

	$svg_path = $plugin_dir . 'assets/we-are-geniuses-logo.svg';

	if ( file_exists( $svg_path ) ) {
		$url = $plugin_url . 'assets/we-are-geniuses-logo.svg';
		return $url;
	}

	$png_path = $plugin_dir . 'assets/we-are-geniuses-logo-sm.png';

	if ( file_exists( $png_path ) ) {
		$url = $plugin_url . 'assets/we-are-geniuses-logo-sm.png';
		return $url;
	}

	$uploads_logo = WP_CONTENT_DIR . '/uploads/2025/05/We-Are-Geniuses-Logosm.png';

	if ( file_exists( $uploads_logo ) ) {
		$url = content_url( 'uploads/2025/05/We-Are-Geniuses-Logosm.png' );
		return $url;
	}

	$url = '';
	return $url;
}

/**
 * Logo markup for the session promo CTA header.
 *
 * @param bool $for_capture Whether this is for html2canvas capture.
 * @return string
 */
function ftd_get_gnls_session_cta_logo_html( $for_capture = false ) {
	if ( $for_capture ) {
		$svg_path = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'assets/we-are-geniuses-logo-white.svg';

		if ( file_exists( $svg_path ) ) {
			$svg      = file_get_contents( $svg_path );
			$data_uri = 'data:image/svg+xml;base64,' . base64_encode( $svg );

			return sprintf(
				'<img class="gnls-cta-logo gnls-cta-logo--stacked" src="%1$s" alt="%2$s" decoding="sync" />',
				esc_attr( $data_uri ),
				esc_attr__( 'We Are Geniuses', 'ftd-directory-listings' )
			);
		}
	}

	$logo_url = ftd_get_wag_logo_sm_url();

	if ( ! $logo_url ) {
		return '';
	}

	return sprintf(
		'<img class="gnls-cta-logo gnls-cta-logo--stacked" src="%1$s" alt="%2$s" width="515" height="374" loading="lazy" decoding="async" />',
		esc_url( $logo_url ),
		esc_attr__( 'We Are Geniuses', 'ftd-directory-listings' )
	);
}

/**
 * Render one host block for poster layouts.
 *
 * @param array<string, mixed> $host        Host data.
 * @param int                  $index       Zero-based index.
 * @param bool                 $for_capture Inline images for html2canvas.
 * @return string
 */
function ftd_render_gnls_session_cta_host_item( $host, $index, $for_capture = false ) {
	$profile_url       = ! empty( $host['url'] ) ? $host['url'] : ftd_get_member_profile_url( $host['id'] );
	$host_subtitle     = trim( (string) ( $host['subtitle'] ?? '' ) );
	$has_custom_avatar = function_exists( 'ftd_user_has_custom_profile_image' ) && ftd_user_has_custom_profile_image( $host['id'] );
	$avatar_url        = function_exists( 'ftd_get_user_profile_image_url' )
		? ftd_get_user_profile_image_url( $host['id'], 144 )
		: ( $host['avatar_url'] ?? '' );

	if ( $for_capture && $has_custom_avatar && $avatar_url && function_exists( 'ftd_get_gnls_capture_image_data_uri' ) ) {
		$avatar_url = ftd_get_gnls_capture_image_data_uri( $avatar_url );

		if ( 0 !== strpos( $avatar_url, 'data:' ) ) {
			$has_custom_avatar = false;
		}
	}

	$initials = ftd_get_member_initials( $host['name'] );
	$tag      = ( $profile_url && ! $for_capture ) ? 'a' : 'div';
	$href     = ( $profile_url && ! $for_capture ) ? ' href="' . esc_url( $profile_url ) . '"' : '';

	ob_start();
	printf(
		'<%1$s class="gnls-cta-host gnls-cta-host--%2$d"%3$s>',
		$tag,
		$index + 1,
		$href
	);
	?>
	<div class="gnls-cta-host-avatar-wrap">
		<?php if ( $has_custom_avatar && $avatar_url ) : ?>
			<img
				class="gnls-cta-host-avatar gnls-cta-host-avatar--photo"
				src="<?php echo $for_capture ? esc_attr( $avatar_url ) : esc_url( $avatar_url ); ?>"
				alt="<?php echo esc_attr( $host['name'] ); ?>"
				width="72"
				height="72"
				<?php echo $for_capture ? 'decoding="sync"' : 'loading="lazy" decoding="async"'; ?>
			/>
		<?php else : ?>
			<span class="gnls-cta-host-avatar gnls-cta-host-avatar--initials"><?php echo esc_html( $initials ); ?></span>
		<?php endif; ?>
	</div>
	<div class="gnls-cta-host-copy">
		<span class="gnls-cta-host-name"><?php echo esc_html( strtoupper( $host['name'] ) ); ?></span>
		<?php if ( $host_subtitle ) : ?>
			<span class="gnls-cta-host-tagline"><?php echo esc_html( $host_subtitle ); ?></span>
		<?php else : ?>
			<span class="gnls-cta-host-tagline gnls-cta-host-tagline--empty" aria-hidden="true"></span>
		<?php endif; ?>
	</div>
	<?php
	printf( '</%s>', $tag );

	return (string) ob_get_clean();
}

/**
 * Full-bleed centred poster layout — front-end, social capture, and YouTube capture.
 *
 * @param int                  $post_id Session post ID.
 * @param array<string, mixed> $atts    Shortcode attributes.
 * @param array<string, mixed> $context Render context.
 * @return string
 */
function ftd_render_gnls_session_cta_poster( $post_id, $atts = array(), $context = array() ) {
	$post_id = (int) $post_id;

	$context = wp_parse_args(
		$context,
		array(
			'for_capture'     => false,
			'capture_variant' => '',
			'link_to_session' => false,
		)
	);

	$for_capture     = ! empty( $context['for_capture'] );
	$capture_variant = (string) $context['capture_variant'];
	$link_to_session = ! empty( $context['link_to_session'] );
	$show_logo       = ! isset( $atts['show_logo'] ) || filter_var( $atts['show_logo'], FILTER_VALIDATE_BOOLEAN );
	$presents        = ! empty( $atts['presents'] ) ? (string) $atts['presents'] : __( 'We Are Geniuses presents...', 'ftd-directory-listings' );
	$logo_html       = $show_logo ? ftd_get_gnls_session_cta_logo_html( $for_capture ) : '';
	$permalink       = get_permalink( $post_id );
	$schedule        = ftd_get_community_call_schedule_parts( $post_id );
	$members         = array_slice( ftd_get_community_call_members( $post_id ), 0, 2 );
	$title_html      = ftd_format_gnls_session_cta_title( get_the_title( $post_id ) );
	$tagline         = $schedule['subtitle'];
	$kicker          = function_exists( 'ftd_get_community_call_single_kicker' )
		? ftd_get_community_call_single_kicker( $post_id )
		: '';
	$promo_times     = function_exists( 'ftd_get_community_call_promo_times' )
		? ftd_get_community_call_promo_times( $post_id )
		: '';
	$bg_color        = '#673f69';
	$bg_src          = '';

	if ( function_exists( 'ftd_get_session_cta_background_config' ) ) {
		$bg_color = ftd_get_session_cta_background_config( $post_id )['color'];
	}

	$attachment_id = ftd_get_community_call_feature_image_id( $post_id );

	if ( $attachment_id > 0 ) {
		if ( $for_capture ) {
			$img_url = wp_get_attachment_image_url( $attachment_id, 'full' );
			$bg_src  = function_exists( 'ftd_get_gnls_capture_image_data_uri' )
				? ftd_get_gnls_capture_image_data_uri( $img_url )
				: $img_url;

			if ( 0 !== strpos( (string) $bg_src, 'data:' ) && $img_url ) {
				$bg_src = $img_url;
			}
		} else {
			$bg_src = wp_get_attachment_image_url( $attachment_id, 'large' );
		}
	}

	$card_classes = array( 'ftd-sc', 'ftd-sc--gnls-session-cta', 'gnls-session-cta', 'gnls-cta--poster' );

	if ( 'social' === $capture_variant ) {
		$card_classes[] = 'gnls-cta--capture-social';
	} elseif ( 'youtube' === $capture_variant ) {
		$card_classes[] = 'gnls-cta--capture-youtube';
	}

	if ( $link_to_session && $permalink ) {
		$card_classes[] = 'gnls-cta--linked';
	}

	$site_tag  = $for_capture ? 'span' : 'a';
	$site_href = $for_capture ? '' : ' href="' . esc_url( ftd_get_gnls_session_cta_site_url() ) . '"';

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>" style="--gnls-cta-bg: <?php echo esc_attr( $bg_color ); ?>;">
		<?php if ( $link_to_session && $permalink ) : ?>
			<a class="gnls-cta-card-link" href="<?php echo esc_url( $permalink ); ?>">
				<span class="screen-reader-text">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: session title */
							__( 'View session: %s', 'ftd-directory-listings' ),
							get_the_title( $post_id )
						)
					);
					?>
				</span>
			</a>
		<?php endif; ?>

		<?php if ( $bg_src ) : ?>
			<img class="gnls-cta-poster-bg" src="<?php echo $for_capture ? esc_attr( $bg_src ) : esc_url( $bg_src ); ?>" alt="" <?php echo $for_capture ? 'decoding="sync"' : 'loading="lazy" decoding="async"'; ?> />
		<?php endif; ?>
		<div class="gnls-cta-poster-overlay" aria-hidden="true"></div>

		<div class="gnls-cta-poster-inner">
			<header class="gnls-cta-poster-header">
				<?php if ( $logo_html ) : ?>
					<div class="gnls-cta-brand"><?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<?php if ( $presents ) : ?>
					<p class="gnls-cta-presents"><?php echo esc_html( $presents ); ?></p>
				<?php endif; ?>
				<?php if ( $kicker ) : ?>
					<p class="gnls-cta-series gnls-cta-series--poster"><?php echo esc_html( $kicker ); ?></p>
				<?php endif; ?>
			</header>

			<div class="gnls-cta-poster-main">
				<?php if ( $title_html ) : ?>
					<h2 class="gnls-cta-title"><?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<?php endif; ?>
				<?php if ( $tagline ) : ?>
					<p class="gnls-cta-tagline"><?php echo esc_html( $tagline ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $members ) ) : ?>
				<div class="gnls-cta-hosts gnls-cta-hosts--poster">
					<?php
					foreach ( $members as $index => $host ) {
						echo ftd_render_gnls_session_cta_host_item( $host, $index, $for_capture ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $schedule['day_name'] || $schedule['day_num'] || $schedule['month_name'] || $promo_times ) : ?>
			<div class="gnls-cta-poster-date" aria-hidden="true">
				<?php if ( $schedule['day_name'] ) : ?>
					<span class="gnls-cta-day"><?php echo esc_html( $schedule['day_name'] ); ?></span>
				<?php endif; ?>
				<?php if ( $schedule['day_num'] || $schedule['month_name'] ) : ?>
					<p class="gnls-cta-date-block">
						<?php if ( $schedule['day_num'] ) : ?>
							<span class="gnls-cta-date-num"><?php echo esc_html( ltrim( $schedule['day_num'], '0' ) ); ?></span>
						<?php endif; ?>
						<?php if ( $schedule['month_name'] ) : ?>
							<span class="gnls-cta-date-month"><?php echo esc_html( $schedule['month_name'] ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<?php if ( $promo_times ) : ?>
					<p class="gnls-cta-times"><?php echo esc_html( $promo_times ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="gnls-cta-poster-site" aria-hidden="true">
			<span class="gnls-cta-poster-more"><?php esc_html_e( 'FIND OUT MORE', 'ftd-directory-listings' ); ?></span>
			<?php
			printf(
				'<%1$s class="gnls-cta-site"%2$s>%3$s</%1$s>',
				$site_tag,
				$site_href,
				esc_html( ftd_get_gnls_session_cta_site_label() )
			);
			?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string
 */
function ftd_gnls_session_cta_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'              => 0,
			'show_logo'       => '1',
			'presents'        => '',
			'link_to_session' => '',
		),
		$atts,
		'gnls_session_cta'
	);

	$post_id = ftd_get_gnls_session_cta_post_id( (int) $atts['id'] );

	if ( $post_id <= 0 ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-gnls-session-cta' );

	return ftd_render_gnls_session_cta( $post_id, $atts );
}

/**
 * Render the live session promo CTA.
 *
 * @param int                  $post_id Session post ID.
 * @param array<string, mixed> $atts    Shortcode attributes.
 * @return string
 */
function ftd_render_gnls_session_cta( $post_id, $atts = array() ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return '';
	}

	$capture_mode    = isset( $atts['capture'] ) ? (string) $atts['capture'] : '';
	$capture_variant = '';

	if ( 'social' === $capture_mode ) {
		$capture_variant = 'social';
	} elseif ( in_array( $capture_mode, array( '1', 'youtube', 'true' ), true ) ) {
		$capture_variant = 'youtube';
	}

	$link_to_session = ! empty( $atts['link_to_session'] ) && filter_var( $atts['link_to_session'], FILTER_VALIDATE_BOOLEAN );

	return ftd_render_gnls_session_cta_poster(
		$post_id,
		$atts,
		array(
			'for_capture'     => '' !== $capture_variant,
			'capture_variant' => $capture_variant,
			'link_to_session' => $link_to_session,
		)
	);
}

/**
 * Back-compat alias for social capture renderer.
 *
 * @param int                  $post_id Session post ID.
 * @param array<string, mixed> $atts    Shortcode attributes.
 * @return string
 */
function ftd_render_gnls_session_cta_social_capture( $post_id, $atts = array() ) {
	return ftd_render_gnls_session_cta_poster(
		$post_id,
		$atts,
		array(
			'for_capture'     => true,
			'capture_variant' => 'social',
			'link_to_session' => false,
		)
	);
}
