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
 * @return void
 */
function ftd_stats_ticker_clear_cache() {
	delete_transient( FTD_STATS_TICKER_CACHE_KEY );
}

add_action( 'pmpro_after_checkout', 'ftd_stats_ticker_clear_cache', 10, 2 );
add_action( 'user_register', 'ftd_stats_ticker_clear_cache' );
add_action( 'profile_update', 'ftd_stats_ticker_clear_cache' );

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
 * Ticker slides with live values.
 *
 * @return array<int, array<string, mixed>>
 */
function ftd_get_stats_ticker_slides() {
	$cached = get_transient( FTD_STATS_TICKER_CACHE_KEY );

	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$last_country = ftd_stats_get_last_signup_country();

	$slides = array(
		array(
			'value'   => ftd_stats_count_members(),
			'label'   => __( 'members', 'ftd-directory-listings' ),
			'tagline' => __( 'the community grows,', 'ftd-directory-listings' ),
			'type'    => 'number',
		),
		array(
			'value'   => ftd_stats_count_on_map(),
			'label'   => __( 'on the genius map', 'ftd-directory-listings' ),
			'tagline' => __( 'sharing their genius,', 'ftd-directory-listings' ),
			'type'    => 'number',
		),
		array(
			'value'   => ftd_stats_count_quantum_geniuses(),
			'label'   => __( 'quantum geniuses', 'ftd-directory-listings' ),
			'tagline' => __( 'leading the way,', 'ftd-directory-listings' ),
			'type'    => 'number',
		),
		array(
			'value'   => $last_country ? $last_country : __( 'Unknown', 'ftd-directory-listings' ),
			'label'   => __( 'last sign-up from', 'ftd-directory-listings' ),
			'tagline' => __( 'the threshold opens,', 'ftd-directory-listings' ),
			'type'    => 'text',
		),
	);

	$slides = apply_filters( 'ftd_stats_ticker_slides', $slides );

	set_transient( FTD_STATS_TICKER_CACHE_KEY, $slides, FTD_STATS_TICKER_CACHE_TTL );

	return $slides;
}
