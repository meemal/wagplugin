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
	$sessions_title     = $archive_settings['sessions_title'] ?? __( 'Next Live Genius Networking Calls', 'ftd-directory-listings' );
	$sessions_empty     = $archive_settings['sessions_empty_msg'] ?? __( 'No live sessions have been published yet. Check back soon — the first call details will appear here.', 'ftd-directory-listings' );
	?>

	<section class="gcc-archive-sessions">
		<?php if ( $sessions_title ) : ?>
			<h2 class="gcc-archive-intro-title text-purple gcc-archive-sessions-title"><?php echo esc_html( $sessions_title ); ?></h2>
		<?php endif; ?>

		<?php
		if ( $featured_session_id ) {
			echo ftd_render_gnls_up_next_session( $featured_session_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		}

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
				foreach ( $grid_session_ids as $post_id ) {
					echo ftd_render_gnls_archive_session_card( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				}
				?>
			</div>
		<?php elseif ( ! $featured_session_id ) : ?>
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
