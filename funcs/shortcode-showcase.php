<?php
/**
 * Page template: Shortcode & style reference showcase.
 *
 * MAINTENANCE — update this file whenever you add plugin shortcodes or styles:
 * - New shortcode → ftd_get_shortcode_showcase_groups()
 * - New CSS component → ftd_enqueue_shortcode_showcase_assets() $styles array
 * - New brand colour / background / typography → helpers below + template samples
 *
 * Test page: create a Page using template "WAG Shortcode & Style Showcase".
 * See .cursor/rules/directory-scope.mdc for the full checklist.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FTD_SHORTCODE_SHOWCASE_TEMPLATE', 'ftd-page-shortcode-showcase.php' );

add_filter( 'theme_page_templates', 'ftd_register_shortcode_showcase_template' );
add_filter( 'template_include', 'ftd_shortcode_showcase_template_include', 97 );
add_action( 'wp_enqueue_scripts', 'ftd_enqueue_shortcode_showcase_assets', 30 );

/**
 * @param array<string, string> $templates Page templates.
 * @return array<string, string>
 */
function ftd_register_shortcode_showcase_template( $templates ) {
	$templates[ FTD_SHORTCODE_SHOWCASE_TEMPLATE ] = __( 'WAG Shortcode & Style Showcase', 'ftd-directory-listings' );

	return $templates;
}

/**
 * @param string $template Current template.
 * @return string
 */
function ftd_shortcode_showcase_template_include( $template ) {
	if ( ! is_page() || FTD_SHORTCODE_SHOWCASE_TEMPLATE !== get_page_template_slug( get_queried_object_id() ) ) {
		return $template;
	}

	$plugin_template = plugin_dir_path( FTD_DIRECTORY_LISTINGS_FILE ) . 'templates/page-shortcode-showcase.php';

	return file_exists( $plugin_template ) ? $plugin_template : $template;
}

/**
 * Enqueue all component styles for the showcase page.
 *
 * Add new css/{component}.css files here when created.
 */
function ftd_enqueue_shortcode_showcase_assets() {
	if ( ! is_page() || FTD_SHORTCODE_SHOWCASE_TEMPLATE !== get_page_template_slug( get_queried_object_id() ) ) {
		return;
	}

	$base   = plugin_dir_url( FTD_DIRECTORY_LISTINGS_FILE );
	$ver    = FTD_DIRECTORY_LISTINGS_VERSION;
	$styles = array(
		'ftd-showcase'           => 'css/shortcode-showcase.css',
		'ftd-page-header'        => 'css/page-header.css',
		'ftd-community-calls'    => 'css/community-calls.css',
		'ftd-sc-founding-genius' => 'css/founding-genius-banner.css',
		'ftd-sc-founding-spots'  => 'css/founding-spots-banner.css',
		'ftd-sc-genius-cta'      => 'css/genius-cta.css',
		'ftd-sc-wag-features'    => 'css/wag-features-ctas.css',
		'ftd-sc-social-share'    => 'css/social-share.css',
		'ftd-sc-favourite-quotes'=> 'css/favourite-quotes.css',
		'ftd-sc-gnls-session-cta'=> 'css/gnls-session-cta.css',
		'ftd-sc-gnls-join-cta'   => 'css/gnls-join-cta.css',
	);

	foreach ( $styles as $handle => $relative ) {
		wp_enqueue_style( $handle, $base . $relative, array( 'directory-listings-style' ), $ver );
	}

	if ( function_exists( 'ftd_register_founding_genius_banner_assets' ) ) {
		wp_enqueue_script( 'ftd-founding-genius-banner' );
	}

	if ( function_exists( 'ftd_register_social_share_assets' ) ) {
		ftd_register_social_share_assets();
		wp_enqueue_script( 'ftd-social-share' );
	}

	if ( function_exists( 'ftd_register_favourite_quotes_assets' ) ) {
		ftd_register_favourite_quotes_assets();
		wp_enqueue_script( 'ftd-favourite-quotes' );
	}
}

/**
 * Shortcode groups for the showcase page.
 *
 * Add every new plugin shortcode here (with aliases and copy-paste code).
 *
 * @return array<int, array{title: string, description: string, items: array<int, array<string, mixed>>}>
 */
