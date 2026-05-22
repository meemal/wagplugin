<?php
/**
 * Live site stats for the stats ticker shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_STATS_TICKER_CACHE_KEY', 'ftd_stats_ticker_slides' );
define( 'FTD_STATS_TICKER_CACHE_TTL', 5 * MINUTE_IN_SECONDS );

/**
 * @return int Quantum Genius PMPro level ID.
 */
function ftd_stats_get_quantum_level_id() {
	return (int) apply_filters( 'ftd_stats_ticker_quantum_level_id', 3 );
}

/**
 * @return int Creator Genius PMPro level ID.
 */
function ftd_stats_get_creator_level_id() {
	return (int) apply_filters( 'ftd_stats_ticker_creator_level_id', 2 );
}

/**
 * @return void
 */
function ftd_stats_ticker_clear_cache() {
	delete_transient( FTD_STATS_TICKER_CACHE_KEY );
}

add_action( 'pmpro_after_checkout', 'ftd_stats_ticker_clear_cache', 10, 2 );
add_action( 'pmpro_after_change_membership_level', 'ftd_stats_ticker_clear_cache', 10, 3 );
add_action( 'user_register', 'ftd_stats_ticker_clear_cache' );
add_action( 'profile_update', 'ftd_stats_ticker_clear_cache' );
add_action( 'save_post_directory_listing', 'ftd_stats_ticker_clear_cache' );
add_action(
	'acf/save_post',
	function ( $post_id ) {
		if ( ! function_exists( 'ftd_get_genius_directory_banner_post_ids' ) ) {
			return;
		}

		if ( in_array( $post_id, ftd_get_genius_directory_banner_post_ids(), true ) ) {
			ftd_stats_ticker_clear_cache();
		}
	}
);

/**
 * Active PMPro members (distinct users).
 *
 * @return int
 */
function ftd_stats_count_members() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT( DISTINCT user_id )
		 FROM {$wpdb->pmpro_memberships_users}
		 WHERE status = 'active'
		   AND membership_id > 0"
	);
}

/**
 * Members with a map pin (Genius Map).
 *
 * @return int
 */
function ftd_stats_count_on_map() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT( DISTINCT u.ID )
		 FROM {$wpdb->users} u
		 INNER JOIN {$wpdb->pmpro_memberships_users} mu
			 ON u.ID = mu.user_id
			AND mu.status = 'active'
			AND mu.membership_id > 0
		 INNER JOIN {$wpdb->usermeta} um
			 ON u.ID = um.user_id
			AND um.meta_key = 'pmpromd_pin_location'
			AND um.meta_value != ''
			AND um.meta_value IS NOT NULL"
	);
}

/**
 * Active members on Creator Genius level.
 *
 * @return int
 */
function ftd_stats_count_creator_geniuses() {
	global $wpdb;

	$level_id = ftd_stats_get_creator_level_id();

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT( DISTINCT user_id )
			 FROM {$wpdb->pmpro_memberships_users}
			 WHERE status = 'active'
			   AND membership_id = %d",
			$level_id
		)
	);
}

/**
 * Active members on Quantum Genius level.
 *
 * @return int
 */
function ftd_stats_count_quantum_geniuses() {
	global $wpdb;

	$level_id = ftd_stats_get_quantum_level_id();

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT( DISTINCT user_id )
			 FROM {$wpdb->pmpro_memberships_users}
			 WHERE status = 'active'
			   AND membership_id = %d",
			$level_id
		)
	);
}

/**
 * Resolve a readable country name from a country code.
 *
 * @param string $code Country code.
 * @return string
 */
function ftd_stats_format_country( $code ) {
	$code = trim( (string) $code );

	if ( '' === $code ) {
		return '';
	}

	global $pmpro_countries;

	if ( ! empty( $pmpro_countries ) && is_array( $pmpro_countries ) && isset( $pmpro_countries[ $code ] ) ) {
		return $pmpro_countries[ $code ];
	}

	return $code;
}

/**
 * Country for the most recently joined active member.
 *
 * @return string Country name or empty.
 */
