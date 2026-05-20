<?php
/**
 * MailPoet shortcodes / personalization tags for member affiliate links.
 *
 * Classic editor: [wag:affiliate_link]
 * New email editor: [wag/affiliate-link] (insert via personalization picker under "We Are Geniuses")
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string>
 */
function ftd_mailpoet_affiliate_shortcode_map() {
	return array(
		'affiliate_link'         => 'link',
		'affiliate_link_display' => 'display',
		'affiliate_code'         => 'code',
		'affiliate-link'         => 'link',
		'affiliate-link-display' => 'display',
		'affiliate-code'         => 'code',
	);
}

/**
 * Detect affiliate shortcode type from a MailPoet shortcode string.
 *
 * @param string $shortcode Raw shortcode e.g. [wag:affiliate_link | default:].
 * @return string|null link|display|code
 */
function ftd_mailpoet_match_affiliate_shortcode_type( $shortcode ) {
	$shortcode = html_entity_decode( trim( (string) $shortcode ), ENT_QUOTES, 'UTF-8' );
	$shortcode = preg_replace( '/\s*\|.*?(?=\])/s', '', $shortcode );

	if ( ! preg_match( '/\[(wag|ftd)[:\\/]([^\]]+)\]/i', $shortcode, $matches ) ) {
		return null;
	}

	$action = strtolower( str_replace( '-', '_', $matches[2] ) );
	$map    = ftd_mailpoet_affiliate_shortcode_map();

	return $map[ $action ] ?? null;
}

/**
 * Resolve WordPress user ID from a MailPoet subscriber.
 *
 * @param mixed $subscriber MailPoet SubscriberEntity or null.
 * @param bool  $wp_user_preview Preview mode flag.
 * @return int
 */
function ftd_mailpoet_subscriber_to_user_id( $subscriber, $wp_user_preview = false ) {
	if ( $wp_user_preview && is_user_logged_in() ) {
		return (int) get_current_user_id();
	}

	if ( ! $subscriber || ! is_object( $subscriber ) ) {
		return 0;
	}

	if ( method_exists( $subscriber, 'getWpUserId' ) ) {
		$wp_user_id = (int) $subscriber->getWpUserId();
		if ( $wp_user_id > 0 ) {
			return $wp_user_id;
		}
	}

	$email = '';
	if ( method_exists( $subscriber, 'getEmail' ) ) {
		$email = (string) $subscriber->getEmail();
	}

	if ( '' !== $email ) {
		$user = get_user_by( 'email', $email );
		if ( $user instanceof WP_User ) {
			return (int) $user->ID;
		}
	}

	return 0;
}

/**
 * @param int|null $user_id           WordPress user ID.
 * @param string   $type              link|display|code.
 * @param string   $default           Fallback when no affiliate data.
 * @return string
 */
function ftd_mailpoet_affiliate_value_for_user( $user_id, $type, $default = '' ) {
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return $default;
	}

	if ( 'code' === $type ) {
		$code = ftd_get_user_affiliate_code( $user_id );
		return $code ? $code : $default;
	}

	$link = ftd_get_user_affiliate_link( $user_id );
	if ( '' === $link ) {
		return $default;
	}

	if ( 'display' === $type ) {
		return ftd_social_share_display_link( $link );
	}

	return $link;
}

/**
 * @param mixed  $subscriber        MailPoet subscriber.
 * @param bool   $wp_user_preview   Preview flag.
 * @param string $type              link|display|code.
 * @param string $default           Fallback when no affiliate data.
 * @return string
 */
function ftd_mailpoet_resolve_affiliate_value( $subscriber, $wp_user_preview, $type, $default = '' ) {
	$user_id = ftd_mailpoet_subscriber_to_user_id( $subscriber, $wp_user_preview );
	return ftd_mailpoet_affiliate_value_for_user( $user_id, $type, $default );
}

/**
 * Classic MailPoet newsletter shortcodes.
 *
 * @param string $shortcode         Shortcode token.
 * @param mixed  $newsletter        Newsletter entity.
 * @param mixed  $subscriber        Subscriber entity.
 * @param mixed  $queue             Sending queue.
 * @param string $content           Email content.
 * @param array  $arguments         Parsed shortcode arguments.
 * @param bool   $wp_user_preview   Preview mode.
 * @return string
 */
function ftd_mailpoet_newsletter_affiliate_shortcode( $shortcode, $newsletter, $subscriber, $queue, $content, $arguments, $wp_user_preview ) {
	$type = ftd_mailpoet_match_affiliate_shortcode_type( $shortcode );
	if ( null === $type ) {
		return $shortcode;
	}

	$default = '';
	if ( is_array( $arguments ) && isset( $arguments['default'] ) ) {
		$default = (string) $arguments['default'];
	}

	return ftd_mailpoet_resolve_affiliate_value( $subscriber, (bool) $wp_user_preview, $type, $default );
}

