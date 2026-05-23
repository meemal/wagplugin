<?php
/**
 * Capture session promo CTA as a 1280×720 YouTube thumbnail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'ftd_register_gnls_cta_capture_meta_box' );
add_action( 'admin_enqueue_scripts', 'ftd_enqueue_gnls_cta_capture_admin_assets' );
add_action( 'template_redirect', 'ftd_handle_gnls_cta_capture_preview', 1 );
add_action( 'wp_ajax_ftd_save_gnls_cta_thumbnail', 'ftd_ajax_save_gnls_cta_thumbnail' );
add_action( 'wp_ajax_ftd_save_gnls_social_thumbnail', 'ftd_ajax_save_gnls_social_thumbnail' );

/**
 * Map a public image URL to a local filesystem path when possible.
 *
 * @param string $url Image URL.
 * @return string
 */
function ftd_gnls_capture_url_to_path( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	$map = array(
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) => plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ),
		content_url()                               => WP_CONTENT_DIR,
		site_url()                                  => ABSPATH,
		home_url()                                  => ABSPATH,
	);

	$upload_dir = wp_upload_dir();

	if ( ! empty( $upload_dir['baseurl'] ) && ! empty( $upload_dir['basedir'] ) ) {
		$map[ $upload_dir['baseurl'] ] = wp_normalize_path( $upload_dir['basedir'] );
	}

	foreach ( $map as $base_url => $base_path ) {
		if ( 0 !== strpos( $url, $base_url ) ) {
			continue;
		}

		$relative = substr( $url, strlen( $base_url ) );
		$path     = wp_normalize_path( $base_path . ltrim( $relative, '/' ) );

		if ( is_readable( $path ) ) {
			return $path;
		}
	}

	return '';
}

/**
 * Inline data URI for images used in html2canvas capture.
 *
 * @param string $url Image URL.
 * @return string
 */
function ftd_get_gnls_capture_image_data_uri( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	static $cache = array();

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$attachment_id = attachment_url_to_postid( $url );

	if ( $attachment_id > 0 ) {
		$attached = get_attached_file( $attachment_id );

		if ( is_string( $attached ) && is_readable( $attached ) ) {
			$mime = wp_check_filetype( $attached );
			$type = ! empty( $mime['type'] ) ? $mime['type'] : 'image/jpeg';
			$data = file_get_contents( $attached );

			if ( false !== $data && '' !== $data ) {
				$cache[ $url ] = 'data:' . $type . ';base64,' . base64_encode( $data );
				return $cache[ $url ];
			}
		}
	}

	$path = ftd_gnls_capture_url_to_path( $url );

	if ( $path ) {
		$mime = wp_check_filetype( $path );
		$type = ! empty( $mime['type'] ) ? $mime['type'] : 'image/jpeg';
		$data = file_get_contents( $path );

		if ( false !== $data && '' !== $data ) {
			$cache[ $url ] = 'data:' . $type . ';base64,' . base64_encode( $data );
			return $cache[ $url ];
		}
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout'   => 20,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'headers'   => array(
				'Accept' => 'image/*,*/*;q=0.8',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$cache[ $url ] = $url;
		return $url;
	}

	$body = wp_remote_retrieve_body( $response );
	$type = wp_remote_retrieve_header( $response, 'content-type' );

	if ( ! is_string( $type ) || '' === $type ) {
		$type = 'image/jpeg';
	} else {
		$type = trim( strtok( $type, ';' ) );
	}

	if ( '' === $body ) {
		$cache[ $url ] = $url;
		return $url;
	}

	$cache[ $url ] = 'data:' . $type . ';base64,' . base64_encode( $body );
	return $cache[ $url ];
}

/**
 * @font-face rules for capture preview (woff2 from Google Fonts, cached).
 *
 * @return string
 */
