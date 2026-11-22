<?php
/**
 * Search results.
 *
 * @package Appverse
 */

get_header();

$av_query  = get_search_query();
$av_total  = (int) $GLOBALS['wp_query']->found_posts;
$av_apps   = array();
$av_posts  = array();
$av_others = array();

foreach ( $GLOBALS['wp_query']->posts as $av_result ) {
	if ( 'app' === $av_result->post_type ) {
		$av_apps[] = $av_result;
	} elseif ( 'post' === $av_result->post_type ) {
		$av_posts[] = $av_result;
	} else {
		$av_others[] = $av_result;
	}
}
?>
<main id="av-main" class="av-main av-main--search">

	<?php get_search_form(); ?>

	<?php
	appverse_page_heading(
		sprintf( /* translators: %s query */ __( '“%s” 的搜索结果', 'appverse' ), $av_query ),
		sprintf( /* translators: %d results */ __( '共找到 %d 条内容', 'appverse' ), $av_total ),
		__( 'SEARCH', 'appverse' )
	);
	?>

	<?php if ( $av_apps ) : ?>
		<section class="av-search-section">
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '应用', 'appverse' ); ?></h2>
				<span class="av-section__more"><?php echo esc_html( (string) count( $av_apps ) ); ?></span>
			</div>
			<div class="av-appgrid">
				<?php
				global $post;
				foreach ( $av_apps as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					get_template_part( 'template-parts/app-card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $av_posts ) : ?>
		<section class="av-search-section">
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '动态', 'appverse' ); ?></h2>
				<span class="av-section__more"><?php echo esc_html( (string) count( $av_posts ) ); ?></span>
			</div>
			<div class="av-feed">
				<?php
				foreach ( $av_posts as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					get_template_part( 'template-parts/post-card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $av_others ) : ?>
		<section class="av-search-section">
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '其他内容', 'appverse' ); ?></h2>
			</div>
			<div class="av-card av-card--pad av-search-list">
				<?php foreach ( $av_others as $av_other ) : ?>
					<a class="av-searchrow" href="<?php echo esc_url( get_permalink( $av_other ) ); ?>">
						<span class="av-searchrow__title"><?php echo esc_html( get_the_title( $av_other ) ); ?></span>
						<?php appverse_the_icon( 'chevron-right', 16 ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! $av_apps && ! $av_posts && ! $av_others ) : ?>
		<?php
		get_template_part(
			'template-parts/empty',
			null,
			array(
				'icon'  => 'search',
				'title' => __( '没有找到相关内容', 'appverse' ),
				'text'  => __( '换一个关键词试试，例如应用名、分类或开发者。', 'appverse' ),
				'cta'   => __( '回到首页', 'appverse' ),
				'url'   => home_url( '/' ),
			)
		);
		?>
	<?php endif; ?>

	<?php appverse_pagination(); ?>

</main>
<?php
get_footer();
