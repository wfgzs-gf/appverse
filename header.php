<?php
/**
 * Theme header.
 *
 * @package Appverse
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#eef0f5">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#av-main"><?php esc_html_e( '跳到主要内容', 'appverse' ); ?></a>
<div class="av-app">
	<?php
	if ( is_page_template( 'page-profile.php' ) ) {
		get_template_part( 'template-parts/topbar-profile' );
	} elseif ( is_front_page() || is_home() ) {
		get_template_part( 'template-parts/topbar-home' );
	} else {
		get_template_part( 'template-parts/topbar-inner' );
	}
	?>
