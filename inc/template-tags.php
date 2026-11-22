<?php
/**
 * Template helpers.
 *
 * @package Appverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL of a bundled demo asset.
 *
 * @param string $path Relative path under assets/demo.
 * @return string
 */
function appverse_demo_asset( $path ) {
	$path = ltrim( $path, '/' );
	$file = APPVERSE_DIR . '/assets/demo/' . $path;
	if ( ! is_readable( $file ) ) {
		return '';
	}
	return APPVERSE_URI . '/assets/demo/' . $path;
}

/**
 * Normalized app meta.
 *
 * @param int $post_id App post ID.
 * @return array
 */
function appverse_get_app_meta( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$fields  = appverse_app_fields();
	$meta    = array();

	foreach ( $fields as $key => $field ) {
		$value = get_post_meta( $post_id, '_app_' . $key, true );
		if ( '' === $value || null === $value ) {
			$value = $field['default'];
		}
		$meta[ $key ] = $value;
	}

	$meta['downloads'] = (int) $meta['downloads'];
	$meta['rating']    = (float) $meta['rating'];
	$meta['official']  = (bool) $meta['official'];
	$meta['badge']     = $meta['badge'] ? $meta['badge'] : ( $meta['official'] ? __( '正式版', 'appverse' ) : __( '提取版', 'appverse' ) );
	$meta['screenshots'] = appverse_get_screenshot_ids( $post_id );
	$meta['developer'] = $meta['developer'] ? $meta['developer'] : get_bloginfo( 'name' );
	$meta['updated']   = $meta['updated'] ? $meta['updated'] : get_the_modified_date( 'Y-m-d', $post_id );
	$meta['url']       = get_permalink( $post_id );
	if ( ! $meta['download_url'] ) {
		$meta['download_url'] = $meta['url'];
	}

	return $meta;
}

/**
 * Screenshot attachment IDs for an app.
 *
 * @param int $post_id App ID.
 * @return int[]
 */
function appverse_get_screenshot_ids( $post_id ) {
	$raw = get_post_meta( $post_id, '_app_screenshots', true );
	if ( is_array( $raw ) ) {
		return array_values( array_filter( array_map( 'absint', $raw ) ) );
	}
	if ( ! $raw ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) );
}

/**
 * App icon markup.
 *
 * @param int   $post_id App ID.
 * @param string $size   xs|sm|md|lg|xl.
 * @param array $args    badge, class, link, echo.
 * @return string
 */
function appverse_get_app_icon( $post_id = 0, $size = 'md', $args = array() ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$args    = wp_parse_args(
		$args,
		array(
			'badge' => true,
			'class' => '',
			'echo'  => false,
		)
	);
	$meta    = appverse_get_app_meta( $post_id );
	$src     = get_the_post_thumbnail_url( $post_id, 'appverse-icon' );
	if ( ! $src ) {
		$src = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
	}
	if ( ! $src ) {
		$fallback = appverse_demo_asset( 'icons/app-' . str_pad( (string) ( ( (int) $post_id % 12 ) + 1 ), 2, '0', STR_PAD_LEFT ) . '.png' );
		$src      = $fallback ? $fallback : appverse_demo_asset( 'icons/app-default.png' );
	}

	$badge_class = $meta['official'] ? 'av-appicon__badge--official' : 'av-appicon__badge--extract';
	$classes     = trim( 'av-appicon av-appicon--' . sanitize_html_class( $size ) . ' ' . $args['class'] );
	$alt         = get_the_title( $post_id );

	$html  = '<span class="' . esc_attr( $classes ) . '">';
	$html .= '<img class="av-appicon__img" src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" width="256" height="256" loading="lazy" decoding="async">';
	if ( $args['badge'] && $meta['badge'] ) {
		$html .= '<span class="av-appicon__badge ' . esc_attr( $badge_class ) . '">' . esc_html( $meta['badge'] ) . '</span>';
	}
	$html .= '</span>';

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
	return $html;
}

/**
 * Echo app icon.
 *
 * @param int    $post_id App ID.
 * @param string $size    Size.
 * @param array  $args    Args.
 */
function appverse_app_icon( $post_id = 0, $size = 'md', $args = array() ) {
	$args['echo'] = true;
	appverse_get_app_icon( $post_id, $size, $args );
}