function ftd_stats_get_last_signup_country() {
	global $wpdb;

	$user_id = (int) $wpdb->get_var(
		"SELECT u.ID
		 FROM {$wpdb->users} u
		 INNER JOIN {$wpdb->pmpro_memberships_users} mu
			 ON u.ID = mu.user_id
			AND mu.status = 'active'
			AND mu.membership_id > 0
		 ORDER BY mu.startdate DESC, u.user_registered DESC
		 LIMIT 1"
	);

	if ( ! $user_id ) {
		return '';
	}

	$country_keys = array( 'pmpromd_country', 'pmpromm_country', 'pmpro_bcountry' );

	foreach ( $country_keys as $key ) {
		$code = get_user_meta( $user_id, $key, true );
		if ( $code ) {
			$label = ftd_stats_format_country( $code );
			if ( '' !== $label ) {
				return $label;
			}
		}
	}

	if ( function_exists( 'pmpromd_get_consolidated_map_data' ) ) {
		$map = pmpromd_get_consolidated_map_data( $user_id );
		if ( ! empty( $map['country'] ) ) {
			return ftd_stats_format_country( $map['country'] );
		}
	}

	return '';
}

/**
 * Published directory listing posts.
 *
 * @return int
 */
function ftd_stats_count_directory_listings() {
	$counts = wp_count_posts( 'directory_listing' );

	return (int) ( $counts->publish ?? 0 );
}

/**
 * Live stat values (cached).
 *
 * @return array<string, int|string>
 */
function ftd_get_stats_ticker_values() {
	$cached = get_transient( FTD_STATS_TICKER_CACHE_KEY );

	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$last_country = ftd_stats_get_last_signup_country();

	$values = array(
		'members'     => ftd_stats_count_members(),
		'map'         => ftd_stats_count_on_map(),
		'listings'    => ftd_stats_count_directory_listings(),
		'creator'     => ftd_stats_count_creator_geniuses(),
		'quantum'     => ftd_stats_count_quantum_geniuses(),
		'last_signup' => $last_country ? $last_country : __( 'Unknown', 'ftd-directory-listings' ),
	);

	$values = apply_filters( 'ftd_stats_ticker_values', $values );

	set_transient( FTD_STATS_TICKER_CACHE_KEY, $values, FTD_STATS_TICKER_CACHE_TTL );

	return $values;
}

/**
 * Ticker slides with live values.
 *
 * @return array<int, array<string, mixed>>
 */
function ftd_get_stats_ticker_slides() {
	$text   = function_exists( 'ftd_get_stats_ticker_text_settings' )
		? ftd_get_stats_ticker_text_settings()
		: ftd_get_stats_ticker_text_defaults();
	$values = ftd_get_stats_ticker_values();

	$slides = array(
		array(
			'value'   => $values['members'],
			'label'   => $text['members_label'],
			'tagline' => $text['members_tagline'],
			'type'    => 'number',
		),
		array(
			'value'   => $values['map'],
			'label'   => $text['map_label'],
			'tagline' => $text['map_tagline'],
			'type'    => 'number',
		),
		array(
			'value'   => $values['listings'],
			'label'   => $text['listings_label'],
			'tagline' => $text['listings_tagline'],
			'type'    => 'number',
		),
		array(
			'value'   => $values['creator'],
			'label'   => $text['creator_label'],
			'tagline' => $text['creator_tagline'],
			'type'    => 'number',
		),
		array(
			'value'   => $values['quantum'],
			'label'   => $text['quantum_label'],
			'tagline' => $text['quantum_tagline'],
			'type'    => 'number',
		),
		array(
			'value'   => $values['last_signup'],
			'label'   => $text['last_signup_label'],
			'tagline' => $text['last_signup_tagline'],
			'type'    => 'text',
		),
	);

	return apply_filters( 'ftd_stats_ticker_slides', $slides );
}

/**
 * Marquee items for sitewide banner (founding offer + live stats).
 *
 * @param array<string, mixed>|null $settings Founding banner settings.
 * @param array<string, mixed>|null $tokens   Founding tokens (remaining, total, code, used).
 * @param string                    $stats_eyebrow Eyebrow label for stat items.
 * @return array<int, array<string, mixed>>
 */
