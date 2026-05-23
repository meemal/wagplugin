<?php
/**
 * Shortcode: [wag_social_share]
 *
 * Social sharing tool with scrollable message options and PMPro affiliate link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string>
 */
function ftd_get_social_share_ui_settings() {
	$defaults = array(
		'heading'      => 'Share the gathering',
		'subtitle'     => 'pick a voice, pass it on,',
		'voices_label' => 'PICK A VOICE',
	);

	if ( function_exists( 'ftd_genius_directory_social_share_get_field' ) ) {
		foreach ( array_keys( $defaults ) as $key ) {
			$acf_key = 'social_share_' . $key;
			$value   = ftd_genius_directory_social_share_get_field( $acf_key );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$defaults[ $key ] = trim( $value );
			}
		}
	}

	return apply_filters( 'ftd_social_share_ui_settings', $defaults );
}

/**
 * Convert ACF textarea line breaks (<br> tags) to plain newlines.
 *
 * @param string $message Raw message from ACF or defaults.
 * @return string
 */
function ftd_normalize_share_message_text( $message ) {
	$message = (string) $message;
	$message = preg_replace( '#<br\s*/?>#i', "\n", $message );
	$message = wp_strip_all_tags( $message );

	return str_replace( array( "\r\n", "\r" ), "\n", $message );
}

/**
 * Personalize share copy with the member's affiliate link.
 *
 * @param string $message       Raw message.
 * @param string $affiliate_url Full affiliate URL.
 * @return string
 */
function ftd_personalize_share_message( $message, $affiliate_url ) {
	if ( '' === trim( (string) $affiliate_url ) ) {
		return $message;
	}

	$display = ftd_social_share_display_link( $affiliate_url );

	// Longest placeholders first; bare domain must not match inside an already-inserted join URL.
	$patterns = array(
		'/\{\{affiliate_link\}\}/i',
		'#https?://wearegeniuses\.com/join-we-are-geniuses/?#i',
		'#https?://wearegeniuses\.co/join-we-are-geniuses/?#i',
		'#wearegeniuses\.com/join-we-are-geniuses/?#i',
		'#wearegeniuses\.co/join-we-are-geniuses/?#i',
		'#wearegeniuses\.com(?!/join-we-are-geniuses)#i',
		'#wearegeniuses\.co(?!/join-we-are-geniuses)#i',
	);

	foreach ( $patterns as $pattern ) {
		$message = preg_replace( $pattern, $display, $message );
	}

	return $message;
}

/**
 * @return array<int, array<string, string>>
 */
function ftd_get_social_share_messages() {
	$messages = array();

	if ( function_exists( 'ftd_genius_directory_social_share_get_field' ) ) {
		$rows = ftd_genius_directory_social_share_get_field( 'social_share_messages' );
		if ( is_array( $rows ) && ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$parsed = function_exists( 'ftd_parse_social_share_message_row' )
					? ftd_parse_social_share_message_row( $row )
					: null;
				if ( null !== $parsed ) {
					$messages[] = $parsed;
				}
			}
		}
	}

	if ( empty( $messages ) ) {
		$messages = ftd_get_social_share_default_messages();
	}

	return apply_filters( 'ftd_social_share_messages', $messages );
}

/**
 * Build external share URLs for a message.
 *
 * @param string $text          Share text (with affiliate link in body).
 * @param string $affiliate_url URL to share.
 * @return array<string, string>
 */
function ftd_get_social_share_urls( $text, $affiliate_url ) {
	$body = $text . "\n\n" . $affiliate_url;

	return array(
		'twitter'   => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $body ),
		'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $affiliate_url ) . '&quote=' . rawurlencode( $text ),
		'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $affiliate_url ),
		'whatsapp'  => 'https://wa.me/?text=' . rawurlencode( $body ),
		'telegram'  => ftd_get_telegram_share_url( $affiliate_url, $text ),
		'email'     => 'mailto:?subject=' . rawurlencode( __( 'Join me on We Are Geniuses', 'ftd-directory-listings' ) ) . '&body=' . rawurlencode( $body ),
	);
}

/**
 * Build a Telegram share URL.
 *
 * @param string $url  Link to share.
 * @param string $text Optional message shown above the link.
 * @return string
 */
function ftd_get_telegram_share_url( $url, $text = '' ) {
	return 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $text );
}

