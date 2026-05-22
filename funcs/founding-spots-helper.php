<?php
/**
 * Founding discount code usage (Paid Memberships Pro).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Count successful PMPro orders using a discount code.
 *
 * @param string $coupon_code Discount code (case-insensitive).
 * @return int
 */
function ftd_get_pmpro_discount_code_uses( $coupon_code ) {
	$coupon_code = sanitize_text_field( strtolower( $coupon_code ) );

	if ( '' === $coupon_code || ! function_exists( 'pmpro_getDiscountCode' ) ) {
		return 0;
	}

	global $wpdb;

	$discount = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id
			 FROM {$wpdb->prefix}pmpro_discount_codes
			 WHERE LOWER(code) = %s
			 LIMIT 1",
			$coupon_code
		)
	);

	if ( ! $discount ) {
		return 0;
	}

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			 FROM {$wpdb->prefix}pmpro_discount_codes_uses dcu
			 INNER JOIN {$wpdb->prefix}pmpro_membership_orders mo
				 ON dcu.order_id = mo.id
			 WHERE dcu.code_id = %d
			   AND mo.status IN ('success', 'pending')",
			$discount->id
		)
	);
}

/**
 * Discount codes that count toward founding-genius spot usage.
 *
 * @return string[]
 */
function ftd_get_founding_spots_discount_codes() {
	return apply_filters(
		'ftd_founding_spots_discount_codes',
		array(
			'originalgenius111',
			'loveyougive',
			'geniushelpers555',
		)
	);
}

/**
 * Total founding spots in the originalgenius111 pool (display cap).
 *
 * @return int
 */
function ftd_get_founding_spots_total() {
	return (int) apply_filters( 'ftd_founding_spots_total', 111 );
}

/**
 * Combined founding spots claimed (Creator + Quantum Genius members).
 *
 * @return int
 */
function ftd_get_founding_spots_used() {
	$creator = function_exists( 'ftd_stats_count_creator_geniuses' )
		? ftd_stats_count_creator_geniuses()
		: 0;
	$quantum = function_exists( 'ftd_stats_count_quantum_geniuses' )
		? ftd_stats_count_quantum_geniuses()
		: 0;

	return (int) apply_filters( 'ftd_founding_spots_used', $creator + $quantum );
}
