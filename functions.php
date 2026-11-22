<?php
/**
 * Appverse theme bootstrap.
 *
 * @package Appverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'APPVERSE_VERSION', '1.0.0' );
define( 'APPVERSE_DIR', get_template_directory() );
define( 'APPVERSE_URI', get_template_directory_uri() );

require_once APPVERSE_DIR . '/inc/icons.php';
require_once APPVERSE_DIR . '/inc/core.php';
require_once APPVERSE_DIR . '/inc/template-tags.php';
require_once APPVERSE_DIR . '/inc/demo.php';

/**
 * Theme supports, menus and image sizes.
 */
function appverse_setup() {
	load_theme_textdomain( 'appverse', APPVERSE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 96,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );

	register_nav_menus(
		array(
			'tabbar' => __( '底部导航', 'appverse' ),
			'footer' => __( '页脚导航', 'appverse' ),
		)
	);

	add_image_size( 'appverse-icon', 256, 256, true );
	add_image_size( 'appverse-shot', 720, 1280, true );
	add_image_size( 'appverse-banner', 1200, 560, true );
	add_image_size( 'appverse-card', 640, 360, true );
}
add_action( 'after_setup_theme', 'appverse_setup' );

/**
 * Content width.
 */
function appverse_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'appverse_content_width', 0 );

/**
 * Front-end assets.
 */
function appverse_scripts() {
	$style_path = APPVERSE_DIR . '/style.css';
	$main_path  = APPVERSE_DIR . '/assets/css/main.css';
	$js_path    = APPVERSE_DIR . '/assets/js/main.js';

	wp_enqueue_style( 'appverse-style', get_stylesheet_uri(), array(), appverse_asset_version( $style_path ) );
	wp_enqueue_style(
		'appverse-main',
		APPVERSE_URI . '/assets/css/main.css',
		array( 'appverse-style' ),
		appverse_asset_version( $main_path )
	);

	wp_enqueue_script(
		'appverse-main',
		APPVERSE_URI . '/assets/js/main.js',
		array(),
		appverse_asset_version( $js_path ),
		true
	);

	wp_localize_script(
		'appverse-main',
		'AppverseData',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'appverse_nonce' ),
			'homeUrl'     => home_url( '/' ),
			'loginUrl'    => wp_login_url( appverse_current_url() ),
			'profileUrl'  => appverse_page_url( 'me' ),
			'isLoggedIn'  => is_user_logged_in(),
			'badgeCount'  => appverse_notification_count(),
			'downloadTip' => get_theme_mod( 'appverse_download_tip', __( '下载即表示同意《用户协议》与《隐私政策》，应用由开发者提供，请自行甄别。', 'appverse' ) ),
			'i18n'        => array(
				'liked'       => __( '已点赞', 'appverse' ),
				'unliked'     => __( '已取消', 'appverse' ),
				'checkin'     => __( '签到成功，积分 +5', 'appverse' ),
				'checked'     => __( '今天已经签到啦', 'appverse' ),
				'downloading' => __( '开始下载…', 'appverse' ),
				'login'       => __( '登录后才能操作', 'appverse' ),
				'error'       => __( '操作失败，请稍后重试', 'appverse' ),
				'copy'        => __( '链接已复制', 'appverse' ),
				'close'       => __( '关闭', 'appverse' ),
				'continue'    => __( '继续下载', 'appverse' ),
				'cancel'      => __( '取消', 'appverse' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'appverse_scripts' );

/**
 * Asset cache-busting version.
 *
 * @param string $path Absolute path.
 * @return string
 */
function appverse_asset_version( $path ) {
	if ( is_readable( $path ) ) {
		return (string) filemtime( $path );
	}
	return APPVERSE_VERSION;
}

/**
 * Admin assets for app editor.
 *
 * @param string $hook Current admin page.
 */
function appverse_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$is_app = $screen && 'app' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	if ( ! $is_app ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'appverse-admin', APPVERSE_URI . '/assets/admin/admin.css', array(), APPVERSE_VERSION );
	wp_enqueue_script( 'appverse-admin', APPVERSE_URI . '/assets/admin/admin.js', array( 'jquery' ), APPVERSE_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'appverse_admin_assets' );

/**
 * Body classes.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function appverse_body_classes( $classes ) {
	$classes[] = 'av-body';
	$classes[] = is_user_logged_in() ? 'av-logged-in' : 'av-logged-out';

	if ( is_front_page() ) {
		$classes[] = 'av-is-home';
	}
	if ( is_page_template( 'page-profile.php' ) ) {
		$classes[] = 'av-is-profile';
	}
	if ( is_home() ) {
		$classes[] = 'av-is-feed';
	}
	if ( is_singular( 'app' ) ) {
		$classes[] = 'av-is-app';
	}

	return $classes;
}
add_filter( 'body_class', 'appverse_body_classes' );

/**
 * Excerpt length.
 *
 * @return int
 */
function appverse_excerpt_length() {
	return 32;
}
add_filter( 'excerpt_length', 'appverse_excerpt_length', 20 );

/**
 * Excerpt more.
 *
 * @return string
 */
function appverse_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'appverse_excerpt_more' );

/**
 * Current URL helper for login redirects.
 *
 * @return string
 */
function appverse_current_url() {
	global $wp;
	if ( is_admin() ) {
		return home_url( '/' );
	}
	$path = isset( $wp->request ) ? $wp->request : '';
	return home_url( '/' . ltrim( $path, '/' ) );
}

/**
 * Remove emoji payload for a leaner app shell.
 */
function appverse_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'appverse_disable_emoji' );

