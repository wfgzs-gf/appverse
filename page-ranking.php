<?php
/**
 * Template Name: 应用排行
 * Description: 应用下载排行榜。
 *
 * @package Appverse
 */

get_header();

$range = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : 'hot'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$args  = array(
	'post_type'      => 'app',
	'posts_per_page' => 20,
	'post_status'    => 'publish',
);

if ( 'new' === $range ) {
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
	unset( $args['meta_key'] );
} elseif ( 'indie' === $range ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'app_category',
			'field'    => 'slug',
			'terms'    => 'indie',
		),
	);
} else {
	$args['meta_key'] = '_app_downloads';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';
}

$ranking = new WP_Query( $args );
$base    = appverse_page_url( 'ranking' );
$tabs    = array(
	'hot'   => __( '总榜', 'appverse' ),
	'new'   => __( '新锐', 'appverse' ),
	'indie' => __( '独立开发', 'appverse' ),
);
?>
<main id="av-main" class="av-main av-main--ranking">

	<?php
	appverse_page_heading(
		__( '应用排行', 'appverse' ),
		__( '基于真实下载与收藏热度，每天更新。', 'appverse' ),
		__( 'CHARTS', 'appverse' )
	);
	?>

	<nav class="av-filterbar" aria-label="<?php esc_attr_e( '排行榜类型', 'appverse' ); ?>">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a class="av-filter<?php echo $range === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'range', $key, $base ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php if ( $ranking->have_posts() ) : ?>
		<section class="av-card av-card--pad av-ranklist">
			<?php
			$rank = 1;
			while ( $ranking->have_posts() ) :
				$ranking->the_post();
				$meta  = appverse_get_app_meta( get_the_ID() );
				$terms = get_the_terms( get_the_ID(), 'app_category' );
				$cat   = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0]->name : __( '应用', 'appverse' );
				?>
				<a class="av-rankrow<?php echo $rank <= 3 ? ' is-top' : ''; ?>" href="<?php the_permalink(); ?>">
					<span class="av-rankrow__num"><?php echo esc_html( (string) $rank ); ?></span>
					<?php appverse_app_icon( get_the_ID(), 'sm', array( 'badge' => false ) ); ?>
					<span class="av-rankrow__info">
						<span class="av-rankrow__name"><?php echo esc_html( get_the_title() ); ?></span>
						<span class="av-rankrow__meta"><?php echo esc_html( $cat ); ?> · <?php echo esc_html( $meta['version'] ); ?></span>
					</span>
					<span class="av-rankrow__right">
						<span class="av-rankrow__downloads">↓<?php echo esc_html( appverse_format_count( $meta['downloads'] ) ); ?></span>
						<?php echo appverse_get_stars( $meta['rating'], 'av-stars--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
					</span>
					<?php appverse_the_icon( 'chevron-right', 16, 'av-rankrow__chevron' ); ?>
				</a>
				<?php
				++$rank;
			endwhile;
			wp_reset_postdata();
			?>
		</section>
	<?php else : ?>
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'trophy', 'title' => __( '榜单正在生成', 'appverse' ), 'text' => __( '上架更多应用后，这里会自动出现排名。', 'appverse' ) ) ); ?>
	<?php endif; ?>

</main>
<?php
get_footer();
