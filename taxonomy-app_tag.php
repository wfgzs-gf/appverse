<?php
/**
 * App tag archive.
 *
 * @package Appverse
 */

get_header();
$term     = get_queried_object();
$base_url = get_term_link( $term );
$base_url = is_wp_error( $base_url ) ? appverse_apps_url() : $base_url;
?>
<main id="av-main" class="av-main av-main--archive">
	<?php
	appverse_page_heading(
		single_term_title( '', false ),
		wp_strip_all_tags( term_description( $term ) ),
		__( '应用标签', 'appverse' )
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
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'folder', 'title' => __( '没有相关应用', 'appverse' ) ) ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
