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
			'total'           => 111,
			'code'            => 'originalgenius111',
			'badge'           => 'Founding genius offer',
			'headline'        => 'Free lifetime listing &mdash; forever',
			'subtext'         => 'be one of the original {total},',
			'code_label'      => 'use code',
			'joined_text'     => '{used} joined',
			'remaining_text'  => '{remaining} left',
			'all_claimed'     => 'All {total} founding spots have been claimed',
			'claimed_notice'  => 'The founding offer has now closed. Join now to access our standard plans.',
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
	$headline       = wp_kses_post( $replace( $atts['headline'] ) );
	$subtext        = esc_html( $replace( $atts['subtext'] ) );
	$code_label     = esc_html( $replace( $atts['code_label'] ) );
	$joined_label   = esc_html( $replace( $atts['joined_text'] ) );
	$remaining_label = esc_html( $replace( $atts['remaining_text'] ) );
	$claimed_notice = esc_html( $replace( $atts['claimed_notice'] ) );
	$all_claimed    = esc_html( $replace( $atts['all_claimed'] ) );

	$counter_aria = sprintf(
		/* translators: 1: spots remaining, 2: total spots */
		__( '%1$d of %2$d spots remaining', 'ftd-directory-listings' ),
		$remaining,
		$total
	);

	ob_start();
	?>
	<div class="ftd-sc ftd-sc--founding-genius">
		<div class="fg-banner" role="region" aria-label="<?php echo esc_attr( $badge ); ?>">

			<?php if ( $badge ) : ?>
				<p class="fg-label"><?php echo $badge; ?></p>
			<?php endif; ?>

			<?php if ( $headline ) : ?>
				<h2 class="fg-headline"><?php echo $headline; ?></h2>
			<?php endif; ?>

			<?php if ( $subtext ) : ?>
				<p class="fg-subtext"><?php echo $subtext; ?></p>
			<?php endif; ?>

			<?php if ( $remaining > 0 ) : ?>
				<div class="fg-counter" aria-label="<?php echo esc_attr( $counter_aria ); ?>">
					<span class="fg-counter-num"><?php echo esc_html( $remaining ); ?></span>
					<span class="fg-counter-of">of</span>
					<span class="fg-counter-total"><?php echo esc_html( $total ); ?></span>
				</div>

				<div class="fg-progress">
					<div class="fg-track"
						 role="progressbar"
						 aria-valuenow="<?php echo esc_attr( $used ); ?>"
						 aria-valuemin="0"
						 aria-valuemax="<?php echo esc_attr( $total ); ?>"
						 aria-label="<?php echo esc_attr( $joined_label . ', ' . $remaining_label ); ?>">
						<div class="fg-fill" style="width:<?php echo esc_attr( $percent ); ?>%;"></div>
					</div>
					<div class="fg-progress-labels">
						<span class="fg-joined"><?php echo $joined_label; ?></span>
						<span class="fg-left"><?php echo $remaining_label; ?></span>
					</div>
				</div>

				<?php if ( $code_label ) : ?>
					<div class="fg-code-pill">
						<span class="fg-code-label"><?php echo $code_label; ?></span>
						<strong class="fg-code-value"><?php echo esc_html( strtoupper( $coupon_code ) ); ?></strong>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<p class="fg-all-claimed"><?php echo $all_claimed; ?></p>
				<?php if ( $claimed_notice ) : ?>
					<p class="fg-claimed-notice"><?php echo $claimed_notice; ?></p>
				<?php endif; ?>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'founding_genius_banner', 'ftd_founding_genius_banner_shortcode' );