/**
 * Format a download count for display.
 *
 * @param int $number Number.
 * @return string
 */
function appverse_format_count( $number ) {
	$number = (int) $number;
	if ( $number >= 100000000 ) {
		return round( $number / 100000000, 1 ) . '亿';
	}
	if ( $number >= 10000 ) {
		return round( $number / 10000, 1 ) . '万';
	}
	return number_format_i18n( $number );
}

/**
 * Get download URL.
 *
 * @param int $post_id App ID.
 * @return string
 */
function appverse_get_download_url( $post_id ) {
	$url = get_post_meta( $post_id, '_app_download_url', true );
	return $url ? $url : get_permalink( $post_id );
}

/**
 * Download button markup.
 *
 * @param int    $post_id App ID.
 * @param string $label   Button label.
 * @param string $class   Extra classes.
 * @return string
 */
function appverse_get_download_button( $post_id, $label = '', $class = '' ) {
	$meta  = appverse_get_app_meta( $post_id );
	$label = $label ? $label : __( '下载', 'appverse' );
	$class = trim( 'av-btn av-btn--download ' . $class );

	return sprintf(
		'<button type="button" class="%1$s" data-download data-app-id="%2$d" data-app-name="%3$s" data-app-version="%4$s" data-app-url="%5$s" data-app-count="%6$d">%7$s</button>',
		esc_attr( $class ),
		(int) $post_id,
		esc_attr( get_the_title( $post_id ) ),
		esc_attr( $meta['version'] ),
		esc_url( $meta['download_url'] ),
		(int) $meta['downloads'],
		esc_html( $label )
	);
}

/**
 * Echo download button.
 *
 * @param int    $post_id App ID.
 * @param string $label   Label.
 * @param string $class   Class.
 */
