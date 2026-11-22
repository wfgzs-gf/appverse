<?php
/**
 * Fallback template.
 *
 * @package Appverse
 */

get_header();
?>
<main id="av-main" class="av-main av-main--index">
	<?php
	appverse_page_heading(
		is_home() && ! is_front_page() ? get_the_title( (int) get_option( 'page_for_posts' ) ) : get_bloginfo( 'name' ),
		get_bloginfo( 'description' ),
		__( 'FEED', 'appverse' )
	);
	?>

	<?php if ( have_posts() ) : ?>
		<div class="av-feed">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( 'app' === get_post_type() ) {
					echo '<div class="av-appgrid av-appgrid--single">';
					get_template_part( 'template-parts/app-card' );
					echo '</div>';
				} elseif ( 'post' === get_post_type() ) {
					get_template_part( 'template-parts/post-card' );
				} else {
					?>
					<article class="av-card av-card--pad av-pagebody">
						<h2 class="av-section__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="av-prose"><?php the_excerpt(); ?></div>
					</article>
					<?php
				}
			endwhile;
			?>
		</div>
		<?php appverse_pagination(); ?>
	<?php else : ?>
		<?php
		get_template_part(
			'template-parts/empty',
			null,
			array(
				'icon'  => 'sparkles',
				'title' => __( '还没有内容', 'appverse' ),
				'text'  => __( '发布第一篇文章，或者先导入演示内容看看效果。', 'appverse' ),
			)
		);
		?>
	<?php endif; ?>
</main>
<?php
get_footer();
