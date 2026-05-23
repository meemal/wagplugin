<?php
/**
 * Shortcode: [wag_favourite_quotes]
 *
 * Carousel of members' favourite quotes (profile field), showing initials only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param int $user_id User ID.
 * @return string
 */
function ftd_get_user_favourite_quote_raw( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return '';
	}

	if ( function_exists( 'get_field' ) ) {
		$acf = get_field( 'favourite_dr_joe_quote', 'user_' . $user_id );
		if ( is_string( $acf ) && '' !== trim( $acf ) ) {
			return trim( $acf );
		}
	}

	foreach ( array( 'favourite_dr_joe_quote', 'favourite_quote' ) as $meta_key ) {
		$value = get_user_meta( $user_id, $meta_key, true );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return trim( $value );
		}
	}

	return '';
}

/**
 * @param int $user_id User ID.
 * @return string
 */
function ftd_get_user_favourite_quote_author( $user_id ) {
	$user_id = (int) $user_id;

	foreach ( array( 'favourite_quote_author', 'quote_author', 'favourite_dr_joe_quote_author' ) as $meta_key ) {
		$value = get_user_meta( $user_id, $meta_key, true );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return trim( $value );
		}
	}

	if ( function_exists( 'get_field' ) ) {
		foreach ( array( 'favourite_quote_author', 'quote_author' ) as $field ) {
			$acf = get_field( $field, 'user_' . $user_id );
			if ( is_string( $acf ) && '' !== trim( $acf ) ) {
				return trim( $acf );
			}
		}
	}

	return '';
}

/**
 * Split stored quote into body + attribution when formatted as "quote — Author".
 *
 * @param string $raw Stored profile value.
 * @return array{quote: string, author: string}
 */
function ftd_parse_favourite_quote( $raw ) {
	$raw = trim( (string) $raw );

	if ( '' === $raw ) {
		return array(
			'quote'  => '',
			'author' => '',
		);
	}

	$patterns = array(
		'/^(.+?)\s*[—–]\s*(.+)$/u',
		'/^(.+?)\s+-\s+([^-].+)$/u',
		'/^(.+?)\s*~\s*(.+)$/u',
		'/^(.+?)\s*\|\s*(.+)$/u',
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $raw, $matches ) ) {
			return array(
				'quote'  => trim( $matches[1] ),
				'author' => trim( $matches[2] ),
			);
		}
	}

	return array(
		'quote'  => $raw,
		'author' => '',
	);
}

/**
 * @param int $user_id User ID.
 * @return string e.g. "S.P."
 */
function ftd_get_user_initials( $user_id ) {
	$user = get_userdata( (int) $user_id );

	if ( ! $user ) {
		return '';
	}

	$first = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
	$last  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );

	if ( '' === $first && '' === $last ) {
		$first = trim( (string) $user->first_name );
		$last  = trim( (string) $user->last_name );
	}

	$letters = array();

	if ( '' !== $first ) {
		$letters[] = mb_strtoupper( mb_substr( $first, 0, 1 ) );
	}

	if ( '' !== $last ) {
		$letters[] = mb_strtoupper( mb_substr( $last, 0, 1 ) );
	}

	if ( ! empty( $letters ) ) {
		return implode( '.', $letters ) . '.';
	}

	$parts = preg_split( '/\s+/', trim( $user->display_name ), -1, PREG_SPLIT_NO_EMPTY );

	if ( empty( $parts ) ) {
		return '';
	}

	foreach ( $parts as $part ) {
		$letters[] = mb_strtoupper( mb_substr( $part, 0, 1 ) );
	}

	return implode( '.', array_slice( $letters, 0, 2 ) ) . '.';
}

/**
 * @param int $limit Number of quotes.
 * @return array<int, array{user_id: int, quote: string, author: string, initials: string}>
 */