function appverse_download_button( $post_id, $label = '', $class = '' ) {
	echo appverse_get_download_button( $post_id, $label, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in builder.
}

/**
 * Star rating markup.
 *
 * @param float  $rating Rating 0-5.
 * @param string $class  Extra class.
 * @return string
 */
function appverse_get_stars( $rating, $class = '' ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$width  = ( $rating / 5 ) * 100;
	return sprintf(
		'<span class="av-stars %1$s" role="img" aria-label="%2$s"><span class="av-stars__base" aria-hidden="true">★★★★★</span><span class="av-stars__fill" aria-hidden="true" style="width:%3$s%%">★★★★★</span></span>',
		esc_attr( $class ),
		/* translators: %s rating value */
		esc_attr( sprintf( __( '评分 %s 分', 'appverse' ), number_format_i18n( $rating, 1 ) ) ),
		esc_attr( (string) $width )
	);
}

/**
 * Notification badge count.
 *
 * @return int
 */
function appverse_notification_count() {
	return max( 0, (int) get_theme_mod( 'appverse_notification_count', 62 ) );
}

/**
 * Resolve a core page URL by slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function appverse_page_url( $slug ) {
	static $cache = array();
	$slug = sanitize_title( $slug );
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	if ( 'square' === $slug && get_option( 'page_for_posts' ) ) {
		$url = get_permalink( (int) get_option( 'page_for_posts' ) );
	} else {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
	}

	$cache[ $slug ] = $url;
	return $url;
}

/**
 * App archive URL with optional query.
 *
 * @param array $args Query args.
 * @return string
 */
function appverse_apps_url( $args = array() ) {
	$url = get_post_type_archive_link( 'app' );
	if ( ! $url ) {
		$url = home_url( '/apps/' );
	}
	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Avatar URL.
 *
 * @param int $user_id User ID.
 * @param int $size    Pixels.
 * @return string
 */
function appverse_avatar_url( $user_id = 0, $size = 120 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$default = appverse_demo_asset( 'avatars/user-' . str_pad( (string) ( ( (int) $user_id % 4 ) + 1 ), 2, '0', STR_PAD_LEFT ) . '.png' );

	if ( $user_id ) {
		$attachment_id = (int) get_user_meta( $user_id, 'appverse_avatar', true );
		if ( $attachment_id ) {
			$custom = wp_get_attachment_image_url( $attachment_id, array( $size, $size ) );
			if ( $custom ) {
				return $custom;
			}
		}
	}

	// Keep demo/site avatars local: never hit gravatar.com for placeholder images.
	if ( get_option( 'appverse_demo_imported' ) && $default ) {
		return $default;
	}

	$fallback = appverse_demo_asset( 'avatars/user-01.png' );
	$url      = get_avatar_url(
		$user_id,
		array(
			'size'    => $size,
			'default' => $fallback ? $fallback : 'mystery',
		)
	);
	return $url ? $url : ( $fallback ? $fallback : '' );
}

/**
 * Avatar markup.
 *
 * @param int    $user_id User ID.
 * @param string $size    sm|md|lg.
 * @param array  $args    Extra args (link, badge, echo, class).
 * @return string
 */
function appverse_get_avatar( $user_id = 0, $size = 'sm', $args = array() ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$args    = wp_parse_args(
		$args,
		array(
			'link'  => false,
			'badge' => false,
			'echo'  => false,
			'class' => '',
		)
	);
	$px      = 'lg' === $size ? 180 : ( 'md' === $size ? 120 : 88 );
	$url     = appverse_avatar_url( $user_id, $px );
	$name    = $user_id ? get_the_author_meta( 'display_name', $user_id ) : __( '游客', 'appverse' );
	$html    = '<span class="av-avatar av-avatar--' . esc_attr( $size ) . ' ' . esc_attr( $args['class'] ) . '">';
	$html   .= '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" width="' . esc_attr( (string) $px ) . '" height="' . esc_attr( (string) $px ) . '" loading="lazy" decoding="async">';
	if ( $args['badge'] ) {
		$html .= '<span class="av-badge">' . esc_html( (string) appverse_notification_count() ) . '</span>';
	}
	$html .= '</span>';

	if ( $args['link'] ) {
		$href  = is_user_logged_in() ? appverse_page_url( 'me' ) : wp_login_url( appverse_current_url() );
		$html  = '<a class="av-avatar-link" href="' . esc_url( $href ) . '" aria-label="' . esc_attr( $name ) . '">' . $html . '</a>';
	}

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in builder.
	}
	return $html;
}

/**
 * Echo avatar.
 *
 * @param int    $user_id User ID.
 * @param string $size    Size.
 * @param array  $args    Args.
 */
function appverse_avatar( $user_id = 0, $size = 'sm', $args = array() ) {
	$args['echo'] = true;
	appverse_get_avatar( $user_id, $size, $args );
}

/**
 * User store stats.
 *
 * @param int|null $user_id User ID or null for current.
 * @return array
 */
function appverse_get_user_stats( $user_id = null ) {
	$user_id = null === $user_id ? get_current_user_id() : (int) $user_id;
	$user    = $user_id ? get_userdata( $user_id ) : false;

	$level     = (int) get_user_meta( $user_id, 'appverse_level', true );
	$exp       = (int) get_user_meta( $user_id, 'appverse_exp', true );
	$exp_max   = (int) get_user_meta( $user_id, 'appverse_exp_max', true );
	$points    = (int) get_user_meta( $user_id, 'appverse_points', true );
	$share     = (int) get_user_meta( $user_id, 'appverse_share_value', true );
	$traffic   = get_user_meta( $user_id, 'appverse_traffic', true );
	$following = (int) get_user_meta( $user_id, 'appverse_following', true );
	$posts     = $user_id ? (int) count_user_posts( $user_id, 'post', true ) : 0;

	$exp_max = $exp_max > 0 ? $exp_max : 1000;

	return array(
		'user_id'     => $user_id,
		'name'        => $user ? $user->display_name : __( '未登录', 'appverse' ),
		'level'       => $level,
		'exp'         => $exp,
		'exp_max'     => $exp_max,
		'exp_percent' => min( 100, round( ( $exp / max( 1, $exp_max ) ) * 100, 1 ) ),
		'points'      => $points,
		'share_value' => $share,
		'traffic'     => $traffic ? $traffic : '0MB',
		'following'   => $following,
		'posts'       => $posts,
		'checked_in'  => appverse_is_checked_in( $user_id ),
		'streak'      => (int) get_user_meta( $user_id, 'appverse_checkin_streak', true ),
		'login_url'   => wp_login_url( appverse_current_url() ),
	);
}

/**
 * Whether a user checked in today.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function appverse_is_checked_in( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	return current_time( 'Y-m-d' ) === get_user_meta( $user_id, 'appverse_checkin', true );
}

/**
 * Check-in calendar for the current month.
 *
 * @param int $user_id User ID.
 * @return array
 */
function appverse_get_checkin_calendar( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$today   = current_time( 'Y-m-d' );
	$first   = strtotime( gmdate( 'Y-m-01', strtotime( $today ) ) );
	$days    = (int) gmdate( 't', $first );
	$offset  = (int) gmdate( 'N', $first ) - 1;
	$checked = '';
	if ( $user_id ) {
		$checked = get_user_meta( $user_id, 'appverse_checkin', true );
	}
	$streak = (int) get_user_meta( $user_id, 'appverse_checkin_streak', true );

	$calendar = array(
		'year'    => (int) gmdate( 'Y', $first ),
		'month'   => (int) gmdate( 'n', $first ),
		'offset'  => $offset,
		'days'    => $days,
		'today'   => (int) gmdate( 'j', strtotime( $today ) ),
		'checked' => $checked === $today,
		'streak'  => $streak,
		'days_on' => array(),
	);
	// Mark the days covered by the current streak inside this month.
	$start = ( $checked === $today ) ? 0 : 1;
	$end   = min( $streak + $start, (int) gmdate( 'j', strtotime( $today ) ) );
	for ( $i = $start; $i < $end; $i++ ) {
		$calendar['days_on'][] = (int) gmdate( 'j', strtotime( $today . ' -' . $i . ' day' ) );
	}
	return $calendar;
}

/**
 * Relative "today / yesterday" time for feed cards.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function appverse_post_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$time    = get_the_time( 'Y-m-d H:i', $post_id );
	$today   = current_time( 'Y-m-d' );
	$yday    = gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) );
	$date    = get_the_time( 'Y-m-d', $post_id );
	$clock   = get_the_time( 'H:i', $post_id );

	if ( $date === $today ) {
		return sprintf( /* translators: %s time */ __( '今天 %s', 'appverse' ), $clock );
	}
	if ( $date === $yday ) {
		return sprintf( /* translators: %s time */ __( '昨天 %s', 'appverse' ), $clock );
	}
	return get_the_time( 'm-d H:i', $post_id );
}

