<?php
/**
 * Page template: WAG Page with Header.
 *
 * @package FTD_Directory_Listings
 */

get_header();

while ( have_posts() ) :
	the_post();
	ftd_the_page_header();
	?>
	<main id="content" class="content ftd-page-with-header-content">
		<article <?php post_class(); ?>>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	</main>
	<?php
endwhile;

ftd_the7_after_content();
get_footer();