function ftd_get_gnls_capture_fonts_css() {
	static $css = null;

	if ( null !== $css ) {
		return $css;
	}

	$transient = 'ftd_gnls_capture_fonts_css';
	$cached    = get_transient( $transient );

	if ( is_string( $cached ) && '' !== $cached ) {
		$css = $cached;
		return $css;
	}

	$response = wp_remote_get(
		'https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&family=Roboto:wght@400;700&display=block',
		array(
			'headers' => array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$css = '';
		return $css;
	}

	$css = wp_remote_retrieve_body( $response );

	if ( ! is_string( $css ) ) {
		$css = '';
		return $css;
	}

	set_transient( $transient, $css, WEEK_IN_SECONDS );

	return $css;
}

/**
 * Inline poster capture styles so html2canvas does not depend on external CSS requests.
 *
 * @return string
 */
function ftd_get_gnls_capture_inline_styles() {
	static $css = null;

	if ( null !== $css ) {
		return $css;
	}

	$css   = '';
	$files = array(
		'directory-listings.css',
		'gnls-session-cta.css',
		'gnls-session-cta-poster-capture.css',
	);
	$base = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/';

	foreach ( $files as $file ) {
		$path = $base . $file;

		if ( is_readable( $path ) ) {
			$css .= file_get_contents( $path ) . "\n";
		}
	}

	return $css;
}

/**
 * Minimal layout rules always present in the capture iframe.
 *
 * @param int $width  Capture width.
 * @param int $height Capture height.
 * @return string
 */
function ftd_get_gnls_capture_layout_styles( $width, $height ) {
	return '
		html {
			font-size: 16px;
		}
		html, body {
			margin: 0;
			padding: 0;
			background: transparent;
			width: ' . (int) $width . 'px;
			height: ' . (int) $height . 'px;
			overflow: hidden;
		}
		#gnls-cta-capture-target {
			width: ' . (int) $width . 'px;
			height: ' . (int) $height . 'px;
		}
		#gnls-cta-capture-target > .ftd-sc--gnls-session-cta {
			width: ' . (int) $width . 'px !important;
			max-width: none !important;
			height: ' . (int) $height . 'px !important;
			aspect-ratio: auto !important;
		}
		.gnls-cta-poster-inner,
		.gnls-cta-title,
		.gnls-cta-tagline,
		.gnls-cta-presents,
		.gnls-cta-series--poster,
		.gnls-cta-host-name,
		.gnls-cta-host-tagline,
		.gnls-cta-poster-date,
		.gnls-cta-poster-site {
			line-height: normal;
		}
	';
}

/**
 * Meta box on live session edit screen.
 */
function ftd_register_gnls_cta_capture_meta_box() {
	add_meta_box(
		'ftd_gnls_cta_capture',
		__( 'Promo images', 'ftd-directory-listings' ),
		'ftd_render_gnls_cta_capture_meta_box',
		FTD_COMMUNITY_CALL_POST_TYPE,
		'side',
		'default'
	);
}

/**
 * @param WP_Post $post Post object.
 */
