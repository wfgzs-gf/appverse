<?php
/**
 * Generic archive.
 *
 * @package Appverse
 */

get_header();
?>
<main id="av-main" class="av-main av-main--archive">
	<?php appverse_page_heading( wp_strip_all_tags( get_the_archive_title() ), wp_strip_all_tags( get_the_archive_description() ), __( 'ARCHIVE', 'appverse' ) ); ?>

	<?php if ( have_posts() ) : ?>
		<div class="av-feed">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			?>
		</div>
		<?php appverse_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'folder', 'title' => __( '暂时没有内容', 'appverse' ) ) ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
