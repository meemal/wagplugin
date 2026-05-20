<?php
/**
 * Shortcode: [wag_stats_ticker]
 *
 * Standalone scrolling stats marquee (same items as sitewide banner, without founding offer).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function ftd_stats_ticker_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'eyebrow' => 'SO FAR',
		),
		$atts,
		'wag_stats_ticker'
	);

	if ( ! function_exists( 'ftd_get_banner_marquee_items' ) ) {
		return '';
	}

	$items = ftd_get_banner_marquee_items( null, null, $atts['eyebrow'] );

	if ( empty( $items ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-founding-spots-banner' );

	ob_start();
	?>
	<div class="ftd-sc ftd-sc--founding-spots-banner ftd-sc--stats-ticker-only">
		<div class="fsb-banner" role="region" aria-label="<?php esc_attr_e( 'Community statistics', 'ftd-directory-listings' ); ?>">
			<div class="fsb-row">
				<?php ftd_render_banner_marquee_track( $items ); ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'wag_stats_ticker', 'ftd_stats_ticker_shortcode' );
add_shortcode( 'ftd_stats_ticker', 'ftd_stats_ticker_shortcode' );

/**
 * Register shared banner/marquee styles for standalone ticker.
 */
function ftd_register_stats_ticker_assets() {
	wp_register_style(
		'ftd-sc-founding-spots-banner',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/founding-spots-banner.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);
}

add_action( 'wp_enqueue_scripts', 'ftd_register_stats_ticker_assets', 15 );
