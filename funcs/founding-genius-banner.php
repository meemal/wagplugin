<?php
/**
 * Founding Genius Banner Shortcode
 *
 * Displays the founding member offer banner with live discount code
 * usage pulled from Paid Memberships Pro.
 *
 * Usage: [founding_genius_banner]
 * Requirements: Paid Memberships Pro active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function ftd_founding_genius_banner_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'total'          => 111,
			'code'           => 'originalgenius111',
			'stamp_line1'    => 'Founding',
			'stamp_line2'    => 'Geniuses',
			'badge'          => 'Founding offer',
			'eyebrow'        => 'Limited to the first {total} members',
			'headline'       => 'Lifetime directory listing &mdash; completely free',
			'subtext'        => 'Be part of the founding community. The first {total} Geniuses who join get a permanent directory listing with no subscription, ever.',
			'code_label'     => 'Use code',
			'spots_text'     => '{used} of {total} spots claimed',
			'all_claimed'    => 'All {total} founding spots have been claimed',
			'claimed_notice' => 'The founding offer has now closed. Join now to access our standard plans.',
		),
		$atts,
		'founding_genius_banner'
	);

	$total       = intval( $atts['total'] );
	$coupon_code = sanitize_text_field( strtolower( $atts['code'] ) );

	$used = 0;

	if ( function_exists( 'pmpro_getDiscountCode' ) ) {
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

		if ( $discount ) {
			$used = intval(
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*)
						 FROM {$wpdb->prefix}pmpro_discount_codes_uses dcu
						 INNER JOIN {$wpdb->prefix}pmpro_membership_orders mo
							 ON dcu.order_id = mo.id
						 WHERE dcu.code_id = %d
						   AND mo.status IN ('success', 'pending')",
						$discount->id
					)
				)
			);
		}
	}

	$remaining = max( 0, $total - $used );
	$percent   = $total > 0 ? min( 100, round( ( $used / $total ) * 100 ) ) : 0;

	$tokens = array(
		'{total}'     => $total,
		'{used}'      => $used,
		'{remaining}' => $remaining,
		'{code}'      => strtoupper( $coupon_code ),
	);

	$replace = function ( $string ) use ( $tokens ) {
		return str_replace( array_keys( $tokens ), array_values( $tokens ), $string );
	};

	$badge          = esc_html( $replace( $atts['badge'] ) );
	$eyebrow        = esc_html( $replace( $atts['eyebrow'] ) );
	$headline       = wp_kses_post( $replace( $atts['headline'] ) );
	$subtext        = esc_html( $replace( $atts['subtext'] ) );
	$code_label     = esc_html( $replace( $atts['code_label'] ) );
	$stamp_line1    = esc_html( $replace( $atts['stamp_line1'] ) );
	$stamp_line2    = esc_html( $replace( $atts['stamp_line2'] ) );
	$claimed_notice = esc_html( $replace( $atts['claimed_notice'] ) );

	$spots_label = $remaining > 0
		? esc_html( $replace( $atts['spots_text'] ) )
		: esc_html( $replace( $atts['all_claimed'] ) );

	ob_start();
	?>
	<div class="fg-banner" role="region" aria-label="<?php echo esc_attr( $badge ); ?>">

		<div class="fg-stamp" aria-hidden="true">
			<span class="fg-stamp-num"><?php echo esc_html( $total ); ?></span>
			<span class="fg-stamp-label"><?php echo $stamp_line1; ?></span>
			<?php if ( $stamp_line2 ) : ?>
				<span class="fg-stamp-label"><?php echo $stamp_line2; ?></span>
			<?php endif; ?>
		</div>

		<div class="fg-body">

			<?php if ( $badge ) : ?>
				<span class="fg-pill"><?php echo $badge; ?></span>
			<?php endif; ?>

			<?php if ( $eyebrow ) : ?>
				<p class="fg-eyebrow"><?php echo $eyebrow; ?></p>
			<?php endif; ?>

			<?php if ( $headline ) : ?>
				<h2 class="fg-headline"><?php echo $headline; ?></h2>
			<?php endif; ?>

			<?php if ( $subtext || $code_label ) : ?>
				<p class="fg-sub">
					<?php echo $subtext; ?>
					<?php if ( $code_label ) : ?>
						<?php echo ' ' . $code_label; ?>
						<strong><?php echo esc_html( strtoupper( $coupon_code ) ); ?></strong>.
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<div class="fg-spots-row" aria-label="<?php echo esc_attr( $spots_label ); ?>">
				<div class="fg-track"
					 role="progressbar"
					 aria-valuenow="<?php echo esc_attr( $used ); ?>"
					 aria-valuemin="0"
					 aria-valuemax="<?php echo esc_attr( $total ); ?>">
					<div class="fg-fill" style="width:<?php echo esc_attr( $percent ); ?>%;"></div>
				</div>
				<span class="fg-spots-text"><?php echo $spots_label; ?></span>
			</div>

			<?php if ( $remaining <= 0 && $claimed_notice ) : ?>
				<p class="fg-claimed-notice"><?php echo $claimed_notice; ?></p>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'founding_genius_banner', 'ftd_founding_genius_banner_shortcode' );
