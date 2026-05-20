<?php
/**
 * PMPro affiliate link helpers (site + MailPoet + shortcodes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Production join page used for all affiliate links (?pa= is appended). */
define( 'FTD_AFFILIATE_LINK_BASE', 'https://wearegeniuses.com/join-we-are-geniuses/' );

/**
 * Base URL for affiliate links (?pa= is appended).
 *
 * @return string
 */
function ftd_get_affiliate_link_base() {
	return trailingslashit(
		apply_filters( 'ftd_affiliate_link_base', FTD_AFFILIATE_LINK_BASE )
	);
}

/**
 * @param int|null $user_id User ID (defaults to current user).
 * @return string Affiliate code or empty.
 */
function ftd_get_user_affiliate_code( $user_id = null ) {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return '';
	}

	if ( function_exists( 'pmpro_affiliates_getAffiliatesForUser' ) ) {
		$affiliates = pmpro_affiliates_getAffiliatesForUser( $user_id );
		if ( ! empty( $affiliates[0]->code ) ) {
			return sanitize_text_field( $affiliates[0]->code );
		}
	}

	$meta = get_user_meta( $user_id, 'pmpro_affiliate_code', true );
	if ( is_string( $meta ) && '' !== trim( $meta ) ) {
		return sanitize_text_field( trim( $meta ) );
	}

	return '';
}

/**
 * @param int|null $user_id User ID (defaults to current user).
 * @return string Affiliate URL or empty.
 */
function ftd_get_user_affiliate_link( $user_id = null ) {
	$code = ftd_get_user_affiliate_code( $user_id );

	if ( '' === $code ) {
		return '';
	}

	return add_query_arg( 'pa', $code, ftd_get_affiliate_link_base() );
}

/**
 * @param string $url Full URL.
 * @return string Display-friendly link (no protocol or query).
 */
function ftd_social_share_display_link( $url ) {
	$display = preg_replace( '#^https?://#i', '', $url );
	$display = preg_replace( '#\?.*$#', '', $display );

	return rtrim( $display, '/' );
}
