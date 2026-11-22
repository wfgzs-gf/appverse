<?php
/**
 * Template Name: 每日签到
 * Description: 每日签到与积分奖励。
 *
 * @package Appverse
 */

get_header();

$logged_in = is_user_logged_in();
$calendar  = appverse_get_checkin_calendar();
$stats     = appverse_get_user_stats();
$weekdays  = array( '一', '二', '三', '四', '五', '六', '日' );
$rewards   = array(
	array( 'days' => 3, 'points' => 15, 'label' => __( '连续 3 天', 'appverse' ) ),
	array( 'days' => 7, 'points' => 50, 'label' => __( '连续 7 天', 'appverse' ) ),
	array( 'days' => 15, 'points' => 120, 'label' => __( '连续 15 天', 'appverse' ) ),
);
?>
<main id="av-main" class="av-main av-main--checkin">

	<section class="av-checkin-hero">
		<div class="av-checkin-hero__head">
			<div>
				<p class="av-checkin-hero__eyebrow"><?php esc_html_e( 'DAILY CHECK-IN', 'appverse' ); ?></p>
				<h1 class="av-checkin-hero__title"><?php esc_html_e( '每日签到', 'appverse' ); ?></h1>
				<p class="av-checkin-hero__subtitle">
					<?php
					if ( $logged_in ) {
						echo esc_html( sprintf( /* translators: %d days */ __( '已连续签到 %d 天，继续保持。', 'appverse' ), max( 1, (int) $calendar['streak'] ) ) );
					} else {
						esc_html_e( '登录后即可签到，每天都有积分。', 'appverse' );
					}
					?>
				</p>
			</div>
			<span class="av-checkin-hero__stamp"><?php appverse_the_icon( 'calendar-check', 30 ); ?></span>
		</div>

		<div class="av-checkin-hero__foot">
			<div class="av-checkin-hero__points">
				<span class="av-checkin-hero__points-num"><?php echo esc_html( (string) $stats['points'] ); ?></span>
				<span class="av-checkin-hero__points-label"><?php esc_html_e( '当前积分', 'appverse' ); ?></span>
			</div>
			<?php if ( $logged_in ) : ?>
				<button class="av-btn av-btn--primary<?php echo $stats['checked_in'] ? ' is-done' : ''; ?>" type="button" data-checkin data-checked="<?php echo $stats['checked_in'] ? '1' : '0'; ?>">
					<?php echo $stats['checked_in'] ? esc_html__( '今日已签到', 'appverse' ) : esc_html__( '立即签到 +5', 'appverse' ); ?>
				</button>
			<?php else : ?>
				<a class="av-btn av-btn--primary" href="<?php echo esc_url( wp_login_url( appverse_page_url( 'checkin' ) ) ); ?>"><?php esc_html_e( '登录后签到', 'appverse' ); ?></a>
			<?php endif; ?>
		</div>
	</section>

	<section class="av-card av-card--pad av-calendar-card">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php echo esc_html( sprintf( '%d 年 %d 月', $calendar['year'], $calendar['month'] ) ); ?></h2>
			<span class="av-calendar-card__tip"><?php esc_html_e( '每天 00:00 重置', 'appverse' ); ?></span>
		</div>

		<div class="av-calendar__week" aria-hidden="true">
			<?php foreach ( $weekdays as $weekday ) : ?>
				<span><?php echo esc_html( $weekday ); ?></span>
			<?php endforeach; ?>
		</div>
		<div class="av-calendar">
			<?php for ( $i = 0; $i < (int) $calendar['offset']; $i++ ) : ?>
				<span class="av-day av-day--blank" aria-hidden="true"></span>
			<?php endfor; ?>
			<?php for ( $day = 1; $day <= (int) $calendar['days']; $day++ ) : ?>
				<?php
				$classes = array( 'av-day' );
				if ( $day === (int) $calendar['today'] ) {
					$classes[] = 'is-today';
				}
				if ( in_array( $day, (array) $calendar['days_on'], true ) ) {
					$classes[] = 'is-checked';
				}
				if ( $day > (int) $calendar['today'] ) {
					$classes[] = 'is-future';
				}
				?>
				<span class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<?php echo esc_html( (string) $day ); ?>
					<?php if ( in_array( $day, (array) $calendar['days_on'], true ) ) : ?>
						<?php appverse_the_icon( 'check', 10, 'av-day__check' ); ?>
					<?php endif; ?>
				</span>
			<?php endfor; ?>
		</div>
	</section>

	<section class="av-rewards">
		<?php foreach ( $rewards as $reward ) : ?>
			<div class="av-reward<?php echo (int) $calendar['streak'] >= $reward['days'] ? ' is-done' : ''; ?>">
				<span class="av-reward__check"><?php appverse_the_icon( 'check', 12 ); ?></span>
				<span class="av-reward__label"><?php echo esc_html( $reward['label'] ); ?></span>
				<span class="av-reward__points">+<?php echo esc_html( (string) $reward['points'] ); ?> <?php esc_html_e( '积分', 'appverse' ); ?></span>
			</div>
		<?php endforeach; ?>
	</section>

	<section class="av-card av-card--pad av-rules">
		<h2 class="av-section__title"><?php esc_html_e( '签到规则', 'appverse' ); ?></h2>
		<ul class="av-rules__list">
			<li><?php esc_html_e( '每天可签到一次，每次获得 5 积分与 10 点经验。', 'appverse' ); ?></li>
			<li><?php esc_html_e( '连续签到会累计天数，中断后重新计算。', 'appverse' ); ?></li>
			<li><?php esc_html_e( '积分可在积分商城兑换会员与周边。', 'appverse' ); ?></li>
		</ul>
	</section>

</main>
<?php
get_footer();
