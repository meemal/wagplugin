<?php
/**
 * Single template: Genius Network Live Session.
 *
 * @package FTD_Directory_Listings
 */

get_header();

$post_id      = get_the_ID();
$long_desc    = ftd_get_community_call_long_description( $post_id );
$youtube      = ftd_get_community_call_youtube_link( $post_id );
$embed_url    = ftd_get_youtube_embed_url( $youtube );
$share_html   = ftd_render_live_session_share_buttons( $post_id );
?>
<main class="ftd-sc ftd-sc--community-calls gcc-single">
	<div class="gcc-single-content">
		<div class="gcc-single-content-inner">
			<?php echo ftd_render_community_call_single_back_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<article <?php post_class( 'gcc-single-article' ); ?>>
				<?php echo ftd_render_community_call_single_hero( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<div class="gcc-single-layout">
					<div class="gcc-single-main">
						<?php if ( $long_desc ) : ?>
							<div class="gcc-single-body entry-content">
								<?php echo wp_kses_post( $long_desc ); ?>
							</div>
						<?php endif; ?>

						<?php
						echo ftd_render_community_call_featuring_section( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>

						<?php
						if ( function_exists( 'ftd_render_gnls_next_steps_cta' ) ) {
							echo ftd_render_gnls_next_steps_cta(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>

						<?php if ( $share_html ) : ?>
							<?php echo $share_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						<?php endif; ?>

						<?php if ( $embed_url ) : ?>
							<section class="gcc-single-recording">
								<h2 class="gcc-section-title"><?php esc_html_e( 'Recording', 'ftd-directory-listings' ); ?></h2>
								<div class="gcc-video-embed std-border-radius">
									<iframe
										src="<?php echo esc_url( $embed_url ); ?>"
										title="<?php echo esc_attr( get_the_title() ); ?>"
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
										allowfullscreen
										loading="lazy"
									></iframe>
								</div>
							</section>
						<?php endif; ?>
					</div>

					<?php echo ftd_render_community_call_details_sidebar( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

			</article>
		</div>
	</div>

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