/**
 * Post device string.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function appverse_get_feed_device( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$device  = get_post_meta( $post_id, '_feed_device', true );
	return $device ? $device : 'Appverse';
}

/**
 * Attached app for a feed post.
 *
 * @param int $post_id Post ID.
 * @return WP_Post|null
 */
function appverse_get_attached_app( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$app_id  = (int) get_post_meta( $post_id, '_feed_app', true );
	if ( ! $app_id ) {
		return null;
	}
	$app = get_post( $app_id );
	return ( $app && 'app' === $app->post_type && 'publish' === $app->post_status ) ? $app : null;
}

/**
 * Author level.
 *
 * @param int $user_id User ID.
 * @return int
 */
function appverse_get_user_level( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_the_author_meta( 'ID' );
	return (int) get_user_meta( $user_id, 'appverse_level', true );
}

/**
 * Like state for a post.
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID.
 * @return array
 */
function appverse_get_reaction_state( $post_id, $user_id = 0 ) {
	$user_id  = $user_id ? $user_id : get_current_user_id();
	$likes    = array_map( 'absint', (array) get_user_meta( $user_id, 'appverse_liked_posts', true ) );
	$dislikes = array_map( 'absint', (array) get_user_meta( $user_id, 'appverse_disliked_posts', true ) );
	return array(
		'liked'        => in_array( (int) $post_id, $likes, true ),
		'disliked'     => in_array( (int) $post_id, $dislikes, true ),
		'likeCount'    => max( 0, (int) get_post_meta( $post_id, '_appverse_like_count', true ) ),
		'dislikeCount' => max( 0, (int) get_post_meta( $post_id, '_appverse_dislike_count', true ) ),
	);
}

/**
 * Featured apps for the home grid.
 *
 * @param int $limit Number.
 * @return WP_Query
 */
