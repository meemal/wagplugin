<?php
/**
 * Shortcode: [gnls_session_cta]
 *
 * Promotional live session CTA block with event details and featured image.
 *
 * Usage:
 *   [gnls_session_cta]
 *   [gnls_session_cta id="123"]
 *   [gnls_session_cta button="Save my seat"]
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
		FTD_DIRECTORY_LISTINGS_VERSION
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
 * We Are Geniuses stacked logo URL for session promo card header.
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
 * @return string
 */
function ftd_get_gnls_session_cta_logo_html() {
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
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string
 */
function ftd_gnls_session_cta_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'           => 0,
			'button'       => '',
			'button_url'   => '',
			'show_button'  => '1',
			'show_logo'    => '1',
			'footnote'     => 'your seat is waiting,',
			'presents'     => '',
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

	$show_button = true;
	if ( isset( $atts['show_button'] ) && ! filter_var( $atts['show_button'], FILTER_VALIDATE_BOOLEAN ) ) {
		$show_button = false;
	}

	$link_to_session = ! empty( $atts['link_to_session'] ) && filter_var( $atts['link_to_session'], FILTER_VALIDATE_BOOLEAN );
	$show_logo       = ! isset( $atts['show_logo'] ) || filter_var( $atts['show_logo'], FILTER_VALIDATE_BOOLEAN );
	$logo_html       = $show_logo ? ftd_get_gnls_session_cta_logo_html() : '';
	$presents        = ! empty( $atts['presents'] ) ? (string) $atts['presents'] : '';

	$schedule   = ftd_get_community_call_schedule_parts( $post_id );
	$members    = ftd_get_community_call_members( $post_id );
	$permalink  = get_permalink( $post_id );
	$settings   = function_exists( 'ftd_get_gnls_featured_panel_settings' ) ? ftd_get_gnls_featured_panel_settings() : array();
	$button     = ! empty( $atts['button'] ) ? (string) $atts['button'] : ( $settings['cta_label'] ?? __( 'Save my seat', 'ftd-directory-listings' ) );
	$button_url = ! empty( $atts['button_url'] ) ? (string) $atts['button_url'] : (string) $permalink;
	$footnote   = ! empty( $atts['footnote'] ) ? (string) $atts['footnote'] : __( 'your seat is waiting,', 'ftd-directory-listings' );
	$session_no = ftd_get_live_session_number( $post_id );
	$series     = ftd_gnls_label_singular();

	if ( $session_no > 0 ) {
		$series = sprintf(
			/* translators: 1: session label, 2: zero-padded session number */
			__( '%1$s · NO. %2$s', 'ftd-directory-listings' ),
			strtoupper( $series ),
			str_pad( (string) $session_no, 2, '0', STR_PAD_LEFT )
		);
	} else {
		$series = strtoupper( $series );
	}

	$title_html = ftd_format_gnls_session_cta_title( get_the_title( $post_id ) );
	$tagline    = $schedule['subtitle'];
	$day_name   = $schedule['day_name'];
	$date_line  = ftd_get_community_call_promo_date( $post_id );
	$time_line  = ftd_get_community_call_promo_times( $post_id );
	$is_capture = ! empty( $atts['capture'] ) && filter_var( $atts['capture'], FILTER_VALIDATE_BOOLEAN );
	$image_size = $is_capture ? 'gnls-youtube-thumb' : 'large';
	$image_html = ftd_get_community_call_feature_image_html(
		$post_id,
		$image_size,
		array(
			'class' => 'gnls-cta-image',
		)
	);

	$card_classes = array( 'ftd-sc', 'ftd-sc--gnls-session-cta', 'gnls-session-cta' );
	if ( $is_capture ) {
		$card_classes[] = 'gnls-cta--capture';
	}
	if ( $link_to_session && $permalink ) {
		$card_classes[] = 'gnls-cta--linked';
	}

	$content_style = function_exists( 'ftd_get_session_cta_background_style_attr' )
		? ftd_get_session_cta_background_style_attr( $post_id )
		: '';

	$bg_color = function_exists( 'ftd_get_session_cta_background_config' )
		? ftd_get_session_cta_background_config( $post_id )['color']
		: '#673f69';

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
		<div class="gnls-cta-content"<?php echo $content_style ? ' style="' . esc_attr( $content_style ) . '"' : ''; ?>>
			<?php if ( $logo_html ) : ?>
				<div class="gnls-cta-brand"><?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></div>
			<?php elseif ( $presents ) : ?>
				<p class="gnls-cta-presents"><?php echo esc_html( $presents ); ?></p>
			<?php endif; ?>

			<p class="gnls-cta-series"><?php echo esc_html( $series ); ?></p>

			<?php if ( $title_html ) : ?>
				<h2 class="gnls-cta-title"><?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></h2>
			<?php endif; ?>

			<?php if ( $tagline ) : ?>
				<p class="gnls-cta-tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>

			<div class="gnls-cta-bottom">
				<?php if ( ! empty( $members ) ) : ?>
					<div class="gnls-cta-hosts">
						<?php
						foreach ( $members as $index => $host ) :
							$profile_url = ! empty( $host['url'] ) ? $host['url'] : ftd_get_member_profile_url( $host['id'] );
							$tagline_host = ftd_get_community_call_member_tagline( $host['id'] );
							$initials     = ftd_get_member_initials( $host['name'] );
							$has_avatar     = ! empty( $host['avatar_url'] ) && false === strpos( $host['avatar_url'], 'gravatar.com/avatar/?' );
							?>
							<?php if ( $index > 0 ) : ?>
								<span class="gnls-cta-host-sep" aria-hidden="true">&amp;</span>
							<?php endif; ?>

							<?php if ( $profile_url ) : ?>
								<a class="gnls-cta-host gnls-cta-host--<?php echo esc_attr( (string) ( $index + 1 ) ); ?>" href="<?php echo esc_url( $profile_url ); ?>">
							<?php else : ?>
								<div class="gnls-cta-host gnls-cta-host--<?php echo esc_attr( (string) ( $index + 1 ) ); ?>">
							<?php endif; ?>
									<?php if ( $has_avatar ) : ?>
										<img
											class="gnls-cta-host-avatar gnls-cta-host-avatar--photo"
											src="<?php echo esc_url( $host['avatar_url'] ); ?>"
											alt="<?php echo esc_attr( $host['name'] ); ?>"
											width="72"
											height="72"
											loading="lazy"
										/>
									<?php else : ?>
										<span class="gnls-cta-host-avatar gnls-cta-host-avatar--initials"><?php echo esc_html( $initials ); ?></span>
									<?php endif; ?>
									<span class="gnls-cta-host-name"><?php echo esc_html( strtoupper( $host['name'] ) ); ?></span>
									<?php if ( $tagline_host ) : ?>
										<span class="gnls-cta-host-tagline"><?php echo esc_html( $tagline_host ); ?></span>
									<?php endif; ?>
							<?php if ( $profile_url ) : ?>
								</a>
							<?php else : ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="gnls-cta-when">
					<?php if ( $day_name ) : ?>
						<p class="gnls-cta-day"><?php echo esc_html( $day_name ); ?></p>
					<?php endif; ?>
					<?php if ( $date_line ) : ?>
						<p class="gnls-cta-date"><?php echo esc_html( $date_line ); ?></p>
					<?php endif; ?>
					<?php if ( $time_line ) : ?>
						<p class="gnls-cta-times"><?php echo esc_html( $time_line ); ?></p>
					<?php endif; ?>
					<?php if ( $show_button && $button && $button_url ) : ?>
						<a class="btn gnls-cta-button" href="<?php echo esc_url( $button_url ); ?>">
						<span class="gnls-cta-button-icon ftd-btn-arrow" aria-hidden="true"></span>
							<span><?php echo esc_html( $button ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="gnls-cta-footer">
				<?php if ( $footnote ) : ?>
					<span class="gnls-cta-footnote"><?php echo esc_html( $footnote ); ?></span>
				<?php endif; ?>
				<span class="gnls-cta-site"><?php echo esc_html( ftd_get_gnls_session_cta_site_label() ); ?></span>
			</div>
		</div>

		<?php if ( $image_html || $is_capture ) : ?>
			<div class="gnls-cta-media">
				<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
