<?php
/**
 * Template Name: 我的（个人中心）
 * Description: 应用商店个人中心，包含等级、资产、升级与应用管理。
 *
 * @package Appverse
 */

get_header();

$logged_in = is_user_logged_in();
$stats     = appverse_get_user_stats();
$user_id   = get_current_user_id();
$upgrade   = appverse_query_apps(
	array(
		'posts_per_page' => 6,
		'no_found_rows'  => true,
	)
);
$manage_items = array(
	array(
		'label' => __( '下载管理', 'appverse' ),
		'icon'  => 'download',
		'url'   => appverse_page_url( 'downloads' ),
	),
	array(
		'label' => __( '应用管理', 'appverse' ),
		'icon'  => 'grid',
		'url'   => appverse_page_url( 'apps-manage' ),
	),
	array(
		'label' => __( '应用集', 'appverse' ),
		'icon'  => 'collection',
		'url'   => appverse_page_url( 'collections' ),
	),
	array(
		'label' => __( '应用筛选', 'appverse' ),
		'icon'  => 'filter',
		'url'   => appverse_page_url( 'filter' ),
	),
);
?>
<main id="av-main" class="av-main av-main--profile">

	<section class="av-card av-profile-card">
		<div class="av-profile__head">
			<?php if ( $logged_in ) : ?>
				<?php appverse_avatar( $user_id, 'lg' ); ?>
			<?php else : ?>
				<span class="av-avatar av-avatar--lg">
					<?php appverse_the_icon( 'user', 40, 'av-avatar__placeholder' ); ?>
				</span>
			<?php endif; ?>

			<div class="av-profile__info">
				<div class="av-profile__row">
					<h1 class="av-profile__name"><?php echo esc_html( $stats['name'] ); ?></h1>
					<?php if ( $logged_in ) : ?>
						<span class="av-level">Lv.<?php echo esc_html( (string) $stats['level'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $logged_in ) : ?>
					<div class="av-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( (string) $stats['exp_max'] ); ?>" aria-valuenow="<?php echo esc_attr( (string) $stats['exp'] ); ?>">
						<span class="av-progress__bar" style="--value:<?php echo esc_attr( (string) $stats['exp_percent'] ); ?>%"></span>
					</div>
					<div class="av-progress__meta">
						<span><?php echo esc_html( (string) $stats['exp'] ); ?></span>
						<span class="av-progress__max">/ <?php echo esc_html( (string) $stats['exp_max'] ); ?></span>
					</div>
				<?php else : ?>
					<p class="av-profile__guest-text"><?php esc_html_e( '登录后同步等级、资产与下载记录', 'appverse' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $logged_in ) : ?>
				<button class="av-checkin<?php echo $stats['checked_in'] ? ' is-done' : ''; ?>" type="button" data-checkin data-checked="<?php echo $stats['checked_in'] ? '1' : '0'; ?>">
					<?php echo $stats['checked_in'] ? esc_html__( '已签到', 'appverse' ) : esc_html__( '签到', 'appverse' ); ?>
				</button>
			<?php else : ?>
				<a class="av-checkin" href="<?php echo esc_url( wp_login_url( appverse_page_url( 'me' ) ) ); ?>"><?php esc_html_e( '登录', 'appverse' ); ?></a>
			<?php endif; ?>
		</div>
	</section>

	<div class="av-statgrid">
		<a class="av-stat" href="<?php echo esc_url( appverse_page_url( 'square' ) ); ?>">
			<span class="av-stat__num"><?php echo esc_html( (string) $stats['posts'] ); ?></span>
			<span class="av-stat__label"><?php esc_html_e( '动态', 'appverse' ); ?></span>
		</a>
		<a class="av-stat" href="<?php echo esc_url( $logged_in ? get_author_posts_url( $user_id ) : wp_login_url( appverse_page_url( 'me' ) ) ); ?>">
			<span class="av-stat__num"><?php echo esc_html( (string) $stats['following'] ); ?></span>
			<span class="av-stat__label"><?php esc_html_e( '关注', 'appverse' ); ?></span>
		</a>
	</div>

	<section class="av-card av-card--pad av-assets">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php esc_html_e( '我的资产', 'appverse' ); ?></h2>
		</div>
		<div class="av-assetrow">
			<span class="av-assetrow__icon"><?php appverse_the_icon( 'chart', 20 ); ?></span>
			<span class="av-assetrow__item"><span class="av-assetrow__label"><?php esc_html_e( '积分', 'appverse' ); ?>：</span><b class="av-assetrow__value"><?php echo esc_html( (string) $stats['points'] ); ?></b></span>
			<span class="av-assetrow__item"><span class="av-assetrow__label"><?php esc_html_e( '分享值', 'appverse' ); ?>：</span><b class="av-assetrow__value"><?php echo esc_html( (string) $stats['share_value'] ); ?></b></span>
			<span class="av-assetrow__item"><span class="av-assetrow__label"><?php esc_html_e( '流量', 'appverse' ); ?>：</span><b class="av-assetrow__value"><?php echo esc_html( $stats['traffic'] ); ?></b></span>
			<a class="av-assetrow__more" href="<?php echo esc_url( appverse_page_url( 'points' ) ); ?>">
				<?php esc_html_e( '查看', 'appverse' ); ?><?php appverse_the_icon( 'chevron-right', 14 ); ?>
			</a>
		</div>
	</section>

	<section class="av-card av-card--pad av-upgrade">
		<span class="av-upgrade__bell"><?php appverse_the_icon( 'bell', 22, 'av-upgrade__bell-icon' ); ?></span>
		<div class="av-upgrade__body">
			<h2 class="av-section__title"><?php esc_html_e( '应用升级', 'appverse' ); ?></h2>
			<div class="av-upgrade__icons">
				<?php
				if ( $upgrade->have_posts() ) :
					while ( $upgrade->have_posts() ) :
						$upgrade->the_post();
						appverse_app_icon( get_the_ID(), 'xs', array( 'badge' => false, 'class' => 'av-upgrade__icon' ) );
					endwhile;
					wp_reset_postdata();
					?>
				<?php else : ?>
					<span class="av-upgrade__placeholder"><?php esc_html_e( '暂无更新', 'appverse' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<a class="av-upgrade__side" href="<?php echo esc_url( appverse_apps_url( array( 'sort' => 'new' ) ) ); ?>">
			<span><?php esc_html_e( '升级', 'appverse' ); ?></span>
			<span class="av-badge"><?php echo esc_html( (string) appverse_notification_count() ); ?></span>
		</a>
	</section>

	<nav class="av-quickgrid av-quickgrid--3 av-quickgrid--tiles" aria-label="<?php esc_attr_e( '会员与积分', 'appverse' ); ?>">
		<a class="av-quick av-quick--tile" href="<?php echo esc_url( appverse_page_url( 'member' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'vip', 24 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '会员中心', 'appverse' ); ?></span>
		</a>
		<a class="av-quick av-quick--tile" href="<?php echo esc_url( appverse_page_url( 'tasks' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'task', 24 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '任务中心', 'appverse' ); ?></span>
		</a>
		<a class="av-quick av-quick--tile" href="<?php echo esc_url( appverse_page_url( 'points' ) ); ?>">
			<span class="av-quick__icon"><?php appverse_the_icon( 'gift', 24 ); ?></span>
			<span class="av-quick__label"><?php esc_html_e( '积分商城', 'appverse' ); ?></span>
		</a>
	</nav>

	<section class="av-card av-card--pad av-manage">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php esc_html_e( '应用管理', 'appverse' ); ?></h2>
		</div>
		<div class="av-tilegrid av-tilegrid--4">
			<?php foreach ( $manage_items as $item ) : ?>
				<a class="av-tile" href="<?php echo esc_url( $item['url'] ); ?>">
					<span class="av-tile__icon"><?php appverse_the_icon( $item['icon'], 24 ); ?></span>
					<span class="av-tile__label"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php if ( ! $logged_in ) : ?>
		<a class="av-btn av-btn--primary av-btn--block av-profile-login" href="<?php echo esc_url( wp_login_url( appverse_page_url( 'me' ) ) ); ?>"><?php esc_html_e( '登录 / 注册', 'appverse' ); ?></a>
	<?php endif; ?>

</main>
<?php
get_footer();
