<?php
/**
 * Single template: Genius Network Live Session.
 *
 * @package FTD_Directory_Listings
 */

get_header();

$post_id      = get_the_ID();
$short_desc   = ftd_get_community_call_short_description( $post_id );
$long_desc    = ftd_get_community_call_long_description( $post_id );
$what_need    = ftd_get_community_call_what_do_i_need( $post_id );
$members      = ftd_get_community_call_members( $post_id );
$youtube      = ftd_get_community_call_youtube_link( $post_id );
$embed_url    = ftd_get_youtube_embed_url( $youtube );
$feature_html = ftd_get_community_call_feature_image_html( $post_id, 'large' );
$share_html   = ftd_render_live_session_share_buttons( $post_id );
?>
<main class="ftd-sc ftd-sc--community-calls gcc-single">
	<article <?php post_class( 'gcc-call card' ); ?>>
		<?php if ( $feature_html ) : ?>
			<div class="gcc-call-hero">
				<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<header class="gcc-call-header">
			<p class="gcc-call-kicker"><?php echo esc_html( ftd_gnls_kicker() ); ?></p>
			<h1 class="gcc-call-title"><?php the_title(); ?></h1>
			<?php if ( $short_desc ) : ?>
				<p class="gcc-call-subtitle"><?php echo esc_html( $short_desc ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $members ) ) : ?>
			<div class="gcc-call-members">
				<?php echo ftd_render_community_call_section_title( __( 'Featuring', 'ftd-directory-listings' ), 'featuring' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<ul class="gcc-member-list">
					<?php foreach ( $members as $member ) : ?>
						<li>
							<?php ftd_render_community_call_member_link( $member ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $long_desc ) : ?>
			<div class="gcc-call-content">
				<?php echo ftd_render_community_call_section_title( __( 'What we\'ll do', 'ftd-directory-listings' ), 'do' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="gcc-call-content-body entry-content">
					<?php echo wp_kses_post( $long_desc ); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $what_need ) : ?>
			<div class="gcc-call-need">
				<?php echo ftd_render_community_call_section_title( __( 'What do I need', 'ftd-directory-listings' ), 'need' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="gcc-call-need-body entry-content">
					<?php echo wp_kses_post( wpautop( $what_need ) ); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="gcc-call-video">
			<h2 class="gcc-section-title"><?php esc_html_e( 'YouTube', 'ftd-directory-listings' ); ?></h2>
			<?php if ( $embed_url ) : ?>
				<div class="gcc-video-embed std-border-radius">
					<iframe
						src="<?php echo esc_url( $embed_url ); ?>"
						title="<?php echo esc_attr( get_the_title() ); ?>"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
						allowfullscreen
						loading="lazy"
					></iframe>
				</div>
				<p class="gcc-external-link">
					<a href="<?php echo esc_url( ftd_normalize_external_link( $youtube ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Open on YouTube', 'ftd-directory-listings' ); ?>
					</a>
				</p>
			<?php else : ?>
				<p class="gcc-coming-soon"><?php echo esc_html( $youtube ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $share_html ) : ?>
			<?php echo $share_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endif; ?>
	</article>

	<p class="gcc-back-link">
		<a href="<?php echo esc_url( get_post_type_archive_link( FTD_COMMUNITY_CALL_POST_TYPE ) ); ?>">
			<?php esc_html_e( '← All live sessions', 'ftd-directory-listings' ); ?>
		</a>
	</p>

	<section class="gcc-page-section gcc-page-section--previous">
		<h2 class="gcc-page-section-title"><?php esc_html_e( 'Watch our previous Genius Network Live Sessions', 'ftd-directory-listings' ); ?></h2>
		<?php ftd_render_previous_live_session_slot( $post_id ); ?>
	</section>

	<section class="gcc-page-section gcc-page-section--spots">
		<?php echo do_shortcode( '[founding_genius_banner]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>

	<section class="gcc-page-section gcc-page-section--ctas">
		<?php echo do_shortcode( '[WAG_Features_CTAS]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
</main>
<?php
ftd_the7_after_content();
get_footer();