function ftd_render_gnls_cta_capture_meta_box( $post ) {
	$youtube_id = function_exists( 'ftd_get_community_call_youtube_thumbnail_id' )
		? ftd_get_community_call_youtube_thumbnail_id( $post->ID )
		: (int) get_post_meta( $post->ID, 'youtube_thumbnail', true );
	$social_id  = function_exists( 'ftd_get_community_call_social_share_thumbnail_id' )
		? ftd_get_community_call_social_share_thumbnail_id( $post->ID )
		: (int) get_post_meta( $post->ID, 'social_share_thumbnail', true );
	$yt_preview = ftd_get_gnls_cta_capture_preview_url( $post->ID, 'youtube' );
	$og_preview = ftd_get_gnls_cta_capture_preview_url( $post->ID, 'social' );
	$yt_nonce   = wp_create_nonce( 'ftd_gnls_cta_capture_' . $post->ID );
	$og_nonce   = wp_create_nonce( 'ftd_gnls_social_capture_' . $post->ID );
	$steps      = array(
		array(
			'postId'        => (int) $post->ID,
			'previewUrl'    => $og_preview,
			'nonce'         => $og_nonce,
			'captureAction' => 'ftd_save_gnls_social_thumbnail',
			'renderWidth'   => (int) FTD_GNLS_SOCIAL_WIDTH,
			'renderHeight'  => (int) FTD_GNLS_SOCIAL_HEIGHT,
			'cardSelector'  => '.gnls-cta--capture-social',
			'previewTarget' => '.ftd-gnls-social-thumb-preview',
			'workingLabel'  => __( 'Generating social image…', 'ftd-directory-listings' ),
		),
		array(
			'postId'        => (int) $post->ID,
			'previewUrl'    => $yt_preview,
			'nonce'         => $yt_nonce,
			'captureAction' => 'ftd_save_gnls_cta_thumbnail',
			'renderWidth'   => (int) FTD_GNLS_CAPTURE_RENDER_WIDTH,
			'renderHeight'  => (int) FTD_GNLS_CAPTURE_RENDER_HEIGHT,
			'cardSelector'  => '.gnls-cta--capture-youtube',
			'previewTarget' => '.ftd-gnls-youtube-thumb-preview',
			'workingLabel'  => __( 'Generating YouTube thumbnail…', 'ftd-directory-listings' ),
		),
	);
	?>
	<p class="description">
		<?php esc_html_e( 'Builds social (1200×630) and YouTube (1280×720) images from the featured image and session details.', 'ftd-directory-listings' ); ?>
	</p>

	<div class="ftd-gnls-promo-previews">
		<?php if ( $social_id ) : ?>
			<p class="ftd-gnls-social-thumb-preview">
				<strong><?php esc_html_e( 'Social', 'ftd-directory-listings' ); ?></strong><br>
				<?php echo wp_get_attachment_image( $social_id, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		<?php endif; ?>

		<?php if ( $youtube_id ) : ?>
			<p class="ftd-gnls-youtube-thumb-preview">
				<strong><?php esc_html_e( 'YouTube', 'ftd-directory-listings' ); ?></strong><br>
				<?php echo wp_get_attachment_image( $youtube_id, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		<?php endif; ?>
	</div>

	<p>
		<button
			type="button"
			class="button button-primary"
			id="ftd-gnls-generate-promo-btn"
			data-steps="<?php echo esc_attr( wp_json_encode( $steps ) ); ?>"
		>
			<?php esc_html_e( 'Generate promo images', 'ftd-directory-listings' ); ?>
		</button>
	</p>
	<p class="description" id="ftd-gnls-capture-status" aria-live="polite"></p>
	<iframe id="ftd-gnls-cta-capture-frame" title="<?php esc_attr_e( 'Promo card capture preview', 'ftd-directory-listings' ); ?>" style="position:absolute;left:-9999px;width:1280px;height:720px;border:0;" tabindex="-1" aria-hidden="true"></iframe>
	<?php
}

/**
 * Preview URL for capture iframe.
 *
 * @param int    $post_id Post ID.
 * @param string $type    Capture type: youtube or social.
 * @return string
 */
function ftd_get_gnls_cta_capture_preview_url( $post_id, $type = 'youtube' ) {
	$type = ( 'social' === $type ) ? 'social' : 'youtube';

	return add_query_arg(
		array(
			'gnls_cta_preview' => 1,
			'capture_type'     => $type,
			'post_id'          => (int) $post_id,
			'gnls_nonce'       => wp_create_nonce( 'ftd_gnls_cta_preview_' . (int) $post_id ),
		),
		home_url( '/' )
	);
}

/**
 * Enqueue capture scripts on session edit screen.
 *
 * @param string $hook Current admin page hook.
 */
function ftd_enqueue_gnls_cta_capture_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || FTD_COMMUNITY_CALL_POST_TYPE !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'ftd-gnls-session-cta',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/gnls-session-cta.css',
		array( 'directory-listings-style' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	wp_enqueue_style(
		'ftd-gnls-session-cta-poster-capture',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/gnls-session-cta-poster-capture.css',
		array( 'ftd-gnls-session-cta' ),
		FTD_DIRECTORY_LISTINGS_VERSION
	);

	wp_enqueue_script(
		'html2canvas',
		'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js',
		array(),
		'1.4.1',
		true
	);

	wp_enqueue_script(
		'ftd-gnls-cta-capture',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/gnls-session-cta-capture.js',
		array( 'html2canvas', 'jquery' ),
		FTD_DIRECTORY_LISTINGS_VERSION,
		true
	);

	wp_add_inline_script(
		'ftd-gnls-cta-capture',
		'window.ftdGnlsCtaCapture = window.ftdGnlsCtaCapture || {};'
		. 'window.ftdGnlsCtaCapture.ajaxUrl = ' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';'
		. 'window.ftdGnlsCtaCapture.i18n = ' . wp_json_encode(
			array(
				'working' => __( 'Generating images…', 'ftd-directory-listings' ),
				'done'    => __( 'Social and YouTube images saved.', 'ftd-directory-listings' ),
				'error'   => __( 'Could not generate images. Try again.', 'ftd-directory-listings' ),
			)
		) . ';',
		'before'
	);
}

/**
 * Minimal front-end preview page for html2canvas.
 */
function ftd_handle_gnls_cta_capture_preview() {
	if ( empty( $_GET['gnls_cta_preview'] ) || empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id = (int) $_GET['post_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce   = isset( $_GET['gnls_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['gnls_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! wp_verify_nonce( $nonce, 'ftd_gnls_cta_preview_' . $post_id ) ) {
		wp_die( esc_html__( 'Invalid preview link.', 'ftd-directory-listings' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You cannot preview this session.', 'ftd-directory-listings' ) );
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || FTD_COMMUNITY_CALL_POST_TYPE !== $post->post_type ) {
		wp_die( esc_html__( 'Session not found.', 'ftd-directory-listings' ) );
	}

	if ( ! function_exists( 'ftd_render_gnls_session_cta' ) ) {
		wp_die( esc_html__( 'CTA renderer unavailable.', 'ftd-directory-listings' ) );
	}

	$capture_type = isset( $_GET['capture_type'] ) ? sanitize_key( wp_unslash( $_GET['capture_type'] ) ) : 'youtube'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$is_social    = ( 'social' === $capture_type );

	$cta = ftd_render_gnls_session_cta(
		$post_id,
		array(
			'capture' => $is_social ? 'social' : '1',
		)
	);

	$css_base  = plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/';
	$ver       = FTD_DIRECTORY_LISTINGS_VERSION;
	$fonts_css = ftd_get_gnls_capture_fonts_css();
	$inline_css = ftd_get_gnls_capture_inline_styles();
	$width     = $is_social ? FTD_GNLS_SOCIAL_WIDTH : FTD_GNLS_CAPTURE_RENDER_WIDTH;
	$height    = $is_social ? FTD_GNLS_SOCIAL_HEIGHT : FTD_GNLS_CAPTURE_RENDER_HEIGHT;
	$body_class = $is_social ? 'gnls-social-capture-preview' : 'gnls-cta-capture-preview';
	$shell_class = $is_social ? 'gnls-cta-capture-shell gnls-cta-capture-shell--social' : 'gnls-cta-capture-shell';

	status_header( 200 );
	nocache_headers();

	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=<?php echo (int) $width; ?>">
	<?php if ( '' !== $fonts_css ) : ?>
	<style><?php echo $fonts_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Google Fonts CSS. ?></style>
	<?php else : ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&family=Roboto:wght@400;700&display=block">
	<?php endif; ?>
	<?php if ( '' !== $inline_css ) : ?>
	<style><?php echo $inline_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin CSS. ?></style>
	<?php else : ?>
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'directory-listings.css?ver=' . $ver ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'gnls-session-cta.css?ver=' . $ver ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'gnls-session-cta-poster-capture.css?ver=' . $ver ); ?>">
	<?php endif; ?>
	<style><?php echo ftd_get_gnls_capture_layout_styles( $width, $height ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline layout CSS. ?></style>
</head>
<body class="<?php echo esc_attr( $body_class ); ?>">
	<div class="<?php echo esc_attr( $shell_class ); ?>" id="gnls-cta-capture-target">
		<?php echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
	</div>
</body>
</html>
	<?php
	exit;
}

/**
 * Save captured PNG via AJAX.
 */
function ftd_ajax_save_gnls_cta_thumbnail() {
	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( $post_id <= 0 || ! wp_verify_nonce( $nonce, 'ftd_gnls_cta_capture_' . $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'ftd-directory-listings' ) ), 403 );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ftd-directory-listings' ) ), 403 );
	}

	$data_url = isset( $_POST['image'] ) ? (string) wp_unslash( $_POST['image'] ) : '';
	if ( ! preg_match( '#^data:image/png;base64,#', $data_url ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid image data.', 'ftd-directory-listings' ) ), 400 );
	}

	$binary = base64_decode( substr( $data_url, strlen( 'data:image/png;base64,' ) ), true );
	if ( false === $binary || '' === $binary ) {
		wp_send_json_error( array( 'message' => __( 'Could not decode image.', 'ftd-directory-listings' ) ), 400 );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = 'gnls-youtube-' . $post_id . '-' . gmdate( 'Ymd-His' ) . '.png';
	$upload   = wp_upload_bits( $filename, null, $binary );

	if ( ! empty( $upload['error'] ) ) {
		wp_send_json_error( array( 'message' => $upload['error'] ), 500 );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => sanitize_file_name( get_the_title( $post_id ) . ' YouTube thumbnail' ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		wp_send_json_error( array( 'message' => __( 'Could not save attachment.', 'ftd-directory-listings' ) ), 500 );
	}

	wp_generate_attachment_metadata( $attachment_id, $upload['file'] );

	update_post_meta( $post_id, 'youtube_thumbnail', $attachment_id );

	$thumb_url = wp_get_attachment_image_url( $attachment_id, 'full' );

	wp_send_json_success(
		array(
			'attachment_id' => $attachment_id,
			'url'           => $thumb_url,
			'message'       => __( 'YouTube thumbnail saved.', 'ftd-directory-listings' ),
		)
	);
}

/**
 * Save captured social share PNG via AJAX.
 */
function ftd_ajax_save_gnls_social_thumbnail() {
	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( $post_id <= 0 || ! wp_verify_nonce( $nonce, 'ftd_gnls_social_capture_' . $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'ftd-directory-listings' ) ), 403 );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ftd-directory-listings' ) ), 403 );
	}

	$data_url = isset( $_POST['image'] ) ? (string) wp_unslash( $_POST['image'] ) : '';
	if ( ! preg_match( '#^data:image/png;base64,#', $data_url ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid image data.', 'ftd-directory-listings' ) ), 400 );
	}

	$binary = base64_decode( substr( $data_url, strlen( 'data:image/png;base64,' ) ), true );
	if ( false === $binary || '' === $binary ) {
		wp_send_json_error( array( 'message' => __( 'Could not decode image.', 'ftd-directory-listings' ) ), 400 );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = 'gnls-social-' . $post_id . '-' . gmdate( 'Ymd-His' ) . '.png';
	$upload   = wp_upload_bits( $filename, null, $binary );

	if ( ! empty( $upload['error'] ) ) {
		wp_send_json_error( array( 'message' => $upload['error'] ), 500 );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => sanitize_file_name( get_the_title( $post_id ) . ' Social share image' ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		wp_send_json_error( array( 'message' => __( 'Could not save attachment.', 'ftd-directory-listings' ) ), 500 );
	}

	wp_generate_attachment_metadata( $attachment_id, $upload['file'] );

	update_post_meta( $post_id, 'social_share_thumbnail', $attachment_id );

	$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );

	wp_send_json_success(
		array(
			'attachment_id' => $attachment_id,
			'url'           => $thumb_url,
			'message'       => __( 'Social share image saved.', 'ftd-directory-listings' ),
		)
	);
}
