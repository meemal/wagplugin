<?php
/**
 * Helpers for Genius Network Live Sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plural front-end / admin label.
 *
 * @return string
 */
function ftd_gnls_label_plural() {
	return __( 'Genius Network Live Sessions', 'ftd-directory-listings' );
}

/**
 * Singular label.
 *
 * @return string
 */
function ftd_gnls_label_singular() {
	return __( 'Genius Network Live Session', 'ftd-directory-listings' );
}

/**
 * Uppercase kicker used on cards and single pages.
 *
 * @return string
 */
function ftd_gnls_kicker() {
	return __( 'GENIUS NETWORK LIVE SESSIONS', 'ftd-directory-listings' );
}

/**
 * Join / signup URL for directory CTAs (from banner settings).
 *
 * @return string
 */
function ftd_get_gnls_directory_join_url() {
	if ( function_exists( 'ftd_get_founding_spots_banner_settings' ) ) {
		$settings = ftd_get_founding_spots_banner_settings();

		if ( ! empty( $settings['link_url'] ) ) {
			return (string) $settings['link_url'];
		}
	}

	return home_url( '/join-we-are-geniuses/' );
}

/**
 * Directory join CTA button.
 *
 * @param string $label Button label.
 * @param string $class Extra CSS classes.
 * @return string
 */
function ftd_render_gnls_directory_join_cta( $label, $class = '' ) {
	$url = ftd_get_gnls_directory_join_url();

	if ( ! $url ) {
		return '';
	}

	$classes = trim( 'btn gcc-archive-cta ' . $class );

	return sprintf(
		'<p class="gcc-archive-cta-wrap"><a class="%1$s" href="%2$s">%3$s</a></p>',
		esc_attr( $classes ),
		esc_url( $url ),
		esc_html( $label )
	);
}

/**
 * Share buttons for the live sessions archive page.
 *
 * @return string
 */