function appverse_featured_apps( $limit = 5 ) {
	$raw      = get_option( 'appverse_featured_ids', '' );
	$featured = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
	$featured = array_values( array_filter( array_map( 'absint', $featured ) ) );
	$sticky   = array_filter( array_map( 'absint', (array) get_option( 'sticky_posts' ) ) );
	$args     = array(
		'post_type'           => 'app',
		'posts_per_page'      => $limit,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $featured ) {
		$args['post__in'] = array_slice( $featured, 0, $limit );
		$args['orderby']  = 'post__in';
	} elseif ( $sticky ) {
		$args['post__in'] = array_slice( $sticky, 0, $limit );
		$args['orderby']  = 'post__in';
	} else {
		$args['meta_key'] = '_app_downloads';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
	}
	return new WP_Query( $args );
}

/**
 * Query apps.
 *
 * @param array $args Query overrides.
 * @return WP_Query
 */
function appverse_query_apps( $args = array() ) {
	$defaults = array(
		'post_type'      => 'app',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'meta_key'       => '_app_downloads',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	);
	return new WP_Query( wp_parse_args( $args, $defaults ) );
}

/**
 * Related apps.
 *
 * @param int $post_id App ID.
 * @param int $limit   Number.
 * @return WP_Query
 */
function appverse_related_apps( $post_id, $limit = 4 ) {
	$terms = wp_get_post_terms( $post_id, 'app_category', array( 'fields' => 'ids' ) );
	$args  = array(
		'post_type'           => 'app',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( ! is_wp_error( $terms ) && $terms ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'app_category',
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		);
	}
	return new WP_Query( $args );
}

/**
 * Banner slides: customizer values with demo fallbacks.
 *
 * @return array
 */
function appverse_get_banners() {
	$defaults = array(
		array(
			'image'    => appverse_demo_asset( 'banners/banner-01.jpg' ),
			'eyebrow'  => __( 'AppShare 出品', 'appverse' ),
			'title'    => __( '灵界', 'appverse' ),
			'latin'    => 'LingVerse',
			'subtitle' => __( '打开即玩的文字修仙世界', 'appverse' ),
			'cta'      => __( '立即入世', 'appverse' ),
			'url'      => appverse_apps_url( array( 'app_category' => 'games' ) ),
		),
		array(
			'image'    => appverse_demo_asset( 'banners/banner-02.jpg' ),
			'eyebrow'  => __( '本周精选', 'appverse' ),
			'title'    => __( '夜色电台', 'appverse' ),
			'latin'    => 'Night Radio',
			'subtitle' => __( '把深夜留给声音与故事', 'appverse' ),
			'cta'      => __( '去看看', 'appverse' ),
			'url'      => appverse_apps_url( array( 'sort' => 'new' ) ),
		),
		array(
			'image'    => appverse_demo_asset( 'banners/banner-03.jpg' ),
			'eyebrow'  => __( '独立开发者计划', 'appverse' ),
			'title'    => __( '小团队，大作品', 'appverse' ),
			'latin'    => 'Indie Makers',
			'subtitle' => __( '发现那些认真做产品的人', 'appverse' ),
			'cta'      => __( '逛独立区', 'appverse' ),
			'url'      => appverse_page_url( 'indie' ),
		),
	);

	$slides = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$slide = $defaults[ $i - 1 ];
		$image = get_theme_mod( 'appverse_banner_' . $i . '_image', '' );
		if ( $image ) {
			$slide['image'] = $image;
		}
		foreach ( array( 'eyebrow', 'title', 'latin', 'subtitle', 'cta', 'url' ) as $key ) {
			$value = get_theme_mod( 'appverse_banner_' . $i . '_' . $key, '' );
			if ( $value ) {
				$slide[ $key ] = $value;
			}
		}
		if ( $slide['image'] ) {
			$slides[] = $slide;
		}
	}
	return $slides;
}

/**
 * Tab bar entries.
 *
 * @return array
 */