add_filter( 'mailpoet_newsletter_shortcode', 'ftd_mailpoet_newsletter_affiliate_shortcode', 10, 7 );

/**
 * Register affiliate personalization tags (new MailPoet email editor).
 *
 * @param mixed $registry Personalization tags registry.
 * @return mixed
 */
function ftd_mailpoet_register_affiliate_personalization_tags( $registry ) {
	if ( ! is_object( $registry ) || ! method_exists( $registry, 'register' ) ) {
		return $registry;
	}

	if ( ! class_exists( 'Automattic\WooCommerce\EmailEditor\Engine\PersonalizationTags\Personalization_Tag' ) ) {
		return $registry;
	}

	$tag_class = 'Automattic\WooCommerce\EmailEditor\Engine\PersonalizationTags\Personalization_Tag';
	$post_type = 'mailpoet_email';

	$tags = array(
		array( __( 'Affiliate link', 'ftd-directory-listings' ), 'wag/affiliate-link', 'ftd_mailpoet_personalize_affiliate_link' ),
		array( __( 'Affiliate link (display)', 'ftd-directory-listings' ), 'wag/affiliate-link-display', 'ftd_mailpoet_personalize_affiliate_link_display' ),
		array( __( 'Affiliate code', 'ftd-directory-listings' ), 'wag/affiliate-code', 'ftd_mailpoet_personalize_affiliate_code' ),
	);

	foreach ( $tags as $tag_def ) {
		$token = '[' . $tag_def[1] . ']';
		if ( method_exists( $registry, 'get_by_token' ) && $registry->get_by_token( $token ) ) {
			continue;
		}

		$registry->register(
			new $tag_class(
				$tag_def[0],
				$tag_def[1],
				__( 'We Are Geniuses', 'ftd-directory-listings' ),
				$tag_def[2],
				array(),
				null,
				array( $post_type )
			)
		);
	}

	return $registry;
}

/**
 * Ensure tags are registered even if the registry initialized early.
 */
function ftd_mailpoet_ensure_personalization_tags_registered() {
	if ( ! class_exists( 'Automattic\WooCommerce\EmailEditor\Email_Editor_Container' ) ) {
		return;
	}

	try {
		$registry = \Automattic\WooCommerce\EmailEditor\Email_Editor_Container::container()->get(
			'Automattic\WooCommerce\EmailEditor\Engine\PersonalizationTags\Personalization_Tags_Registry'
		);
		ftd_mailpoet_register_affiliate_personalization_tags( $registry );
	} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		// Email editor container not ready yet.
	}
}

add_filter( 'woocommerce_email_editor_register_personalization_tags', 'ftd_mailpoet_register_affiliate_personalization_tags', 5 );
add_filter( 'mailpoet_automation_email_extend_personalization_tags', 'ftd_mailpoet_register_affiliate_personalization_tags', 10 );
add_action( 'mailpoet_automation_email_extend_personalization_tags_for_sending', 'ftd_mailpoet_ensure_personalization_tags_registered', 5 );
add_action( 'plugins_loaded', 'ftd_mailpoet_ensure_personalization_tags_registered', 99 );
add_action( 'init', 'ftd_mailpoet_ensure_personalization_tags_registered', 99 );

/**
 * Register tags when editing any MailPoet email post.
 *
 * @param int|string $post_id Post ID.
 */
function ftd_mailpoet_extend_tags_for_email_post( $post_id ) {
	if ( 'mailpoet_email' !== get_post_type( (int) $post_id ) ) {
		return;
	}
	ftd_mailpoet_ensure_personalization_tags_registered();
}

add_action( 'woocommerce_email_editor_personalization_tags_for_post', 'ftd_mailpoet_extend_tags_for_email_post', 5 );

/**
 * Add recipient WP user ID to personalization context when possible.
 *
 * @param array<string, mixed> $context   Context.
 * @param array                $subjects  Automation subjects.
 * @return array<string, mixed>
 */
function ftd_mailpoet_personalization_context( $context, $subjects = array() ) {
	unset( $subjects );

	if ( empty( $context['recipient_email'] ) ) {
		return $context;
	}

	$user = get_user_by( 'email', (string) $context['recipient_email'] );
	if ( $user instanceof WP_User ) {
		$context['recipient_wp_user_id'] = (int) $user->ID;
	}

	return $context;
}

add_filter( 'mailpoet_automation_email_personalization_context', 'ftd_mailpoet_personalization_context', 10, 2 );

/**
 * @param array<string, mixed> $context Send context.
 * @param array                $args    Tag args.
 * @param string               $type    link|display|code.
 * @return string
 */