function ftd_render_gnls_archive_share_buttons() {
	$url = get_post_type_archive_link( FTD_COMMUNITY_CALL_POST_TYPE );

	if ( ! $url || ! function_exists( 'ftd_social_share_icon_svg' ) ) {
		return '';
	}

	$title = ftd_gnls_label_plural();
	$text  = __(
		'Genius Network Live Sessions — regular calls where advanced Joe Dispenza students connect. Come and meet the people in the directory.',
		'ftd-directory-listings'
	);
	$body  = $text . "\n\n" . $url;

	$urls = array(
		'twitter'  => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $body ),
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) . '&quote=' . rawurlencode( $text ),
		'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ),
		'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $body ),
		'email'    => 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $body ),
	);

	wp_enqueue_style( 'ftd-sc-social-share' );
	wp_enqueue_script( 'ftd-live-session-share' );

	ob_start();
	?>
	<div class="gcc-share gcc-share--archive">
		<div class="gcc-share-row">
			<a class="ftd-ss-pill ftd-ss-pill--twitter" href="<?php echo esc_url( $urls['twitter'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'twitter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">X / Twitter</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--facebook" href="<?php echo esc_url( $urls['facebook'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">Facebook</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--linkedin" href="<?php echo esc_url( $urls['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'linkedin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">LinkedIn</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--whatsapp" href="<?php echo esc_url( $urls['whatsapp'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">WhatsApp</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--email" href="<?php echo esc_url( $urls['email'] ); ?>">
				<span class="ftd-ss-pill-icon" aria-hidden="true">✉</span>
				<span class="ftd-ss-pill-label"><?php esc_html_e( 'Email', 'ftd-directory-listings' ); ?></span>
			</a>
			<button type="button" class="ftd-ss-pill ftd-ss-pill--copy gcc-share-copy" data-copy-url="<?php echo esc_attr( $url ); ?>">
				<span class="ftd-ss-pill-icon ftd-ss-pill-icon--copy" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label"><?php esc_html_e( 'Copy link', 'ftd-directory-listings' ); ?></span>
			</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Marketing intro and explainer sections for the archive page.
 *
 * @return void
 */
function ftd_render_gnls_archive_intro() {
	$settings = function_exists( 'ftd_get_gnls_archive_settings' )
		? ftd_get_gnls_archive_settings()
		: array();

	$eyebrow   = $settings['intro_eyebrow'] ?? '';
	$title     = $settings['intro_title'] ?? '';
	$tagline   = $settings['intro_tagline'] ?? '';
	$col1_title = $settings['intro_col1_title'] ?? '';
	$col1_body  = function_exists( 'ftd_format_gnls_archive_content' )
		? ftd_format_gnls_archive_content( $settings['intro_col1_content'] ?? '' )
		: '';
	$col1_highlights = function_exists( 'ftd_get_gnls_archive_col1_highlights' )
		? ftd_get_gnls_archive_col1_highlights()
		: array();
	$col2_title = $settings['intro_col2_title'] ?? '';
	$col2_body  = function_exists( 'ftd_format_gnls_archive_content' )
		? ftd_format_gnls_archive_content( $settings['intro_col2_content'] ?? '' )
		: '';

	if ( '' === trim( $title ) && '' === $col1_body && '' === $col2_body && empty( $col1_highlights ) ) {
		return;
	}
	?>
	<section class="gcc-archive-intro" aria-labelledby="gcc-archive-intro-title">
		<div class="gcc-archive-intro-inner">
			<?php if ( $eyebrow || $title || $tagline ) : ?>
				<header class="gcc-archive-intro-header">
					<?php if ( $eyebrow ) : ?>
						<p class="gcc-archive-intro-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<?php endif; ?>

					<?php if ( $title ) : ?>
						<h2 id="gcc-archive-intro-title" class="gcc-archive-intro-title text-purple"><?php echo esc_html( $title ); ?></h2>
					<?php endif; ?>

					<?php if ( $tagline ) : ?>
						<p class="gcc-archive-intro-tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>

					<div class="gcc-archive-intro-divider" aria-hidden="true"><span>♡</span></div>
				</header>
			<?php endif; ?>

			<?php if ( $col1_body || $col2_body ) : ?>
				<div class="gcc-archive-intro-columns">
					<?php if ( $col1_body ) : ?>
						<div class="gcc-archive-intro-col">
							<?php if ( $col1_title ) : ?>
								<h3 class="gcc-archive-intro-col-title text-purple"><?php echo esc_html( $col1_title ); ?></h3>
							<?php endif; ?>
							<div class="gcc-archive-intro-prose entry-content">
								<?php echo $col1_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in helper. ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $col2_body ) : ?>
						<div class="gcc-archive-intro-col">
							<?php if ( $col2_title ) : ?>
								<h3 class="gcc-archive-intro-col-title text-purple"><?php echo esc_html( $col2_title ); ?></h3>
							<?php endif; ?>
							<div class="gcc-archive-intro-prose entry-content">
								<?php echo $col2_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in helper. ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $col1_highlights ) ) : ?>
				<div class="gcc-archive-intro-highlights-wrap">
					<ul class="gcc-archive-intro-highlights">
						<?php foreach ( $col1_highlights as $highlight ) : ?>
							<li class="gcc-archive-intro-highlight">
								<span class="gcc-archive-intro-highlight-marker" aria-hidden="true"></span>
								<span class="gcc-archive-intro-highlight-text"><?php echo esc_html( $highlight['text'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Closing join CTA for the archive page.
 *
 * @return void
 */
function ftd_render_gnls_archive_outro() {
	if ( function_exists( 'ftd_render_gnls_join_cta' ) ) {
		echo ftd_render_gnls_join_cta(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
	}
}

/**
 * Read an ACF/meta field with legacy fallbacks.
 *
 * @param string   $field   Primary field name.
 * @param int      $post_id Post ID.
 * @param string[] $legacy  Legacy field names.
 * @return mixed
 */
function ftd_get_community_call_field( $field, $post_id = 0, $legacy = array() ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( 'profile_members' === $field ) {
		$value = null;

		if ( function_exists( 'get_field' ) ) {
			$value = get_field( $field, $post_id );

			if ( is_array( $value ) && ! empty( $value ) ) {
				return $value;
			}
		}

		if ( function_exists( 'ftd_read_community_call_profile_members_meta' ) ) {
			$rows = ftd_read_community_call_profile_members_meta( $post_id );

			if ( ! empty( $rows ) ) {
				return $rows;
			}
		}

		foreach ( array( 'involved_members' ) as $legacy_field ) {
			$legacy = get_post_meta( $post_id, $legacy_field, true );

			if ( ! empty( $legacy ) && is_array( $legacy ) && function_exists( 'ftd_normalize_community_call_profile_members' ) ) {
				return ftd_normalize_community_call_profile_members( $legacy, $post_id, array() );
			}
		}

		return is_array( $value ) ? $value : array();
	}

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field, $post_id );

		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	foreach ( $legacy as $legacy_field ) {
		if ( function_exists( 'get_field' ) ) {
			$value = get_field( $legacy_field, $post_id );

			if ( null !== $value && false !== $value && '' !== $value ) {
				return $value;
			}
		}

		$value = get_post_meta( $post_id, $legacy_field, true );

		if ( '' !== $value && false !== $value && null !== $value ) {
			return $value;
		}
	}

	return null;
}

/**
 * Default host user ID (Naomi Spirit).
 *
 * @return int
 */
function ftd_get_naomi_spirit_user_id() {
	static $user_id = null;

	if ( null !== $user_id ) {
		return $user_id;
	}

	$logins = array( 'naomi-spirit', 'naomispirit', 'naomi_spirit', 'naomi' );

	foreach ( $logins as $login ) {
		$user = get_user_by( 'login', $login );

		if ( $user ) {
			$user_id = (int) $user->ID;
			return $user_id;
		}
	}

	foreach ( $logins as $slug ) {
		$user = get_user_by( 'slug', $slug );

		if ( $user ) {
			$user_id = (int) $user->ID;
			return $user_id;
		}
	}

	$users = get_users(
		array(
			'search'         => 'Naomi Spirit',
			'search_columns' => array( 'display_name' ),
			'number'         => 1,
		)
	);

	$user_id = ! empty( $users[0] ) ? (int) $users[0]->ID : 0;

	return $user_id;
}

/**
 * Whether the current user has an active PMPro membership.
 *
 * @param int $user_id Optional user ID.
 * @return bool
 */
function ftd_community_call_user_is_member( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return false;
	}

	if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		return true;
	}

	$level = pmpro_getMembershipLevelForUser( $user_id );

	return ! empty( $level ) && ! empty( $level->id );
}

/**
 * Member profile URL.
 *
 * @param int $user_id User ID.
 * @return string
 */
function ftd_get_member_profile_url( $user_id ) {
	$user = get_userdata( (int) $user_id );

	if ( ! $user ) {
		return '';
	}

	$slug = $user->user_nicename ? $user->user_nicename : $user->user_login;
	$url  = home_url( '/profile/' . $slug . '/' );

	/**
	 * Filter member profile URL.
	 *
	 * @param string   $url     Profile URL.
	 * @param int      $user_id User ID.
	 * @param WP_User  $user    User object.
	 */
	return apply_filters( 'ftd_member_profile_url', $url, (int) $user_id, $user );
}

/**
 * Profile-linked members for a community call.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{id: int, name: string, url: string, avatar_url: string, subtitle: string}>
 */
function ftd_get_community_call_members( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$members = ftd_get_community_call_field( 'profile_members', $post_id );

	if ( empty( $members ) ) {
		return array();
	}

	if ( ! is_array( $members ) ) {
		$members = array( $members );
	}

	if ( function_exists( 'ftd_normalize_community_call_profile_members' ) ) {
		$members = ftd_normalize_community_call_profile_members( $members, $post_id, array() );
	}

	$out = array();

	foreach ( $members as $member ) {
		$user_id  = 0;
		$subtitle = '';

		if ( is_array( $member ) ) {
			$user_id    = function_exists( 'ftd_get_community_call_profile_member_id_from_row' )
				? ftd_get_community_call_profile_member_id_from_row( $member )
				: (int) ( $member['member'] ?? $member['ID'] ?? $member['id'] ?? 0 );
			$subtitle   = function_exists( 'ftd_get_community_call_profile_subtitle_from_row' )
				? ftd_get_community_call_profile_subtitle_from_row( $member )
				: ( isset( $member['subtitle'] ) ? trim( (string) $member['subtitle'] ) : '' );
			$role_label = function_exists( 'ftd_get_community_call_profile_role_from_row' )
				? ftd_get_community_call_profile_role_from_row( $member )
				: ( isset( $member['role_label'] ) ? trim( (string) $member['role_label'] ) : '' );
		} else {
			$user_id    = (int) $member;
			$role_label = '';
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			continue;
		}

		if ( '' === $subtitle && function_exists( 'ftd_get_community_call_member_tagline' ) ) {
			$subtitle = ftd_get_community_call_member_tagline( $user_id );
		}

		$out[] = array(
			'id'         => $user_id,
			'name'       => $user->display_name,
			'url'        => ftd_get_member_profile_url( $user_id ),
			'avatar_url' => ftd_get_community_call_member_avatar_url( $user_id ),
			'subtitle'   => $subtitle,
			'role_label' => $role_label ?? '',
		);
	}

	return $out;
}

/**
 * Short description for archive cards and SEO.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_short_description( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$desc    = ftd_get_community_call_field( 'short_description', $post_id, array( 'subtitle', 'meta_description' ) );

	if ( is_string( $desc ) && '' !== trim( $desc ) ) {
		return trim( $desc );
	}

	$post = get_post( $post_id );

	if ( $post instanceof WP_Post && ! empty( $post->post_excerpt ) ) {
		return trim( wp_strip_all_tags( $post->post_excerpt ) );
	}

	return '';
}

/**
 * Meta description for a community call.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_meta_description( $post_id = 0 ) {
	$short = ftd_get_community_call_short_description( $post_id );

	if ( '' !== $short ) {
		return $short;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$post    = get_post( $post_id );

	if ( $post instanceof WP_Post ) {
		$long = ftd_get_community_call_long_description( $post_id );

		if ( '' !== $long ) {
			return trim( wp_strip_all_tags( wp_trim_words( $long, 35, '…' ) ) );
		}
	}

	return '';
}

/**
 * Long description HTML for members.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_long_description( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$desc    = ftd_get_community_call_field( 'long_description', $post_id );

	if ( is_string( $desc ) && '' !== trim( $desc ) ) {
		return $desc;
	}

	$post = get_post( $post_id );

	if ( $post instanceof WP_Post && ! empty( $post->post_content ) ) {
		return $post->post_content;
	}

	return '';
}

/**
 * “What do I need” copy for a community call.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_what_do_i_need( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$text    = ftd_get_community_call_field( 'what_do_i_need', $post_id );

	if ( is_string( $text ) && '' !== trim( $text ) ) {
		return trim( $text );
	}

	return 'Come as you are. No prep needed. Just bring yourself.';
}

/**
 * Call date/time raw value.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_datetime( $post_id = 0 ) {
	$post_id  = $post_id ? (int) $post_id : get_the_ID();
	$datetime = ftd_get_community_call_field( 'call_datetime', $post_id );

	return is_string( $datetime ) ? trim( $datetime ) : '';
}

/**
 * Parse schedule display parts for the featured session panel.
 *
 * @param int $post_id Post ID.
 * @return array{day_name: string, day_num: string, month_name: string, time_lines: string[], subtitle: string}
 */
function ftd_get_community_call_schedule_parts( $post_id = 0 ) {
	$post_id  = $post_id ? (int) $post_id : get_the_ID();
	$short    = ftd_get_community_call_short_description( $post_id );
	$datetime = ftd_get_community_call_datetime( $post_id );
	$parts    = array(
		'day_name'   => '',
		'day_num'    => '',
		'month_name' => '',
		'time_lines' => array(),
		'subtitle'   => $short,
	);

	if ( '' !== $short && false !== strpos( $short, '·' ) ) {
		list( $before, $after ) = array_map( 'trim', explode( '·', $short, 2 ) );

		if ( '' !== $after && preg_match( '/pm|am|UK|CT|\//i', $after ) ) {
			$parts['time_lines'] = array_values(
				array_filter(
					array_map( 'trim', preg_split( '/\s*\/\s*/', $after ) )
				)
			);
		}

		if ( preg_match( '/pm|am|UK|CT|\//i', $before ) ) {
			$parts['subtitle'] = '';
		} elseif ( preg_match( '/\b(monday|tuesday|wednesday|thursday|friday|saturday|sunday|january|february|march|april|may|june|july|august|september|october|november|december|\d{1,2})\b/i', $before ) ) {
			$parts['subtitle'] = '';
		}
	}

	if ( preg_match( '/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\b.*\b\d{1,2}\b.*\b(pm|am)\b/i', $parts['subtitle'] ) ) {
		$parts['subtitle'] = '';
	}

	if ( '' !== $datetime ) {
		$timestamp = strtotime( $datetime );

		if ( $timestamp ) {
			$parts['day_name']   = strtoupper( wp_date( 'l', $timestamp ) );
			$parts['day_num']    = wp_date( 'd', $timestamp );
			$parts['month_name'] = strtoupper( wp_date( 'F', $timestamp ) );

			if ( empty( $parts['time_lines'] ) ) {
				$parts['time_lines'] = array( wp_date( 'g:ia', $timestamp ) );
			}
		}
	}

	return $parts;
}

/**
 * Published session number by publish date (oldest = 1).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ftd_get_live_session_number( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( $post_id <= 0 ) {
		return 0;
	}

	static $cache = null;

	if ( null === $cache ) {
		$cache = array();
		$query = new WP_Query(
			array(
				'post_type'              => FTD_COMMUNITY_CALL_POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => array(
					'call_datetime_clause' => 'ASC',
					'date'                   => 'ASC',
				),
				'meta_query'             => array(
					'relation'             => 'OR',
					'call_datetime_clause' => array(
						'key'     => 'call_datetime',
						'compare' => 'EXISTS',
					),
					'no_call_datetime'     => array(
						'key'     => 'call_datetime',
						'compare' => 'NOT EXISTS',
					),
				),
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $query->posts as $index => $id ) {
			$cache[ (int) $id ] = $index + 1;
		}
	}

	return $cache[ $post_id ] ?? 0;
}

/**
 * Initials from a display name.
 *
 * @param string $name Display name.
 * @return string
 */
function ftd_get_member_initials( $name ) {
	$name = trim( (string) $name );

	if ( '' === $name ) {
		return '';
	}

	$parts    = preg_split( '/\s+/', $name );
	$initials = '';

	foreach ( array_slice( $parts, 0, 2 ) as $part ) {
		$initials .= strtoupper( substr( $part, 0, 1 ) );
	}

	return $initials;
}

/**
 * Session title with ALL-CAPS words accented.
 *
 * @param string $title Post title.
 * @return string
 */
function ftd_format_gnls_session_cta_title( $title ) {
	$title = (string) $title;

	if ( '' === trim( $title ) ) {
		return '';
	}

	$chunks = preg_split( '/(\s+)/', $title, -1, PREG_SPLIT_DELIM_CAPTURE );
	$html   = '';

	foreach ( $chunks as $chunk ) {
		if ( '' === $chunk ) {
			continue;
		}

		if ( preg_match( '/^[A-Z]{2,}$/', $chunk ) ) {
			$html .= '<span class="gnls-cta-title-accent">' . esc_html( $chunk ) . '</span>';
		} elseif ( preg_match( '/^\s+$/', $chunk ) ) {
			$html .= $chunk;
		} else {
			$html .= esc_html( $chunk );
		}
	}

	return $html;
}

/**
 * Combined date line for promo CTAs (e.g. 02 June).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_promo_date( $post_id = 0 ) {
	$schedule = ftd_get_community_call_schedule_parts( $post_id );

	if ( $schedule['day_num'] && $schedule['month_name'] ) {
		return trim( $schedule['day_num'] . ' ' . ucfirst( strtolower( $schedule['month_name'] ) ) );
	}

	return '';
}

/**
 * Combined time line for promo CTAs (UK · ET · CET when call datetime is set).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_promo_times( $post_id = 0 ) {
	$post_id  = $post_id ? (int) $post_id : get_the_ID();
	$datetime = ftd_get_community_call_datetime( $post_id );

	if ( '' !== $datetime ) {
		$timestamp = false;

		if ( function_exists( 'wp_timezone' ) ) {
			$dt = date_create( $datetime, wp_timezone() );

			if ( $dt instanceof DateTime ) {
				$timestamp = $dt->getTimestamp();
			}
		}

		if ( ! $timestamp ) {
			$timestamp = strtotime( $datetime );
		}

		if ( $timestamp ) {
			$formatted = ftd_format_community_call_timezone_times( $timestamp );

			if ( '' !== $formatted ) {
				return $formatted;
			}
		}
	}

	$schedule = ftd_get_community_call_schedule_parts( $post_id );

	if ( empty( $schedule['time_lines'] ) ) {
		return '';
	}

	return implode( ' · ', $schedule['time_lines'] );
}

/**
 * Unix timestamp for a session call datetime.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ftd_get_community_call_timestamp( $post_id = 0 ) {
	$post_id  = $post_id ? (int) $post_id : get_the_ID();
	$datetime = ftd_get_community_call_datetime( $post_id );

	if ( '' === $datetime ) {
		return 0;
	}

	if ( function_exists( 'wp_timezone' ) ) {
		$dt = date_create( $datetime, wp_timezone() );

		if ( $dt instanceof DateTime ) {
			return $dt->getTimestamp();
		}
	}

	return (int) strtotime( $datetime );
}

/**
 * Popular timezone labels for session times.
 *
 * @return array<string, string>
 */
function ftd_get_community_call_timezone_zones() {
	return apply_filters(
		'ftd_community_call_timezone_zones',
		array(
			'UK'   => 'Europe/London',
			'CET'  => 'Europe/Paris',
			'ET'   => 'America/New_York',
			'PT'   => 'America/Los_Angeles',
			'CT'   => 'America/Chicago',
			'IST'  => 'Asia/Kolkata',
			'AEST' => 'Australia/Sydney',
			'UTC'  => 'UTC',
		)
	);
}

/**
 * Timezone lines for a session (label + formatted time).
 *
 * @param int $post_id Post ID.
 * @param int $limit   Max zones (0 = all).
 * @return array<int, array{label: string, time: string, text: string}>
 */
function ftd_get_community_call_timezone_lines( $post_id = 0, $limit = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$timestamp = ftd_get_community_call_timestamp( $post_id );
	$zones     = ftd_get_community_call_timezone_zones();

	if ( $limit > 0 ) {
		$zones = array_slice( $zones, 0, $limit, true );
	}

	if ( $timestamp <= 0 ) {
		$schedule = ftd_get_community_call_schedule_parts( $post_id );

		if ( empty( $schedule['time_lines'] ) ) {
			return array();
		}

		$lines = array();

		foreach ( $schedule['time_lines'] as $line ) {
			$line = trim( (string) $line );

			if ( '' === $line ) {
				continue;
			}

			$lines[] = array(
				'label' => '',
				'time'  => $line,
				'text'  => $line,
			);
		}

		return $lines;
	}

	$lines = array();

	foreach ( $zones as $label => $timezone ) {
		try {
			$zone = new DateTimeZone( $timezone );
		} catch ( Exception $e ) {
			continue;
		}

		$time    = strtolower( wp_date( 'g:ia', $timestamp, $zone ) );
		$lines[] = array(
			'label' => $label,
			'time'  => $time,
			'text'  => $time . ' ' . $label,
		);
	}

	return $lines;
}

/**
 * Format a session timestamp as timezone labels.
 *
 * @param int $timestamp Unix timestamp.
 * @param int $limit     Max zones (0 = all).
 * @return string
 */
function ftd_format_community_call_timezone_times( $timestamp, $limit = 3 ) {
	$timestamp = (int) $timestamp;

	if ( $timestamp <= 0 ) {
		return '';
	}

	$zones = ftd_get_community_call_timezone_zones();

	if ( $limit > 0 ) {
		$zones = array_slice( $zones, 0, $limit, true );
	}

	$parts = array();

	foreach ( $zones as $label => $timezone ) {
		try {
			$zone = new DateTimeZone( $timezone );
		} catch ( Exception $e ) {
			continue;
		}

		$time    = strtolower( wp_date( 'g:ia', $timestamp, $zone ) );
		$parts[] = $time . ' ' . $label;
	}

	return implode( ' · ', $parts );
}

/**
 * Member tagline for session cards (business name or listing headline).
 *
 * @param int $user_id User ID.
 * @return string
 */
function ftd_get_community_call_member_tagline( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return '';
	}

	$business = get_user_meta( $user_id, 'business_name', true );

	if ( is_string( $business ) && '' !== trim( $business ) ) {
		return trim( $business );
	}

	$headline = get_user_meta( $user_id, 'directory_listing_headline', true );

	if ( is_string( $headline ) && '' !== trim( $headline ) ) {
		return trim( $headline );
	}

	return '';
}

/**
 * Teaser copy for featured session panel.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_panel_teaser( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$long    = ftd_get_community_call_long_description( $post_id );

	if ( '' === $long ) {
		return '';
	}

	$plain = trim( wp_strip_all_tags( $long ) );

	if ( '' === $plain ) {
		return '';
	}

	return wp_trim_words( $plain, 55, '…' );
}

/**
 * Next upcoming live session (or earliest scheduled).
 *
 * @return int
 */
function ftd_get_featured_live_session_id() {
	static $featured_id = null;

	if ( null !== $featured_id ) {
		return (int) $featured_id;
	}

	$featured_id = 0;
	$now         = current_time( 'Y-m-d H:i:s' );

	$upcoming = new WP_Query(
		array(
			'post_type'              => FTD_COMMUNITY_CALL_POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'meta_key'               => 'call_datetime',
			'meta_value'             => $now,
			'meta_compare'           => '>=',
			'meta_type'              => 'DATETIME',
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $upcoming->have_posts() ) {
		$upcoming->the_post();
		$featured_id = get_the_ID();
		wp_reset_postdata();

		return (int) $featured_id;
	}

	$scheduled = new WP_Query(
		array(
			'post_type'              => FTD_COMMUNITY_CALL_POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'meta_key'               => 'call_datetime',
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $scheduled->have_posts() ) {
		$scheduled->the_post();
		$featured_id = get_the_ID();
		wp_reset_postdata();

		return (int) $featured_id;
	}

	$latest = new WP_Query(
		array(
			'post_type'              => FTD_COMMUNITY_CALL_POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $latest->have_posts() ) {
		$latest->the_post();
		$featured_id = get_the_ID();
		wp_reset_postdata();
	}

	return (int) $featured_id;
}

/**
 * Featured session panel settings from archive options.
 *
 * @return array{title: string, subtitle: string, cta_label: string}
 */
function ftd_get_gnls_featured_panel_settings() {
	$defaults = function_exists( 'ftd_get_page_header_archive_default_config' )
		? ftd_get_page_header_archive_default_config()
		: array();

	$get = static function ( $field ) use ( $defaults ) {
		if ( function_exists( 'ftd_page_header_get_field' ) ) {
			$value = ftd_page_header_get_field( $field, FTD_PAGE_HEADER_ARCHIVE_OPTION );

			if ( is_string( $value ) && '' !== trim( $value ) ) {
				return trim( $value );
			}
		}

		return isset( $defaults[ $field ] ) ? (string) $defaults[ $field ] : '';
	};

	return array(
		'title'     => $get( 'featured_section_title' ),
		'subtitle'  => $get( 'featured_section_subtitle' ),
		'cta_label' => $get( 'featured_cta_label' ),
	);
}

/**
 * Render the featured upcoming session hero (session CTA layout).
 *
 * @return string
 */
function ftd_render_gnls_featured_session_panel() {
	$post_id = ftd_get_featured_live_session_id();

	if ( $post_id <= 0 || ! function_exists( 'ftd_render_gnls_session_cta' ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-gnls-session-cta' );

	$cta_html = ftd_render_gnls_session_cta(
		$post_id,
		array(
			'show_button'     => '0',
			'link_to_session' => '1',
		)
	);

	if ( '' === trim( $cta_html ) ) {
		return '';
	}

	$section_title = __( 'Our next genius network live session', 'ftd-directory-listings' );

	ob_start();
	?>
	<section class="gcc-featured-hero" aria-labelledby="gcc-featured-hero-title">
		<div class="gcc-featured-hero-inner">
			<h2 id="gcc-featured-hero-title" class="gcc-featured-hero-title text-purple"><?php echo esc_html( $section_title ); ?></h2>
			<?php echo $cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Echo the featured session panel.
 *
 * @return void
 */
function ftd_the_gnls_featured_session_panel() {
	echo ftd_render_gnls_featured_session_panel(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
}

/**
 * Archive session grid card markup.
 *
 * @param int $post_id Session post ID.
 * @return string
 */
function ftd_render_gnls_archive_session_card( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return '';
	}

	$short_desc   = ftd_get_community_call_short_description( $post_id );
	$members      = ftd_get_community_call_members( $post_id );
	$feature_html = ftd_get_community_call_feature_image_html(
		$post_id,
		'medium_large',
		array( 'class' => 'gcc-card-image std-border-radius' )
	);

	ob_start();
	?>
	<article <?php post_class( 'gcc-card card', $post_id ); ?>>
		<?php if ( $feature_html ) : ?>
			<a class="gcc-card-image-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by wp_get_attachment_image. ?>
			</a>
		<?php endif; ?>

		<div class="gcc-card-body">
			<p class="gcc-card-kicker"><?php echo esc_html( ftd_gnls_kicker() ); ?></p>
			<h3 class="gcc-card-title">
				<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
			</h3>

			<?php if ( $short_desc ) : ?>
				<p class="gcc-card-subtitle"><?php echo esc_html( $short_desc ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $members ) ) : ?>
				<p class="gcc-card-members">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: comma-separated member names */
							__( 'With %s', 'ftd-directory-listings' ),
							implode(
								', ',
								array_map(
									static function ( $member ) {
										return $member['name'];
									},
									$members
								)
							)
						)
					);
					?>
				</p>
			<?php endif; ?>

			<a class="btn gcc-card-btn" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<?php esc_html_e( 'View session', 'ftd-directory-listings' ); ?>
			</a>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * Upcoming session highlight with "Up Next" banner (archive sessions section).
 *
 * @param int $post_id Session post ID. Defaults to featured/upcoming session.
 * @return string
 */
function ftd_render_gnls_up_next_session( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : ftd_get_featured_live_session_id();

	if ( $post_id <= 0 ) {
		return '';
	}

	$short_desc   = ftd_get_community_call_short_description( $post_id );
	$members      = ftd_get_community_call_members( $post_id );
	$schedule     = ftd_get_community_call_schedule_parts( $post_id );
	$date_line    = ftd_get_community_call_promo_date( $post_id );
	$time_line    = ftd_get_community_call_promo_times( $post_id );
	$feature_html = ftd_get_community_call_feature_image_html(
		$post_id,
		'medium_large',
		array( 'class' => 'gcc-up-next-image std-border-radius' )
	);

	ob_start();
	?>
	<div class="gcc-up-next">
		<p class="gcc-up-next-banner"><?php esc_html_e( 'Up Next', 'ftd-directory-listings' ); ?></p>
		<a class="gcc-up-next-card card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( $feature_html ) : ?>
				<div class="gcc-up-next-media">
					<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by wp_get_attachment_image. ?>
				</div>
			<?php endif; ?>

			<div class="gcc-up-next-body">
				<?php if ( $schedule['day_name'] || $date_line || $time_line ) : ?>
					<p class="gcc-up-next-date">
						<?php if ( $schedule['day_name'] ) : ?>
							<span class="gcc-up-next-date-day"><?php echo esc_html( $schedule['day_name'] ); ?></span>
						<?php endif; ?>
						<?php if ( $date_line ) : ?>
							<span class="gcc-up-next-date-num"><?php echo esc_html( $date_line ); ?></span>
						<?php endif; ?>
						<?php if ( $time_line ) : ?>
							<span class="gcc-up-next-date-time"><?php echo esc_html( $time_line ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<p class="gcc-up-next-kicker"><?php echo esc_html( ftd_gnls_kicker() ); ?></p>
				<h3 class="gcc-up-next-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>

				<?php if ( $short_desc ) : ?>
					<p class="gcc-up-next-subtitle"><?php echo esc_html( $short_desc ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $members ) ) : ?>
					<p class="gcc-up-next-members">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: comma-separated member names */
								__( 'With %s', 'ftd-directory-listings' ),
								implode(
									', ',
									array_map(
										static function ( $member ) {
											return $member['name'];
										},
										$members
									)
								)
							)
						);
						?>
					</p>
				<?php endif; ?>

				<span class="btn gcc-up-next-btn"><?php esc_html_e( 'View session', 'ftd-directory-listings' ); ?></span>
			</div>
		</a>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * YouTube link label/value.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_youtube_link( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$link    = ftd_get_community_call_field( 'youtube_link', $post_id, array( 'youtube_url' ) );

	if ( ! is_string( $link ) || '' === trim( $link ) ) {
		return 'Coming soon';
	}

	return trim( $link );
}

/**
 * Featured image attachment ID.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ftd_get_community_call_feature_image_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id );
	}

	return 0;
}

/**
 * Feature image markup.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @param array  $attr    Image attributes.
 * @return string
 */
function ftd_get_community_call_feature_image_html( $post_id = 0, $size = 'large', $attr = array() ) {
	$attachment_id = ftd_get_community_call_feature_image_id( $post_id );

	if ( $attachment_id <= 0 ) {
		return '';
	}

	$defaults = array( 'class' => 'gcc-call-image std-border-radius' );
	$attr     = wp_parse_args( $attr, $defaults );

	return wp_get_attachment_image( $attachment_id, $size, false, $attr );
}

/**
 * YouTube embed URL from a watch/share link.
 *
 * @param string $url YouTube URL.
 * @return string
 */
function ftd_get_youtube_embed_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url || 0 === stripos( $url, 'coming soon' ) ) {
		return '';
	}

	$patterns = array(
		'#youtube\.com/watch\?v=([^&]+)#i',
		'#youtu\.be/([^?&]+)#i',
		'#youtube\.com/embed/([^?&]+)#i',
		'#youtube\.com/live/([^?&]+)#i',
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $url, $matches ) ) {
			return 'https://www.youtube.com/embed/' . rawurlencode( $matches[1] );
		}
	}

	return '';
}

