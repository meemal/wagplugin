<?php
/**
 * Founding Genius Banner Shortcode
 *
 * Displays the founding member offer banner with live discount code
 * usage pulled from Paid Memberships Pro.
 *
 * Usage: [founding_genius_banner]
 * Requirements: Paid Memberships Pro active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ftd_register_founding_genius_banner_assets', 15 );

/**
 * Register founding genius banner assets.
 */
function ftd_register_founding_genius_banner_assets() {
	wp_register_script(
		'ftd-founding-genius-banner',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/founding-genius-banner.js',
		array(),
		FTD_DIRECTORY_LISTINGS_VERSION,
		true
	);
}

/**
 * Build label markup with an animated number span for a token placeholder.
 *
 * @param string $template Template containing e.g. {used}.
 * @param string $token    Token key without braces.
 * @param int    $value    Target number.
 * @param string $class    Span class.
 * @return string
 */
function ftd_founding_genius_animated_number_markup( $template, $token, $value, $class = 'fg-animated-num' ) {
	$placeholder = '{' . $token . '}';
	$value       = (int) $value;

	if ( false === strpos( $template, $placeholder ) ) {
		return esc_html( str_replace( $placeholder, (string) $value, $template ) );
	}

	$parts = explode( $placeholder, $template, 2 );
	$html  = '';

	if ( '' !== $parts[0] ) {
		$html .= esc_html( $parts[0] );
	}

	$html .= sprintf(
		'<span class="%1$s" data-fg-count-to="%2$d">0</span>',
		esc_attr( $class ),
		$value
	);

	if ( isset( $parts[1] ) && '' !== $parts[1] ) {
		$html .= esc_html( $parts[1] );
	}

	return $html;
}

/**
 * Founding genius banner settings from ACF (with shortcode defaults as fallback).
 *
 * @return array<string, mixed>
 */
function ftd_get_founding_genius_banner_settings() {
	$settings = array_merge(
		ftd_get_founding_genius_banner_text_defaults(),
		array(
			'total' => 111,
			'code'  => 'originalgenius111',
		)
	);

	if ( function_exists( 'ftd_get_founding_spots_banner_settings' ) ) {
		$spots = ftd_get_founding_spots_banner_settings();

		$settings['total'] = (int) ( $spots['total_spots'] ?? $settings['total'] );
		$settings['code']  = (string) ( $spots['discount_code'] ?? $settings['code'] );
	}

	if ( function_exists( 'ftd_genius_directory_banner_get_field' ) ) {
		$group = ftd_genius_directory_banner_get_field( 'founding_genius_banner' );

		if ( is_array( $group ) ) {
			foreach ( array_keys( ftd_get_founding_genius_banner_text_defaults() ) as $key ) {
				if ( isset( $group[ $key ] ) && is_string( $group[ $key ] ) && '' !== trim( $group[ $key ] ) ) {
					$settings[ $key ] = trim( $group[ $key ] );
				}
			}
		}
	}

	$settings['total'] = max( 1, (int) $settings['total'] );
	$settings['code']  = sanitize_text_field( strtolower( (string) $settings['code'] ) );

	return apply_filters( 'ftd_founding_genius_banner_settings', $settings );
}

