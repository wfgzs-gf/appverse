<?php
/**
 * App collection archive.
 *
 * @package Appverse
 */

get_header();

$term = get_queried_object();
?>
<main id="av-main" class="av-main av-main--archive">
	<?php
	appverse_page_heading(
		single_term_title( '', false ),
		wp_strip_all_tags( term_description( $term ) ),
		__( '应用集', 'appverse' )
	);
	?>

	<?php if ( have_posts() ) : ?>
		<div class="av-appgrid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/app-card' );
			endwhile;
			?>
		</div>
		<?php appverse_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'collection', 'title' => __( '这个应用集还是空的', 'appverse' ), 'text' => __( '编辑应用时把它加入应用集即可。', 'appverse' ) ) ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