/**
 * Normalise an external link for output.
 *
 * @param string $url Raw URL.
 * @return string
 */
function ftd_normalize_external_link( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( 0 === strpos( $url, '//' ) ) {
		$url = set_url_scheme( 'https:' . $url );
	}

	if ( ! preg_match( '#^https?://#i', $url ) ) {
		$url = 'https://' . ltrim( $url, '/' );
	}

	$sanitized = esc_url_raw( $url );

	return $sanitized ? $sanitized : '';
}

/**
 * Share URLs for a live session page.
 *
 * @param int $post_id Post ID.
 * @return array<string, string>
 */
function ftd_get_live_session_share_urls( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = get_the_title( $post_id );
	$text    = ftd_get_community_call_short_description( $post_id );

	if ( '' === $text ) {
		$text = $title;
	}

	$body = $text . "\n\n" . $url;

	return array(
		'twitter'  => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $body ),
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) . '&quote=' . rawurlencode( $text ),
		'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ),
		'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $body ),
		'email'    => 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $body ),
	);
}

/**
 * Social share buttons for the current live session.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_render_live_session_share_buttons( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$urls    = ftd_get_live_session_share_urls( $post_id );
	$url     = get_permalink( $post_id );

	if ( ! function_exists( 'ftd_social_share_icon_svg' ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-social-share' );
	wp_enqueue_script( 'ftd-live-session-share' );

	ob_start();
	?>
	<div class="gcc-share">
		<h2 class="gcc-section-title"><?php esc_html_e( 'Share this session', 'ftd-directory-listings' ); ?></h2>
		<div class="gcc-share-row">
			<a class="ftd-ss-pill ftd-ss-pill--twitter" href="<?php echo esc_url( $urls['twitter'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'twitter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">X / Twitter</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--facebook" href="<?php echo esc_url( $urls['facebook'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">Facebook</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--linkedin" href="<?php echo esc_url( $urls['linkedin'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'linkedin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">LinkedIn</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--whatsapp" href="<?php echo esc_url( $urls['whatsapp'] ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="ftd-ss-pill-icon" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label">WhatsApp</span>
			</a>
			<a class="ftd-ss-pill ftd-ss-pill--email" href="<?php echo esc_url( $urls['email'] ); ?>">
				<span class="ftd-ss-pill-icon" aria-hidden="true">✉</span>
				<span class="ftd-ss-pill-label"><?php esc_html_e( 'Email', 'ftd-directory-listings' ); ?></span>
			</a>
			<button type="button" class="ftd-ss-pill ftd-ss-pill--copy gcc-share-copy" data-copy-url="<?php echo esc_attr( $url ); ?>">
				<span class="ftd-ss-pill-icon ftd-ss-pill-icon--copy" aria-hidden="true"><?php echo ftd_social_share_icon_svg( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ftd-ss-pill-label"><?php esc_html_e( 'Copy link', 'ftd-directory-listings' ); ?></span>
			</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Most recent other live session (excluding current).
 *
 * @param int $exclude_id Post ID to exclude.
 * @return int
 */
