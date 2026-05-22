<?php
/**
 * The7 theme compatibility for plugin templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'presscore_config_base_init', 'ftd_the7_enable_footer_on_plugin_archives', 20, 2 );

/**
 * Show the standard The7 footer on plugin-driven archive templates.
 *
 * The7 only enables footer widgets on the blog home archive by default.
 *
 * @param string|false $post_type Current post type from config init.
 * @param string|null  $template  Current template slug.
 * @return void
 */
function ftd_the7_enable_footer_on_plugin_archives( $post_type = false, $template = null ) {
	unset( $post_type, $template );

	if ( ! function_exists( 'presscore_config' ) ) {
		return;
	}

	if ( ! is_post_type_archive( FTD_COMMUNITY_CALL_POST_TYPE ) && ! is_post_type_archive( 'directory_listing' ) ) {
		return;
	}

	$config = presscore_config();
	$config->set( 'footer_show', true );
}

/**
 * Output The7's after-content hook before the theme footer.
 *
 * @return void
 */
function ftd_the7_after_content() {
	if ( function_exists( 'do_action' ) ) {
		do_action( 'presscore_after_content' );
	}
}