function appverse_get_tabbar_items() {
	$items = array();
	$menu  = wp_get_nav_menu_items( 'tabbar' );

	if ( $menu ) {
		$active_map = array( is_front_page(), is_home(), is_page_template( 'page-profile.php' ) );
		foreach ( array_values( $menu ) as $index => $item ) {
			$items[] = array(
				'label'  => $item->title,
				'url'    => $item->url,
				'icon'   => 0 === $index ? 'home' : ( 1 === $index ? 'compass' : 'user' ),
				'active' => isset( $active_map[ $index ] ) ? $active_map[ $index ] : false,
				'badge'  => 2 === $index ? appverse_notification_count() : 0,
			);
		}
		if ( $items ) {
			return $items;
		}
	}

	$items = array(
		array(
			'label'  => __( '首页', 'appverse' ),
			'url'    => home_url( '/' ),
			'icon'   => 'home',
			'active' => is_front_page(),
			'badge'  => 0,
		),
		array(
			'label'  => __( '广场', 'appverse' ),
			'url'    => appverse_page_url( 'square' ),
			'icon'   => 'compass',
			'active' => is_home(),
			'badge'  => 0,
		),
		array(
			'label'  => __( '我的', 'appverse' ),
			'url'    => appverse_page_url( 'me' ),
			'icon'   => 'user',
			'active' => is_page_template( 'page-profile.php' ),
			'badge'  => appverse_notification_count(),
		),
	);

	return $items;
}

/**
 * Render tab bar.
 */