function ftd_get_banner_marquee_items( $settings = null, $tokens = null, $stats_eyebrow = 'SO FAR' ) {
	$items = array();

	if ( is_array( $settings ) && is_array( $tokens ) ) {
		$remaining = (int) ( $tokens['remaining'] ?? 0 );
		$total     = (int) ( $tokens['total'] ?? 0 );
		$code      = (string) ( $tokens['code'] ?? '' );

		$ticker_text = function_exists( 'ftd_get_stats_ticker_text_settings' )
			? ftd_get_stats_ticker_text_settings()
			: ftd_get_stats_ticker_text_defaults();

		$code_label = trim( (string) ( $settings['code_label'] ?? __( 'use code', 'ftd-directory-listings' ) ) );
		$founding_label = str_replace( '{total}', (string) $total, $ticker_text['founding_spots_label'] );

		$items[] = array(
			'eyebrow'    => $ticker_text['offer_eyebrow'],
			'value'      => $remaining,
			'label'      => $founding_label,
			'code_label' => $code_label,
			'code'       => $code,
			'type'       => 'founding',
		);
	}

	$eyebrow = $stats_eyebrow ?: 'SO FAR';

	foreach ( ftd_get_stats_ticker_slides() as $slide ) {
		$items[] = array(
			'eyebrow' => $eyebrow,
			'value'   => $slide['value'] ?? '',
			'label'   => $slide['label'] ?? '',
			'tagline' => $slide['tagline'] ?? '',
			'type'    => $slide['type'] ?? 'number',
		);
	}

	return apply_filters( 'ftd_banner_marquee_items', $items, $settings, $tokens );
}

/**
 * Render one marquee item.
 *
 * @param array<string, mixed> $item Item data.
 * @return void
 */
function ftd_render_banner_marquee_item( $item ) {
	$eyebrow = trim( (string) ( $item['eyebrow'] ?? '' ) );
	$value   = $item['value'] ?? '';
	$label   = trim( (string) ( $item['label'] ?? '' ) );
	$tagline = trim( (string) ( $item['tagline'] ?? '' ) );
	$type    = $item['type'] ?? 'number';
	$is_text = ( 'text' === $type );
	$is_founding = ( 'founding' === $type );
	?>
	<span class="fsb-marquee-item<?php echo $is_founding ? ' fsb-marquee-item--founding' : ''; ?>">
		<?php if ( $eyebrow ) : ?>
			<span class="fsb-item-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>
		<span class="fsb-item-value<?php echo $is_text ? ' fsb-item-value--text' : ''; ?>">
			<?php echo esc_html( (string) $value ); ?>
		</span>
		<?php if ( $label ) : ?>
			<span class="fsb-item-label"><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
		<?php if ( $is_founding && ! empty( $item['code'] ) ) : ?>
			<span class="fsb-item-sep" aria-hidden="true">—</span>
			<span class="fsb-code-pill">
				<?php if ( ! empty( $item['code_label'] ) ) : ?>
					<span class="fsb-code-label"><?php echo esc_html( $item['code_label'] ); ?></span>
				<?php endif; ?>
				<strong class="fsb-code-value"><?php echo esc_html( (string) $item['code'] ); ?></strong>
			</span>
		<?php elseif ( $tagline ) : ?>
			<span class="fsb-item-sep" aria-hidden="true">—</span>
			<span class="fsb-item-tagline"><?php echo esc_html( $tagline ); ?></span>
		<?php endif; ?>
	</span>
	<?php
}

/**
 * Render duplicated marquee track for infinite scroll.
 *
 * @param array<int, array<string, mixed>> $items Marquee items.
 * @return void
 */
function ftd_render_banner_marquee_track( $items ) {
	if ( empty( $items ) ) {
		return;
	}

	$sets = array( $items, $items );
	?>
	<div class="fsb-marquee-viewport" aria-hidden="true">
		<div class="fsb-marquee-track">
			<?php foreach ( $sets as $set ) : ?>
				<span class="fsb-marquee-group">
					<?php foreach ( $set as $item ) : ?>
						<?php ftd_render_banner_marquee_item( $item ); ?>
					<?php endforeach; ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
