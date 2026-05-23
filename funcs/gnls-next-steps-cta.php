<?php
/**
 * Next steps CTA for Genius Network Live Sessions (member vs guest messaging).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'gnls_next_steps_cta', 'ftd_gnls_next_steps_cta_shortcode' );

/**
 * Default copy for the next steps CTA.
 *
 * @return array<string, string>
 */
function ftd_get_gnls_next_steps_defaults() {
	return array(
		'eyebrow'              => 'Next steps',
		'member_heading'       => 'Genius {name}, you\'re on the list!',
		'member_body'          => 'A Zoom link will be sent to your inbox before the call.',
		'guest_heading'        => 'Want to join us live?',
		'guest_body'           => 'Join the Genius Directory to receive your meeting invite for the call.',
		'guest_button_label'   => 'Join the directory',
		'guest_button_url'     => '',
	);
}

/**
 * Read next steps settings from the archive options page.
 *
 * @return array<string, string>
 */
function ftd_get_gnls_next_steps_settings() {
	$defaults = ftd_get_gnls_next_steps_defaults();
	$settings = array();

	foreach ( array_keys( $defaults ) as $key ) {
		$field = 'next_steps_' . $key;
		$value = function_exists( 'ftd_get_gnls_archive_field' )
			? ftd_get_gnls_archive_field( $field )
			: null;

		if ( is_string( $value ) && '' !== trim( $value ) ) {
			$settings[ $key ] = trim( $value );
		} else {
			$settings[ $key ] = (string) $defaults[ $key ];
		}
	}

	if ( '' === $settings['guest_button_url'] && function_exists( 'ftd_get_gnls_directory_join_url' ) ) {
		$settings['guest_button_url'] = ftd_get_gnls_directory_join_url();
	}

	return $settings;
}

/**
 * First name for personalised member messaging.
 *
 * @param int $user_id Optional user ID.
 * @return string
 */
function ftd_get_gnls_next_steps_member_first_name( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return '';
	}

	$first = trim( (string) get_user_meta( $user_id, 'first_name', true ) );

	if ( '' === $first ) {
		$user = get_userdata( $user_id );

		if ( $user instanceof WP_User ) {
			$first = trim( (string) $user->first_name );

			if ( '' === $first && $user->display_name ) {
				$parts = preg_split( '/\s+/', trim( $user->display_name ), 2, PREG_SPLIT_NO_EMPTY );
				$first = $parts[0] ?? '';
			}
		}
	}

	return $first;
}

/**
 * Build member heading HTML with {name} highlighted.
 *
 * @param string $template Heading template from ACF.
 * @param int    $user_id  Optional user ID.
 * @return string
 */
function ftd_format_gnls_next_steps_member_heading( $template, $user_id = 0 ) {
	$template = trim( (string) $template );
	$name     = ftd_get_gnls_next_steps_member_first_name( $user_id );

	if ( '' === $template ) {
		return '';
	}

	if ( false === strpos( $template, '{name}' ) ) {
		return esc_html( $template );
	}

	if ( '' === $name ) {
		$fallback = str_replace(
			array( '{name}, ', ', {name}', ' {name}', '{name}' ),
			'',
			$template
		);

		return esc_html( trim( preg_replace( '/\s+/', ' ', $fallback ) ) );
	}

	$parts = explode( '{name}', $template, 2 );

	if ( 2 !== count( $parts ) ) {
		return esc_html( $template );
	}

	$highlight = '<span class="gcc-next-steps-name">' . esc_html( $name ) . '</span>';

	return esc_html( $parts[0] ) . $highlight . esc_html( $parts[1] );
}

/**
 * Render the next steps CTA block.
 *
 * @return string
 */
function ftd_render_gnls_next_steps_cta() {
	$settings  = ftd_get_gnls_next_steps_settings();
	$is_member = function_exists( 'ftd_community_call_user_is_member' ) && ftd_community_call_user_is_member();

	if ( $is_member ) {
		$heading     = ftd_format_gnls_next_steps_member_heading( $settings['member_heading'] );
		$body        = $settings['member_body'];
		$show_button = false;
	} else {
		$heading     = esc_html( $settings['guest_heading'] );
		$body        = $settings['guest_body'];
		$show_button = '' !== trim( $settings['guest_button_label'] ) && '' !== trim( $settings['guest_button_url'] );
	}

	if ( '' === trim( $heading ) && '' === trim( $body ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="gcc-next-steps card" aria-labelledby="gcc-next-steps-heading">
		<?php if ( $settings['eyebrow'] ) : ?>
			<p class="gcc-next-steps-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 id="gcc-next-steps-heading" class="gcc-next-steps-heading"><?php echo wp_kses( $heading, array( 'span' => array( 'class' => true ) ) ); ?></h2>
		<?php endif; ?>

		<?php if ( $body ) : ?>
			<div class="gcc-next-steps-body">
				<?php echo wp_kses_post( wpautop( $body ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! $is_member && $show_button ) : ?>
			<p class="gcc-next-steps-button-wrap">
				<a class="btn gcc-next-steps-button" href="<?php echo esc_url( $settings['guest_button_url'] ); ?>">
					<span class="gcc-next-steps-button-icon ftd-btn-arrow" aria-hidden="true"></span>
					<span><?php echo esc_html( $settings['guest_button_label'] ); ?></span>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param array<string, mixed> $atts Shortcode attributes (unused).
 * @return string
 */
function ftd_gnls_next_steps_cta_shortcode( $atts ) {
	unset( $atts );

	return ftd_render_gnls_next_steps_cta();
}
