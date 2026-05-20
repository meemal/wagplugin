<?php
/**
 * Shortcode: [wag_stats_ticker]
 *
 * Rotating live site stats (members, map, quantum geniuses, last sign-up country).
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
			'eyebrow'  => 'SO FAR',
			'interval' => 6000,
		),
		$atts,
		'wag_stats_ticker'
	);

	if ( ! function_exists( 'ftd_get_stats_ticker_slides' ) ) {
		return '';
	}

	$slides = ftd_get_stats_ticker_slides();

	if ( empty( $slides ) ) {
		return '';
	}

	$interval = max( 3000, (int) $atts['interval'] );
	$eyebrow  = esc_html( $atts['eyebrow'] );

	wp_enqueue_style( 'ftd-sc-stats-ticker' );
	wp_enqueue_script( 'ftd-stats-ticker' );

	ob_start();
	?>
	<div
		class="ftd-sc ftd-sc--stats-ticker"
		data-ftd-stats-ticker
		data-interval="<?php echo esc_attr( $interval ); ?>"
	>
		<div class="ftd-st-wrap">
			<div class="ftd-st-track" aria-live="polite">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php
					$is_number = ( 'text' !== ( $slide['type'] ?? 'number' ) );
					$active    = 0 === $index ? ' is-active' : '';
					$value     = $slide['value'] ?? '';
					$label     = $slide['label'] ?? '';
					$tagline   = $slide['tagline'] ?? '';
					?>
					<div class="ftd-st-slide<?php echo esc_attr( $active ); ?>" data-slide="<?php echo esc_attr( $index ); ?>">
						<span class="ftd-st-eyebrow"><?php echo $eyebrow; ?></span>
						<span class="ftd-st-value<?php echo $is_number ? '' : ' ftd-st-value--text'; ?>">
							<?php echo esc_html( (string) $value ); ?>
						</span>
						<span class="ftd-st-label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $tagline ) : ?>
							<span class="ftd-st-sep" aria-hidden="true">—</span>
							<span class="ftd-st-tagline"><?php echo esc_html( $tagline ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ftd-st-dots" role="tablist" aria-label="<?php esc_attr_e( 'Site statistics', 'ftd-directory-listings' ); ?>">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<button
						type="button"
						class="ftd-st-dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-slide="<?php echo esc_attr( $index ); ?>"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Stat %d', 'ftd-directory-listings' ), $index + 1 ) ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'wag_stats_ticker', 'ftd_stats_ticker_shortcode' );
add_shortcode( 'ftd_stats_ticker', 'ftd_stats_ticker_shortcode' );

/**
 * Enqueue stats ticker assets when shortcode is present.
 */
function ftd_register_stats_ticker_assets() {
	wp_register_style(
		'ftd-sc-stats-ticker',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/stats-ticker.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	wp_register_script(
		'ftd-stats-ticker',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/stats-ticker.js',
		array(),
		FTD_DIRECTORY_LISTINGS_VERSION,
		true
	);
}

add_action( 'wp_enqueue_scripts', 'ftd_register_stats_ticker_assets', 15 );
