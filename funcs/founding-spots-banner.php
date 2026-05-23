<?php
/**
 * Sitewide founding-spots banner (top of every front-end page).
 *
 * Infinite horizontal marquee: founding offer + live stats + Sign up now.
 * Settings: Genius Directory Settings → Banner (ACF).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a banner link for output (relative or absolute).
 *
 * @param string $url Raw URL from ACF.
 * @return string Safe absolute URL, or empty string if invalid.
 */
function ftd_normalize_banner_link_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	// Site-relative path: /join-we-are-geniuses/
	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		return home_url( $url );
	}

	// Protocol-relative: //example.com/path
	if ( 0 === strpos( $url, '//' ) ) {
		$url = set_url_scheme( 'https:' . $url );
	}

	// Bare path without leading slash: join-we-are-geniuses/
	if ( ! preg_match( '#^https?://#i', $url ) && 0 !== strpos( $url, 'mailto:' ) && 0 !== strpos( $url, 'tel:' ) ) {
		if ( 0 === strpos( $url, '#' ) || 0 === strpos( $url, '?' ) ) {
			return home_url( '/' ) . ltrim( $url, '/' );
		}

		if ( false === strpos( $url, '.' ) && false === strpos( $url, '://' ) ) {
			return home_url( '/' . ltrim( $url, '/' ) );
		}

		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . ltrim( $url, '/' );
		}
	}

	$sanitized = esc_url_raw( $url );

	return $sanitized ? $sanitized : '';
}

/**
 * @return array<string, mixed>
 */
function ftd_get_founding_spots_banner_settings() {
	$defaults = array(
		'total_spots'          => 111,
		'discount_code'        => 'originalgenius111',
		'stats_eyebrow'        => 'SO FAR',
		'code_label'           => 'use code',
		'link_url'             => '/join-we-are-geniuses/',
		'signup_button_label'  => 'Sign up now',
		'show_progress_bar'    => true,
	);

	$enabled = true;
	if ( function_exists( 'ftd_genius_directory_banner_get_field' ) ) {
		$enabled_field = ftd_genius_directory_banner_get_field( 'founding_spots_banner_enabled' );
		if ( false === $enabled_field || 0 === $enabled_field || '0' === $enabled_field ) {
			$enabled = false;
		}

		$group = ftd_genius_directory_banner_get_field( 'founding_spots_banner' );
		if ( is_array( $group ) ) {
			foreach ( $defaults as $key => $default ) {
				if ( isset( $group[ $key ] ) && '' !== $group[ $key ] && null !== $group[ $key ] ) {
					$defaults[ $key ] = $group[ $key ];
				}
			}
		}
	}

	$defaults['enabled']          = $enabled;
	$defaults['total_spots']      = max( 1, (int) $defaults['total_spots'] );
	$defaults['discount_code']    = sanitize_text_field( strtolower( (string) $defaults['discount_code'] ) );
	$defaults['show_progress_bar'] = ! empty( $defaults['show_progress_bar'] );
	$defaults['link_url']         = ftd_normalize_banner_link_url( $defaults['link_url'] );

	return $defaults;
}

/**
 * Whether the sitewide banner should load on this request.
 *
 * @return bool
 */
function ftd_should_show_founding_spots_banner() {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return false;
	}

	$settings = ftd_get_founding_spots_banner_settings();

	return ! empty( $settings['enabled'] );
}

/**
 * Replace {remaining}, {total}, {used}, {code} in a string.
 *
 * @param string               $text   Template.
 * @param array<string, mixed> $tokens Values.
 * @return string
 */
function ftd_founding_spots_replace_tokens( $text, $tokens ) {
	return str_replace(
		array( '{remaining}', '{total}', '{used}', '{code}' ),
		array(
			$tokens['remaining'],
			$tokens['total'],
			$tokens['used'],
			$tokens['code'],
		),
		$text
	);
}

/**
 * @return string Banner HTML or empty string.
 */
