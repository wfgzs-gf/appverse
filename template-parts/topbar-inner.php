<?php
/**
 * Inner page top bar.
 *
 * @package Appverse
 */

?>
<header class="av-topbar av-topbar--inner">
	<a class="av-iconbtn av-iconbtn--back" href="<?php echo esc_url( appverse_get_back_url() ); ?>" aria-label="<?php esc_attr_e( '返回', 'appverse' ); ?>">
		<?php appverse_the_icon( 'chevron-left', 22 ); ?>
	</a>
	<div class="av-topbar__title"><?php echo esc_html( appverse_get_page_title() ); ?></div>
	<a class="av-iconbtn" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( '搜索', 'appverse' ); ?>">
		<?php appverse_the_icon( 'search', 22 ); ?>
	</a>
</header>
