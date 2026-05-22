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

			<?php if ( $col1_body || $col2_body || ! empty( $col1_highlights ) ) : ?>
				<div class="gcc-archive-intro-columns">
					<?php if ( $col1_body || ! empty( $col1_highlights ) ) : ?>
						<div class="gcc-archive-intro-col">
							<?php if ( $col1_title ) : ?>
								<h3 class="gcc-archive-intro-col-title text-purple"><?php echo esc_html( $col1_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $col1_body ) : ?>
								<div class="gcc-archive-intro-prose entry-content">
									<?php echo $col1_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in helper. ?>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $col1_highlights ) ) : ?>
								<ul class="gcc-archive-intro-highlights">
									<?php foreach ( $col1_highlights as $highlight ) : ?>
										<li class="gcc-archive-intro-highlight">
											<span class="gcc-archive-intro-highlight-marker" aria-hidden="true"></span>
											<span class="gcc-archive-intro-highlight-text"><?php echo esc_html( $highlight['text'] ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
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
 * @return array<int, array{id: int, name: string, url: string}>
 */
function ftd_get_community_call_members( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$members = ftd_get_community_call_field( 'profile_members', $post_id, array( 'involved_members' ) );

	if ( empty( $members ) ) {
		return array();
	}

	if ( ! is_array( $members ) ) {
		$members = array( $members );
	}

	$out = array();

	foreach ( $members as $member ) {
		$user_id = 0;

		if ( is_array( $member ) ) {
			$user_id = (int) ( $member['ID'] ?? $member['id'] ?? 0 );
		} else {
			$user_id = (int) $member;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			continue;
		}

		$out[] = array(
			'id'         => $user_id,
			'name'       => $user->display_name,
			'url'        => ftd_get_member_profile_url( $user_id ),
			'avatar_url' => ftd_get_community_call_member_avatar_url( $user_id ),
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
 * Combined time line for promo CTAs.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ftd_get_community_call_promo_times( $post_id = 0 ) {
	$schedule = ftd_get_community_call_schedule_parts( $post_id );

	if ( empty( $schedule['time_lines'] ) ) {
		return '';
	}

	return implode( ' · ', $schedule['time_lines'] );
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
		return '';
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
	$name       = $member['name'] ?? '';
	$url        = $member['url'] ?? '';
	$avatar_url = $member['avatar_url'] ?? '';

	if ( '' === $name ) {
		return;
	}

	if ( $url ) {
		printf(
			'<a class="gcc-member-link" href="%1$s"><img class="gcc-member-avatar" src="%2$s" alt="%3$s" width="48" height="48" loading="lazy" /><span class="gcc-member-name">%3$s</span></a>',
			esc_url( $url ),
			esc_url( $avatar_url ? $avatar_url : get_avatar_url( 0 ) ),
			esc_attr( $name )
		);
		return;
	}

	printf(
		'<span class="gcc-member-link gcc-member-link--static"><img class="gcc-member-avatar" src="%1$s" alt="%2$s" width="48" height="48" loading="lazy" /><span class="gcc-member-name">%2$s</span></span>',
		esc_url( $avatar_url ? $avatar_url : get_avatar_url( 0 ) ),
		esc_attr( $name )
	);
}