function ftd_get_founding_spots_banner_html() {
	if ( ! ftd_should_show_founding_spots_banner() ) {
		return '';
	}

	if ( ! function_exists( 'ftd_get_banner_marquee_items' ) ) {
		return '';
	}

	$settings = ftd_get_founding_spots_banner_settings();

	$total = function_exists( 'ftd_get_founding_spots_total' )
		? ftd_get_founding_spots_total()
		: (int) $settings['total_spots'];
	$code  = $settings['discount_code'];
	$used = function_exists( 'ftd_get_founding_spots_used' )
		? ftd_get_founding_spots_used()
		: 0;

	$remaining = max( 0, $total - $used );
	$percent   = $total > 0 ? min( 100, round( ( $used / $total ) * 100 ) ) : 0;

	$tokens = array(
		'remaining' => $remaining,
		'total'     => $total,
		'used'      => $used,
		'code'      => strtoupper( $code ),
	);

	$marquee_items = ftd_get_banner_marquee_items(
		$settings,
		$tokens,
		$settings['stats_eyebrow'] ?? 'SO FAR'
	);

	if ( empty( $marquee_items ) ) {
		return '';
	}

	$signup_url   = esc_url( $settings['link_url'] );
	$signup_label = esc_html( $settings['signup_button_label'] ?: __( 'Sign up now', 'ftd-directory-listings' ) );

	$progress_aria = sprintf(
		/* translators: 1: used count, 2: total spots */
		__( '%1$d of %2$d founding spots claimed', 'ftd-directory-listings' ),
		$used,
		$total
	);

	ob_start();
	?>
	<div id="ftd-founding-spots-banner" class="ftd-sc ftd-sc--founding-spots-banner ftd-founding-spots-banner--sitewide">
		<div class="fsb-banner" style="background:#2d132c;color:#fff;" role="region" aria-label="<?php esc_attr_e( 'Founding membership offer and community stats', 'ftd-directory-listings' ); ?>">
			<div class="fsb-row">
				<?php ftd_render_banner_marquee_track( $marquee_items ); ?>

				<?php if ( $signup_url ) : ?>
					<a class="fsb-signup-btn" href="<?php echo $signup_url; ?>">
						<?php echo $signup_label; ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $settings['show_progress_bar'] ) && $remaining > 0 ) : ?>
				<div
					class="fsb-progress"
					role="progressbar"
					aria-valuenow="<?php echo esc_attr( $used ); ?>"
					aria-valuemin="0"
					aria-valuemax="<?php echo esc_attr( $total ); ?>"
					aria-label="<?php echo esc_attr( $progress_aria ); ?>"
				>
					<div class="fsb-track">
						<div class="fsb-fill" style="width:<?php echo esc_attr( $percent ); ?>%;"></div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Echo sitewide banner (guarded against duplicate output).
 */
function ftd_render_founding_spots_banner_sitewide() {
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	$html = ftd_get_founding_spots_banner_html();
	if ( '' === $html ) {
		return;
	}

	$rendered = true;
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in template.
}

add_action( 'wp_body_open', 'ftd_render_founding_spots_banner_sitewide', 5 );

/**
 * Critical banner styles in head so first paint matches the styled ticker (avoids FOUC).
 */
function ftd_print_founding_spots_banner_critical_css() {
	if ( ! ftd_should_show_founding_spots_banner() ) {
		return;
	}
	?>
<style id="ftd-founding-spots-banner-critical">
#ftd-founding-spots-banner,.ftd-founding-spots-banner--sitewide{width:100%;position:relative;z-index:99990}
.ftd-sc--founding-spots-banner .fsb-banner{background:#2d132c;color:#fff;box-sizing:border-box;padding:.75rem 0 .65rem}
.ftd-sc--founding-spots-banner .fsb-row{display:flex;align-items:center;gap:.75rem;padding:0 0 0 .5rem;box-sizing:border-box}
.ftd-sc--founding-spots-banner .fsb-marquee-viewport{flex:1;min-width:0;overflow:hidden}
.ftd-sc--founding-spots-banner .fsb-marquee-track{display:flex;width:max-content}
.ftd-sc--founding-spots-banner .fsb-item-value,.ftd-sc--founding-spots-banner .fsb-item-label{color:#fff}
</style>
	<?php
}

add_action( 'wp_head', 'ftd_print_founding_spots_banner_critical_css', 1 );

/**
 * Enqueue banner CSS on front end when the banner is active.
 */
function ftd_enqueue_founding_spots_banner_styles() {
	if ( ! ftd_should_show_founding_spots_banner() ) {
		return;
	}

	wp_enqueue_style(
		'ftd-sc-founding-spots-banner',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/founding-spots-banner.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
	);
}

add_action( 'wp_enqueue_scripts', 'ftd_enqueue_founding_spots_banner_styles', 20 );