function ftd_get_shortcode_showcase_groups() {
	return array(
		array(
			'title'       => __( 'Live Sessions — added in recent work', 'ftd-directory-listings' ),
			'description' => __( 'New shortcodes for the Genius Network Live Sessions archive, promos, and CTAs.', 'ftd-directory-listings' ),
			'items'       => array(
				array(
					'name'    => 'gnls_session_cta',
					'aliases' => array( 'live_session_cta' ),
					'code'    => '[gnls_session_cta]',
					'notes'   => __( 'Promo card for the next/upcoming session. Optional: id="123", show_button="0", link_to_session="1".', 'ftd-directory-listings' ),
					'render'  => true,
				),
				array(
					'name'    => 'gnls_join_cta',
					'aliases' => array( 'live_sessions_join_cta' ),
					'code'    => '[gnls_join_cta]',
					'notes'   => __( 'Join/register CTA block. Copy editable in Genius Directory Settings → Live Sessions join CTA.', 'ftd-directory-listings' ),
					'render'  => true,
				),
			),
		),
		array(
			'title'       => __( 'Membership & founding offer', 'ftd-directory-listings' ),
			'description' => '',
			'items'       => array(
				array(
					'name'    => 'founding_genius_banner',
					'aliases' => array(),
					'code'    => '[founding_genius_banner]',
					'notes'   => __( 'Animated founding spots counter + progress bar + discount code.', 'ftd-directory-listings' ),
					'render'  => true,
				),
				array(
					'name'    => 'wag_stats_ticker',
					'aliases' => array( 'ftd_stats_ticker' ),
					'code'    => '[wag_stats_ticker]',
					'notes'   => __( 'Scrolling community stats marquee.', 'ftd-directory-listings' ),
					'render'  => true,
				),
				array(
					'name'    => 'genius_levels_cta',
					'aliases' => array(),
					'code'    => '[genius_levels_cta]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'genius_buttons',
					'aliases' => array(),
					'code'    => '[genius_buttons]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'geniuses_join_link',
					'aliases' => array(),
					'code'    => '[geniuses_join_link]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'ftd_membership_pending_notice',
					'aliases' => array(),
					'code'    => '[ftd_membership_pending_notice]',
					'notes'   => __( 'Shows only when relevant to the logged-in member.', 'ftd-directory-listings' ),
					'render'  => true,
				),
			),
		),
		array(
			'title'       => __( 'Community & sharing', 'ftd-directory-listings' ),
			'description' => '',
			'items'       => array(
				array(
					'name'    => 'WAG_Features_CTAS',
					'aliases' => array( 'genius_calls' ),
					'code'    => '[WAG_Features_CTAS]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'wag_social_share',
					'aliases' => array( 'ftd_social_share' ),
					'code'    => '[wag_social_share]',
					'notes'   => __( 'Requires login + PMPro affiliate code.', 'ftd-directory-listings' ),
					'render'  => true,
				),
				array(
					'name'    => 'wag_favourite_quotes',
					'aliases' => array( 'ftd_favourite_quotes' ),
					'code'    => '[wag_favourite_quotes]',
					'notes'   => '',
					'render'  => true,
				),
			),
		),
		array(
			'title'       => __( 'Directory & listings', 'ftd-directory-listings' ),
			'description' => '',
			'items'       => array(
				array(
					'name'    => 'user_directory_listings',
					'aliases' => array(),
					'code'    => '[user_directory_listings]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'custom_member_profile',
					'aliases' => array(),
					'code'    => '[custom_member_profile]',
					'notes'   => __( 'Best viewed on a member profile URL.', 'ftd-directory-listings' ),
					'render'  => true,
				),
				array(
					'name'    => 'my_directory_listings_account_page',
					'aliases' => array(),
					'code'    => '[my_directory_listings_account_page]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'my_directory_listings_as_list',
					'aliases' => array(),
					'code'    => '[my_directory_listings_as_list]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'directory_listing_usage',
					'aliases' => array(),
					'code'    => '[directory_listing_usage]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'directory_map_btns',
					'aliases' => array(),
					'code'    => '[directory_map_btns]',
					'notes'   => '',
					'render'  => true,
				),
				array(
					'name'    => 'map_signup_cta_box',
					'aliases' => array(),
					'code'    => '[map_signup_cta_box]',
					'notes'   => '',
					'render'  => true,
				),
			),
		),
		array(
			'title'       => __( 'Forms & affiliates (copy only — not rendered)', 'ftd-directory-listings' ),
			'description' => __( 'These output forms or member-specific UI; paste the tag where needed instead of previewing here.', 'ftd-directory-listings' ),
			'items'       => array(
				array(
					'name'    => 'add_user_directory_listings',
					'aliases' => array(),
					'code'    => '[add_user_directory_listings]',
					'notes'   => __( 'Add listing form.', 'ftd-directory-listings' ),
					'render'  => false,
				),
				array(
					'name'    => 'edit_user_directory_listings',
					'aliases' => array(),
					'code'    => '[edit_user_directory_listings]',
					'notes'   => __( 'Edit listings form.', 'ftd-directory-listings' ),
					'render'  => false,
				),
				array(
					'name'    => 'member_map_settings_form',
					'aliases' => array(),
					'code'    => '[member_map_settings_form]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'ftd_top_affiliates',
					'aliases' => array(),
					'code'    => '[ftd_top_affiliates]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'ftd_top_affiliates_month',
					'aliases' => array(),
					'code'    => '[ftd_top_affiliates_month]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'pmpro_affiliates_report',
					'aliases' => array(),
					'code'    => '[pmpro_affiliates_report]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'ftd_affiliate_sidebar',
					'aliases' => array(),
					'code'    => '[ftd_affiliate_sidebar]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'pmpro_level_description',
					'aliases' => array(),
					'code'    => '[pmpro_level_description]',
					'notes'   => '',
					'render'  => false,
				),
				array(
					'name'    => 'login_prompt_not_logged_in',
					'aliases' => array(),
					'code'    => '[login_prompt_not_logged_in]',
					'notes'   => '',
					'render'  => false,
				),
			),
		),
	);
}

