<?php
/**
 * Shortcode: [WAG_Features_CTAS]
 *
 * Four feature CTAs in one row. Content: Genius Directory Settings (ACF).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default copy and style per card slot.
 *
 * @return array<int, array<string, string>>
 */
function ftd_wag_features_cta_defaults() {
	return array(
		1 => array(
			'title'       => 'Genius directory',
			'description' => 'Easily find advanced Joe Dispenza students based on their expertise!',
			'style'       => 'sunset',
		),
		2 => array(
			'title'       => 'Genius map',
			'description' => 'Zoom in on Geniuses in your area or worldwide!',
			'style'       => 'magenta',
		),
		3 => array(
			'title'       => 'Share your genius',
			'description' => 'Let others connect with you by location or skills!',
			'style'       => 'coral',
		),
		4 => array(
			'title'       => 'Genius Network Call',
			'description' => "Let's raise each other up professionally!",
			'style'       => 'plum',
		),
	);
}

/**
 * @param string $field ACF field name.
 * @return mixed
 */
function ftd_wag_features_get_field( $field ) {
	if ( function_exists( 'ftd_genius_directory_settings_get_field' ) ) {
		return ftd_genius_directory_settings_get_field( $field );
	}

	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	return get_field( $field, 'options' );
}

/**
 * @param mixed $image ACF image array or ID.
 * @return array{url: string, alt: string}
 */
function ftd_wag_features_normalize_image( $image ) {
	$url = '';
	$alt = '';

	if ( is_array( $image ) ) {
		$id = (int) ( $image['ID'] ?? $image['id'] ?? 0 );
		if ( $id ) {
			$url = wp_get_attachment_image_url( $id, 'large' )
				?: wp_get_attachment_image_url( $id, 'medium_large' )
				?: wp_get_attachment_image_url( $id, 'full' )
				?: '';
			$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		}
		if ( '' === $url ) {
			$url = $image['sizes']['large'] ?? $image['sizes']['medium_large'] ?? $image['url'] ?? '';
		}
		if ( '' === $alt ) {
			$alt = $image['alt'] ?? '';
		}
	} elseif ( is_numeric( $image ) ) {
		$id  = (int) $image;
		$url = wp_get_attachment_image_url( $id, 'large' )
			?: wp_get_attachment_image_url( $id, 'medium_large' )
			?: wp_get_attachment_image_url( $id, 'full' )
			?: '';
		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	} elseif ( is_string( $image ) && '' !== $image ) {
		if ( is_numeric( $image ) ) {
			return ftd_wag_features_normalize_image( (int) $image );
		}
		$url = $image;
	}

	return array(
		'url' => $url,
		'alt' => $alt,
	);
}

/**
 * @param mixed $link ACF link array.
 * @return array{url: string, label: string, target: string}
 */
function ftd_wag_features_normalize_link( $link ) {
	if ( ! is_array( $link ) ) {
		return array(
			'url'    => '',
			'label'  => '',
			'target' => '',
		);
	}

	return array(
		'url'    => $link['url'] ?? '',
		'label'  => $link['title'] ?? '',
		'target' => ! empty( $link['target'] ) ? $link['target'] : '',
	);
}

/**
 * Load feature CTA group from ACF (current + legacy field names).
 *
 * @param int $number Card slot 1–4.
 * @return array<string, mixed>
 */
function ftd_wag_features_get_feature_cta_group( $number ) {
	$group = ftd_wag_features_get_field( "feature_cta_{$number}" );
	if ( is_array( $group ) && ! empty( $group ) ) {
		return $group;
	}

	// Legacy call-centric panels (pre–feature_cta_* rename).
	$legacy = ftd_wag_features_get_field( "wagfc_panel_{$number}" );
	if ( is_array( $legacy ) && ! empty( $legacy ) ) {
		return array(
			'image'          => $legacy['image'] ?? null,
			'small_label'    => $legacy['label'] ?? '',
			'title'          => $legacy['heading'] ?? $legacy['event_title'] ?? '',
			'description'    => $legacy['subtext'] ?? $legacy['description'] ?? '',
			'link'           => $legacy['link'] ?? null,
			'feature_link'   => null,
			'style'          => '',
			'button_label'   => '',
		);
	}

	return array();
}

/**
 * @param int $number Card slot 1–4.
 * @return array<string, mixed>
 */
