<?php
/**
 * 404 page.
 *
 * @package Appverse
 */

get_header();
?>
<main id="av-main" class="av-main av-main--404">
	<div class="av-notfound">
		<p class="av-notfound__code">404</p>
		<h1 class="av-notfound__title"><?php esc_html_e( '这个页面已经离开了应用商店', 'appverse' ); ?></h1>
		<p class="av-notfound__text"><?php esc_html_e( '链接可能已经失效，或者应用已经下架。你可以回到首页重新开始。', 'appverse' ); ?></p>
		<div class="av-notfound__actions">
			<a class="av-btn av-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '回到首页', 'appverse' ); ?></a>
			<a class="av-btn av-btn--ghost" href="<?php echo esc_url( appverse_apps_url() ); ?>"><?php esc_html_e( '逛逛应用库', 'appverse' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
