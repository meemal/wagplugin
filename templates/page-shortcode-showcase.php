<?php
/**
 * Page template: Shortcode & style reference showcase.
 *
 * Assign via Page → Template → "WAG Shortcode & Style Showcase".
 * Keep in sync with funcs/shortcode-showcase.php — see .cursor/rules/directory-scope.mdc.
 *
 * @package FTD_Directory_Listings
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="content" class="content ftd-showcase-page">
		<article <?php post_class( 'ftd-showcase-page__article' ); ?>>
			<header class="ftd-showcase-page__intro">
				<h1 class="ftd-showcase-page__title text-purple"><?php the_title(); ?></h1>
				<?php if ( get_the_content() ) : ?>
					<div class="entry-content ftd-showcase-page__lead">
						<?php the_content(); ?>
					</div>
				<?php else : ?>
					<p class="ftd-showcase-page__lead"><?php esc_html_e( 'Copy shortcode tags from the grey boxes below. Each live preview shows the rendered output.', 'ftd-directory-listings' ); ?></p>
				<?php endif; ?>
			</header>

			<section class="ftd-showcase-section" id="shortcodes-new">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Quick reference — new Live Sessions shortcodes', 'ftd-directory-listings' ); ?></h2>
				<ul class="ftd-showcase-quicklist">
					<li><code>[gnls_session_cta]</code> <?php esc_html_e( 'alias', 'ftd-directory-listings' ); ?> <code>[live_session_cta]</code></li>
					<li><code>[gnls_join_cta]</code> <?php esc_html_e( 'alias', 'ftd-directory-listings' ); ?> <code>[live_sessions_join_cta]</code></li>
				</ul>
				<p class="ftd-showcase-note"><?php esc_html_e( 'Not shortcodes (but related): page header uses ftd_the_page_header() in templates; archive intro/highlights are ACF-driven under Genius Network Live Sessions → Archive page.', 'ftd-directory-listings' ); ?></p>
			</section>

			<?php foreach ( ftd_get_shortcode_showcase_groups() as $group ) : ?>
				<section class="ftd-showcase-section">
					<h2 class="ftd-showcase-section__title text-purple"><?php echo esc_html( $group['title'] ); ?></h2>
					<?php if ( ! empty( $group['description'] ) ) : ?>
						<p class="ftd-showcase-section__desc"><?php echo esc_html( $group['description'] ); ?></p>
					<?php endif; ?>

					<?php foreach ( $group['items'] as $item ) : ?>
						<div class="ftd-showcase-block" id="<?php echo esc_attr( 'sc-' . sanitize_title( $item['name'] ) ); ?>">
							<header class="ftd-showcase-block__header">
								<h3 class="ftd-showcase-block__name"><?php echo esc_html( $item['name'] ); ?></h3>
								<?php if ( ! empty( $item['aliases'] ) ) : ?>
									<p class="ftd-showcase-block__aliases">
										<?php esc_html_e( 'Aliases:', 'ftd-directory-listings' ); ?>
										<?php foreach ( $item['aliases'] as $alias ) : ?>
											<code><?php echo esc_html( $alias ); ?></code>
										<?php endforeach; ?>
									</p>
								<?php endif; ?>
								<pre class="ftd-showcase-copy" tabindex="0"><?php echo esc_html( $item['code'] ); ?></pre>
								<?php if ( ! empty( $item['notes'] ) ) : ?>
									<p class="ftd-showcase-block__notes"><?php echo esc_html( $item['notes'] ); ?></p>
								<?php endif; ?>
							</header>

							<?php if ( ! empty( $item['render'] ) ) : ?>
								<div class="ftd-showcase-block__preview">
									<?php echo do_shortcode( $item['code'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php else : ?>
								<p class="ftd-showcase-block__copy-only"><?php esc_html_e( 'Preview skipped — copy the tag above and paste on the target page.', 'ftd-directory-listings' ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>

			<section class="ftd-showcase-section" id="style-colors">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Brand colours', 'ftd-directory-listings' ); ?></h2>
				<p class="ftd-showcase-section__desc"><?php esc_html_e( 'CSS custom properties in directory-listings.css. Utility classes: .text-{name}', 'ftd-directory-listings' ); ?></p>
				<div class="ftd-showcase-swatches">
					<?php foreach ( ftd_get_showcase_color_tokens() as $token ) : ?>
						<div class="ftd-showcase-swatch">
							<span class="ftd-showcase-swatch__chip" style="background: <?php echo esc_attr( $token['hex'] ); ?>;"></span>
							<span class="ftd-showcase-swatch__label"><?php echo esc_html( $token['label'] ); ?></span>
							<code><?php echo esc_html( $token['var'] ); ?></code>
							<code><?php echo esc_html( $token['hex'] ); ?></code>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="ftd-showcase-text-samples">
					<p class="text-purple"><?php esc_html_e( '.text-purple — section headings', 'ftd-directory-listings' ); ?></p>
					<p class="text-coral"><?php esc_html_e( '.text-coral', 'ftd-directory-listings' ); ?></p>
					<p class="text-golden"><?php esc_html_e( '.text-golden — accent / taglines', 'ftd-directory-listings' ); ?></p>
					<p class="text-pinkcoral"><?php esc_html_e( '.text-pinkcoral', 'ftd-directory-listings' ); ?></p>
					<p class="text-pinkdusk"><?php esc_html_e( '.text-pinkdusk', 'ftd-directory-listings' ); ?></p>
					<p class="text-green"><?php esc_html_e( '.text-green — tags', 'ftd-directory-listings' ); ?></p>
					<p class="text-midgrey"><?php esc_html_e( '.text-midgrey — body copy', 'ftd-directory-listings' ); ?></p>
					<p class="info-dim"><?php esc_html_e( '.info-dim — italic secondary notes', 'ftd-directory-listings' ); ?></p>
				</div>
			</section>

			<section class="ftd-showcase-section" id="style-buttons">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Buttons', 'ftd-directory-listings' ); ?></h2>
				<div class="ftd-showcase-buttons">
					<a class="btn" href="#"><?php esc_html_e( '.btn', 'ftd-directory-listings' ); ?></a>
					<a class="btn btn-secondary" href="#"><?php esc_html_e( '.btn.btn-secondary', 'ftd-directory-listings' ); ?></a>
					<a class="btn btn-coral" href="#"><?php esc_html_e( '.btn.btn-coral', 'ftd-directory-listings' ); ?></a>
					<a class="btn btn-coral-outline" href="#"><?php esc_html_e( '.btn.btn-coral-outline', 'ftd-directory-listings' ); ?></a>
					<a class="btn btn-soft" href="#"><?php esc_html_e( '.btn.btn-soft', 'ftd-directory-listings' ); ?></a>
					<a class="btn btn-small" href="#"><?php esc_html_e( '.btn.btn-small', 'ftd-directory-listings' ); ?></a>
				</div>
			</section>

			<section class="ftd-showcase-section" id="style-backgrounds">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Section backgrounds', 'ftd-directory-listings' ); ?></h2>
				<div class="ftd-showcase-backgrounds">
					<?php foreach ( ftd_get_showcase_backgrounds() as $bg ) : ?>
						<div class="ftd-showcase-bg-card">
							<div
								class="ftd-showcase-bg-card__sample<?php echo ! empty( $bg['image'] ) ? ' ftd-showcase-bg-card__sample--image' : ''; ?>"
								<?php if ( ! empty( $bg['image'] ) ) : ?>
									style="background-image: url('<?php echo esc_url( $bg['value'] ); ?>');"
								<?php else : ?>
									style="background-color: <?php echo esc_attr( $bg['value'] ); ?>;"
								<?php endif; ?>
							></div>
							<p class="ftd-showcase-bg-card__label"><?php echo esc_html( $bg['label'] ); ?></p>
							<code><?php echo esc_html( $bg['note'] ); ?></code>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="ftd-showcase-section" id="style-headings">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Headings & typography patterns', 'ftd-directory-listings' ); ?></h2>
				<div class="ftd-showcase-type-grid">
					<div class="ftd-showcase-type-card">
						<p class="gcc-archive-intro-eyebrow"><?php esc_html_e( '.gcc-archive-intro-eyebrow', 'ftd-directory-listings' ); ?></p>
						<h2 class="gcc-archive-intro-title text-purple"><?php esc_html_e( '.gcc-archive-intro-title', 'ftd-directory-listings' ); ?></h2>
						<p class="gcc-archive-intro-tagline"><?php esc_html_e( '.gcc-archive-intro-tagline — italic serif tagline', 'ftd-directory-listings' ); ?></p>
					</div>
					<div class="ftd-showcase-type-card ftd-showcase-type-card--plum">
						<p class="fg-label"><?php esc_html_e( '.fg-label — founding banner eyebrow', 'ftd-directory-listings' ); ?></p>
						<h2 class="fg-headline"><?php esc_html_e( '.fg-headline', 'ftd-directory-listings' ); ?></h2>
						<p class="fg-subtext"><?php esc_html_e( '.fg-subtext — italic serif', 'ftd-directory-listings' ); ?></p>
					</div>
					<div class="ftd-showcase-type-card">
						<h3 class="gcc-archive-intro-col-title text-purple"><?php esc_html_e( '.gcc-archive-intro-col-title', 'ftd-directory-listings' ); ?></h3>
						<p class="gcc-archive-intro-prose"><?php esc_html_e( '.gcc-archive-intro-prose — body copy in intro columns', 'ftd-directory-listings' ); ?></p>
					</div>
					<div class="ftd-showcase-type-card ftd-showcase-type-card--stone">
						<ul class="gcc-archive-intro-highlights" style="grid-template-columns: 1fr;">
							<li class="gcc-archive-intro-highlight">
								<span class="gcc-archive-intro-highlight-marker" aria-hidden="true"></span>
								<span class="gcc-archive-intro-highlight-text"><?php esc_html_e( '.gcc-archive-intro-highlight-text — italic bullet cards', 'ftd-directory-listings' ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</section>

			<section class="ftd-showcase-section" id="style-quotes">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Quotes', 'ftd-directory-listings' ); ?></h2>
				<div class="ftd-sc ftd-sc--favourite-quotes">
					<div class="ftd-fq-track">
						<figure class="ftd-fq-slide is-active">
							<p class="ftd-fq-mark" aria-hidden="true">“</p>
							<blockquote class="ftd-fq-quote"><p><?php esc_html_e( 'Sample favourite quote — use [wag_favourite_quotes] for the live carousel.', 'ftd-directory-listings' ); ?></p></blockquote>
							<figcaption class="ftd-fq-cite"><?php esc_html_e( 'Member name · .ftd-fq-quote / .ftd-fq-cite', 'ftd-directory-listings' ); ?></figcaption>
						</figure>
					</div>
				</div>
			</section>

			<section class="ftd-showcase-section" id="style-cards">
				<h2 class="ftd-showcase-section__title text-purple"><?php esc_html_e( 'Cards & layout utilities', 'ftd-directory-listings' ); ?></h2>
				<div class="content-card std-border-radius">
					<h3><?php esc_html_e( '.content-card', 'ftd-directory-listings' ); ?></h3>
					<p><?php esc_html_e( 'White card with purple headings. .std-border-radius on images.', 'ftd-directory-listings' ); ?></p>
					<span class="tag"><?php esc_html_e( '.tag', 'ftd-directory-listings' ); ?></span>
				</div>
			</section>
		</article>
	</main>
	<?php
endwhile;

if ( function_exists( 'ftd_the7_after_content' ) ) {
	ftd_the7_after_content();
}

get_footer();
