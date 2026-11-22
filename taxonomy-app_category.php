<?php
/**
 * App category archive.
 *
 * @package Appverse
 */

get_header();

$term     = get_queried_object();
$sort     = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'new'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$base_url = get_term_link( $term );
$base_url = is_wp_error( $base_url ) ? appverse_apps_url() : $base_url;
?>
<main id="av-main" class="av-main av-main--archive">

	<?php
	appverse_page_heading(
		single_term_title( '', false ),
		wp_strip_all_tags( term_description( $term ) ),
		sprintf( /* translators: %d apps */ __( '%d 款应用', 'appverse' ), (int) $term->count )
	);
	?>
	<?php appverse_sort_bar( $base_url, $sort ); ?>

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
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'folder', 'title' => __( '这个分类还没有应用', 'appverse' ), 'text' => __( '过段时间再来看看吧。', 'appverse' ) ) ); ?>
	<?php endif; ?>

</main>
<?php
get_footer();
