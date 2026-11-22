<?php
/**
 * Home / community top bar with search.
 *
 * @package Appverse
 */

$badge = appverse_notification_count();
?>
<header class="av-topbar av-topbar--home">
	<form class="av-searchbar" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<button class="av-searchbar__submit" type="submit" aria-label="<?php esc_attr_e( '搜索', 'appverse' ); ?>">
			<?php appverse_the_icon( 'search', 22 ); ?>
		</button>
		<input class="av-searchbar__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( '搜索', 'appverse' ); ?>" aria-label="<?php esc_attr_e( '搜索应用与动态', 'appverse' ); ?>">
	</form>

	<button class="av-iconbtn av-iconbtn--bell" type="button" data-toast="<?php esc_attr_e( '暂时没有新通知', 'appverse' ); ?>" aria-label="<?php esc_attr_e( '通知', 'appverse' ); ?>">
		<?php appverse_the_icon( 'bell', 22 ); ?>
		<?php if ( $badge ) : ?><span class="av-badge"><?php echo esc_html( (string) $badge ); ?></span><?php endif; ?>
	</button>

	<a class="av-topbar__avatar" href="<?php echo esc_url( is_user_logged_in() ? appverse_page_url( 'me' ) : wp_login_url( appverse_current_url() ) ); ?>" aria-label="<?php esc_attr_e( '我的', 'appverse' ); ?>">
		<?php appverse_avatar( 0, 'sm', array( 'badge' => (bool) $badge ) ); ?>
	</a>
</header>
