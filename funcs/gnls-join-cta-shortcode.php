<?php
/**
 * Shortcode: [gnls_join_cta] / [live_sessions_join_cta]
 *
 * Join the directory / register for Live Sessions CTA block.
 *
 * Usage:
 *   [gnls_join_cta]
 *   [gnls_join_cta heading="All members welcome" button="Join now"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'gnls_join_cta', 'ftd_gnls_join_cta_shortcode' );
add_shortcode( 'live_sessions_join_cta', 'ftd_gnls_join_cta_shortcode' );

add_action( 'wp_enqueue_scripts', 'ftd_register_gnls_join_cta_assets', 15 );
add_action( 'get_footer', 'ftd_auto_render_gnls_join_cta_on_directory_archive', 1 );

/**
 * Register join CTA stylesheet.
 */
function ftd_register_gnls_join_cta_assets() {
	wp_register_style(
		'ftd-sc-gnls-join-cta',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/gnls-join-cta.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);
}

/**
 * Output the join CTA at the bottom of the Genius Directory archive.
 */
function ftd_auto_render_gnls_join_cta_on_directory_archive() {
	if ( ! is_post_type_archive( 'directory_listing' ) ) {
		return;
	}

	echo ftd_render_gnls_join_cta(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
}

/**
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string
 */
function ftd_gnls_join_cta_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'eyebrow'     => '',
			'heading'     => '',
			'subtitle'    => '',
			'body'        => '',
			'button'      => '',
			'button_url'  => '',
			'tagline'     => '',
			'show_button' => '',
		),
		$atts,
		'gnls_join_cta'
	);

	$overrides = array(
		'eyebrow'        => $atts['eyebrow'],
		'heading'        => $atts['heading'],
		'subtitle'       => $atts['subtitle'],
		'body'           => $atts['body'],
		'button_label'   => $atts['button'],
		'button_url'     => $atts['button_url'],
		'footer_tagline' => $atts['tagline'],
	);

	if ( '' !== $atts['show_button'] ) {
		$overrides['button_enabled'] = $atts['show_button'];
	}

	return ftd_render_gnls_join_cta( $overrides );
}

/**
 * Render the join CTA block.
 *
 * @param array<string, mixed> $overrides Optional setting overrides.
 * @return string
 */
function ftd_render_gnls_join_cta( $overrides = array() ) {
	if ( ! function_exists( 'ftd_get_gnls_join_cta_settings' ) ) {
		return '';
	}

	$settings = ftd_get_gnls_join_cta_settings( $overrides );

	if ( '' === trim( $settings['heading'] ) && '' === trim( $settings['body'] ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-gnls-join-cta' );

	ob_start();
	?>
	<section class="ftd-sc ftd-sc--gnls-join-cta gnls-join-cta">
		<div class="gnls-join-cta-inner">
			<?php if ( $settings['eyebrow'] ) : ?>
				<p class="gnls-join-cta-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( $settings['heading'] ) : ?>
				<h2 class="gnls-join-cta-heading"><?php echo esc_html( $settings['heading'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $settings['subtitle'] ) : ?>
				<p class="gnls-join-cta-subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
			<?php endif; ?>

			<?php if ( $settings['subtitle'] || $settings['heading'] ) : ?>
				<div class="gnls-join-cta-divider" aria-hidden="true"><span>♡</span></div>
			<?php endif; ?>

			<?php if ( $settings['body'] ) : ?>
				<div class="gnls-join-cta-body">
					<?php echo wp_kses_post( wpautop( $settings['body'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $settings['button_enabled'] && $settings['button_label'] && $settings['button_url'] ) : ?>
				<p class="gnls-join-cta-button-wrap">
					<a class="btn gnls-join-cta-button" href="<?php echo esc_url( $settings['button_url'] ); ?>">
						<span class="gnls-join-cta-button-icon ftd-btn-arrow" aria-hidden="true"></span>
						<span><?php echo esc_html( $settings['button_label'] ); ?></span>
					</a>
				</p>
			<?php endif; ?>

			<?php if ( $settings['footer_tagline'] ) : ?>
				<footer class="gnls-join-cta-footer">
					<p class="gnls-join-cta-tagline">
						<span class="gnls-join-cta-tagline-icon" aria-hidden="true">◆</span>
						<em><?php echo esc_html( $settings['footer_tagline'] ); ?></em>
					</p>
				</footer>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Compact join CTA for single session sidebar (same content as archive outro).
 *
 * @param array<string, mixed> $overrides Optional setting overrides.
 * @return string
 */
function ftd_render_gnls_join_cta_sidebar( $overrides = array() ) {
	if ( ! function_exists( 'ftd_get_gnls_join_cta_settings' ) ) {
		return '';
	}

	$settings = ftd_get_gnls_join_cta_settings( $overrides );

	if ( '' === trim( $settings['eyebrow'] ) && '' === trim( $settings['heading'] ) && '' === trim( $settings['subtitle'] ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-gnls-join-cta' );

	ob_start();
	?>
	<section class="ftd-sc ftd-sc--gnls-join-cta gnls-join-cta gnls-join-cta--sidebar card">
		<div class="gnls-join-cta-inner">
			<?php if ( $settings['eyebrow'] ) : ?>
				<p class="gnls-join-cta-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( $settings['heading'] ) : ?>
				<h2 class="gnls-join-cta-heading"><?php echo esc_html( $settings['heading'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $settings['subtitle'] ) : ?>
				<p class="gnls-join-cta-subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
			<?php endif; ?>

			<?php if ( $settings['button_enabled'] && $settings['button_label'] && $settings['button_url'] ) : ?>
				<p class="gnls-join-cta-button-wrap">
					<a class="btn gnls-join-cta-button" href="<?php echo esc_url( $settings['button_url'] ); ?>">
						<span class="gnls-join-cta-button-icon ftd-btn-arrow" aria-hidden="true"></span>
						<span><?php echo esc_html( $settings['button_label'] ); ?></span>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
