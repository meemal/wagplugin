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
