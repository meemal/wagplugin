<?php
/**
 * Archive template: Genius Network Live Sessions.
 *
 * @package FTD_Directory_Listings
 */

get_header();

ftd_the_page_header( FTD_PAGE_HEADER_ARCHIVE_OPTION );

$featured_session_id = ftd_get_featured_live_session_id();
echo ftd_render_gnls_featured_session_panel(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
?>
<main class="ftd-sc ftd-sc--community-calls gcc-archive">
	<?php ftd_render_gnls_archive_intro(); ?>

	<?php
	$archive_settings = function_exists( 'ftd_get_gnls_archive_settings' ) ? ftd_get_gnls_archive_settings() : array();
	$sessions_title     = $archive_settings['sessions_title'] ?? __( 'Sessions', 'ftd-directory-listings' );
	$sessions_empty     = $archive_settings['sessions_empty_msg'] ?? __( 'No live sessions have been published yet. Check back soon — the first call details will appear here.', 'ftd-directory-listings' );
	?>

	<section class="gcc-archive-sessions">
		<?php if ( $sessions_title ) : ?>
			<h2 class="gcc-page-section-title text-purple"><?php echo esc_html( $sessions_title ); ?></h2>
		<?php endif; ?>

		<?php
		$grid_session_ids = array();

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$loop_id = get_the_ID();

				if ( $featured_session_id && $loop_id === $featured_session_id ) {
					continue;
				}

				$grid_session_ids[] = $loop_id;
			}
		}

		if ( ! empty( $grid_session_ids ) ) :
			?>
			<div class="gcc-archive-grid">
				<?php
				foreach ( $grid_session_ids as $post_id ) :
					$short_desc   = ftd_get_community_call_short_description( $post_id );
					$members      = ftd_get_community_call_members( $post_id );
					$feature_html = ftd_get_community_call_feature_image_html( $post_id, 'medium_large', array( 'class' => 'gcc-card-image std-border-radius' ) );
					?>
					<article <?php post_class( 'gcc-card card', $post_id ); ?>>
						<?php if ( $feature_html ) : ?>
							<a class="gcc-card-image-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
								<?php echo $feature_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by wp_get_attachment_image. ?>
							</a>
						<?php endif; ?>

						<div class="gcc-card-body">
							<p class="gcc-card-kicker"><?php echo esc_html( ftd_gnls_kicker() ); ?></p>
							<h3 class="gcc-card-title">
								<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
							</h3>

							<?php if ( $short_desc ) : ?>
								<p class="gcc-card-subtitle"><?php echo esc_html( $short_desc ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $members ) ) : ?>
								<p class="gcc-card-members">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %s: comma-separated member names */
											__( 'With %s', 'ftd-directory-listings' ),
											implode(
												', ',
												array_map(
													static function ( $member ) {
														return $member['name'];
													},
													$members
												)
											)
										)
									);
									?>
								</p>
							<?php endif; ?>

							<a class="btn gcc-card-btn" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
								<?php esc_html_e( 'View session', 'ftd-directory-listings' ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="gcc-archive-empty">
				<p><?php echo esc_html( $sessions_empty ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<?php ftd_render_gnls_archive_outro(); ?>

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