function ftd_get_random_favourite_quotes( $limit = 10 ) {
	global $wpdb;

	$limit = max( 1, min( 20, (int) $limit ) );

	$user_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT user_id
			FROM {$wpdb->usermeta}
			WHERE meta_key IN ('favourite_dr_joe_quote', 'favourite_quote')
			AND meta_value IS NOT NULL
			AND TRIM(meta_value) <> ''
			ORDER BY RAND()
			LIMIT %d",
			$limit
		)
	);

	$slides = array();

	foreach ( $user_ids as $user_id ) {
		$user_id = (int) $user_id;
		$raw     = ftd_get_user_favourite_quote_raw( $user_id );

		if ( '' === $raw ) {
			continue;
		}

		$parsed = ftd_parse_favourite_quote( $raw );
		$quote  = $parsed['quote'];
		$author = $parsed['author'];

		if ( '' === $author ) {
			$author = ftd_get_user_favourite_quote_author( $user_id );
		}

		if ( '' === $author ) {
			$author = __( 'Dr Joe Dispenza', 'ftd-directory-listings' );
		}

		$initials = ftd_get_user_initials( $user_id );

		if ( '' === $quote || '' === $initials ) {
			continue;
		}

		$slides[] = array(
			'user_id'  => $user_id,
			'quote'    => $quote,
			'author'   => $author,
			'initials' => $initials,
		);
	}

	return apply_filters( 'ftd_favourite_quotes_slides', $slides, $limit );
}

/**
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function ftd_favourite_quotes_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count'    => 10,
			'autoplay' => '1',
			'interval' => 6000,
		),
		$atts,
		'wag_favourite_quotes'
	);

	$slides = ftd_get_random_favourite_quotes( (int) $atts['count'] );

	if ( empty( $slides ) ) {
		return '';
	}

	wp_enqueue_style( 'ftd-sc-favourite-quotes' );
	wp_enqueue_script( 'ftd-favourite-quotes' );

	$autoplay = filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );
	$interval = max( 3000, (int) $atts['interval'] );
	$uid      = wp_unique_id( 'ftd-fq-' );

	ob_start();
	?>
	<div
		class="ftd-sc ftd-sc--favourite-quotes"
		id="<?php echo esc_attr( $uid ); ?>"
		data-ftd-favourite-quotes
		data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
		data-interval="<?php echo esc_attr( $interval ); ?>"
		role="region"
		aria-roledescription="<?php esc_attr_e( 'carousel', 'ftd-directory-listings' ); ?>"
		aria-label="<?php esc_attr_e( 'Favourite quotes from members', 'ftd-directory-listings' ); ?>"
	>
		<div class="ftd-fq-track">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<figure
					class="ftd-fq-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-index="<?php echo esc_attr( $index ); ?>"
					<?php echo 0 !== $index ? ' hidden' : ''; ?>
				>
					<div class="ftd-fq-mark" aria-hidden="true">&ldquo;</div>
					<blockquote class="ftd-fq-quote">
						<p><?php echo esc_html( $slide['quote'] ); ?></p>
					</blockquote>
					<figcaption class="ftd-fq-meta">
						<span class="ftd-fq-initials"><?php echo esc_html( $slide['initials'] ); ?></span>
						<span class="ftd-fq-meta-sep" aria-hidden="true"></span>
						<span class="ftd-fq-source">
							<?php
							printf(
								/* translators: %s: quote author name */
								esc_html__( 'favourite quote by %s', 'ftd-directory-listings' ),
								esc_html( $slide['author'] )
							);
							?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'wag_favourite_quotes', 'ftd_favourite_quotes_shortcode' );
add_shortcode( 'ftd_favourite_quotes', 'ftd_favourite_quotes_shortcode' );

/**
 * Register assets.
 */
function ftd_register_favourite_quotes_assets() {
	wp_register_style(
		'ftd-sc-favourite-quotes',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'css/favourite-quotes.css',
		array( 'directory-listings-style' ),
		ftd_get_plugin_asset_version()
	);

	wp_register_script(
		'ftd-favourite-quotes',
		plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE ) . 'js/favourite-quotes.js',
		array(),
		ftd_get_plugin_asset_version(),
		true
	);
}

add_action( 'wp_enqueue_scripts', 'ftd_register_favourite_quotes_assets', 15 );