/**
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function ftd_founding_genius_banner_shortcode( $atts ) {
	$acf_settings = ftd_get_founding_genius_banner_settings();

	$atts = shortcode_atts(
		array(
			'total'           => $acf_settings['total'],
			'code'            => $acf_settings['code'],
			'badge'           => $acf_settings['badge'],
			'headline'        => $acf_settings['headline'],
			'subtext'         => $acf_settings['subtext'],
			'code_label'      => $acf_settings['code_label'],
			'joined_text'     => $acf_settings['joined_text'],
			'remaining_text'  => $acf_settings['remaining_text'],
			'all_claimed'     => $acf_settings['all_claimed'],
			'claimed_notice'  => $acf_settings['claimed_notice'],
		),
		$atts,
		'founding_genius_banner'
	);

	$total       = function_exists( 'ftd_get_founding_spots_total' )
		? ftd_get_founding_spots_total()
		: intval( $atts['total'] );
	$coupon_code = sanitize_text_field( strtolower( $atts['code'] ) );

	$used = function_exists( 'ftd_get_founding_spots_used' )
		? ftd_get_founding_spots_used()
		: 0;

	$remaining = max( 0, $total - $used );
	$percent   = $total > 0 ? min( 100, round( ( $used / $total ) * 100 ) ) : 0;

	$tokens = array(
		'{total}'     => $total,
		'{used}'      => $used,
		'{remaining}' => $remaining,
		'{code}'      => strtoupper( $coupon_code ),
	);

	$replace = function ( $string ) use ( $tokens ) {
		return str_replace( array_keys( $tokens ), array_values( $tokens ), $string );
	};

	$badge          = esc_html( $replace( $atts['badge'] ) );
	$headline       = wp_kses_post( $replace( $atts['headline'] ) );
	$subtext        = esc_html( $replace( $atts['subtext'] ) );
	$code_label     = esc_html( $replace( $atts['code_label'] ) );
	$joined_label   = esc_html( $replace( $atts['joined_text'] ) );
	$remaining_label = esc_html( $replace( $atts['remaining_text'] ) );
	$claimed_notice = esc_html( $replace( $atts['claimed_notice'] ) );
	$all_claimed    = esc_html( $replace( $atts['all_claimed'] ) );

	wp_enqueue_style( 'ftd-sc-founding-genius' );
	wp_enqueue_script( 'ftd-founding-genius-banner' );

	$counter_aria = sprintf(
		/* translators: 1: spots remaining, 2: total spots */
		__( '%1$d of %2$d spots remaining', 'ftd-directory-listings' ),
		$remaining,
		$total
	);

	ob_start();
	?>
	<div class="ftd-sc ftd-sc--founding-genius">
		<div
			class="fg-banner"
			role="region"
			aria-label="<?php echo esc_attr( $badge ); ?>"
			<?php if ( $remaining > 0 ) : ?>
				data-fg-animate="1"
				data-fg-total="<?php echo esc_attr( (string) $total ); ?>"
				data-fg-used="<?php echo esc_attr( (string) $used ); ?>"
				data-fg-remaining="<?php echo esc_attr( (string) $remaining ); ?>"
			<?php endif; ?>
		>

			<?php if ( $badge ) : ?>
				<p class="fg-label"><?php echo $badge; ?></p>
			<?php endif; ?>

			<?php if ( $headline ) : ?>
				<h2 class="fg-headline"><?php echo $headline; ?></h2>
			<?php endif; ?>

			<?php if ( $subtext ) : ?>
				<p class="fg-subtext"><?php echo $subtext; ?></p>
			<?php endif; ?>

			<?php if ( $remaining > 0 ) : ?>
				<div class="fg-counter" aria-label="<?php echo esc_attr( $counter_aria ); ?>">
					<span class="fg-counter-num fg-animated-num" data-fg-count-to="<?php echo esc_attr( (string) $remaining ); ?>">0</span>
					<span class="fg-counter-of">of</span>
					<span class="fg-counter-total fg-animated-num" data-fg-count-to="<?php echo esc_attr( (string) $total ); ?>">0</span>
				</div>

				<div class="fg-progress">
					<div class="fg-track"
						 role="progressbar"
						 aria-valuenow="0"
						 aria-valuemin="0"
						 aria-valuemax="<?php echo esc_attr( $total ); ?>"
						 data-fg-used="<?php echo esc_attr( (string) $used ); ?>"
						 aria-label="<?php echo esc_attr( $joined_label . ', ' . $remaining_label ); ?>">
						<div class="fg-fill" data-fg-percent="<?php echo esc_attr( (string) $percent ); ?>" style="width:0;"></div>
					</div>
					<div class="fg-progress-labels">
						<span class="fg-joined"><?php echo ftd_founding_genius_animated_number_markup( $atts['joined_text'], 'used', $used, 'fg-animated-num fg-joined-num' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></span>
						<span class="fg-left"><?php echo ftd_founding_genius_animated_number_markup( $atts['remaining_text'], 'remaining', $remaining, 'fg-animated-num fg-left-num' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></span>
					</div>
				</div>

				<?php if ( $code_label ) : ?>
					<div class="fg-code-pill">
						<span class="fg-code-label"><?php echo $code_label; ?></span>
						<strong class="fg-code-value"><?php echo esc_html( strtoupper( $coupon_code ) ); ?></strong>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<p class="fg-all-claimed"><?php echo $all_claimed; ?></p>
				<?php if ( $claimed_notice ) : ?>
					<p class="fg-claimed-notice"><?php echo $claimed_notice; ?></p>
				<?php endif; ?>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'founding_genius_banner', 'ftd_founding_genius_banner_shortcode' );
