<?php
/**
 * Profile top bar.
 *
 * @package Appverse
 */

$badge = appverse_notification_count();
?>
<header class="av-topbar av-topbar--profile">
	<a class="av-iconbtn" href="<?php echo esc_url( appverse_page_url( 'downloads' ) ); ?>" aria-label="<?php esc_attr_e( '下载管理', 'appverse' ); ?>">
		<?php appverse_the_icon( 'download', 22 ); ?>
	</a>
	<span class="av-topbar__spacer"></span>
	<button class="av-iconbtn av-iconbtn--bell" type="button" data-toast="<?php esc_attr_e( '暂时没有新通知', 'appverse' ); ?>" aria-label="<?php esc_attr_e( '通知', 'appverse' ); ?>">
		<?php appverse_the_icon( 'bell', 22 ); ?>
		<?php if ( $badge ) : ?><span class="av-badge"><?php echo esc_html( (string) $badge ); ?></span><?php endif; ?>
	</button>
	<a class="av-iconbtn" href="<?php echo esc_url( is_user_logged_in() ? admin_url( 'profile.php' ) : wp_login_url( appverse_current_url() ) ); ?>" aria-label="<?php esc_attr_e( '设置', 'appverse' ); ?>">
		<?php appverse_the_icon( 'settings', 22 ); ?>
	</a>
</header>