function ftd_get_previous_live_session_id( $exclude_id = 0 ) {
	$exclude_id = (int) $exclude_id;

	$query = new WP_Query(
		array(
			'post_type'              => FTD_COMMUNITY_CALL_POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'post__not_in'           => $exclude_id ? array( $exclude_id ) : array(),
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$previous_id = 0;

	if ( $query->have_posts() ) {
		$query->the_post();
		$previous_id = get_the_ID();
		wp_reset_postdata();
	}

	return (int) $previous_id;
}

/**
 * One horizontal previous-session card or coming-soon placeholder.
 *
 * @param int $exclude_id Current post ID.
 * @return void
 */
function ftd_render_previous_live_session_slot( $exclude_id = 0 ) {
	$previous_id = ftd_get_previous_live_session_id( $exclude_id );

	if ( $previous_id > 0 ) {
		$short_desc   = ftd_get_community_call_short_description( $previous_id );
		$feature_html = ftd_get_community_call_feature_image_html(
			$previous_id,
			'medium_large',
			array( 'class' => 'gcc-previous-card-image std-border-radius' )
		);
		?>
		<a class="gcc-previous-card card" href="<?php echo esc_url( get_permalink( $previous_id ) ); ?>">
			<?php if ( $feature_html ) : ?>
				<div class="gcc-previous-card-media">
					<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
			<div class="gcc-previous-card-body">
				<p class="gcc-previous-card-kicker"><?php echo esc_html( ftd_gnls_kicker() ); ?></p>
				<h3 class="gcc-previous-card-title"><?php echo esc_html( get_the_title( $previous_id ) ); ?></h3>
				<?php if ( $short_desc ) : ?>
					<p class="gcc-previous-card-subtitle"><?php echo esc_html( $short_desc ); ?></p>
				<?php endif; ?>
				<span class="btn gcc-previous-card-btn"><?php esc_html_e( 'Watch session', 'ftd-directory-listings' ); ?></span>
			</div>
		</a>
		<?php
		return;
	}
	?>
	<div class="gcc-previous-card gcc-previous-card--placeholder card">
		<div class="gcc-previous-card-body">
			<p class="gcc-previous-card-kicker"><?php esc_html_e( 'Coming soon', 'ftd-directory-listings' ); ?></p>
			<h3 class="gcc-previous-card-title"><?php echo esc_html( ftd_gnls_label_singular() ); ?></h3>
			<p class="gcc-previous-card-subtitle"><?php esc_html_e( 'Previous session replays will appear here.', 'ftd-directory-listings' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Profile picture URL for a member.
 *
 * @param int $user_id User ID.
 * @return string
 */
function ftd_get_community_call_member_avatar_url( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return function_exists( 'ftd_get_default_profile_image_url' )
			? ftd_get_default_profile_image_url()
			: '';
	}

	if ( function_exists( 'ftd_get_user_profile_image_url' ) ) {
		return ftd_get_user_profile_image_url( $user_id, 96 );
	}

	if ( function_exists( 'get_user_profile_pic' ) ) {
		$profile = get_user_profile_pic( 'user_' . $user_id );

		if ( is_array( $profile ) && ! empty( $profile['url'] ) ) {
			return (string) $profile['url'];
		}
	}

	return (string) get_avatar_url(
		$user_id,
		array(
			'size' => 96,
		)
	);
}

/**
 * Inline SVG icon for community call sections.
 *
 * @param string $icon Icon key.
 * @return string
 */
function ftd_get_community_call_section_icon( $icon ) {
	$icons = array(
		'featuring' => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		'do'        => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>',
		'need'      => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zm-9-1a2 2 0 0 1 4 0v1h-4V6zm2 10a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>',
		'bring'     => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		'when'      => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
		'where'     => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V6h16v12zM6 10h2v7H6v-7zm4 3h2v4h-2v-4zm4-6h2v10h-2V7z"/></svg>',
		'recording' => '<svg class="gcc-section-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8 5v14l11-7L8 5z"/></svg>',
	);

	return $icons[ $icon ] ?? '';
}

/**
 * Section heading with icon.
 *
 * @param string $title Section title.
 * @param string $icon  Icon key.
 * @return string
 */
function ftd_render_community_call_section_title( $title, $icon = '' ) {
	$icon_html = $icon ? ftd_get_community_call_section_icon( $icon ) : '';

	return sprintf(
		'<h2 class="gcc-section-title gcc-section-title--icon">%1$s<span>%2$s</span></h2>',
		$icon_html,
		esc_html( $title )
	);
}

/**
 * Member link with avatar.
 *
 * @param array{id: int, name: string, url: string, avatar_url?: string} $member Member data.
 * @return void
 */
function ftd_render_community_call_member_link( $member ) {
	$name            = $member['name'] ?? '';
	$url             = $member['url'] ?? '';
	$avatar_url      = $member['avatar_url'] ?? '';
	$default_avatar  = function_exists( 'ftd_get_default_profile_image_url' )
		? ftd_get_default_profile_image_url()
		: get_avatar_url( 0 );
	$resolved_avatar = $avatar_url ? $avatar_url : $default_avatar;

	if ( '' === $name ) {
		return;
	}

	if ( $url ) {
		printf(
			'<a class="gcc-member-link" href="%1$s"><img class="gcc-member-avatar" src="%2$s" alt="%3$s" width="48" height="48" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=\'%4$s\';" /><span class="gcc-member-name">%3$s</span></a>',
			esc_url( $url ),
			esc_url( $resolved_avatar ),
			esc_attr( $name ),
			esc_url( $default_avatar )
		);
		return;
	}

	printf(
		'<span class="gcc-member-link gcc-member-link--static"><img class="gcc-member-avatar" src="%1$s" alt="%2$s" width="48" height="48" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=\'%3$s\';" /><span class="gcc-member-name">%2$s</span></span>',
		esc_url( $resolved_avatar ),
		esc_attr( $name ),
		esc_url( $default_avatar )
	);
}

/**
 * Archive URL for community calls.
 *
 * @return string
 */
function ftd_get_community_call_archive_url() {
	$url = get_post_type_archive_link( FTD_COMMUNITY_CALL_POST_TYPE );

	return $url ? (string) $url : home_url( '/' );
}

/**
 * Kicker for single session hero.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_single_kicker( $post_id = 0 ) {
	$post_id     = $post_id ? (int) $post_id : get_the_ID();
	$session_num = ftd_get_live_session_number( $post_id );
	$label       = __( 'GENIUS COMMUNITY CALL', 'ftd-directory-listings' );

	if ( $session_num > 0 ) {
		return sprintf( '%s · %02d', $label, $session_num );
	}

	return $label;
}

/**
 * Long formatted date for sidebar (e.g. Monday 2 June 2026).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_formatted_date_long( $post_id = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$timestamp = ftd_get_community_call_timestamp( $post_id );

	if ( $timestamp > 0 ) {
		return wp_date( 'l j F Y', $timestamp );
	}

	$schedule = ftd_get_community_call_schedule_parts( $post_id );
	$parts    = array_filter(
		array(
			$schedule['day_name'] ? ucfirst( strtolower( $schedule['day_name'] ) ) : '',
			ltrim( (string) $schedule['day_num'], '0' ),
			$schedule['month_name'] ? ucfirst( strtolower( $schedule['month_name'] ) ) : '',
		)
	);

	return implode( ' ', $parts );
}

/**
 * Default copy for single session sidebar rows.
 *
 * @param int $post_id Post ID.
 * @return array<string, string>
 */
function ftd_get_community_call_details_defaults( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return apply_filters(
		'ftd_community_call_details_defaults',
		array(
			'where_label'       => __( 'Zoom', 'ftd-directory-listings' ),
			'where_subtext'     => __( 'Link arrives in your inbox the morning of', 'ftd-directory-listings' ),
			'recording_label'   => __( 'Available after', 'ftd-directory-listings' ),
			'recording_subtext' => __( 'Posted to YouTube for members', 'ftd-directory-listings' ),
		),
		$post_id
	);
}

/**
 * Google Calendar URL for a session.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_google_calendar_url( $post_id = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$timestamp = ftd_get_community_call_timestamp( $post_id );

	if ( $timestamp <= 0 ) {
		return '';
	}

	$end    = $timestamp + HOUR_IN_SECONDS;
	$params = array(
		'action'   => 'TEMPLATE',
		'text'     => get_the_title( $post_id ),
		'dates'    => gmdate( 'Ymd\THis\Z', $timestamp ) . '/' . gmdate( 'Ymd\THis\Z', $end ),
		'details'  => get_permalink( $post_id ),
		'location' => 'Zoom',
	);

	return 'https://calendar.google.com/calendar/render?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
}

/**
 * Outlook web calendar URL for a session.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_outlook_calendar_url( $post_id = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$timestamp = ftd_get_community_call_timestamp( $post_id );

	if ( $timestamp <= 0 ) {
		return '';
	}

	$end    = $timestamp + HOUR_IN_SECONDS;
	$params = array(
		'path'     => '/calendar/action/compose',
		'rru'      => 'addevent',
		'subject'  => get_the_title( $post_id ),
		'startdt'  => gmdate( 'Y-m-d\TH:i:s\Z', $timestamp ),
		'enddt'    => gmdate( 'Y-m-d\TH:i:s\Z', $end ),
		'body'     => get_permalink( $post_id ),
		'location' => 'Zoom',
	);

	return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
}

/**
 * Download URL for session .ics file.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_ics_url( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = get_permalink( $post_id );

	if ( ! $url ) {
		return '';
	}

	return add_query_arg( 'ftd_gcc_ics', '1', $url );
}

/**
 * Apple Calendar webcal URL for a session.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_apple_calendar_url( $post_id = 0 ) {
	$ics_url = ftd_get_community_call_ics_url( $post_id );

	if ( '' === $ics_url ) {
		return '';
	}

	return preg_replace( '#^https?://#', 'webcal://', $ics_url );
}

/**
 * iCalendar file contents for a session.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_ics_content( $post_id = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$timestamp = ftd_get_community_call_timestamp( $post_id );

	if ( $timestamp <= 0 ) {
		return '';
	}

	$end       = $timestamp + HOUR_IN_SECONDS;
	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id );
	$uid       = 'gcc-' . $post_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//We Are Geniuses//Community Call//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		'UID:' . $uid,
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		'DTSTART:' . gmdate( 'Ymd\THis\Z', $timestamp ),
		'DTEND:' . gmdate( 'Ymd\THis\Z', $end ),
		'SUMMARY:' . ftd_escape_ics_text( $title ),
		'DESCRIPTION:' . ftd_escape_ics_text( $permalink ),
		'LOCATION:Zoom',
		'URL:' . $permalink,
		'END:VEVENT',
		'END:VCALENDAR',
	);

	return implode( "\r\n", $lines ) . "\r\n";
}

/**
 * Escape text for iCalendar properties.
 *
 * @param string $text Raw text.
 * @return string
 */
function ftd_escape_ics_text( $text ) {
	$text = wp_strip_all_tags( (string) $text );
	$text = str_replace( array( '\\', ';', ',', "\n", "\r" ), array( '\\\\', '\\;', '\\,', '\\n', '' ), $text );

	return $text;
}

/**
 * Back link markup for single session pages.
 *
 * @return string
 */
function ftd_render_community_call_single_back_link() {
	return sprintf(
		'<p class="gcc-single-back"><a href="%1$s">%2$s</a></p>',
		esc_url( ftd_get_community_call_archive_url() ),
		esc_html__( '← ALL COMMUNITY CALLS', 'ftd-directory-listings' )
	);
}

/**
 * Hero banner for single session page.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_render_community_call_single_hero( $post_id = 0 ) {
	$post_id      = $post_id ? (int) $post_id : get_the_ID();
	$feature_html = ftd_get_community_call_feature_image_html(
		$post_id,
		'large',
		array(
			'class' => 'gcc-single-hero-image',
		)
	);
	$schedule     = ftd_get_community_call_schedule_parts( $post_id );
	$tagline      = trim( (string) $schedule['subtitle'] );
	$title_html   = ftd_format_gnls_session_cta_title( get_the_title( $post_id ) );
	$hero_classes = 'gcc-single-hero std-border-radius';

	if ( ! $feature_html ) {
		$hero_classes .= ' gcc-single-hero--no-image';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( $hero_classes ); ?>">
		<?php if ( $feature_html ) : ?>
			<div class="gcc-single-hero-media">
				<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<div class="gcc-single-hero-overlay" aria-hidden="true"></div>
		<div class="gcc-single-hero-content">
			<div class="gcc-single-hero-copy">
				<p class="gcc-single-hero-kicker"><?php echo esc_html( ftd_get_community_call_single_kicker( $post_id ) ); ?></p>
				<h1 class="gcc-single-hero-title"><?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
				<?php if ( $tagline ) : ?>
					<p class="gcc-single-hero-tagline"><?php echo esc_html( $tagline ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $schedule['day_name'] || $schedule['day_num'] || $schedule['month_name'] ) : ?>
				<div class="gcc-single-hero-date" aria-label="<?php echo esc_attr( ftd_get_community_call_formatted_date_long( $post_id ) ); ?>">
					<?php if ( $schedule['day_name'] ) : ?>
						<span class="gcc-single-hero-date-day"><?php echo esc_html( $schedule['day_name'] ); ?></span>
					<?php endif; ?>
					<?php if ( $schedule['day_num'] ) : ?>
						<span class="gcc-single-hero-date-num"><?php echo esc_html( ltrim( $schedule['day_num'], '0' ) ); ?></span>
					<?php endif; ?>
					<?php if ( $schedule['month_name'] ) : ?>
						<span class="gcc-single-hero-date-month"><?php echo esc_html( $schedule['month_name'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Featuring / hosts section for single session page.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_render_community_call_featuring_section( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$members = ftd_get_community_call_members( $post_id );

	if ( empty( $members ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="gcc-single-featuring">
		<h2 class="gcc-single-featuring-title"><?php esc_html_e( 'Featuring', 'ftd-directory-listings' ); ?></h2>
		<div class="gcc-featuring-list gcc-featuring-list--count-<?php echo esc_attr( (string) count( $members ) ); ?>">
			<?php foreach ( $members as $index => $member ) : ?>
				<?php echo ftd_render_community_call_featuring_member( $member, $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Featuring card for single session page.
 *
 * @param array{id: int, name: string, url: string, avatar_url?: string, subtitle?: string, role_label?: string} $member Member data.
 * @param int                                                                                                     $index  Zero-based index.
 * @return string
 */
function ftd_render_community_call_featuring_member( $member, $index = 0 ) {
	$name       = $member['name'] ?? '';
	$url        = $member['url'] ?? '';
	$subtitle   = $member['subtitle'] ?? '';
	$role_label = $member['role_label'] ?? '';

	if ( '' === $name ) {
		return '';
	}

	$user_id           = (int) ( $member['id'] ?? 0 );
	$has_custom_avatar = function_exists( 'ftd_user_has_custom_profile_image' ) && ftd_user_has_custom_profile_image( $user_id );
	$avatar_url        = function_exists( 'ftd_get_user_profile_image_url' )
		? ftd_get_user_profile_image_url( $user_id, 144 )
		: ( $member['avatar_url'] ?? '' );
	$initials          = ftd_get_member_initials( $name );
	$tag               = $url ? 'a' : 'div';
	$avatar_html       = '';

	if ( $has_custom_avatar && $avatar_url ) {
		$avatar_html = sprintf(
			'<img class="gcc-featuring-avatar gcc-featuring-avatar--photo" src="%1$s" alt="" width="80" height="80" loading="lazy" decoding="async" />',
			esc_url( $avatar_url )
		);
	} else {
		$avatar_html = sprintf(
			'<span class="gcc-featuring-avatar gcc-featuring-avatar--initials">%1$s</span>',
			esc_html( $initials )
		);
	}

	ob_start();
	printf(
		'<%1$s class="gcc-featuring-member gcc-featuring-member--%2$d"%3$s>',
		$tag,
		$index + 1,
		$url ? ' href="' . esc_url( $url ) . '"' : ''
	);
	?>
	<div class="gcc-featuring-avatar-wrap">
		<?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div class="gcc-featuring-copy">
		<span class="gcc-featuring-name"><?php echo esc_html( strtoupper( $name ) ); ?></span>
		<?php if ( $subtitle ) : ?>
			<span class="gcc-featuring-subtitle"><?php echo esc_html( $subtitle ); ?></span>
		<?php else : ?>
			<span class="gcc-featuring-subtitle gcc-featuring-subtitle--empty" aria-hidden="true"></span>
		<?php endif; ?>
		<?php if ( $role_label ) : ?>
			<span class="gcc-featuring-role"><?php echo esc_html( $role_label ); ?></span>
		<?php endif; ?>
	</div>
	<?php
	printf( '</%s>', $tag );

	return (string) ob_get_clean();
}

/**
 * Details sidebar for single session page.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_render_community_call_details_sidebar( $post_id = 0 ) {
	$post_id      = $post_id ? (int) $post_id : get_the_ID();
	$defaults     = ftd_get_community_call_details_defaults( $post_id );
	$date_long    = ftd_get_community_call_formatted_date_long( $post_id );
	$time_lines   = ftd_get_community_call_timezone_lines( $post_id, 0 );
	$time_text    = implode( ' · ', wp_list_pluck( $time_lines, 'text' ) );
	$what_need    = ftd_get_community_call_what_do_i_need( $post_id );
	$youtube      = ftd_get_community_call_youtube_link( $post_id );
	$embed_url    = ftd_get_youtube_embed_url( $youtube );
	$google_cal   = ftd_get_community_call_google_calendar_url( $post_id );
	$apple_cal    = ftd_get_community_call_apple_calendar_url( $post_id );
	$outlook_cal  = ftd_get_community_call_outlook_calendar_url( $post_id );
	$ics_url      = ftd_get_community_call_ics_url( $post_id );
	$bring_lines  = array_values(
		array_filter(
			array_map(
				'trim',
				preg_split( '/\.\s+/', wp_strip_all_tags( $what_need ), 2 )
			)
		)
	);
	$bring_primary = $bring_lines[0] ?? $what_need;
	$bring_secondary = $bring_lines[1] ?? '';

	ob_start();
	?>
	<aside class="gcc-single-details card">
		<h2 class="gcc-single-details-title"><?php esc_html_e( 'The details', 'ftd-directory-listings' ); ?></h2>

		<?php if ( $date_long || $time_text ) : ?>
			<section class="gcc-single-details-row">
				<h3 class="gcc-single-details-label">
					<?php echo ftd_get_community_call_section_icon( 'when' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'When', 'ftd-directory-listings' ); ?></span>
				</h3>
				<?php if ( $date_long ) : ?>
					<p class="gcc-single-details-date"><?php echo esc_html( $date_long ); ?></p>
				<?php endif; ?>
				<?php if ( $time_text ) : ?>
					<p class="gcc-single-details-times"><?php echo esc_html( $time_text ); ?></p>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<section class="gcc-single-details-row">
			<h3 class="gcc-single-details-label">
				<?php echo ftd_get_community_call_section_icon( 'where' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Where', 'ftd-directory-listings' ); ?></span>
			</h3>
			<p class="gcc-single-details-strong"><?php echo esc_html( $defaults['where_label'] ); ?></p>
			<p class="gcc-single-details-subtext"><?php echo esc_html( $defaults['where_subtext'] ); ?></p>
		</section>

		<?php if ( $what_need ) : ?>
			<section class="gcc-single-details-row">
				<h3 class="gcc-single-details-label">
					<?php echo ftd_get_community_call_section_icon( 'bring' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'What to bring', 'ftd-directory-listings' ); ?></span>
				</h3>
				<p class="gcc-single-details-bring">
					<?php if ( $bring_primary ) : ?>
						<em class="gcc-single-details-bring-accent"><?php echo esc_html( rtrim( $bring_primary, '.' ) ); ?>,</em>
					<?php endif; ?>
					<?php if ( $bring_secondary ) : ?>
						<span><?php echo esc_html( lcfirst( $bring_secondary ) ); ?></span>
					<?php elseif ( ! $bring_primary ) : ?>
						<span><?php echo esc_html( $what_need ); ?></span>
					<?php endif; ?>
				</p>
			</section>
		<?php endif; ?>

		<section class="gcc-single-details-row">
			<h3 class="gcc-single-details-label">
				<?php echo ftd_get_community_call_section_icon( 'recording' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Recording', 'ftd-directory-listings' ); ?></span>
			</h3>
			<p class="gcc-single-details-strong"><?php echo esc_html( $defaults['recording_label'] ); ?></p>
			<p class="gcc-single-details-subtext">
				<?php if ( $embed_url ) : ?>
					<a href="<?php echo esc_url( ftd_normalize_external_link( $youtube ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $defaults['recording_subtext'] ); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html( $defaults['recording_subtext'] ); ?>
				<?php endif; ?>
			</p>
		</section>

		<?php if ( $google_cal || $apple_cal || $outlook_cal || $ics_url ) : ?>
			<section class="gcc-single-details-row gcc-single-details-row--calendar">
				<h3 class="gcc-single-details-calendars-title"><?php esc_html_e( 'Add to calendar', 'ftd-directory-listings' ); ?></h3>
				<div class="gcc-single-details-calendars">
				<?php if ( $google_cal ) : ?>
					<a class="gcc-single-details-cal-btn" href="<?php echo esc_url( $google_cal ); ?>" target="_blank" rel="noopener noreferrer">+ Google</a>
				<?php endif; ?>
				<?php if ( $apple_cal ) : ?>
					<a class="gcc-single-details-cal-btn" href="<?php echo esc_url( $apple_cal ); ?>">+ Apple</a>
				<?php endif; ?>
				<?php if ( $outlook_cal ) : ?>
					<a class="gcc-single-details-cal-btn" href="<?php echo esc_url( $outlook_cal ); ?>" target="_blank" rel="noopener noreferrer">+ Outlook</a>
				<?php endif; ?>
				<?php if ( $ics_url ) : ?>
					<a class="gcc-single-details-cal-btn" href="<?php echo esc_url( $ics_url ); ?>" download="community-call.ics">+ .ics</a>
				<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>
	</aside>
	<?php
	return (string) ob_get_clean();
}