function ftd_mailpoet_personalize_affiliate_from_context( $context, $args, $type ) {
	$default = isset( $args['default'] ) ? (string) $args['default'] : '';

	if ( ! empty( $context['recipient_wp_user_id'] ) ) {
		return ftd_mailpoet_affiliate_value_for_user( (int) $context['recipient_wp_user_id'], $type, $default );
	}

	$email = isset( $context['recipient_email'] ) ? (string) $context['recipient_email'] : '';
	if ( '' === $email ) {
		return $default;
	}

	$user = get_user_by( 'email', $email );
	if ( ! $user instanceof WP_User ) {
		return $default;
	}

	return ftd_mailpoet_affiliate_value_for_user( $user->ID, $type, $default );
}

/**
 * @param array<string, mixed> $context Send context.
 * @param array                $args    Tag args.
 * @return string
 */
function ftd_mailpoet_personalize_affiliate_link( $context, $args = array() ) {
	return ftd_mailpoet_personalize_affiliate_from_context( $context, $args, 'link' );
}

/**
 * @param array<string, mixed> $context Send context.
 * @param array                $args    Tag args.
 * @return string
 */
function ftd_mailpoet_personalize_affiliate_link_display( $context, $args = array() ) {
	return ftd_mailpoet_personalize_affiliate_from_context( $context, $args, 'display' );
}

/**
 * @param array<string, mixed> $context Send context.
 * @param array                $args    Tag args.
 * @return string
 */
function ftd_mailpoet_personalize_affiliate_code( $context, $args = array() ) {
	return ftd_mailpoet_personalize_affiliate_from_context( $context, $args, 'code' );
}

/**
 * Normalize slash-format tags to colon format for the classic shortcode processor.
 *
 * @param string $content Email HTML or text.
 * @return string
 */
function ftd_mailpoet_normalize_affiliate_shortcodes_in_content( $content ) {
	$map = array(
		'[wag/affiliate-link]'         => '[wag:affiliate_link]',
		'[wag/affiliate-link-display]' => '[wag:affiliate_link_display]',
		'[wag/affiliate-code]'         => '[wag:affiliate_code]',
		'[ftd/affiliate-link]'         => '[ftd:affiliate_link]',
		'[ftd/affiliate-link-display]' => '[ftd:affiliate_link_display]',
		'[ftd/affiliate-code]'         => '[ftd:affiliate_code]',
	);

	return str_replace( array_keys( $map ), array_values( $map ), $content );
}

/**
 * @param array<string, string> $email_content Rendered newsletter.
 * @return array<string, string>
 */
function ftd_mailpoet_normalize_rendered_newsletter_shortcodes( $email_content ) {
	if ( ! is_array( $email_content ) ) {
		return $email_content;
	}

	foreach ( array( 'html', 'text' ) as $key ) {
		if ( ! empty( $email_content[ $key ] ) && is_string( $email_content[ $key ] ) ) {
			$email_content[ $key ] = ftd_mailpoet_normalize_affiliate_shortcodes_in_content( $email_content[ $key ] );
		}
	}

	return $email_content;
}

add_filter( 'mailpoet_sending_newsletter_render_after_pre_process', 'ftd_mailpoet_normalize_rendered_newsletter_shortcodes', 8 );

/**
 * Document shortcodes in MailPoet classic editor shortcode list (admin JS).
 *
 * @param array<string, array<int, array<string, string>>> $shortcodes Shortcode groups.
 * @return array<string, array<int, array<string, string>>>
 */
function ftd_mailpoet_add_affiliate_shortcodes_to_helper( $shortcodes ) {
	if ( ! is_array( $shortcodes ) ) {
		$shortcodes = array();
	}

	$group = __( 'We Are Geniuses', 'ftd-directory-listings' );

	if ( ! isset( $shortcodes[ $group ] ) ) {
		$shortcodes[ $group ] = array();
	}

	$shortcodes[ $group ][] = array(
		'text'      => __( 'Affiliate link (full URL)', 'ftd-directory-listings' ),
		'shortcode' => '[wag:affiliate_link]',
	);
	$shortcodes[ $group ][] = array(
		'text'      => __( 'Affiliate link (display)', 'ftd-directory-listings' ),
		'shortcode' => '[wag:affiliate_link_display]',
	);
	$shortcodes[ $group ][] = array(
		'text'      => __( 'Affiliate code', 'ftd-directory-listings' ),
		'shortcode' => '[wag:affiliate_code]',
	);

	return $shortcodes;
}

add_filter( 'mailpoet_shortcodes', 'ftd_mailpoet_add_affiliate_shortcodes_to_helper' );
add_filter( 'mailpoet_newsletter_shortcodes', 'ftd_mailpoet_add_affiliate_shortcodes_to_helper' );