function ftd_wag_features_get_feature_cta( $number ) {
	$defaults_all = ftd_wag_features_cta_defaults();
	$defaults     = $defaults_all[ $number ] ?? array();

	$group = ftd_wag_features_get_feature_cta_group( $number );

	$image   = ftd_wag_features_normalize_image( $group['image'] ?? null );
	$link    = ftd_wag_features_normalize_link( $group['link'] ?? null );
	$feature = ftd_wag_features_normalize_link( $group['feature_link'] ?? $group['secondary_link'] ?? null );

	$style = trim( (string) ( $group['style'] ?? '' ) );
	if ( '' === $style || ! array_key_exists( $style, ftd_feature_cta_style_choices() ) ) {
		$style = $defaults['style'] ?? 'magenta';
	}

	$title = trim( (string) ( $group['title'] ?? '' ) );
	if ( '' === $title ) {
		$title = $defaults['title'] ?? '';
	}

	$description = trim( (string) ( $group['description'] ?? '' ) );
	if ( '' === $description ) {
		$description = $defaults['description'] ?? '';
	}

	$button_label = trim( (string) ( $group['button_label'] ?? '' ) );
	if ( '' === $button_label ) {
		$button_label = __( 'Read more', 'ftd-directory-listings' );
	}

	$feature_label = $feature['label'];
	if ( '' === $feature_label && ( 1 === $number || 2 === $number ) ) {
		$feature_label = __( 'Levels & Prices', 'ftd-directory-listings' );
	}

	return array(
		'number'            => $number,
		'style'             => $style,
		'image_url'         => $image['url'],
		'image_alt'         => $image['alt'],
		'small_label'       => trim( (string) ( $group['small_label'] ?? '' ) ),
		'title'             => $title,
		'description'       => $description,
		'link_url'          => $link['url'],
		'link_label'        => $link['label'] ?: $button_label,
		'link_target'       => $link['target'],
		'button_label'      => $button_label,
		'feature_url'       => $feature['url'],
		'feature_label'     => $feature_label,
		'feature_target'    => $feature['target'],
	);
}

/**
 * @return array<int, array<string, mixed>>
 */
function ftd_get_wag_features_cta_panels() {
	$panels = array();

	for ( $i = 1; $i <= 4; $i++ ) {
		$panels[] = ftd_wag_features_get_feature_cta( $i );
	}

	return $panels;
}

/**
 * @param array<string, mixed> $panel Panel data.
 * @return void
 */
function ftd_render_wag_features_cta_panel( $panel ) {
	$style = esc_attr( $panel['style'] ?? 'magenta' );

	$feature_target_attr = ! empty( $panel['feature_target'] )
		? ' target="' . esc_attr( $panel['feature_target'] ) . '" rel="noopener noreferrer"'
		: '';
	?>
	<article class="wagfc-panel wagfc-panel--style-<?php echo $style; ?>">
		<div class="wagfc-panel-media">
			<?php if ( ! empty( $panel['feature_url'] ) ) : ?>
				<a
					class="wagfc-panel-media-link"
					href="<?php echo esc_url( $panel['feature_url'] ); ?>"
					<?php echo $feature_target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
			<?php endif; ?>

			<?php if ( ! empty( $panel['image_url'] ) ) : ?>
				<img
					class="wagfc-panel-image"
					src="<?php echo esc_url( $panel['image_url'] ); ?>"
					alt="<?php echo esc_attr( $panel['image_alt'] ?: $panel['title'] ); ?>"
					loading="lazy"
					decoding="async"
				/>
			<?php else : ?>
				<div class="wagfc-panel-visual" aria-hidden="true"></div>
			<?php endif; ?>

			<?php if ( ! empty( $panel['feature_url'] ) ) : ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $panel['small_label'] ) ) : ?>
				<span class="wagfc-badge"><?php echo esc_html( $panel['small_label'] ); ?></span>
			<?php endif; ?>
		</div>

		<div class="wagfc-panel-body">
			<?php if ( ! empty( $panel['title'] ) ) : ?>
				<h3 class="wagfc-panel-title">
					<?php if ( ! empty( $panel['feature_url'] ) ) : ?>
						<a
							class="wagfc-panel-title-link"
							href="<?php echo esc_url( $panel['feature_url'] ); ?>"
							<?php echo $feature_target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						>
							<?php echo esc_html( $panel['title'] ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $panel['title'] ); ?>
					<?php endif; ?>
				</h3>
			<?php endif; ?>

			<?php if ( ! empty( $panel['description'] ) ) : ?>
				<p class="wagfc-panel-description"><?php echo esc_html( $panel['description'] ); ?></p>
			<?php endif; ?>

			<div class="wagfc-panel-actions">
				<?php if ( ! empty( $panel['link_url'] ) ) : ?>
					<a
						class="wagfc-btn-read-more"
						href="<?php echo esc_url( $panel['link_url'] ); ?>"
						<?php echo ! empty( $panel['link_target'] ) ? ' target="' . esc_attr( $panel['link_target'] ) . '" rel="noopener noreferrer"' : ''; ?>
					>
						<span class="wagfc-btn-icon ftd-btn-arrow" aria-hidden="true"></span>
						<span class="wagfc-btn-text"><?php echo esc_html( $panel['link_label'] ?: $panel['button_label'] ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * @return string
 */
function ftd_wag_features_ctas_shortcode() {
	$panels = ftd_get_wag_features_cta_panels();

	wp_enqueue_style( 'ftd-sc-wag-features-ctas' );

	ob_start();
	?>
	<div class="ftd-sc ftd-sc--wag-features-ctas">
		<section class="wagfc-wrap" aria-label="<?php echo esc_attr__( 'We Are Geniuses feature CTAs', 'ftd-directory-listings' ); ?>">
			<div class="wagfc-row">
				<?php foreach ( $panels as $panel ) : ?>
					<?php ftd_render_wag_features_cta_panel( $panel ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'WAG_Features_CTAS', 'ftd_wag_features_ctas_shortcode' );
add_shortcode( 'genius_calls', 'ftd_wag_features_ctas_shortcode' );
