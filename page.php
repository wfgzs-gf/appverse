<?php
/**
 * Default page template.
 *
 * @package Appverse
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="av-main" class="av-main av-main--page">
		<?php appverse_page_heading( get_the_title(), get_the_excerpt() ); ?>

		<article class="av-card av-card--pad av-pagebody">
			<div class="av-prose">
				<?php the_content(); ?>
			</div>
			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="av-pager">',
					'after'  => '</nav>',
				)
			);
			?>
			<?php edit_post_link( __( '编辑页面', 'appverse' ), '<p class="av-edit-link">', '</p>' ); ?>
		</article>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<section class="av-card av-card--pad">
				<?php comments_template(); ?>
			</section>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