function appverse_tabbar() {
	$items = appverse_get_tabbar_items();
	if ( ! $items ) {
		return;
	}
	?>
	<nav class="av-tabbar" aria-label="<?php esc_attr_e( '底部导航', 'appverse' ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$icon = $item['icon'];
			if ( $item['active'] ) {
				$filled_defs = appverse_icon_definitions();
				if ( isset( $filled_defs[ $icon . '-fill' ] ) ) {
					$icon .= '-fill';
				}
			}
			?>
			<a class="av-tab<?php echo $item['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>" <?php echo $item['active'] ? 'aria-current="page"' : ''; ?>>
				<span class="av-tab__icon">
					<?php appverse_the_icon( $icon, 25 ); ?>
					<?php if ( ! empty( $item['badge'] ) ) : ?>
						<span class="av-tab__badge"><?php echo esc_html( (string) $item['badge'] ); ?></span>
					<?php endif; ?>
				</span>
				<span class="av-tab__label"><?php echo esc_html( $item['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * App categories used in filter bars and category cards.
 *
 * @param int $limit Limit.
 * @return WP_Term[]
 */
function appverse_get_app_categories( $limit = 0 ) {
	$args = array(
		'taxonomy'   => 'app_category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	);
	if ( $limit ) {
		$args['number'] = $limit;
	}
	$terms = get_terms( $args );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Feed tabs config.
 *
 * @return array
 */
function appverse_get_feed_tabs() {
	return array(
		'all'        => __( '全部', 'appverse' ),
		'dynamic'    => __( '动态', 'appverse' ),
		'attachment' => __( '附件', 'appverse' ),
		'developer'  => __( '开发者', 'appverse' ),
	);
}

/**
 * Current feed tab.
 *
 * @return string
 */
function appverse_current_feed_tab() {
	$feed = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return array_key_exists( $feed, appverse_get_feed_tabs() ) ? $feed : 'all';
}

/**
 * Filter the community query by feed type.
 *
 * @param WP_Query $query Main query.
 */
function appverse_filter_feed_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}
	$feed = appverse_current_feed_tab();
	if ( 'all' !== $feed ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'feed_type',
					'field'    => 'slug',
					'terms'    => $feed,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'appverse_filter_feed_query' );

/**
 * Pagination markup.
 *
 * @param WP_Query|null $query Query.
 */
function appverse_pagination( $query = null ) {
	$query = $query ? $query : $GLOBALS['wp_query'];
	$links = paginate_links(
		array(
			'total'     => (int) $query->max_num_pages,
			'current'   => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
			'prev_text' => appverse_icon( 'chevron-left', 18 ),
			'next_text' => appverse_icon( 'chevron-right', 18 ),
			'type'      => 'array',
			'mid_size'  => 1,
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="av-pager" aria-label="' . esc_attr__( '分页', 'appverse' ) . '">';
	foreach ( $links as $link ) {
		echo '<span class="av-pager__item">' . wp_kses_post( $link ) . '</span>';
	}
	echo '</nav>';
}

/**
 * Page heading block.
 *
 * @param string $title    Title.
 * @param string $subtitle Subtitle.
 * @param string $eyebrow  Small label.
 */
function appverse_page_heading( $title, $subtitle = '', $eyebrow = '' ) {
	?>
	<header class="av-pagehead">
		<?php if ( $eyebrow ) : ?>
			<p class="av-pagehead__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
		<h1 class="av-pagehead__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $subtitle ) : ?>
			<p class="av-pagehead__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * App list sort filter links.
 *
 * @param string $base_url Base URL.
 * @param string $current  Current sort.
 */
function appverse_sort_bar( $base_url, $current = 'new' ) {
	$sorts = array(
		'new'    => __( '最新', 'appverse' ),
		'hot'    => __( '热门', 'appverse' ),
		'rating' => __( '高分', 'appverse' ),
	);
	echo '<nav class="av-filterbar" aria-label="' . esc_attr__( '排序', 'appverse' ) . '">';
	foreach ( $sorts as $key => $label ) {
		$url = add_query_arg( 'sort', $key, $base_url );
		printf(
			'<a class="av-filter%1$s" href="%2$s">%3$s</a>',
			$key === $current ? ' is-active' : '',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</nav>';
}

/**
 * Get comments count label.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function appverse_comments_label( $post_id = 0 ) {
	$count = (int) get_comments_number( $post_id );
	return $count > 0 ? (string) $count : '0';
}

/**
 * Comment callback with app-store styling.
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    Args.
 * @param int        $depth   Depth.
 */
function appverse_comment_callback( $comment, $args, $depth ) {
	$tag = 'div' === $args['style'] ? 'div' : 'li';
	?>
	<<?php echo esc_attr( $tag ); ?> <?php comment_class( 'av-comment' ); ?> id="comment-<?php comment_ID(); ?>">
		<article class="av-comment__inner">
			<div class="av-comment__avatar"><?php echo get_avatar( $comment, 88 ); ?></div>
			<div class="av-comment__body">
				<header class="av-comment__head">
					<span class="av-comment__name"><?php comment_author(); ?></span>
					<span class="av-comment__meta"><?php echo esc_html( get_comment_date( 'm-d H:i' ) ); ?></span>
				</header>
				<div class="av-comment__text"><?php comment_text(); ?></div>
				<footer class="av-comment__actions">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'reply_text' => __( '回复', 'appverse' ),
							)
						)
					);
					?>
				</footer>
			</div>
		</article>
	<?php
	// Closing tag is added by wp_list_comments.
}

/**
 * App version/updated info line.
 *
 * @param array $meta App meta.
 * @return string
 */
function appverse_app_version_line( $meta ) {
	return sprintf(
		/* translators: 1: version, 2: size */
		__( '版本 %1$s · %2$s', 'appverse' ),
		$meta['version'],
		$meta['size']
	);
}

/**
 * Page title used by the inner top bar.
 *
 * @return string
 */
function appverse_get_page_title() {
	if ( is_front_page() ) {
		return get_bloginfo( 'name' );
	}
	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );
		return $page_id ? get_the_title( $page_id ) : __( '广场', 'appverse' );
	}
	if ( is_singular() ) {
		return get_the_title();
	}
	if ( is_category() || is_tax() || is_tag() ) {
		return single_term_title( '', false );
	}
	if ( is_search() ) {
		return __( '搜索结果', 'appverse' );
	}
	if ( is_404() ) {
		return __( '页面未找到', 'appverse' );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	return get_bloginfo( 'name' );
}

/**
 * Contextual back URL for inner pages.
 *
 * @return string
 */
function appverse_get_back_url() {
	if ( is_singular( 'app' ) ) {
		return appverse_apps_url();
	}
	if ( is_singular( 'post' ) ) {
		return appverse_page_url( 'square' );
	}
	if ( is_tax( 'app_category' ) || is_tax( 'app_tag' ) || is_tax( 'app_collection' ) || is_post_type_archive( 'app' ) ) {
		return appverse_page_url( 'categories' );
	}
	if ( is_search() ) {
		return home_url( '/' );
	}
	$referer = wp_get_referer();
	if ( $referer && false === strpos( $referer, 'wp-admin' ) ) {
		return $referer;
	}
	return home_url( '/' );
}

/**
 * Generic share URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function appverse_share_url( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return add_query_arg(
		array(
			'text' => rawurlencode( get_the_title( $post_id ) ),
			'url'  => rawurlencode( get_permalink( $post_id ) ),
		),
		'https://service.weibo.com/share/share.php'
	);
}