/**
 * Register widget areas (sidebar/footer) for completeness.
 */
function appverse_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( '页脚小工具', 'appverse' ),
			'id'            => 'footer-1',
			'description'   => __( '显示在页面底部的补充区域。', 'appverse' ),
			'before_widget' => '<section id="%1$s" class="av-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="av-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'appverse_widgets_init' );

/**
 * Flush rewrite rules on activation/deactivation.
 */
function appverse_after_switch_theme() {
	appverse_register_content_types();
	flush_rewrite_rules();

	// Seed the demo store on first activation so the theme preview is complete.
	if ( ! get_option( 'appverse_demo_imported' ) && ! get_option( 'appverse_demo_dismissed' ) ) {
		set_transient( 'appverse_activation_notice', 1, 120 );
		if ( function_exists( 'appverse_import_demo_content' ) ) {
			appverse_import_demo_content();
		}
	}
}
add_action( 'after_switch_theme', 'appverse_after_switch_theme' );

/**
 * Clean rewrite rules on switch away.
 */
function appverse_switch_theme() {
	flush_rewrite_rules();
}
add_action( 'switch_theme', 'appverse_switch_theme' );

/**
 * Hide the WordPress admin bar on the app-like front end.
 *
 * The store shell is designed as a standalone mobile app; the admin bar
 * breaks the top bar on every page. Admin access stays available via /wp-admin/.
 *
 * @param bool $show Whether to show the bar.
 * @return bool
 */
function appverse_hide_admin_bar( $show ) {
	return is_admin() ? $show : false;
}
add_filter( 'show_admin_bar', 'appverse_hide_admin_bar' );

/**
 * Add a "theme ready" admin notice.
 */
function appverse_activation_notice() {
	if ( ! get_transient( 'appverse_activation_notice' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	delete_transient( 'appverse_activation_notice' );
	$demo_url = admin_url( 'themes.php?page=appverse-demo' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Appverse 已启用。', 'appverse' ),
		esc_html__( '演示应用、动态和页面已经准备好，你可以直接预览首页。', 'appverse' ),
		esc_url( $demo_url ),
		esc_html__( '管理演示内容', 'appverse' )
	);
}
add_action( 'admin_notices', 'appverse_activation_notice' );
