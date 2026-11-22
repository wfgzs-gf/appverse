<?php
/**
 * Front page: app store home.
 *
 * @package Appverse
 */

get_header();

$banners  = appverse_get_banners();
$featured = appverse_featured_apps( 5 );
$sticky   = array_filter( array_map( 'absint', (array) get_option( 'sticky_posts' ) ) );
$app_rows = new WP_Query(
	array(
		'post_type'           => 'app',
		'posts_per_page'      => 3,
		'post_status'         => 'publish',
		'post__not_in'        => $sticky,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	)
);
?>
<main id="av-main" class="av-main av-main--home">
	<h1 class="screen-reader-text"><?php echo esc_html( get_bloginfo( 'name' ) . ' · ' . get_bloginfo( 'description' ) ); ?></h1>

	<?php if ( $banners ) : ?>
		<section class="av-hero<?php echo 1 === count( $banners ) ? ' av-hero--solo' : ''; ?>" data-carousel aria-label="<?php esc_attr_e( '精选推荐', 'appverse' ); ?>">
			<div class="av-hero__viewport">
				<div class="av-hero__track">
					<?php foreach ( $banners as $index => $banner ) : ?>
						<a class="av-hero__slide<?php echo 0 === $index ? ' is-active' : ''; ?>" href="<?php echo esc_url( $banner['url'] ); ?>" style="--hero-img:url('<?php echo esc_url( $banner['image'] ); ?>');">
							<span class="av-hero__media" aria-hidden="true"></span>
							<span class="av-hero__shade" aria-hidden="true"></span>
							<span class="av-hero__content">
								<span class="av-hero__eyebrow"><?php echo esc_html( $banner['eyebrow'] ); ?></span>
								<span class="av-hero__title"><?php echo esc_html( $banner['title'] ); ?><?php if ( ! empty( $banner['latin'] ) ) : ?> <em><?php echo esc_html( $banner['latin'] ); ?></em><?php endif; ?></span>
								<span class="av-hero__subtitle"><?php echo esc_html( $banner['subtitle'] ); ?></span>
								<span class="av-hero__cta"><?php echo esc_html( $banner['cta'] ); ?><?php appverse_the_icon( 'chevron-right', 14 ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( count( $banners ) > 1 ) : ?>
				<div class="av-hero__dots" role="tablist" aria-label="<?php esc_attr_e( '轮播图', 'appverse' ); ?>">
					<?php foreach ( $banners as $index => $banner ) : ?>
						<button class="av-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" role="tab" aria-label="<?php echo esc_attr( sprintf( /* translators: %d slide */ __( '第 %d 张', 'appverse' ), $index + 1 ) ); ?>" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" data-slide="<?php echo esc_attr( (string) $index ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<nav class="av-quickgrid" aria-label="<?php esc_attr_e( '快捷入口', 'appverse' ); ?>">
		<a class="av-quick" href="<?php echo esc_url( appverse_apps_url( array( 'sort' => 'new' ) ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'sparkles', 25 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '最新', 'appverse' ); ?></span>
		</a>
		<a class="av-quick" href="<?php echo esc_url( appverse_page_url( 'ranking' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'trophy', 25 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '排行', 'appverse' ); ?></span>
		</a>
		<a class="av-quick" href="<?php echo esc_url( appverse_page_url( 'categories' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'grid', 25 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '分类', 'appverse' ); ?></span>
		</a>
		<a class="av-quick" href="<?php echo esc_url( appverse_page_url( 'checkin' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'calendar-check', 25 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '签到', 'appverse' ); ?></span>
		</a>
		<a class="av-quick" href="<?php echo esc_url( appverse_page_url( 'indie' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'code', 25 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '独立开发', 'appverse' ); ?></span>
		</a>
	</nav>

	<section class="av-card av-card--pad av-recommend">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php esc_html_e( '推荐应用', 'appverse' ); ?></h2>
			<a class="av-section__more" href="<?php echo esc_url( appverse_apps_url() ); ?>">
				<?php esc_html_e( '全部', 'appverse' ); ?><?php appverse_the_icon( 'chevron-right', 14 ); ?>
			</a>
		</div>

		<?php if ( $featured->have_posts() ) : ?>
			<div class="av-rec">
				<?php
				while ( $featured->have_posts() ) :
					$featured->the_post();
					?>
					<a class="av-rec__item" href="<?php the_permalink(); ?>">
						<?php appverse_app_icon( get_the_ID(), 'md' ); ?>
						<span class="av-rec__name"><?php echo esc_html( get_the_title() ); ?></span>
					</a>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<?php if ( $app_rows->have_posts() ) : ?>
			<div class="av-applist">
				<?php
				while ( $app_rows->have_posts() ) :
					$app_rows->the_post();
					get_template_part( 'template-parts/app-row' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/empty', null, array( 'title' => __( '还没有上架应用', 'appverse' ), 'text' => __( '在后台「应用商店 → 上架应用」中添加第一个应用。', 'appverse' ) ) ); ?>
		<?php endif; ?>
	</section>

	<section class="av-card av-card--pad av-manage">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php esc_html_e( '应用管理', 'appverse' ); ?></h2>
			<a class="av-section__more" href="<?php echo esc_url( appverse_page_url( 'apps-manage' ) ); ?>">
				<?php esc_html_e( '管理', 'appverse' ); ?><?php appverse_the_icon( 'chevron-right', 14 ); ?>
			</a>
		</div>
		<div class="av-tilegrid av-tilegrid--4">
			<a class="av-tile" href="<?php echo esc_url( appverse_page_url( 'downloads' ) ); ?>">
				<span class="av-tile__icon"><?php appverse_the_icon( 'download', 24 ); ?></span>
				<span class="av-tile__label"><?php esc_html_e( '下载管理', 'appverse' ); ?></span>
			</a>
			<a class="av-tile" href="<?php echo esc_url( appverse_page_url( 'apps-manage' ) ); ?>">
				<span class="av-tile__icon"><?php appverse_the_icon( 'grid', 24 ); ?></span>
				<span class="av-tile__label"><?php esc_html_e( '应用管理', 'appverse' ); ?></span>
			</a>
			<a class="av-tile" href="<?php echo esc_url( appverse_page_url( 'collections' ) ); ?>">
				<span class="av-tile__icon"><?php appverse_the_icon( 'collection', 24 ); ?></span>
				<span class="av-tile__label"><?php esc_html_e( '应用集', 'appverse' ); ?></span>
			</a>
			<a class="av-tile" href="<?php echo esc_url( appverse_page_url( 'filter' ) ); ?>">
				<span class="av-tile__icon"><?php appverse_the_icon( 'filter', 24 ); ?></span>
				<span class="av-tile__label"><?php esc_html_e( '应用筛选', 'appverse' ); ?></span>
			</a>
		</div>
	</section>

</main>
<?php
get_footer();
