<?php
/**
 * App archive.
 *
 * @package Appverse
 */

get_header();

$sort     = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'new'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$base_url = get_post_type_archive_link( 'app' );
$badge    = isset( $_GET['badge'] ) ? sanitize_key( wp_unslash( $_GET['badge'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<main id="av-main" class="av-main av-main--archive">

	<?php
	appverse_page_heading(
		__( '应用库', 'appverse' ),
		__( '所有上架应用，按下载热度与更新时间排列。', 'appverse' ),
		__( 'APP LIBRARY', 'appverse' )
	);
	?>

	<div class="av-toolbar">
		<?php appverse_sort_bar( $base_url, $sort ); ?>
		<a class="av-toolbar__filter<?php echo 'official' === $badge ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'badge', 'official', $base_url ) ); ?>" aria-label="<?php esc_attr_e( '只看官方版', 'appverse' ); ?>">
			<?php appverse_the_icon( 'shield', 18 ); ?>
		</a>
	</div>

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
		<?php
		get_template_part(
			'template-parts/empty',
			null,
			array(
				'icon'  => 'grid',
				'title' => __( '没有找到应用', 'appverse' ),
				'text'  => __( '试试其他分类，或回到首页看看推荐。', 'appverse' ),
				'cta'   => __( '回到首页', 'appverse' ),
				'url'   => home_url( '/' ),
			)
		);
		?>
	<?php endif; ?>

</main>
<?php
get_footer();