/**
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function ftd_social_share_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'modal' => '0',
		),
		$atts,
		'wag_social_share'
	);

	if ( ! is_user_logged_in() ) {
		return '<p class="ftd-social-share-notice">' . esc_html__( 'Please log in to use the sharing tool.', 'ftd-directory-listings' ) . '</p>';
	}

	$affiliate_url = ftd_get_user_affiliate_link();

	if ( '' === $affiliate_url ) {
		return '<p class="ftd-social-share-notice">' . esc_html__( 'Your affiliate link is not available yet. Contact support if you believe this is an error.', 'ftd-directory-listings' ) . '</p>';
	}

	$messages = ftd_get_social_share_messages();

	if ( empty( $messages ) ) {
		return '';
	}

	$ui        = ftd_get_social_share_ui_settings();
	$prepared  = array();
	$is_modal  = filter_var( $atts['modal'], FILTER_VALIDATE_BOOLEAN );

	foreach ( $messages as $index => $item ) {
		$label = $item['label'] ?: sprintf(
			/* translators: %d: option number */
			__( 'Voice %d', 'ftd-directory-listings' ),
			$index + 1
		);
		$text = ftd_normalize_share_message_text(
			ftd_personalize_share_message( $item['message'], $affiliate_url )
		);

		$prepared[] = array(
			'label' => $label,
			'text'  => $text,
			'urls'  => ftd_get_social_share_urls( $text, $affiliate_url ),
			'index' => $index,
		);
	}

	wp_enqueue_style( 'ftd-sc-social-share' );
	wp_enqueue_script( 'ftd-social-share' );

	$wrapper_class = 'ftd-sc ftd-sc--social-share';
	if ( $is_modal ) {
		$wrapper_class .= ' ftd-sc--social-share-modal is-open';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-ftd-social-share data-affiliate-url="<?php echo esc_url( $affiliate_url ); ?>"<?php echo $is_modal ? ' data-ftd-ss-modal' : ''; ?>>
		<?php if ( $is_modal ) : ?>
			<div class="ftd-ss-backdrop" data-ftd-ss-close></div>
		<?php endif; ?>

		<div class="ftd-ss-card" role="dialog" aria-labelledby="ftd-ss-heading"<?php echo $is_modal ? ' aria-modal="true"' : ''; ?>>
			<button type="button" class="ftd-ss-close" data-ftd-ss-close aria-label="<?php esc_attr_e( 'Close', 'ftd-directory-listings' ); ?>">&times;</button>

			<div class="ftd-ss-ornament" aria-hidden="true">
				<span class="ftd-ss-ornament-line"></span>
				<span class="ftd-ss-ornament-diamond"></span>
				<span class="ftd-ss-ornament-line"></span>
			</div>

			<h2 id="ftd-ss-heading" class="ftd-ss-title"><?php echo esc_html( $ui['heading'] ); ?></h2>
			<p class="ftd-ss-subtitle"><?php echo esc_html( $ui['subtitle'] ); ?></p>

			<div class="ftd-ss-voices">
				<p class="ftd-ss-voices-label"><?php echo esc_html( $ui['voices_label'] ); ?></p>
				<div class="ftd-ss-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Share message voices', 'ftd-directory-listings' ); ?>">
					<?php foreach ( $prepared as $i => $slide ) : ?>
						<button
							type="button"
							class="ftd-ss-tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
							role="tab"
							data-index="<?php echo esc_attr( $i ); ?>"
							aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							aria-controls="ftd-ss-panel-<?php echo esc_attr( $i ); ?>"
							id="ftd-ss-tab-<?php echo esc_attr( $i ); ?>"
						><?php echo esc_html( $slide['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="ftd-ss-panels">
				<?php foreach ( $prepared as $i => $slide ) : ?>
					<div
						class="ftd-ss-panel<?php echo 0 === $i ? ' is-active' : ''; ?>"
						id="ftd-ss-panel-<?php echo esc_attr( $i ); ?>"
						role="tabpanel"
						aria-labelledby="ftd-ss-tab-<?php echo esc_attr( $i ); ?>"
						<?php echo 0 !== $i ? ' hidden' : ''; ?>
					>
						<div class="ftd-ss-message"><?php echo nl2br( esc_html( $slide['text'] ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ftd-ss-footer">
				<div class="ftd-ss-social-row">
					<a class="ftd-ss-pill ftd-ss-pill--twitter" href="<?php echo esc_url( $prepared[0]['urls']['twitter'] ); ?>" data-share="twitter" target="_blank" rel="noopener noreferrer">
						<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'twitter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ftd-ss-pill-label">X / Twitter</span>
					</a>
					<a class="ftd-ss-pill ftd-ss-pill--facebook" href="<?php echo esc_url( $prepared[0]['urls']['facebook'] ); ?>" data-share="facebook" target="_blank" rel="noopener noreferrer">
						<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ftd-ss-pill-label">Facebook</span>
					</a>
					<a class="ftd-ss-pill ftd-ss-pill--linkedin" href="<?php echo esc_url( $prepared[0]['urls']['linkedin'] ); ?>" data-share="linkedin" target="_blank" rel="noopener noreferrer">
						<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'linkedin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ftd-ss-pill-label">LinkedIn</span>
					</a>
					<a class="ftd-ss-pill ftd-ss-pill--whatsapp" href="<?php echo esc_url( $prepared[0]['urls']['whatsapp'] ); ?>" data-share="whatsapp" target="_blank" rel="noopener noreferrer">
						<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ftd-ss-pill-label">WhatsApp</span>
					</a>
					<a class="ftd-ss-pill ftd-ss-pill--telegram" href="<?php echo esc_url( $prepared[0]['urls']['telegram'] ); ?>" data-share="telegram" target="_blank" rel="noopener noreferrer">
						<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ftd-ss-pill-label">Telegram</span>
					</a>
				</div>
				<button type="button" class="ftd-ss-pill ftd-ss-pill--copy" data-ftd-ss-copy>
					<span class="ftd-ss-pill-icon ftd-ss-pill-icon--copy" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="ftd-ss-pill-label"><?php esc_html_e( 'Copy', 'ftd-directory-listings' ); ?></span>
				</button>
			</div>

			<script type="application/json" id="ftd-social-share-data"><?php echo wp_json_encode( $prepared ); ?></script>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Simple inline icons for social pills.
 *
 * @param string $name Icon key.
 * @return string
 */
function ftd_social_share_icon_svg( $name ) {
	$icons = array(
		'twitter'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.437H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-.295 1.144-1.527 2.174-3.047 2.174v3.47h3.281z"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.919v5.813h-3.554V9.351h3.554v1.561h.051c.711-1.966 2.878-3.154 4.847-3.154 5.604 0 6.557 3.617 6.557 7.498v6.196zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.065zm1.782 13.019H3.555V9.351h3.564v11.101zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.336-.407.465-.14.176-.292.473-.386.67-.156.197-.003.36-.618.54-.82.174-.149.297-.247.386-.475.099.178-.617.92-.95 1.551-.297.67-.003.447-.292.519 1.058.748 1.674.748 2.444 0 1.422-.139 2.654-.837 3.683-.606 1.102-2.03 2.001-2.03.973-.004.01-.004.729-.054.134-1.72z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.399-1.145A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.77 9.77 0 01-4.986-1.363l-.357-.212-3.826 1.004.98-3.735-.233-.371A9.818 9.818 0 0112 2.182c5.454 0 9.818 4.364 9.818 9.818 0 5.454-4.364 9.818-9.818 9.818z"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M9.417 15.181l-.397 5.584c.568 0 .814-.244 1.109-.537l2.664-2.538 5.523 4.038c1.012.557 1.73.264 1.979-.936l3.594-16.822h.001c.318-1.478-.537-2.055-1.514-1.708L1.179 9.557c-1.454.564-1.434 1.374-.248 1.735l4.753 1.482 11.054-6.954c.521-.329 1.000-.146.608.183"/></svg>',
		'copy'      => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>',
	);

	return $icons[ $name ] ?? '';
}

add_shortcode( 'wag_social_share', 'ftd_social_share_shortcode' );
add_shortcode( 'ftd_social_share', 'ftd_social_share_shortcode' );

/**
 * Register assets.
 */
function ftd_register_social_share_assets() {
	wp_register_style(
		'ftd-sc-social-share',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/social-share.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
	);

	wp_register_script(
		'ftd-social-share',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/social-share.js',
		array(),
		ftd_get_plugin_asset_version(),
		true
	);
}

add_action( 'wp_enqueue_scripts', 'ftd_register_social_share_assets', 15 );