/**
 * Brand colour tokens for the style guide.
 *
 * @return array<int, array{var: string, hex: string, label: string}>
 */
function ftd_get_showcase_color_tokens() {
	return array(
		array( 'var' => '--color-purple', 'hex' => '#673f69', 'label' => 'Purple' ),
		array( 'var' => '--color-coral', 'hex' => '#F26849', 'label' => 'Coral' ),
		array( 'var' => '--color-golden', 'hex' => '#F2A341', 'label' => 'Golden' ),
		array( 'var' => '--color-pinkcoral', 'hex' => '#d13d60', 'label' => 'Pink coral' ),
		array( 'var' => '--color-pinkdusk', 'hex' => '#d94e81', 'label' => 'Pink dusk' ),
		array( 'var' => '--color-cyan', 'hex' => '#00E6E6', 'label' => 'Cyan' ),
		array( 'var' => '--color-green', 'hex' => '#247556', 'label' => 'Green' ),
		array( 'var' => '--color-orange', 'hex' => '#e67e22', 'label' => 'Orange' ),
		array( 'var' => '--color-darkgrey', 'hex' => '#111111', 'label' => 'Dark grey' ),
		array( 'var' => '--color-midgrey', 'hex' => '#4b4b4b', 'label' => 'Mid grey' ),
		array( 'var' => '--color-lightgrey', 'hex' => '#6b6b6b', 'label' => 'Light grey' ),
	);
}

/**
 * Section background swatches used across the site.
 *
 * @return array<int, array{label: string, value: string, note: string}>
 */
function ftd_get_showcase_backgrounds() {
	$backgrounds = array(
		array(
			'label' => __( 'Archive intro stone', 'ftd-directory-listings' ),
			'value' => '#f7f0ea',
			'note'  => '.gcc-archive-intro',
		),
		array(
			'label' => __( 'Featured session hero stone', 'ftd-directory-listings' ),
			'value' => '#f7f0ea',
			'note'  => '.gcc-featured-hero',
		),
		array(
			'label' => __( 'Page header default purple', 'ftd-directory-listings' ),
			'value' => '#673f69',
			'note'  => 'page_header_background_color',
		),
		array(
			'label' => __( 'Founding genius banner plum', 'ftd-directory-listings' ),
			'value' => '#2d132c',
			'note'  => '.fg-banner',
		),
		array(
			'label' => __( 'Favourite quotes plum', 'ftd-directory-listings' ),
			'value' => '#512851',
			'note'  => '.ftd-sc--favourite-quotes',
		),
		array(
			'label' => __( 'Session CTA default purple', 'ftd-directory-listings' ),
			'value' => '#673f69',
			'note'  => 'cta_background_color',
		),
	);

	if ( function_exists( 'ftd_get_page_header_background_presets' ) ) {
		foreach ( ftd_get_page_header_background_presets() as $key => $preset ) {
			$url = ftd_get_page_header_background_preset_url( $key );
			if ( $url ) {
				$backgrounds[] = array(
					'label' => $preset['label'],
					'value' => $url,
					'note'  => 'page_header preset: ' . $key,
					'image' => true,
				);
			}
		}
	}

	return $backgrounds;
}
