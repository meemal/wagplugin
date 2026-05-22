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

/**
 * Map a public image URL to a local filesystem path when possible.
 *
 * @param string $url Image URL.
 * @return string
 */
function ftd_gnls_capture_url_to_path( $url ) {
	$map = array(
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE )  => plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ),
		content_url()                                 => WP_CONTENT_DIR,
		site_url()                                    => ABSPATH,
	);

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
			'timeout'   => 15,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
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
 * Meta box on live session edit screen.
 */
function ftd_register_gnls_cta_capture_meta_box() {
	add_meta_box(
		'ftd_gnls_cta_capture',
		__( 'YouTube thumbnail', 'ftd-directory-listings' ),
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
	$thumb_id  = function_exists( 'get_field' ) ? (int) get_field( 'youtube_thumbnail', $post->ID ) : 0;
	$preview   = ftd_get_gnls_cta_capture_preview_url( $post->ID );
	$nonce     = wp_create_nonce( 'ftd_gnls_cta_capture_' . $post->ID );
	?>
	<p class="description">
		<?php esc_html_e( 'Generates a 1280×720 PNG from the promo card layout. Saved separately from the sidebar featured image (used as the card artwork).', 'ftd-directory-listings' ); ?>
	</p>

	<?php if ( $thumb_id ) : ?>
		<p>
			<?php echo wp_get_attachment_image( $thumb_id, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	<?php endif; ?>

	<p>
		<button type="button" class="button button-secondary" id="ftd-gnls-cta-capture-btn" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-preview-url="<?php echo esc_url( $preview ); ?>">
			<?php esc_html_e( 'Generate YouTube thumbnail', 'ftd-directory-listings' ); ?>
		</button>
	</p>
	<p class="description" id="ftd-gnls-cta-capture-status" aria-live="polite"></p>
	<iframe id="ftd-gnls-cta-capture-frame" title="<?php esc_attr_e( 'Promo card capture preview', 'ftd-directory-listings' ); ?>" style="position:absolute;left:-9999px;width:1280px;height:720px;border:0;" tabindex="-1" aria-hidden="true"></iframe>
	<?php
}

/**
 * Preview URL for capture iframe.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_gnls_cta_capture_preview_url( $post_id ) {
	return add_query_arg(
		array(
			'gnls_cta_preview' => 1,
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
		'ftd-gnls-session-cta-capture',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/gnls-session-cta-capture.css',
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

	wp_localize_script(
		'ftd-gnls-cta-capture',
		'ftdGnlsCtaCapture',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => array(
				'working' => __( 'Generating thumbnail…', 'ftd-directory-listings' ),
				'done'    => __( 'YouTube thumbnail saved.', 'ftd-directory-listings' ),
				'error'   => __( 'Could not generate thumbnail. Try again.', 'ftd-directory-listings' ),
			),
		)
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

	$cta = ftd_render_gnls_session_cta(
		$post_id,
		array(
			'show_button' => '0',
			'capture'     => '1',
		)
	);

	$css_base = plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/';
	$ver      = FTD_DIRECTORY_LISTINGS_VERSION;

	status_header( 200 );
	nocache_headers();

	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=1280">
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'directory-listings.css?ver=' . $ver ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'gnls-session-cta.css?ver=' . $ver ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $css_base . 'gnls-session-cta-capture.css?ver=' . $ver ); ?>">
	<style>
		html, body {
			margin: 0;
			padding: 0;
			background: #111;
			width: <?php echo (int) FTD_GNLS_YOUTUBE_WIDTH; ?>px;
			height: <?php echo (int) FTD_GNLS_YOUTUBE_HEIGHT; ?>px;
			overflow: hidden;
		}
	</style>
</head>
<body class="gnls-cta-capture-preview">
	<div class="gnls-cta-capture-shell" id="gnls-cta-capture-target">
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

	if ( function_exists( 'update_field' ) ) {
		update_field( 'youtube_thumbnail', $attachment_id, $post_id );
	} else {
		update_post_meta( $post_id, 'youtube_thumbnail', $attachment_id );
	}

	$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );

	wp_send_json_success(
		array(
			'attachment_id' => $attachment_id,
			'url'           => $thumb_url,
			'message'       => __( 'YouTube thumbnail saved.', 'ftd-directory-listings' ),
		)
	);
}
